resource "aws_dynamodb_table" "customer-order-table" {
    name = "customer-orders"
    billing_mode = "PROVISIONED"
    read_capacity = 20
    write_capacity = 20
    hash_key = "OrderId"
    
    attribute {
        name = "OrderId"
        type = "S"
    }
    
    attribute {
        name = "CustomerId"
        type = "N"
    }

    global_secondary_index {
        name = "StatusIndex"
        hash_key = "CustomerId"
        write_capacity = 10
        read_capacity = 10
        projection_type = "INCLUDE"
        non_key_attributes = ["OrderId"]
    }

    tags = {
        Name = "customer-orders"
    }
}
