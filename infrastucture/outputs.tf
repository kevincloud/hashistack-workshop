output "public-ip" {
    value = "http://${aws_instance.working-env.public_ip}:5801/"
}

output "public-host" {
    value = "http://${aws_instance.working-env.public_dns}:5801/"
}

output "vault-ip" {
    value = "${aws_instance.vault-server.public_ip}"
}
