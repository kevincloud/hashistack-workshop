<?php

class Order
{
	public $ID = 0;
	public $OrderID = "";
	public $CustomerID = "";
	public $OrderDate = NULL;
	public $ShipDate = NULL;
	public $TaxAmount = 0.0;
	public $ShippingAmount = 0.0;
	public $SubtotalAmount = 0.0;
	public $TotalAmount = 0.0;
	public $Comments = "";
	public $ShippingAddress = NULL;
	public $Status = "";
	public $Items = array();
	public $Invoice = NULL;
	public $TmpOrderID = "";
	public $OrderApi = "";
	
	
	private $_settings;
	
	public function __construct()
	{
		global $orderapi;

		$this->OrderApi = $orderapi;

		$this->ShippingAddress = new Address();
		
		$this->_settings = new ApplicationSettings();
	}
	
	public function GetOrder($ordid)
	{
		if (!isBlank($ordid))
		{
		// 	***INLINESQL***
		// 	$sql = "select id, rguid, ordid, custid, ordertype, orderdate, custpo, shipdate, discount, addrid, ".
		// 		"	tax, freight, subtotal, totalamt, cust_source, source, comments, shiptype, sname, saddr1, ".
		// 		"	saddr2, scity, sstate, szip, scountry, scountry_numcode, sphone, semail, status, promocode ".
		// 		"from cc_orders ".
		// 		"where ordid = ".smartQuote($ordid);
		// 	$row = $this->_db->get_row($sql);
		// 	if (count($row) >= 1)
		// 	{
		// 		$this->ID = $row->id;
		// 		$this->RowGuid = $row->rguid;
		// 		$this->OrderID = $row->ordid;
		// 		$this->CustomerID = $row->custid;
		// 		$this->OrderType = $row->ordertype;
		// 		$this->OrderDate = $row->orderdate;
		// 		$this->CustomerPO = $row->custpo;
		// 		$this->ShipDate = $row->shipdate;
		// 		$this->Discount = $row->discount;
		// 		$this->TaxAmount = $row->tax;
		// 		$this->ShippingAmount = $row->freight;
		// 		$this->SubtotalAmount = $row->subtotal;
		// 		$this->TotalAmount = $row->totalamt;
		// 		$this->CustomerSource = $row->cust_source;
		// 		$this->OrderSource = $row->source;
		// 		$this->Comments = $row->comments;
		// 		$this->ShipMethod = $row->shiptype;
		// 		$this->ShippingEmail = $row->semail;
		// 		$this->Status = $row->status;
		// 		$this->PromoCode = $row->promocode;
		// 		$this->AddressID = $row->addrid;
		// 		$this->ShippingAddress->AddressID = $row->addrid;
		// 		$this->ShippingAddress->Contact = $row->sname;
		// 		$this->ShippingAddress->Address1 = $row->saddr1;
		// 		$this->ShippingAddress->Address2 = $row->saddr2;
		// 		$this->ShippingAddress->City = $row->scity;
		// 		$this->ShippingAddress->State = $row->sstate;
		// 		$this->ShippingAddress->Zip = $row->szip;
		// 		$this->ShippingAddress->Country = $row->scountry;
		// 		$this->ShippingAddress->CountryCode = $row->scountry_numcode;
		// 		$this->ShippingAddress->Phone = $row->sphone;
				
		// 		unset($this->Items);
		// 		$sql = "select id from cc_orders_items where ordid = ".smartQuote($ordid);
		// 		$rs = $this->_db->get_results($sql);
		// 		if (count($rs) > 0)
		// 		{
		// 			foreach($rs as $row)
		// 			{
		// 				$i = new OrderItem($this->_db);
		// 				$i->GetItem($row->id);
		// 				if ($i->IsEBook == true) $this->HasEBook = true;
		// 				if ($i->IsAudioBook == true) $this->HasAudioBook = true;
		// 				$this->Items[] = $i;
		// 			}
		// 		}
		// 	}
		}
	}
	
	public function GenerateOrderID()
	{
		$onum = sprintf("%02d", rand(2500, 98943));
		return "ORD".date("Ydm").$onum;
	}
	
	public function Save()
	{
		if ($this->OrderID == "")
		{
			if ($this->TmpOrderID != "")
			{
				$this->OrderID = $this->TmpOrderID;
			}
			else
			{
				// ***INLINESQL***
				// $sql = "exec s_newcustorderid";
				// $this->OrderID = $this->_db->get_var($sql);
			}

			$request = $this->OrderApi."/order";
			$rr = new RestRunner();
			$retval = $rr->Post($request, $this->OutputJson());
			echo "<pre>";
			print_r($retval);
			echo "</pre>";
			exit();
		}
	}
	
	public function DisplayShipMethod()
	{
		switch (strtoupper($this->ShipMethod))
		{
			case "COLLECT":
				return "Collect";
			case "SPC":
			case "CUSTOM":
				return "Other";
			case "USPSMMDC":
			case "MML":
				return "Media Mail";
			case "UPS3GC":
				return "Third-Party Shipping";
				return "Priority (Tracked / Insured)";
			case "STD":
			case "FDXGND":
			case "DHL4MED":
			case "PUROGND":
			case "UPSGSCNA":
			case "UPSGSRNA":
				return "Standard (Tracked / Insured)";
			case "EXP":
			case "UPSNDA":
			case "UPSNDAR":
				return "Overnight (Tracked / Insured)";
			case "PRI":
			case "SEL":
			case "UPSSDA":
			case "DHL4PRI":
			case "UPSSDAR":
			case "UPS3DASR":
				return "Priority (Tracked / Insured)";
			case "DHLWPX":
			case "IMEXROW":
			case "UPSWWX":
				return "International Priority (Tracked / Insured)";
			case "USP":
			case "USPS1P":
				return "Standard";
			default:
				return "Electronic Delivery";
		}
	}
	
	public function DisplayOrder($msg="")
	{
		if ($this->OrderID == "")
			return "";
		
		if (!isset($_SESSION["__account__"]))
		{
			return "Please login to view your orders.";
		}
		
		if ($this->CustomerID != $_SESSION["__account__"]->CustomerID)
		{
			return "You do not access to view this order.";
		}

		$this->_settings = new ApplicationSettings();
		
		$out = "";
		
		$invoice = new Invoice();

		// ***INLINESQL***
		// $sql = "select invnum from cc_invoice where ordref = ".smartQuote($this->OrderID);
		// $invnum = $this->_db->get_var($sql);
		// $invoice->GetInvoice($invnum);
		
		$out .= "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\">\n";
		$out .= "<html>\n";
		$out .= "	<head>\n";
		$out .= "		<title>Order Confirmation - ".$this->OrderID."</title>\n";
		$out .= "	</head>\n";
		$out .= "	<body style=\"margin:0;padding:0\" bgcolor=\"#FFF9EE\">\n";
		$out .= "		<table align=\"center\" cellspacing=\"0\" cellpadding=\"0\" bgcolor=\"#FFF9EE\">\n";
		$out .= "			<tr>\n";
		$out .= "				<td style=\"padding:18px 10px 20px 10px\">\n";
		$out .= "					<table align=\"center\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\" width=\"702\">\n";
		$out .= "						<tr>\n";
		$out .= "							<td>\n";
		$out .= "								<table align=\"center\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\" width=\"702\">\n";
		$out .= "									<tr>\n";
		$out .= "										<td style=\"padding-bottom:8px;padding-left:2px\" valign=\"bottom\" align=\"left\">\n";
		$out .= "											<img src=\"".$this->_settings->SiteURL."/framework/img/wp-mail-header.png\" width=\"279\" height=\"69\" style=\"display:block;margin:0\" border=\"0\" alt=\"Java Perks\">\n";
		$out .= "										</td>\n";
		$out .= "										<td style=\"padding-bottom:10px;\" valign=\"bottom\" align=\"right\">\n";
		$out .= "											<div style=\"font-family: Helvetica, sans-serif, Arial, Verdana;color:#444444;font-size:20px;line-height:1em !important\">Order Confirmation</div>\n";
		$out .= "										</td>\n";
		$out .= "									</tr>\n";
		$out .= "								</table>\n";
		$out .= "							</td>\n";
		$out .= "						</tr>\n";
		$out .= "						<tr>\n";
		$out .= "							<td>\n";
		$out .= "								<table style=\"-webkit-border-radius:5px;-moz-border-radius:5px;\" bgcolor=\"#ffffff\" align=\"center\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\" width=\"702\">\n";
		if ($this->PromoCode != "")
		{
			// ***INLINESQL***
			// $sql = "select email_html from cc_store_promotions where promocode = ".smartQuote($this->PromoCode)." and email_html is not null";
			// $emailtxt = $this->_db->get_var($sql);
			if ($emailtxt != "")
			{
				$out .= "									<tr>\n";
				$out .= "										<td bgcolor=\"#e3e3e3\" width=\"1\"></td>\n";
				$out .= "										<td bgcolor=\"#ffffff\" width=\"700\">\n";
				$out .= "											".$emailtxt;
				$out .= "										</td>\n";
				$out .= "										<td bgcolor=\"#e3e3e3\" width=\"1\"></td>\n";
				$out .= "									</tr>\n";
			}
		}
		$out .= "									<tr>\n";
		$out .= "										<td bgcolor=\"#e3e3e3\" width=\"1\"></td>\n";
		$out .= "										<td bgcolor=\"#ffffff\" width=\"700\">\n";
		$out .= "											<table align=\"center\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\">\n";
		$out .= "												<tr>\n";
		$out .= "													<td style=\"padding:17px 0 0 0;\">\n";
		$out .= "														<table align=\"center\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\" width=\"700\">\n";
		$out .= "															<tr valign=\"middle\">\n";
		$out .= "																<td align=\"left\" style=\"padding-left: 20px;\">\n";
		$out .= "																	<div style=\"font-family:Helvetica, sans-serif, Arial, Verdana;color:#000000;font-size:13px;line-height:0.92em !important;font-weight:bold\">Order Number: <a href=\"".$this->_settings->SiteURL."/shop/cart/order/".strtoupper($this->OrderID)."\" style=\"color:#4e9cde;font-weight:normal\">".$this->OrderID."</a>\n";
		$out .= "    																	</div>\n";
		$out .= "																</td>\n";
		$out .= "																<td align=\"right\" style=\"padding-right: 19px;\">\n";
		$out .= "																	<div style=\"font-family:Helvetica, sans-serif, Arial, Verdana;color:#000000;font-size:12px;line-height:1em !important\">Ordered on ".date("F d, Y", strtotime($this->OrderDate))."</div>\n";
		$out .= "																</td>\n";
		$out .= "															</tr>\n";
		$out .= "															<tr>\n";
		$out .= "																<td style=\"padding-top:15px;line-height: 1px;\" colspan=\"2\">&nbsp;</td>\n";
		$out .= "															</tr>\n";
		$out .= "														</table>\n";
		$out .= "													</td>\n";
		$out .= "												</tr>\n";
		$out .= "											</table>\n";
		$out .= "										</td>\n";
		$out .= "										<td bgcolor=\"#e3e3e3\" width=\"1\"></td>\n";
		$out .= "									</tr>\n";
		$out .= "								</table>\n";
		$out .= "							</td>\n";
		$out .= "						</tr>\n";
		$out .= "						<tr>\n";
		$out .= "							<td>\n";
		$out .= "								<table align=\"center\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\" bgcolor=\"#ffffff\">\n";
		$out .= "									<tr>\n";
		$out .= "										<td bgcolor=\"#e3e3e3\" width=\"1\"></td>\n";
		$out .= "										<td>\n";
		$out .= "											<table align=\"center\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\" width=\"700\">\n";
		$out .= "												<tr>\n";
		$out .= "													<td style=\"padding:6px 20px 8px 20px;background-color:#978864;\" valign=\"top\" align=\"left\">\n";
		$out .= "														<div style=\"color:#ffffff;font-size:13px;font-weight:bold;line-height:1em !important;font-family:Helvetica, sans-serif, Arial, Verdana;\">Items Ordered</div>\n";
		$out .= "													</td>\n";
		$out .= "												</tr>\n";
		$out .= "											</table>\n";
		$out .= "											<table align=\"center\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\" bgcolor=\"#ffffff\">\n";
		$out .= "												<tr>\n";
		$out .= "													<td>\n";
		$out .= "														<table align=\"center\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\" width=\"660\">\n";
		
		foreach ($this->Items as &$item)
		{
			$p = new Product();
			try
			{
				$p->GetProduct($item->PID, true);
				$out .= "															<tr>\n";
				$out .= "																<td width=\"15\" style=\"padding-top:18px\"></td>\n";
				$out .= "																<td width=\"410\" align=\"left\" valign=\"top\" style=\"padding-top:18px\"><div style=\"font-family:Helvetica, sans-serif, Arial, Verdana;color:#000000;font-size:12px;line-height:1.25em;font-weight:bold\">".(isBlank($item->Description) ? $p->ProductName : $item->Description)."</div></td>\n";
				$out .= "																<td width=\"76\" align=\"right\" valign=\"top\" style=\"padding-top:18px\"><div style=\"font-family: Helvetica, sans-serif, Arial, Verdana;color:#797979;font-size:11px;line-height:1.37em;padding-left:5px\">".money_format("%.2n", $item->DiscountedPrice())."</div></td>\n";
				$out .= "																<td width=\"56\" align=\"right\" valign=\"top\" style=\"padding-top:18px\"><div style=\"font-family: Helvetica, sans-serif, Arial, Verdana;color:#797979;font-size:11px;line-height:1.37em;padding-left:5px\">".$item->Quantity."</div></td>\n";
				$out .= "																<td width=\"103\" align=\"right\" valign=\"top\" style=\"padding-top:18px\"><div style=\"font-family: Helvetica, sans-serif, Arial, Verdana;color:#000000;font-size:12px;line-height:1.25em;padding-left:5px\">".money_format("%.2n", $item->ExtendedPrice())."</div></td>\n";
				$out .= "															</tr>\n";
			}
			catch (Exception $x)
			{
				$out .= "															<tr>\n";
				$out .= "																<td width=\"15\" style=\"padding-top:18px\"></td>\n";
				$out .= "																<td width=\"410\" align=\"left\" valign=\"top\" style=\"padding-top:18px\"><div style=\"font-family:Helvetica, sans-serif, Arial, Verdana;color:#000000;font-size:12px;line-height:1.25em;font-weight:bold\">".(isBlank($item->Description) ? "Product No Longer Available" : $item->Description)."</div></td>\n";
				$out .= "																<td width=\"76\" align=\"right\" valign=\"top\" style=\"padding-top:18px\"><div style=\"font-family: Helvetica, sans-serif, Arial, Verdana;color:#797979;font-size:11px;line-height:1.37em;padding-left:5px\">".money_format("%.2n", $item->DiscountedPrice())."</div></td>\n";
				$out .= "																<td width=\"56\" align=\"right\" valign=\"top\" style=\"padding-top:18px\"><div style=\"font-family: Helvetica, sans-serif, Arial, Verdana;color:#797979;font-size:11px;line-height:1.37em;padding-left:5px\">".$item->Quantity."</div></td>\n";
				$out .= "																<td width=\"103\" align=\"right\" valign=\"top\" style=\"padding-top:18px\"><div style=\"font-family: Helvetica, sans-serif, Arial, Verdana;color:#000000;font-size:12px;line-height:1.25em;padding-left:5px\">".money_format("%.2n", $item->ExtendedPrice())."</div></td>\n";
				$out .= "															</tr>\n";
			}
		}
		
		$out .= "														</table>\n";
		$out .= "													</td>\n";
		$out .= "												</tr>\n";
		$out .= "												<tr>\n";
		$out .= "													<td style=\"padding:0 0 10px 0;\">\n";
		$out .= "														<table width=\"662\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\" align=\"center\">\n";
		$out .= "															<tr>\n";
		$out .= "																<td height=\"38\" colspan=\"3\"></td>\n";
		$out .= "															</tr>\n";
		$out .= "															<tr>\n";
		$out .= "																<td colspan=\"3\" height=\"1\" bgcolor=\"#ececec\"></td>\n";
		$out .= "															</tr>\n";
		$out .= "															<tr>\n";
		$out .= "																<td width=\"1\" bgcolor=\"#ececec\"></td>\n";
		$out .= "																<td width=\"660\" bgcolor=\"#f5f5f5\" style=\"\">\n";
		$out .= "																	<table width=\"660\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\">\n";
		$out .= "																		<tr>\n";
		$out .= "																			<td width=\"595\" style=\"padding:18px 47px 20px 18px;\">\n";
		$out .= "																				<table width=\"595\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\">\n";
		$out .= "																					<tr valign=\"top\">\n";
		$out .= "																						<td width=\"295\">\n";
		$out .= "																							<table width=\"295\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\">\n";
		$out .= "																								<tr valign=\"top\" align=\"left\">\n";
		$out .= "																									<td width=\"125\">\n";
		$out .= "																										<div style=\"font-family: Helvetica, sans-serif, Arial, Verdana;font-weight:bold;font-size:11px;line-height:1.36em;color:#777777;\">\n";
		$out .= "																											Ship to:\n";
		$out .= "																										</div>\n";
		$out .= "																									</td>\n";
		$out .= "																									<td width=\"170\" align=\"left\">\n";
		$out .= "																										<div style=\"font-family: Helvetica, sans-serif, Arial, Verdana;font-size:11px;line-height:1.36em;color:#000000;\">\n";
		$out .= "																											".$this->ShippingAddress->DisplayFormatted()."\n";
		$out .= "																										</div>\n";
		$out .= "																									</td>\n";
		$out .= "																								</tr>\n";
		$out .= "																							</table>\n";
		$out .= "																						</td>\n";
		$out .= "																						<td width=\"35\" style=\"padding-bottom:20px;\"></td>\n";
		$out .= "																						<td width=\"265\" style=\"padding-bottom:20px;\">\n";
		$out .= "																							<table width=\"265\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\">\n";
		$out .= "																								<tr valign=\"top\">\n";
		$out .= "																									<td width=\"121\" style=\"padding:0 0 3px 5px;\">\n";
		$out .= "																										<div style=\"font-family: Helvetica, sans-serif, Arial, Verdana;font-weight:bold;font-size:11px;line-height:1.36em;color:#777777;\">\n";
		$out .= "																											Shipping Method:\n";
		$out .= "																										</div>\n";
		$out .= "																									</td>\n";
		$out .= "																									<td width=\"139	\" style=\"padding:0 0 3px 0;\">\n";
		$out .= "																										<div style=\"font-family: Helvetica, sans-serif, Arial, Verdana;font-size:11px;line-height:1.36em;color:#000000;\">".$this->DisplayShipMethod()."</div>\n";
		$out .= "																									</td>\n";
		$out .= "																								</tr>\n";
		$out .= "																							</table>\n";
		$out .= "																						</td>\n";
		$out .= "																					</tr>\n";
		$out .= "																				</table>\n";
		$out .= "																			</td>\n";
		$out .= "																		</tr>\n";
		$out .= "																	</table>\n";
		$out .= "																</td>\n";
		$out .= "																<td width=\"1\" bgcolor=\"#ececec\"></td>\n";
		$out .= "															</tr>\n";
		$out .= "															<tr>\n";
		$out .= "																<td colspan=\"3\">&nbsp;</td>\n";
		$out .= "															</tr>\n";
		$out .= "														</table>\n";
		$out .= "													</td>\n";
		$out .= "												</tr>\n";
		$out .= "												<tr>\n";
		$out .= "													<td>\n";
		$out .= "														<table align=\"center\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\" width=\"700\">\n";
		$out .= "															<tr>\n";
		$out .= "																<td style=\"padding:6px 20px 8px 20px;background-color:#978864;\" valign=\"top\" align=\"left\">\n";
		$out .= "																	<div style=\"color:#ffffff;font-size:13px;font-weight:bold;line-height:1em !important;font-family: Helvetica, sans-serif, Arial, Verdana;\">Payment</div>\n";
		$out .= "																</td>\n";
		$out .= "															</tr>\n";
		$out .= "														</table>\n";
		$out .= "													</td>\n";
		$out .= "												</tr>\n";
		$out .= "												<tr>\n";
		$out .= "													<td style=\"padding:0 0 10px 0;\">\n";
		$out .= "														<table width=\"662\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\" align=\"center\">\n";
		$out .= "															<tr>\n";
		$out .= "																<td height=\"14\" colspan=\"3\"></td>\n";
		$out .= "															</tr>\n";
		$out .= "															<tr>\n";
		$out .= "																<td width=\"662\">\n";
		$out .= "																	<table width=\"662\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\">\n";
		$out .= "																		<tr>\n";
		$out .= "																			<td width=\"597\" style=\"padding:0 47px 28px 18px;\">\n";
		$out .= "																				<table width=\"595\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\">\n";
		$out .= "																					<tr valign=\"top\">\n";
		$out .= "																						<td width=\"295\">\n";
		$out .= "																							<table width=\"295\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\">\n";
		$out .= "																								<tr valign=\"top\" align=\"left\">\n";
		$out .= "																									<td width=\"125\">\n";
		$out .= "																										<div style=\"font-family: Helvetica, sans-serif, Arial, Verdana;font-weight:bold;font-size:11px;line-height:1.36em;color:#777777;\">\n";
		$out .= "																											Bill to:\n";
		$out .= "																										</div>\n";
		$out .= "																									</td>\n";
		$out .= "																									<td width=\"170\" align=\"left\">\n";
		$out .= "																										<div style=\"font-family: Helvetica, sans-serif, Arial, Verdana;font-size:11px;line-height:1.36em;color:#000000;\">\n";
		$out .= "																											".$invoice->BillingAddress->DisplayFormatted()."\n";
		$out .= "																										</div>\n";
		$out .= "																									</td>\n";
		$out .= "																								</tr>\n";
		$out .= "																							</table>\n";
		$out .= "																						</td>\n";
		$out .= "																						<td width=\"35\" style=\"padding-bottom:20px;\"></td>\n";
		$out .= "																						<td width=\"265\" style=\"padding-bottom:20px;\">\n";
		$out .= "																							<table width=\"265\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\">\n";
		$out .= "																								<tr valign=\"top\">\n";
		$out .= "																									<td width=\"121\" style=\"padding:0 0 3px 5px;\">\n";
		$out .= "																										<div style=\"font-family: Helvetica, sans-serif, Arial, Verdana;font-weight:bold;font-size:11px;line-height:1.36em;color:#777777;\">\n";
		$out .= "																										</div>\n";
		$out .= "																									</td>\n";
		$out .= "																									<td width=\"139\" style=\"padding:0 0 3px 0;\">\n";
		$out .= "																										<div style=\"font-family: Helvetica, sans-serif, Arial, Verdana;font-size:11px;line-height:1.36em;color:#000000;\">\n";
		$out .= "																									</td>\n";
		$out .= "																								</tr>\n";
		$out .= "																							</table>\n";
		$out .= "																						</td>\n";
		$out .= "																					</tr>\n";
		$out .= "																				</table>\n";
		$out .= "																			</td>\n";
		$out .= "																		</tr>\n";
		$out .= "																		<tr valign=\"top\">\n";
		$out .= "																			<td>\n";
		$out .= "																				<table width=\"220\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\" align=\"right\">\n";
		$out .= "																					<tr valign=\"top\" align=\"right\">\n";
		$out .= "																						<td width=\"220\" colspan=\"2\" style=\"padding: 12px 0px 0px 0\">\n";
		$out .= "																							<table width=\"220\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\" align=\"right\">\n";
		$out .= "																								<tr>\n";
		$out .= "																									<td width=\"302\" align=\"right\" valign=\"top\" style=\"padding-top:6px\"><div style=\"font-family: Helvetica, sans-serif, Arial, Verdana;color:#797979;font-size:11px;line-height:1em\">Subtotal</div></td>\n";
		$out .= "																									<td width=\"103\" align=\"right\" valign=\"top\" style=\"padding-top:6px\"><div style=\"font-family: Helvetica, sans-serif, Arial, Verdana;color:#797979;font-size:11px;line-height:1em;padding-left:5px\">".money_format("%.2n", $this->SubtotalAmount)."</div></td>\n";
		$out .= "																								</tr>\n";
		$out .= "																								<tr>\n";
		$out .= "																									<td width=\"302\" align=\"right\" valign=\"top\" style=\"padding-top:6px\"><div style=\"font-family: Helvetica, sans-serif, Arial, Verdana;color:#797979;font-size:11px;line-height:1em\">Shipping</div></td>\n";
		$out .= "																									<td width=\"103\" align=\"right\" valign=\"top\" style=\"padding-top:6px\"><div style=\"font-family: Helvetica, sans-serif, Arial, Verdana;color:#797979;font-size:11px;line-height:1em;padding-left:5px\">".money_format("%.2n", $this->ShippingAmount)."</div></td>\n";
		$out .= "																								</tr>\n";
		$out .= "																								<tr>\n";
		$out .= "																									<td width=\"302\" align=\"right\" valign=\"top\" style=\"padding-top:6px\"><div style=\"font-family: Helvetica, sans-serif, Arial, Verdana;color:#797979;font-size:11px;line-height:1em\">Estimated Tax</div></td>\n";
		$out .= "																									<td width=\"103\" align=\"right\" valign=\"top\" style=\"padding-top:6px\"><div style=\"font-family: Helvetica, sans-serif, Arial, Verdana;color:#797979;font-size:11px;line-height:1em;padding-left:5px\">".money_format("%.2n", $this->TaxAmount)."</div></td>\n";
		$out .= "																								</tr>\n";
		$out .= "																								<tr><td colspan=\"2\"><table width=\"205\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" align=\"right\"><tr><td width=\"205\" style=\"padding-top:8px;border-bottom:1px solid #e1e1e1;font-size:1px;line-height:1px;-webkit-text-size-adjust:none\">&nbsp;</td></tr></table></td></tr>\n";
		$out .= "																								<tr>\n";
		$out .= "																									<td width=\"302\" align=\"right\" valign=\"top\" style=\"padding-top:8px\"><div style=\"font-family: Helvetica, sans-serif, Arial, Verdana;color:#000000;font-size:12px;line-height:1em;font-weight:bold\">Order Total</div></td>\n";
		$out .= "																									<td width=\"103\" align=\"right\" valign=\"top\" style=\"padding-top:8px\"><div style=\"font-family: Helvetica, sans-serif, Arial, Verdana;color:#000000;font-size:12px;line-height:1em;font-weight:bold;padding-left:5px\">".money_format("%.2n", $this->TotalAmount)."</div></td>\n";
		$out .= "																								</tr>\n";
		$out .= "																							</table>\n";
		$out .= "																						</td>\n";
		$out .= "																					</tr>\n";
		$out .= "																				</table>\n";
		$out .= "																			</td>\n";
		$out .= "																		</tr>\n";
		$out .= "																	</table>\n";
		$out .= "																</td>\n";
		$out .= "															</tr>\n";
		$out .= "														</table>\n";
		$out .= "													</td>\n";
		$out .= "												</tr>\n";
		$out .= "											</table>\n";
		$out .= "										</td>\n";
		$out .= "										<td bgcolor=\"#e3e3e3\" width=\"1\"></td>\n";
		$out .= "									</tr>\n";
		$out .= "								</table>\n";
		$out .= "							</td>\n";
		$out .= "						</tr>\n";
		$out .= "						<tr>\n";
		$out .= "							<td>\n";
		$out .= "								<table width=\"700\" align=\"center\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">\n";
		$out .= "									<tr><td style=\"padding-top:14px;-webkit-text-size-adjust:125%; text-align:center;\"><div style=\"font-size:10px; line-height:1.3em; color:#979797;font-family: Helvetica, sans-serif, Arial, Verdana\">Copyright &#169; 2019&nbsp;<a style=\"text-decoration:none !important;color:#979797\">Java Perks</a>&#32;All rights reserved.</div></td></tr>\n";
		$out .= "								</table>\n";
		$out .= "							</td>\n";
		$out .= "						</tr>\n";
		$out .= "					</table>\n";
		$out .= "				</td>\n";
		$out .= "			</tr>\n";
		$out .= "		</table>\n";
		$out .= "	</body>\n";
		$out .= "</html>\n";
		
		return $out;
	}

	public function OutputJson()
	{
		$out = "";
		$items = "";

		foreach ($this->Items as $item)
		{
			$items .= $item->OutputJson . ",";
		}

		if ($items != "")
			$items = substr($items, 0, -1);

		$out .="{";
		$out .="	\"orderid\": \"".$this->OrderID."\", ";
		$out .="	\"customerid\": \"".$this->CustomerID."\", ";
		$out .="	\"invoiceid\": \"".$this->Invoice->InvoiceID."\", ";
		$out .="	\"subtotal\": \"".$this->SubtotalAmount."\", ";
		$out .="	\"shipping\": \"".$this->ShippingAmount."\", ";
		$out .="	\"tax\": \"".$this->TaxAmount."\", ";
		$out .="	\"total\": \"".$this->TotalAmount."\", ";
		$out .="	\"comments\": \"".$this->Comments."\", ";
		$out .="	\"address\": { ";
		$out .="		\"contact\": \"".$this->ShippingAddress->Contact."\" ";
		$out .="		\"address1\": \"".$this->ShippingAddress->Address1."\" ";
		$out .="		\"address2\": \"".$this->ShippingAddress->Address2."\" ";
		$out .="		\"city\": \"".$this->ShippingAddress->City."\" ";
		$out .="		\"state\": \"".$this->ShippingAddress->State."\" ";
		$out .="		\"zip\": \"".$this->ShippingAddress->Zip."\" ";
		$out .="		\"phone\": \"".$this->ShippingAddress->Phone."\" ";
		$out .="    },";
		$out .="	\"items\": \"".$items."\" ";
		$out .="}";
		
		return $out;
	}
}

class OrderItem
{
	public $ID = 0;
	public $OrderID = "";
	public $LineNumber = 0;
	public $PID = 0;
	public $Product = "";
	public $Description = "";
	public $Quantity = 0;
	public $Price = 0;
	
	public function __construct()
	{
	}
	
	
	public function GetItem($id)
	{
		// ***INLINESQL***
		// $sql = "select ordid, linenum, pid, custom_descr, isbn, quantity, price, discount, regcode, tracknum, shipper, pickable, feature_text from cc_orders_items where id = ".smartQuote($id);
		// $row = $this->_db->get_row($sql);
		// if (count($row) >= 1)
		// {
		// 	$this->ID = $id;
		// 	$this->OrderID = $row->ordid;
		// 	$this->LineNumber = $row->linenum;
		// 	$this->PID = $row->pid;
		// 	$this->Description = $row->custom_descr;
		// 	$this->ISBN = $row->isbn;
		// 	$this->Quantity = $row->quantity;
		// 	$this->Price = $row->price;
		// 	$this->Discount = $row->discount;
		// 	$this->RegistrationCode = $row->regcode;
		// 	$this->TrackingNumber = $row->tracknum;
		// 	$this->Courier = $row->shipper;
		// 	$this->Pickable = $row->pickable;
		// 	$this->ExtraText = $row->feature_text;
			
		// 	$sql = "select binding from cc_product where pid = ".$this->PID;
		// 	$fmt = $this->_db->get_var($sql);
		// 	switch ($fmt)
		// 	{
		// 		case "ebook":
		// 			$this->IsEBook = true;
		// 			break;
		// 		case "audiomp3":
		// 			$this->IsAudioBook = true;
		// 			break;
		// 	}
		// }
	}
	
	public function SaveItem()
	{
		if ($this->OrderID == "" || $this->LineNumber <= 0)
			return;
		
		if ($this->ID == 0)
		{
			// ***INLINESQL***
			// $sql = "set nocount on; ".
			// 	"insert into cc_orders_items(ordid, linenum, pid, custom_descr, isbn, quantity, price, discount, pickable, feature_text) values(".
			// 	smartQuote($this->OrderID).", ".
			// 	smartQuote($this->LineNumber).", ".
			// 	smartQuote($this->PID).", ".
			// 	smartQuote($this->Description).", ".
			// 	smartQuote($this->ISBN).", ".
			// 	smartQuote($this->Quantity).", ".
			// 	smartQuote($this->Price).", ".
			// 	smartQuote($this->Discount).", ".
			// 	smartQuote($this->Pickable).", ".
			// 	smartQuote($this->ExtraText).");".
			// 	"select @@identity as id";
			// $this->ID = $this->_db->get_var($sql);
		}
	}
	
	public function DiscountedPrice()
	{
		return round((1 - ($this->Discount / 100)) * $this->Price, 2);
	}
	
	public function ExtendedPrice()
	{
		return $this->DiscountedPrice() * $this->Quantity;
	}

	public function ImageURL($ext=false)
	{
		$out = "";
		
		$relpath = "/products/images/".$this->PID."/large/".Utilities::ToISBN13($this->ISBN).".jpg";
		
		if ($ext === true)
		{
			if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off')
				$out .= "http://";
			else
				$out .= "https://";
			
			$out .= $_SERVER['HTTP_HOST'].$relpath;
		}
		else
			$out = $relpath;
		
		return $out;
	}

	public function OutputJson()
	{
		$out = "";

		$out .= "{";
		$out .= "	\"ID\" : \"".$this->ID."\", ";
		$out .= "	\"LineNumber\" : \"".$this->LineNumber."\", ";
		$out .= "	\"Product\" : \"".$this->Product."\", ";
		$out .= "	\"Description\" : \"".$this->Description."\", ";
		$out .= "	\"Price\" : \"".$this->Price."\", ";
		$out .= "	\"Quantity\" : \"".$this->Quantity."\" ";
		$out .= "}";

		return $out;
	}
}






?>

{
	"orderid": "test",
	"customerid": "test",
	"invoiceid": "test",
	"subtotal": "test",
	"shipping": "test",
	"tax": "test",
	"total": "test",
	"comments": "test",
	"address": { 
		"contact": "test",
		"address1": "test",
		"address2": "test",
		"city": "test",
		"state": "test", 
		"zip": "test",
		"phone": "test"
    },
	"items": [
		{
			"ID" : 0,
			"LineNumber" : 1,
			"Product" : "test",
			"Description" : "test",
			"Price" : 9.99,
			"Quantity" : 0
		}
	]
}
