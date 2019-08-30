#!/bin/bash

if [ "${1,,}" = "-purge" ]; then
    # Purge all images
    docker image prune -f
    docker image rm `docker images --filter=reference=order-app --format "{{.ID}}"` -f
    aws ecr batch-delete-image --repository-name order-app --image-ids "`aws ecr list-images --repository-name order-app --query 'imageIds[*]' --output json`" || true
fi

# Get latest code updates
git pull

# Delete Nomad job
curl \
    --request DELETE \
    http://nomad-server.service.us-east-1.consul:4646/v1/job/order-api-job?purge=true


# Package a new image
docker build -t order-app:order-app .

# Push image to ECR
aws ecr get-login --region us-east-1 --no-include-email > login.sh
chmod a+x login.sh
./login.sh
docker tag order-app:order-app 753646501470.dkr.ecr.us-east-1.amazonaws.com/order-app:order-app
docker push 753646501470.dkr.ecr.us-east-1.amazonaws.com/order-app:order-app

# Submit a Nomad job
curl \
    --request POST \
    --data @/root/jobs/order-api-job.nomad \
    http://nomad-server.service.us-east-1.consul:4646/v1/jobs
