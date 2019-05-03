output "ip-address" {
  value = "${aws_instance.kevin-php-server.public_ip}"
}

output "web-address" {
  value = "http://${aws_instance.kevin-php-server.public_ip}/"
}

output "ssh-login" {
  value = "ssh -i ~/keys/${var.key_pair}.pem ubuntu@${aws_instance.kevin-php-server.public_ip}"
}
