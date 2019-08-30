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
	
	// ***INLINESQL***
	// private $_db;
	
	public function __construct()
	{
		// $this->_db = $db;
		$this->BillingAddress = new Address();
	}
	
	public function GetInvoice($invnum)
	{
		if (!isBlank($invnum))
		{
			// ***INLINESQL***
			// $sql = "select i.id, i.ordref, i.invnum, i.custid, i.amount, i.freight, i.tax, i.total, i.paytype, i.addrid, i.payid, i.invdate, ".
			// 	"	i.duedate, i.paid, i.datepaid, i.bname, i.baddr1, i.baddr2, i.bcity, i.bstate, i.bzip, i.bcountry_numcode, c.code, ".
			// 	"	i.bphone, i.invoice_title, i.wp_division, i.depositacct, i.rguid ".
			// 	"from cc_invoice as i ".
			// 	"	inner join cc_countries as c on (c.numcode = i.bcountry_numcode) ".
			// 	"where i.invnum = ".smartQuote($invnum);
			// $row = $this->_db->get_row($sql);
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
		{
			// ***INLINESQL***
			// $sql = "exec pw_newSalesOrderID";
			// $this->InvoiceNumber = $this->_db->get_var($sql);
			
			// $sql = "set nocount on;".
			// 	"insert into cc_invoice(ordref, invnum, custid, amount, freight, tax, total, paytype, ".
			// 	"	payid, invdate, duedate, paid, datepaid, addrid, bname, baddr1, baddr2, bcity, bstate, ".
			// 	"	bzip, bcountry_numcode, bphone, invoice_title, wp_division, depositacct) values (".
			// 	smartQuote($this->OrderID).", ".
			// 	smartQuote($this->InvoiceNumber).", ".
			// 	smartQuote($this->CustomerID).", ".
			// 	smartQuote($this->SubtotalAmount).", ".
			// 	smartQuote($this->ShippingAmount).", ".
			// 	smartQuote($this->TaxAmount).", ".
			// 	smartQuote($this->TotalAmount).", ".
			// 	smartQuote($this->PayType).", ".
			// 	smartQuote($this->PayID).", ".
			// 	"getdate(), ".
			// 	"DateAdd(d, 30, getdate()), ".
			// 	smartQuote($this->Paid ? 1 : 0).", ".
			// 	($this->Paid ? "getdate()" : "NULL").", ".
			// 	smartQuote($this->BillingAddress->AddressID).", ".
			// 	smartQuote($this->BillingAddress->Contact).", ".
			// 	smartQuote($this->BillingAddress->Address1).", ".
			// 	smartQuote($this->BillingAddress->Address2).", ".
			// 	smartQuote($this->BillingAddress->City).", ".
			// 	smartQuote($this->BillingAddress->State).", ".
			// 	smartQuote($this->BillingAddress->Zip).", ".
			// 	smartQuote($this->BillingAddress->CountryCode).", ".
			// 	smartQuote($this->BillingAddress->Phone).", ".
			// 	smartQuote($this->InvoiceTitle).", ".
			// 	smartQuote($this->Division).", ".
			// 	smartQuote($this->DepositAccount)."); ".
			// 	"select @@identity as id";
			// $this->InvoiceID = $this->_db->get_var($sql);
			
			$num = 0;
			foreach ($this->Items as &$item)
			{
				$num++;
				$item->InvoiceID = $this->InvoiceID;
				$item->InvoiceNumber = $this->InvoiceNumber;
				$item->LineNumber = $num;
				$item->SaveItem();
			}
		}
	}
	
	public function SavePayment($id)
	{
		// ***INLINESQL***
		// $sql = "insert into cc_invoice_payments(custid, invnum, paytype, payid, amount, paydate) values(".
		// 	smartQuote($this->CustomerID).", ".
		// 	smartQuote($this->InvoiceNumber).", ".
		// 	"'CREDIT', ".
		// 	smartQuote($id == "" ? "{00000000-0000-0000-0000-000000000000}" : $id).", ".
		// 	smartQuote($this->TotalAmount).", ".
		// 	"getdate())";
		// $this->_db->query($sql);
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
}




?>