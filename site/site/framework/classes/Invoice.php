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
			
			// if (count($row) > 0)
			// {
			// 	$this->InvoiceID = $row->id;
			// 	$this->RowGuid = $row->rguid;
			// 	$this->InvoiceNumber = $row->invnum;
			// 	$this->OrderID = $row->ordref;
			// 	$this->CustomerID = $row->custid;
			// 	$this->SubtotalAmount = $row->amount;
			// 	$this->ShippingAmount = $row->freight;
			// 	$this->TaxAmount = $row->tax;
			// 	$this->TotalAmount = $row->total;
			// 	$this->PayType = $row->paytype;
			// 	$this->PayID = $row->payid;
			// 	$this->InvoiceDate = $row->invdate;
			// 	$this->DueDate = $row->duedate;
			// 	$this->Paid = $row->paid;
			// 	$this->DatePaid = $row->datepaid;
			// 	$this->InvoiceTitle = $row->invoice_title;
			// 	$this->Division = $row->wp_division;
			// 	$this->DepositAccount = $row->depositacct;
			// 	$this->AddressID = $row->addrid;
			// 	$this->BillingAddress->AddressID = $row->addrid;
			// 	$this->BillingAddress->Contact = $row->bname;
			// 	$this->BillingAddress->Address1 = $row->baddr1;
			// 	$this->BillingAddress->Address2 = $row->baddr2;
			// 	$this->BillingAddress->City = $row->bcity;
			// 	$this->BillingAddress->State = $row->bstate;
			// 	$this->BillingAddress->Zip = $row->bzip;
			// 	$this->BillingAddress->Country = $row->code;
			// 	$this->BillingAddress->CountryCode = $row->bcountry_numcode;
			// 	$this->BillingAddress->Phone = $row->bphone;
				
			// 	$sql = "select id from cc_orders_items where ordid = ".smartQuote($this->OrderID);
			// 	$rs = $this->_db->get_results($sql);
			// 	if (count($rs) >= 1)
			// 	{
			// 		$i = new OrderItem($this->_db);
			// 		$i->GetItem($row->id);
			// 		$this->Items[] = $i;
			// 	}
			// }
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
		$out .="	\"custId\": ".$this->CustomerID.",";
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
	
	public function GetItem($id)
	{
		if (!isBlank($id))
		{
			// ***INLINESQL***
			// $sql = "select id, invid, invnum, descr, longdescr, amount, quantity, linenum, itemclass, incomeaccount ".
			// 	"from cc_invoice_items ".
			// 	"where id = ".smartQuote($id);
			// $row = $this->_db->get_row($sql);
			// if (count($row) > 0)
			// {
			// 	$this->ID = $row->id;
			// 	$this->InvoiceID = $row->invid;
			// 	$this->InvoiceNumber = $row->invnum;
			// 	$this->Product = $row->descr;
			// 	$this->Description = $row->longdescr;
			// 	$this->Amount = $row->amount;
			// 	$this->Quantity = $row->quantity;
			// 	$this->LineNumber = $row->linenum;
			// 	$this->ItemClass = $row->itemclass;
			// 	$this->IncomeAccount = $row->incomeaccount;
			// }
		}
	}
	
	public function SaveItem()
	{
		if ($this->InvoiceID == "" || $this->LineNumber <= 0)
			return;
		
		if ($this->ID == 0)
		{
			// ***INLINESQL***
			// $sql = "set nocount on; ".
			// 	"insert into cc_invoice_items(invid, invnum, descr, longdescr, amount, quantity, linenum, itemclass, incomeaccount) values(".
			// 		smartQuote($this->InvoiceID).", ".
			// 		smartQuote($this->InvoiceNumber).", ".
			// 		smartQuote($this->Product).", ".
			// 		smartQuote($this->Description).", ".
			// 		smartQuote($this->Amount).", ".
			// 		smartQuote($this->Quantity).", ".
			// 		smartQuote($this->LineNumber).", ".
			// 		smartQuote($this->ItemClass).", ".
			// 		smartQuote($this->IncomeAccount)."); ".
			// 	"select @@identity as id;";
			// $this->ID = $this->_db->get_var($sql);
		}
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
		$out .="		},";
	
		return $out;
	}
}




?>