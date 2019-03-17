data "template_file" "work_setup" {
    template = "${file("${path.module}/scripts/work_setup.sh")}"

    vars = {
        VAULT_SERVER = "${aws_instance.vault-server.public_ip}"
        ROOT_PASSWORD = "${var.root_pass}"
        AWS_ACCESS_KEY = "${var.aws_access_key}"
        AWS_SECRET_KEY = "${var.aws_secret_key}"
    }
}

data "template_file" "app_creds" {
    template = "${file("${path.module}/scripts/appcreds.py")}"

    vars = {
        VAULT_SERVER = "${aws_instance.vault-server.public_ip}"
    }
}

data "template_file" "cust_app" {
    template = "${file("${path.module}/scripts/cust-app.py")}"

    vars = {
        VAULT_SERVER = "${aws_instance.vault-server.public_ip}"
    }
}

resource "aws_instance" "working-env" {
    ami = "${data.aws_ami.ubuntu.id}"
    instance_type = "t2.micro"
    key_name = "${var.key_pair}"
    vpc_security_group_ids = ["${aws_security_group.working-env-sg.id}"]
    user_data = "${data.template_file.work_setup.rendered}"
    subnet_id = "${aws_subnet.public-subnet.id}"

    provisioner "file" {
        source      = "scripts/requirements.txt"
        destination = "/root/img/requirements.txt"

        connection {
            type     = "ssh"
            user     = "root"
            password = "${var.root_pass}"
        }
    }

    provisioner "file" {
        source      = "scripts/Dockerfile-api1"
        destination = "/root/img/Dockerfile"

        connection {
            type     = "ssh"
            user     = "root"
            password = "${var.root_pass}"
        }
    }

    provisioner "file" {
        source      = "scripts/customer-load.py"
        destination = "/root/customer-load.py"

        connection {
            type     = "ssh"
            user     = "root"
            password = "${var.root_pass}"
        }
    }

    provisioner "file" {
        content      = "${data.template_file.app_creds.rendered}"
        destination = "/root/appcreds.py"

        connection {
            type     = "ssh"
            user     = "root"
            password = "${var.root_pass}"
        }
    }

    provisioner "file" {
        content      = "${data.template_file.cust_app.rendered}"
        destination = "/root/img/cust-app.py"

        connection {
            type     = "ssh"
            user     = "root"
            password = "${var.root_pass}"
        }
    }

    provisioner "file" {
        source      = "scripts/work_config.sh"
        destination = "/root/work_config.sh"

        connection {
            type     = "ssh"
            user     = "root"
            password = "${var.root_pass}"
        }
    }

    provisioner "remote-exec" {
        inline = [
            "chmod +x /root/work_config.sh",
            "/root/work_config.sh"
        ]

        connection {
            type     = "ssh"
            user     = "root"
            password = "${var.root_pass}"
        }
    }
    
    tags = {
        Name = "cust-mgmt-work"
    }

    depends_on = [
        "aws_instance.vault-server",
        "aws_dynamodb_table.customer-data-table",
        "aws_dynamodb_table.customer-data-table",
        "aws_dynamodb_table.product-data-table"
    ]
}

resource "aws_security_group" "working-env-sg" {
    name = "working-env-sg"
    description = "work server security group"
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
