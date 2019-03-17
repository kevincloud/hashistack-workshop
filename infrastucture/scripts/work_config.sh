#!/bin/bash

# generate credentials for docker image
python3 /root/appcreds.py > /root/img/creds.json

# create customer-app image
cd /root/img
docker build -t customer-app:latest .
docker save -o ../customer-app.tar customer-app:latest

# load customer data
python3 /root/customer-load.py

# load product data
python3 /root/product-load.py
