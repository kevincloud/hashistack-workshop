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
    data='{ "role_id": "' + role_id + '", "secret_id": "' + secret_id + '" }'
)

token_id = json.loads(json.dumps(resp.json()))["auth"]["client_token"]

resp = requests.get('http://${VAULT_SERVER}:8200/v1/secret/data/aws',
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
    table = ddb.Table('product-main')

    response = table.scan(
        Select='ALL_ATTRIBUTES',
        Limit=20
    )

    return json.dumps(response['Items'])

@app.route('/customer/<customer_id>', strict_slashes=False, methods=['GET'])
def product_info(product_id):
    table = ddb.Table('product-main')
    response = table.query(
        KeyConditionExpression=Key('ProductId').eq(product_id)
    )

    return json.dumps(response['Items'])

if __name__=='__main__':
    app.run(host='0.0.0.0', debug=True, port=5821)

