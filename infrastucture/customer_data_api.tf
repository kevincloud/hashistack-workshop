resource "aws_instance" "customer-data-api" {
    ami = "${data.aws_ami.ubuntu.id}"
    instance_type = "t2.micro"
    key_name = "${var.key_pair}"
    vpc_security_group_ids = ["${aws_security_group.customer-data-api-sg.id}"]
    user_data = "${data.template_file.user_data.rendered}"
    subnet_id = "${aws_subnet.public-subnet.id}"
    
    tags = {
        Name = "cust-mgmt-web"
    }
}

resource "aws_security_group" "customer-data-api-sg" {
    name = "customer-data-api-sg"
    description = "webserver security group"
    vpc_id = "${aws_vpc.primary-vpc.id}"

    ingress {
        from_port = 22
        to_port = 22
        protocol = "tcp"
        cidr_blocks = ["0.0.0.0/0"]
    }

    ingress {
        from_port = 5801
        to_port = 5801
        protocol = "tcp"
        cidr_blocks = ["0.0.0.0/0"]
    }

    egress {
        from_port = 0
        to_port = 0
        protocol = "-1"
        cidr_blocks = ["0.0.0.0/0"]
    }
}
