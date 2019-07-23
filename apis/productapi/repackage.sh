#!/bin/bash

if [ ${1,,} = "-purge" ]; then
    # Purge all images
    docker image prune -f
    docker image rm `docker images --filter=reference=product-app --format "{{.ID}}"` -f
    aws ecr batch-delete-image --repository-name product-app --image-ids "`aws ecr list-images --repository-name product-app --query 'imageIds[*]' --output json`" || true
fi

# Delete Nomad job
curl \
    --request DELETE \
    http://nomad-server.service.dc1.consul:4646/v1/job/product-api-job?purge=true


# Package a new image
docker build -t product-app:product-app .

# Push image to ECR
aws ecr get-login --region us-east-1 --no-include-email > login.sh
chmod a+x login.sh
./login.sh
docker tag product-app:product-app 753646501470.dkr.ecr.us-east-1.amazonaws.com/product-app:product-app
docker push 753646501470.dkr.ecr.us-east-1.amazonaws.com/product-app:product-app

# Submit a Nomad job
curl \
    --request POST \
    --data @/root/jobs/product-api-job.nomad \
    http://nomad-server.service.dc1.consul:4646/v1/jobs
