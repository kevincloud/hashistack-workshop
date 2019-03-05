#!/bin/bash

apt update
apt -y upgrade
apt -y install python3 python3-pip
pip3 install flask
