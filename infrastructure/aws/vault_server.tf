# provider "consul" {
#   address    = "${aws_instance.consul-server.private_ip}:80"
#   datacenter = "dc1"
# }

data "template_file" "vault_setup" {
    template = "${file("${path.module}/../scripts/vault_server_install.sh")}"

    vars = {
        MYSQL_HOST = "${aws_db_instance.vault-mysql.address}:${aws_db_instance.vault-mysql.port}"
        AWS_ACCESS_KEY = "${var.aws_access_key}"
        AWS_SECRET_KEY = "${var.aws_secret_key}"
        CONSUL_IP = "${aws_instance.consul-server.private_ip}"
    }
}

resource "aws_instance" "vault-server" {
    ami = "${data.aws_ami.ubuntu.id}"
    instance_type = "t2.micro"
    key_name = "${var.key_pair}"
    vpc_security_group_ids = ["${aws_security_group.vault-server-sg.id}"]
    user_data = "${data.template_file.vault_setup.rendered}"
    subnet_id = "${aws_subnet.public-subnet.id}"
    iam_instance_profile = "${aws_iam_instance_profile.vault-kms-unseal.id}"
    
    tags = {
        Name = "cust-mgmt-web"
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
    instance_class = "db.t2.micro"
    name = "vaultdb"
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
      "kms:DescribeKey"
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