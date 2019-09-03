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
	public $InvoiceID = "";

	private $OrderApi = "";
	private $CustomerApi = "";
	private $_settings;
	
	public function __construct()
	{
		global $orderapi;
		global $customerapi;

		$this->OrderApi = $orderapi;
		$this->CustomerApi = $customerapi;

		$this->ShippingAddress = new Address();
		
		$this->_settings = new ApplicationSettings();
	}
	
	public function GetOrder($ordid)
	{
		if (!isBlank($ordid))
		{
			$rr = new RestRunner();
			$row = $rr->Get($this->OrderApi."/order/".$ordid);
			if (count($row) >= 1)
			{
				unset($this->Items);
				$this->ShippingAddress = new Address();

				$this->OrderID = $row[0]->OrderId;
				$this->CustomerID = $row[0]->CustomerId;
				$this->OrderDate = $row[0]->OrderDate;
				$this->TaxAmount = $row[0]->TaxAmount;
				$this->ShippingAmount = $row[0]->ShippingAmount;
				$this->SubtotalAmount = $row[0]->SubtotalAmount;
				$this->TotalAmount = $row[0]->TotalAmount;
				$this->Status = $row[0]->Status;
				$this->Comments = $row[0]->Comments == "." ? "" : $row[0]->Comments;
				$this->InvoiceID = $row[0]->InvoiceId;
				$this->ShippingAddress->Contact = $row[0]->ShippingAddress->Contact;
				$this->ShippingAddress->Address1 = $row[0]->ShippingAddress->Address1;
				$this->ShippingAddress->Address2 = $row[0]->ShippingAddress->Address2;
				$this->ShippingAddress->City = $row[0]->ShippingAddress->City;
				$this->ShippingAddress->State = $row[0]->ShippingAddress->State;
				$this->ShippingAddress->Zip = $row[0]->ShippingAddress->Zip;
				$this->ShippingAddress->Phone = $row[0]->ShippingAddress->Phone;

				foreach ($row[0]->Items as $item)
				{
					$i = new OrderItem();
					$i->ID = $item->ID;
					$i->LineNumber = $item->LineNumber;
					$i->Product = $item->Product;
					$i->Price = $item->Price;
					$i->Quantity = $item->Quantity;

					$this->Items[] = $i;
				}
			}

			$this->Invoice = new Invoice();
			$this->Invoice->GetInvoice($this->InvoiceID);
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
				$this->OrderID = $this->GenerateOrderID();
			}
		}

		$request = $this->OrderApi."/order";
		$rr = new RestRunner();
		$rr->SetHeader("Content-Type", "application/json");
		$retval = $rr->Post($request, $this->OutputJson());
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
		// $out .= "											<img src=\"/framework/img/wp-mail-header.png\" width=\"279\" height=\"69\" style=\"display:block;margin:0\" border=\"0\" alt=\"Java Perks\">\n";
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
			$p->GetProduct($item->PID, true);
			$out .= "															<tr>\n";
			$out .= "																<td width=\"15\" style=\"padding-top:18px\"></td>\n";
			$out .= "																<td width=\"410\" align=\"left\" valign=\"top\" style=\"padding-top:18px\"><div style=\"font-family:Helvetica, sans-serif, Arial, Verdana;color:#000000;font-size:12px;line-height:1.25em;font-weight:bold\">".$item->Product."</div></td>\n";
			$out .= "																<td width=\"76\" align=\"right\" valign=\"top\" style=\"padding-top:18px\"><div style=\"font-family: Helvetica, sans-serif, Arial, Verdana;color:#797979;font-size:11px;line-height:1.37em;padding-left:5px\">".money_format("%.2n", $item->Price)."</div></td>\n";
			$out .= "																<td width=\"56\" align=\"right\" valign=\"top\" style=\"padding-top:18px\"><div style=\"font-family: Helvetica, sans-serif, Arial, Verdana;color:#797979;font-size:11px;line-height:1.37em;padding-left:5px\">".$item->Quantity."</div></td>\n";
			//$out .= "																<td width=\"103\" align=\"right\" valign=\"top\" style=\"padding-top:18px\"><div style=\"font-family: Helvetica, sans-serif, Arial, Verdana;color:#000000;font-size:12px;line-height:1.25em;padding-left:5px\">".money_format("%.2n", $item->ExtendedPrice())."</div></td>\n";
			$out .= "															</tr>\n";
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
		$out .= "																											".$this->Invoice->BillingAddress->DisplayFormatted()."\n";
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
			$items .= $item->OutputJson() . ",";
		}

		if ($items != "")
			$items = substr($items, 0, -1);

		$out .="{";
		$out .="	\"OrderId\": \"".$this->OrderID."\", ";
		$out .="	\"CustomerId\": \"".$this->CustomerID."\", ";
		$out .="	\"InvoiceId\": ".$this->InvoiceID.", ";
		$out .="	\"OrderDate\": \"".date("Y-m-d\TH:i:s-05:00")."\", ";
		$out .="	\"SubtotalAmount\": \"".$this->SubtotalAmount."\", ";
		$out .="	\"ShippingAmount\": \"".$this->ShippingAmount."\", ";
		$out .="	\"TaxAmount\": \"".$this->TaxAmount."\", ";
		$out .="	\"TotalAmount\": \"".$this->TotalAmount."\", ";
		$out .="	\"Comments\": \"".(isBlank($this->Comments) ? "." : $this->Comments)."\", ";
		$out .="	\"ShippingAddress\": { ";
		$out .="		\"Contact\": \"".$this->ShippingAddress->Contact."\", ";
		$out .="		\"Address1\": \"".$this->ShippingAddress->Address1."\", ";
		$out .="		\"Address2\": \"".(isBlank($this->ShippingAddress->Address2) ? "." : $this->ShippingAddress->Address2)."\", ";
		$out .="		\"City\": \"".$this->ShippingAddress->City."\", ";
		$out .="		\"State\": \"".$this->ShippingAddress->State."\", ";
		$out .="		\"Zip\": \"".$this->ShippingAddress->Zip."\", ";
		$out .="		\"Phone\": \"".$this->ShippingAddress->Phone."\" ";
		$out .="    },";
		$out .="	\"Status\": \"Paid\", ";
		$out .="	\"Items\": [";
		$out .= $items;
		$out .="	] ";
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
	
	public function OutputJson()
	{
		$out = "";

		$out .= "{";
		$out .= "	\"ID\" : \"".$this->ID."\", ";
		$out .= "	\"LineNumber\" : \"".$this->LineNumber."\", ";
		$out .= "	\"Product\" : \"".$this->Product."\", ";
		$out .= "	\"Price\" : \"".$this->Price."\", ";
		$out .= "	\"Quantity\" : \"".$this->Quantity."\" ";
		$out .= "}";

		return $out;
	}
}






?>
