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
{{with secret "secret/data/aws"}}
AWS_ACCESS_KEY = "{{.Data.data.aws_access_key}}"
AWS_SECRET_KEY = "{{.Data.data.aws_secret_key}}"
{{end}}
AWS_REGION = "us-east-1"
                EOF

                destination = "secrets/file.env"
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