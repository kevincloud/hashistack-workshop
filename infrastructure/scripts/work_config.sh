#!/bin/bash

# generate credentials for docker image
python3 /root/appcreds.py > /root/img/creds.json

# load customer data
python3 /root/customer-load.py

# load product data
python3 /root/product-load.py

# create customer-app image
cd /root/img
docker build -t customer-app:latest .
docker save -o ./customer-app.tar customer-app:latest
# scp ./customer-app.tar <DESTINATION HOST>

# run the app
# docker load -i ./customer-app.tar
# docker run -d -p 5801:5801 customer-app:latest

