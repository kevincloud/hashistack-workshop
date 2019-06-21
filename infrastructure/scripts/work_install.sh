#!/bin/bash

echo 'libc6 libraries/restart-without-asking boolean true' | sudo debconf-set-selections
export DEBIAN_FRONTEND=noninteractive
apt-get update > /dev/null 2>&1
apt-get -y upgrade > /dev/null 2>&1
apt-get -y install unzip git jq python3 python3-pip docker.io python3-dev default-libmysqlclient-dev npm > /dev/null 2>&1

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
pip3 install mysqlclient
pip3 install awscli

echo "Installing Consul..."
mkdir /etc/consul.d
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

cd /root
git clone https://${GIT_USER}:${GIT_TOKEN}@github.com/kevincloud/hashistack-workshop.git
cd /root/hashistack-workshop/apis

# load product data
python3 ./scripts/product_load.py

# Upload images to S3
aws s3 cp /root/hashistack-workshop/apis/productapi/images/ s3://${S3_BUCKET}/ --recursive --acl public-read

# create product-app image
cd ./productapi
docker build -t product-app:product-app .
aws ecr get-login --region ${AWS_REGION} --no-include-email > login.sh
chmod a+x login.sh
./login.sh
docker tag product-app:product-app ${REPO_URL_PROD}:product-app
docker push ${REPO_URL_PROD}:product-app

# create cart-app image
cd /root/hashistack-workshop/apis/cartapi
docker build -t cart-app:cart-app .
aws ecr get-login --region ${AWS_REGION} --no-include-email > login.sh
chmod a+x login.sh
./login.sh
docker tag cart-app:cart-app ${REPO_URL_CART}:cart-app
docker push ${REPO_URL_CART}:cart-app

# create online-site image
cd /root/hashistack-workshop/site
sudo bash -c "cat >/root/hashistack-workshop/site/site/framework/config.php" <<EOF
<?php
\$productapiurl = "http://${CONSUL_IP}:8500/v1/catalog/service/product-api";
\$cartapiurl = "http://${CONSUL_IP}:8500/v1/catalog/service/cart-api";
\$assetbucket = "https://s3.amazonaws.com/${S3_BUCKET}/"
?>
EOF

docker build -t online-store:online-store .
aws ecr get-login --region ${AWS_REGION} --no-include-email > login.sh
chmod a+x login.sh
./login.sh
docker tag online-store:online-store ${REPO_URL_SITE}:online-store
docker push ${REPO_URL_SITE}:online-store

curl \
    http://nomad-server.service.dc1.consul:4646/v1/jobs \
    --request POST \
    --data @- <<PAYLOAD
{
    "Job": {
        "ID": "product-api-job",
        "Name": "product-api",
        "Type": "service",
        "Datacenters": ["dc1"],
        "TaskGroups": [{
            "Name": "product-api-group",
            "Tasks": [{
                "Name": "product-api",
                "Driver": "docker",
                "Vault": {
                    "Policies": ["access-creds"]
                },
                "Config": {
                    "image": "https://${REPO_URL_PROD}:product-app",
                    "port_map": [{
                        "http": 5821
                    }]
                },
                "Templates": [{
                    "EmbeddedTmpl": "{{with secret \"secret/data/aws\"}}\nAWS_ACCESS_KEY = \"{{.Data.data.aws_access_key}}\"\nAWS_SECRET_KEY = \"{{.Data.data.aws_secret_key}}\"\n{{end}}\nAWS_REGION = \"${AWS_REGION}\"\n                ",
                    "DestPath": "secrets/file.env",
                    "Envvars": true
                }],
                "Resources": {
                    "Networks": [{
                        "ReservedPorts": [
                            {
                                "Label": "http",
                                "Value": 5821
                            }
                        ]
                    }]
                },
                "Services": [{
                    "Name": "product-api",
                    "PortLabel": "http"
                }]
            }]
        }]
    }
}
PAYLOAD

curl \
    http://nomad-server.service.dc1.consul:4646/v1/jobs \
    --request POST \
    --data @- <<PAYLOAD
{
    "Job": {
        "ID": "cart-api-job",
        "Name": "cart-api",
        "Type": "service",
        "Datacenters": ["dc1"],
        "TaskGroups": [{
            "Name": "cart-api-group",
            "Tasks": [{
                "Name": "cart-api",
                "Driver": "docker",
                "Vault": {
                    "Policies": ["access-creds"]
                },
                "Config": {
                    "image": "https://${REPO_URL_CART}:cart-app",
                    "port_map": [{
                        "http": 5823
                    }]
                },
                "Templates": [{
                    "EmbeddedTmpl": "{{with secret \"secret/data/aws\"}}\nAWS_ACCESS_KEY_ID = \"{{.Data.data.aws_access_key}}\"\nAWS_SECRET_ACCESS_KEY = \"{{.Data.data.aws_secret_key}}\"\n{{end}}\nAWS_REGION = \"${AWS_REGION}\"\n                ",
                    "DestPath": "secrets/file.env",
                    "Envvars": true
                }],
                "Resources": {
                    "Networks": [{
                        "ReservedPorts": [
                            {
                                "Label": "http",
                                "Value": 5823
                            }
                        ]
                    }]
                },
                "Services": [{
                    "Name": "cart-api",
                    "PortLabel": "http"
                }]
            }]
        }]
    }
}
PAYLOAD

curl \
    http://nomad-server.service.dc1.consul:4646/v1/jobs \
    --request POST \
    --data @- <<PAYLOAD
{
    "Job": {
        "ID": "online-store-job",
        "Name": "online-store",
        "Type": "service",
        "Datacenters": ["dc1"],
        "TaskGroups": [{
            "Name": "online-store-group",
            "Tasks": [{
                "Name": "online-store",
                "Driver": "docker",
                "Vault": {
                    "Policies": ["access-creds"]
                },
                "Config": {
                    "image": "https://${REPO_URL_SITE}:online-store",
                    "port_map": [{
                        "http": 80
                    }]
                },
                "Templates": [{
                    "EmbeddedTmpl": "{{with secret \"secret/data/aws\"}}\nAWS_ACCESS_KEY = \"{{.Data.data.aws_access_key}}\"\nAWS_SECRET_KEY = \"{{.Data.data.aws_secret_key}}\"\n{{end}}\nAWS_REGION = \"${AWS_REGION}\"\n                ",
                    "DestPath": "secrets/file.env",
                    "Envvars": true
                }],
                "Resources": {
                    "Networks": [{
                        "ReservedPorts": [
                           {
                                "Label": "http",
                                "Value": 80
                            }
                        ]
                    }]
                },
                "Services": [{
                    "Name": "online-store",
                    "PortLabel": "http"
                }]
            }]
        }]
    }
}
PAYLOAD


