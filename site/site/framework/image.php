<?php
include_once("ini.php");

$action = "";
if (isset($_REQUEST["action"]))
	$action = strtolower(trim($_REQUEST["action"]));

switch ($action)
{
	default:
		ShowProductImage();
		break;
}

function ShowFeaturedImage()
{
	global $db;
	
	$id = $_REQUEST["id"];
	
	$image = null;
	$imagefile = "";
	$name = "";
	$ext = "";
	$url = "";
	
	$sql = "select a.adimage from cc_store_ads as a where a.adguid = ".smartQuote(Utilities::FormatGuid($id));
	$row = $db->get_row($sql, ARRAY_A);
	if ($row)
	{
		$imagefile = $row["adimage"];
		$ext = substr(strrchr($imagefile,'.'),1);
		$url = "http://ads.java-perks.com".dirname($imagefile)."/".str_replace(" ", "%20", basename($imagefile));
		
		$ch = curl_init();
		curl_setopt ($ch, CURLOPT_URL, $url);
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 0);
		$image = curl_exec($ch);
		curl_close($ch);		
		
		switch (strtolower($ext))
		{
			case "png":
				header("Content-type: image/png");
			case "gif":
				header("Content-type: image/gif");
			default:
				header("Content-type: image/jpeg");
		}
		header("Content-Disposition: attachment; filename=\"".basename($imagefile)."\"");
		echo $image;
	}
}

function ShowProductImage()
{	
	$pid = $_REQUEST["pid"];
	$p = new Product();
	$p->GetProduct($pid);
	$p->ShowImage();
}

?>