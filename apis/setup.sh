#!/bin/bash

#sudo aws s3 cp /var/www/html/online-store/productapi/images/ s3://${S3_BUCKET}/ --acl public-read

# load product data
python3 ./scripts/product-load.py

# create customer-app image
cd ./productapi
docker build -t product-app:latest .
# docker save -o ./product-app.tar product-app:latest
# docker tag product-app:latest 753646501470.dkr.ecr.us-east-1.amazonaws.com/hashistack_appgroup
docker push 753646501470.dkr.ecr.us-east-1.amazonaws.com/hashistack_appgroup:latest

