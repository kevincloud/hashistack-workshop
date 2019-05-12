job "mailer" {
    datacenters = ["dc1"]

    type = "system"

    group "api" {
      count = 1

      task "listen" {
        driver = "docker"

        # need to add all of these with envconsul
        env {
          API_TOKEN = ""
          LIST_ID = ""
          AUTH_NAME = ""
        }

        artifact {
          # could also elect to use the sshkey option too if you wanted
          source      = "git::https:$TOKEN//@github.com/joshuaNjordan85/mailer"
          destination = "local/mailer"
        }

        config {
          image = "joshuanjordan/mailer-node:latest"

          auth {
            username = ""
            password  = ""
            email = ""
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
