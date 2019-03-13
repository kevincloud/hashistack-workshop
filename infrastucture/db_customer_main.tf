resource "aws_dynamodb_table" "customer-data-table" {
    name = "customer-main"
    billing_mode = "PROVISIONED"
    read_capacity = 20
    write_capacity = 20
    hash_key = "CustomerId"
    
    attribute = [{
        name = "CustomerId"
        type = "S"
    }, {
        name = "FirstName"
        type = "S"
    }, {
        name = "LastName"
        type = "S"
    }, {
        name = "EmailAddress"
        type = "S"
    }]

    global_secondary_index {
        name = "NameIndex"
        hash_key = "LastName"
        range_key = "FirstName"
        write_capacity = 10
        read_capacity = 10
        projection_type = "INCLUDE"
        non_key_attributes = ["CustomerId"]
    }

    global_secondary_index {
        name = "EmailIndex"
        hash_key = "EmailAddress"
        write_capacity = 10
        read_capacity = 10
        projection_type = "INCLUDE"
        non_key_attributes = ["CustomerId"]
    }

    tags = {
        Name = "cust-mgmt-data"
    }
}
