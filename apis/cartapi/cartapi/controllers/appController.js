'use strict';

var AWS = require('aws-sdk');

region = process.env.AWS_REGION;
AWS.config.update({region: region});

var ddb = new AWS.DynamoDB.DocumentClient();
var table = 'customer-cart';
var datetime = new Date().getTime().toString();

exports.list_cart_items = function(req, res) {
    var sessionid = 'mysession';

    ddb.get({
        TableName: table,
        Key: {
            'SessionId': sessionid
        }
    }, function(err, data) {
        if (err) {
            console.log("Error", err);
        } else {
            res.json(data);
            console.log("Success", data);
        }
    })
};

exports.add_to_cart = function(req, res) {
    var sessionid = 'MySessionID';
    var productid = 'BE0001';
    var quantity = 2;

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
            console.log("Error", err);
        } else {
            console.log("Success", data);
        }
    });
};

exports.update_cart_item = function(req, res) {
    var sessionid = 'MySessionID';
    var productid = 'BE0001';
    var quantity = 2;

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
            console.log("Error", err);
        } else {
            console.log("Success", data);
        }
    });
};

exports.delete_cart_item = function(req, res) {
    var sessionid = 'MySessionID';
    var productid = 'BE0001';

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
            console.log("Error", err);
        } else {
            console.log("Success", data);
        }
    });
};

exports.empty_cart = function(req, res) {
    var sessionid = 'MySessionID';

    ddb.delete({
        TableName: table,
        Key: {
            'SessionId': sessionid
        }
    }, function(err, data) {
        if (err) {
            console.log("Error", err);
        } else {
            console.log("Success", data);
        }
    });
};
