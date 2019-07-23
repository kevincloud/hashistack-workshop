#!/bin/bash

if [ ${1,,} = "-purge" ]; then
    # Purge all images
    docker image prune -f
    docker image rm `docker images --filter=reference=cart-app --format "{{.ID}}"` -f
    aws ecr batch-delete-image --repository-name cart-app --image-ids "`aws ecr list-images --repository-name cart-app --query 'imageIds[*]' --output json`" || true
fi

# Delete Nomad job
curl \
    --request DELETE \
    http://nomad-server.service.dc1.consul:4646/v1/job/cart-api-job?purge=true


# Package a new image
docker build -t cart-app:cart-app .

# Push image to ECR
aws ecr get-login --region us-east-1 --no-include-email > login.sh
chmod a+x login.sh
./login.sh
docker tag cart-app:cart-app 753646501470.dkr.ecr.us-east-1.amazonaws.com/cart-app:cart-app
docker push 753646501470.dkr.ecr.us-east-1.amazonaws.com/cart-app:cart-app

# Submit a Nomad job
curl \
    --request POST \
    --data @/root/jobs/cart-api-job.nomad \
    http://nomad-server.service.dc1.consul:4646/v1/jobs
