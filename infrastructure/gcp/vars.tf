// RUNTIME: You will be prompted
// The GCP region you want to deploy your primary Vault cluster in (i.e. us-east4)
variable "region" {
  type = "string"
}

variable "zone" {
  type = "string"
}

// The name of your project in GCP
variable "projectName" {
  type = "string"
}

variable "networkName" {
  type = "string"
}

// The username for GCP without the @whatever.com part (i.e. jjordan instead of jjordan@hashicorp.com)
variable "userName" {
  type = "string"
}

// The file path to the root of this directory, you can use the pwd command before running terraform plan or apply for ease
variable "localPath" {
  type = "string"
}

// This is a map, so the values needed are { email = "someServiceAccount" scopes = "cloud-platform" }
variable "serviceAccount" {
  type = "map"
}

// This should match the name of the pre-configured key-ring you've set up
variable "keyRing" {
  type = "string"
}

// This should match the name of the pre-configured cryptoKey assc with your keyring
variable "cryptoKey" {
  type = "string"
}

// This is how consul will auto discover everything
variable "consulNetworkTag" {
  type = "map"

  default = {
    dc1 = "discover-dc1"
    dc2 = "discover-dc2"
  }
}

// CONFIGURABLE: which means you can change them and they will automatically be used
variable "consul" {
  type = "map"

  default = {
    "version"      = "1.5.1"
    "downloadPath" = "linux_amd64"
  }
}

variable "vault" {
  type = "map"

  default = {
    "version"      = "1.1.2"
    "downloadPath" = "linux_amd64"
  }
}

variable "nomad" {
  type = "map"

  default = {
    "version"      = "0.9.3"
    "downloadPath" = "linux_amd64"
  }
}

variable "consul_template" {
  type = "map"

  default = {
    "version"      = "0.20.0"
    "downloadPath" = "linux_amd64"
  }
}

// You shouldn't need to change this unless you want to use larger versions, this information comes directly from our deployment guide and for the POC I see little reason to use larger instance types.
variable "machineTypes" {
  type = "map"

  default = {
    "consul-server" = "n1-standard-8"
    "vault-client"  = "n1-standard-2"
    "nomad-server"  = "n1-standard-8"
    "nomad-client"  = "n1-standard-2"
  }
}

// Standard cluster count, only change if you feel the need to
variable "counts" {
  type = "map"

  default = {
    "consul-server" = 3
    "vault-client"  = 2
    "nomad-server"  = 3
    "nomad-client"  = 3
  }
}

// I like debian for linux, but it's ultimately up to you to change this. Be careful though, the provisioning scripts I've written may not be compatable depending on the linux OS version you deploy into your cluster.
variable "imageSpec" {
  type    = "string"
  default = "debian-cloud/debian-9"
}
