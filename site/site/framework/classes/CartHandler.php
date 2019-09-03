<?php

class CartHandler extends BasePage
{
	private $_urltag = "shop";

	public function __construct()
	{
		$this->Initialize();
	}
	
	public function Run()
	{

		$this->BeginPage();
		
		switch ($this->Action)
		{
			// case "order":
			// 	$this->DisplayOrder();
			// 	break;
			case "continue":
				$this->ProcessCart();
				break;
			case "confirm":
				$this->ShowPayment();
				break;
			case "shipping":
				$this->ShowShipping();
				break;
			case "billing":
				$this->ShowBilling();
				break;
			// case "update":
			// 	$this->UpdateCart();
			// 	break;
			// case "empty":
			// 	$this->EmptyCart();
			// 	break;
			case "added":
				$this->ItemAdded();
				break;
			case "add":
				$this->AddItem();
				break;
			// case "update":
			// 	$this->UpdateItem();
			// 	break;
			case "remove":
				$this->DeleteItem();
				break;
			default:
				$this->ViewCart();
				break;
		}
		
		$this->EndPage();
	}

	// private function DisplayOrder()
	// {
	// 	$order = new Order($this->_db);
	// 	$order->GetOrder($this->PageVariables["ordid"]);
	// 	echo "<div class=\"content\">".$order->DisplayOrder()."</div>";
	// }
	
	private function ProcessCart()
	{
		if (!isset($this->PageVariables["command"]))
		{
			echo "<div class=\"content\">Sorry. This isn't yet implemented.</div>";
			return;
		}
		
		switch ($this->PageVariables["command"])
		{
			case "placeorder":
				$this->PlaceOrder();
				break;
			case "shipping":
				$this->SaveShipping();
				break;
			case "address";
				$this->SaveAddress();
				break;
			case "review":
				$this->UpdateCart();
				break;
			default:
				echo "<div class=\"content\">Sorry. This isn't yet implemented.</div>";
				break;
		}
	}
	
	private function PlaceOrder()
	{
		$ordid = "";
		
		if (isset($this->Cart))
		{
			$cc = new CreditCard();

			$cc->CardName = trim($this->PageVariables["pay_new_cardname"]);
			$cc->CardType = trim($this->PageVariables["pay_new_cardtype"]);
			$cc->CardNumber = str_replace(array("-", " ", "."), "", $this->PageVariables["pay_new_cardnum"]);
			$cc->CVV = trim($this->PageVariables["pay_new_cvvnum"]);
			$cc->ExpirationMonth = intval(trim($this->PageVariables["pay_new_expmonth"]));
			$cc->ExpirationYear = intval(trim($this->PageVariables["pay_new_expyear"]));
			// $this->Cart->SaveCard = isset($this->PageVariables["pay_new_save"]) ? true : false;
			$this->Cart->PayType = trim($this->PageVariables["pay_type"]);
			$this->Cart->LastError = "";
			$this->Cart->CreditCard = $cc;
			
			if (isBlank($this->Cart->PayType))
			{
				$this->Cart->LastError = "Please select a method of payment";
				$this->Redirect("/shop/cart/confirm");
			}
			
			if (strtoupper($this->Cart->PayType) == "NEW")
			{
				if (isBlank($this->Cart->CreditCard->CardName))
				{
					$this->Cart->LastError = "Please enter the name as it appears on your credit card.";
					$this->Redirect("/shop/cart/confirm");
				}
				
				if (isBlank($this->Cart->CreditCard->CardNumber))
				{
					$this->Cart->LastError = "Please enter the credit card number";
					$this->Redirect("/shop/cart/confirm");
				}
				
				if (isBlank($this->Cart->CreditCard->CVV))
				{
					$this->Cart->LastError = "Please enter the CVV code, typically located on the back of the card.";
					$this->Redirect("/shop/cart/confirm");
				}
				
				if (!$this->Cart->CreditCard->IsValidCard())
				{
					$this->Cart->LastError = "The credit card is not valid. Enter numbers only&mdash;no spaces or dashes.";
					$this->Redirect("/shop/cart/confirm");
				}
				
				if ($this->Cart->CreditCard->IsExpired())
				{
					$this->Cart->LastError = "The credit card is expired. Please use a current credit card.";
					$this->Redirect("/shop/cart/confirm");
				}
				
			}
			
			if (!isset($this->PageVariables["cart_agree"]))
			{
				$this->Cart->LastError = "You must agree to the terms and conditions to continue with your order.";
				$this->Redirect("/shop/cart/confirm");
			}
			$this->Cart->AgreeTerms = true;
			
			$this->Cart->PlaceOrder();
			$ordid = $this->Cart->Order->OrderID;
			$this->Cart->StartOver();
			$this->Redirect("/profile/order/".$ordid);
		}
		else
		{
			$this->Redirect("/shop/cart/view");
		}
	}
	
	private function SaveShipping()
	{
		if (isset($this->Cart))
		{
			$this->Cart->ShippingService = $this->Cart->ShipMethodList[intval($this->PageVariables["cart_ship_method"])][0];
			$this->Cart->ShippingAmount = floatval($this->Cart->ShipMethodList[intval($this->PageVariables["cart_ship_method"])][1]);
			$this->Cart->Checkout = true;

			$this->Redirect("/".$this->_urltag."/cart/confirm");
		}
		else
			$this->Redirect("/shop/cart/view");
	}
	
	private function SaveAddress()
	{
		if (isset($this->Cart))
		{
			$this->Cart->Checkout = true;
			// $this->Cart->BillingAddress->Contact = trim($this->PageVariables["cart_b_contact"]);
			$this->Cart->BillingAddress->Address1 = trim($this->PageVariables["cart_b_address1"]);
			$this->Cart->BillingAddress->Address2 = trim($this->PageVariables["cart_b_address2"]);
			$this->Cart->BillingAddress->City = trim($this->PageVariables["cart_b_city"]);
			$this->Cart->BillingAddress->State = trim($this->PageVariables["cart_b_state"]);
			$this->Cart->BillingAddress->Zip = trim($this->PageVariables["cart_b_zip"]);
			$this->Cart->BillingAddress->Phone = trim($this->PageVariables["cart_b_phone"]);
	
			// $this->Cart->ShippingAddress->Contact = trim($this->PageVariables["cart_s_contact"]);
			$this->Cart->ShippingAddress->Address1 = trim($this->PageVariables["cart_s_address1"]);
			$this->Cart->ShippingAddress->Address2 = trim($this->PageVariables["cart_s_address2"]);
			$this->Cart->ShippingAddress->City = trim($this->PageVariables["cart_s_city"]);
			$this->Cart->ShippingAddress->State = trim($this->PageVariables["cart_s_state"]);
			$this->Cart->ShippingAddress->Zip = trim($this->PageVariables["cart_s_zip"]);
			$this->Cart->ShippingAddress->Phone = trim($this->PageVariables["cart_s_phone"]);
			
			$this->Cart->ShippingAddress->SaveAddresses = false;
			
			if (trim($this->PageVariables["cart_s_phone"]) == "") $this->Cart->LastError = "Shipping Address Error: The billing phone number is missing";
			if (trim($this->PageVariables["cart_s_zip"]) == "") $this->Cart->LastError = "Shipping Address Error: The zip/postal code is missing";
			if (trim($this->PageVariables["cart_s_city"]) == "") $this->Cart->LastError = "Shipping Address Error: The city name is missing";
			if (trim($this->PageVariables["cart_s_address1"]) == "") $this->Cart->LastError = "Shipping Address Error: The street address is missing";
			// if (trim($this->PageVariables["cart_s_contact"]) == "") $this->Cart->LastError = "Shipping Address Error: The contact name is missing";
			if (trim($this->PageVariables["cart_s_state"]) == "") $this->Cart->LastError = "Shipping Address Error: The state is missing";
			
			if (trim($this->PageVariables["cart_b_phone"]) == "") $this->Cart->LastError = "Billing Address Error: The billing phone number is missing";
			if (trim($this->PageVariables["cart_b_zip"]) == "") $this->Cart->LastError = "Billing Address Error: The zip/postal code is missing";
			if (trim($this->PageVariables["cart_b_city"]) == "") $this->Cart->LastError = "Billing Address Error: The city name is missing";
			if (trim($this->PageVariables["cart_b_address1"]) == "") $this->Cart->LastError = "Billing Address Error: The street address is missing";
			if (trim($this->PageVariables["cart_b_state"]) == "") $this->Cart->LastError = "Billing Address Error: The state is missing";
			
			if ($this->Cart->LastError != "")
				$this->Redirect("/".$this->_urltag."/cart/billing");
			else
				$this->Redirect("/".$this->_urltag."/cart/shipping");
		}
		else
			$this->Redirect("/shop/cart/view");
	}
	
	private function UpdateCart()
	{
		if (!isset($this->PageVariables["cart_item"]))
		{
			throw new Exception('There was an error updating the shopping cart. [Error 1002]');
		}
		
		foreach ($this->Cart->Items as &$item)
		{
			$this->Cart->AddItem($item->PID, $this->PageVariables["cart_item"][$item->PID]);
		}
		$this->Cart->CleanCart();
		
		$cart = false;
		if (isset($this->PageVariables["cart_process"]) && $this->PageVariables["cart_process"] == "checkout") $cart = true;
		
		if ($cart)
			$this->Redirect("/".$this->_urltag."/cart/billing");
		else
		{
			if (isset($this->PageVariables["cart_pid"]))
			{
				if ($this->Cart->Contains($this->PageVariables["cart_pid"]))
				{
					$p = new Product($this->_db);
					$p->GetProduct($this->PageVariables["cart_pid"]);
					$this->Redirect("/shop/cart/add/".$p->PID."/".$p->Identifier);
				}
				else
					$this->Redirect("/shop/cart/view");
			}
			else
				$this->Redirect("/shop/cart/view");
		}
	}
	
	private function ShowPayment()
	{
		if (!$this->Cart->Checkout)
			$this->Redirect("/shop/cart/view");
		else
			echo $this->Cart->ConfirmPayment($this->_urltag);
	}
	
	private function ShowShipping()
	{
		if (!$this->Cart->Checkout)
			$this->Redirect("/shop/cart/view");
		else
		{
			$out = $this->Cart->ShippingMethod($this->_urltag);
			if ($out == "")
			{
				$this->Cart->LastError = "<div>There are no shipping services available for your address. Please check your address again, enter a different address, or call 800-421-7323 for additional options.</div>";
				$this->Redirect("/".$this->_urltag."/cart/billing");
			}
			else
				echo $out;
		}
	}
	
	private function ShowBilling()
	{
		if ($this->Account->LoggedIn())
			echo $this->Cart->BillingInfo($this->_urltag);
		else
		{
			$this->Cart->Checkout = true;
			$this->Redirect("/profile/login");
		}
	}
	
	// private function EmptyCart()
	// {
	// 	unset($this->Cart->Items);
	// }
	
	private function ItemAdded()
	{
		echo $this->Cart->JustAdded($this->PageVariables["pid"]);
	}
	
	private function AddItem()
	{
		if (!isset($this->PageVariables["cart_pid"]))
		{
			throw new Exception('There was an error while trying to add this product to your cart [Error: 1001].');
		}
		
		$p = new Product();
		$p->GetProduct($this->PageVariables["cart_pid"]);

		try
		{
			$this->Cart->AddItem($this->PageVariables["cart_pid"], $this->PageVariables["cart_qty"]);
			$this->Redirect("/shop/cart/add/".$p->PID."/".$p->Identifier);
		}
		catch (Exception $e)
		{
			$this->Cart->LastError = $e->getMessage();
			//$this->Redirect($p->Permalink());
		}
	}
	
	private function DeleteItem()
	{
		$this->Cart->DeleteItem($this->PageVariables["pid"]);
		$this->Redirect("/shop/cart/view");
	}
	
	private function ViewCart()
	{
		$this->Cart->ShippingAmount = 0;
		$this->Cart->SubtotalAmount = 0;
		$this->Cart->TaxAmount = 0;
		$this->Cart->TotalAmount = 0;
		$this->Cart->ShippingService = "FREE";
		
		if ($this->Cart->Count() > 0)
		{
			$this->Cart->ShippingAmount = 0;
			$this->Cart->SubtotalAmount = 0;
			$this->Cart->TaxAmount = 0;
			$this->Cart->TotalAmount = 0;
			$this->Cart->ShippingService = "FREE";
			echo $this->Cart->ReviewCart();
		}
		else
			echo $this->Cart->CartEmpty();
	}
}




?>