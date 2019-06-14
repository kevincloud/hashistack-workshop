data "template_file" "nomad-client-setup-1" {
    template = "${file("${path.module}/../scripts/nomad_client_install.sh")}"

    vars = {
        AWS_ACCESS_KEY = "${var.aws_access_key}"
        AWS_SECRET_KEY = "${var.aws_secret_key}"
        AWS_REGION = "${var.aws_region}"
        CONSUL_IP = "${aws_instance.consul-server.private_ip}"
        CLIENT_NAME = "client1"
    }
}

data "template_file" "nomad-client-setup-2" {
    template = "${file("${path.module}/../scripts/nomad_client_install.sh")}"

    vars = {
        AWS_ACCESS_KEY = "${var.aws_access_key}"
        AWS_SECRET_KEY = "${var.aws_secret_key}"
        AWS_REGION = "${var.aws_region}"
        CONSUL_IP = "${aws_instance.consul-server.private_ip}"
        CLIENT_NAME = "client2"
    }
}

resource "aws_instance" "nomad-client-1" {
    ami = "${data.aws_ami.ubuntu.id}"
    instance_type = "t2.micro"
    key_name = "${var.key_pair}"
    vpc_security_group_ids = ["${aws_security_group.nomad-server-sg.id}"]
    user_data = "${data.template_file.nomad-client-setup-1.rendered}"
    subnet_id = "${aws_subnet.public-subnet.id}"
    iam_instance_profile = "${aws_iam_instance_profile.nomad-profile.id}"
    
    tags = {
        Name = "nomad-client-1"
    }

    depends_on = ["aws_instance.nomad-server"]
}

resource "aws_instance" "nomad-client-2" {
    ami = "${data.aws_ami.ubuntu.id}"
    instance_type = "t2.micro"
    key_name = "${var.key_pair}"
    vpc_security_group_ids = ["${aws_security_group.nomad-server-sg.id}"]
    user_data = "${data.template_file.nomad-client-setup-2.rendered}"
    subnet_id = "${aws_subnet.public-subnet.id}"
    iam_instance_profile = "${aws_iam_instance_profile.nomad-profile.id}"
    
    tags = {
        Name = "nomad-client-2"
    }

    depends_on = ["aws_instance.nomad-server"]
}
