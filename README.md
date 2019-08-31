# HashiStack Workshop
Workshop with application to include the full HashiCorp software stack.


docker exec -it `docker ps --format '{{.Image}} {{.Names}}' | grep online-store | awk -F ' ' '{print $2}'` bash
docker exec -it `docker ps --format '{{.Image}} {{.Names}}' | grep order-app | awk -F ' ' '{print $2}'` bash
