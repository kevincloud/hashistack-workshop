data "template_file" "nomad-server-setup" {
    template = "${file("${path.module}/../scripts/nomad_server_install.sh")}"

    vars = {
        AWS_ACCESS_KEY = "${var.aws_access_key}"
        AWS_SECRET_KEY = "${var.aws_secret_key}"
        REGION = "${var.aws_region}"
        CONSUL_URL = "${var.consul_dl_url}"
        CONSUL_LICENSE = "${var.consul_license_key}"
        CONSUL_JOIN_KEY = "${var.consul_join_key}"
        CONSUL_JOIN_VALUE = "${var.consul_join_value}"
    }
}

resource "aws_instance" "nomad-server" {
    ami = "${data.aws_ami.ubuntu.id}"
    instance_type = "${var.instance_size}"
    key_name = "${var.key_pair}"
    vpc_security_group_ids = ["${aws_security_group.nomad-server-sg.id}"]
    user_data = "${data.template_file.nomad-server-setup.rendered}"
    subnet_id = "${aws_subnet.public-subnet.id}"
    iam_instance_profile = "${aws_iam_instance_profile.nomad-profile.id}"
    
    tags = {
        Name = "${var.unit_prefix}-nomad-server"
        TTL = "-1"
        owner = "kcochran@hashicorp.com"
    }

    depends_on = [
        "aws_instance.vault-server", 
        "aws_instance.consul-server-1",
        "aws_instance.consul-server-2",
        "aws_instance.consul-server-3"
    ]
}

resource "aws_security_group" "nomad-server-sg" {
    name = "nomad-server-sg"
    description = "webserver security group"
    vpc_id = "${aws_vpc.primary-vpc.id}"

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
        from_port = 5821
        to_port = 5825
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

    ingress {
        from_port = 0
        to_port = 0
        protocol = "-1"
        cidr_blocks = ["10.0.0.0/16"]
    }

    egress {
        from_port = 0
        to_port = 0
        protocol = "-1"
        cidr_blocks = ["0.0.0.0/0"]
    }
}

data "aws_iam_policy_document" "nomad-assume-role" {
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
        "ecr:GetAuthorizationToken",
        "ecr:BatchCheckLayerAvailability",
        "ecr:GetDownloadUrlForLayer",
        "ecr:GetRepositoryPolicy",
        "ecr:DescribeRepositories",
        "ecr:ListImages",
        "ecr:BatchGetImage",
        "ec2:DescribeInstances",
        "ssm:UpdateInstanceInformation"
    ]
  }
}

resource "aws_iam_role" "full-s3-access" {
  name               = "full-s3-role-access"
  assume_role_policy = "${data.aws_iam_policy_document.nomad-assume-role.json}"
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
