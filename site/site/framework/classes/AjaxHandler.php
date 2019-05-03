<?php

abstract class AjaxHandler
{
	protected $Cart;
	protected $Account;
	protected $AjaxVariables = array();
	protected $Action = "";
	protected $Body = "";
	protected $Error = "";
	protected $Message = "";
	protected $Data = NULL;

	// ***INLINESQL***
	// protected $_db;
	
	// public function __construct($db)
	// {
	// 	$this->_db = $db;
	// 	$this->Initialize();
	// }
	
	public function __construct()
	{
		$this->Initialize();
	}
	
	protected function Initialize()
	{
		$this->Action = "";
		
		foreach ($_REQUEST as $key => $value)
		{
			if ($key == "action")
			{
				if ($this->Action == "")
					$this->Action = strtolower(trim($value));
			}
			else
			{
				if (!array_key_exists($key, $this->AjaxVariables))
				{
					$this->AjaxVariables[$key] = $value;
				}
			}
		}
		
		if (isset($_SESSION["__account__"]))
			$this->Account = &$_SESSION["__account__"];
		else
			$this->Account = new Account();
		
		if (isset($_SESSION["__cart__"]))
			$this->Cart = &$_SESSION["__cart__"];
		else
			$this->Cart = new ShoppingCart();
	}
	
	abstract protected function Process();
	
	protected function MessageBox($title, $message)
	{
		if ($title == "") $title = "NOT IMPLEMENTED";
		$title = strtoupper($title);
		
		if ($message == "") $message = "This feature is not yet implemented.";
		
		$out = "";
		
		$out .= "	<div class=\"order-signin\" style=\"\">\n";
		$out .= "		<img src=\"/framework/img/close.png\" style=\"height:32px; width:32px; position:absolute; right:-12px; top:-12px; cursor:pointer;\" onclick=\"unpopWindow();\" />\n";
		$out .= "		<div class=\"order-summary-heading\">".$title."</div>\n";
		$out .= "		<div style=\"padding:15px;font-size:16px;\">".$message."</div>\n";
		$out .= "		<div class=\"clearfloat\"></div>\n";
		$out .= "		<div class=\"order-signin-controls\">\n";
		$out .= "			<div class=\"order-summary-continue\"><input class=\"green button\" name=\"close_button\" id=\"close_button\" value=\"Close\" onclick=\"unpopWindow();\" type=\"button\" /></div>\n";
		$out .= "			<div class=\"clearfloat\"></div>\n";
		$out .= "		</div>\n";
		$out .= "		<div class=\"clearfloat\"></div>\n";
		$out .= "	</div>\n";
		
		return $out;
	}
	
	protected function Complete()
	{
		$retval = array();
		
		// override previous actions
		switch ($this->Action)
		{
			case "messagebox":
				$retval["Body"] = $this->MessageBox($this->AjaxVariables["title"], $this->AjaxVariables["message"]);
				break;
			default:
				$retval["Error"] = $this->Error;
				$retval["Message"] = $this->Message;
				$retval["Body"] = $this->Body;
				$retval["Data"] = $this->Data;
				break;
		}
		
		
		echo json_encode($retval);
	}
}




?>