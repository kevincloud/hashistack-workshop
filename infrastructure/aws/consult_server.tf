data "template_file" "consul-server-setup-1" {
    template = "${file("${path.module}/../scripts/consul_server_install.sh")}"

    vars = {
        AWS_ACCESS_KEY = "${var.aws_access_key}"
        AWS_SECRET_KEY = "${var.aws_secret_key}"
        REGION = "${var.aws_region}"
        CONSUL_SERVER_NAME = "consul-server-1"
        CONSUL_URL = "${var.consul_dl_url}"
        CONSUL_LICENSE = "${var.consul_license_key}"
        CONSUL_JOIN_KEY = "${var.consul_join_key}"
        CONSUL_JOIN_VALUE = "${var.consul_join_value}"
    }
}

data "template_file" "consul-server-setup-2" {
    template = "${file("${path.module}/../scripts/consul_server_install.sh")}"

    vars = {
        AWS_ACCESS_KEY = "${var.aws_access_key}"
        AWS_SECRET_KEY = "${var.aws_secret_key}"
        REGION = "${var.aws_region}"
        CONSUL_SERVER_NAME = "consul-server-2"
        CONSUL_URL = "${var.consul_dl_url}"
        CONSUL_LICENSE = "${var.consul_license_key}"
        CONSUL_JOIN_KEY = "${var.consul_join_key}"
        CONSUL_JOIN_VALUE = "${var.consul_join_value}"
    }
}

data "template_file" "consul-server-setup-3" {
    template = "${file("${path.module}/../scripts/consul_server_install.sh")}"

    vars = {
        AWS_ACCESS_KEY = "${var.aws_access_key}"
        AWS_SECRET_KEY = "${var.aws_secret_key}"
        REGION = "${var.aws_region}"
        CONSUL_SERVER_NAME = "consul-server-3"
        CONSUL_URL = "${var.consul_dl_url}"
        CONSUL_LICENSE = "${var.consul_license_key}"
        CONSUL_JOIN_KEY = "${var.consul_join_key}"
        CONSUL_JOIN_VALUE = "${var.consul_join_value}"
    }
}

resource "aws_instance" "consul-server-1" {
    ami = "${data.aws_ami.ubuntu.id}"
    instance_type = "${var.instance_size}"
    key_name = "${var.key_pair}"
    vpc_security_group_ids = ["${aws_security_group.consul-server-sg.id}"]
    user_data = "${data.template_file.consul-server-setup-1.rendered}"
    subnet_id = "${aws_subnet.public-subnet.id}"
    iam_instance_profile = "${aws_iam_instance_profile.consul-profile.id}"
    
    tags = {
        Name = "${var.unit_prefix}-consul-server-1"
        TTL = "-1"
        owner = "kcochran@hashicorp.com"
        "${var.consul_join_key}" = "${var.consul_join_value}"
    }
}

resource "aws_instance" "consul-server-2" {
    ami = "${data.aws_ami.ubuntu.id}"
    instance_type = "${var.instance_size}"
    key_name = "${var.key_pair}"
    vpc_security_group_ids = ["${aws_security_group.consul-server-sg.id}"]
    user_data = "${data.template_file.consul-server-setup-2.rendered}"
    subnet_id = "${aws_subnet.public-subnet.id}"
    iam_instance_profile = "${aws_iam_instance_profile.consul-profile.id}"
    
    tags = {
        Name = "${var.unit_prefix}-consul-server-2"
        TTL = "-1"
        owner = "kcochran@hashicorp.com"
        "${var.consul_join_key}" = "${var.consul_join_value}"
    }
}

resource "aws_instance" "consul-server-3" {
    ami = "${data.aws_ami.ubuntu.id}"
    instance_type = "${var.instance_size}"
    key_name = "${var.key_pair}"
    vpc_security_group_ids = ["${aws_security_group.consul-server-sg.id}"]
    user_data = "${data.template_file.consul-server-setup-3.rendered}"
    subnet_id = "${aws_subnet.public-subnet.id}"
    iam_instance_profile = "${aws_iam_instance_profile.consul-profile.id}"
    
    tags = {
        Name = "${var.unit_prefix}-consul-server-3"
        TTL = "-1"
        owner = "kcochran@hashicorp.com"
        "${var.consul_join_key}" = "${var.consul_join_value}"
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

data "aws_iam_policy_document" "consul-tag-access" {
  statement {
    sid       = "FullAccess"
    effect    = "Allow"
    resources = ["*"]

    actions = [
      "ec2:DescribeInstances"
    ]
  }
}

resource "aws_iam_role" "consul-tag-access" {
  name               = "consul-tag-role-access"
  assume_role_policy = "${data.aws_iam_policy_document.consul-assume-role.json}"
}

resource "aws_iam_role_policy" "consul-tag-access" {
  name   = "consul-s3-access"
  role   = "${aws_iam_role.consul-tag-access.id}"
  policy = "${data.aws_iam_policy_document.consul-tag-access.json}"
}

resource "aws_iam_instance_profile" "consul-profile" {
  name = "consul-tag-access"
  role = "${aws_iam_role.consul-tag-access.name}"
}
