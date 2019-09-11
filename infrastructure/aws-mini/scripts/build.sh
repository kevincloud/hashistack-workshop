#!/bin/bash

####################
# Build APIs
####################
cd /root
mkdir /root/components

export MYSQL_USER=$(curl -s --header "X-Vault-Token: $VAULT_TOKEN" http://vault-main.service.$REGION.consul:8200/v1/secret/data/dbhost | jq -r .data.data.username)
export MYSQL_PASS=$(curl -s --header "X-Vault-Token: $VAULT_TOKEN" http://vault-main.service.$REGION.consul:8200/v1/secret/data/dbhost | jq -r .data.data.password)
export CONSUL_NODE_ID=$(curl -s http://127.0.0.1:8500/v1/catalog/node/consul-client1 | jq -r .Node.ID)

# enable transit
curl \
    --header "X-Vault-Token: $VAULT_TOKEN" \
    --request POST \
    --data '{"type":"transit"}' \
    http://vault-main.service.$REGION.consul:8200/v1/sys/mounts/transit

curl \
    --header "X-Vault-Token: $VAULT_TOKEN" \
    --request POST \
    http://vault-main.service.$REGION.consul:8200/v1/transit/keys/account

curl \
    --header "X-Vault-Token: $VAULT_TOKEN" \
    --request POST \
    http://vault-main.service.$REGION.consul:8200/v1/transit/keys/payment

# register the database host with consul
curl \
    --request PUT \
    --data "{ \"Datacenter\": \"$REGION\", \"Node\": \"$CONSUL_NODE_ID\", \"Address\":\"$MYSQL_HOST\", \"Service\": { \"ID\": \"customer-db\", \"Service\": \"customer-db\", \"Address\": \"$MYSQL_HOST\", \"Port\": 3306 } }" \
    http://127.0.0.1:8500/v1/catalog/register

    # "Checks": [{
    #     "ID": "sqlsvc",
    #     "Name": "Port Accessibility",
    #     "DeregisterCriticalServiceAfter": "10m",
    #     "TCP": "customer-db.service.us-east-1.consul:3306",
    #     "Interval": "10s",
    #     "TTL": "15s",
    #     "TLSSkipVerify": true
    # }]

# Create mysql database
python3 /root/hashistack-workshop/apis/scripts/create_db.py customer-db.service.us-east-1.consul $MYSQL_USER $MYSQL_PASS $VAULT_TOKEN $REGION

# load product data
python3 /root/hashistack-workshop/apis/scripts/product_load.py

#################################
# build authapi
#################################
curl -O https://dl.google.com/go/go1.12.7.linux-amd64.tar.gz
tar xvf go1.12.7.linux-amd64.tar.gz
chown -R root:root ./go
mv go /usr/local
mkdir /root/go
mkdir /root/go/.cache
export GOPATH=/root/go
export GOCACHE=/root/go/.cache
export PATH=$PATH:/usr/local/go/bin:$GOPATH/bin

cd /root/components
git clone https://github.com/kevincloud/javaperks-auth-api.git
cd javaperks-auth-api
go get
go build -v
aws s3 cp /root/components/javaperks-auth-api/authapi s3://$S3_BUCKET/bin/authapi


#################################
# create product-app image
#################################
cd /root/components
git clone https://github.com/kevincloud/javaperks-product-api.git
cd javaperks-product-api
docker build -t product-app:product-app .
aws ecr get-login --region $REGION --no-include-email > login.sh
chmod a+x login.sh
./login.sh
docker tag product-app:product-app $REPO_URL_PROD:product-app
docker push $REPO_URL_PROD:product-app
# Upload images to S3
aws s3 cp /root/components/javaperks-product-api/images/ s3://$S3_BUCKET/images/ --recursive --acl public-read

#################################
# create cart-app image
#################################
cd /root/components
git clone https://github.com/kevincloud/javaperks-cart-api.git
cd javaperks-cart-api
docker build -t cart-app:cart-app .
aws ecr get-login --region $REGION --no-include-email > login.sh
chmod a+x login.sh
./login.sh
docker tag cart-app:cart-app $REPO_URL_CART:cart-app
docker push $REPO_URL_CART:cart-app

#################################
# create order-app image
#################################
cd /root/components
git clone https://github.com/kevincloud/javaperks-order-api.git
cd javaperks-order-api
docker build -t order-app:order-app .
aws ecr get-login --region $REGION --no-include-email > login.sh
chmod a+x login.sh
./login.sh
docker tag order-app:order-app $REPO_URL_ORDR:order-app
docker push $REPO_URL_ORDR:order-app

#################################
# create customer-api jar
#################################
cd /root/components
git clone https://github.com/kevincloud/javaperks-customer-api.git
cd javaperks-customer-api
mvn package
aws s3 cp /root/hashistack-workshop/apis/customerapi/CustomerApi/target/CustomerApi-0.1.0-SNAPSHOT.jar s3://$S3_BUCKET/jars/CustomerApi-0.1.0-SNAPSHOT.jar

#################################
# create online-site image
#################################
cd /root/components
git clone https://github.com/kevincloud/javaperks-online-store.git
cd javaperks-online-store
sudo bash -c "cat >./site/framework/config.php" <<EOF
<?php
\$assetbucket = "https://s3.amazonaws.com/$S3_BUCKET/";
\$region = "$REGION";
?>
EOF

docker build -t online-store:online-store .
aws ecr get-login --region $REGION --no-include-email > login.sh
chmod a+x login.sh
./login.sh
docker tag online-store:online-store $REPO_URL_SITE:online-store
docker push $REPO_URL_SITE:online-store

#################################
# create nomad jobs
#################################

mkdir /root/jobs

sudo bash -c "cat >/root/jobs/auth-api-job.nomad" <<EOF
{
    "Job": {
        "ID": "auth-api-job",
        "Name": "auth-api",
        "Type": "service",
        "Datacenters": ["$REGION"],
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
                    "GetterSource": "https://s3.amazonaws.com/$S3_BUCKET/bin/authapi",
                    "RelativeDest": "local/"
                }],
                "Templates": [{
                    "EmbeddedTmpl": "VAULT_ADDR = \"http://vault-main.service.$REGION.consul:8200\"\nVAULT_TOKEN = \"$VAULT_TOKEN\"",
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
        "Datacenters": ["$REGION"],
        "TaskGroups": [{
            "Name": "product-api-group",
            "Tasks": [{
                "Name": "product-api",
                "Driver": "docker",
                "Vault": {
                    "Policies": ["access-creds"]
                },
                "Config": {
                    "image": "https://$REPO_URL_PROD:product-app",
                    "port_map": [{
                        "http": 5821
                    }]
                },
                "Templates": [{
                    "EmbeddedTmpl": "{{with secret \"secret/data/aws\"}}\nAWS_ACCESS_KEY = \"{{.Data.data.aws_access_key}}\"\nAWS_SECRET_KEY = \"{{.Data.data.aws_secret_key}}\"\n{{end}}\nAWS_REGION = \"$REGION\"\n                ",
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
        "Datacenters": ["$REGION"],
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
                    "GetterSource": "https://s3.amazonaws.com/$S3_BUCKET/jars/CustomerApi-0.1.0-SNAPSHOT.jar",
                    "RelativeDest": "local/"
                }],
                "Templates": [{
                    "EmbeddedTmpl": "logging:\n  level: INFO\n  loggers:\n    com.javaperks.api: DEBUG\nserver:\n  applicationConnectors:\n  - type: http\n    port: 5822\n  adminConnectors:\n  - type: http\n    port: 9001\nvaultAddress: \"http://vault-main.service.$REGION.consul:8200\"\nvaultToken: \"$VAULT_TOKEN\"\n",
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
        "Datacenters": ["$REGION"],
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
                    "image": "https://$REPO_URL_CART:cart-app",
                    "port_map": [{
                        "http": 5823
                    }]
                },
                "Templates": [{
                    "EmbeddedTmpl": "{{with secret \"secret/data/aws\"}}\nAWS_ACCESS_KEY_ID = \"{{.Data.data.aws_access_key}}\"\nAWS_SECRET_ACCESS_KEY = \"{{.Data.data.aws_secret_key}}\"\n{{end}}\nREGION = \"$REGION\"\n                ",
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
                    "PortLabel": "http",
                    "Checks": [{
                        "Name": "HTTP Check",
                        "Type": "http",
                        "PortLabel": "http",
                        "Path": "/_health_check",
                        "Interval": 5000000000,
                        "Timeout": 2000000000
                    }]
                }]
            }]
        }]
    }
}
EOF

sudo bash -c "cat >/root/jobs/order-api-job.nomad" <<EOF
{
    "Job": {
        "ID": "order-api-job",
        "Name": "order-api",
        "Type": "service",
        "Datacenters": ["$REGION"],
        "TaskGroups": [{
            "Name": "order-api-group",
            "Count": 1,
            "Tasks": [{
                "Name": "order-api",
                "Driver": "docker",
                "Vault": {
                    "Policies": ["access-creds"]
                },
                "Config": {
                    "image": "https://$REPO_URL_ORDR:order-app",
                    "port_map": [{
                        "http": 5826
                    }]
                },
                "Templates": [{
                    "EmbeddedTmpl": "{{with secret \"secret/data/aws\"}}\nAWS_ACCESS_KEY = \"{{.Data.data.aws_access_key}}\"\nAWS_SECRET_KEY = \"{{.Data.data.aws_secret_key}}\"\n{{end}}\nAWS_REGION = \"$REGION\"\n                ",
                    "DestPath": "secrets/file.env",
                    "Envvars": true
                }],
                "Resources": {
                    "Networks": [{
                        "MBits": 1,
                        "ReservedPorts": [
                            {
                                "Label": "http",
                                "Value": 5826
                            }
                        ]
                    }]
                },
                "Services": [{
                    "Name": "order-api",
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
        "Datacenters": ["$REGION"],
        "TaskGroups": [{
            "Name": "online-store-group",
            "Tasks": [{
                "Name": "online-store",
                "Driver": "docker",
                "Vault": {
                    "Policies": ["access-creds"]
                },
                "Config": {
                    "image": "https://$REPO_URL_SITE:online-store",
                    "dns_servers": ["169.254.1.1"],
                    "port_map": [{
                        "http": 80
                    }]
                },
                "Templates": [{
                    "EmbeddedTmpl": "{{with secret \"secret/data/aws\"}}\nAWS_ACCESS_KEY = \"{{.Data.data.aws_access_key}}\"\nAWS_SECRET_KEY = \"{{.Data.data.aws_secret_key}}\"\n{{end}}{{with secret \"secret/data/roottoken\"}}\nVAULT_TOKEN = \"{{.Data.data.token}}\"\n{{end}}\nREGION = \"$REGION\"\n                ",
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
    http://nomad-server.service.$REGION.consul:4646/v1/jobs

curl \
    --request POST \
    --data @/root/jobs/product-api-job.nomad \
    http://nomad-server.service.$REGION.consul:4646/v1/jobs

curl \
    --request POST \
    --data @/root/jobs/cart-api-job.nomad \
    http://nomad-server.service.$REGION.consul:4646/v1/jobs

curl \
    --request POST \
    --data @/root/jobs/order-api-job.nomad \
    http://nomad-server.service.$REGION.consul:4646/v1/jobs

curl \
    --request POST \
    --data @/root/jobs/online-store-job.nomad \
    http://nomad-server.service.$REGION.consul:4646/v1/jobs

curl \
    --request POST \
    --data @/root/jobs/customer-api-job.nomad \
    http://nomad-server.service.$REGION.consul:4646/v1/jobs
