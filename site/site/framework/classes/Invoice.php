<?php

class Invoice
{
	public $InvoiceID = 0;
	public $InvoiceNumber = "";
	public $OrderID = "";
	public $CustomerID = "";
	public $SubtotalAmount = 0.0;
	public $ShippingAmount = 0.0;
	public $TaxAmount = 0.0;
	public $TotalAmount = 0.0;
	public $PayType = "CREDIT";
	public $PayID = 0;
	public $InvoiceDate = NULL;
	public $DueDate = NULL;
	public $Paid = false;
	public $DatePaid = NULL;
	public $AddressID = 0;
	public $BillingAddress = NULL;
	public $InvoiceTitle = "Java-Perks.com Order";
	public $Order = NULL;
	public $Items = array();
	
	private $CustomerApi = "";
		
	public function __construct()
	{
		global $customerapi;

		$this->CustomerApi = $customerapi;

		$this->BillingAddress = new Address();
	}

	public function GenerateInvoiceID()
	{
		$onum = sprintf("%02d", rand(2500, 98943));
		return "INV".date("Ydm").$onum;
	}

	public function GetInvoice($invnum)
	{
		if (!isBlank($invnum))
		{
			$rr = new RestRunner();
			$row = $rr->Get($this->CustomerApi."/invoice/".$invnum);
			if (count($row) > 0)
			{
				$this->InvoiceID = $row->invoiceId;
				$this->InvoiceNumber = $row->invoiceNumber;
				$this->OrderID = $row->orderId;
				$this->CustomerID = $row->custId;
				$this->SubtotalAmount = $row->amount;
				$this->ShippingAmount = $row->shipping;
				$this->TaxAmount = $row->tax;
				$this->TotalAmount = $row->total;
				$this->InvoiceDate = $row->invoiceDate;
				$this->DatePaid = $row->datePaid;
				$this->InvoiceTitle = $row->title;
				$this->BillingAddress->Contact = $row->contact;
				$this->BillingAddress->Address1 = $row->address1;
				$this->BillingAddress->Address2 = $row->address2;
				$this->BillingAddress->City = $row->city;
				$this->BillingAddress->State = $row->state;
				$this->BillingAddress->Zip = $row->zip;
				$this->BillingAddress->Phone = $row->phone;
				
				foreach ($row->items as $item)
				{
					$i = new InvoiceItem();
					$i->ItemID = $item->itemId;
					$i->InvoiceID = $item->invoiceId;
					$i->Product = $item->product;
					$i->Description = $item->description;
					$i->Amount = $item->amount;
					$i->Quantiy = $item->quantity;
					$i->LineNumber = $item->lineNumber;

					$this->Items[] = $i;
				}
			}
		}
	}
	
	public function Save()
	{
		if ($this->InvoiceNumber == "")
			$this->InvoiceNumber = $this->GenerateInvoiceID();
		
		$num = 0;
		foreach ($this->Items as &$item)
		{
			$num++;
			$item->InvoiceID = $this->InvoiceID;
			$item->InvoiceNumber = $this->InvoiceNumber;
			$item->LineNumber = $num;
		}

		$request = $this->CustomerApi."/invoice";
		$rr = new RestRunner();
		$rr->SetHeader("Content-Type", "application/json");
		$retval = $rr->Post($request, $this->OutputJson());
		$value = json_decode($retval->message);
		return $value->invoiceId;
	}
	
	public function OutputJson()
	{
		$out = "";
		$items = "";

		foreach ($this->Items as $item)
		{
			$items .= $item->OutputJson() . ",";
		}

		if ($items != "")
			$items = substr($items, 0, -1);

		$out .="{";
		$out .="	\"invoiceId\": ".$this->InvoiceID.",";
		$out .="	\"invoiceNumber\": \"".$this->InvoiceNumber."\",";
		$out .="	\"custId\": \"".$this->CustomerID."\",";
		$out .="	\"invoiceDate\": \"".date("Y-d-m H:i:s")."\",";
		$out .="	\"orderId\": \"".$this->OrderID."\",";
		$out .="	\"title\": \"".$this->InvoiceTitle."\",";
		$out .="	\"amount\": ".$this->SubtotalAmount.",";
		$out .="	\"tax\": ".$this->TaxAmount.",";
		$out .="	\"shipping\": ".$this->ShippingAmount.",";
		$out .="	\"total\": ".$this->TotalAmount.",";
		$out .="	\"datePaid\": \"".date("Y-d-m H:i:s")."\",";
		$out .="	\"contact\": \"".$this->BillingAddress->Contact."\",";
		$out .="	\"address1\": \"".$this->BillingAddress->Address1."\",";
		$out .="	\"address2\": \"".$this->BillingAddress->Address2."\",";
		$out .="	\"city\": \"".$this->BillingAddress->City."\",";
		$out .="	\"state\": \"".$this->BillingAddress->State."\",";
		$out .="	\"zip\": \"".$this->BillingAddress->Zip."\",";
		$out .="	\"phone\": \"".$this->BillingAddress->Phone."\",";
		$out .="	\"items\": [";
		$out .= $items;
		$out .="	]";
		$out .="}";

		return $out;
	}
}

class InvoiceItem
{
	public $ID = 0;
	public $InvoiceID = -1;
	public $InvoiceNumber = "";
	public $Product = "";
	public $Description = "";
	public $Amount = 0.0;
	public $Quantity = 0;
	public $LineNumber = 0;
	
	public function __construct()
	{
	}
	
	public function OutputJson() {
		$out = "";

		$out .="		{";
		$out .="			\"itemId\": ".$this->ID.",";
		$out .="			\"invoiceId\": ".$this->InvoiceID.",";
		$out .="			\"product\": \"".$this->Product."\",";
		$out .="			\"description\": \"".$this->Description."\",";
		$out .="			\"amount\": ".$this->Amount.",";
		$out .="			\"quantity\": ".$this->Quantity.",";
		$out .="			\"lineNumber\": ".$this->LineNumber;
		$out .="		}";
	
		return $out;
	}
}




?>