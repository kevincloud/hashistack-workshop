data "template_file" "nomad-client-setup-1" {
    template = "${file("${path.module}/scripts/nomad_client_install.sh")}"

    vars = {
        AWS_ACCESS_KEY = "${var.aws_access_key}"
        AWS_SECRET_KEY = "${var.aws_secret_key}"
        NOMAD_SERVER = "${aws_instance.nomad-server.private_ip}"
        CLIENT_NAME = "client1"
    }
}

data "template_file" "nomad-client-setup-2" {
    template = "${file("${path.module}/scripts/nomad_client_install.sh")}"

    vars = {
        AWS_ACCESS_KEY = "${var.aws_access_key}"
        AWS_SECRET_KEY = "${var.aws_secret_key}"
        NOMAD_SERVER = "${aws_instance.nomad-server.private_ip}"
        CLIENT_NAME = "client2"
    }
}

resource "aws_instance" "nomad-client-1" {
    ami = "${data.aws_ami.ubuntu.id}"
    instance_type = "t2.micro"
    key_name = "${var.key_pair}"
    vpc_security_group_ids = ["${aws_security_group.nomad-server-sg.id}"]
    user_data = "${data.template_file.nomad-client-setup-1.rendered}"
    iam_instance_profile = "${aws_iam_instance_profile.nomad-profile.id}"
    
    tags = {
        Name = "nomad-client-1"
    }
}

resource "aws_instance" "nomad-client-2" {
    ami = "${data.aws_ami.ubuntu.id}"
    instance_type = "t2.micro"
    key_name = "${var.key_pair}"
    vpc_security_group_ids = ["${aws_security_group.nomad-server-sg.id}"]
    user_data = "${data.template_file.nomad-client-setup-2.rendered}"
    iam_instance_profile = "${aws_iam_instance_profile.nomad-profile.id}"
    
    tags = {
        Name = "nomad-client-2"
    }
}
