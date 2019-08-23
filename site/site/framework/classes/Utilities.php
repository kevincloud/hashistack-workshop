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

	public static function EncryptValue($transitkey, $plaintext)
	{
		global $vaulturl;
		$r = new RestRunner();


		$r->SetHeader("X-Vault-Token", getenv("VAULT_TOKEN"));
		$result = $r->Post(
			$vaulturl."/v1/transit/encrypt/".$transitkey, 
			"{ \"plaintext\": \"".base64_encode($plaintext)."\" }");
		return $result->data->ciphertext;
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

	public static function GetStates()
	{
		return array(
			array("Name" => "Alabama", "Abbr" => "AL"), 
			array("Name" => "Alaska", "Abbr" => "AK"), 
			array("Name" => "Arizona", "Abbr" => "AZ"), 
			array("Name" => "Arkansas", "Abbr" => "AR"), 
			array("Name" => "California", "Abbr" => "CA"), 
			array("Name" => "Colorado", "Abbr" => "CO"), 
			array("Name" => "Connecticut", "Abbr" => "CT"), 
			array("Name" => "Delaware", "Abbr" => "DE"), 
			array("Name" => "Florida", "Abbr" => "FL"), 
			array("Name" => "Georgia", "Abbr" => "GA"), 
			array("Name" => "Hawaii", "Abbr" => "HI"), 
			array("Name" => "Idaho", "Abbr" => "ID"), 
			array("Name" => "Illinois", "Abbr" => "IL"), 
			array("Name" => "Indiana", "Abbr" => "IN"), 
			array("Name" => "Iowa", "Abbr" => "IA"), 
			array("Name" => "Kansas", "Abbr" => "KS"), 
			array("Name" => "Kentucky", "Abbr" => "KY"), 
			array("Name" => "Louisiana", "Abbr" => "LA"), 
			array("Name" => "Maine", "Abbr" => "ME"), 
			array("Name" => "Maryland", "Abbr" => "MD"), 
			array("Name" => "Massachusetts", "Abbr" => "MA"), 
			array("Name" => "Michigan", "Abbr" => "MI"), 
			array("Name" => "Minnesota", "Abbr" => "MN"), 
			array("Name" => "Mississippi", "Abbr" => "MS"), 
			array("Name" => "Missouri", "Abbr" => "MO"), 
			array("Name" => "Montana", "Abbr" => "MT"), 
			array("Name" => "Nebraska", "Abbr" => "NE"), 
			array("Name" => "Nevada", "Abbr" => "NV"), 
			array("Name" => "New Hampshire", "Abbr" => "NH"), 
			array("Name" => "New Jersey", "Abbr" => "NJ"), 
			array("Name" => "New Mexico", "Abbr" => "NM"), 
			array("Name" => "New York", "Abbr" => "NY"), 
			array("Name" => "North Carolina", "Abbr" => "NC"), 
			array("Name" => "North Dakota", "Abbr" => "ND"), 
			array("Name" => "Ohio", "Abbr" => "OH"), 
			array("Name" => "Oklahoma", "Abbr" => "OK"), 
			array("Name" => "Oregon", "Abbr" => "OR"), 
			array("Name" => "Pennsylvania", "Abbr" => "PA"), 
			array("Name" => "Rhode Island", "Abbr" => "RI"), 
			array("Name" => "South Carolina", "Abbr" => "SC"), 
			array("Name" => "South Dakota", "Abbr" => "SD"), 
			array("Name" => "Tennessee", "Abbr" => "TN"), 
			array("Name" => "Texas", "Abbr" => "TX"), 
			array("Name" => "Utah", "Abbr" => "UT"), 
			array("Name" => "Vermont", "Abbr" => "VT"), 
			array("Name" => "Virginia", "Abbr" => "VA"), 
			array("Name" => "Washington", "Abbr" => "WA"), 
			array("Name" => "West Virginia", "Abbr" => "WV"), 
			array("Name" => "Wisconsin", "Abbr" => "WI"), 
			array("Name" => "Wyoming", "Abbr" => "WY")
		);
	}
}

?>