resource "google_compute_instance" "nomad-client" {
  depends_on   = ["google_compute_instance.nomad-server"]
  count        = "${var.counts["nomad-client"]}"
  name         = "nomad-client-${count.index}"
  machine_type = "${var.machineTypes["nomad-client"]}"
  zone         = "${var.region}-a"
  tags         = ["workshirt-cluster-dc1", "nomad-client", "consul-client", "http-server"]

  metadata {
    sshKeys = "${var.userName}:${file("/Users/${var.userName}/.ssh/id_rsa.pub")}"
  }

  boot_disk {
    initialize_params {
      image = "${var.imageSpec}"
    }
  }

  service_account {
    email  = "${var.serviceAccount["email"]}"
    scopes = "${list(var.serviceAccount["scopes"])}"
  }

  network_interface {
    network = "default"

    access_config {
      // Include this section to give the VM an external ip address
    }
  }

  provisioner "remote-exec" {
    connection {
      type        = "ssh"
      user        = "${var.userName}"
      private_key = "${file("/Users/${var.userName}/.ssh/id_rsa")}"
    }

    inline = [
      "sudo apt-get -y remove docker docker-engine docker.io containerd runc",
      "sudo apt-get -y update",
      "sudo apt-get -y install unzip apt-transport-https ca-certificates curl gnupg2 software-properties-common nginx",
      "sudo rm /etc/nginx/conf.d/*.conf",
      "sudo rm /etc/nginx/sites-available/*",
      "curl -fs https://releases.hashicorp.com/consul-template/0.19.5/consul-template_0.19.5_linux_amd64.zip -o /home/${var.userName}/consul-template.zip",
      "curl -fsSL https://download.docker.com/linux/debian/gpg | sudo apt-key add -",
      "sudo add-apt-repository \"deb [arch=amd64] https://download.docker.com/linux/debian $(lsb_release -cs) stable\"",
      "sudo apt-get -y update",
      "sudo apt-get -y install docker-ce docker-ce-cli containerd.io",
      "sudo groupadd docker",
      "sudo usermod -aG docker ${var.userName}",
      "sudo usermod -aG root ${var.userName}",

      # create docker volumes until drivers are available
      "sudo docker volume create --name ghostcontent",

      "sudo docker volume create --name mongodata",
      "mkdir /home/${var.userName}/templates",
      "mkdir /home/${var.userName}/nomad.d",
      "mkdir /home/${var.userName}/opt",
      "mkdir /home/${var.userName}/opt/nomad",
      "mkdir /home/${var.userName}/consul.d",
      "mkdir /home/${var.userName}/consul.d/data",
    ]
  }

  provisioner "file" {
    connection {
      type        = "ssh"
      user        = "${var.userName}"
      private_key = "${file("/Users/${var.userName}/.ssh/id_rsa")}"
    }

    source      = "${var.localPath}/deployment/templates/nginx.conf.tpl"
    destination = "/home/${var.userName}/templates/nginx.conf.tpl"
  }

  provisioner "file" {
    connection {
      type        = "ssh"
      user        = "${var.userName}"
      private_key = "${file("/Users/${var.userName}/.ssh/id_rsa")}"
    }

    content = <<HCL
    datacenter = "dc1"
    data_dir = "/home/${var.userName}/opt/nomad"
    client {
      enabled = true
    }
    HCL

    destination = "/home/${var.userName}/nomad.d/client.hcl"
  }

  provisioner "file" {
    connection {
      type        = "ssh"
      user        = "${var.userName}"
      private_key = "${file("/Users/${var.userName}/.ssh/id_rsa")}"
    }

    source      = "${var.localPath}/deployment/binaries/consul_${var.consulVersion}_linux_amd64.zip"
    destination = "/home/${var.userName}/consul_${var.consulVersion}_linux_amd64.zip"
  }

  provisioner "file" {
    connection {
      type        = "ssh"
      user        = "${var.userName}"
      private_key = "${file("/Users/${var.userName}/.ssh/id_rsa")}"
    }

    source      = "${var.localPath}/deployment/binaries/nomad-enterprise_0.8.6+ent_linux_amd64.zip"
    destination = "/home/${var.userName}/nomad-enterprise_0.8.6+ent_linux_amd64.zip"
  }

  provisioner "file" {
    connection {
      type        = "ssh"
      user        = "${var.userName}"
      private_key = "${file("/Users/${var.userName}/.ssh/id_rsa")}"
    }

    source      = "${var.localPath}/deployment/binaries/documentation_agent_.0.0.1_linux_amd64.zip"
    destination = "/home/${var.userName}/documentation_agent_.0.0.1_linux_amd64.zip"
  }

  provisioner "file" {
    connection {
      type        = "ssh"
      user        = "${var.userName}"
      private_key = "${file("/Users/${var.userName}/.ssh/id_rsa")}"
    }

    source      = "${var.localPath}/deployment/binaries/simpleApiForNomadDemo_0.0.1_linux_amd64.zip"
    destination = "/home/${var.userName}/simpleApiForNomadDemo_0.0.1_linux_amd64.zip"
  }

  provisioner "file" {
    connection {
      type        = "ssh"
      user        = "${var.userName}"
      private_key = "${file("/Users/${var.userName}/.ssh/id_rsa")}"
    }

    content = <<JSON
    {
      ${jsonencode("server")}: false,
      ${jsonencode("node_name")}: ${jsonencode("nomad-client-${count.index}")},
      ${jsonencode("datacenter")}: ${jsonencode("dc1")},
      ${jsonencode("data_dir")}: ${jsonencode("/home/${var.userName}/consul.d/data")},
      ${jsonencode("bind_addr")}: ${jsonencode("0.0.0.0")},
      ${jsonencode("client_addr")}: ${jsonencode("0.0.0.0")},
      ${jsonencode("advertise_addr")}: ${jsonencode("${self.network_interface.0.access_config.0.nat_ip}")},
      ${jsonencode("retry_join")}: ${jsonencode("${list("provider=gce project_name=${var.userName}-test tag_value=workshirt-cluster-dc1")}")},
      ${jsonencode("log_level")}: ${jsonencode("DEBUG")},
      ${jsonencode("enable_syslog")}: true,
      ${jsonencode("acl_enforce_version_8")}: false
      }
      JSON

    destination = "/home/${var.userName}/consul.d/client.json"
  }

  provisioner "file" {
    connection {
      type        = "ssh"
      user        = "${var.userName}"
      private_key = "${file("/Users/${var.userName}/.ssh/id_rsa")}"
    }

    content     = "${data.template_file.consul-template-systemd.rendered}"
    destination = "/home/${var.userName}/consul-template.service"
  }

  provisioner "file" {
    connection {
      type        = "ssh"
      user        = "${var.userName}"
      private_key = "${file("/Users/${var.userName}/.ssh/id_rsa")}"
    }

    content     = "${data.template_file.consul-systemd-client.rendered}"
    destination = "/home/${var.userName}/consul-client.service"
  }

  provisioner "file" {
    connection {
      type        = "ssh"
      user        = "${var.userName}"
      private_key = "${file("/Users/${var.userName}/.ssh/id_rsa")}"
    }

    content     = "${data.template_file.nomad-systemd-client.rendered}"
    destination = "/home/${var.userName}/nomad.service"
  }

  provisioner "remote-exec" {
    connection {
      type        = "ssh"
      user        = "${var.userName}"
      private_key = "${file("/Users/${var.userName}/.ssh/id_rsa")}"
    }

    inline = [
      "unzip /home/${var.userName}/consul-template.zip",
      "unzip /home/${var.userName}/nomad-enterprise_0.8.6+ent_linux_amd64.zip",
      "unzip /home/${var.userName}/consul_${var.consulVersion}_linux_amd64.zip",
      "unzip /home/${var.userName}/documentation_agent_.0.0.1_linux_amd64.zip",
      "unzip /home/${var.userName}/simpleApiForNomadDemo_0.0.1_linux_amd64.zip",
      "sudo mv /home/${var.userName}/consul-template /bin/",
      "sudo mv /home/${var.userName}/consul /bin/",
      "sudo mv /home/${var.userName}/nomad /bin/",
      "sudo mv /home/${var.userName}/documentation-agent /bin/",
      "rm /home/${var.userName}/consul-template.zip",
      "rm /home/${var.userName}/nomad-enterprise_0.8.6+ent_linux_amd64.zip",
      "rm /home/${var.userName}/consul_${var.consulVersion}_linux_amd64.zip",
      "rm /home/${var.userName}/documentation_agent_.0.0.1_linux_amd64.zip",
      "rm /home/${var.userName}/simpleApiForNomadDemo_0.0.1_linux_amd64.zip",
      "sudo mv /home/${var.userName}/*.service /etc/systemd/system/",
      "sudo systemctl start consul-client",
      "sudo systemctl start nomad",
      "sudo systemctl start consul-template",
    ]
  }
}
