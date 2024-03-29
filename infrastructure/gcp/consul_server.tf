resource "google_compute_instance" "consul-server" {
  count        = "${var.counts["consul-server"]}"
  name         = "consul-server-${count.index}"
  machine_type = "${var.machineTypes["consul-server"]}"
  zone         = "${var.region}-${var.zone}"
  tags         = ["${var.consulNetworkTag["dc1"]}", "consul-server", "http-server"]

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
    network    = "${module.vpc.network_self_link}"
    subnetwork = "${element(module.vpc.subnets_self_links, 0)}"

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
      "sudo apt-get -y update",
      "sudo apt-get -y install unzip curl",
      "mkdir /home/${var.userName}/consul.d",
      "mkdir /home/${var.userName}/consul.d/data",
      "curl -fs https://releases.hashicorp.com/consul/${var.consul["version"]}/consul_${var.consul["version"]}_${var.consul["downloadPath"]}.zip -o /home/${var.userName}/consul.zip",
    ]
  }

  provisioner "file" {
    connection {
      type        = "ssh"
      user        = "${var.userName}"
      private_key = "${file("/Users/${var.userName}/.ssh/id_rsa")}"
    }

    content = <<JSON
    {
      ${jsonencode("server")}: true,
      ${jsonencode("node_name")}: ${jsonencode("consul-server-${count.index}")},
      ${jsonencode("datacenter")}: ${jsonencode("dc1")},
      ${jsonencode("data_dir")}: ${jsonencode("/home/${var.userName}/consul.d/data")},
      ${jsonencode("bind_addr")}: ${jsonencode("0.0.0.0")},
      ${jsonencode("client_addr")}: ${jsonencode("0.0.0.0")},
      ${jsonencode("bootstrap_expect")}: ${var.counts["consul-server"]},
      ${jsonencode("retry_join")}: ${jsonencode("${list("provider=gce project_name=${var.userName}-test tag_value=${var.consulNetworkTag["dc2"]}")}")},
      ${jsonencode("ui")}: true,
      ${jsonencode("log_level")}: ${jsonencode("DEBUG")},
      ${jsonencode("enable_syslog")}: true,
      ${jsonencode("acl_enforce_version_8")}: false
    }
    JSON

    destination = "/home/${var.userName}/consul.d/server.json"
  }

  provisioner "file" {
    connection {
      type        = "ssh"
      user        = "${var.userName}"
      private_key = "${file("/Users/${var.userName}/.ssh/id_rsa")}"
    }

    content     = "${data.template_file.consul-systemd-server.rendered}"
    destination = "/home/${var.userName}/consul-server.service"
  }

  provisioner "remote-exec" {
    connection {
      type        = "ssh"
      user        = "${var.userName}"
      private_key = "${file("/Users/${var.userName}/.ssh/id_rsa")}"
    }

    inline = [
      "unzip /home/${var.userName}/consul.zip",
      "sudo mv /home/${var.userName}/consul /bin/",
      "sudo rm /home/${var.userName}/consul.zip",
      "sudo mv /home/${var.userName}/*.service /etc/systemd/system/",
      "sudo systemctl start consul-server",
    ]
  }
}
