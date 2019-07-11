'use strict';

var AWS = require('aws-sdk');

var region = process.env.AWS_REGION;
AWS.config.update({region: region});

var ddb = new AWS.DynamoDB.DocumentClient();
var table = 'customer-cart';
var datetime = new Date().getTime().toString();

exports.list_cart_items = function(req, res) {
    var sessionid = req.params.sessionId;

    ddb.get({
        TableName: table,
        Key: {
            'SessionId': sessionid
        }
    }, function(err, data) {
        if (err) {
            res.send({
                success: false,
                message: 'Server error'
            })
            console.log("Error", err);
        } else {
            const { Items } = data;

            res.send({
                success: true,
                message: 'Retrieved all items in cart',
                items: Items
            });
            console.log("Success", data);
        }
    });
};

exports.get_cart_item = function(req, res) {
    var sessionid = req.params.sessionId;
    var productid = req.params.productId;

    ddb.get({
        TableName: table,
        Key: {
            'SessionId': sessionid,
            'ProductId': productid
        }
    }, function(err, data) {
        if (err) {
            res.send({
                success: false,
                message: 'Server error'
            })
            console.log("Error", err);
        } else {
            const { Item } = data;

            res.send({
                success: true,
                message: 'Retrieved all items in cart',
                item: Item
            });
            console.log("Success", data);
        }
    });
};

exports.add_to_cart = function(req, res) {
    var sessionid = req.body.sessionId;
    var productid = req.body.productId;
    var quantity = parseInt(req.body.quantity);

    // TODO: make sure to do an update
    // if the item is already in the cart.
    
    ddb.put({
        TableName: table,
        Item: {
            'SessionId': sessionid,
            'ProductId': productid,
            'Quantity': quantity,
            'DateStamp': datetime
        }
    }, function(err, data) {
        if (err) {
            res.send({
                success: false,
                message: 'Server error'
            });
            console.log("Error", err);
        } else {
            res.send({
                success: true,
                message: 'Item added'
            });
            console.log("Success", data);
        }
    });
};

exports.update_cart_item = function(req, res) {
    var sessionid = req.params.sessionId;
    var productid = req.params.productId;
    var quantity = req.params.quantity;

    ddb.update({
        TableName: table,
        Key: {
            'SessionId': sessionid,
            'ProductId': productid
        },
        UpdateExpression: "set Quantity = :q, DateStamp = :d",
        ExpressionAttributeValues: {
            ":q": quantity,
            ":d": datetime
        },
        ReturnValues: "UPDATED_NEW"
    }, function(err, data) {
        if (err) {
            res.send({
                success: false,
                message: 'Server error'
            });
            console.log("Error", err);
        } else {
            res.send({
                success: true,
                message: 'Item updated'
            });
            console.log("Success", data);
        }
    });
};

exports.delete_cart_item = function(req, res) {
    var sessionid = req.params.sessionId;
    var productid = req.params.productId;

    ddb.delete({
        TableName: table,
        Key: {
            'SessionId': sessionid
        },
        ConditionExpression: "ProductId = :p",
        ExpressionAttributeValues: {
            ":p": productid
        }
    }, function(err, data) {
        if (err) {
            res.send({
                success: false,
                message: 'Server error'
            });
            console.log("Error", err);
        } else {
            res.send({
                success: true,
                message: 'Item deleted'
            });
            console.log("Success", data);
        }
    });
};

exports.empty_cart = function(req, res) {
    var sessionid = req.params.sessionId;

    ddb.delete({
        TableName: table,
        Key: {
            'SessionId': sessionid
        }
    }, function(err, data) {
        if (err) {
            res.send({
                success: false,
                message: 'Server error'
            });
            console.log("Error", err);
        } else {
            res.send({
                success: true,
                message: 'Cart is now empty'
            });
            console.log("Success", data);
        }
    });
};
