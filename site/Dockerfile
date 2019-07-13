FROM ubuntu:18.04
  
LABEL Kevin Cochran "kcochran@hashicorp.com"

RUN echo 'libc6 libraries/restart-without-asking boolean true' | debconf-set-selections
ENV DEBIAN_FRONTEND noninteractive

RUN apt-get update && apt-get upgrade -y
RUN apt-get install -y unzip jq python3 python3-pip php libapache2-mod-php php-fpm php-common php-curl
RUN pip3 install awscli botocore boto3 flask flask-cors
RUN rm -rf /var/www/html/*
RUN mkdir -p /var/run/php
RUN a2enmod rewrite

ADD site /var/www/html/

COPY htaccess /var/www/html/.htaccess
COPY config.sh /root/config.sh

RUN sed -i 's/^\(display_errors\s*=\s*\).*$/\1On/' /etc/php/7.2/fpm/php.ini

RUN /root/config.sh

CMD apachectl -D FOREGROUND
