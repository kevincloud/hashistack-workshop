#!/bin/bash

# Get latest code updates
git pull

# Delete Nomad job
curl \
    --request DELETE \
    http://nomad-server.service.us-east-1.consul:4646/v1/job/customer-api-job?purge=true


# Package a new jar
mvn package

# Push jar to S3
aws s3 cp /root/hashistack-workshop/apis/customerapi/CustomerApi/target/CustomerApi-0.1.0-SNAPSHOT.jar s3://hc-workshop-2.0-assets/jars/CustomerApi-0.1.0-SNAPSHOT.jar

# Submit a Nomad job
curl \
    --request POST \
    --data @/root/jobs/product-api-job.nomad \
    http://nomad-server.service.us-east-1.consul:4646/v1/jobs
