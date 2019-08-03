#!/bin/sh
# Configures the Nomad server

echo "Preparing to install Nomad..."
echo 'libc6 libraries/restart-without-asking boolean true' | sudo debconf-set-selections
export DEBIAN_FRONTEND=noninteractive
sudo apt-get -y update > /dev/null 2>&1
sudo apt-get -y upgrade > /dev/null 2>&1
sudo apt-get install -y unzip jq python3 dnsmasq python3-pip docker.io golang openjdk-8-jre > /dev/null 2>&1
pip3 install awscli

mkdir /etc/nomad.d
mkdir -p /opt/nomad
mkdir -p /opt/nomad/plugins
mkdir -p /etc/consul.d
mkdir -p /opt/consul
mkdir -p /root/.aws
mkdir -p /root/go
mkdir -p /etc/docker

sudo bash -c "cat >/root/.aws/config" <<EOF
[default]
aws_access_key_id=${AWS_ACCESS_KEY}
aws_secret_access_key=${AWS_SECRET_KEY}
EOF
sudo bash -c "cat >/root/.aws/credentials" <<EOF
[default]
aws_access_key_id=${AWS_ACCESS_KEY}
aws_secret_access_key=${AWS_SECRET_KEY}
region=${REGION}
EOF

sudo bash -c "cat >/etc/docker/config.json" <<EOF
{
	"credsStore": "ecr-login"
}
EOF

echo "Installing Consul..."
export CLIENT_IP=`curl http://169.254.169.254/latest/meta-data/local-ipv4`
wget ${CONSUL_URL}
sudo unzip $(basename ${CONSUL_URL}) -d /usr/local/bin/

# Server configuration
sudo bash -c "cat >/etc/consul.d/consul.json" <<EOF
{
    "datacenter": "${REGION}",
    "bind_addr": "$CLIENT_IP",
    "data_dir": "/opt/consul",
    "node_name": "consul-${CLIENT_NAME}",
    "retry_join": ["provider=aws tag_key=${CONSUL_JOIN_KEY} tag_value=${CONSUL_JOIN_VALUE}"],
    "server": false,
    "recursors": ["169.254.169.253"]
}
EOF

# Set Consul up as a systemd service
echo "Installing systemd service for Consul..."
sudo bash -c "cat >/etc/systemd/system/consul.service" << 'EOF'
[Unit]
Description=Hashicorp Consul
Documentation=https://www.consul.io/
Requires=network-online.target
After=network-online.target

[Service]
Type=simple
User=root
WorkingDirectory=/root
ExecStart=/usr/local/bin/consul agent -config-file=/etc/consul.d/consul.json
Restart=on-failure # or always, on-abort, etc
ExecReload=/bin/kill -HUP $MAINPID
KillSignal=SIGTERM
LimitNOFILE=65536

[Install]
WantedBy=multi-user.target
EOF

sudo systemctl enable consul
sudo systemctl start consul

echo "Configure Consul..."
systemctl disable systemd-resolved
systemctl stop systemd-resolved
ls -lh /etc/resolv.conf
rm /etc/resolv.conf
echo "nameserver 127.0.0.1" > /etc/resolv.conf
netplan apply

sudo bash -c "cat >>/etc/dnsmasq.conf" <<EOF
server=/consul/127.0.0.1#8600
server=169.254.169.253#53
no-resolv
log-queries
EOF
systemctl stop dnsmasq
systemctl start dnsmasq

echo "Installing Nomad..."
wget https://releases.hashicorp.com/nomad/0.9.3/nomad_0.9.3_linux_amd64.zip
sudo unzip nomad_0.9.3_linux_amd64.zip -d /usr/local/bin/

# Server configuration
export VAULT_ADDR=http://vault-main.service.${REGION}.consul:8200
export VAULT_TOKEN=$(consul kv get service/vault/root-token)

echo "Setting up environment variables..."
echo "export VAULT_ADDR=http://vault-main.service.${REGION}.consul:8200" >> /home/ubuntu/.profile
echo "export VAULT_TOKEN=$(consul kv get service/vault/root-token)" >> /home/ubuntu/.profile
echo "export VAULT_ADDR=http://vault-main.service.${REGION}.consul:8200" >> /root/.profile
echo "export VAULT_TOKEN=$(consul kv get service/vault/root-token)" >> /root/.profile
echo "export AWS_ACCESS_KEY_ID=${AWS_ACCESS_KEY}" >> /root/.profile
echo "export AWS_SECRET_ACCESS_KEY=${AWS_SECRET_KEY}" >> /root/.profile
echo "export AWS_ACCESS_KEY_ID=${AWS_ACCESS_KEY}" >> /root/.bashrc
echo "export AWS_SECRET_ACCESS_KEY=${AWS_SECRET_KEY}" >> /root/.bashrc
echo "export GOPATH=/root/go" >> /root/.profile
echo "export GOPATH=/root/go" >> /root/.profile

export AWS_ACCESS_KEY_ID="${AWS_ACCESS_KEY}"
export AWS_SECRET_ACCESS_KEY="${AWS_SECRET_KEY}"
export GOPATH="/root/go"

sudo bash -c "cat >/etc/nomad.d/vault-token.json" <<EOF
{
  "policies": [
    "nomad-server"
  ],
  "ttl": "72h",
  "renewable": true,
  "no_parent": true
}
EOF

curl \
    --header "X-Vault-Token: $VAULT_TOKEN" \
    --request POST \
    --data @/etc/nomad.d/vault-token.json \
    $VAULT_ADDR/v1/auth/token/create | jq . > /etc/nomad.d/token.json

export CLIENT_TOKEN="$(cat /etc/nomad.d/token.json | jq -r .auth.client_token | tr -d '\n')"


# Server configuration
sudo bash -c "cat >/etc/nomad.d/nomad.hcl" << 'EOF'
data_dir  = "/opt/nomad"
plugin_dir = "/opt/nomad/plugins"
bind_addr = "0.0.0.0"
datacenter = "${REGION}"

name = "${CLIENT_NAME}"

ports {
    http = 4646
    rpc  = 4647
    serf = 4648
}

consul {
    address             = "127.0.0.1:8500"
    server_service_name = "nomad"
    client_service_name = "nomad-${CLIENT_NAME}"
    auto_advertise      = true
    server_auto_join    = true
    client_auto_join    = true
}

vault {
  enabled          = true
  address          = "http://vault-main.service.${REGION}.consul:8200"
}

client {
    enabled       = true
    network_speed = 100
    options {
        "driver.raw_exec.enable"    = "1"
        "docker.auth.config"        = "/etc/docker/config.json"
        "docker.auth.helper"        = "ecr-login"
        "docker.privileged.enabled" = "true"
    }
    servers = ["nomad-server.service.${REGION}.consul:4647"]
}
EOF

# Set Nomad up as a systemd service
echo "Installing systemd service for Nomad..."
sudo bash -c "cat >/etc/systemd/system/nomad.service" << 'EOF'
[Unit]
Description=Hashicorp Nomad
After=network.target

[Service]
Type=simple
User=root
WorkingDirectory=/root
ExecStart=/usr/local/bin/nomad agent -config=/etc/nomad.d/nomad.hcl
Restart=on-failure # or always, on-abort, etc

[Install]
WantedBy=multi-user.target
EOF

sudo systemctl enable nomad
sudo systemctl start nomad

cd /root
go get -u github.com/awslabs/amazon-ecr-credential-helper/ecr-login/cli/docker-credential-ecr-login
mv /root/go/bin/docker-credential-ecr-login /usr/local/bin

echo "Nomad installation complete."
