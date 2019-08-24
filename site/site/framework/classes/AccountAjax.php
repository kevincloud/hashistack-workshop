<?php

/*
 *  _BLANK_AJAX CLASS
 */

class AccountAjax extends AjaxHandler
{
	public function Process()
	{
		switch ($this->Action)
		{
			case "getstates":
				$this->GetStates();
				break;
			case "savepassword";
				$this->SaveNewPassword();
				break;
			case "resetpassword":
				$this->ResetPassword();
				break;
			case "deletecard":
				$this->DeleteCard();
				break;
			case "savenewcard":
				$this->SaveNewCard();
				break;
			case "newcard":
				$this->ShowNewCard();
				break;
			case "changeavatar":
				$this->ChangeAvatar();
				break;
		}
		
		$this->Complete();
	}
	
	
	private function GetStates()
	{
		$val = $this->Account->GetStates($this->AjaxVariables["country"], $this->AjaxVariables["state"]);
		$this->Data = $val;
	}
	
	
	private function SaveNewPassword()
	{
		$val = $this->Account->SaveNewPassword($this->AjaxVariables["id"], $this->AjaxVariables["pwd"], $this->AjaxVariables["pwdc"]);
		$this->Error = $val["Error"];
		$this->Body = $val["Body"];
	}
	
	
	private function ResetPassword()
	{
		$val = $this->Account->ResetPassword($this->AjaxVariables["email"]);
		$this->Error = $val["Error"];
		$this->Body = $val["Body"];
	}
	
	
	private function DeleteCard()
	{
		$cardid = $this->AjaxVariables["id"];
		$this->Account->DeleteCreditCard($cardid);
	}
	
	
	private function SaveNewCard()
	{
		$CardName = trim($this->AjaxVariables["cardname"]);
		$CardType = trim($this->AjaxVariables["cardtype"]);
		$CardNumber = str_replace(array("-", " ", "."), "", $this->AjaxVariables["cardnum"]);
		$CardCVV = trim($this->AjaxVariables["cvvnum"]);
		$CardExpMonth = intval(trim($this->AjaxVariables["expmonth"]));
		$CardExpYear = intval(trim($this->AjaxVariables["expyear"]));
		
		if (isBlank($CardName))
		{
			$this->Error = "Please enter the name as it appears on your credit card.";
			return;
		}
		
		if (isBlank($CardNumber))
		{
			$this->Error = "Please enter the credit card number";
			return;
		}
		
		if (isBlank($CardCVV))
		{
			$this->Error = "Please enter the CVV code, typically located on the back of the card.";
			return;
		}
		
		$cc = new CreditCard();
		$cc->CardType = $CardType;
		$cc->CardName = $CardName;
		$cc->CardNumber = $CardNumber;
		$cc->ExpirationMonth = intval($CardExpMonth);
		$cc->ExpirationYear = intval($CardExpYear);
		
		if (!$cc->IsValidCard())
		{
			$this->Error = "The credit card is not valid. Enter numbers only&mdash;no spaces or dashes.";
			return;
		}
		
		if ($cc->IsExpired())
		{
			$this->Error = "The credit card is expired. Please use a current credit card.";
			return;
		}

		$CardNumber = Utilities::EncryptValue("payment", $CardNumber);
		$CardCVV = Utilities::EncryptValue("payment", $CardCVV);
		
		$this->Account->SaveCreditCard($CardName, $CardType, $CardNumber, $CardCVV, $CardExpMonth, $CardExpYear);
	}
	
	
	private function ShowNewCard()
	{
		$this->Body = $this->Account->AddNewCard();
	}
	
	
	private function ChangeAvatar()
	{
		$this->Body = $this->Account->NotImplemented("Change Avatar");
	}
}





?>