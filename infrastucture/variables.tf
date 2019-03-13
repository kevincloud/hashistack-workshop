variable "aws_access_key" {
    description = "AWS Access Key"
}

variable "aws_secret_key" {
    description = "AWS Secret Key"
}

variable "key_pair" {
    description = "Key pair used to login to the instance"
}

variable "mysql_user" {
    description = "Root user name for the MySQL server backend for Vault"
}

variable "mysql_pass" {
    description = "Root user password for the MySQL server backend for Vault"
}

variable "root_pass" {
    description = "Root password for working vm"
}
