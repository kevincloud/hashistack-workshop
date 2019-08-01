#!/bin/bash

####################
# Build APIs
####################
cd /root/hashistack-workshop/apis

# load product data
python3 ./scripts/product_load.py

# Upload images to S3
aws s3 cp /root/hashistack-workshop/apis/productapi/images/ s3://${S3_BUCKET}/images/ --recursive --acl public-read

# build authapi
curl -O https://dl.google.com/go/go1.12.7.linux-amd64.tar.gz
tar xvf go1.12.7.linux-amd64.tar.gz
chown -R root:root ./go
mv go /usr/local
mkdir /root/go
mkdir /root/go/.cache
export GOPATH=/root/go
export GOCACHE=/root/go/.cache
export PATH=$PATH:/usr/local/go/bin:$GOPATH/bin

cd /root/hashistack-workshop/apis/authapi
go get
go build -v
aws s3 cp /root/hashistack-workshop/apis/authapi/authapi s3://${S3_BUCKET}/bin/authapi


# create product-app image
cd /root/hashistack-workshop/apis/productapi
docker build -t product-app:product-app .
aws ecr get-login --region ${REGION} --no-include-email > login.sh
chmod a+x login.sh
./login.sh
docker tag product-app:product-app ${REPO_URL_PROD}:product-app
docker push ${REPO_URL_PROD}:product-app

# create cart-app image
cd /root/hashistack-workshop/apis/cartapi
docker build -t cart-app:cart-app .
aws ecr get-login --region ${REGION} --no-include-email > login.sh
chmod a+x login.sh
./login.sh
docker tag cart-app:cart-app ${REPO_URL_CART}:cart-app
docker push ${REPO_URL_CART}:cart-app

# create account-broker image
cd /root/hashistack-workshop/apis/account-broker
docker build -t account-broker:account-broker .
aws ecr get-login --region ${REGION} --no-include-email > login.sh
chmod a+x login.sh
./login.sh
docker tag account-broker:account-broker ${REPO_URL_ACCT}:account-broker
docker push ${REPO_URL_ACCT}:account-broker

# create customer-api jar
cd /root/hashistack-workshop/apis/customerapi/CustomerApi
mvn package
aws s3 cp /root/hashistack-workshop/apis/customerapi/CustomerApi/target/CustomerApi-0.1.0-SNAPSHOT.jar s3://${S3_BUCKET}/jars/CustomerApi-0.1.0-SNAPSHOT.jar

# create online-site image
cd /root/hashistack-workshop/site
sudo bash -c "cat >/root/hashistack-workshop/site/site/framework/config.php" <<EOF
<?php
\$authapiurl = "http://${CONSUL_IP}:8500/v1/catalog/service/auth-api";
\$productapiurl = "http://${CONSUL_IP}:8500/v1/catalog/service/product-api";
\$customerapiurl = "http://${CONSUL_IP}:8500/v1/catalog/service/customer-api";
\$cartapiurl = "http://${CONSUL_IP}:8500/v1/catalog/service/cart-api";
\$assetbucket = "https://s3.amazonaws.com/${S3_BUCKET}/"
?>
EOF

docker build -t online-store:online-store .
aws ecr get-login --region ${REGION} --no-include-email > login.sh
chmod a+x login.sh
./login.sh
docker tag online-store:online-store ${REPO_URL_SITE}:online-store
docker push ${REPO_URL_SITE}:online-store

mkdir /root/jobs

sudo bash -c "cat >/root/jobs/auth-api-job.nomad" <<EOF
{
    "Job": {
        "ID": "auth-api-job",
        "Name": "auth-api",
        "Type": "service",
        "Datacenters": ["${REGION}"],
        "TaskGroups": [{
            "Name": "auth-api-group",
            "Tasks": [{
                "Name": "auth-api",
                "Driver": "exec",
                "Count": 1,
                "Update": {
                    "Stagger": 10000000000,
                    "MaxParallel": 1,
                    "HealthCheck": "checks",
                    "MinHealthyTime": 10000000000,
                    "HealthyDeadline": 300000000000
                },
                "Vault": {
                    "Policies": ["access-creds"]
                },
                "Config": {
                    "command": "local/authapi"
                },
                "Artifacts": [{
                    "GetterSource": "https://s3.amazonaws.com/${S3_BUCKET}/bin/authapi",
                    "RelativeDest": "local/"
                }],
                "Templates": [{
                    "EmbeddedTmpl": "VAULT_ADDR = \"http://${VAULT_IP}:8200\"\nVAULT_TOKEN = \"$VAULT_TOKEN\"",
                    "DestPath": "secrets/file.env",
                    "Envvars": true
                }],
                "Resources": {
                    "Networks": [{
                        "MBits": 1,
                        "ReservedPorts": [
                            {
                                "Label": "http",
                                "Value": 5825
                            }
                        ]
                    }]
                },
                "Services": [{
                    "Name": "auth-api",
                    "PortLabel": "http"
                }]
            }]
        }]
    }
}
EOF

sudo bash -c "cat >/root/jobs/product-api-job.nomad" <<EOF
{
    "Job": {
        "ID": "product-api-job",
        "Name": "product-api",
        "Type": "service",
        "Datacenters": ["${REGION}"],
        "TaskGroups": [{
            "Name": "product-api-group",
            "Tasks": [{
                "Name": "product-api",
                "Driver": "docker",
                "Vault": {
                    "Policies": ["access-creds"]
                },
                "Config": {
                    "image": "https://${REPO_URL_PROD}:product-app",
                    "port_map": [{
                        "http": 5821
                    }]
                },
                "Templates": [{
                    "EmbeddedTmpl": "{{with secret \"secret/data/aws\"}}\nAWS_ACCESS_KEY = \"{{.Data.data.aws_access_key}}\"\nAWS_SECRET_KEY = \"{{.Data.data.aws_secret_key}}\"\n{{end}}\nAWS_REGION = \"${REGION}\"\n                ",
                    "DestPath": "secrets/file.env",
                    "Envvars": true
                }],
                "Resources": {
                    "Networks": [{
                        "MBits": 1,
                        "ReservedPorts": [
                            {
                                "Label": "http",
                                "Value": 5821
                            }
                        ]
                    }]
                },
                "Services": [{
                    "Name": "product-api",
                    "PortLabel": "http"
                }]
            }]
        }]
    }
}
EOF

sudo bash -c "cat >/root/jobs/customer-api-job.nomad" <<EOF
{
    "Job": {
        "ID": "customer-api-job",
        "Name": "customer-api",
        "Type": "service",
        "Datacenters": ["${REGION}"],
        "TaskGroups": [{
            "Name": "customer-api-group",
            "Tasks": [{
                "Name": "customer-api",
                "Driver": "java",
                "Count": 1,
                "Update": {
                    "Stagger": 10000000000,
                    "MaxParallel": 1,
                    "HealthCheck": "checks",
                    "MinHealthyTime": 10000000000,
                    "HealthyDeadline": 300000000000
                },
                "Vault": {
                    "Policies": ["access-creds"]
                },
                "Config": {
                    "jar_path": "local/CustomerApi-0.1.0-SNAPSHOT.jar",
                    "args": [ "server", "local/config.yml" ]
                },
                "Artifacts": [{
                    "GetterSource": "https://s3.amazonaws.com/${S3_BUCKET}/jars/CustomerApi-0.1.0-SNAPSHOT.jar",
                    "RelativeDest": "local/"
                }],
                "Templates": [{
                    "EmbeddedTmpl": "logging:\n  level: INFO\n  loggers:\n    com.javaperks.api: DEBUG\nserver:\n  applicationConnectors:\n  - type: http\n    port: 5822\n  adminConnectors:\n  - type: http\n    port: 9001\nvaultAddress: \"http://${VAULT_IP}:8200\"\nvaultToken: \"$VAULT_TOKEN\"\n",
                    "DestPath": "local/config.yml"
                }],
                "Resources": {
                    "Networks": [{
                        "MBits": 1,
                        "ReservedPorts": [
                            {
                                "Label": "http",
                                "Value": 5822
                            }
                        ]
                    }]
                },
                "Services": [{
                    "Name": "customer-api",
                    "PortLabel": "http"
                }]
            }]
        }]
    }
}
EOF

sudo bash -c "cat >/root/jobs/cart-api-job.nomad" <<EOF
{
    "Job": {
        "ID": "cart-api-job",
        "Name": "cart-api",
        "Type": "service",
        "Datacenters": ["${REGION}"],
        "TaskGroups": [{
            "Name": "cart-api-group",
            "Count": 1,
            "Tasks": [{
                "Name": "cart-api",
                "Driver": "docker",
                "Vault": {
                    "Policies": ["access-creds"]
                },
                "Config": {
                    "image": "https://${REPO_URL_CART}:cart-app",
                    "port_map": [{
                        "http": 5823
                    }]
                },
                "Templates": [{
                    "EmbeddedTmpl": "{{with secret \"secret/data/aws\"}}\nAWS_ACCESS_KEY_ID = \"{{.Data.data.aws_access_key}}\"\nAWS_SECRET_ACCESS_KEY = \"{{.Data.data.aws_secret_key}}\"\n{{end}}\nREGION = \"${REGION}\"\n                ",
                    "DestPath": "secrets/file.env",
                    "Envvars": true
                }],
                "Resources": {
                    "Networks": [{
                        "MBits": 1,
                        "ReservedPorts": [
                            {
                                "Label": "http",
                                "Value": 5823
                            }
                        ]
                    }]
                },
                "Services": [{
                    "Name": "cart-api",
                    "PortLabel": "http"
                }]
            }]
        }]
    }
}
EOF

sudo bash -c "cat >/root/jobs/account-broker-job.nomad" <<EOF
{
    "Job": {
        "ID": "account-broker-job",
        "Name": "account-broker",
        "Type": "service",
        "Datacenters": ["${REGION}"],
        "TaskGroups": [{
            "Name": "account-broker-group",
            "Count": 1,
            "Tasks": [{
                "Name": "account-broker",
                "Driver": "docker",
                "Vault": {
                    "Policies": ["access-creds"]
                },
                "Config": {
                    "image": "https://${REPO_URL_ACCT}:account-broker",
                    "port_map": [{
                        "http": 5824
                    }]
                },
                "Templates": [{
                    "EmbeddedTmpl": "PORT = 5824\nVAULT_ADDR = \"http://${VAULT_IP}:8200\"\nVAULT_TOKEN = \"$VAULT_TOKEN\"",
                    "DestPath": "secrets/file.env",
                    "Envvars": true
                }],
                "Resources": {
                    "Networks": [{
                        "MBits": 1,
                        "ReservedPorts": [
                            {
                                "Label": "http",
                                "Value": 5824
                            }
                        ]
                    }]
                },
                "Services": [{
                    "Name": "account-broker",
                    "PortLabel": "http"
                }]
            }]
        }]
    }
}
EOF

sudo bash -c "cat >/root/jobs/online-store-job.nomad" <<EOF
{
    "Job": {
        "ID": "online-store-job",
        "Name": "online-store",
        "Type": "service",
        "Datacenters": ["${REGION}"],
        "TaskGroups": [{
            "Name": "online-store-group",
            "Tasks": [{
                "Name": "online-store",
                "Driver": "docker",
                "Vault": {
                    "Policies": ["access-creds"]
                },
                "Config": {
                    "image": "https://${REPO_URL_SITE}:online-store",
                    "port_map": [{
                        "http": 80
                    }]
                },
                "Templates": [{
                    "EmbeddedTmpl": "{{with secret \"secret/data/aws\"}}\nAWS_ACCESS_KEY = \"{{.Data.data.aws_access_key}}\"\nAWS_SECRET_KEY = \"{{.Data.data.aws_secret_key}}\"\n{{end}}\nREGION = \"${REGION}\"\n                ",
                    "DestPath": "secrets/file.env",
                    "Envvars": true
                }],
                "Resources": {
                    "Networks": [{
                        "MBits": 1,
                        "ReservedPorts": [
                           {
                                "Label": "http",
                                "Value": 80
                            }
                        ]
                    }]
                },
                "Services": [{
                    "Name": "online-store",
                    "PortLabel": "http"
                }]
            }]
        }]
    }
}
EOF

curl \
    --request POST \
    --data @/root/jobs/auth-api-job.nomad \
    http://nomad-server.service.${REGION}.consul:4646/v1/jobs

curl \
    --request POST \
    --data @/root/jobs/product-api-job.nomad \
    http://nomad-server.service.${REGION}.consul:4646/v1/jobs

curl \
    --request POST \
    --data @/root/jobs/cart-api-job.nomad \
    http://nomad-server.service.${REGION}.consul:4646/v1/jobs

curl \
    --request POST \
    --data @/root/jobs/account-broker-job.nomad \
    http://nomad-server.service.${REGION}.consul:4646/v1/jobs

curl \
    --request POST \
    --data @/root/jobs/online-store-job.nomad \
    http://nomad-server.service.${REGION}.consul:4646/v1/jobs

curl \
    --request POST \
    --data @/root/jobs/customer-api-job.nomad \
    http://nomad-server.service.${REGION}.consul:4646/v1/jobs
