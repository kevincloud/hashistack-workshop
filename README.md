# HashiStack Workshop
Workshop with application to include the full HashiCorp software stack.


docker build -t online-store:online-store .
aws ecr get-login --region us-east-1 --no-include-email > login.sh
chmod a+x login.sh
./login.sh
docker tag online-store:online-store 753646501470.dkr.ecr.us-east-1.amazonaws.com/online-store:online-store
docker push 753646501470.dkr.ecr.us-east-1.amazonaws.com/online-store:online-store

curl \
    --request POST \
    --data @/root/jobs/online-store-job.nomad \
    http://nomad-server.service.dc1.consul:4646/v1/jobs

--------
run request
--------
curl -X POST \
    -H "Content-Type: application/x-www-form-urlencoded" \
    -d "sessionId=asdfasdf&productId=BE0001&quantity=1" \
    http://10.0.1.203:5823/cart
