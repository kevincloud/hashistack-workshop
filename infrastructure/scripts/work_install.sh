#!/bin/bash

echo 'libc6 libraries/restart-without-asking boolean true' | sudo debconf-set-selections
export DEBIAN_FRONTEND=noninteractive
apt-get update > /dev/null 2>&1
apt-get -y upgrade > /dev/null 2>&1
apt-get -y install unzip git jq python3 python3-pip docker.io dnsmasq python3-dev default-libmysqlclient-dev npm openjdk-8-jdk maven mysql-client-core-5.7 > /dev/null 2>&1

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
pip3 install hvac

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


sleep 3

cd /root
git clone https://${GIT_USER}:${GIT_TOKEN}@github.com/kevincloud/hashistack-workshop.git
cd /root/hashistack-workshop

export REGION="${REGION}"
export S3_BUCKET="${S3_BUCKET}"
export REPO_URL_PROD="${REPO_URL_PROD}"
export REPO_URL_CART="${REPO_URL_CART}"
export REPO_URL_ACCT="${REPO_URL_ACCT}"
export REPO_URL_SITE="${REPO_URL_SITE}"
export REPO_URL_ORDR="${REPO_URL_ORDR}"
export CONSUL_IP="${CONSUL_IP}"
export VAULT_TOKEN=$(consul kv get service/vault/root-token)
export MYSQL_HOST="${MYSQL_HOST}"

chmod a+x /root/hashistack-workshop/infrastructure/scripts/build.sh
/root/hashistack-workshop/infrastructure/scripts/build.sh
