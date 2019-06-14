#!/bin/sh
# Configures the Nomad server

echo "Preparing to install Nomad..."
sudo apt-get -y update > /dev/null 2>&1
sudo apt-get -y upgrade > /dev/null 2>&1
sudo apt-get install -y unzip jq python3 python3-pip docker.io golang > /dev/null 2>&1
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
region=${AWS_REGION}
EOF

sudo bash -c "cat >/etc/docker/config.json" <<EOF
{
	"credsStore": "ecr-login"
}
EOF

echo "Installing Consul..."
export CLIENT_IP=`ifconfig eth0 | grep "inet " | awk -F' ' '{print $2}'`
wget https://releases.hashicorp.com/consul/1.5.1/consul_1.5.1_linux_amd64.zip
sudo unzip consul_1.5.1_linux_amd64.zip -d /usr/local/bin/

# Server configuration
sudo bash -c "cat >/etc/consul.d/consul.json" <<EOF
{
    "datacenter": "dc1",
    "bind_addr": "$CLIENT_IP",
    "data_dir": "/opt/consul",
    "node_name": "consul-${CLIENT_NAME}",
    "retry_join": ["${CONSUL_IP}"],
    "server": false
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
sudo printf "DNS=127.0.0.1\nDomains=~consul" >> /etc/systemd/resolved.conf
sudo iptables -t nat -A OUTPUT -d localhost -p udp -m udp --dport 53 -j REDIRECT --to-ports 8600
sudo iptables -t nat -A OUTPUT -d localhost -p tcp -m tcp --dport 53 -j REDIRECT --to-ports 8600
sudo service systemd-resolved restart

echo "Installing Nomad..."
wget https://releases.hashicorp.com/nomad/0.9.3/nomad_0.9.3_linux_amd64.zip
sudo unzip nomad_0.9.3_linux_amd64.zip -d /usr/local/bin/

# Server configuration
export VAULT_ADDR=http://vault-main.service.dc1.consul:8200
export VAULT_TOKEN=root

echo "Setting up environment variables..."
echo "export VAULT_ADDR=http://vault-main.service.dc1.consul:8200" >> /home/ubuntu/.profile
echo "export VAULT_TOKEN=root" >> /home/ubuntu/.profile
echo "export VAULT_ADDR=http://vault-main.service.dc1.consul:8200" >> /root/.profile
echo "export VAULT_TOKEN=root" >> /root/.profile
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
  address          = "http://vault-main.service.dc1.consul:8200"
}

client {
    enabled       = true
    network_speed = 10
    options {
        "driver.raw_exec.enable" = "1"
        "docker.auth.config"     = "/etc/docker/config.json"
        "docker.auth.helper"     = "ecr-login"
    }
    servers = ["nomad-server.service.dc1.consul:4647"]
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
