import requests
import os
import json
import decimal
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

class DecimalEncoder(json.JSONEncoder):
    def default(self, o): # pylint: disable=E0202
        if isinstance(o, decimal.Decimal):
            if o % 1 > 0:
                return float(o)
            else:
                return int(o)
        return super(DecimalEncoder, self).default(o)

@app.route('/all', strict_slashes=False, methods=['GET'])
def get_all():
    table = ddb.Table('product-main')

    response = table.scan(
        Select='ALL_ATTRIBUTES',
        Limit=20
    )

    output = []
    for i in response['Items']:
        output.append(i)

    return json.dumps(output, cls=DecimalEncoder)

@app.route('/detail/<product_id>', strict_slashes=False, methods=['GET'])
def product_info(product_id):
    table = ddb.Table('product-main')
    response = table.query(
        KeyConditionExpression=Key('ProductId').eq(product_id)
    )

    output = []
    for i in response['Items']:
        output.append(i)

    return json.dumps(output, cls=DecimalEncoder)

@app.route('/image/<product_id>', strict_slashes=False, methods=['GET'])
def product_image(product_id):
    table = ddb.Table('product-main')
    response = table.query(
        KeyConditionExpression=Key('ProductId').eq(product_id)
    )
    
    image_name = ""
    for i in response['Items']:
        image_name = i["Image"]
    
    return image_name

if __name__=='__main__':
    app.run(host='0.0.0.0', debug=True, port=5821)

