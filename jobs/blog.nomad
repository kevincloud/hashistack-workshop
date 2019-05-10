job "blog" {
    datacenters = ["dc1"]
    type = "service"
    group "web" {
      count = 1

      task "ghost" {
        driver = "docker"

        template {
          data = <<TPL
          url = "http://{{ with node }}{{ .Node.Address }}{{ end }}"
          TPL

          destination = "local/ghost.env"
          env = true
        }

        config {
          image = "ghost:2.14.0-alpine"

          volumes = [
            "ghostcontent:/var/lib/ghost/content"
          ]

          port_map = {
            http = 2368
          }
        }

        resources {
          network {
            port "http" {}
          }
        }

        service {
          tags = ["ghost", "node", "docker"]

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
