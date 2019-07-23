#!/bin/bash

if [ ${1,,} = "-purge" ]; then
    # Purge all images
    docker image prune -f
    docker image rm `docker images --filter=reference=online-store --format "{{.ID}}"` -f
    aws ecr batch-delete-image --repository-name online-store --image-ids "`aws ecr list-images --repository-name online-store --query 'imageIds[*]' --output json`" || true
fi

# Delete Nomad job
curl \
    --request DELETE \
    http://nomad-server.service.dc1.consul:4646/v1/job/online-store-job?purge=true


# Package a new image
docker build -t online-store:online-store .

# Push image to ECR
aws ecr get-login --region us-east-1 --no-include-email > login.sh
chmod a+x login.sh
./login.sh
docker tag online-store:online-store 753646501470.dkr.ecr.us-east-1.amazonaws.com/online-store:online-store
docker push 753646501470.dkr.ecr.us-east-1.amazonaws.com/online-store:online-store

# Submit a Nomad job
curl \
    --request POST \
    --data @/root/jobs/online-store-job.nomad \
    http://nomad-server.service.dc1.consul:4646/v1/jobs
