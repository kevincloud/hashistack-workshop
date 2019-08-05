cat<<EOF >>/etc/apache2/apache2.conf
<Directory /var/www/html/>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
</Directory>
EOF

cat<<EOF >>/etc/dnsmasq.conf
server=/consul/127.0.0.1#8600
server=169.254.169.253#53
no-resolv
log-queries
EOF
