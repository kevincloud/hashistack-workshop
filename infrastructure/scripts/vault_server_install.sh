#!/bin/sh
# Configures the Vault server for a database secrets demo

# cd /tmp
echo "Preparing to install Vault..."
sudo apt-get -y update > /dev/null 2>&1
sudo apt-get -y upgrade > /dev/null 2>&1
sudo apt install -y unzip jq cowsay mysql-client > /dev/null 2>&1

mkdir -p /etc/vault.d
mkdir -p /etc/consul.d

echo "Installing Vault..."
wget https://releases.hashicorp.com/vault/1.1.3/vault_1.1.3_linux_amd64.zip
sudo unzip vault_1.1.3_linux_amd64.zip -d /usr/local/bin/

# Set Vault up as a systemd service
echo "Installing systemd service for Vault..."
sudo bash -c "cat >/etc/systemd/system/vault.service" << 'EOF'
[Unit]
Description=Hashicorp Vault
After=network.target

[Service]
Type=simple
User=root
WorkingDirectory=/root
ExecStart=/usr/local/bin/vault server -dev -dev-root-token-id=root -dev-listen-address=0.0.0.0:8200
Restart=on-failure # or always, on-abort, etc

[Install]
WantedBy=multi-user.target
EOF

sudo systemctl start vault
sudo systemctl enable vault

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
    "node_name": "consul-vault",
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

echo "Setting up environment variables..."
export VAULT_ADDR=http://localhost:8200
export VAULT_TOKEN=root
echo "export VAULT_ADDR=http://localhost:8200" >> /home/ubuntu/.profile
echo "export VAULT_TOKEN=root" >> /home/ubuntu/.profile
echo "export MYSQL_HOST=${MYSQL_HOST}" >> /home/ubuntu/.profile
echo "export VAULT_ADDR=http://localhost:8200" >> /root/.profile
echo "export VAULT_TOKEN=root" >> /root/.profile
echo "export MYSQL_HOST=${MYSQL_HOST}" >> /root/.profile

sudo bash -c "cat >/etc/vault.d/nomad-policy.json" <<EOF
{
    "policy": "# Allow creating tokens under \"nomad-cluster\" token role. The token role name\n# should be updated if \"nomad-cluster\" is not used.\npath \"auth/token/create/nomad-cluster\" {\n  capabilities = [\"update\"]\n}\n\n# Allow looking up \"nomad-cluster\" token role. The token role name should be\n# updated if \"nomad-cluster\" is not used.\npath \"auth/token/roles/nomad-cluster\" {\n  capabilities = [\"read\"]\n}\n\n# Allow looking up the token passed to Nomad to validate # the token has the\n# proper capabilities. This is provided by the \"default\" policy.\npath \"auth/token/lookup-self\" {\n  capabilities = [\"read\"]\n}\n\n# Allow looking up incoming tokens to validate they have permissions to access\n# the tokens they are requesting. This is only required if\n# `allow_unauthenticated` is set to false.\npath \"auth/token/lookup\" {\n  capabilities = [\"update\"]\n}\n\n# Allow revoking tokens that should no longer exist. This allows revoking\n# tokens for dead tasks.\npath \"auth/token/revoke-accessor\" {\n  capabilities = [\"update\"]\n}\n\n# Allow checking the capabilities of our own token. This is used to validate the\n# token upon startup.\npath \"sys/capabilities-self\" {\n  capabilities = [\"update\"]\n}\n\n# Allow our own token to be renewed.\npath \"auth/token/renew-self\" {\n  capabilities = [\"update\"]\n}\n"
}
EOF

sudo bash -c "cat >/etc/vault.d/access-creds.json" <<EOF
{
    "policy": "path \"secret/data/aws\" {\n  capabilities = [\"read\", \"list\"]\n}\n"
}
EOF

sudo bash -c "cat >/etc/vault.d/nomad-cluster-role.json" <<EOF
{
  "disallowed_policies": "nomad-server",
  "explicit_max_ttl": 0,
  "name": "nomad-cluster",
  "orphan": true,
  "period": 259200,
  "renewable": true
}
EOF

echo "Configuring Vault..."
curl \
    --header "X-Vault-Token: $VAULT_TOKEN" \
    --request POST \
    --data '{"data": { "aws_access_key": "${AWS_ACCESS_KEY}", "aws_secret_key": "${AWS_SECRET_KEY}" } }' \
    http://127.0.0.1:8200/v1/secret/data/aws


curl \
    --header "X-Vault-Token: $VAULT_TOKEN" \
    --request PUT \
    --data @/etc/vault.d/nomad-policy.json \
    http://127.0.0.1:8200/v1/sys/policy/nomad-server

curl \
    --header "X-Vault-Token: $VAULT_TOKEN" \
    --request PUT \
    --data @/etc/vault.d/access-creds.json \
    http://127.0.0.1:8200/v1/sys/policy/access-creds

curl \
    --header "X-Vault-Token: $VAULT_TOKEN" \
    --request POST \
    --data @/etc/vault.d/nomad-cluster-role.json \
    http://127.0.0.1:8200/v1/auth/token/roles/nomad-cluster

echo "Register with Consul"
curl \
    http://127.0.0.1:8500/v1/agent/service/register \
    --request PUT \
    --data @- <<PAYLOAD
{
    "ID": "vault-main",
    "Name": "vault-main",
    "Port": 8200
}
PAYLOAD

echo "Vault installation complete."

