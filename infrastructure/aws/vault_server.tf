# provider "consul" {
#   address    = "${aws_instance.consul-server.private_ip}:80"
#   datacenter = "${var.aws_region}"
# }

data "template_file" "vault_setup" {
    template = "${file("${path.module}/../scripts/vault_server_install.sh")}"

    vars = {
        MYSQL_HOST = "${aws_db_instance.vault-mysql.address}"
        MYSQL_USER = "${var.mysql_user}"
        MYSQL_PASS = "${var.mysql_pass}"
        MYSQL_DB = "${var.mysql_database}"
        AWS_ACCESS_KEY = "${var.aws_access_key}"
        AWS_SECRET_KEY = "${var.aws_secret_key}"
        AWS_KMS_KEY_ID = "${var.aws_kms_key_id}"
        REGION = "${var.aws_region}"
        VAULT_URL = "${var.vault_dl_url}"
        VAULT_LICENSE = "${var.vault_license_key}"
        CONSUL_URL = "${var.consul_dl_url}"
        CONSUL_LICENSE = "${var.consul_license_key}"
        CONSUL_JOIN_KEY = "${var.consul_join_key}"
        CONSUL_JOIN_VALUE = "${var.consul_join_value}"
    }
}

resource "aws_instance" "vault-server" {
    ami = "${data.aws_ami.ubuntu.id}"
    instance_type = "${var.instance_size}"
    key_name = "${var.key_pair}"
    vpc_security_group_ids = ["${aws_security_group.vault-server-sg.id}"]
    user_data = "${data.template_file.vault_setup.rendered}"
    subnet_id = "${aws_subnet.public-subnet.id}"
    iam_instance_profile = "${aws_iam_instance_profile.vault-kms-unseal.id}"
    
    tags = {
        Name = "${var.unit_prefix}-vault-server"
        TTL = "-1"
        owner = "kcochran@hashicorp.com"
    }
}

resource "aws_security_group" "vault-server-sg" {
    name = "vault-server-sg"
    description = "webserver security group"
    vpc_id = "${aws_vpc.primary-vpc.id}"

    ingress {
        from_port = 22
        to_port = 22
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
        from_port = 8200
        to_port = 8200
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

resource "aws_db_subnet_group" "dbsubnets" {
    name = "main-db-subnet"
    subnet_ids = ["${aws_subnet.private-subnet.id}", "${aws_subnet.private-subnet-2.id}"]
}


resource "aws_db_instance" "vault-mysql" {
    allocated_storage = 10
    storage_type = "gp2"
    engine = "mysql"
    engine_version = "5.7"
    instance_class = "db.${var.instance_size}"
    name = "vaultdb"
    identifier = "kevinvaultdb"
    db_subnet_group_name = "${aws_db_subnet_group.dbsubnets.name}"
    vpc_security_group_ids = ["${aws_security_group.vault-mysql-sg.id}"]
    username = "${var.mysql_user}"
    password = "${var.mysql_pass}"
    skip_final_snapshot = true
}

resource "aws_security_group" "vault-mysql-sg" {
    name = "vault-mysql-sg"
    description = "mysql security group"
    vpc_id = "${aws_vpc.primary-vpc.id}"

    ingress {
        from_port = 3306
        to_port = 3306
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

data "aws_iam_policy_document" "vault-kms-unseal" {
  statement {
    sid       = "VaultKMSUnseal"
    effect    = "Allow"
    resources = ["*"]

    actions = [
      "kms:Encrypt",
      "kms:Decrypt",
      "kms:DescribeKey",
      "ec2:DescribeInstances"
    ]
  }
}

resource "aws_iam_role" "vault-kms-unseal" {
  name               = "vault-kms-role-unseal"
  assume_role_policy = "${data.aws_iam_policy_document.assume_role.json}"
}

resource "aws_iam_role_policy" "vault-kms-unseal" {
  name   = "Vault-KMS-Unseal"
  role   = "${aws_iam_role.vault-kms-unseal.id}"
  policy = "${data.aws_iam_policy_document.vault-kms-unseal.json}"
}

resource "aws_iam_instance_profile" "vault-kms-unseal" {
  name = "vault-kms-unseal"
  role = "${aws_iam_role.vault-kms-unseal.name}"
}