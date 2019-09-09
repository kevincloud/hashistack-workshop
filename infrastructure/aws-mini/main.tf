provider "aws" {
    access_key = "${var.aws_access_key}"
    secret_key = "${var.aws_secret_key}"
    region = "us-east-1"
}

resource "aws_vpc" "primary-vpc" {
    cidr_block = "10.0.0.0/16"
    enable_dns_hostnames = true

    tags = {
        Name = "cust-mgmt-vpc"
    }
}

resource "aws_internet_gateway" "igw" {
    vpc_id = "${aws_vpc.primary-vpc.id}"
}

resource "aws_subnet" "public-subnet" {
    vpc_id = "${aws_vpc.primary-vpc.id}"
    cidr_block = "10.0.1.0/24"
    availability_zone = "us-east-1a"
    map_public_ip_on_launch = true
    depends_on = ["aws_internet_gateway.igw"]
}

resource "aws_subnet" "private-subnet" {
    vpc_id = "${aws_vpc.primary-vpc.id}"
    cidr_block = "10.0.2.0/24"
    availability_zone = "us-east-1b"
}

resource "aws_subnet" "private-subnet-2" {
    vpc_id = "${aws_vpc.primary-vpc.id}"
    cidr_block = "10.0.3.0/24"
    availability_zone = "us-east-1c"
}

resource "aws_route" "public-routes" {
    route_table_id = "${aws_vpc.primary-vpc.default_route_table_id}"
    destination_cidr_block = "0.0.0.0/0"
    gateway_id = "${aws_internet_gateway.igw.id}"
}

resource "aws_eip" "nat-ip" {
    vpc = true
}

resource "aws_nat_gateway" "natgw" {
    allocation_id   = "${aws_eip.nat-ip.id}"
    subnet_id       = "${aws_subnet.public-subnet.id}"
    depends_on      = ["aws_internet_gateway.igw","aws_subnet.public-subnet"]
}

resource "aws_route_table" "natgw-route" {
    vpc_id = "${aws_vpc.primary-vpc.id}"
    route {
        cidr_block = "0.0.0.0/0"
        nat_gateway_id = "${aws_nat_gateway.natgw.id}"
    }

    tags = {
        Name = "cust-mgmt-natgw"
    }
}

resource "aws_route_table_association" "route-out" {
    subnet_id = "${aws_subnet.private-subnet.id}"
    route_table_id = "${aws_route_table.natgw-route.id}"
}


