<?php

class ShippingUSPS
{
	public $PackageNumber = 1;
	public $ShipToZip = "";
	public $ShipToCountry = "";
	public $ShipFromZip = "";
	public $ShipFromCountry = "";
	public $UserID = "";
	public $Items = array();
	public $Service = NULL;
	public $Rates = array();
	public $DebugResponse = "";
	public $Domestic = true;
	
	public function __construct()
	{
		$this->Service = ServiceUSPS::MediaMail;
	}
	
	public function AddPackage($weight, $size)
	{
		$this->Items[] = new PackageDataUSPS($weight, $size);
		$this->PackageNumber++;
	}
	
	public function GetServiceName($servicecode)
	{
		if (stripos($servicecode, "PRIORITY") !== false)
			return ServiceUSPS::PriorityMail;
		else if (stripos($servicecode, "MEDIA") !== false)
			return ServiceUSPS::MediaMail;
		else if (stripos($servicecode, "PARCEL") !== false)
			return ServiceUSPS::ParcelPost;
		else if (stripos($servicecode, "FIRST CLASS") !== false)
			return ServiceUSPS::FirstClass;
		else if (stripos($servicecode, "EXPRESS") !== false)
			return ServiceUSPS::ExpressMail;
		else
			return ServiceUSPS::International;
	}
	
	public function GetXMLIntl()
	{
		$retval = "";
		$svccode = "MEDIA";
		$i = 0;

		switch ($this->Service)
		{
			case ServiceUSPS::PriorityMail:
				$svccode = "FlatRate";
				break;
			case ServiceUSPS::MediaMail:
				$svccode = "Package";
				break;
			default:
				$svccode = "Package";
				break;
		}

		if (count($this->Items) > 0 && $this->UserID != "" && $this->ShipToZip != "" && $this->ShipFromZip != "")
		{
			$retval .= "<?xml version=\"1.0\"?>".
				"<IntlRateV2Request USERID=\"".$this->UserID."\">";

			foreach ($this->Items as $package)
			{
				$i++;
				$retval .= "<Package ID=\"".$i."\">";
				$retval .= $package->GetXMLIntl1();
				$retval .= "<Machinable>True</Machinable>";
				$retval .= "<MailType>".$svccode."</MailType>";
				$retval .= "<GXG>";
				$retval .= "	<POBoxFlag>Y</POBoxFlag>";
				$retval .= "	<GiftFlag>Y</GiftFlag>";
				$retval .= "</GXG>";
				$retval .= "<ValueOfContents>200</ValueOfContents>";
				$retval .= "<Country>".$this->ShipToCountry."</Country>";
				$retval .= $package->GetXMLIntl2();
				$retval .= "<CommercialFlag>N</CommercialFlag>";
				$retval .= "</Package>";
			}

			$retval .= "</IntlRateV2Request>";
		}
		
		//echo $retval;
		//exit();

		return $retval;
	}
	
	public function GetXML()
	{
		$retval = "";
		$svccode = "MEDIA";
		$i = 0;

		switch ($this->Service)
		{
			case ServiceUSPS::PriorityMail:
				$svccode = "PRIORITY";
				break;
			case ServiceUSPS::MediaMail:
				$svccode = "MEDIA";
				break;
			case ServiceUSPS::ParcelPost:
				$svccode = "PARCEL";
				break;
			case ServiceUSPS::FirstClass:
				$svccode = "FIRST CLASS";
				break;
			default:
				$svccode = "MEDIA";
				break;
		}

		if (count($this->Items) > 0 && $this->UserID != "" && $this->ShipToZip != "" && $this->ShipFromZip != "")
		{
			$retval .= "<?xml version=\"1.0\"?>".
				"<RateV4Request USERID=\"".$this->UserID."\">";

			foreach ($this->Items as $package)
			{
				$i++;
				if ($package->Height == 0 && $this->Service == ServiceUSPS::MediaMail) $package->SetBoxSize(BoxSizeUSPS::Small);
				$retval .= "<Package ID=\"".$i."\">";
				$retval .= "<Service>".$svccode."</Service>";
				$retval .= "<ZipOrigination>".$this->ShipFromZip."</ZipOrigination>";
				$retval .= "<ZipDestination>".$this->ShipToZip."</ZipDestination>";
				$retval .= $package->GetXML($this->Domestic);
				$retval .= "</Package>";
			}

			$retval .= "</RateV4Request>";
		}

		return $retval;
	}
	
	public function Send()
	{
		if ($this->Domestic)
			$postdata = $this->GetXML();
		else
			$postdata = $this->GetXMLIntl();
		
		unset($this->Rates);
		
		if (isBlank($postdata))
			return;
		
		if ($this->Domestic)
			$request = "http://production.shippingapis.com/ShippingAPI.dll/?API=RateV4&XML=".rawurlencode($postdata);
		else
			$request = "http://production.shippingapis.com/ShippingAPI.dll/?API=IntlRateV2&XML=".rawurlencode($postdata);
		$tmprate = 0;
		$shiptype = "";
		$response = "";
		
		$ch = curl_init();
		
		curl_setopt($ch, CURLOPT_URL, $request);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_HTTPGET, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		$response = curl_exec($ch);
		
		curl_close($ch);
		
		$this->DebugResponse = $response;
		//echo $response;
		//exit();
		$xml = simplexml_load_string($response);

		if ($this->Domestic)
		{
			$lowest = 10000.0;
			
			foreach ($xml->Package->Postage as $pkg)
			{
				if (floatval($pkg->Rate) < $lowest)
					$lowest = floatval($pkg->Rate);
			}
			
			foreach ($xml->Package->Postage as $pkg)
			{
				if (floatval($pkg->Rate) === $lowest)
					$this->Rates[] = new ShippingRateUSPS($this->GetServiceName($pkg->MailService), floatval($pkg->Rate));
			}
			
			//foreach ($xml->Package->Postage as $pkg)
			//	$this->Rates[] = new ShippingRateUSPS($this->GetServiceName($pkg->MailService), floatval($pkg->Rate));
		}
		else
		{
			$lowest = 10000.0;
			
			foreach ($xml->Package->Service as $pkg)
			{
				if (floatval($pkg->Postage) < $lowest)
					$lowest = floatval($pkg->Postage);
			}
			
			foreach ($xml->Package->Service as $pkg)
			{
				if (floatval($pkg->Postage) === $lowest)
					$this->Rates[] = new ShippingRateUSPS($this->GetServiceName($pkg->SvcDescription), floatval($pkg->Postage));
			}
		}
	}
}

class ShippingRateUSPS
{
	public $Service = NULL;
	public $Rate = 0.0;
	
	public function __construct($service, $rate)
	{
		$this->Service = $service;
		$this->Rate = $rate;
	}
	
	public function ServiceName()
	{
		switch ($this->Service)
		{
			case ServiceUSPS::MediaMail:
				return "Media Mail";
			case ServiceUSPS::PriorityMail:
				return "Priority Mail";
			case ServiceUSPS::ParcelPost:
				return "Parcel Post";
			case ServiceUSPS::FirstClass:
				return "First Class";
			case ServiceUSPS::International:
				return "International";
			default:
				return "Unspecified";
		}
	}
	
	public function LSICode()
	{
		switch ($this->Service)
		{
			case ServiceUSPS::MediaMail:
				return "USPSMMDC";
			case ServiceUSPS::PriorityMail:
				return "USPS1P";
			default:
				return "";
		}
	}
}

class PackageDataUSPS
{
	public $Length = 13.5;
	public $Width = 10.5;
	public $Height = 11;
	public $Weight = 20;
	public $Girth = 0;
	
	public function __construct($weight, $size)
	{
		$this->SetPackage($weight, $size);
	}
	
	public function SetBoxSize($size)
	{
		switch ($size)
		{
			case BoxSizeUSPS::Envelope:
				$this->Length = 12;
				$this->Width = 9.5;
				$this->Height = 0;
				break;
			case BoxSizeUSPS::Small:
				$this->Length = 8.5;
				$this->Width = 5.25;
				$this->Height = 1.5;
				break;
			case BoxSizeUSPS::Medium:
				$this->Length = 12;
				$this->Width = 9;
				$this->Height = 6;
				break;
			case BoxSizeUSPS::Large:
				$this->Length = 16;
				$this->Width = 10;
				$this->Height = 10.5;
				break;
			case BoxSizeUSPS::Test:
				$this->Length = 16;
				$this->Width = 10;
				$this->Height = 10.5;
				break;
			default:
				$this->Length = 13.5;
				$this->Width = 10.5;
				$this->Height = 11;
				break;
		}
		$this->Girth = ($this->Length + $this->Width) * 2;
	}
	
	public function SetPackage($weight, $size)
	{
			$this->Weight = $weight;
			$this->SetBoxSize($size);
	}

	public function GetXMLIntl1()
	{
		$retval = "<Pounds>".$this->Weight."</Pounds>".
			"<Ounces>0</Ounces>";
			
		return $retval;
	}
	
	public function GetXMLIntl2()
	{
		$retval = "<Container>RECTANGULAR</Container>".
			"<Size>REGULAR</Size>".
			"<Width>".$this->Width."</Width>".
			"<Length>".$this->Length."</Length>".
			"<Height>".$this->Height."</Height>".
			"<Girth>".$this->Girth."</Girth>";
		
		return $retval;
	}
	
	public function GetXML()
	{
		$retval = "<Pounds>".$this->Weight."</Pounds>".
			"<Ounces>0</Ounces>";
		
		if ($this->Height == 0)
		{
			$retval .= "<Container>FLAT RATE ENVELOPE</Container>".
				"<Size>REGULAR</Size>";
		}
		else
		{
			$retval .= "<Container />".
				"<Size>REGULAR</Size>".
				"<Width>".$this->Width."</Width>".
				"<Length>".$this->Length."</Length>".
				"<Height>".$this->Height."</Height>".
				"<Girth>".$this->Girth."</Girth>";
		}

		return $retval;
	}
}

class ServiceUSPS
{
	const PriorityMail = 0;
	const MediaMail = 1;
	const ParcelPost = 2;
	const FirstClass = 3;
	const All = 10;
	const Unspecified = 12;
	const International = 20;
	const ExpressMail = 21;
}

class BoxSizeUSPS
{
	const Envelope = 0;
	const Small = 1;
	const Medium = 2;
	const Large = 3;
	const Test = 4;
}

?>