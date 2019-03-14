#!/bin/bash

python3 /root/appcreds.py > /root/img/creds.json

cd /root/img
docker build -t customer-app:latest .

python3 /root/customer-load.py
