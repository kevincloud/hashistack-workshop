resource "aws_dynamodb_table" "order-history-table" {
    name = "order-history"
    billing_mode = "PROVISIONED"
    read_capacity = 20
    write_capacity = 20
    hash_key = "OrderId"
    range_key = "CustomerId"
    
    attribute = [{
        name = "OrderId"
        type = "S"
    }, {
        name = "CustomerId"
        type = "S"
    }, {
        name = "Status"
        type = "S"
    }]

    global_secondary_index {
        name = "StatusIndex"
        hash_key = "CustomerId"
        range_key = "Status"
        write_capacity = 10
        read_capacity = 10
        projection_type = "INCLUDE"
        non_key_attributes = ["OrderId"]
    }

    tags = {
        Name = "cust-mgmt-data"
    }
}
