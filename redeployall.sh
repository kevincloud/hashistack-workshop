#!/bin/bash

if [ "${1,,}" = "-purge" ]; then
    # Purge all images
    docker image prune -f
    docker image rm `docker images --filter=reference=online-store --format "{{.ID}}"` -f
    aws ecr batch-delete-image --repository-name online-store --image-ids "`aws ecr list-images --repository-name online-store --query 'imageIds[*]' --output json`" || true

    docker image rm `docker images --filter=reference=cart-app --format "{{.ID}}"` -f
    aws ecr batch-delete-image --repository-name cart-app --image-ids "`aws ecr list-images --repository-name cart-app --query 'imageIds[*]' --output json`" || true

    docker image rm `docker images --filter=reference=product-app --format "{{.ID}}"` -f
    aws ecr batch-delete-image --repository-name product-app --image-ids "`aws ecr list-images --repository-name product-app --query 'imageIds[*]' --output json`" || true
fi

# Get latest code updates
git pull

# Delete Nomad jobs
curl \
    --request DELETE \
    http://nomad-server.service.us-east-1.consul:4646/v1/job/cart-api-job?purge=true

curl \
    --request DELETE \
    http://nomad-server.service.us-east-1.consul:4646/v1/job/product-api-job?purge=true

curl \
    --request DELETE \
    http://nomad-server.service.us-east-1.consul:4646/v1/job/auth-api-job?purge=true

curl \
    --request DELETE \
    http://nomad-server.service.us-east-1.consul:4646/v1/job/online-store-job?purge=true

curl \
    --request DELETE \
    http://nomad-server.service.us-east-1.consul:4646/v1/job/customer-api-job?purge=true

####################################
# Package a new cart-api image
####################################
cd ~/hashistack-workshop/apis/cartapi

# build image
docker build -t cart-app:cart-app .

# Push image to ECR
aws ecr get-login --region us-east-1 --no-include-email > login.sh
chmod a+x login.sh
./login.sh
docker tag cart-app:cart-app 753646501470.dkr.ecr.us-east-1.amazonaws.com/cart-app:cart-app
docker push 753646501470.dkr.ecr.us-east-1.amazonaws.com/cart-app:cart-app

####################################
# Package a new product-api image
####################################
cd ~/hashistack-workshop/apis/productapi

# build image
docker build -t product-app:product-app .

# Push image to ECR
aws ecr get-login --region us-east-1 --no-include-email > login.sh
chmod a+x login.sh
./login.sh
docker tag product-app:product-app 753646501470.dkr.ecr.us-east-1.amazonaws.com/product-app:product-app
docker push 753646501470.dkr.ecr.us-east-1.amazonaws.com/product-app:product-app

####################################
# Package a new online-store image
####################################
cd ~/hashistack-workshop/site

# build image
docker build -t online-store:online-store .

# Push image to ECR
aws ecr get-login --region us-east-1 --no-include-email > login.sh
chmod a+x login.sh
./login.sh
docker tag online-store:online-store 753646501470.dkr.ecr.us-east-1.amazonaws.com/online-store:online-store
docker push 753646501470.dkr.ecr.us-east-1.amazonaws.com/online-store:online-store

####################################
# Package a new customer-api jar
####################################
cd ~/hashistack-workshop/apis/customerapi/CustomerApi

# build jar
mvn package

# Push jar to S3
aws s3 cp /root/hashistack-workshop/apis/customerapi/CustomerApi/target/CustomerApi-0.1.0-SNAPSHOT.jar s3://hc-workshop-2.0-assets/jars/CustomerApi-0.1.0-SNAPSHOT.jar

####################################
# Package a new auth-api binary
####################################
cd ~/hashistack-workshop/apis/authapi

export GOPATH=/root/go
export GOCACHE=/root/go/.cache
export PATH=$PATH:/usr/local/go/bin:$GOPATH/bin

# build binary
cd /root/hashistack-workshop/apis/authapi
go get
go build -v

# push binary to S3
aws s3 cp /root/hashistack-workshop/apis/authapi/authapi s3://${S3_BUCKET}/bin/authapi


####################################
# Package a new auth-api binary
####################################
curl \
    --request POST \
    --data @/root/jobs/auth-api-job.nomad \
    http://nomad-server.service.us-east-1.consul:4646/v1/jobs

curl \
    --request POST \
    --data @/root/jobs/product-api-job.nomad \
    http://nomad-server.service.us-east-1.consul:4646/v1/jobs

curl \
    --request POST \
    --data @/root/jobs/cart-api-job.nomad \
    http://nomad-server.service.us-east-1.consul:4646/v1/jobs

curl \
    --request POST \
    --data @/root/jobs/online-store-job.nomad \
    http://nomad-server.service.us-east-1.consul:4646/v1/jobs

curl \
    --request POST \
    --data @/root/jobs/customer-api-job.nomad \
    http://nomad-server.service.us-east-1.consul:4646/v1/jobs
