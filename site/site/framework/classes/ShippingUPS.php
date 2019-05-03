<?php

class ShippingUPS
{
	public $PackageNumber = 1;
	public $ShipToState = "";
	public $ShipToZip = "";
	public $ShipToCountry = "";
	public $ShipFromState = "";
	public $ShipFromZip = "";
	public $ShipFromCountry = "";
	public $UserID = "";
	public $Password = "";
	public $LicenseKey = "";
	public $Residential = false;
	public $ShipperNumber = "";
	public $Items = array();
	public $Service = NULL;
	public $Rates = array();
	
	public function __construct()
	{
		$this->Service = ServiceUPS::Ground;
	}
	
	public function AddPackage($weight, $size)
	{
			$this->Items[] = new PackageDataUPS($weight, $size);
			$this->PackageNumber++;
	}
	
	public function GetServiceName($servicecode)
	{
		switch ($servicecode)
		{
			case "01":
				return ServiceUPS::NextDayAir;
			case "02":
				return ServiceUPS::SecondDayAir;
			case "03":
				return ServiceUPS::Ground;
			case "07":
				return ServiceUPS::WorldwideExpress;
			case "08":
				return ServiceUPS::WorldwideExpedited;
			case "11":
				return ServiceUPS::WorldwideStandard;
			case "12":
				return ServiceUPS::ThreeDaySelect;
			case "13":
				return ServiceUPS::NextDayAirSaver;
			case "14":
				return ServiceUPS::NextDayAirEarly;
			case "54":
				return ServiceUPS::WorldwideExpressPlus;
			case "59":
				return ServiceUPS::SecondDayAirAM;
			case "65":
				return ServiceUPS::WorldwideSaver;
			default:
				return ServiceUPS::Unspecified;
		}
	}
	
	public function GetXML()
	{
		$retval = "";
		$svccode = "01";
		$pickupcode = "03";
		$classcode = "04";

		switch ($this->Service)
		{
			case ServiceUPS::NextDayAir:
				$svccode = "01";
				break;
			case ServiceUPS::SecondDayAir:
				$svccode = "02";
				break;
			case ServiceUPS::ThreeDaySelect:
				$svccode = "12";
				break;
			case ServiceUPS::Ground:
				$svccode = "03";
				break;
			case ServiceUPS::WorldwideStandard:
				$svccode = "11";
				break;
			case ServiceUPS::WorldwideExpress:
				$svccode = "07";
				break;
			case ServiceUPS::WorldwideExpedited:
				$svccode = "08";
				break;
			case ServiceUPS::WorldwideSaver:
				$svccode = "65";
				break;
		}

		if ($this->ShipperNumber != "")
		{
			$pickupcode = "01";
			$classcode = "01";
		}

		if (count($this->Items) > 0 && $this->UserID != "" && $this->ShipToZip != "" && $this->ShipFromZip != "")
		{
			if ($this->ShipToCountry != "US" && $this->Service == ServiceUPS::Ground) $this->Service = ServiceUPS::WorldwideStandard;
			
			$retval .= "<?xml version=\"1.0\"?>". 
				"<AccessRequest xml:lang=\"en-US\">". 
				"	<AccessLicenseNumber>".$this->LicenseKey."</AccessLicenseNumber>". 
				"	<UserId>".$this->UserID."</UserId>". 
				"	<Password>".$this->Password."</Password>". 
				"</AccessRequest>";

			$retval .= "<?xml version=\"1.0\"?>".
				"<RatingServiceSelectionRequest xml:lang=\"en-US\">".
				"	<Request>".
				"		<TransactionReference>".
				"			<CustomerContext>Bare Bones Rate Request</CustomerContext>".
				"			<XpciVersion>1.0001</XpciVersion>".
				"		</TransactionReference>".
				"		<RequestAction>Rate</RequestAction>".
				"		<RequestOption>Shop</RequestOption>".
				"	</Request>".
				"	<PickupType>".
				"		<Code>".$pickupcode."</Code>".
				"	</PickupType>".
				"	<CustomerClassification>".
				"		<Code>".$classcode."</Code>".
				"	</CustomerClassification>".
				"	<Shipment>";
			if ($this->ShipperNumber != "")
			{
				$retval .= "".
				"		<RateInformation>".
				"			<NegotiatedRatesIndicator />".
				"		</RateInformation>".
				"		<Shipper>".
				"			<ShipperNumber>".$this->ShipperNumber."</ShipperNumber>".
				"			<Name>Java Perks</Name>".
				"			<Address>".
				"				<Address1>101 Second St.</Address1>".
				"				<Address2>Suite #700</Address2>".
				"				<Address3 />".
				"				<City>San Francisco</City>".
				"				<StateProvinceCode>CA</StateProvinceCode>".
				"				<PostalCode>94105</PostalCode>".
				"				<CountryCode>US</CountryCode>".
				"			</Address>".
				"		</Shipper>";
			}
			else
			{
				$retval .= "".
				"		<Shipper>".
				"			<Address>".
				"				<StateProvinceCode>".$this->ShipFromState."</StateProvinceCode>".
				"				<PostalCode>".$this->ShipFromZip."</PostalCode>".
				"				<CountryCode>".$this->ShipFromCountry."</CountryCode>".
				"			</Address>".
				"		</Shipper>";
			}
			$retval .= "".
				"		<ShipTo>".
				"			<Address>".
				"				<StateProvinceCode>".$this->ShipToState."</StateProvinceCode>".
				"				<PostalCode>".$this->ShipToZip."</PostalCode>".
				"				<CountryCode>".$this->ShipToCountry."</CountryCode>";
			if ($this->Residential)
				$retval .= "".
				"				<ResidentialAddressIndicator/>";
			$retval .= "".
				"			</Address>".
				"		</ShipTo>".
				"		<ShipFrom>".
				"			<Address>".
				"				<StateProvinceCode>".$this->ShipFromState."</StateProvinceCode>".
				"				<PostalCode>".$this->ShipFromZip."</PostalCode>".
				"				<CountryCode>".$this->ShipFromCountry."</CountryCode>".
				"			</Address>".
				"		</ShipFrom>".
				"		<Service>".
				"			<Code>".$svccode."</Code>".
				"		</Service>";
			
			foreach ($this->Items as $package)
			{
				$retval .= $package->GetXML();
			}

			$retval .= "	</Shipment>".
				"</RatingServiceSelectionRequest>";
		}

		return $retval;
	}
	
	public function Send()
	{
		$postdata = $this->GetXML();
		$request = "https://www.ups.com/ups.app/xml/Rate";
		$tmprate = 0;
		$shiptype = "";
		$response = "";
		unset($this->Rates);
		
		
		$ch = curl_init();
		
		curl_setopt($ch, CURLOPT_URL, $request);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		
		$response = curl_exec($ch);
		
		curl_close($ch);
		
		$xml = simplexml_load_string($response);
		
		if ($xml->Response->ResponseStatusCode == 1)
		{
			foreach ($xml->RatedShipment as $pkg)
			{
				$this->Rates[] = new ShippingRateUPS($this->GetServiceName($pkg->Service->Code), floatval($pkg->RatedPackage->TotalCharges->MonetaryValue));
			}
		}
	}
}

class ShippingRateUPS
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
			case ServiceUPS::NextDayAirEarly:
				return "Next Day Air Early";
			case ServiceUPS::NextDayAir:
				return "Next Day Air";
			case ServiceUPS::NextDayAirSaver:
				return "Next Day Air Saver";
			case ServiceUPS::SecondDayAirAM:
				return "2nd Day Air AM";
			case ServiceUPS::SecondDayAir:
				return "2nd Day Air";
			case ServiceUPS::ThreeDaySelect:
				return "3 Day Select";
			case ServiceUPS::Ground:
				return "Ground";
			case ServiceUPS::WorldwideStandard:
				return "Worldwide Standard";
			case ServiceUPS::WorldwideExpress:
				return "Worldwide Express";
			case ServiceUPS::WorldwideExpressPlus:
				return "Worldwide Express Plus";
			case ServiceUPS::WorldwideExpedited:
				return "Worldwide Expedited";
			case ServiceUPS::WorldwideSaver:
				return "Worldwide Saver";
			default:
				return "Unspecified";
		}
	}
	
	public function LSICode()
	{
		switch ($this->Service)
		{
			case ServiceUPS::NextDayAir:
				return "UPSNDAR";
			case ServiceUPS::SecondDayAir:
				return "UPSSDAR";
			case ServiceUPS::ThreeDaySelect:
				return "UPS3DASR";
			case ServiceUPS::Ground:
				return "UPSGSRNA";
			case ServiceUPS::WorldwideExpress:
				return "UPSWEXP";
			case ServiceUPS::WorldwideExpedited:
				return "UPSWWX";
			case ServiceUPS::WorldwideSaver:
				return "UPSWEXS";
			default:
				return "";
		}
	}
}

class PackageDataUPS
{
	public $Length = 13.5;
	public $Width = 10.5;
	public $Height = 11;
	public $Weight = 20;
	
	public function __construct($weight, $size)
	{
		$this->SetPackage($weight, $size);
	}
	
	public function SetBoxSize($size)
	{
		switch ($size)
		{
			case BoxSizeUPS::Small:
				$this->Length = 13.5;
				$this->Width = 10.5;
				$this->Height = 3;
				break;
			case BoxSizeUPS::Medium:
				$this->Length = 13.5;
				$this->Width = 10.5;
				$this->Height = 11;
				break;
			case BoxSizeUPS::Large:
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
	}
	
	public function SetPackage($weight, $size)
	{
			$this->Weight = $weight;
			$this->SetBoxSize($size);
	}

	public function GetXML()
	{
		$retval = "		<Package>".
				"			<PackagingType>".
				"				<Code>02</Code>".
				"			</PackagingType>".
				"			<Dimensions>".
				"				<UnitOfMeasurement>".
				"					<Code>IN</Code>".
				"				</UnitOfMeasurement>".
				"				<Length>".$this->Length."</Length>".
				"				<Width>".$this->Width."</Width>".
				"				<Height>".$this->Height."</Height>".
				"			</Dimensions>".
				"			<PackageWeight>".
				"				<UnitOfMeasurement>".
				"					<Code>LBS</Code>".
				"				</UnitOfMeasurement>".
				"				<Weight>".$this->Weight."</Weight>".
				"			</PackageWeight>".
				"		</Package>";

		return $retval;
	}
}

class ServiceUPS
{
	const NextDayAir = 0;
	const NextDayAirSaver = 1;
	const NextDayAirEarly = 2;
	const SecondDayAir = 3;
	const SecondDayAirAM = 4;
	const ThreeDaySelect = 5;
	const Ground = 6;
	const WorldwideStandard = 7;
	const WorldwideExpress = 8;
	const WorldwideExpressPlus = 9;
	const WorldwideExpedited = 10;
	const WorldwideSaver = 11;
	const Unspecified = 12;
}

class BoxSizeUPS
{
	const Small = 0;
	const Medium = 1;
	const Large = 2;
}


?>