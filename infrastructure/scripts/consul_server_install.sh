#!/bin/sh
# Configures the Consul server

echo "Preparing to install Consul..."
sudo apt-get -y update > /dev/null 2>&1
sudo apt-get -y upgrade > /dev/null 2>&1
sudo apt-get install -y unzip jq python3 python3-pip > /dev/null 2>&1
pip3 install awscli

mkdir /etc/consul.d
mkdir -p /opt/consul
# mkdir -p /opt/consul/plugins
mkdir -p /root/.aws

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
wget https://releases.hashicorp.com/consul/1.4.4/consul_1.4.4_linux_amd64.zip
sudo unzip consul_1.4.4_linux_amd64.zip -d /usr/local/bin/

# Server configuration
sudo bash -c "cat >/etc/consul.d/consul-server.json" <<EOF
{
    "data_dir": "/opt/consul",
    "datacenter": "dc1",
    "node_name": "consul-server",
    "client_addr": "0.0.0.0",
    "domain": "consul",
    "server": true,
    "bootstrap_expect": 1,
    "ui": true
}
EOF

# Set Consul up as a systemd service
echo "Installing systemd service for Consul..."
sudo bash -c "cat >/etc/systemd/system/consul.service" << 'EOF'
[Unit]
Description=Hashicorp Consul
After=network.target

[Service]
Type=simple
User=root
WorkingDirectory=/root
ExecStart=/usr/local/bin/consul agent -config-dir=/etc/consul.d
Restart=on-failure # or always, on-abort, etc

[Install]
WantedBy=multi-user.target
EOF

sudo systemctl start consul
sudo systemctl enable consul

echo "Consul installation complete."
