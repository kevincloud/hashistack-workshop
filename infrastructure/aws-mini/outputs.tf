output "00-Site-URL" {
    value = "http://${aws_instance.hashi-server.public_ip}/"
}

output "01-vault-server" {
    value = "http://${aws_instance.hashi-server.public_ip}:8200/"
}

output "02-nomad-server" {
    value = "http://${aws_instance.hashi-server.public_ip}:4646/"
}

output "03-consul-server" {
    value = "http://${aws_instance.hashi-server.public_ip}:8500/"
}

output "04-server-ssh" {
    value = "ssh -i ~/keys/${var.key_pair}.pem ubuntu@${aws_instance.hashi-server.public_ip}"
}
