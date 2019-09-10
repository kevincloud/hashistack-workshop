#!/bin/sh
# Configures the Consul server

echo "Installing Consul..."
curl -sfLo "consul.zip" "${CONSUL_URL}"
sudo unzip consul.zip -d /usr/local/bin/

# Server configuration
sudo bash -c "cat >/etc/consul.d/consul-server.json" <<EOF
{
    "data_dir": "/opt/consul",
    "datacenter": "${REGION}",
    "node_name": "consult-server",
    "client_addr": "${CLIENT_IP}",
    "bind_addr": "${CLIENT_IP}",
    "domain": "consul",
    "acl_enforce_version_8": false,
    "server": true,
    "bootstrap_expect": 1,
    "retry_join": ["provider=aws tag_key=${CONSUL_JOIN_KEY} tag_value=${CONSUL_JOIN_VALUE}"],
    "ui": true,
    "recursors": ["169.254.169.253"]
}
EOF

# Set Consul up as a systemd service
echo "Installing systemd service for Consul..."
sudo bash -c "cat >/etc/systemd/system/consul.service" <<EOF
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

echo "Configure Consul name resolution..."
systemctl disable systemd-resolved
systemctl stop systemd-resolved
ls -lh /etc/resolv.conf
rm /etc/resolv.conf
echo "nameserver 127.0.0.1" > /etc/resolv.conf
netplan apply

sudo bash -c "cat >>/etc/dnsmasq.conf" <<EOF
server=/consul/${CLIENT_IP}#8600
server=169.254.169.253#53
listen-address=${CLIENT_IP}
listen-address=169.254.1.1
no-resolv
log-queries
EOF

ip link add dummy0 type dummy
ip link set dev dummy0 up
ip addr add 169.254.1.1/32 dev dummy0
ip link set dev dummy0 up

sudo bash -c "cat >>//etc/systemd/network/dummy0.netdev" <<EOF
[NetDev]
Name=dummy0
Kind=dummy
EOF

sudo bash -c "cat >>/etc/systemd/network/dummy0.network" <<EOF
[Match]
Name=dummy0

[Network]
Address=169.254.1.1/32
EOF

systemctl restart systemd-networkd
systemctl stop dnsmasq
systemctl start dnsmasq
service consul stop
service consul start

sleep 3


echo "Consul installation complete."
