<?php

/*
 *  LANDINGAJAX CLASS
 */

class LandingAjax extends AjaxHandler
{
	public function Process()
	{
		switch ($this->Action)
		{
			case "checkstate":
				$this->CheckUserState();
				break;
			case "cartcount":
				$this->CartCount();
				break;
			case "getdescr":
				$this->GetDescription();
				break;
			case "ebooks":
				$this->DisplayEbooks();
				break;
			case "updatecart":
				$this->UpdateItems();
				break;
			case "addtocart":
				$this->AddItem();
				break;
			case "moreinfo":
				$this->ShowMoreInfo();
				break;
		}
		
		$this->Complete();
	}
	
	public function CheckUserState()
	{
		$pids = explode(",", $this->AjaxVariables["books"]);
		$incomplete = false;
		unset($this->Cart->LandingFreeItems);
		
		foreach($pids as $pid)
		{
			$this->Cart->LandingFreeItems[] = new CartItem($pid, 1, false, 0, true, "", false);
		}
		
		$allowed = $this->Cart->Count(true) * 2;
		$selected = count($this->Cart->LandingFreeItems);
		
		if ($selected < $allowed)
			$incomplete = true;
		
		if (count($this->Cart->LandingFreeItems) >= 30)
			$incomplete = false;
		
		$this->Cart->Checkout = true;
		$this->Data = array("LoggedIn" => $this->Account->LoggedIn(), "Incomplete" => $incomplete);
	}
	
	public function CartCount()
	{
		$this->Data = array("CartCount" => $this->Cart->Count(true));
	}
	
	public function GetDescription()
	{
		$p = new Product($this->_db);
		$p->GetProduct($this->AjaxVariables["pid"]);
		$this->Data = array("Product" => $p->ProductName, "Description" => $p->Description);
	}
	
	public function UpdateItems()
	{
		foreach ($this->Cart->LandingItems as &$item)
		{
			$item->Quantity = $this->AjaxVariables["cart_item"][$item->PID];
		}
		$this->Cart->CleanCart(true);
		$this->Body = $this->Cart->ShowLandingCart();
	}
	
	public function AddItem()
	{
		$p = new Product($this->_db);
		$p->GetProduct($this->AjaxVariables["pid"]);
		
		try
		{
			$this->Cart->AddItem($this->AjaxVariables["pid"], 1, true);
		}
		catch (Exception $e)
		{
			$this->Cart->LastError = $e->getMessage();
		}
		$this->Body = $this->Cart->ShowLandingCart();
	}
	
	public function ShowMoreInfo()
	{
		$out = "";
		$p = new Product($this->_db);
		$p->GetProduct($this->AjaxVariables["pid"]);
		$p->CalculateValues();
		
		$out .= "	<div class=\"order-signin\" style=\"height:400px;\">\n";
		$out .= "		<img src=\"/framework/img/close.png\" style=\"height:32px; width:32px; position:absolute; right:-12px; top:-12px; cursor:pointer;\" onclick=\"unpopWindow();\" />\n";
		$out .= "		<div class=\"order-summary-heading\">".$p->ProductName."</div>\n";
		$out .= "		<div style=\"padding:10px; height:338px; font-size: 16px; overflow-y:scroll; overflow:scroll; overflow-x:hidden;overflow:-moz-scrollbars-vertical;\">";
		$out .= "			<div><img src=\"".$p->ImageURL()."\" alt=\"".$p->ProductName."\" border=\"0\" style=\"float:left; max-height:130px; margin:7px;\" /></div>";
		$out .= "			<div>by ".$p->Manufacturer."</div>";
		$out .= "			<div>Price: $".money_format("%.2n", ($p->CalculatedDiscount > 0 ? $p->CalculatedPrice : $p->Price))."</div>\n";
		$out .= "			<div style=\"clear:both;\">&nbsp;</div>";
		$out .= "			<div style=\"font-weight:bold; border-bottom:1px dotted #999999;padding-bottom:10px;margin-bottom:10px;\">Description</div>";
		$out .= "			<div>".$p->Description."</div>";
		$out .= "		</div>";
		$out .= "	</div>\n";
		
		$this->Body = $out;
	}
}





?>