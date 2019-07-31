#!/bin/bash

echo 'libc6 libraries/restart-without-asking boolean true' | sudo debconf-set-selections
export DEBIAN_FRONTEND=noninteractive
apt-get update > /dev/null 2>&1
apt-get -y upgrade > /dev/null 2>&1
apt-get -y install unzip git jq python3 python3-pip docker.io golang-go python3-dev default-libmysqlclient-dev npm openjdk-8-jdk maven > /dev/null 2>&1

# create a sudo user
#useradd -m builder
#echo 'builder:test5678' | chpasswd
#usermod -aG sudo builder
# echo 'root:$--{ROOT_PASSWORD}--' | chpasswd
# sed -i.bak 's/^\(PasswordAuthentication \).*/\1yes/' /etc/ssh/sshd_config
# sed -i.bak 's/#PermitRootLogin prohibit-password/PermitRootLogin yes/' /etc/ssh/sshd_config
# service ssh restart

mkdir -p /root/.aws
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

pip3 install botocore
pip3 install boto3
pip3 install mysqlclient
pip3 install awscli

echo "Installing Consul..."
mkdir /etc/consul.d
export CLIENT_IP=`curl http://169.254.169.254/latest/meta-data/local-ipv4`
wget https://releases.hashicorp.com/consul/1.5.1/consul_1.5.1_linux_amd64.zip
sudo unzip consul_1.5.1_linux_amd64.zip -d /usr/local/bin/

# Server configuration
sudo bash -c "cat >/etc/consul.d/consul.json" <<EOF
{
    "datacenter": "${REGION}",
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

sleep 3
export VAULT_TOKEN=$(consul kv get service/vault/root-token)

cd /root
git clone https://${GIT_USER}:${GIT_TOKEN}@github.com/kevincloud/hashistack-workshop.git
sudo bash -c "cat >>/root/hashistack-workshop/.git/config" <<EOF
[submodule "apis/account-broker"]
        url = https://${GIT_USER}:${GIT_TOKEN}@github.com/joshuaNjordan85/account-broker.git
        active = true
[submodule "apis/minion"]
        url = https://${GIT_USER}:${GIT_TOKEN}@github.com/joshuaNjordan85/minion.git
        active = true
EOF
cd /root/hashistack-workshop
git submodule update

export REGION="${REGION}"
export S3_BUCKET="${S3_BUCKET}"
export REPO_URL_PROD="${REPO_URL_PROD}"
export REPO_URL_CART="${REPO_URL_CART}"
export REPO_URL_ACCT="${REPO_URL_ACCT}"
export REPO_URL_SITE="${REPO_URL_SITE}"
export CONSUL_IP="${CONSUL_IP}"
export VAULT_IP="${VAULT_IP}"
export VAULT_TOKEN="${VAULT_TOKEN}"

chmod a+x /root/hashistack-workshop/infrastructure/scripts/build.sh
/root/hashistack-workshop/infrastructure/scripts/build.sh
