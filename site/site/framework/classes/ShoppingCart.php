<?php

class ShoppingCart
{
	public $ShippingAddress = NULL;
	public $BillingAddress = NULL;
	public $Checkout = false;
	public $LastError = "";
	public $ShippingAmount = 0.0;
	public $SubtotalAmount = 0.0;
	public $TaxAmount = 0.0;
	public $TotalAmount = 0.0;
	public $Order = NULL;
	public $Comments = "";
	
	private $CartApi = "";

	public function __construct()
	{
		global $cartapi;

		$this->CartApi = $cartapi."/cart";
	}
	
	// public function Contains($pid)
	// {
	// 	$answer = NULL;
		
	// 	// foreach ($this->Items as $item)
	// 	// {
	// 	// 	if ($item->PID == $pid)
	// 	// 		$answer = $item;
	// 	// }
		
	// 	return $answer;
	// }
	
	public function Count()
	{
		$answer = 0;

		$r = new RestRunner();

		$result = $r->Get($this->CartApi, null);
		$answer = $result->Count;
		
		return $answer;
	}
	
	// public function CleanCart()
	// {
	// 	// foreach ($this->Items as $key => &$item)
	// 	// {
	// 	// 	if ($item->Quantity == 0)
	// 	// 		unset($this->Items[$key]);
	// 	// }
	// }
	
	public function AddItem($pid, $qty)
	{
		$r = new RestRunner();

		$itempid = array('Key' => 'productId', 'Value' => $pid);
		$itemqty = array('Key' => 'quantity', 'Value' => $qty);
		$itemsid = array('Key' => 'sessionId', 'Value' => session_id());
		$a = array($itempid, $itemqty, $itemsid);

		$p = new Product();
		$p->GetProduct($pid);

		$result = $r->Post($this->CartApi, $a);
	}
	
	
	// public function UpdateItem($pid, $qty)
	// {
	// 	// foreach ($this->Items as &$item)
	// 	// {
	// 	// 	if ($item->PID == $pid)
	// 	// 	{
	// 	// 		$item->Quantity += $qty;
	// 	// 		$add = true;
	// 	// 	}
	// 	// }
	// }
	
	// public function DeleteItem($pid, $landing=false)
	// {
	// 	// foreach ($this->Items as $key => &$item)
	// 	// {
	// 	// 	if ($item->PID == $pid)
	// 	// 	{
	// 	// 		unset($this->Items[$key]);
	// 	// 	}
	// 	// }
	// }
	
	/*
	 *	Function: 	HideSidebar()
	 *	
	 *	Summary:	Hides the left-hand sidebar
	 *	
	 *	Parameters:	No parameters
	 *	
	 *	Returns:	No return value
	 *	
	 */
	public function HideSidebar()
	{
		$out = "";
		
		$out .= "<style>\n";
		$out .= "	aside.sidebar {\n";
		$out .= "		display: none;\n";
		$out .= "	}\n";
		$out .= "	div.content {\n";
		$out .= "		width: 100%;\n";
		$out .= "		padding: 25px;\n";
		$out .= "	}\n";
		$out .= "</style>\n";
		
		return $out;
	}
	
	public function CartEmpty()
	{
		$out = "";
		
		$out .= "<div class=\"empty-cart\">\n";
		$out .= "	<div class=\"empty-cart-heading\">\n";
		$out .= "		<strong>Uh-oh! Your shopping cart is empty.</strong><br>Why not try a few products below?\n";
		$out .= "	</div>\n";
		$out .= "	<div class=\"clearfloat\"></div>\n";
		$out .= "	<div class=\"product-list\">\n";
		// $out .= $this->ShowAdditionalProducts(NULL, 5);
		$out .= "	</div>\n";
		$out .= "	<div class=\"clearfloat\"></div>\n";
		$out .= "</div>\n";
		
		return $out;
	}
	
	public function Breadcrumbs($pos)
	{
		$out = "";
		$ident = " style=\"font-weight:bold;\"";
		$review = "";
		$bill = "";
		$ship = "";
		$pay = "";
		
		switch($pos)
		{
			case "review":
				$review = $ident;
				break;
			case "bill":
				$bill = $ident;
				break;
			case "ship":
				$ship = $ident;
				break;
			case "pay":
				$pay = $ident;
				break;
		}
		
		$out .= "	<div class=\"breadcrumbs\">\n";
		$out .= "		<ul>\n";
		$out .= "			<li".$review."><a>Review Cart</a></li>\n";
		$out .= "			<li".$bill."><a>Billing</a></li>\n";
		$out .= "			<li".$ship."><a>Ship Method</a></li>\n";
		$out .= "			<li".$pay."><a>Confirm/Payment</a></li>\n";
		$out .= "		</ul>\n";
		$out .= "	</div>\n";
		
		return $out;
	}
	
	// public function StartOver()
	// {
	// 	$this->ShippingAddress = NULL;
	// 	$this->BillingAddress = NULL;
	// 	$this->Checkout = false;
	// 	$this->LastError = "";
	// 	$this->ShippingService = "CUSTOM";
	// 	$this->ShippingAmount = 0.0;
	// 	$this->SubtotalAmount = 0.0;
	// 	$this->TaxAmount = 0.0;
	// 	$this->TotalAmount = 0.0;
	// 	$this->Order = NULL;
	// 	$this->Comments = "";
	// }
	
	// public function PleaseWait()
	// {
	// 	$out = "";
		
	// 	$out .= "	<div class=\"order-signin\" style=\"position: relative;\">\n";
	// 	$out .= "		<div class=\"order-summary-heading\">PROCESSING</div>\n";
	// 	$out .= "		<div style=\"padding:15px;font-size:16px;\"><img src=\"/framework/img/wait24trans.gif\" style=\"vertical-align:middle; border:0px; width:24px; height:24px;\"> Please wait while your order is being processed...</div>\n";
	// 	$out .= "		<div class=\"clearfloat\"></div>\n";
	// 	$out .= "	</div>\n";
		
	// 	return $out;
	// }
	
	// public function PlaceOrder($landing=false)
	// {
	// 	$urltag = $landing ? "special" : "shop";
	// 	if (isBlank($this->ShippingAddress->AddressID)) $this->ShippingAddress->AddressID = 0;
	// 	if (isBlank($this->BillingAddress->AddressID)) $this->BillingAddress->AddressID = 0;
		
	// 	$ispaid = false;
	// 	$stopbook = false;
	// 	$haspod = false;
	// 	$initstatus = "HD";
	// 	$status = "Pending Payment";
	// 	$invduedate = time();
	// 	$payid = "";
	// 	$payguid = "";
	// 	$order = new Order();
	// 	$invoice = new Invoice();
	// 	$xc = new CreditCard();
		
	// 	if ($this->TmpOrderID == "")
	// 		$this->TmpOrderID = $order->GenerateOrderID();
		
	// 	$this->PayMethod = $this->PayType;
		
	// 	switch ($this->PayType)
	// 	{
	// 		case "CHECK":
	// 		case "NET30":
	// 			$invduedate = time()+(60*60*24*30);
	// 			$payid = "";
	// 			$initstatus = "HD";
	// 			break;
	// 		case "NET45":
	// 			$invduedate = time()+(60*60*24*45);
	// 			$payid = "";
	// 			$initstatus = "HD";
	// 			break;
	// 		case "NET60":
	// 			$invduedate = time()+(60*60*24*60);
	// 			$payid = "";
	// 			$initstatus = "HD";
	// 			break;
	// 		case "NET90":
	// 			$invduedate = time()+(60*60*24*90);
	// 			$payid = "";
	// 			$initstatus = "HD";
	// 			break;
	// 		case "CASH":
	// 			$invduedate = time();
	// 			$payid = "";
	// 			$initstatus = "HD";
	// 			break;
	// 		case "NONE":
	// 			$invduedate = time();
	// 			$payid = "";
	// 			$initstatus = "HD";
	// 			$ispaid = true;
	// 			break;
	// 		case "NEW";
	// 			$invduedate = time()+(60*60*24*30);
	// 			$payid = "";
	// 			$initstatus = "HD";
	// 			$this->PayMethod = "CREDIT";
	// 			break;
	// 		default:
	// 			if (strlen($this->PayType) == 32)
	// 			{
	// 				$payguid = $this->PayType;
	// 				$formatted = Utilities::FormatGuid($payguid);
					
	// 				// ***INLINESQL***
	// 				// $sql = "select id, cardname, cardtype, cardnum, cvv, expmo, expyr from cc_moulah where rguid = ".smartQuote($formatted);
	// 				// $row = $this->_db->get_row($sql);
	// 				// if (count($row) > 0)
	// 				// {
	// 				// 	$this->PayMethod = "CREDIT";
	// 				// 	$this->CardName = $row->cardname;
	// 				// 	$this->CardType = $row->cardtype;
	// 				// 	$this->CardNumber = $xc->DecodeNumber($row->cardnum);
	// 				// 	$this->CardCVV = $row->cvv;
	// 				// 	$this->CardExpMonth = intval($row->expmo);
	// 				// 	$this->CardExpYear = intval($row->expyr);
	// 				// 	$payid = $row->id;
	// 				// 	$invduedate = time()+(60*60*24*30);
	// 				// 	$initstatus = "HD";
	// 				// }
	// 				// else
	// 				// {
	// 				// 	$this->LastError = "There was a problem with the credit card you selected. Please try again.";
	// 				// 	header("Location: /".$urltag."/cart/confirm");
	// 				// 	exit();
	// 				// }
	// 			}
	// 			else
	// 			{
	// 				$this->LastError = "Please select a method of payment";
	// 				header("Location: /".$urltag."/cart/confirm");
	// 				exit();
	// 			}
	// 			break;
	// 	}
		
	// 	/******************************************************
	// 	 * PROCESS CREDIT CARD
	// 	 ******************************************************/
		
	// 	if ($this->PayMethod == "CREDIT")
	// 	{
	// 		// FOR TESTING:
	// 		//$r = $this->RunTransaction(true, "APPROVED");
	// 		$r = $this->RunTransaction();
			
	// 		if ($r == "APPROVED")
	// 		{
	// 			$ispaid = true;
	// 			//$status = "Paid";
	// 			if ($this->PayType == "NEW")
	// 			{
	// 				// ***INLINESQL***
	// 				// $sql = "set nocount on; insert into cc_moulah(custid, cardname, cardnum, cardtype, cvv, expmo, expyr, active) values(".
	// 				// 	smartQuote($_SESSION["__account__"]->CustomerID).", ".
	// 				// 	smartQuote($this->CardName).", ".
	// 				// 	smartQuote($xc->EncodeNumber($this->CardNumber)).", ".
	// 				// 	smartQuote($this->CardType).", ".
	// 				// 	smartQuote($this->CardCVV).", ".
	// 				// 	smartQuote($this->CardExpMonth).", ".
	// 				// 	smartQuote($this->CardExpYear).", ".
	// 				// 	($this->SaveCard ? "1" : "0")."); select @@identity as id";
	// 				// $payid = $this->_db->get_var($sql);
					
	// 				// $sql = "select rguid from cc_moulah where id = ".smartQuote($payid);
	// 				// $payguid = mssql_guid_string($this->_db->get_var($sql));
	// 			}
	// 		}
	// 		else
	// 		{
	// 			$this->LastError = "Your card was declined for this transaction. Please select a different payment.";
	// 			header("Location: /".$urltag."/cart/confirm");
	// 			exit();
	// 		}
	// 	}
		
	// 	/******************************************************
	// 	 * SAVE ORDER AND INVOICE
	// 	 ******************************************************/
		
	// 	if ($this->SaveAddresses)
	// 	{
	// 		try
	// 		{
	// 			$this->BillingAddress->CustomerID = $_SESSION["__account__"]->CustomerID;
	// 			$this->BillingAddress->SaveAddress();
	// 		}
	// 		catch (Exception $ex)
	// 		{
	// 			//echo "Billing Address - ".$ex->getMessage();
	// 			//exit();
	// 		}
			
	// 		try
	// 		{
	// 			$this->ShippingAddress->CustomerID = $_SESSION["__account__"]->CustomerID;
	// 			$this->ShippingAddress->SaveAddress();
	// 		}
	// 		catch (Exception $ex)
	// 		{
	// 			//echo "Billing Address - ".$ex->getMessage();
	// 			//exit();
	// 		}
	// 	}
		
	// 	if ($ispaid == true) $status = "Paid";
		
	// 	$order->TmpOrderID = $this->TmpOrderID;
	// 	$order->CustomerID = $_SESSION["__account__"]->CustomerID;
	// 	$order->OrderType = "S";
	// 	$order->SubtotalAmount = $this->SubtotalAmount;
	// 	$order->ShippingAmount = $this->ShippingAmount;
	// 	$order->TaxAmount = $this->TaxAmount;
	// 	$order->TotalAmount = $this->TotalAmount;
	// 	$order->Status = $status;
	// 	$order->PromoCode = $this->PromoCode;
	// 	$order->CustomerSource = $this->Source;
	// 	$order->Comments = $this->Comments;
	// 	$order->ShipMethod = $this->ShippingService;
	// 	$order->ShippingAddress = clone $this->ShippingAddress;
		
	// 	$invoice->CustomerID = $_SESSION["__account__"]->CustomerID;
	// 	$invoice->SubtotalAmount = $this->SubtotalAmount;
	// 	$invoice->ShippingAmount = $this->ShippingAmount;
	// 	$invoice->TaxAmount = $this->TaxAmount;
	// 	$invoice->TotalAmount = $this->TotalAmount;
	// 	$invoice->PayType = $this->PayMethod;
	// 	$invoice->PayID = $payid;
	// 	$invoice->Paid = $ispaid;
	// 	$invoice->BillingAddress = clone $this->BillingAddress;
		
	// 	if ($landing)
	// 	{
	// 		foreach ($this->LandingItems as $item)
	// 		{
	// 			$pickable = false;
	// 			$p = new Product();
	// 			$p->GetProduct($item->PID);
	// 			$p->CalculateValues();
				
				
	// 			$d = new OrderItem();
	// 			$d->PID = $p->PID;
	// 			$d->Description = $p->ProductName;
	// 			$d->ISBN = $p->ISBN;
	// 			$d->Quantity = $item->Quantity;
	// 			$d->Price = $p->Price;
	// 			$d->Discount = $p->CalculatedDiscount;
	// 			$d->Pickable = $item->Fulfillment;
	// 			$order->Items[] = $d;
				
	// 			$i = new InvoiceItem();
	// 			$i->Title = $p->ProductName;
	// 			$i->Amount = $p->CalculatedPrice;
	// 			$i->Quantity = $item->Quantity;
	// 			$invoice->Items[] = $i;
	// 		}
	// 		foreach ($this->LandingFreeItems as $item)
	// 		{
	// 			$p = new Product();
	// 			$p->GetProduct($item->PID);
				
	// 			$d = new OrderItem();
	// 			$d->PID = $p->PID;
	// 			$d->Description = $p->ProductName;
	// 			$d->ISBN = $p->ISBN;
	// 			$d->Quantity = 1;
	// 			$d->Price = 0;
	// 			$d->Discount = 0;
	// 			$d->Pickable = false;
	// 			$order->Items[] = $d;
				
	// 			$i = new InvoiceItem();
	// 			$i->Title = $p->ProductName;
	// 			$i->Amount = 0;
	// 			$i->Quantity = 1;
	// 			$invoice->Items[] = $i;
				
	// 		}
	// 	}
	// 	else
	// 	{
	// 		foreach ($this->Items as $item)
	// 		{
	// 			$pickable = false;
	// 			$p = new Product();
	// 			$p->GetProduct($item->PID);
	// 			$p->CalculateValues();
				
				
	// 			$d = new OrderItem();
	// 			$d->PID = $p->PID;
	// 			$d->Description = $p->ProductName;
	// 			$d->ISBN = $p->ISBN;
	// 			$d->Quantity = $item->Quantity;
	// 			$d->Price = $p->Price;
	// 			$d->Discount = $p->CalculatedDiscount;
	// 			$d->Pickable = $item->Fulfillment;
	// 			$order->Items[] = $d;
				
	// 			$i = new InvoiceItem();
	// 			$i->Title = $p->ProductName;
	// 			$i->Amount = $p->CalculatedPrice;
	// 			$i->Quantity = $item->Quantity;
	// 			$invoice->Items[] = $i;
	// 		}
	// 	}
		
	// 	$order->Save();
	// 	$invoice->OrderID = $order->OrderID;
	// 	$invoice->Save();
	// 	if ($ispaid)
	// 	{
	// 		$invoice->SavePayment($payguid);
	// 	}
		
	// 	$this->Order = $order;
		
	// 	/******************************************************
	// 	 * EMAIL CONFIRMATION
	// 	 ******************************************************/
		
	// 	require_once("framework/plugins/swift/lib/swift_required.php");
		
	// 	$message = Swift_Message::newInstance();
	// 	$message->setSubject("Your order from java-perks.com");
	// 	$message->setFrom(array("no_reply@java-perks.com" => "Java Perks Store"));
	// 	$message->setTo(array($_SESSION["__account__"]->Email => $_SESSION["__account__"]->FullName()));
	// 	$message->setBody($this->Order->DisplayOrder(), "text/html");
		
	// 	$transport = Swift_SmtpTransport::newInstance("email.java-perks.com", 25);
	// 	//$transport->setUsername("noc");
	// 	//$transport->setPassword("All4l0ve");
		
	// 	$mailer = Swift_Mailer::newInstance($transport);
	// 	$result = $mailer->send($message);
	// }
	
	// public function RunTransaction($test=false, $result="")
	// {
	// 	$retval = "APPROVED";
		
	// 	return $retval;
	// }
	
	// public function ConfirmPayment($urltag="shop")
	// {
	// 	$out = "";
	// 	$num = 1;
	// 	$taxrate = 0.0;
	// 	$savings = 0.0;
	// 	$landing = $urltag == "special" ? true : false;
	// 	$this->SubtotalAmount = 0.0;
	// 	$this->TaxAmount = 0.0;
	// 	$this->TotalAmount = 0.0;
		
	// 	if ($this->IsESDOnly()) $this->ShippingAmount = 0.0;
		
	// 	$out .= $this->HideSidebar();
	// 	$out .= "<div class=\"content\">\n";
	// 	$out .= $this->Breadcrumbs("pay");
	// 	$out .= "<form action=\"/".$urltag."/cart/continue\" method=\"post\" id=\"form_place_order\">\n";
	// 	$out .= "	<div class=\"order-summary\" style=\"float:left;width:250px;margin-right:20px;padding:10px;\">\n";
	// 	$out .= "		<div style=\"border-bottom:1px solid #999999;font-weight:bold;margin:10px 0px 10px 0px;\">Billing Address</div>\n";
	// 	$out .= "		<div style=\"padding-left:10px;\">".$this->BillingAddress->DisplayFormatted()."</div>\n";
	// 	$out .= "		<div style=\"border-bottom:1px solid #999999;font-weight:bold;margin:10px 0px 10px 0px;\">Shipping Address</div>\n";
	// 	$out .= "		<div style=\"padding-left:10px;\">".$this->ShippingAddress->DisplayFormatted()."</div>\n";
	// 	$out .= "		<div style=\"border-bottom:1px solid #999999;font-weight:bold;margin:10px 0px 10px 0px;\">E-mail Address</div>\n";
	// 	$out .= "		<div style=\"padding-left:10px;\">\n";
	// 	$out .= "			<div>".$_SESSION["__account__"]->Email."</div>\n";
	// 	$out .= "		</div>\n";
	// 	if (!isBlank($this->PromoCode))
	// 	{
	// 		$out .= "		<div style=\"border-bottom:1px solid #999999;font-weight:bold;margin:10px 0px 10px 0px;\">Promotional Code</div>\n";
	// 		$out .= "		<div style=\"padding-left:10px;\">\n";
	// 		$out .= "			<div>".$this->PromoCode."</div>\n";
	// 		$out .= "		</div>\n";
	// 	}
	// 	$out .= "		<div style=\"border-bottom:1px solid #999999;font-weight:bold;margin:10px 0px 10px 0px;\">Special Instructions</div>\n";
	// 	$out .= "		<div>\n";
	// 	$out .= "			<div style=\"font-size:10px;\">250 characters or less</div>\n";
	// 	$out .= "			<div><textarea name=\"pay_instructions\" id=\"pay_instructions\" maxlength=\"250\" style=\"width:100%;height:100px;\" >".$this->SpecialNotes."</textarea></div>\n";
	// 	$out .= "		</div>\n";
	// 	$out .= "	</div>\n";
		
		
	// 	$out .= "	<div class=\"order-summary\" style=\"float:right;width:680px;\">\n";
	// 	$out .= "		<div class=\"order-summary-heading\">ORDER SUMMARY</div>\n";
	// 	$out .= "		<div class=\"order-summary-titles\">";
	// 	$out .= "			<p class=\"product\" style=\"width:300px;\">Product</p>\n";
	// 	$out .= "			<p class=\"format\">Format</p>\n";
	// 	$out .= "			<p class=\"qty\">Qty</p>\n";
	// 	$out .= "			<p class=\"price\">Price</p>\n";
	// 	$out .= "			<p class=\"itemtotal\">Item Total</p>\n";
	// 	$out .= "			<div class=\"clearfloat\"></div>\n";
	// 	$out .= "		</div>\n";
		
	// 	if ($landing)
	// 	{
	// 		foreach ($this->LandingItems as $item)
	// 		{
	// 			$p = new Product();
	// 			$p->GetProduct($item->PID);
	// 			$p->CalculateValues();
	// 			$out .= "		<div class=\"order-summary-items".($num == 1 ? "-first" : "")."\">\n";
	// 			$out .= "			<p class=\"product\" style=\"width:300px;\">\n";
	// 			$out .= "				<img src=\"".$p->ImageURL()."\" alt=\"".$p->ProductName."\" border=\"0\" />\n";
	// 			$out .= "				<strong>".$p->ProductName."</strong><br>by ".$p->Manufacturer."<br>Ships in 2 to 3 business days\n";
	// 			$out .= "			</p>\n";
	// 			$out .= "			<p class=\"format\">".$p->Format."</p>\n";
	// 			$out .= "			<p class=\"qty\">".$item->Quantity."</p>\n";
	// 			if ($p->CalculatedDiscount > 0)
	// 			{
	// 				$out .= "			<p class=\"price\">";
	// 				$out .= "				<strong>$".money_format("%.2n", round($p->CalculatedPrice, 2))."</strong><br>\n";
	// 				$out .= "				<span class=\"savings\">Save ".round($p->CalculatedDiscount)."%</span>\n";
	// 				$out .= "			</p>\n";
	// 				$out .= "			<p class=\"itemtotal\"><strong>$".money_format("%.2n", round($p->CalculatedPrice * $item->Quantity, 2))."</strong></p>\n";
	// 				$savings += round($p->Price - $p->CalculatedPrice, 2) * $item->Quantity;
	// 				$this->SubtotalAmount += round($p->CalculatedPrice * $item->Quantity, 2); 
	// 			}
	// 			else
	// 			{
	// 				$out .= "			<p class=\"price\"><strong>$".money_format("%.2n", round($p->Price, 2))."</strong></p>\n";
	// 				$out .= "			<p class=\"itemtotal\"><strong>$".money_format("%.2n", round($p->Price * $item->Quantity, 2))."</strong></p>\n";
	// 				$this->SubtotalAmount += round($p->Price * $item->Quantity, 2); 
	// 			}
	// 			$out .= "			</p>\n";
	// 			$out .= "			<div class=\"clearfloat\"></div>\n";
	// 			$out .= "		</div>\n";
	// 			$num++;
	// 		}
	// 		foreach ($this->LandingFreeItems as $itemx)
	// 		{
	// 			$p = new Product();
	// 			$p->GetProduct($itemx->PID);
	// 			$out .= "		<div class=\"order-summary-items".($num == 1 ? "-first" : "")."\" style=\"background-color:#fff9ee;\">\n";
	// 			$out .= "			<p class=\"product\" style=\"width:300px;\">\n";
	// 			$out .= "				<img src=\"".$p->ImageURL()."\" alt=\"".$p->ProductName."\" border=\"0\" />\n";
	// 			$out .= "				<strong>".$p->ProductName."</strong><br>by ".$p->Manufacturer."<br>Ships in 2 to 3 business days\n";
	// 			$out .= "			</p>\n";
	// 			$out .= "			<p class=\"format\">".$p->Format."</p>\n";
	// 			$out .= "			<p class=\"qty\">".$itemx->Quantity."</p>\n";
	// 			$out .= "			<p class=\"price\"><strong>FREE</strong></p>\n";
	// 			$out .= "			<p class=\"itemtotal\"><strong>$0.00</strong></p>\n";
	// 			$out .= "			</p>\n";
	// 			$out .= "			<div class=\"clearfloat\"></div>\n";
	// 			$out .= "		</div>\n";
	// 			$num++;
	// 		}
	// 		$out .= "	</div>\n";
	// 	}
	// 	else
	// 	{
	// 		foreach ($this->Items as &$item)
	// 		{
	// 			$p = new Product();
	// 			$p->GetProduct($item->PID);
	// 			$p->CalculateValues();
	// 			$out .= "		<div class=\"order-summary-items".($num == 1 ? "-first" : "")."\">\n";
	// 			$out .= "			<p class=\"product\" style=\"width:300px;\">\n";
	// 			$out .= "				<img src=\"".$p->ImageURL()."\" alt=\"".$p->ProductName."\" border=\"0\" />\n";
	// 			$out .= "				<strong>".$p->ProductName."</strong><br>by ".$p->Manufacturer."<br>Ships in 2 to 3 business days\n";
	// 			$out .= "			</p>\n";
	// 			$out .= "			<p class=\"format\">".$p->Format."</p>\n";
	// 			$out .= "			<p class=\"qty\">".$item->Quantity."</p>\n";
	// 			if ($p->CalculatedDiscount > 0)
	// 			{
	// 				$out .= "			<p class=\"price\">";
	// 				$out .= "				<strong>$".money_format("%.2n", round($p->CalculatedPrice, 2))."</strong><br>\n";
	// 				$out .= "				<span class=\"savings\">Save ".round($p->CalculatedDiscount)."%</span>\n";
	// 				$out .= "			</p>\n";
	// 				$out .= "			<p class=\"itemtotal\"><strong>$".money_format("%.2n", round($p->CalculatedPrice * $item->Quantity, 2))."</strong></p>\n";
	// 				$savings += round($p->Price - $p->CalculatedPrice, 2) * $item->Quantity;
	// 				$this->SubtotalAmount += round($p->CalculatedPrice * $item->Quantity, 2); 
	// 			}
	// 			else
	// 			{
	// 				$out .= "			<p class=\"price\"><strong>$".money_format("%.2n", round($p->Price, 2))."</strong></p>\n";
	// 				$out .= "			<p class=\"itemtotal\"><strong>$".money_format("%.2n", round($p->Price * $item->Quantity, 2))."</strong></p>\n";
	// 				$this->SubtotalAmount += round($p->Price * $item->Quantity, 2); 
	// 			}
	// 			$out .= "			</p>\n";
	// 			$out .= "			<div class=\"clearfloat\"></div>\n";
	// 			$out .= "		</div>\n";
	// 			$num++;
	// 		}
	// 		$out .= "	</div>\n";
	// 	}
		
	// 	$taxrate = $this->GetTaxRate();
	// 	$this->TaxAmount = ($this->SubtotalAmount + $this->ShippingAmount) * $taxrate;
	// 	$this->TotalAmount = $this->SubtotalAmount + $this->ShippingAmount + $this->TaxAmount;
		
		
	// 	$out .= "	<div class=\"order-summary\" style=\"float:right;width:680px;position:relative;\">\n";
	// 	$out .= "		<div class=\"order-summary-heading\">ORDER TOTAL</div>\n";
	// 	$out .= "		<div class=\"order-summary-total\">&nbsp;</div>\n";
	// 	if (!$landing)
	// 		$out .= "		<p class=\"summary\" style=\"width:300px;padding-left:20px;font-weight:bold; font-size:16px;text-align:left;position:absolute; top:56px;\"><a href=\"/shop/cart/view\">Change Order...</a></p>\n";
	// 	$out .= "		<div class=\"order-summary-total\">\n";
	// 	$out .= "			<p class=\"summary\" style=\"width:564px;\">Order Subtotal: </p>\n";
	// 	$out .= "			<p class=\"totals\">$".money_format("%.2n", round($this->SubtotalAmount, 2))."</p>\n";
	// 	$out .= "			<div class=\"clearfloat\"></div>\n";
	// 	$out .= "		</div>\n";
	// 	if (!$this->IsESDOnly())
	// 	{
	// 		$out .= "		<div class=\"order-summary-total\">\n";
	// 		$out .= "			<p class=\"summary\" style=\"width:564px;\">Shipping: </p>\n";
	// 		$out .= "			<p class=\"totals\">$".money_format("%.2n", round($this->ShippingAmount, 2))."</p>\n";
	// 		$out .= "			<div class=\"clearfloat\"></div>\n";
	// 		$out .= "		</div>\n";
	// 	}
	// 	$out .= "		<div class=\"order-summary-total\">\n";
	// 	$out .= "			<p class=\"summary\" style=\"width:564px;\">Estimated Tax: </p>\n";
	// 	$out .= "			<p class=\"totals\">".($this->TaxAmount <= 0 ? "---" : "$".money_format("%.2n", round($this->TaxAmount, 2)))."</p>\n";
	// 	$out .= "			<div class=\"clearfloat\"></div>\n";
	// 	$out .= "		</div>\n";
	// 	$out .= "		<div class=\"order-summary-total\">&nbsp;</div>\n";
	// 	$out .= "		<div class=\"order-summary-total\">\n";
	// 	$out .= "			<p class=\"summary\" style=\"width:564px;\"><strong>Estimated Total: </strong></p>\n";
	// 	$out .= "			<p class=\"totals\"><strong>$".money_format("%.2n", round($this->TotalAmount, 2))."</strong></p>\n";
	// 	$out .= "			<div class=\"clearfloat\"></div>\n";
	// 	$out .= "		</div>\n";
	// 	$out .= "		<div class=\"order-summary-total\">&nbsp;</div>\n";
	// 	$out .= "	</div>\n";
		
	// 	$out .= "	<div class=\"order-summary\" style=\"float:right;width:680px;position:relative;\">\n";
	// 	$out .= "		<div class=\"order-summary-heading\">PAYMENT</div>\n";
	// 	$out .= "		<div class=\"order-summary-total\">&nbsp;</div>\n";
	// 	if ($this->TotalAmount <= 0)
	// 	{
	// 		$out .= "		<div style=\"float:left;margin-left:10px; width:290px;\">\n";
	// 		$out .= "			<ul>\n";
	// 		$out .= "				<li style=\"list-style:none;padding-bottom:10px;\">\n";
	// 		$out .= "					<div style=\"float:left;width:24px;vertical-align:middle;\"><input type=\"radio\" checked=\"checked\" id=\"pay_type_none\" name=\"pay_type\" value=\"NONE\" style=\"vertical-align:middle;\" /></div>\n";
	// 		$out .= "					<div style=\"float:left;vertical-align:middle;\"><strong>No payment due</strong></div>\n";
	// 		$out .= "					<div class=\"clearfloat\"></div>\n";
	// 		$out .= "				</li>\n";
	// 		$out .= "			</ul>\n";
	// 		$out .= "		</div>\n";
	// 	}
	// 	else
	// 	{
	// 		$out .= "		<div style=\"float:left;margin-left:10px; width:290px;\">\n";
	// 		$out .= "			<ul>\n";
	// 		$out .= "				<li style=\"list-style:none;padding-bottom:10px;\">\n";
	// 		$out .= "					<div style=\"float:left;width:24px;vertical-align:middle;\"><input type=\"radio\"".($this->PayType == "NEW" ? " checked=\"checked\"" : "")." id=\"pay_type_new\" name=\"pay_type\" value=\"NEW\" style=\"vertical-align:middle;\" /></div>\n";
	// 		$out .= "					<div style=\"float:left;vertical-align:middle;\"><strong>Enter Credit Card Information</strong></div>\n";
	// 		$out .= "					<div class=\"clearfloat\"></div>\n";
	// 		$out .= "				</li>\n";

	// 		// ***INLINESQL***
	// 		// $sql = "select * from cc_moulah where custid = ".smartQuote($_SESSION["__account__"]->CustomerID)." and isnull(custid, '') <> '' and active = 1 and case when convert(smallint, expyr) > YEAR(getdate()) then 1 when convert(smallint, expyr) = YEAR(getdate()) and convert(tinyint, expmo) >= month(getdate()) then 1 else 0 end = 1 order by id";
	// 		// $rs = $this->_db->get_results($sql);
	// 		// if (count($rs) > 0)
	// 		// {
	// 		// 	foreach ($rs as &$row)
	// 		// 	{
	// 		// 		$cc = new CreditCard();
	// 		// 		$cc->CardID = $row->id;
	// 		// 		$cc->RowID = strtoupper(str_replace(array("{", "}", "-"), "", mssql_guid_string($row->rguid)));
	// 		// 		$cc->CardType = $row->cardtype;
	// 		// 		$cc->CardName = $row->cardname;
	// 		// 		$cc->CardNumber = $cc->DecodeNumber($row->cardnum);
	// 		// 		$cc->ExpirationMonth = intval($row->expmo);
	// 		// 		$cc->ExpirationYear = intval($row->expyr);
	// 		// 		$cc->CVV = $row->cvv;
					
	// 		// 		$out .= "				<li style=\"list-style:none;padding-bottom:10px;\">\n";
	// 		// 		$out .= "					<div style=\"float:left;width:24px;vertical-align:middle;\"><input type=\"radio\"".($this->PayType == $cc->RowID ? " checked=\"checked\"" : "")." id=\"pay_type_".$cc->RowID."\" name=\"pay_type\" value=\"".$cc->RowID."\" style=\"vertical-align:middle;\" /></div>\n";
	// 		// 		$out .= "					<div style=\"float:left;vertical-align:middle;\">".$cc->DisplayFormatted()."</div>\n";
	// 		// 		$out .= "					<div class=\"clearfloat\"></div>\n";
	// 		// 		$out .= "				</li>\n";
	// 		// 	}
	// 		// }
	// 		if (!isBlank($_SESSION["__account__"]->Terms))
	// 		{
	// 			$itm = "";
	// 			$lbl = "";
				
	// 			switch($_SESSION["__account__"]->Terms)
	// 			{
	// 				case "NET30":
	// 					$lbl = "30-Day Billing";
	// 					break;
	// 				case "NET45":
	// 					$lbl = "45-Day Billing";
	// 					break;
	// 				case "NET60":
	// 					$lbl = "90-Day Billing";
	// 					break;
	// 				case "NET90":
	// 					$lbl = "90-Day Billing";
	// 					break;
	// 			}
	// 			$out .= "				<li style=\"list-style:none;padding-bottom:10px;\">\n";
	// 			$out .= "					<div style=\"float:left;width:24px;vertical-align:middle;\"><input type=\"radio\"".($this->PayType == $_SESSION["__account__"]->Terms ? " checked=\"checked\"" : "")." id=\"pay_type_".strtolower($_SESSION["__account__"]->Terms)."\" name=\"pay_type\" value=\"".$_SESSION["__account__"]->Terms."\" style=\"vertical-align:middle;\" /></div>\n";
	// 			$out .= "					<div style=\"float:left;vertical-align:middle;\">".$lbl." (".$_SESSION["__account__"]->Terms.")</div>\n";
	// 			$out .= "					<div class=\"clearfloat\"></div>\n";
	// 			$out .= "				</li>\n";
	// 		}
	// 		$out .= "				<li style=\"list-style:none;padding-bottom:10px;\">\n";
	// 		$out .= "					<div style=\"float:left;width:24px;vertical-align:middle;\"><input type=\"radio\"".($this->PayType == "CHECK" ? " checked=\"checked\"" : "")." id=\"pay_type_check\" name=\"pay_type\" value=\"CHECK\" style=\"vertical-align:middle;\" /></div>\n";
	// 		$out .= "					<div style=\"float:left;vertical-align:middle;\"><strong>Mail a Check or Money Order</strong></div>\n";
	// 		$out .= "					<div class=\"clearfloat\"></div>\n";
	// 		$out .= "				</li>\n";
	// 		$out .= "			</ul>\n";
	// 		$out .= "		</div>\n";
	// 		$out .= "		<div style=\"float:right;margin-right:10px;width:360px;\">\n";
	// 		$out .= "			<div class=\"address-line\">\n";
	// 		$out .= "				<div class=\"address-label\" style=\"width:144px;\">Name on Credit Card: </div>\n";
	// 		$out .= "				<div class=\"address-input\" style=\"width:200px;\"><input type=\"text\" maxlength=\"35\" name=\"pay_new_cardname\" id=\"pay_new_cardname\" value=\"".$this->CardName."\" style=\"width:195px;\" /></div>\n";
	// 		$out .= "				<div class=\"clearfloat\"></div>\n";
	// 		$out .= "			</div>\n";
	// 		$out .= "			<div class=\"address-line\">\n";
	// 		$out .= "				<div class=\"address-label\" style=\"width:144px;\">Card Types: </div>\n";
	// 		$out .= "				<div class=\"address-input\" style=\"width:200px;\">";
	// 		$out .= "					<img src=\"/framework/img/VS_cards".($this->CardType != "" && $this->CardType != "VS" ? "_gray" : "").".png\" id=\"pay_cardtype_VS\" style=\"width:45px;float:left;margin-right:5px;\" />\n";
	// 		$out .= "					<img src=\"/framework/img/MC_cards".($this->CardType != "" && $this->CardType != "MC" ? "_gray" : "").".png\" id=\"pay_cardtype_MC\" style=\"width:45px;float:left;margin-right:5px;\" />\n";
	// 		$out .= "					<img src=\"/framework/img/AX_cards".($this->CardType != "" && $this->CardType != "AX" ? "_gray" : "").".png\" id=\"pay_cardtype_AX\" style=\"width:45px;float:left;margin-right:5px;\" />\n";
	// 		$out .= "					<img src=\"/framework/img/DI_cards".($this->CardType != "" && $this->CardType != "DI" ? "_gray" : "").".png\" id=\"pay_cardtype_DI\" style=\"width:45px;float:left;margin-right:5px;\" />\n";
	// 		$out .= "				</div>\n";
	// 		$out .= "				<div class=\"clearfloat\"></div>\n";
	// 		$out .= "			</div>\n";
	// 		$out .= "			<div class=\"address-line\">\n";
	// 		$out .= "				<div class=\"address-label\" style=\"width:144px;\">Card Number: </div>\n";
	// 		$out .= "				<div class=\"address-input\" style=\"width:200px;\"><input type=\"text\" maxlength=\"16\" name=\"pay_new_cardnum\" id=\"pay_new_cardnum\" value=\"".$this->CardNumber."\" onkeypress=\"return ccNumbersOnly(this, event);\" style=\"width:195px;\" /></div>\n";
	// 		$out .= "				<div class=\"clearfloat\"></div>\n";
	// 		$out .= "			</div>\n";
	// 		$out .= "			<div class=\"address-line\">\n";
	// 		$out .= "				<div class=\"address-label\" style=\"width:144px;\">CVV Number: </div>\n";
	// 		$out .= "				<div class=\"address-input\" style=\"width:200px;\"><input type=\"text\" maxlength=\"4\" name=\"pay_new_cvvnum\" id=\"pay_new_cvvnum\" value=\"".$this->CardCVV."\" onkeypress=\"return ccNumbersOnly(this, event);\" style=\"width:60px;\" /></div>\n";
	// 		$out .= "				<div class=\"clearfloat\"></div>\n";
	// 		$out .= "			</div>\n";
	// 		$out .= "			<div class=\"address-line\">\n";
	// 		$out .= "				<div class=\"address-label\" style=\"width:144px;\">Expiration: </div>\n";
	// 		$out .= "				<div class=\"address-input\" style=\"width:200px;\">";
	// 		$out .= "					<select name=\"pay_new_expmonth\" id=\"pay_new_expmonth\" style=\"width:110px;\" />\n";
	// 		$out .= "						<option value=\"1\"".($this->CardExpMonth === 1 ? " selected" : "").">January</option>\n";
	// 		$out .= "						<option value=\"2\"".($this->CardExpMonth === 2 ? " selected" : "").">February</option>\n";
	// 		$out .= "						<option value=\"3\"".($this->CardExpMonth === 3 ? " selected" : "").">March</option>\n";
	// 		$out .= "						<option value=\"4\"".($this->CardExpMonth === 4 ? " selected" : "").">April</option>\n";
	// 		$out .= "						<option value=\"5\"".($this->CardExpMonth === 5 ? " selected" : "").">May</option>\n";
	// 		$out .= "						<option value=\"6\"".($this->CardExpMonth === 6 ? " selected" : "").">June</option>\n";
	// 		$out .= "						<option value=\"7\"".($this->CardExpMonth === 7 ? " selected" : "").">July</option>\n";
	// 		$out .= "						<option value=\"8\"".($this->CardExpMonth === 8 ? " selected" : "").">August</option>\n";
	// 		$out .= "						<option value=\"9\"".($this->CardExpMonth === 9 ? " selected" : "").">September</option>\n";
	// 		$out .= "						<option value=\"10\"".($this->CardExpMonth === 10 ? " selected" : "").">October</option>\n";
	// 		$out .= "						<option value=\"11\"".($this->CardExpMonth === 11 ? " selected" : "").">November</option>\n";
	// 		$out .= "						<option value=\"12\"".($this->CardExpMonth === 12 ? " selected" : "").">December</option>\n";
	// 		$out .= "					</select>\n";
	// 		$out .= "					<select name=\"pay_new_expyear\" id=\"pay_new_expyear\" style=\"width:75px;\" />\n";
	// 		for ($i = 0; $i < 10; $i++)
	// 		{
	// 			$out .= "						<option value=\"".(intval(date("Y")) + $i)."\"".($this->CardExpYear == (intval(date("Y")) + $i) ? " selected" : "").">".(intval(date("Y")) + $i)."</option>\n";
	// 		}
	// 		$out .= "					</select>\n";
	// 		$out .= "				</div>\n";
	// 		$out .= "				<input type=\"hidden\" name=\"pay_new_cardtype\" id=\"pay_new_cardtype\" value=\"".$this->CardType."\" />\n";
	// 		$out .= "				<div class=\"clearfloat\"></div>\n";
	// 		$out .= "			</div>\n";
	// 		$out .= "			<div class=\"address-line\">\n";
	// 		$out .= "				<div class=\"address-label\" style=\"width:144px;\">&nbsp;</div>\n";
	// 		$out .= "				<div class=\"address-input\" style=\"width:200px;\"><input type=\"checkbox\"".($this->SaveCard ? " checked=\"checked\"" : "")." id=\"pay_new_save\" name=\"pay_new_save\" value=\"1\" /><label for=\"pay_new_save\"> Save for future purchases</label></div>";
	// 		$out .= "				<div class=\"clearfloat\"></div>\n";
	// 		$out .= "			</div>\n";
	// 		$out .= "			<div class=\"order-summary-total\">&nbsp;</div>\n";
	// 		$out .= "		</div>\n";
	// 	}
	// 	$out .= "	</div>\n";
		
		
	// 	$out .= "	<div class=\"order-signin-error\" style=\"float:right;width:680px;margin-bottom:10px;\" id=\"signin-error\">";
	// 	if (!isBlank($this->LastError))
	// 	{
	// 		$out .= "		".$this->LastError."\n";
	// 		$this->LastError = "";
	// 	}
	// 	$out .= "	</div>";
	// 	$out .= "	<div class=\"clearfloat\"></div>\n";
	// 	$out .= "	<div class=\"order-continue\">\n";
	// 	$out .= "		<div style=\"font-size:12px;font-weight:normal;margin-bottom:10px;\"><input type=\"checkbox\" id=\"cart_agree\" name=\"cart_agree\" value=\"1\" /> <a href=\"/about/terms-and-conditions\" target=\"_blank\">I agree to terms and conditions</a></div>\n";
	// 	$out .= "		<div><input class=\"green button\" name=\"cart_btn\" type=\"submit\" onclick=\"scCheckPlaceOrder();return false;\" value=\"PLACE ORDER\" /></div>\n";
	// 	$out .= "	</div>\n";
	// 	$out .= "	<input type=\"hidden\" name=\"command\" value=\"placeorder\" />\n";
	// 	$out .= "</form>\n";
	// 	$out .= "</div>\n";
		
	// 	return $out;
	// }
	
	// public function GetCartWeight($landing=false)
	// {
	// 	$retval = 0.0;
		
	// 	if ($landing)
	// 	{
	// 		foreach ($this->LandingItems as $p)
	// 		{
	// 			$retval += ($p->Weight * $p->Quantity);
	// 		}
			
	// 		if (count($this->LandingItems) > 1)
	// 			$retval += 3;
	// 	}
	// 	else
	// 	{
	// 		foreach ($this->Items as $p)
	// 		{
	// 			$retval += ($p->Weight * $p->Quantity);
	// 		}
			
	// 		if (count($this->Items) > 1)
	// 			$retval += 3;
	// 	}
		
	// 	return $retval;
	// }
	
	// public function IsFulfillmentOnly()
	// {
	// 	$retval = true;
		
	// 	foreach ($this->Items as &$p)
	// 	{
	// 		if ($p->Fulfillment === false)
	// 		{
	// 			$retval = false;
	// 		}
	// 	}
		
	// 	foreach ($this->LandingItems as &$p)
	// 	{
	// 		if ($p->Fulfillment === false)
	// 		{
	// 			$retval = false;
	// 		}
	// 	}
		
	// 	return $retval;
	// }
	
	// public function IsESDOnly()
	// {
	// 	$retval = true;
		
	// 	foreach ($this->Items as &$p)
	// 	{
	// 		if ($p->Electronic === false)
	// 		{
	// 			$retval = false;
	// 		}
	// 	}
		
	// 	foreach ($this->LandingItems as &$p)
	// 	{
	// 		if ($p->Electronic === false)
	// 		{
	// 			$retval = false;
	// 		}
	// 	}
		
	// 	return $retval;
	// }
	
	// public function HasESD()
	// {
	// 	$retval = false;
		
	// 	foreach ($this->Items as &$p)
	// 	{
	// 		if ($p->Electronic === true)
	// 		{
	// 			$retval = true;
	// 		}
	// 	}
		
	// 	foreach ($this->LandingItems as &$p)
	// 	{
	// 		if ($p->Electronic === true)
	// 		{
	// 			$retval = true;
	// 		}
	// 	}
		
	// 	return $retval;
	// }
	
	// public function GetTaxRate()
	// {
	// 	return 0.0;
	// }
	
	// public function ShippingMethod($urltag="shop")
	// {
	// 	$landing = ($urltag == "special" ? true : false);
	// 	$out = "";
	// 	$multiplier = 1.2;
	// 	$totalweight = $this->GetCartWeight($landing);
	// 	$boxes = floor($totalweight / 25);
	// 	$lastbox = $boxes > 0 ? floor(fmod($totalweight, 25)) : ceil(fmod($totalweight, 25));
	// 	$lastbox++;
	// 	$strsel = "";
	// 	$num = 0;
	// 	$fullcountry = "";
		
	// 	unset($this->ShipMethodList);
		
	// 	$out .= $this->HideSidebar();
	// 	$out .= "<div class=\"content\">\n";
	// 	$out .= $this->Breadcrumbs("ship");
	// 	$out .= "<form action=\"/".$urltag."/cart/continue\" method=\"post\">\n";
	// 	$out .= "	<div class=\"order-summary\">\n";
	// 	$out .= "		<div class=\"order-summary-heading\">SHIPPING METHOD</div>\n";
	// 	$out .= "		<div>&nbsp;</div>\n";
	// 	$out .= "		<div class=\"address-line\">\n";
	// 	$out .= "			<div style=\"float:left; width: 200px;border-bottom: 1px solid #999999; font-weight:bold;\">Shipping Method</div>\n";
	// 	$out .= "			<div style=\"float:left; width: 200px;border-bottom: 1px solid #999999; font-weight:bold;\">Estimated Arrival Time</div>\n";
	// 	$out .= "			<div class=\"clearfloat\"></div>\n";
	// 	$out .= "		</div>\n";
		
	// 	// USPS SERVICES
		
	// 	// ***INLINESQL***
	// 	// $sql = "select country from cc_countries where code = ".smartQuote($this->ShippingAddress->Country);
	// 	// $fullcountry = $this->_db->get_var($sql);
		
		
	// 	// if (count($usps->Rates) > 0)
	// 	// {
	// 	// 	$num++;
	// 	// 	$this->ShipMethodList[$num] = array("USPS1P", money_format("%.2n", round($usps->Rates[0]->Rate * $multiplier, 2)));
			
	// 	// 	$out .= "		<div class=\"address-line\">\n";
	// 	// 	$out .= "			<div style=\"float:left; width: 20px;border-bottom: 1px solid #999999; height:45px;\"><input type=\"radio\" name=\"cart_ship_method\" id=\"cart_method_".$num."\" value=\"".$num."\"".($this->ShippingService == "USPS1P" ? " checked=\"checked\"" : "")." /></div>\n";
	// 	// 	$out .= "			<div style=\"float:left; width: 230px;border-bottom: 1px solid #999999; height:45px;\"><label for=\"cart_method_".$num."\"><strong>Standard</strong><br />Shipping Cost: $".money_format("%.2n", round($usps->Rates[0]->Rate * $multiplier, 2))."</label></div>\n";
	// 	// 	$out .= "			<div style=\"float:left; width: 200px;border-bottom: 1px solid #999999; height:45px;\">5 to 7 business days</div>\n";
	// 	// 	$out .= "			<div class=\"clearfloat\"></div>\n";
	// 	// 	$out .= "		</div>\n";
	// 	// 	$out .= "		<div class=\"clearfloat\"></div>\n";
	// 	// }

		
	// 	// if ($this->ShippingAddress->CountrySmall == "US")
	// 	// {
	// 	// 	$usps->Service = ServiceUSPS::MediaMail;
	// 	// 	$usps->Send();
			
	// 	// 	if (count($usps->Rates) > 0)
	// 	// 	{
	// 	// 		$num++;
	// 	// 		$this->ShipMethodList[$num] = array("USPSMMDC", money_format("%.2n", round($usps->Rates[0]->Rate * $multiplier, 2)));
				
	// 	// 		$out .= "		<div class=\"address-line\">\n";
	// 	// 		$out .= "			<div style=\"float:left; width: 20px;border-bottom: 1px solid #999999; height:45px;\"><input type=\"radio\" name=\"cart_ship_method\" id=\"cart_method_".$num."\" value=\"".$num."\"".($this->ShippingService == "USPSMMDC" ? " checked=\"checked\"" : "")." /></div>\n";
	// 	// 		$out .= "			<div style=\"float:left; width: 230px;border-bottom: 1px solid #999999; height:45px;\"><label for=\"cart_method_".$num."\"><strong>Media Mail</strong><br />Shipping Cost: $".money_format("%.2n", round($usps->Rates[0]->Rate * $multiplier, 2))."</label></div>\n";
	// 	// 		$out .= "			<div style=\"float:left; width: 200px;border-bottom: 1px solid #999999; height:45px;\">7 to 10 business days</div>\n";
	// 	// 		$out .= "			<div class=\"clearfloat\"></div>\n";
	// 	// 		$out .= "		</div>\n";
	// 	// 		$out .= "		<div class=\"clearfloat\"></div>\n";
	// 	// 	}
	// 	// }
		
	// 	$out .= "	</div>\n";
	// 	$out .= "	<div class=\"order-continue\"><input class=\"green button\" name=\"cart_btn\" type=\"submit\" value=\"CONTINUE\" /></div>\n";
	// 	$out .= "	<input type=\"hidden\" name=\"command\" value=\"shipping\" />\n";
	// 	$out .= "</form>\n";
	// 	$out .= "</div>\n";
		
	// 	if (count($this->ShipMethodList) == 0)
	// 		$out = "";
		
	// 	return $out;
	// }
	
	// public function BillingInfo($urltag="shop")
	// {
	// 	$out = "";
	// 	$strsel = "";
	// 	$initdisplayb = "";
	// 	$initdisplays = "";
		
	// 	if ($this->BillingAddress == NULL)
	// 		$this->BillingAddress = clone $_SESSION["__account__"]->BillingAddress;
	// 	if ($this->ShippingAddress == NULL)
	// 		$this->ShippingAddress = clone $_SESSION["__account__"]->ShippingAddress;
		
	// 	if ($this->BillingAddress->Country == "USA" || $this->BillingAddress->Country == "AUS" || $this->BillingAddress->Country == "CAN" || $this->BillingAddress->Country == "")
	// 		$initdisplayb = "list";
	// 	else
	// 		$initdisplayb = "text";
		
	// 	if ($this->ShippingAddress->Country == "USA" || $this->ShippingAddress->Country == "AUS" || $this->ShippingAddress->Country == "CAN" || $this->ShippingAddress->Country == "")
	// 		$initdisplays = "list";
	// 	else
	// 		$initdisplays = "text";
		
	// 	$out .= $this->HideSidebar();
	// 	$out .= "<div class=\"content\">\n";
	// 	$out .= $this->Breadcrumbs("bill");
	// 	$out .= "<form action=\"/".$urltag."/cart/continue\" method=\"post\">\n";
	// 	$out .= "	<div class=\"order-address\">\n";
	// 	$out .= "		<div class=\"order-address-heading\">BILLING ADDRESS</div>\n";
	// 	$out .= "		<div class=\"address-line\">\n";
	// 	$out .= "			<div class=\"address-label\">&nbsp;</div>\n";
	// 	$out .= "			<div class=\"address-input\"><input type=\"checkbox\" name=\"cart_b_default\" id=\"cart_b_default\" value=\"1\" checked=\"checked\" /> <label for=\"cart_b_default\">Save these addresses</label></div>\n";
	// 	$out .= "			<div class=\"clearfloat\"></div>\n";
	// 	$out .= "		</div>\n";
	// 	$out .= "		<div class=\"address-line\">\n";
	// 	$out .= "			<div class=\"address-label\">Contact Name:</div>\n";
	// 	$out .= "			<div class=\"address-input\"><input type=\"text\" name=\"cart_b_contact\" id=\"cart_b_contact\" value=\"".$this->BillingAddress->Contact."\" maxlength=\"50\" /></div>\n";
	// 	$out .= "			<div class=\"clearfloat\"></div>\n";
	// 	$out .= "		</div>\n";
	// 	$out .= "		<div class=\"address-line\">\n";
	// 	$out .= "			<div class=\"address-label\">Address:</div>\n";
	// 	$out .= "			<div class=\"address-input\"><input type=\"text\" name=\"cart_b_address1\" id=\"cart_b_address1\" value=\"".$this->BillingAddress->Address1."\" maxlength=\"50\" /></div>\n";
	// 	$out .= "			<div class=\"clearfloat\"></div>\n";
	// 	$out .= "		</div>\n";
	// 	$out .= "		<div class=\"address-line\">\n";
	// 	$out .= "			<div class=\"address-label\">&nbsp;</div>\n";
	// 	$out .= "			<div class=\"address-input\"><input type=\"text\" name=\"cart_b_address2\" id=\"cart_b_address2\" value=\"".$this->BillingAddress->Address2."\" maxlength=\"50\" /></div>\n";
	// 	$out .= "			<div class=\"clearfloat\"></div>\n";
	// 	$out .= "		</div>\n";
	// 	$out .= "		<div class=\"address-line\">\n";
	// 	$out .= "			<div class=\"address-label\">Country:</div>\n";
	// 	$out .= "			<div class=\"address-input\">";
	// 	$out .= "				<select name=\"cart_b_country\" id=\"cart_b_country\" onchange=\"scGetStates(this.options[this.selectedIndex].value, 'B', '".$this->BillingAddress->State."');\" />\n";
		
	// 	// ***INLINESQL***
	// 	// $sql = "select numcode, country from cc_countries where active = 1 and numcode > 0 order by country";
	// 	// $rs = $this->_db->get_results($sql);
	// 	// foreach ($rs as $row)
	// 	// {
	// 	// 	$strsel = "";
	// 	// 	if ($row->numcode == $this->BillingAddress->CountryCode)
	// 	// 		$strsel = " selected";
	// 	// 	$out .= "					<option value=\"".$row->numcode."\"".$strsel.">".$row->country."</option>\n";
	// 	// }
		
	// 	$out .= "				</select>\n";
	// 	$out .= "			</div>\n";
	// 	$out .= "			<div class=\"clearfloat\"></div>\n";
	// 	$out .= "		</div>\n";
	// 	$out .= "		<div class=\"address-line\">\n";
	// 	$out .= "			<div class=\"address-label\">City:</div>\n";
	// 	$out .= "			<div class=\"address-input\"><input type=\"text\" name=\"cart_b_city\" id=\"cart_b_city\" value=\"".$this->BillingAddress->City."\" maxlength=\"50\" /></div>\n";
	// 	$out .= "			<div class=\"clearfloat\"></div>\n";
	// 	$out .= "		</div>\n";
	// 	$out .= "		<div class=\"address-line\" id=\"label_b_state\"".($initdisplayb == "list" ? "" : " style=\"display:none;\"").">\n";
	// 	$out .= "			<div class=\"address-label\">State:</div>\n";
	// 	$out .= "			<div class=\"address-input\">";
	// 	$out .= "				<select name=\"cart_b_state\" id=\"cart_b_state\" />\n";

	// 	// ***INLINESQL***
	// 	// $sql = "select code, state from pw_states where id > 0 and location = ".smartQuote($this->BillingAddress->Country)." order by state";
	// 	// $rs = $this->_db->get_results($sql);
	// 	// if (isset($rs))
	// 	// {
	// 	// 	foreach ($rs as $row)
	// 	// 	{
	// 	// 		$strsel = "";
	// 	// 		if ($row->code == $this->BillingAddress->State)
	// 	// 			$strsel = " selected";
	// 	// 		$out .= "					<option value=\"".$row->code."\"".$strsel.">".$row->state."</option>\n";
	// 	// 	}
	// 	// }

	// 	$out .= "				</select>\n";
	// 	$out .= "			</div>\n";
	// 	$out .= "			<div class=\"clearfloat\"></div>\n";
	// 	$out .= "		</div>\n";
	// 	$out .= "		<div class=\"address-line\" id=\"label_b_istate\"".($initdisplayb == "text" ? "" : " style=\"display:none;\"").">\n";
	// 	$out .= "			<div class=\"address-label\">State:</div>\n";
	// 	$out .= "			<div class=\"address-input\"><input type=\"text\" name=\"cart_b_istate\" id=\"cart_b_istate\" value=\"".$this->BillingAddress->State."\" maxlength=\"50\" /></div>\n";
	// 	$out .= "			<div class=\"clearfloat\"></div>\n";
	// 	$out .= "		</div>\n";
	// 	$out .= "		<div class=\"address-line\">\n";
	// 	$out .= "			<div class=\"address-label\">Zip/Postal Code:</div>\n";
	// 	$out .= "			<div class=\"address-input\"><input type=\"text\" name=\"cart_b_zip\" id=\"cart_b_zip\" value=\"".$this->BillingAddress->Zip."\" maxlength=\"50\" /></div>\n";
	// 	$out .= "			<div class=\"clearfloat\"></div>\n";
	// 	$out .= "		</div>\n";
	// 	$out .= "		<div class=\"address-line\">\n";
	// 	$out .= "			<div class=\"address-label\">Phone:</div>\n";
	// 	$out .= "			<div class=\"address-input\"><input type=\"text\" name=\"cart_b_phone\" id=\"cart_b_phone\" value=\"".$this->BillingAddress->Phone."\" maxlength=\"25\" /></div>\n";
	// 	$out .= "			<div class=\"clearfloat\"></div>\n";
	// 	$out .= "		</div>\n";
	// 	$out .= "		\n";
	// 	$out .= "		\n";
	// 	$out .= "		\n";
	// 	$out .= "	</div>\n";
	// 	$out .= "	<div class=\"order-address\">\n";
	// 	$out .= "		<div class=\"order-address-heading\">SHIPPING ADDRESS</div>\n";
	// 	$out .= "		<div class=\"address-line\">\n";
	// 	$out .= "			<div class=\"address-label\">&nbsp;</div>\n";
	// 	$out .= "			<div class=\"address-input\"><input type=\"checkbox\" name=\"cart_s_same\" id=\"cart_s_same\" value=\"1\" /> <label for=\"cart_s_same\">Same as Billing Address</label></div>\n";
	// 	$out .= "			<div class=\"clearfloat\"></div>\n";
	// 	$out .= "		</div>\n";
	// 	$out .= "		<div class=\"address-line\">\n";
	// 	$out .= "			<div class=\"address-label\">Contact Name:</div>\n";
	// 	$out .= "			<div class=\"address-input\"><input type=\"text\" name=\"cart_s_contact\" id=\"cart_s_contact\" value=\"".$this->ShippingAddress->Contact."\" maxlength=\"50\" /></div>\n";
	// 	$out .= "			<div class=\"clearfloat\"></div>\n";
	// 	$out .= "		</div>\n";
	// 	$out .= "		<div class=\"address-line\">\n";
	// 	$out .= "			<div class=\"address-label\">Address:</div>\n";
	// 	$out .= "			<div class=\"address-input\"><input type=\"text\" name=\"cart_s_address1\" id=\"cart_s_address1\" value=\"".$this->ShippingAddress->Address1."\" maxlength=\"50\" /></div>\n";
	// 	$out .= "			<div class=\"clearfloat\"></div>\n";
	// 	$out .= "		</div>\n";
	// 	$out .= "		<div class=\"address-line\">\n";
	// 	$out .= "			<div class=\"address-label\">&nbsp;</div>\n";
	// 	$out .= "			<div class=\"address-input\"><input type=\"text\" name=\"cart_s_address2\" id=\"cart_s_address2\" value=\"".$this->ShippingAddress->Address2."\" maxlength=\"50\" /></div>\n";
	// 	$out .= "			<div class=\"clearfloat\"></div>\n";
	// 	$out .= "		</div>\n";
	// 	$out .= "		<div class=\"address-line\">\n";
	// 	$out .= "			<div class=\"address-label\">Country:</div>\n";
	// 	$out .= "			<div class=\"address-input\">";
	// 	$out .= "				<select name=\"cart_s_country\" id=\"cart_s_country\" onchange=\"scGetStates(this.options[this.selectedIndex].value, 'S', '".$this->ShippingAddress->State."');\" />\n";
		
	// 	// ***INLINESQL***
	// 	// $sql = "select numcode, country from cc_countries where active = 1 and numcode > 0 order by country";
	// 	// $rs = $this->_db->get_results($sql);
	// 	// foreach ($rs as $row)
	// 	// {
	// 	// 	$strsel = "";
	// 	// 	if ($row->numcode == $this->ShippingAddress->CountryCode)
	// 	// 		$strsel = " selected";
	// 	// 	$out .= "					<option value=\"".$row->numcode."\"".$strsel.">".$row->country."</option>\n";
	// 	// }
		
	// 	$out .= "				</select>\n";
	// 	$out .= "			</div>\n";
	// 	$out .= "			<div class=\"clearfloat\"></div>\n";
	// 	$out .= "		</div>\n";
	// 	$out .= "		<div class=\"address-line\">\n";
	// 	$out .= "			<div class=\"address-label\">City:</div>\n";
	// 	$out .= "			<div class=\"address-input\"><input type=\"text\" name=\"cart_s_city\" id=\"cart_s_city\" value=\"".$this->ShippingAddress->City."\" maxlength=\"50\" /></div>\n";
	// 	$out .= "			<div class=\"clearfloat\"></div>\n";
	// 	$out .= "		</div>\n";
	// 	$out .= "		<div class=\"address-line\" id=\"label_s_state\"".($initdisplays == "list" ? "" : " style=\"display:none;\"").">\n";
	// 	$out .= "			<div class=\"address-label\">State:</div>\n";
	// 	$out .= "			<div class=\"address-input\">";
	// 	$out .= "				<select name=\"cart_s_state\" id=\"cart_s_state\" />\n";
		
	// 	$cnt = $this->ShippingAddress->Country;
	// 	if (!isset($this->ShippingAddress->Country))
	// 		$cnt = "USA";

	// 	// ***INLINESQL***
	// 	// $sql = "select code, state from pw_states where id > 0 and location = ".smartQuote($this->ShippingAddress->Country)." order by state";
	// 	// $rs = $this->_db->get_results($sql);
	// 	// if (isset($rs))
	// 	// {
	// 	// 	foreach ($rs as $row)
	// 	// 	{
	// 	// 		$strsel = "";
	// 	// 		if ($row->code == $this->ShippingAddress->State)
	// 	// 			$strsel = " selected";
	// 	// 		$out .= "					<option value=\"".$row->code."\"".$strsel.">".$row->state."</option>\n";
	// 	// 	}
	// 	// }

	// 	$out .= "				</select>\n";
	// 	$out .= "			</div>\n";
	// 	$out .= "			<div class=\"clearfloat\"></div>\n";
	// 	$out .= "		</div>\n";
	// 	$out .= "		<div class=\"address-line\" id=\"label_s_istate\"".($initdisplays == "text" ? "" : " style=\"display:none;\"").">\n";
	// 	$out .= "			<div class=\"address-label\">State:</div>\n";
	// 	$out .= "			<div class=\"address-input\"><input type=\"text\" name=\"cart_s_istate\" id=\"cart_s_istate\" value=\"".$this->ShippingAddress->State."\" maxlength=\"50\" /></div>\n";
	// 	$out .= "			<div class=\"clearfloat\"></div>\n";
	// 	$out .= "		</div>\n";
	// 	$out .= "		<div class=\"address-line\">\n";
	// 	$out .= "			<div class=\"address-label\">Zip/Postal Code:</div>\n";
	// 	$out .= "			<div class=\"address-input\"><input type=\"text\" name=\"cart_s_zip\" id=\"cart_s_zip\" value=\"".$this->ShippingAddress->Zip."\" maxlength=\"50\" /></div>\n";
	// 	$out .= "			<div class=\"clearfloat\"></div>\n";
	// 	$out .= "		</div>\n";
	// 	$out .= "		<div class=\"address-line\">\n";
	// 	$out .= "			<div class=\"address-label\">Phone:</div>\n";
	// 	$out .= "			<div class=\"address-input\"><input type=\"text\" name=\"cart_s_phone\" id=\"cart_s_phone\" value=\"".$this->ShippingAddress->Phone."\" maxlength=\"25\" /></div>\n";
	// 	$out .= "			<div class=\"clearfloat\"></div>\n";
	// 	$out .= "		</div>\n";
	// 	$out .= "		\n";
	// 	$out .= "		\n";
	// 	$out .= "		\n";
	// 	$out .= "	</div>\n";
	// 	$out .= "	<div class=\"order-signin-error\" id=\"signin-error\">";
	// 	if ($this->LastError != "")
	// 	{
	// 		$out .= "		".$this->LastError;
	// 		$this->LastError = "";
	// 	}
	// 	$out .= "	</div>\n";
	// 	$out .= "	<div class=\"clearfloat\"></div>\n";
	// 	$out .= "	<div class=\"order-continue\"><input class=\"green button\" name=\"cart_btn\" type=\"submit\" value=\"CONTINUE\" /></div>\n";
	// 	$out .= "	<input type=\"hidden\" name=\"command\" value=\"address\" />\n";
	// 	$out .= "</form>\n";
	// 	$out .= "</div>\n";
		
	// 	return $out;
	// }
	
	// public function GetStates($country=840, $state="")
	// {
	// 	if ($country == "")
	// 		$country = 840;
		
	// 	if ($country == 840 || $country == 36 || $country == 124)
	// 	{
	// 		$cnt = "";
			
	// 		// ***INLINESQL***
	// 		// $sql = "select code from cc_countries where numcode = ".$country;
	// 		// $cnt = $this->_db->get_var($sql);
			
	// 		$out = "";
	// 		$retval = array();
			
	// 		// ***INLINESQL***
	// 		// $sql = "select code, state from pw_states where id > 0 and location = ".smartQuote($cnt)." and location is not null order by state";
	// 		// $rs = $this->_db->get_results($sql);
	// 		// foreach ($rs as $row)
	// 		// {
	// 		// 	$ta = array();
	// 		// 	$strsel = false;
	// 		// 	if ($row->code == $state)
	// 		// 		$strsel = true;
				
	// 		// 	$ta["text"] = $row->state;
	// 		// 	$ta["value"] = $row->code;
	// 		// 	$ta["selected"] = $strsel;
	// 		// 	$retval[] = $ta;
	// 		// }
			
	// 		return $retval;
	// 	}
	// 	else
	// 		return "";
	// }
	
	// public function CartLoginView($urltag="shop")
	// {
	// 	$landing = $urltag == "special" ? true : false;
		
	// 	$out = "";
		
	// 	$out .= "	<div class=\"order-signin\" style=\"position: relative;\">\n";
	// 	$out .= "		<img src=\"/framework/img/close.png\" style=\"height:32px; width:32px; position:absolute; right:-12px; top:-12px; cursor:pointer;\" onclick=\"unpopWindow();\" />\n";
	// 	$out .= "		<div class=\"order-summary-heading\">YOUR ACCOUNT</div>\n";
	// 	if (!$landing)
	// 		$out .= "		<form action=\"/".$urltag."/cart/continue\" method=\"post\" id=\"checkout_form\">\n";
	// 	$out .= "			<div class=\"customer-signin\">\n";
	// 	$out .= "				<div class=\"heading\">New Customers</div>\n";
	// 	$out .= "				<div class=\"address-line\">\n";
	// 	$out .= "					<div class=\"address-label\">E-mail Address:</div>\n";
	// 	$out .= "					<div class=\"address-input\"><input type=\"text\" id=\"cart_new_email\" name=\"cart_new_email\" value=\"\" maxlength=\"50\" /></div>\n";
	// 	$out .= "					<div class=\"clearfloat\"></div>\n";
	// 	$out .= "				</div>\n";
	// 	$out .= "				<div class=\"address-line\">\n";
	// 	$out .= "					<div class=\"address-label\">First Name:</div>\n";
	// 	$out .= "					<div class=\"address-input\"><input type=\"text\" id=\"cart_new_firstname\" name=\"cart_new_firstname\" value=\"\" maxlength=\"50\" /></div>\n";
	// 	$out .= "					<div class=\"clearfloat\"></div>\n";
	// 	$out .= "				</div>\n";
	// 	$out .= "				<div class=\"address-line\">\n";
	// 	$out .= "					<div class=\"address-label\">Last Name:</div>\n";
	// 	$out .= "					<div class=\"address-input\"><input type=\"text\" id=\"cart_new_lastname\" name=\"cart_new_lastname\" value=\"\" maxlength=\"50\" /></div>\n";
	// 	$out .= "					<div class=\"clearfloat\"></div>\n";
	// 	$out .= "				</div>\n";
	// 	$out .= "				<div class=\"address-line\">\n";
	// 	$out .= "					<div class=\"address-label\">Password:</div>\n";
	// 	$out .= "					<div class=\"address-input\"><input type=\"password\" id=\"cart_new_password\" name=\"cart_new_password\" value=\"\" maxlength=\"32\" /></div>\n";
	// 	$out .= "					<div class=\"clearfloat\"></div>\n";
	// 	$out .= "				</div>\n";
	// 	$out .= "				<div class=\"address-line\">\n";
	// 	$out .= "					<div class=\"address-label\">Confirm Password:</div>\n";
	// 	$out .= "					<div class=\"address-input\"><input type=\"password\" id=\"cart_new_passwordc\" name=\"cart_new_passwordc\" value=\"\" maxlength=\"32\" /></div>\n";
	// 	$out .= "					<div class=\"clearfloat\"></div>\n";
	// 	$out .= "				</div>\n";
	// 	$out .= "				<div class=\"order-signin-controls\">\n";
	// 	$out .= "					<div class=\"order-summary-continue\"><button class=\"green button\" name=\"cart_btn\" id=\"checkout_newaccount\" onclick=\"scCheckNewAccount(".$landing.");return false;\" type=\"button\">New Account</button></div>\n";
	// 	$out .= "					<div class=\"clearfloat\"></div>\n";
	// 	$out .= "				</div>\n";
	// 	$out .= "			</div>\n";
	// 	if (!$landing)
	// 	$out .= "		</form>\n";
	// 	if (!$landing)
	// 	$out .= "		<form action=\"/".$urltag."/cart/continue\" method=\"post\" id=\"checkout_form\">\n";
	// 	$out .= "			<div class=\"customer-signin\">\n";
	// 	$out .= "				<div class=\"heading\">Returning Customers</div>\n";
	// 	$out .= "				<div class=\"address-line\">\n";
	// 	$out .= "					<div class=\"address-label\">E-mail Address:</div>\n";
	// 	$out .= "					<div class=\"address-input\"><input type=\"text\" id=\"cart_login_email\" name=\"cart_email\" value=\"\" maxlength=\"50\" /></div>\n";
	// 	$out .= "					<div class=\"clearfloat\"></div>\n";
	// 	$out .= "				</div>\n";
	// 	$out .= "				<div class=\"address-line\">\n";
	// 	$out .= "					<div class=\"address-label\">Password:</div>\n";
	// 	$out .= "					<div class=\"address-input\"><input type=\"password\" id=\"cart_login_password\" name=\"cart_password\" value=\"\" maxlength=\"32\" /></div>\n";
	// 	$out .= "					<div class=\"clearfloat\"></div>\n";
	// 	$out .= "				</div>\n";
	// 	$out .= "				<div class=\"order-signin-controls\">\n";
	// 	$out .= "					<div class=\"order-summary-continue\"><button class=\"green button\" name=\"cart_btn\" id=\"checkout_login\" onclick=\"scCheckLogin(".$landing.");return false;\" type=\"button\">Sign In</button></div>\n";
	// 	$out .= "					<div class=\"clearfloat\"></div>\n";
	// 	$out .= "				</div>\n";
	// 	$out .= "			</div>\n";
	// 	if (!$landing)
	// 	$out .= "		</form>\n";
	// 	$out .= "		<div class=\"order-signin-error\" id=\"signin-error\"></div>\n";
	// 	$out .= "		<div class=\"clearfloat\"></div>\n";
	// 	$out .= "	</div>\n";
		
	// 	return $out;
	// }
	
	public function ReviewCart()
	{
		$out = "";
		$savings = 0.0;
		$subtotal = 0.0;
		$num = 1;
		$shipping = 0;
		$tax = 0.0;
		$total = 0.0;
		$loggedin = false;
		if (isset($_SESSION["__account__"]))
		{
			$loggedin = $_SESSION["__account__"]->LoggedIn();
		}
		
		$out .= $this->HideSidebar();
		$out .= "<div class=\"content\">\n";
		$out .= $this->Breadcrumbs("review");
		$out .= "<form action=\"/shop/cart/continue\" method=\"post\" id=\"checkout_form\">\n";
		$out .= "	<div class=\"order-summary\">\n";
		$out .= "		<div class=\"order-summary-heading\">ORDER SUMMARY</div>\n";
		$out .= "		<div class=\"order-summary-titles\">";
		$out .= "			<p class=\"product\">Product</p>\n";
		$out .= "			<p class=\"format\">Format</p>\n";
		$out .= "			<p class=\"qty\">Qty</p>\n";
		$out .= "			<p class=\"price\">Price</p>\n";
		$out .= "			<p class=\"itemtotal\">Item Total</p>\n";
		$out .= "			<div class=\"clearfloat\"></div>\n";
		$out .= "		</div>\n";
		foreach ($this->Items as &$item)
		{
			$p = new Product();
			$p->GetProduct($item->PID);
			$out .= "	<div class=\"order-summary-items".($num == 1 ? "-first" : "")."\">\n";
			$out .= "		<p class=\"product\">\n";
			$out .= "			<img src=\"".$p->ImageURL()."\" alt=\"".$p->ProductName."\" border=\"0\" />\n";
			$out .= "			<strong>".$p->ProductName."</strong><br>by ".$p->Manufacturer."<br>Ships in 2 to 3 business days\n";
			$out .= "		</p>\n";
			// $out .= "		<p class=\"format\">".$p->Format."</p>\n";
			$out .= "		<p class=\"qty\">\n";
			$out .= "			<input type=\"text\" name=\"cart_item[".$item->PID."]\" id=\"cart_item[".$item->PID."]\" value=\"".$item->Quantity."\" maxlength=\"3\" /><br>\n";
			$out .= "			<span class=\"remove\"><a href=\"/shop/cart/remove/".$item->PID."\">remove &rsaquo;</a></span>\n";
			$out .= "		</p>\n";
			$out .= "		<p class=\"price\"><strong>$".money_format("%.2n", round($p->Price, 2))."</strong></p>\n";
			$out .= "		<p class=\"itemtotal\"><strong>$".money_format("%.2n", round($p->Price * $item->Quantity, 2))."</strong></p>\n";
			$subtotal += round($p->Price * $item->Quantity, 2); 
			$out .= "		</p>\n";
			$out .= "		<div class=\"clearfloat\"></div>\n";
			$out .= "	</div>\n";
			$num++;
		}
		
		$tax = $loggedin === false ? 0 : ($subtotal + $shipping) * $this->GetTaxRate();
		
		$total = $subtotal + $shipping + $tax;
		
		$out .= "	</div>\n";
		$out .= "	<div class=\"order-summary\">\n";
		$out .= "		<div class=\"order-summary-heading\">ORDER TOTAL</div>\n";
		$out .= "		<div class=\"order-summary-total\">&nbsp;</div>\n";
		$out .= "		<div class=\"order-summary-total\">\n";
		$out .= "			<p class=\"summary\">Order Subtotal: </p>\n";
		$out .= "			<p class=\"totals\">$".money_format("%.2n", round($subtotal, 2))."</p>\n";
		$out .= "			<div class=\"clearfloat\"></div>\n";
		$out .= "		</div>\n";
		$out .= "		<div class=\"order-summary-total\">\n";
		$out .= "			<p class=\"summary\">Shipping: </p>\n";
		$out .= "			<p class=\"totals\">$".money_format("%.2n", round($shipping, 2))."</p>\n";
		$out .= "			<div class=\"clearfloat\"></div>\n";
		$out .= "		</div>\n";
		$out .= "		<div class=\"order-summary-total\">\n";
		$out .= "			<p class=\"summary\"><span class=\"estship\">(Estimated using US Standard Rate)</p></p>\n";
		$out .= "			<p class=\"totals\">&nbsp;</p>\n";
		$out .= "			<div class=\"clearfloat\"></div>\n";
		$out .= "		</div>\n";
		$out .= "		<div class=\"order-summary-total\">\n";
		$out .= "			<p class=\"summary\">Estimated Tax: </p>\n";
		$out .= "			<p class=\"totals\">".($tax <= 0 ? "---" : "$".money_format("%.2n", round($tax, 2)))."</p>\n";
		$out .= "			<div class=\"clearfloat\"></div>\n";
		$out .= "		</div>\n";
		$out .= "		<div class=\"order-summary-total\">&nbsp;</div>\n";
		$out .= "		<div class=\"order-summary-total\">\n";
		$out .= "			<p class=\"summary\"><strong>Estimated Total: </strong></p>\n";
		$out .= "			<p class=\"totals\"><strong>$".money_format("%.2n", round($total, 2))."</strong></p>\n";
		$out .= "			<div class=\"clearfloat\"></div>\n";
		$out .= "		</div>\n";
		$out .= "		<div class=\"order-summary-total\">&nbsp;</div>\n";
		$out .= "		<div class=\"order-summary-total\">\n";
		$out .= "			<div class=\"order-summary-continue\"><input class=\"green button\" name=\"cart_btn\" id=\"checkout_first\" value=\"CONTINUE CHECKOUT\" onclick=\"scCheckCheckout();return false;\" type=\"button\" /></div>\n";
		$out .= "			<div class=\"order-summary-promo\">Promo Code: <input type=\"text\" maxlength=\"10\" value=\"".$this->PromoCode."\" name=\"cart_promocode\" id=\"cart_promocode\"> <input class=\"button smallbutton\" name=\"cart_btn\" type=\"submit\" value=\"Update Cart\" /></div>\n";
		$out .= "		</div>\n";
		$out .= "	</div>\n";
		$out .= "	<input type=\"hidden\" name=\"cart_process\" id=\"cart_process\" value=\"\">\n";
		$out .= "	<input type=\"hidden\" name=\"command\" value=\"review\" />\n";
		$out .= "	</form>\n";
		$out .= "</div>\n";
		
		return $out;
	}
	
	// public function ShowMiniCart()
	// {
	// 	$out = "";
	// 	$savings = 0.0;
	// 	$subtotal = 0.0;
	// 	$lastpid = 0;
		
	// 	$out .= "<form action=\"/shop/cart/update\" method=\"post\">\n";
	// 	$out .= "<div class=\"mini-cart\">\n";
	// 	$out .= "	<div class=\"header\">\n";
	// 	$out .= "		<p class=\"item\">Item</p>\n";
	// 	$out .= "		<p class=\"qty\">Qty</p>\n";
	// 	$out .= "		<p class=\"price\">Price</p>\n";
	// 	$out .= "		<div class=\"clearfloat\"></div>\n";
	// 	$out .= "	</div>\n";
	// 	foreach ($this->Items as &$item)
	// 	{
	// 		$lastpid = $item->PID;
	// 		$p = new Product();
	// 		$p->GetProduct($item->PID);
	// 		$p->CalculateValues();
	// 		$out .= "	<div class=\"cart-item\">\n";
	// 		$out .= "		<p class=\"item\"><strong>".$p->ProductName."</strong><br>by ".$p->Manufacturer."</p>\n";
	// 		$out .= "		<p class=\"qty\"><input type=\"text\" name=\"cart_item[".$item->PID."]\" id=\"cart_item[".$item->PID."]\" value=\"".$item->Quantity."\" maxlength=\"3\" /></p>\n";
	// 		$out .= "		<p class=\"price\">";
	// 		if ($p->CalculatedDiscount > 0)
	// 		{
	// 			$out .= "				$".money_format("%.2n", round($p->CalculatedPrice * $item->Quantity, 2))."<br>\n";
	// 			$out .= "				<span class=\"savings\">Save ".round($p->CalculatedDiscount)."%</span>\n";
	// 			$savings += round($p->Price - $p->CalculatedPrice, 2) * $item->Quantity;
	// 			$subtotal += round($p->CalculatedPrice * $item->Quantity, 2); 
	// 		}
	// 		else
	// 		{
	// 			$out .= "				$".money_format("%.2n", round($p->Price * $item->Quantity, 2))."\n";
	// 			$subtotal += round($p->Price * $item->Quantity, 2); 
	// 		}
	// 		$out .= "		</p>\n";
	// 		$out .= "		<div class=\"clearfloat\"></div>\n";
	// 		$out .= "	</div>\n";
	// 	}
	// 	$out .= "	<hr class=\"divider\">\n";
	// 	$out .= "	<div class=\"totals\">\n";
	// 	$out .= "		<div class=\"subtotal\">Subtotal: <strong>$".money_format("%.2n", $subtotal)."</strong></div>\n";
	// 	if ($savings > 0)
	// 	{
	// 		$out .= "		<div class=\"savings\">Save $".money_format("%.2n", $savings)."</div>\n";
	// 	}
	// 	$out .= "		<div></div>\n";
	// 	$out .= "		<div></div>\n";
	// 	$out .= "	</div>\n";
	// 	$out .= "</div>";
	// 	$out .= "<div>\n";
	// 	$out .= "	<div style=\"margin-left:10px;\">Promo Code: <input type=\"text\" maxlength=\"10\" value=\"".$this->PromoCode."\" name=\"cart_promocode\" id=\"cart_promocode\"> ";
	// 	$out .= "		<input class=\"button smallbutton\" type=\"submit\" value=\"Update Cart\" />\n";
	// 	$out .= "	</div>\n";
	// 	$out .= "</div>\n";
	// 	$out .= "<div class=\"clearfloat\"></div>\n";
	// 	$out .= "<input type=\"hidden\" name=\"cart_pid\" id=\"cart_pid\" value=\"".$lastpid."\">\n";
	// 	$out .= "</form>\n";
		
	// 	return $out;
	// }
	
	// private function ShowAdditionalProducts($pid, $num)
	// {
	// 	$out = "";
	// 	$firsttitle = "Related Products...";
	// 	$classname = "additional-products";
		
	// 	if ($pid == NULL)
	// 	{
	// 		$items = $this->PopularTitles($num);
	// 		$firsttitle = "Popular Titles...";
	// 		$classname = "try-these-products";
	// 	}
	// 	else
	// 	{
	// 		$p = new Product();
	// 		$p->GetProduct($pid);
	// 		$items = $p->SeeAlso($num);
	// 	}
		
	// 	if (count($items) > 0)
	// 	{
	// 		$out .= "<div class=\"".$classname."\">\n";
	// 		$out .= "	<div class=\"banner\">".$firsttitle."</div>\n";
	// 		foreach ($items as &$item)
	// 		{
	// 			$px = new Product();
	// 			$px->GetProduct($item->PID);
	// 			$out .= $px->FloatView();
	// 		}
	// 		$out .= "	<div class=\"clearfloat\"></div>\n";
	// 		$out .= "</div>\n";
	// 	}

	// 	$items = $this->YouMightEnjoy($num);
	// 	if (count($items) > 0)
	// 	{
	// 		$out .= "<div class=\"".$classname."\">\n";
	// 		$out .= "	<div class=\"banner\">You might also enjoy...</div>\n";
	// 		foreach ($items as $key=>&$value)
	// 		{
	// 			$px = new Product();
	// 			$px->GetProduct($key);
	// 			$out .= $px->FloatView();
	// 			// ***INLINESQL***
	// 			// $sql = "update cc_store_ads_viewed set counter = counter + 1, datestamp = getdate() where advertid = ".smartQuote($value);
	// 			// $this->_db->query($sql);
	// 		}
	// 		$out .= "	<div class=\"clearfloat\"></div>\n";
	// 		$out .= "</div>\n";
	// 	}
		
	// 	return $out;
	// }
	
	// public function PopularTitles($num=5)
	// {
	// 	$titles = array();
		
	// 	// ***INLINESQL***
	// 	// $sql = "select top ".smartQuote($num)." i.pid, p.pname, p.isbn, p.code, p.author, COUNT(i.pid) ".
	// 	// 	"from cc_orders_items as i  ".
	// 	// 	"	inner join cc_orders as o on (o.ordid = i.ordid) ".
	// 	// 	"	inner join cc_product as p on (p.PID = i.pid) ".
	// 	// 	"where i.ordid like 'CO%' ".
	// 	// 	"	and o.orderdate > DATEADD(D, -90, GETDATE()) ".
	// 	// 	"	and p.active = 1 ".
	// 	// 	"	and p.ordertype = \"S\" ".
	// 	// 	"group by i.pid, p.pname, p.isbn, p.code, p.author ".
	// 	// 	"order by COUNT(i.pid) desc";
	// 	// $rs = $this->_db->get_results($sql, ARRAY_A);
	// 	// if ($rs)
	// 	// {
	// 	// 	foreach ($rs as $row)
	// 	// 	{
	// 	// 		$x = Utilities::ToISBN13($row["isbn"]);
	// 	// 		$url = Utilities::BeautifyURL($row["pname"]);
	// 	// 		$titles[] = new MiniProduct($row["pid"], $row["pname"], $row["author"], $row["isbn"], $x, isBlank($row["isbn"])?$row["code"]:$x, $url);
	// 	// 	}
	// 	// }
		
	// 	return $titles;
	// }
	
	// public function YouMightEnjoy($num=5)
	// {
	// 	$retval = array();
		
	// 	// ***INLINESQL***
	// 	// $sql = "select top ".smartQuote($num)." a.advertid, a.adimage, a.pid, a.category, a.adguid, p.pname, p.isbn, p.author, p.code, p.binding, isnull(a.adspecial, '') as adspecial from cc_store_ads as a inner join cc_store_ads_viewed as v on (v.advertid = a.advertid) left join cc_product as p on (p.pid = a.pid) where a.adtype = 'small' and a.active = 1 and getdate() >= a.startdate and getdate() < dateadd(d, 1, a.enddate) order by v.datestamp";
	// 	// $rs = $this->_db->get_results($sql, ARRAY_A);
	// 	// foreach ($rs as $row)
	// 	// {
	// 	// 	$retval[$row["pid"]] = $row["advertid"];
	// 	// }
		
	// 	return $retval;
	// }
	
	public function JustAdded($pid)
	{
		$out = "";
		
		$p = new Product();
		$p->GetProduct($pid);
		
		$out .= $this->HideSidebar();
		$out .= "<div class=\"just-added\">\n";
		$out .= "	<div class=\"added-product\">\n";
		$out .= "		<h2>CART <input class=\"green button\" onclick=\"location='/shop/cart/view';\" type=\"button\" value=\"CHECKOUT NOW\" /></h2>\n";
		$out .= "		<div class=\"just-added-banner\">You just added this item to your cart.</div>\n";
		$out .= "		<div class=\"product-thumbnail\">\n";
		//$out .= "			<a href=\"".$p->Permalink()."\"><img src=\"".$p->ImageURL()."\" alt=\"".$p->ProductName."\" border=\"0\" /></a>\n";
		$out .= "			<a href=\"\"><img src=\"".$p->ImageURL()."\" alt=\"".$p->ProductName."\" border=\"0\" /></a>\n";
		$out .= "		</div>\n";
		$out .= "		<div class=\"product-details\">\n";
		$out .= "			<ul>\n";
		//$out .= "				<li class=\"title\" style=\"\"><a href=\"".$p->Permalink()."\">".$p->ProductName."</a></li>\n";
		$out .= "				<li class=\"title\" style=\"\"><a href=\"\">".$p->ProductName."</a></li>\n";
		$out .= "				<li class=\"manufacturer\">by <a href=\"#\">".$p->Manufacturer."</a></li>\n";
		//$out .= "				<li class=\"format\">".$p->Format."</li>\n";
		$out .= "				<li class=\"list-price\">Price: <strong>".money_format("%.2n", $p->Price)."</strong></li>\n";
		$out .= "			</ul>\n";
		$out .= "		</div>\n";
		$out .= "		<div class=\"clearfloat\"></div>\n";
		//$out .= $this->ShowMiniCart();
		$out .= "		<div class=\"just-added-banner\"></div>\n";
		$out .= "			<h2>&nbsp;<input class=\"green button\" onclick=\"location='/shop/cart/view';\" type=\"button\" value=\"CHECKOUT NOW\" /></h2>\n";
		$out .= "			<div class=\"clearfloat\"></div>\n";
		$out .= "		</div>\n";
		$out .= "	</div>\n";
		$out .= "	<div class=\"product-list\">\n";
		//$out .= $this->ShowAdditionalProducts($pid, 4);
		$out .= "	</div>\n";
		$out .= "	<div class=\"clearfloat\"></div>\n";
		$out .= "</div>\n";
		
		return $out;
	}
}

class CartItem
{
	public $PID = "";
	public $Quantity = 0;
	
	public function __construct($pid, $qty)
	{
		$this->PID = $pid;
		$this->Quantity = $qty;
	}
}
?>