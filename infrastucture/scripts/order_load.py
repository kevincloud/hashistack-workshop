import boto3
import json
import uuid
from datetime import datetime, timedelta

ddb = boto3.resource('dynamodb', region_name='us-east-1')

table = ddb.Table('order-history')

x = datetime.now() - timedelta(days=10, hours=6, minutes=3, seconds=7)
response = table.put_item(
    Item={
        'OrderId': 'CO' + x.strftime("%y%m%d") + '-0078',
        'CustomerId': 'CS106004',
        'OrderDate': x.strftime("%Y-%m-%dT%H:%M:%S-05:00"),
        'Status': 'Shipped',
        'Items': [{
            'ProductId': 'CS-006',
            'ProductName': 'Iced Tea Maker',
            'Price': 29.99,
            'Quantity': 1
        }]
    }
)

x = datetime.now() - timedelta(days=2, hours=10, minutes=48, seconds=0)
response = table.put_item(
    Item={
        'OrderId': 'CO' + x.strftime("%y%m%d") + '-0119',
        'CustomerId': 'CS101438',
        'OrderDate': x.strftime("%Y-%m-%dT%H:%M:%S-05:00"),
        'Status': 'Shipped',
        'Items': [{
            'ProductId': 'CS-005',
            'ProductName': 'Espresso Maker',
            'Price': 199.99,
            'Quantity': 1
        }, {
            'ProductId': 'CS-002',
            'ProductName': 'Burr Coffee Grinder',
            'Price': 58.99,
            'Quantity': 1
        }]
    }
)

x = datetime.now() - timedelta(hours=5, minutes=32, seconds=22)
response = table.put_item(
    Item={
        'OrderId': 'CO' + x.strftime("%y%m%d") + '-0078',
        'CustomerId': 'CS101438',
        'OrderDate': x.strftime("%Y-%m-%dT%H:%M:%S-05:00"),
        'Status': 'Shipped',
        'Items': [{
            'ProductId': 'CS-005',
            'ProductName': 'Espresso Maker',
            'Price': 199.99,
            'Quantity': 1
        }, {
            'ProductId': 'CS-002',
            'ProductName': 'Burr Coffee Grinder',
            'Price': 58.99,
            'Quantity': 1
        }]
    }
)

# CREATE TABLE customer-main (
#     customerid
# )

# CS122955	CS-003	1
# CS122955	CS-004	1
# CS602934	CS-003	1
# CS602934	CS-010	1
# CS157843	CS-007	1
# CS523484	CS-009	1
# CS103393	CS-001	2
		
# CS103393	CS-002	1
# CS103393	CS-010	1