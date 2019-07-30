#!/bin/sh
# Configures the Vault server for a database secrets demo

# cd /tmp
echo "Preparing to install Vault..."
echo 'libc6 libraries/restart-without-asking boolean true' | sudo debconf-set-selections
export DEBIAN_FRONTEND=noninteractive
sudo apt-get -y update > /dev/null 2>&1
sudo apt-get -y upgrade > /dev/null 2>&1
sudo apt-get install -y unzip jq cowsay mysql-client > /dev/null 2>&1

mkdir -p /etc/vault.d
mkdir -p /etc/consul.d
mkdir -p /opt/vault

echo "Installing Consul..."
export CLIENT_IP=`curl http://169.254.169.254/latest/meta-data/local-ipv4`
curl -sfLo "consul.zip" "${CONSUL_URL}"
sudo unzip consul.zip -d /usr/local/bin/

# Server configuration
sudo bash -c "cat >/etc/consul.d/consul.json" <<EOF
{
    "datacenter": "${REGION}",
    "bind_addr": "$CLIENT_IP",
    "data_dir": "/opt/consul",
    "node_name": "consul-vault",
    "retry_join": ["provider=aws tag_key=${CONSUL_JOIN_KEY} tag_value=${CONSUL_JOIN_VALUE}"],
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

echo "Installing Vault..."
curl -sfLo "vault.zip" "${VAULT_URL}"
sudo unzip vault.zip -d /usr/local/bin/

# Server configuration
sudo bash -c "cat >/etc/vault.d/vault.hcl" << 'EOF'
storage "file" {
  path = "/opt/vault"
}

listener "tcp" {
  address     = "0.0.0.0:8200"
  tls_disable = 1
}

seal "awskms" {
    region = "${REGION}"
    kms_key_id = "${AWS_KMS_KEY_ID}"
}

ui = true
EOF

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
ExecStart=/usr/local/bin/vault server -config=/etc/vault.d/vault.hcl
Restart=on-failure # or always, on-abort, etc

[Install]
WantedBy=multi-user.target
EOF

sudo systemctl start vault
sudo systemctl enable vault

echo "Initializing and setting up environment variables..."
export VAULT_ADDR=http://localhost:8200

vault operator init -recovery-shares=1 -recovery-threshold=1 -key-shares=1 -key-threshold=1 > /root/init.txt 2>&1

export VAULT_TOKEN=`cat /root/init.txt | sed -n -e '/^Initial Root Token/ s/.*\: *//p'`
consul kv put service/vault/root-token $VAULT_TOKEN
export RECOVERY_KEY=`cat /root/init.txt | sed -n -e '/^Recovery Key 1/ s/.*\: *//p'`
consul kv put service/vault/recovery-key $RECOVERY_KEY

echo "export VAULT_ADDR=http://localhost:8200" >> /home/ubuntu/.profile
echo "export VAULT_TOKEN=$(consul kv get service/vault/root-token)" >> /home/ubuntu/.profile
echo "export MYSQL_HOST=${MYSQL_HOST}" >> /home/ubuntu/.profile
echo "export VAULT_ADDR=http://localhost:8200" >> /root/.profile
echo "export VAULT_TOKEN=$(consul kv get service/vault/root-token)" >> /root/.profile
echo "export MYSQL_HOST=${MYSQL_HOST}" >> /root/.profile

vault write sys/license text=${VAULT_LICENSE}

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

# vault secrets enable -path=usercreds -version=2 kv

# Enable secrets mount point for kv2
curl \
    --header "X-Vault-Token: $VAULT_TOKEN" \
    --request POST \
    --data '{"type": "kv", "options": { "version": "2" } }' \
    http://127.0.0.1:8200/v1/sys/mounts/usercreds

# add usernames and passwords

curl \
    --header "X-Vault-Token: $VAULT_TOKEN" \
    --request POST \
    --data '{"data": { "username": "jthomp4423@example.com", "password": "SuperSecret1", "customerno": "CS100312" } }' \
    http://127.0.0.1:8200/v1/usercreds/data/jthomp4423@example.com

curl \
    --header "X-Vault-Token: $VAULT_TOKEN" \
    --request POST \
    --data '{"data": { "username": "wilson@example.com", "password": "SuperSecret1", "customerno": "CS106004" } }' \
    http://127.0.0.1:8200/v1/usercreds/data/wilson@example.com

curl \
    --header "X-Vault-Token: $VAULT_TOKEN" \
    --request POST \
    --data '{"data": { "username": "tommy6677@example.com", "password": "SuperSecret1", "customerno": "CS101438" } }' \
    http://127.0.0.1:8200/v1/usercreds/data/tommy6677@example.com

curl \
    --header "X-Vault-Token: $VAULT_TOKEN" \
    --request POST \
    --data '{"data": { "username": "mmccann1212@example.com", "password": "SuperSecret1", "customerno": "CS210895" } }' \
    http://127.0.0.1:8200/v1/usercreds/data/mmccann1212@example.com

curl \
    --header "X-Vault-Token: $VAULT_TOKEN" \
    --request POST \
    --data '{"data": { "username": "cjpcomp@example.com", "password": "SuperSecret1", "customerno": "CS122955" } }' \
    http://127.0.0.1:8200/v1/usercreds/data/cjpcomp@example.com

curl \
    --header "X-Vault-Token: $VAULT_TOKEN" \
    --request POST \
    --data '{"data": { "username": "jjhome7823@example.com", "password": "SuperSecret1", "customerno": "CS602934" } }' \
    http://127.0.0.1:8200/v1/usercreds/data/jjhome7823@example.com

curl \
    --header "X-Vault-Token: $VAULT_TOKEN" \
    --request POST \
    --data '{"data": { "username": "clint.mason312@example.com", "password": "SuperSecret1", "customerno": "CS157843" } }' \
    http://127.0.0.1:8200/v1/usercreds/data/clint.mason312@example.com

curl \
    --header "X-Vault-Token: $VAULT_TOKEN" \
    --request POST \
    --data '{"data": { "username": "greystone89@example.com", "password": "SuperSecret1", "customerno": "CS523484" } }' \
    http://127.0.0.1:8200/v1/usercreds/data/greystone89@example.com

curl \
    --header "X-Vault-Token: $VAULT_TOKEN" \
    --request POST \
    --data '{"data": { "username": "runwayyourway@example.com", "password": "SuperSecret1", "customerno": "CS658871" } }' \
    http://127.0.0.1:8200/v1/usercreds/data/runwayyourway@example.com

curl \
    --header "X-Vault-Token: $VAULT_TOKEN" \
    --request POST \
    --data '{"data": { "username": "olsendog1979@example.com", "password": "SuperSecret1", "customerno": "CS103393" } }' \
    http://127.0.0.1:8200/v1/usercreds/data/olsendog1979@example.com

# Additional configs
curl \
    --header "X-Vault-Token: $VAULT_TOKEN" \
    --request POST \
    --data '{"data": { "aws_access_key": "${AWS_ACCESS_KEY}", "aws_secret_key": "${AWS_SECRET_KEY}" } }' \
    http://127.0.0.1:8200/v1/secret/data/aws

curl \
    --header "X-Vault-Token: $VAULT_TOKEN" \
    --request POST \
    --data '{"data": { "address": "${MYSQL_HOST}", "database": "${MYSQL_DB}", "username": "${MYSQL_USER}", "password": "${MYSQL_PASS}" } }' \
    http://127.0.0.1:8200/v1/secret/data/dbhost


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

