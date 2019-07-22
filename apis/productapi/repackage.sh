curl \
    --request DELETE \
    http://nomad-server.service.dc1.consul:4646/v1/job/product-api-job?purge=true

docker build -t product-app:product-app .
aws ecr get-login --region us-east-1 --no-include-email > login.sh
chmod a+x login.sh
./login.sh
docker tag product-app:product-app 753646501470.dkr.ecr.us-east-1.amazonaws.com/product-app:product-app
docker push 753646501470.dkr.ecr.us-east-1.amazonaws.com/product-app:product-app

curl \
    --request POST \
    --data @/root/jobs/product-api-job.nomad \
    http://nomad-server.service.dc1.consul:4646/v1/jobs
