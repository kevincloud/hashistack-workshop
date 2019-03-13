#!/bin/bash

apt update
apt -y upgrade
apt -y install jq python3 python3-pip docker.io
#pip3 install flask
#pip3 install flask-cors
pip3 install boto3

# create a sudo user
#useradd -m builder
#echo 'builder:test5678' | chpasswd
#usermod -aG sudo builder
echo 'root:${ROOT_PASSWORD}' | chpasswd
sed -i.bak 's/^\(PasswordAuthentication \).*/\1yes/' /etc/ssh/sshd_config
service ssh restart

mkdir -p /root/img
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

python3 /root/appcreds.py > /root/img/creds.json

cd /root/img
docker build -t customer-app:latest .

python3 /root/customer-load.py


---------------------
# export VAULT_ADDR='http://${VAULT_SERVER}:8200'
# export VAULT_TOKEN='root'

# #
# # configure vault
# #

# # enable approle authentication
# curl \
#     --header "X-Vault-Token: $VAULT_TOKEN" \
#     --request POST \
#     --data '{"type": "approle"}' \
#     $VAULT_SERVER/v1/sys/auth/approle

# # create a policy 
# curl \
#     --header "X-Vault-Token: $VAULT_TOKEN" \
#     --request POST \
#     --data '{"policy": "path \"secret/data/aws\" { capabilities = [\"read\", \"list\"] } path \"secret/cust-mgmt/*\" { capabilities = [\"create\",\"update\",\"read\",\"delete\"] }"}' \
#     $VAULT_SERVER/v1/sys/policy/dev-policy

# # create a role
# curl \
#     --header "X-Vault-Token: $VAULT_TOKEN" \
#     --request POST \
#     --data '{"policies": ["dev-policy"]}' \
#     $VAULT_SERVER/v1/auth/approle/role/dev-role

# # get the role id
# curl \
#     -s -q \
#     --header "X-Vault-Token: $VAULT_TOKEN" \
#     $VAULT_SERVER/v1/auth/approle/role/dev-role/role-id | jq .data.role_id

# # get the secret id
# curl \
#     -s -q \
#     --header "X-Vault-Token: $VAULT_TOKEN" \
#     --request POST \
#     $VAULT_SERVER/v1/auth/approle/role/dev-role/secret-id | jq .data.secret_id

# curl --request POST --data '{ "role_id": "...", "secret_id": "..." }' $VAULT_SERVER/v1/auth/approle/login | jq

# curl --header "X-Vault-Token: ..." --request GET http://54.196.140.114:8200/v1/secret/data/aws | jq

