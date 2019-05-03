<?php

class CartAjax extends AjaxHandler
{
	
	public function Process()
	{
		switch ($this->Action)
		{
			case "getstates":
				$this->GetStates();
				break;
			case "placeorder":
				$this->PlaceOrderMessage();
				break;
			case "authlogin":
				$this->AuthenticateLogin();
				break;
			case "newaccount":
				$this->CreateNewAccount();
				break;
			case "loggedin":
				$this->IsLoggedIn();
				break;
				
		}
		
		$this->Complete();
	}
	
	private function GetStates()
	{
		$val = $this->Cart->GetStates($this->AjaxVariables["country"], $this->AjaxVariables["state"]);
		$this->Data = $val;
	}
		
	private function PlaceOrderMessage()
	{
		$this->Body = $this->Cart->PleaseWait();
	}
	
	private function CreateNewAccount()
	{
		try
		{
			$this->Account->Create($this->AjaxVariables["email"], $this->AjaxVariables["password"], $this->AjaxVariables["passwordc"], $this->AjaxVariables["firstname"], $this->AjaxVariables["lastname"]);
			$this->Data["Authenticated"] = true;
			$this->Error = "";
		}
		catch (exception $e)
		{
			$this->Data["Authenticated"] = false;
			$this->Error = $e->getMessage();
		}
	}
	
	
	
	private function AuthenticateLogin()
	{
		try
		{
			$this->Account->Login($this->AjaxVariables["username"], $this->AjaxVariables["password"]);
			$this->Data["Authenticated"] = true;
			$this->Error = "";
		}
		catch (Exception $e)
		{
			$this->Data["Authenticated"] = false;
			$this->Error = $e->getMessage();
		}
	}
	
	
	
	private function IsLoggedIn()
	{
		$this->Data["LoggedIn"] = $this->Account->LoggedIn();
		$this->Body = $this->Cart->CartLoginView();
	}
}





?>