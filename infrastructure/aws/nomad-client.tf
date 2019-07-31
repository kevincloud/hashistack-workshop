data "template_file" "nomad-client-setup-1" {
    template = "${file("${path.module}/../scripts/nomad_client_install.sh")}"

    vars = {
        AWS_ACCESS_KEY = "${var.aws_access_key}"
        AWS_SECRET_KEY = "${var.aws_secret_key}"
        REGION = "${var.aws_region}"
        CONSUL_URL = "${var.consul_dl_url}"
        CONSUL_LICENSE = "${var.consul_license_key}"
        CONSUL_JOIN_KEY = "${var.consul_join_key}"
        CONSUL_JOIN_VALUE = "${var.consul_join_value}"
        CLIENT_NAME = "client1"
    }
}

data "template_file" "nomad-client-setup-2" {
    template = "${file("${path.module}/../scripts/nomad_client_install.sh")}"

    vars = {
        AWS_ACCESS_KEY = "${var.aws_access_key}"
        AWS_SECRET_KEY = "${var.aws_secret_key}"
        REGION = "${var.aws_region}"
        CONSUL_URL = "${var.consul_dl_url}"
        CONSUL_LICENSE = "${var.consul_license_key}"
        CONSUL_JOIN_KEY = "${var.consul_join_key}"
        CONSUL_JOIN_VALUE = "${var.consul_join_value}"
        CLIENT_NAME = "client2"
    }
}

resource "aws_instance" "nomad-client-1" {
    ami = "${data.aws_ami.ubuntu.id}"
    instance_type = "t3.small"
    key_name = "${var.key_pair}"
    vpc_security_group_ids = ["${aws_security_group.nomad-server-sg.id}"]
    user_data = "${data.template_file.nomad-client-setup-1.rendered}"
    subnet_id = "${aws_subnet.public-subnet.id}"
    iam_instance_profile = "${aws_iam_instance_profile.nomad-profile.id}"
    
    tags = {
        Name = "${var.unit_prefix}-nomad-client-1"
        TTL = "-1"
        owner = "kcochran@hashicorp.com"
    }

    depends_on = ["aws_instance.nomad-server"]
}

resource "aws_instance" "nomad-client-2" {
    ami = "${data.aws_ami.ubuntu.id}"
    instance_type = "t3.small"
    key_name = "${var.key_pair}"
    vpc_security_group_ids = ["${aws_security_group.nomad-server-sg.id}"]
    user_data = "${data.template_file.nomad-client-setup-2.rendered}"
    subnet_id = "${aws_subnet.public-subnet.id}"
    iam_instance_profile = "${aws_iam_instance_profile.nomad-profile.id}"
    
    tags = {
        Name = "${var.unit_prefix}-nomad-client-2"
        TTL = "-1"
        owner = "kcochran@hashicorp.com"
    }

    depends_on = ["aws_instance.nomad-server"]
}
