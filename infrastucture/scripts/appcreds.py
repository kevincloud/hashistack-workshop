import requests
import json

role_id = ""
secret_id = ""

resp = requests.post('http://${VAULT_SERVER}:8200/v1/sys/auth/approle',
    data={"type": "approle"},
    headers={'X-Vault-Token':'root'}
)

resp = requests.post('http://${VAULT_SERVER}:8200/v1/sys/policy/dev-policy',
    data={"policy": "path \"secret/data/aws\" { capabilities = [\"read\", \"list\"] } path \"secret/cust-mgmt/*\" { capabilities = [\"create\",\"update\",\"read\",\"delete\"] }"},
    headers={'X-Vault-Token':'root'}
)

resp = requests.post('http://${VAULT_SERVER}:8200/v1/auth/approle/role/dev-role',
    data={"policies": ["dev-policy"]},
    headers={'X-Vault-Token':'root'}
)

resp = requests.get('http://${VAULT_SERVER}:8200/v1/auth/approle/role/dev-role/role-id',
    headers={'X-Vault-Token':'root'}
)

role_id = json.loads(json.dumps(resp.json()))["data"]["role_id"]

resp = requests.post('http://${VAULT_SERVER}:8200/v1/auth/approle/role/dev-role/secret-id',
    headers={'X-Vault-Token':'root'}
)

secret_id = json.loads(json.dumps(resp.json()))["data"]["secret_id"]

print('{\n' + '    "role_id": "' + role_id + '",\n    "secret_id": "' + secret_id + '"\n}')
