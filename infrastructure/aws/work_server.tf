data "template_file" "work_install" {
    template = "${file("${path.module}/scripts/work_install.sh")}"

    vars = {
        VAULT_SERVER = "${aws_instance.vault-server.public_ip}"
        ROOT_PASSWORD = "${var.root_pass}"
        AWS_ACCESS_KEY = "${var.aws_access_key}"
        AWS_SECRET_KEY = "${var.aws_secret_key}"
        AWS_REGION = "${var.aws_region}"
        REPO_URL = "${aws_ecr_repository.ecr-product-app.repository_url}"
        S3_BUCKET = "${aws_s3_bucket.staticimg.id}"
        GIT_USER = "${var.git_user}"
        GIT_TOKEN = "${var.git_token}"
    }
}

resource "aws_instance" "working-env" {
    ami = "${data.aws_ami.ubuntu.id}"
    instance_type = "t2.micro"
    key_name = "${var.key_pair}"
    vpc_security_group_ids = ["${aws_security_group.working-env-sg.id}"]
    user_data = "${data.template_file.work_install.rendered}"
    subnet_id = "${aws_subnet.public-subnet.id}"

    tags = {
        Name = "cust-mgmt-work"
    }

    depends_on = [
        "aws_instance.vault-server",
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

data "aws_iam_policy_document" "assume-role-s3" {
  statement {
    effect  = "Allow"
    actions = ["sts:AssumeRole"]

    principals {
      type        = "Service"
      identifiers = ["ec2.amazonaws.com"]
    }
  }
}

data "aws_iam_policy_document" "work-s3-setup" {
  statement {
    sid       = "WorkS3Setup"
    effect    = "Allow"
    resources = ["*"]

    actions = [
      "s3:*"
    ]
  }
}

resource "aws_iam_role" "work-s3-setup" {
  name               = "work-s3-setup"
  assume_role_policy = "${data.aws_iam_policy_document.assume-role-s3.json}"
}

resource "aws_iam_role_policy" "work-s3-setup" {
  name   = "work-s3-setup"
  role   = "${aws_iam_role.work-s3-setup.id}"
  policy = "${data.aws_iam_policy_document.work-s3-setup.json}"
}

resource "aws_iam_instance_profile" "work-s3-setup" {
  name = "work-s3-setup"
  role = "${aws_iam_role.work-s3-setup.name}"
}
