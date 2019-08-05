cat<<EOF >>/etc/apache2/apache2.conf
<Directory /var/www/html/>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
</Directory>
EOF
