provider "aws" {
    access_key = "${var.aws_access_key}"
    secret_key = "${var.aws_secret_key}"
    region = "${var.region}"
}

data "template_file" "auth_setup" {
    template = "${file("${path.module}/scripts/userdata.sh")}"

    vars = {
        GITUSER = "${var.git_username}"
        GITTOKEN = "${var.git_token}"
        AWS_ACCESS_KEY = "${var.aws_access_key}"
        AWS_SECRET_KEY = "${var.aws_secret_key}"
        S3_BUCKET = "${aws_s3_bucket.staticimg.id}"
        // IP_ADDRESS = "${aws_instance.kevin-php-server.public_ip}"
        S3_REGION = "${var.region}"
    }
}

resource "aws_instance" "kevin-php-server" {
    ami = "${data.aws_ami.ubuntu.id}"
    instance_type = "t2.micro"
    key_name = "${var.key_pair}"
    vpc_security_group_ids = ["${aws_security_group.kevin-php-sg.id}"]
    user_data = "${data.template_file.auth_setup.rendered}"
    
    tags = {
        Name = "kevin-php-test"
    }
}

resource "aws_security_group" "kevin-php-sg" {
    name = "kevin-php-sg"
    description = "kevin-php security group"
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
        from_port = 5821
        to_port = 5821
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

resource "aws_s3_bucket" "staticimg" {
    bucket = "hc-workshop-2.0-static-images"
    force_destroy = true
}

resource "aws_s3_bucket_policy" "staticimgpol" {
    bucket = "${aws_s3_bucket.staticimg.id}"

    policy = <<POLICY
{
  "Version": "2012-10-17",
  "Id": "ImageBucketPolicy",
  "Statement": [
    {
      "Sid": "IPAllow",
      "Effect": "Deny",
      "Principal": "*",
      "Action": ["s3:GetObject"],
      "Resource": "arn:aws:s3:::${aws_s3_bucket.staticimg.id}/*",
      "Condition": {
         "IpAddress": {"aws:SourceIp": "${aws_instance.kevin-php-server.private_ip}"}
      }
    }
  ]
}
POLICY
}