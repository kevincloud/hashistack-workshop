data "template_file" "consul-server-setup" {
    template = "${file("${path.module}/../scripts/consul_server_install.sh")}"

    vars = {
        AWS_ACCESS_KEY = "${var.aws_access_key}"
        AWS_SECRET_KEY = "${var.aws_secret_key}"
    }
}

resource "aws_instance" "consul-server" {
    ami = "${data.aws_ami.ubuntu.id}"
    instance_type = "t2.micro"
    key_name = "${var.key_pair}"
    vpc_security_group_ids = ["${aws_security_group.consul-server-sg.id}"]
    user_data = "${data.template_file.consul-server-setup.rendered}"
    subnet_id = "${aws_subnet.public-subnet.id}"
    iam_instance_profile = "${aws_iam_instance_profile.consul-profile.id}"
    
    tags = {
        Name = "consul-server"
    }
}

resource "aws_security_group" "consul-server-sg" {
    name = "consul-server-sg"
    description = "webserver security group"
    vpc_id = "${aws_vpc.primary-vpc.id}"

    ingress {
        from_port = 22
        to_port = 22
        protocol = "tcp"
        cidr_blocks = ["0.0.0.0/0"]
    }

    ingress {
        from_port = 8500
        to_port = 8500
        protocol = "tcp"
        cidr_blocks = ["0.0.0.0/0"]
    }

    ingress {
        from_port = 8300
        to_port = 8303
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

data "aws_iam_policy_document" "consul-assume-role" {
  statement {
    effect  = "Allow"
    actions = ["sts:AssumeRole"]

    principals {
      type        = "Service"
      identifiers = ["ec2.amazonaws.com"]
    }
  }
}

data "aws_iam_policy_document" "consul-s3-access" {
  statement {
    sid       = "FullAccess"
    effect    = "Allow"
    resources = ["*"]

    actions = [
      "s3:*"
    ]
  }
}

resource "aws_iam_role" "consul-s3-access" {
  name               = "consul-s3-role-access"
  assume_role_policy = "${data.aws_iam_policy_document.consul-assume-role.json}"
}

resource "aws_iam_role_policy" "consul-s3-access" {
  name   = "consul-s3-access"
  role   = "${aws_iam_role.consul-s3-access.id}"
  policy = "${data.aws_iam_policy_document.consul-s3-access.json}"
}

resource "aws_iam_instance_profile" "consul-profile" {
  name = "consul-s3-access"
  role = "${aws_iam_role.consul-s3-access.name}"
}
