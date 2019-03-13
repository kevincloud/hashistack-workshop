
import boto3
import json
import uuid

ddb = boto3.resource('dynamodb', region_name='us-east-1')

table = ddb.Table('customer-main')

response = table.put_item(
    Item={
        'CustomerId': 'CS100312',
        'FirstName': 'Janice',
        'LastName': 'Thompson',
        'EmailAddress': 'jthomp4423@example.com',
        'Addresses': [{
            'Address1': '3611 Farland Street',
            'City': 'Brockton',
            'State': 'MA',
            'ZipCode': '02401',
            'PhoneNumber': '774-240-5996'
        }]
    }
)
response = table.put_item(
    Item={
        'CustomerId': 'CS106004',
        'FirstName': 'James',
        'LastName': 'Wilson',
        'EmailAddress': 'wilson@example.com',
        'Addresses': [{
            'Address1': '1437 Capitol Avenue',
            'City': 'Paragon',
            'State': 'IN',
            'ZipCode': '46166',
            'PhoneNumber': '765-537-0152'
        }]
    }
)
response = table.put_item(
    Item={
        'CustomerId': 'CS101438',
        'FirstName': 'Tommy',
        'LastName': 'Ballinger',
        'EmailAddress': 'tommy6677@example.com',
        'Addresses': [{
            'Address1': '2143 Wescam Court',
            'City': 'Reno',
            'State': 'NV',
            'ZipCode': '89502',
            'PhoneNumber': '775-856-9045'
        }]
    }
)
response = table.put_item(
    Item={
        'CustomerId': 'CS210895',
        'FirstName': 'Mary',
        'LastName': 'McCorckle',
        'EmailAddress': 'mmccorckle@example.com',
        'Addresses': [{
            'Address1': '4512 Layman Avenue',
            'City': 'Robbins',
            'State': 'NC',
            'ZipCode': '27325',
            'PhoneNumber': '910-948-3965'
        }]
    }
)
response = table.put_item(
    Item={
        'CustomerId': 'CS122955',
        'FirstName': 'Chris',
        'LastName': 'Peterson',
        'EmailAddress': 'cjpcomp@example.com',
        'Addresses': [{
            'Address1': '2329 Joanne Lane',
            'City': 'Newburyport',
            'State': 'MA',
            'ZipCode': '01950',
            'PhoneNumber': '978-499-7306'
        }]
    }
)
response = table.put_item(
    Item={
        'CustomerId': 'CS602934',
        'FirstName': 'Jennifer',
        'LastName': 'Jones',
        'EmailAddress': 'jjhome7823@example.com',
        'Addresses': [{
            'Address1': '589 Hidden Valley Road',
            'City': 'Lancaster',
            'State': 'PA',
            'ZipCode': '17670',
            'PhoneNumber': '717-224-9902'
        }]
    }
)
response = table.put_item(
    Item={
        'CustomerId': 'CS157843',
        'FirstName': 'Clint',
        'LastName': 'Mason',
        'EmailAddress': 'clint.mason312@example.com',
        'Addresses': [{
            'Address1': '3641 Alexander Drive',
            'City': 'Denton',
            'State': 'TX',
            'ZipCode': '76201',
            'PhoneNumber': '940-349-9386'
        }]
    }
)
response = table.put_item(
    Item={
        'CustomerId': 'CS523484',
        'FirstName': 'Matt',
        'LastName': 'Grey',
        'EmailAddress': 'greystone89@example.com',
        'Addresses': [{
            'Address1': '1320 Tree Top Lane',
            'City': 'Wayne',
            'State': 'PA',
            'ZipCode': '19087',
            'PhoneNumber': '610-225-6567'
        }]
    }
)
response = table.put_item(
    Item={
        'CustomerId': 'CS658871',
        'FirstName': 'Howard',
        'LastName': 'Turner',
        'EmailAddress': 'runwayyourway@example.com',
        'Addresses': [{
            'Address1': '1179 Lynn Street',
            'City': 'Woburn',
            'State': 'MA',
            'ZipCode': '01801',
            'PhoneNumber': '617-251-5420'
        }]
    }
)
response = table.put_item(
    Item={
        'CustomerId': 'CS103393',
        'FirstName': 'Larry',
        'LastName': 'Olsen',
        'EmailAddress': 'olsendog1979@example.com',
        'Addresses': [{
            'Address1': '2850 Still Street',
            'City': 'Oregon',
            'State': 'OH',
            'ZipCode': '43616',
            'PhoneNumber': '419-698-9890'
        }]
    }
)
