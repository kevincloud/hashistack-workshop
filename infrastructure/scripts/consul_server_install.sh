#!/bin/sh
# Configures the Consul server

echo "Preparing to install Consul..."
echo 'libc6 libraries/restart-without-asking boolean true' | sudo debconf-set-selections
export DEBIAN_FRONTEND=noninteractive
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
wget ${CONSUL_URL}
sudo unzip $(basename ${CONSUL_URL}) -d /usr/local/bin/

# Server configuration
sudo bash -c "cat >/etc/consul.d/consul-server.json" <<EOF
{
    "data_dir": "/opt/consul",
    "datacenter": "${REGION}",
    "node_name": "${CONSUL_SERVER_NAME}",
    "client_addr": "0.0.0.0",
    "bind_addr": "0.0.0.0",
    "domain": "consul",
    "acl_enforce_version_8": false,
    "server": true,
    "bootstrap_expect": 3,
    "retry_join": ["provider=aws tag_key=${CONSUL_JOIN_KEY} tag_value=${CONSUL_JOIN_VALUE}"],
    "ui": true,
    "recursors": ["169.254.169.253"]
}
EOF

# Set Consul up as a systemd service
echo "Installing systemd service for Consul..."
sudo bash -c "cat >/etc/systemd/system/consul.service" << 'EOF'
[Unit]
Description=Hashicorp Consul
Requires=network-online.target
After=network-online.target

[Service]
User=root
Group=root
PIDFile=/var/run/consul/consul.pid
PermissionsStartOnly=true
ExecStartPre=-/bin/mkdir -p /var/run/consul
ExecStart=/usr/local/bin/consul agent -config-dir=/etc/consul.d -pid-file=/var/run/consul/consul.pid
ExecReload=/bin/kill -HUP $MAINPID
KillMode=process
KillSignal=SIGTERM
Restart=on-failure
RestartSec=42s

[Install]
WantedBy=multi-user.target
EOF

sudo systemctl start consul
sudo systemctl enable consul

echo "Waiting for Consul leader"
while [ -z "$(curl -s http://127.0.0.1:8500/v1/status/leader)" ]; do
  sleep 3
done

echo "--> apply Consul License"
sudo consul license put "${CONSUL_LICENSE}" > /root/license.txt

echo "Consul installation complete."
