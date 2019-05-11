FROM ubuntu:18.04
  
LABEL Kevin Cochran "kcochran@hashicorp.com"

RUN apt-get update && apt-get upgrade -y
RUN apt-get install -y unzip jq python3 python3-pip nginx php php-fpm php-common php-cli php-curl
RUN pip3 install awscli botocore boto3 flask flask-cors
RUN apt-get -y remove apache2 
RUN rm -rf /var/www/html/*

COPY ./site/* /var/www/html/

RUN chown -R www-data:www-data /var/www/html

RUN printf 'server {\n\
        listen 80;\n\
        root /var/www/html;\n\
        index index.php;\n\
        server_name _;\n\
\n\
        location / {\n\
                try_files \$uri \$uri/ =404;\n\
        }\n\
        location ~ \.php\$ {\n\
                include snippets/fastcgi-php.conf;\n\
                fastcgi_pass unix:/var/run/php/php7.2-fpm.sock;\n\
        }\n\
        rewrite ^/products\$ /product.php?pid=0 last;\n\
        rewrite ^/products/new-releases\$ /product.php?pid=0 last;\n\
        rewrite ^/products/(.*)/(.*)\$ /product.php?pid=\$1 last;\n\
}\n' \
> /etc/nginx/sites-enabled/default

RUN sed -i -e 's/^;cgi.fix_pathinfo=1/cgi.fix_pathinfo=0/g' /etc/php/7.2/fpm/php.ini

RUN systemctl restart php7.2-fpm && systemctl restart nginx

CMD ["nginx"]