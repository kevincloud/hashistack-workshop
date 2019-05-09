#!/bin/bash

apt-get update > /dev/null 2>&1
apt-get -y upgrade > /dev/null 2>&1
apt-get -y install git jq python3 python3-pip docker.io > /dev/null 2>&1

# create a sudo user
#useradd -m builder
#echo 'builder:test5678' | chpasswd
#usermod -aG sudo builder
echo 'root:${ROOT_PASSWORD}' | chpasswd
sed -i.bak 's/^\(PasswordAuthentication \).*/\1yes/' /etc/ssh/sshd_config
sed -i.bak 's/#PermitRootLogin prohibit-password/PermitRootLogin yes/' /etc/ssh/sshd_config
service ssh restart

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
region=${AWS_REGION}
EOF

pip3 install botocore
pip3 install boto3
pip3 install mysql-connector-python
pip3 install awscli

cd /root
git clone https://${GIT_USER}:${GIT_TOKEN}@github.com/kevincloud/hashistack-workshop.git
cd /root/hashistack-workshop/apis

#sudo aws s3 cp /var/www/html/online-store/productapi/images/ s3://${S3_BUCKET}/ --acl public-read

# load product data
python3 ./scripts/product-load.py

# create customer-app image
cd ./productapi
docker build -t product-app:product-app .
aws ecr get-login --region ${AWS_REGION} --no-include-email > login.sh
chmod a+x login.sh
./login.sh
docker tag product-app:product-app ${REPO_URL}:product-app
docker push ${REPO_URL}:product-app
