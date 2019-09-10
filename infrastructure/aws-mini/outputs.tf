output "A-Site-URL" {
    value = "http://${aws_instance.hashi-server.public_ip}/"
}

output "B-vault-server" {
    value = "http://${aws_instance.hashi-server.public_ip}:8200/"
}

output "C-nomad-server" {
    value = "http://${aws_instance.hashi-server.public_ip}:4646/"
}

output "D-consul-server" {
    value = "http://${aws_instance.hashi-server.public_ip}:8500/"
}

output "E-server-ssh" {
    value = "ssh -i ~/keys/${var.key_pair}.pem ubuntu@${aws_instance.hashi-server.public_ip}"
}
