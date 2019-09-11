#!/bin/sh

echo "Preparing to install ..."

echo 'libc6 libraries/restart-without-asking boolean true' | sudo debconf-set-selections
export DEBIAN_FRONTEND=noninteractive
echo "...installing Ubuntu updates"
sudo apt-get -y update > /dev/null 2>&1
sudo apt-get -y upgrade > /dev/null 2>&1
echo "...installing system packages"
sudo apt-get install -y unzip git jq python3 python3-pip python3-dev dnsmasq mysql-client default-libmysqlclient-dev npm docker.io golang openjdk-8-jre openjdk-8-jdk maven mysql-client-core-5.7 > /dev/null 2>&1

echo "...installing python libraries"
pip3 install botocore
pip3 install boto3
pip3 install mysqlclient
pip3 install awscli
pip3 install hvac

echo "...creating directories"
mkdir -p /root/.aws
mkdir -p /root/go
mkdir -p /etc/vault.d
mkdir -p /etc/consul.d
mkdir -p /etc/nomad.d
mkdir -p /etc/docker
mkdir -p /opt/vault
mkdir -p /opt/consul
mkdir -p /opt/nomad
mkdir -p /opt/nomad/plugins

echo "...setting AWS credentials"
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

echo "...setting environment variables"
export MYSQL_HOST="${MYSQL_HOST}"
export MYSQL_USER="${MYSQL_USER}"
export MYSQL_PASS="${MYSQL_PASS}"
export MYSQL_DB="${MYSQL_DB}"
export AWS_ACCESS_KEY="${AWS_ACCESS_KEY}"
export AWS_SECRET_KEY="${AWS_SECRET_KEY}"
export AWS_KMS_KEY_ID="${AWS_KMS_KEY_ID}"
export REGION="${REGION}"
export REPO_URL_PROD="${REPO_URL_PROD}"
export REPO_URL_CART="${REPO_URL_CART}"
export REPO_URL_ACCT="${REPO_URL_ACCT}"
export REPO_URL_SITE="${REPO_URL_SITE}"
export REPO_URL_ORDR="${REPO_URL_ORDR}"
export S3_BUCKET="${S3_BUCKET}"
export GIT_USER="${GIT_USER}"
export GIT_TOKEN="${GIT_TOKEN}"
export VAULT_URL="${VAULT_URL}"
export VAULT_LICENSE="${VAULT_LICENSE}"
export CONSUL_URL="${CONSUL_URL}"
export CONSUL_LICENSE="${CONSUL_LICENSE}"
export CONSUL_JOIN_KEY="${CONSUL_JOIN_KEY}"
export CONSUL_JOIN_VALUE="${CONSUL_JOIN_VALUE}"
export NOMAD_URL="${NOMAD_URL}"
export GOPATH=/root/go
export GOCACHE=/root/go/.cache

export CLIENT_IP=`curl http://169.254.169.254/latest/meta-data/local-ipv4`

echo $CLIENT_IP $(echo "ip-$CLIENT_IP" | sed "s/\./-/g") >> /etc/hosts

echo "...cloning repo"
cd /root
git clone https://${GIT_USER}:${GIT_TOKEN}@github.com/kevincloud/hashistack-workshop.git

chmod +x /root/hashistack-workshop/infrastructure/aws-mini/scripts/consul_install.sh
chmod +x /root/hashistack-workshop/infrastructure/aws-mini/scripts/vault_install.sh
chmod +x /root/hashistack-workshop/infrastructure/aws-mini/scripts/nomad_install.sh
chmod +x /root/hashistack-workshop/infrastructure/aws-mini/scripts/build.sh

echo "Preparation done."
/root/hashistack-workshop/infrastructure/aws-mini/scripts/consul_install.sh
/root/hashistack-workshop/infrastructure/aws-mini/scripts/vault_install.sh
/root/hashistack-workshop/infrastructure/aws-mini/scripts/nomad_install.sh
/root/hashistack-workshop/infrastructure/aws-mini/scripts/build.sh

