job "mailer" {
    datacenters = ["dc1"]

    type = "system"

    group "api" {
      count = 1

      task "listen" {
        driver = "docker"

        env {
          API_TOKEN = "961e7613c117fe759727f8efd888f8c6-us17"
          LIST_ID = "4eb83a1bfc"
          AUTH_NAME = "candystripedgrenade"
        }

        artifact {
          # could also elect to use the sshkey option too if you wanted
          source      = "git::https://f2193f41ee06f37c24d8c71a7676fb991a342b41@github.com/joshuaNjordan85/mailer"
          destination = "local/mailer"
        }

        config {
          image = "joshuanjordan/mailer-node:latest"

          auth {
            username = "joshuanjordan"
            password  = "Dub792Pat254$"
            email = "joshuanjordan@gmail.com"
          }

          volumes = [
          "local/mailer:/mailer"
          ]

          port_map = {
            http = 9000
          }
        }

        resources {
          network {
            port "http" {}
          }
        }

        service {
          tags = ["node", "docker"]

          port = "http"

          check {
            port = "http"
            type = "tcp"
            interval = "10s"
            timeout = "2s"
          }
        }
      }
    }
  }
