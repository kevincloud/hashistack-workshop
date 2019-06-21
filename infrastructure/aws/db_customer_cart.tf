resource "aws_dynamodb_table" "customer-cart" {
    name = "customer-cart"
    billing_mode = "PROVISIONED"
    read_capacity = 20
    write_capacity = 20
    hash_key = "SessionId"
    
    attribute {
        name = "SessionId"
        type = "S"
    }
    
    attribute {
        name = "ProductId"
        type = "S"
    }
    
    global_secondary_index {
        name = "NameIndex"
        hash_key = "SessionId"
        range_key = "ProductId"
        write_capacity = 10
        read_capacity = 10
        projection_type = "INCLUDE"
        non_key_attributes = ["CustomerId"]
    }

    # global_secondary_index {
    #     name = "EmailIndex"
    #     hash_key = "EmailAddress"
    #     write_capacity = 10
    #     read_capacity = 10
    #     projection_type = "INCLUDE"
    #     non_key_attributes = ["CustomerId"]
    # }

    tags = {
        Name = "cust-cart-data"
    }
}
