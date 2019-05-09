job "product-api-job" {
    datacenters = ["dc1"]

    group "product-api" {
        task "server" {
            driver = "docker"
            
            vault {
                policies = ["access-creds"]
            }

            config {
                image = "https://753646501470.dkr.ecr.us-east-1.amazonaws.com/product-app:product-app"

                port_map {
                    http = 5821
                }
            }

            template {
                data = <<EOF
{{with secret "secret/aws"}}
AWS_ACCESS_KEY_ID="{{.Data.aws_access_key}}"
AWS_SECRET_ACCESS_KEY="{{.Data.aws_secret_key}}"
{{end}}
                EOF

                destination = "secrets/aws.env"
                env = true
            }

            resources {
                network {
                    port "http" {}
                }
            }

            service {
                name = "productapi"
                port = "http"
            }
        }
    }
}