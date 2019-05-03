data "template_file" "nomad-server-setup" {
    template = "${file("${path.module}/scripts/nomad_server_install.sh")}"

    vars = {
        AWS_ACCESS_KEY = "${var.aws_access_key}"
        AWS_SECRET_KEY = "${var.aws_secret_key}"
    }
}

resource "aws_instance" "nomad-server" {
    ami = "${data.aws_ami.ubuntu.id}"
    instance_type = "t2.micro"
    key_name = "${var.key_pair}"
    vpc_security_group_ids = ["${aws_security_group.nomad-server-sg.id}"]
    user_data = "${data.template_file.nomad-server-setup.rendered}"
    iam_instance_profile = "${aws_iam_instance_profile.nomad-profile.id}"
    
    tags = {
        Name = "nomad-server"
    }
}

resource "aws_security_group" "nomad-server-sg" {
    name = "nomad-server-sg"
    description = "webserver security group"
    vpc_id = "${data.aws_vpc.primary-vpc.id}"

    ingress {
        from_port = 22
        to_port = 22
        protocol = "tcp"
        cidr_blocks = ["0.0.0.0/0"]
    }

    ingress {
        from_port = 80
        to_port = 80
        protocol = "tcp"
        cidr_blocks = ["0.0.0.0/0"]
    }

    ingress {
        from_port = 5000
        to_port = 5000
        protocol = "tcp"
        cidr_blocks = ["0.0.0.0/0"]
    }

    ingress {
        from_port = 4646
        to_port = 4648
        protocol = "tcp"
        cidr_blocks = ["0.0.0.0/0"]
    }

    ingress {
        from_port = 4648
        to_port = 4648
        protocol = "udp"
        cidr_blocks = ["0.0.0.0/0"]
    }

    egress {
        from_port = 0
        to_port = 0
        protocol = "-1"
        cidr_blocks = ["0.0.0.0/0"]
    }
}

data "aws_iam_policy_document" "assume_role" {
  statement {
    effect  = "Allow"
    actions = ["sts:AssumeRole"]

    principals {
      type        = "Service"
      identifiers = ["ec2.amazonaws.com"]
    }
  }
}

data "aws_iam_policy_document" "full-s3-access" {
  statement {
    sid       = "FullAccess"
    effect    = "Allow"
    resources = ["*"]

    actions = [
      "s3:*"
    ]
  }
}

resource "aws_iam_role" "full-s3-access" {
  name               = "full-s3-role-access"
  assume_role_policy = "${data.aws_iam_policy_document.assume_role.json}"
}

resource "aws_iam_role_policy" "full-s3-access" {
  name   = "full-s3-access"
  role   = "${aws_iam_role.full-s3-access.id}"
  policy = "${data.aws_iam_policy_document.full-s3-access.json}"
}

resource "aws_iam_instance_profile" "nomad-profile" {
  name = "full-s3-access"
  role = "${aws_iam_role.full-s3-access.name}"
}
