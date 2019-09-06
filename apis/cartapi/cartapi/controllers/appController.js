'use strict';

var AWS = require('aws-sdk');

var region = process.env.REGION;
AWS.config.update({region: region});

var ddb = new AWS.DynamoDB.DocumentClient();
var table = 'customer-cart';

exports.list_cart_items = function(req, res) {
    var sessionid = req.query.sessionId;

    ddb.query({
        TableName: table,
        KeyConditionExpression: '#sid = :sid',
        ExpressionAttributeNames: {
            "#sid": "SessionId"
        },
        ExpressionAttributeValues: {
            ":sid": sessionid
        }
    }, function(err, data) {
        if (err) {
            res.send({
                success: false,
                message: err
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
                message: err
            })
            console.log("Error", err);
        } else {
            const { Item } = data;

            res.send({
                success: true,
                message: 'Retrieved item in cart',
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
    var datetime = new Date().getTime().toString();

    ddb.query({
        TableName: table,
        KeyConditionExpression: "#sid = :sid and #pid = :pid",
        ExpressionAttributeNames:{
            "#sid": "SessionId",
            "#pid": "ProductId",
        },
        ExpressionAttributeValues: {
            ":sid": sessionid,
            ":pid": productid
        }
    }, function(err, data) {
        if (err) {
            res.send({
                success: false,
                message: err
            });
        }
        else {
            if (data.Count == 0) {
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
                            message: err
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
            }
            else {
                quantity += data.Items[0].Quantity;
                
                ddb.update({
                    TableName: table,
                    Key: { 'SessionId': sessionid, 'ProductId': productid },
                    UpdateExpression: "set Quantity = :q",
                    ExpressionAttributeValues: {
                        ':q': quantity
                    },
                    ReturnValues: "UPDATED_NEW"
                }, function(err, data) {
                    if (err) {
                        res.send({
                            success: false,
                            message: err
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
            }
        }
    });
};

exports.update_cart_item = function(req, res) {
    var sessionid = req.params.sessionId;
    var productid = req.params.productId;
    var quantity = req.params.quantity;
    var datetime = new Date().getTime().toString();

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
                message: err
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
    var sessionid = req.body.sessionId;
    var productid = req.params.productId;

    console.log("sessionid: " + sessionid);
    console.log("productid: " + productid);

    ddb.delete({
        TableName: table,
        Key: {
            'SessionId': sessionid,
            'ProductId': productid
        }
    }, function(err, data) {
        if (err) {
            res.send({
                success: false,
                message: err
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

exports.is_alive = function(req, res) {
    res.send({
        success: true,
        message: 'Alive!'
    });
}

exports.empty_cart = function(req, res) {
    var sessionid = req.params.sessionId;

    ddb.delete({
        TableName: table,
        IndexName: 'SessionIndex',
        KeyConditionExpression: "#sid = :sidvalue",
        ExpressionAttributeNames: {
            "sid": "SessionId"
        },
        ExpressionAttributeValues: {
            ":sidvalue": sessionid
        }
    }, function(err, data) {
        if (err) {
            res.send({
                success: false,
                message: err
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

exports.clean_cart = function(req, res) {
    var sessionid = req.params.sessionId;

    ddb.delete({
        TableName: table,
        Key: {
            'SessionId': sessionid
        },
        ConditionExpression: 'Quantity <= :qty',
        ExpressionAttributeValues: { ":qty": 0 }
    }, function(err, data) {
        if (err) {
            res.send({
                success: true,
                message: err
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
