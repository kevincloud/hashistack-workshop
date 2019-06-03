# I use templates to write systemd files dynamically, you will see these referenced in the specific *.tf files where the resource is being provisioned
data "template_file" "vault-systemd-client" {
  template = "${file("${var.localPath}/templates/systemd/vault.tpl")}"

  vars {
    userName = "${var.userName}"
  }
}

data "template_file" "consul-systemd-server" {
  template = "${file("${var.localPath}/templates/systemd/consul-server.tpl")}"

  vars {
    userName = "${var.userName}"
  }
}

data "template_file" "consul-systemd-client" {
  template = "${file("${var.localPath}/templates/systemd/consul-client.tpl")}"

  vars {
    userName = "${var.userName}"
  }
}

data "template_file" "consul-systemd-template" {
  template = "${file("${var.localPath}/templates/systemd/consul-template.tpl")}"

  vars {
    userName = "${var.userName}"
  }
}

data "template_file" "nomad-systemd-client" {
  template = "${file("${var.localPath}/templates/systemd/nomad.tpl")}"

  vars {
    userName = "${var.userName}"
    hclPath  = "client"
  }
}

data "template_file" "nomad-systemd-server" {
  template = "${file("${var.localPath}/templates/systemd/nomad.tpl")}"

  vars {
    userName = "${var.userName}"
    hclPath  = "server"
  }
}

#You will need to set your GOOGLE_CREDENTIALS env variable
provider "google" {
  project = "${var.projectName}"
  region  = "${var.region}"
  zone    = "${var.region}-a"
}

module "vpc" {
  source  = "terraform-google-modules/network/google"
  version = "0.6.0"

  project_id   = "${var.projectName}"
  network_name = "${var.networkName}"

  subnets = [
    {
      subnet_name           = "primary"
      subnet_ip             = "10.10.10.0/28"
      subnet_region         = "${var.region}"
      subnet_private_access = "true"
      subnet_flow_logs      = "true"
    },
  ]

  secondary_ranges = {
    primary = []
  }
}

#FIREWALLS: VAULT, NOMAD, & CONSUL (TCP)
resource "google_compute_firewall" "allow-tcp" {
  provider = "google"
  name     = "allow-tcp-${var.networkName}"
  network  = "${module.vpc.network_self_link}"

  allow {
    protocol = "tcp"
    ports    = ["8200", "8201", "8300", "8301", "8302", "8500", "4646", "4647", "4648"]
  }
}

#FIREWALLS: VAULT, NOMAD, & CONSUL (UDP)
resource "google_compute_firewall" "allow-udp" {
  provider = "google"
  name     = "allow-udp-${var.networkName}"
  network  = "${module.vpc.network_self_link}"

  allow {
    protocol = "udp"
    ports    = ["8301", "8302", "4648"]
  }
}

#FIREWALLS: HTTP/S ++ :8080
resource "google_compute_firewall" "allow-service-access" {
  provider = "google"
  name     = "http-${var.networkName}"
  network  = "${module.vpc.network_self_link}"

  allow {
    protocol = "tcp"
    ports    = ["80", "443", "8080"]
  }
}

#FIREWALLS: SSH
resource "google_compute_firewall" "allow-ssh" {
  provider = "google"
  name     = "ssh-${var.networkName}"
  network  = "${module.vpc.network_self_link}"

  allow {
    protocol = "tcp"
    ports    = ["22"]
  }
}
