curl \
    --request DELETE \
    http://nomad-server.service.dc1.consul:4646/v1/job/cart-api-job?purge=true

docker build -t cart-app:cart-app .
aws ecr get-login --region us-east-1 --no-include-email > login.sh
chmod a+x login.sh
./login.sh
docker tag cart-app:cart-app 753646501470.dkr.ecr.us-east-1.amazonaws.com/cart-app:cart-app
docker push 753646501470.dkr.ecr.us-east-1.amazonaws.com/cart-app:cart-app

curl \
    --request POST \
    --data @/root/jobs/cart-api-job.nomad \
    http://nomad-server.service.dc1.consul:4646/v1/jobs
