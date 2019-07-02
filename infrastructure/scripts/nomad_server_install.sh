#!/bin/sh
# Configures the Nomad server

echo "Updating distro repos..."
echo 'libc6 libraries/restart-without-asking boolean true' | sudo debconf-set-selections
export DEBIAN_FRONTEND=noninteractive
sudo apt-get -y update > /dev/null 2>&1
echo "Installing system updates..."
sudo apt-get -y upgrade > /dev/null 2>&1
echo "Installing additional packages..."
sudo apt-get install -y unzip jq python3 python3-pip > /dev/null 2>&1
echo "Installing Python packages..."
pip3 install awscli

echo "Creating directories..."
mkdir /etc/nomad.d
mkdir -p /opt/nomad
mkdir -p /opt/nomad/plugins
mkdir -p /etc/consul.d
mkdir -p /opt/consul
mkdir -p /root/.aws

echo "Adding AWS credentials..."
sudo bash -c "cat >/root/.aws/config" << 'EOF'
[default]
aws_access_key_id=${AWS_ACCESS_KEY}
aws_secret_access_key=${AWS_SECRET_KEY}
EOF
sudo bash -c "cat >/root/.aws/credentials" << 'EOF'
[default]
aws_access_key_id=${AWS_ACCESS_KEY}
aws_secret_access_key=${AWS_SECRET_KEY}
EOF

echo "Installing Consul..."
export CLIENT_IP=`curl http://169.254.169.254/latest/meta-data/local-ipv4`
wget https://releases.hashicorp.com/consul/1.5.1/consul_1.5.1_linux_amd64.zip
sudo unzip consul_1.5.1_linux_amd64.zip -d /usr/local/bin/

# Server configuration
sudo bash -c "cat >/etc/consul.d/consul.json" <<EOF
{
    "bootstrap": false,
    "datacenter": "dc1",
    "bind_addr": "$CLIENT_IP",
    "data_dir": "/opt/consul",
    "node_name": "consul-nomad-server",
    "retry_join": ["${CONSUL_IP}"],
    "server": false,
    "ui": true
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
    http://vault-main.service.dc1.consul:8200/v1/auth/token/create | jq . > /etc/nomad.d/token.json

export CLIENT_TOKEN="$(cat /etc/nomad.d/token.json | jq -r .auth.client_token | tr -d '\n')"

sudo bash -c "cat >/etc/nomad.d/nomad.hcl" <<EOF
data_dir  = "/opt/nomad"
plugin_dir = "/opt/nomad/plugins"
bind_addr = "0.0.0.0"

ports {
    http = 4646
    rpc  = 4647
    serf = 4648
}

consul {
    address             = "127.0.0.1:8500"
    server_service_name = "nomad-client"
    client_service_name = "nomad-client"
    auto_advertise      = true
    server_auto_join    = true
    client_auto_join    = true
}

vault {
  enabled          = true
  address          = "http://vault-main.service.dc1.consul:8200"
  task_token_ttl   = "1h"
  create_from_role = "nomad-cluster"
  token            = "$CLIENT_TOKEN"
}

server {
    enabled          = true
    bootstrap_expect = 1
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

sudo systemctl start nomad
sudo systemctl enable nomad

curl \
    http://127.0.0.1:8500/v1/agent/service/register \
    --request PUT \
    --data @- <<PAYLOAD
{
    "ID": "nomad-server",
    "Name": "nomad-server",
    "Port": 4647
}
PAYLOAD

echo "Nomad installation complete."
