// 3 nomad server instances
resource "google_compute_instance" "nomad-server" {
  depends_on   = ["google_compute_instance.vault-client"]
  count        = "${var.counts["nomad-server"]}"
  name         = "nomad-server-${count.index}"
  machine_type = "${var.machineTypes["nomad-server"]}"
  zone         = "${var.region}-${var.zone}"
  tags         = ["${var.consulNetworkTag["dc1"]}", "nomad-server", "consul-client", "http-server"]

  metadata {
    sshKeys = "${var.userName}:${file("~/.ssh/id_rsa.pub")}"
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
      "sudo usermod -aG root ${var.userName}",
      "curl -fs https://releases.hashicorp.com/consul/${var.consul["version"]}/consul_${var.consul["version"]}_${var.consul["downloadPath"]}.zip -o /home/${var.userName}/consul.zip",
      "curl -fs https://releases.hashicorp.com/nomad/${var.nomad["version"]}/nomad_${var.nomad["version"]}_${var.nomad["downloadPath"]}.zip -o /home/${var.userName}/nomad.zip",
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

    content = <<HCL
    datacenter = "dc1"
    data_dir = "/home/${var.userName}/opt/nomad"
    server {
      enabled = true
      bootstrap_expect = ${var.counts["nomad-server"]}
    }
    HCL

    destination = "/home/${var.userName}/nomad.d/server.hcl"
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
        ${jsonencode("node_name")}: ${jsonencode("nomad-server-${count.index}")},
        ${jsonencode("datacenter")}: ${jsonencode("dc1")},
        ${jsonencode("data_dir")}: ${jsonencode("/home/${var.userName}/consul.d/data")},
        ${jsonencode("retry_join")}: ${jsonencode("${list("provider=gce project_name=${var.projectName} tag_value=${var.consulNetworkTag["dc1"]}")}")},
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

    content     = "${data.template_file.consul-systemd-client.rendered}"
    destination = "/home/${var.userName}/consul-client.service"
  }

  provisioner "file" {
    connection {
      type        = "ssh"
      user        = "${var.userName}"
      private_key = "${file("/Users/${var.userName}/.ssh/id_rsa")}"
    }

    content     = "${data.template_file.nomad-systemd-server.rendered}"
    destination = "/home/${var.userName}/nomad.service"
  }

  provisioner "remote-exec" {
    connection {
      type        = "ssh"
      user        = "${var.userName}"
      private_key = "${file("/Users/${var.userName}/.ssh/id_rsa")}"
    }

    inline = [
      "unzip /home/${var.userName}/nomad.zip",
      "unzip /home/${var.userName}/consul.zip",
      "sudo mv /home/${var.userName}/consul /bin/",
      "sudo mv /home/${var.userName}/nomad /bin/",
      "rm /home/${var.userName}/nomad.zip",
      "rm /home/${var.userName}/consul.zip",
      "sudo mv /home/${var.userName}/*.service /etc/systemd/system/",
      "sudo systemctl start consul-client",
      "sudo systemctl start nomad",
    ]
  }
}
