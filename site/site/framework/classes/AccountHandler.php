<?php

class AccountHandler extends BasePage
{
	public function Run()
	{
		$this->AddJavascript("/framework/js/account.js");
		$this->BeginPage();
		
		switch ($this->Action)
		{
			case "auth":
				$this->Authenticate();
				break;
			case "login":
				$this->AccountLogin();
				break;
			case "logout":
				$this->AccountLogout();
				break;
			case "create":
				$this->AuthAccount();
				break;
			case "new":
				$this->CreateAccount();
				break;
			case "order":
				$this->DisplayOrder();
				break;
			case "info":
				$this->ShowPersonalInfo();
				break;
			case "infoupdate":
				$this->SavePersonalInfo();
				break;
			case "account":
				$this->ShowLoginInfo();
				break;
			case "accountupdate":
				$this->SaveLoginInfo();
				break;
			case "changepassword":
				$this->SavePassword();
				break;
			case "billaddress":
				$this->ShowAddress("B");
				break;
			case "billupdate":
				$this->SaveAddress("B");
				break;
			case "shipaddress":
				$this->ShowAddress("S");
				break;
			case "shipupdate":
				$this->SaveAddress("S");
				break;
			case "payment":
				$this->ShowPayment();
				break;
			case "payupdate":
				$this->SavePayment();
				break;
			case "history":
				$this->ShowHistory();
				break;
			case "library":
				$this->ShowLibrary();
				break;
			case "setpassword":
				$this->SetPassword();
				break;
			case "recovery":
				$this->RecoverPassword();
				break;
			case "reviews":
			default:
				$this->ViewAccount();
				break;
		}
		
		$this->EndPage();
	}
	
	
	
	private function SetPassword()
	{
		echo $this->Account->SetNewPassword($this->PageVariables["id"]);
	}
	
	
	private function RecoverPassword()
	{
		echo $this->Account->RecoverPassword();
	}
	
	
	private function ShowLibrary()
	{
		echo $this->Account->ShowLibrary();
	}
	
	
	private function ShowHistory()
	{
		echo $this->Account->ShowHistory();
	}
	
	
	private function SavePayment()
	{
		
	}
	
	
	private function ShowPayment()
	{
		echo $this->Account->ShowCreditCards();
	}
	
	
	private function SaveAddress($addrtype)
	{
		$contact = trim($this->PageVariables["info_contact"]);
		$address1 = trim($this->PageVariables["info_address1"]);
		$address2 = trim($this->PageVariables["info_address2"]);
		$city = trim($this->PageVariables["info_city"]);
		$state = trim($this->PageVariables["info_state"]);
		$zip = trim($this->PageVariables["info_zip"]);
		$phone = trim($this->PageVariables["info_phone"]);
		$error = "";
		
		if ($contact == "")
			$error = "Contact name is required";
		if ($address1 == "")
			$error = "Address is required";
		if ($city == "")
			$error = "City is required";
		if ($state == "")
			$error = "State is required";
		if ($phone == "")
			$error = "Phone Number is required";
		
		if ($error != "")
		{
			$this->Account->LastMessage = $error;
			$this->Redirect("/profile/".($addrtype == "B" ? "billing" : "shipping"));
		}
		
		$this->Account->LastMessage = "saved";
		$this->Account->SaveAddress($addrtype, $contact, $address1, $address2, $city, $state, $zip, $phone);
		$this->Redirect("/profile/".($addrtype == "B" ? "billing" : "shipping"));
	}
	
	private function ShowAddress($addrtype)
	{
		echo $this->Account->UpdateAddress($addrtype);
	}
	
	private function SavePersonalInfo()
	{
		$firstname = trim($this->PageVariables["info_firstname"]);
		$lastname = trim($this->PageVariables["info_lastname"]);
		$ssn = $this->PageVariables["info_ssn"];
		$birthday = $this->PageVariables["info_birthday"];
		
		$ssn = $ssn == "" ? "" : Utilities::EncryptValue("account", $ssn);
		$birthday = $birthday == "" ? "" : Utilities::EncryptValue("account", $birthday);

		if ($firstname != "" && $lastname != "")
		{
			$this->Account->SavePersonalInfo($firstname, $lastname, $ssn, $birthday);
			$this->Account->LastMessage = "saved";
			$this->Redirect("/profile/info");
		}
		
		if ($firstname == "")
		{
			$this->Account->LastMessage = "Please enter your first name.";
			$this->Redirect("/profile/info");
		}
		
		if ($lastname == "")
		{
			$this->Account->LastMessage = "Please enter your last name.";
			$this->Redirect("/profile/info");
		}
		
		
		$this->Account->LastMessage = "There was a problem saving your information.";
		$this->Redirect("/profile/info");
	}
	
	private function SaveLoginInfo()
	{
		if (filter_var($this->PageVariables["info_email"], FILTER_VALIDATE_EMAIL))
		{
			$this->Account->SaveEmail(Utilities::EncryptValue("account", $this->PageVariables["info_email"]));
			$this->Account->LastMessage = "l:saved";
		}
		else
		{
			$this->Account->LastMessage = "l:Please enter a valid e-mail address";
		}
		
		$this->Redirect("/profile/account");
	}
	
	private function SavePassword()
	{
		$pass1 = $this->PageVariables["info_password"];
		$pass2 = $this->PageVariables["info_passwordc"];
		
		if (strlen($pass1) >= 8 && $pass1 == $pass2)
		{
			$this->Account->SavePassword($pass1);
			$this->Account->LastMessage = "p:saved";
			$this->Redirect("/profile/account");
		}
		
		if (strlen($pass1) < 8)
		{
			$this->Account->LastMessage = "p:Your password must be at least 8 chanacters.";
			$this->Redirect("/profile/account");
		}
		
		if ($pass1 != $pass2)
		{
			$this->Account->LastMessage = "p:Your passwords don't match.";
			$this->Redirect("/profile/account");
		}
		
		$this->Account->LastMessage = "p:There was a problem saving your password.";
		$this->Redirect("/profile/account");
	}
	
	private function ShowLoginInfo()
	{
		echo $this->Account->UpdateLoginInfo();
	}
	
	private function ShowPersonalInfo()
	{
		echo $this->Account->UpdatePersonalInfo();
	}
	
	private function DisplayOrder()
	{
		$order = new Order();
		$order->GetOrder($this->PageVariables["ordid"]);
		echo $this->Account->PageWrapper("Order Details - ".$order->OrderID, $order->DisplayOrder());
	}
	
	private function Authenticate()
	{
		$show = !($this->Account->LoggedIn());
		$error = "";
		
		try
		{
			$remember = false;
			if (isset($this->PageVariables["login_remember"]))
			{
				if ($this->PageVariables["login_remember"] == "me")
					$remember = true;
			}
			
			$this->Account->Login($this->PageVariables["login_username"], $this->PageVariables["login_password"], $remember);
		}
		catch (exception $e)
		{
			$error = $e->getMessage();
		}
		
		if (!isBlank($error))
		{
			echo $this->Account->LoginView($error);
		}
		else
		{
			if ($this->Cart->Checkout)
				$this->Redirect("/shop/cart/billing");
			else
				$this->Redirect(isBlank($this->Account->LastPage) ? "/" : $this->Account->LastPage);
		}
	}
	
	
	
	private function AccountLogin()
	{
		$show = !($this->Account->LoggedIn());
		
		if ($show)
		{
			if (isset($_SERVER["HTTP_REFERER"]) && !strstr($_SERVER["HTTP_REFERER"], "reset-password"))
				$this->Account->LastPage = $_SERVER["HTTP_REFERER"];
			else
				$this->Account->LastPage = "";
			
			echo $this->Account->LoginView();
		}
		else
			$this->Redirect("/");
	}
	
	
	
	private function AccountLogout()
	{
		$this->Account->Logout();
		$this->Redirect("/");
	}
	
	
	
	private function AuthAccount()
	{
		$error = "";
		
		try
		{
			$this->Account->Create($this->PageVariables["login_username"], $this->PageVariables["login_password"], $this->PageVariables["login_password_two"], $this->PageVariables["login_firstname"], $this->PageVariables["login_lastname"]);
		}
		catch (exception $e)
		{
			$error = $e->getMessage();
		}
		
		if (!isBlank($error))
		{
			echo $this->Account->CreateView($error, $this->PageVariables["login_username"], $this->PageVariables["login_firstname"], $this->PageVariables["login_lastname"]);
		}
		else
		{
			if ($this->Cart->Checkout)
				$this->Redirect("/shop/cart/billing");
			else
				$this->Redirect(isBlank($this->Account->LastPage) ? "/" : $this->Account->LastPage);
		}
	}
	
	
	
	private function CreateAccount()
	{
		$show = !($this->Account->LoggedIn());
		
		if ($show)
		{
			$this->Account->LastPage = isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : "/";
			
			echo $this->Account->CreateView();
		}
		else
			$this->Redirect("/");
	}
	
	
	
	private function ViewAccount()
	{
		echo $this->Account->AccountHome();
	}
}




?>