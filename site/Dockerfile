FROM ubuntu:18.04
  
LABEL Kevin Cochran "kcochran@hashicorp.com"

RUN echo 'libc6 libraries/restart-without-asking boolean true' | debconf-set-selections
ENV DEBIAN_FRONTEND noninteractive

RUN apt-get update && apt-get upgrade -y
RUN apt-get install -y unzip jq python3 python3-pip nginx php php-fpm php-common php-cli php-curl supervisor
RUN pip3 install awscli botocore boto3 flask flask-cors
RUN apt-get -y remove apache2
RUN rm -rf /var/www/html/*
RUN mkdir -p /var/run/php

ADD site /var/www/html/

RUN chown -R www-data:www-data /var/www/html

COPY nginx-default.conf /etc/nginx/sites-enabled/default

RUN sed -i -e 's/^;cgi.fix_pathinfo=1/cgi.fix_pathinfo=0/g' /etc/php/7.2/fpm/php.ini

RUN sed -i 's/^\(display_errors\s*=\s*\).*$/\1On/' /etc/php/7.2/fpm/php.ini

COPY supervisord.conf /etc/supervisord.conf

CMD ["/usr/bin/supervisord", "-n"]
