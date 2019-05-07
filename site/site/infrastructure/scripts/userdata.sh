#!/bin/sh
# Configures the web server

sudo apt-get -y update
sudo apt-get -y upgrade
sudo apt-get install -y unzip jq python3 python3-pip nginx php php-fpm php-common php-cli php-curl
sudo pip3 install awscli botocore boto3 flask flask-cors
sudo apt-get -y remove apache2
rm -rf /var/www/html/*
cd /var/www/html
git clone https://${GITUSER}:${GITTOKEN}@github.com/kevincloud/online-store.git
chown -R www-data:www-data /var/www/html

sudo bash -c "cat >/etc/nginx/sites-enabled/default" <<EOF
server {
        listen 80;
        root /var/www/html/online-store;
        index index.php;
        server_name _;

        location / {
                try_files \$uri \$uri/ =404;
        }
        location ~ \.php\$ {
                include snippets/fastcgi-php.conf;
                fastcgi_pass unix:/var/run/php/php7.2-fpm.sock;
        }
        rewrite ^/products\$ /product.php?pid=0 last;
        rewrite ^/products/new-releases\$ /product.php?pid=0 last;
        rewrite ^/products/(.*)/(.*)\$ /product.php?pid=\$1 last;
}
EOF
sed -i -e 's/^;cgi.fix_pathinfo=1/cgi.fix_pathinfo=0/g' /etc/php/7.2/fpm/php.ini
systemctl restart php7.2-fpm
systemctl restart nginx

# setup aws creds
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
EOF

sudo aws s3 cp /var/www/html/online-store/productapi/images/ s3://${S3_BUCKET}/ --acl public-read

sudo bash -c "cat >//var/www/html/online-store/settings.conf" <<EOF
ProductAPI = "http://IP_ADDRESS:5821"
ImageLocation = "https://s3-${S3_REGION}.amazonaws.com/${S3_BUCKET}/"
EOF
