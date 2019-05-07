# output "public-ip" {
#     value = "http://${aws_instance.working-env.public_ip}:5801/"
# }

# output "public-host" {
#     value = "http://${aws_instance.working-env.public_dns}:5801/"
# }

output "vault-ip" {
    value = "${aws_instance.vault-server.public_ip}"
}

output "mysql-host" {
    value = "${aws_db_instance.vault-mysql.endpoint}"
}

output "nomad-server" {
    value = "http://${aws_instance.nomad-server.public_ip}:4646/"
}

output "consul-server" {
    value = "http://${aws_instance.consul-server.public_ip}:8500/"
}

output "consul-server-ssh" {
    value = "ssh -i ~/keys/${var.key_pair}.pem ubuntu@${aws_instance.consul-server.public_ip}"
}

output "nomad-client-1" {
    value = "http://${aws_instance.nomad-client-1.public_ip}:4646/"
}

output "nomad-client-1-ssh" {
    value = "ssh -i ~/keys/${var.key_pair}.pem ubuntu@${aws_instance.nomad-client-1.public_ip}"
}

output "nomad-client-2" {
    value = "http://${aws_instance.nomad-client-2.public_ip}:4646/"
}

output "nomad-client-2-ssh" {
    value = "ssh -i ~/keys/${var.key_pair}.pem ubuntu@${aws_instance.nomad-client-2.public_ip}"
}
