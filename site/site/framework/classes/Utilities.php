<?php

class Utilities
{
	public static function BeautifyURL($str)
	{
		$str = strip_tags($str);
	
		$str = preg_replace('/[^a-z0-9]+/i', ' ', $str);
		$str = preg_replace('/\s+/i', ' ', $str);
		$str = trim($str);
	
		$str = str_replace(' ', '-', $str);
		return $str;
	}
	
	public static function FormatGuid($id)
	{
		return "{".substr($id, 0, 8)."-".substr($id, 8, 4)."-".substr($id, 12, 4)."-".substr($id, 16, 4)."-".substr($id, 20)."}";
	}


	public static function GetVaultSecret($secretpath)
	{
		global $vaulturl;
		$r = new RestRunner();

		$r->SetHeader("X-Vault-Token", getenv("VAULT_TOKEN"));
		$result = $r->Get($vaulturl."/".$secretpath);
		return $result->data->data;
	}

	public static function DecryptValue($transitkey, $ciphertext)
	{
		global $vaulturl;
		$r = new RestRunner();

		$r->SetHeader("X-Vault-Token", getenv("VAULT_TOKEN"));
		$result = $r->Post(
			$vaulturl."/v1/transit/decrypt/".$transitkey, 
			"{ \"ciphertext\": \"".$ciphertext."\" }");
		return base64_decode($result->data->plaintext);
	}
}

?>