output "public-ip" {
    value = "http://${aws_instance.webserver.public_ip}:5801/"
}

output "public-host" {
    value = "http://${aws_instance.webserver.public_dns}:5801/"
}

