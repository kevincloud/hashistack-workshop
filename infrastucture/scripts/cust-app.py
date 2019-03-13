import requests
import os
import json
from flask import Flask
from flask_cors import CORS
import boto3
from boto3.dynamodb.conditions import Key, Attr

access_key = ""
secret_key = ""

with open("creds.json", "r") as read_file:
    data = json.load(read_file)

role_id = data['role_id']
secret_id = data['secret_id']

resp = requests.post('http://${VAULT_SERVER}:8200/v1/auth/approle/login',
    data={ "role_id": role_id, "secret_id": secret_id }
)

token_id = json.loads(json.dumps(resp.json()))["auth"]["client_token"]

resp = requests.post('http://${VAULT_SERVER}:8200/v1/secret/data/aws',
    headers={'X-Vault-Token': token_id}
)

secrets = json.loads(json.dumps(resp.json()))
access_key = secrets['data']['data']['aws_access_key']
secret_key = secrets['data']['data']['aws_secret_key']

app = Flask(__name__)
CORS(app)
ddb = boto3.resource('dynamodb', aws_access_key_id=access_key, aws_secret_access_key=secret_key, region_name='us-east-1')

@app.route('/')
def home():
    table = ddb.Table('customer-main')

    response = table.scan(
        Select='ALL_ATTRIBUTES',
        Limit=20
    )

    output = "<table>"
    output += "<tr><th>Customer Id</th><th>First Name</th><th>Last Name</th><th>Email Address</th></tr>"
    for i in response['Items']:
        output += "<tr>"
        output += "<td><a href=\"http://54.164.3.240:5801/customer/" + i['CustomerId'] + "\" rel=\"modal:open\">" + i['CustomerId'] + "</a></td>"
        output += "<td>" + i['FirstName'] + "</td>"
        output += "<td>" + i['LastName'] + "</td>"
        output += "<td>" + i['EmailAddress'] + "</td>"
        output += "</tr>"
    output += "</table>"
    return output

@app.route('/customer/<customer_id>', strict_slashes=False, methods=['GET'])
def customer_info(customer_id):
    table = ddb.Table('customer-main')
    response = table.query(
        KeyConditionExpression=Key('CustomerId').eq(customer_id)
    )

    output = ""

    for i in response['Items']:
        output += '<div class="cust-detail">'
        output += '    <div class="cust-name">' + i['FirstName'] + ' ' + i['LastName'] + '</div>'
        output += '    <div class="cust-email"><a href="mailto:' + i['EmailAddress'] + '">' + i['EmailAddress'] + '</a></div>'
        for a in i['Addresses']:
            output += '    <div class="cust-addr">'
            output += '        <div class="cust-addr-head">Address:</div>'
            output += '        <div>' + a['Address1'] + '</div>'
            output += '        <div>' + a['City'] + ', ' + a['State'] + ' ' + a['ZipCode'] + '</div>'
            output += '        <div>' + a['PhoneNumber'] + '</div>'
            output += '    </div>'
        output += '</div>'

    return output

if __name__=='__main__':
    app.run(host='0.0.0.0', debug=True, port=5801)

