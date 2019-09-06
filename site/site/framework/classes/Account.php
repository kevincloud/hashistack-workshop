<?php

class Account
{
	public $CustomerID = "";
	public $RowID = "";
	public $FirstName = "";
	public $LastName = "";
	public $Email = "";
	public $SSN = "";
	public $Birthday = "";
	public $LastPage = "";
	public $LastMessage = "";
	public $BillingAddress = NULL;
	public $ShippingAddress = NULL;
	public $CreditCard = NULL;
	
	private $UserID = "";
	
	private $AuthApi;
	private $CustomerApi;
	private $VaultUrl;
	
	/*
	 *	Function: 	__construct
	 *	
	 *	Summary:	Class constructor to initialize the class.
	 *	
	 *	Parameters:	$db (object) - The global database connection 
	 *					to be used with this class' database operations
	 *	
	 *	Returns:	No return value
	 *	
	 */
	public function __construct()
	{
		global $authapi;
		global $customerapi;
		global $vaulturl;

		$this->AuthApi = $authapi;
		$this->CustomerApi = $customerapi;
		$this->VaultUrl = $vaulturl;
	}
	
	/*
	 *	Function: 	GetAccount()
	 *	
	 *	Summary:	Populates the class with the user's information.
	 *				This function does not create a session.
	 *	
	 *	Parameters:	$custid (string) - The customer ID assigned to the user
	 *	
	 *	Returns:	No return value
	 *	
	 */
	public function GetAccount($custid)
	{
		if (!isBlank($custid))
		{
			$request = $this->CustomerApi."/customers/".$custid;
			$rr = new RestRunner();
			$row = $rr->Get($request);
			if ($row)
			{
				$this->UserID = $row->custNo;
				$this->CustomerID = $row->custNo;
				$this->RowID = $row->custId;
				$this->FirstName = $row->firstName;
				$this->LastName = $row->lastName;
				$this->Email = $row->email;
				$this->SSN = $row->ssn;
				$this->Birthday = $row->dob;
				
				foreach ($row->addresses as $address)
				{
					if ($address->addrType == "B")
					{
						$this->BillingAddress = new Address();
						$this->BillingAddress->AddressID = $address->addrId;
						$this->BillingAddress->CustomerID = $this->CustomerID;
						$this->BillingAddress->AddressType = "B";
						$this->BillingAddress->Contact = $address->contact;
						$this->BillingAddress->Address1 = $address->address1;
						$this->BillingAddress->Address2 = $address->address2;
						$this->BillingAddress->City = $address->city;
						$this->BillingAddress->State = $address->state;
						$this->BillingAddress->Zip = $address->zip;
						$this->BillingAddress->Phone = $address->phone;
					} elseif ($address->addrType == "S") {
						$this->ShippingAddress = new Address();
						$this->ShippingAddress->AddressID = $address->addrId;
						$this->ShippingAddress->CustomerID = $this->CustomerID;
						$this->ShippingAddress->AddressType = "S";
						$this->ShippingAddress->Contact = $address->contact;
						$this->ShippingAddress->Address1 = $address->address1;
						$this->ShippingAddress->Address2 = $address->address2;
						$this->ShippingAddress->City = $address->city;
						$this->ShippingAddress->State = $address->state;
						$this->ShippingAddress->Zip = $address->zip;
						$this->ShippingAddress->Phone = $address->phone;
					}
				}
			}
			else
				throw new Exception("The customer account could not be located.");
		}
		else
			throw new Exception("The customer ID was empty.");
	}
	
	/*
	 *	Function: 	Login()
	 *	
	 *	Summary:	Validates the given username and password
	 *				against the database. Upon validation, the
	 *				class will be populated with the user's
	 *				information and the session will be created.
	 *	
	 *	Parameters:	$user (string) - User name or e-mail address of the user
	 *				$pass (string) - The plain text password of the user
	 *	
	 *	Returns:	No return value
	 *	
	 */
	public function Login($user, $pass, $remember=false)
	{
		if (isBlank($user))
			throw new Exception("No username was specified");
		
		if (isBlank($pass))
			throw new Exception("No password was specified");
		
		$custid = "";
		
		$request = $this->AuthApi."/auth";
		$rr = new RestRunner();
		$itemuser = array('Key' => 'username', 'Value' => $user);
		$itempass = array('Key' => 'password', 'Value' => $pass);
		$a = array($itemuser, $itempass);
		$row = $rr->Post($request, $a);

		if ($row->success == false) {
			throw new Exception("The username/password did not match");
		}

		$this->GetAccount($row->customerno);

		$_SESSION["__account__"] = $this;
		if ($remember)
			setcookie("__custid__", $_SESSION["__account__"]->CustomerID, time()+(60*60*24*365), "/", "java-perks.com", false);
	}
	
	/*
	 *	Function: 	Logout()
	 *	
	 *	Summary:	Terminates a user's session
	 *	
	 *	Parameters:	No parameters
	 *	
	 *	Returns:	No return value
	 *	
	 */
	public function Logout()
	{
		if (isset($_SESSION["__account__"]))
			unset($_SESSION["__account__"]);
		setcookie("__custid__", "", time()-(60*60*24*365), "/", "java-perks.com", false);
	}
	
	/*
	 *	Function: 	LoggedIn()
	 *	
	 *	Summary:	Checks to see if this class is populated
	 *	
	 *	Parameters:	No parameters
	 *	
	 *	Returns:	boolean
	 *	
	 */
	public function LoggedIn()
	{
		$retval = false;
		
		if (!isBlank($this->CustomerID))
			$retval = true;
		
		return $retval;
	}
	
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
	
	public function GetStates($country=840, $state="")
	{
		if ($country == "")
			$country = 840;
		
		if ($country == 840 || $country == 36 || $country == 124)
		{
			$cnt = "";
			
			// ***INLINESQL***
			// $sql = "select code from cc_countries where numcode = ".$country;
			// $cnt = $this->_db->get_var($sql);
			
			// $out = "";
			// $retval = array();
			
			// $sql = "select code, state from pw_states where id > 0 and location = ".smartQuote($cnt)." and location is not null order by state";
			// $rs = $this->_db->get_results($sql);
			// foreach ($rs as $row)
			// {
			// 	$ta = array();
			// 	$strsel = false;
			// 	if ($row->code == $state)
			// 		$strsel = true;
				
			// 	$ta["text"] = $row->state;
			// 	$ta["value"] = $row->code;
			// 	$ta["selected"] = $strsel;
			// 	$retval[] = $ta;
			// }
			
			return $retval;
		}
		else
			return "";
	}
	
	/*
	 *	Function: 	LoginView()
	 *	
	 *	Summary:	Displays the login screen
	 *	
	 *	Parameters:	$error - an error message to display
	 *	
	 *	Returns:	No return value
	 *	
	 */
	public function LoginView($error="")
	{
		$out = "";
		$msg = "Welcome Back!";
		if (!isBlank($error))
			$msg = "Oops!<br><span style=\"color:#bb0000;font-size:16px;\">".$error."</span>";
		
		$out .= $this->HideSidebar();
		$out .= "<div style=\"height:100px;\">&nbsp;</div>";
		$out .= "<div class=\"login-screen\">";
		$out .= "	<div class=\"text\">".$msg."</div>";
		$out .= "	<div class=\"text\">&nbsp;</div>";
		$out .= "	<form action=\"/profile/authenticate\" method=\"post\">";
		$out .= "		<div style=\"margin:10px;\"><input type=\"text\" value=\"\" name=\"login_username\" id=\"login_username\" placeholder=\"E-mail\"></div>";
		$out .= "		<div style=\"margin:10px;\"><input type=\"password\" value=\"\" name=\"login_password\" id=\"login_password\" placeholder=\"Password\"></div>";
		$out .= "		<div style=\"margin:10px;\"><input type=\"submit\" value=\"Sign in to your account\"></div>";
		$out .= "		<div style=\"margin:10px;\"><input type=\"hidden\" name=\"action\" value=\"auth\"></div>";
		$out .= "		<div style=\"margin:10px;\"><input type=\"checkbox\" name=\"login_remember\" id=\"login_remember\" value=\"me\"> <label for=\"login_remember\"><strong>Remember me</strong></label></div>";
		$out .= "	</form>";
		$out .= "</div>";
		// $out .= "<div class=\"login-msg\">";
		// $out .= "	<div><a href=\"/profile/new\">Create an account</a></div>";
		// $out .= "</div>";
		// $out .= "<div class=\"login-msg\">";
		// $out .= "	<div><a href=\"/profile/recovery\">Forgot your password?</a></div>";
		// $out .= "</div>";
		$out .= "<div style=\"height:100px;\">&nbsp;</div>";
		
		return $out;
	}
	
	/*
	 *	Function: 	CreateView()
	 *	
	 *	Summary:	Displays the create account screen
	 *	
	 *	Parameters:	$error - an error message to display
	 *	
	 *	Returns:	No return value
	 *	
	 */
	public function CreateView($error="", $user="", $firstname="", $lastname="")
	{
		$out = "";
		$msg = "Create an account";
		if (!isBlank($error))
			$msg = "Oops!<br><span style=\"color:#bb0000;font-size:16px;\">".$error."</span>";
		
		$out .= $this->HideSidebar();
		$out .= "<div style=\"height:100px;\">&nbsp;</div>";
		$out .= "<div class=\"login-screen\">";
		$out .= "	<div class=\"text\">".$msg."</div>";
		$out .= "	<div class=\"text\">&nbsp;</div>";
		$out .= "	<form action=\"/profile/authenticate\" method=\"post\">";
		$out .= "		<div><input type=\"text\" maxlength=\"50\" value=\"".$user."\" name=\"login_username\" id=\"login_username\" placeholder=\"E-mail\"></div>";
		$out .= "		<div><input type=\"text\" maxlength=\"35\" value=\"".$firstname."\" name=\"login_firstname\" id=\"login_firstname\" placeholder=\"First Name\"></div>";
		$out .= "		<div><input type=\"text\" maxlength=\"50\" value=\"".$lastname."\" name=\"login_lastname\" id=\"login_lastname\" placeholder=\"Last Name\"></div>";
		$out .= "		<div><input type=\"password\" value=\"\" maxlength=\"100\" name=\"login_password\" id=\"login_password\" placeholder=\"Password\"></div>";
		$out .= "		<div><input type=\"password\" value=\"\"  maxlength=\"100\" name=\"login_password_two\" id=\"login_password_two\" placeholder=\"Confirm Password\"></div>";
		$out .= "		<div><input type=\"submit\" value=\"Create your account\"></div>";
		$out .= "		<div><input type=\"hidden\" name=\"action\" value=\"create\"></div>";
		$out .= "	</form>";
		$out .= "</div>";
		$out .= "<div style=\"height:100px;\">&nbsp;</div>";
		
		return $out;
	}

	/*
	 *	Function: 	Create()
	 *	
	 *	Summary:	Validates the information submitted for a new 
	 * 				user account. Upon validation, the new account 
	 *				is created.
	 *	
	 *	Parameters:	$user (string) - User name or e-mail address of the user
	 *				$pass (string) - The plain text password of the user
	 *				$pass2 (string) - The password confirmed
	 *				$firstname (string) - First name of the new user
	 *				$lastname (string) - Last name of the new user
	 *	
	 *	Returns:	No return value
	 *	
	 */
	public function Create($user, $pass, $pass2, $firstname, $lastname)
	{
		if (isBlank($user))
			throw new Exception("No e-mail address was specified");
		
		// 	***INLINESQL***
		// $sql = "select custid, password from pw_user where email = ".smartQuote($user);
		// $row = $this->_db->get_row($sql, ARRAY_A);
		// if ($row)
		// {
		// 	throw new Exception("An account with that e-mail address already exists");
		// }
		
		if (isBlank($firstname))
			throw new Exception("Please provide your first name");
		
		if (isBlank($lastname))
			throw new Exception("Please provide your last name");
		
		if (isBlank($pass))
			throw new Exception("No password was specified");
		
		if (isBlank($pass2))
			throw new Exception("Please confirm your password");
		
		if ($pass != $pass2)
			throw new Exception("The passwords do not match");
		
		// ***INLINESQL***
		// $sql = "exec s_newcustid";
		// $row = $this->_db->get_row($sql, ARRAY_A);
		// if ($row)
		// {
		// 	$this->CustomerID = $row["custid"];
		// }
		$this->Email = $user;
		$this->FirstName = $firstname;
		$this->LastName = $lastname;
		
		// ***INLINESQL***
		// $sql = "insert into pw_customer(custid, firstname, lastname, email) values(".smartQuote($this->CustomerID).", ".smartQuote($this->FirstName).", ".smartQuote($this->LastName).", ".smartQuote($user).")";
		// $this->_db->query($sql);
		
		// $sql = "insert into pw_user(custid, email, password) values(".smartQuote($this->CustomerID).", ".smartQuote($user).", ".smartQuote($pass).")";
		// $this->_db->query($sql);
		
		$this->BillingAddress = new Address();
		$this->ShippingAddress = new Address();
		
		$_SESSION["__account__"] = $this;
	}
	
	/*
	 *	Function: 	FullName()
	 *	
	 *	Summary:	Returns the full name of the account owner
	 *	
	 *	Parameters:	No parameters
	 *	
	 *	Returns:	No return value
	 *	
	 */
	public function FullName()
	{
		return $this->FirstName." ".$this->LastName;
	}
	
	/*
	 *	Function: 	AccountHome()
	 *	
	 *	Summary:	Displays the account home
	 *	
	 *	Parameters:	No parameters
	 *	
	 *	Returns:	string
	 *	
	 */
	public function AccountSidebar($selected)
	{
		$out = "";
		
		$out .= "	<aside class=\"account right\">\n";
		$out .= "		<h4>Account Settings</h4>\n";
		$out .= "		<section class=\"shop\">\n";
		$out .= "			<ul>\n";
		$out .= "				<li><a href=\"/profile/view\">Account Home</a></li>\n";
		$out .= "				<li><a href=\"/profile/info\">Personal Information</a></li>\n";
		$out .= "				<li><a href=\"/profile/account\">Email and Password</a></li>\n";
		$out .= "				<li><a href=\"/profile/billing\">Billing Address</a></li>\n";
		$out .= "				<li><a href=\"/profile/shipping\">Shipping Address</a></li>\n";
		$out .= "				<li><a href=\"/profile/payment\">Credit Cards</a></li>\n";
		$out .= "			<ul>\n";
		$out .= "		</section>\n";
		//$out .= "		<h4>Account Activity</h4>\n";
		//$out .= "		<section class=\"shop\">\n";
		//$out .= "			<ul>\n";
		//$out .= "				<li><a href=\"/profile/history\">My Order History</a></li>\n";
		//$out .= "				<li><a href=\"/profile/library\">My Digital Library</a></li>\n";
		//$out .= "				<li><a href=\"/profile/reviews\">My Reviews</a></li>\n";
		//$out .= "			<ul>\n";
		//$out .= "		</section>\n";
		$out .= "	</aside>\n";
		
		return $out;
	}
	
	/*
	 *	Function: 	AccountHome()
	 *	
	 *	Summary:	Displays the account home
	 *	
	 *	Parameters:	No parameters
	 *	
	 *	Returns:	string
	 *	
	 */
	public function AccountHome()
	{
		$out = "";
		
		$out .= "	<p>&nbsp;</p>\n";
		$out .= "	<section class=\"info-box\">\n";
		$out .= "		<div class=\"subtitle\" style=\"\">Basic Information</div>\n";
		$out .= "		<div style=\"width:676px;\">\n";
		$out .= "			<a href=\"javascript:acctChangeAvatar();\"><img src=\"/framework/img/no_user_pic.jpg\" class=\"avatar\"></a>\n";
		$out .= "			<div style=\"font-weight:bold;\">Personal Information</div>\n";
		$out .= "			<div style=\"padding-left:20px;\">\n";
		$out .= "				<div style=\"\">".$this->FullName()."</div>\n";
		$out .= "				<div style=\"\">Birthday: ".(isBlank($this->Birthday) ? "<span style=\"font-style:italic;\">(not provided)</span>" : date("F d, Y", strtotime(Utilities::DecryptValue("account", $this->Birthday))))."</div>\n";
		$out .= "				<div style=\"\">&nbsp;</div>\n";
		$out .= "				<div style=\"\">E-mail: ".Utilities::DecryptValue("account", $this->Email)."</div>\n";
		$out .= "				<div style=\"\">Password: ********</div>\n";
		$out .= "			</div>\n";
		$out .= "		</div>\n";
		$out .= "		<div style=\"width:676px;height:30px;\">&nbsp;</div>\n";
		$out .= "		<div class=\"subtitle\" style=\"\">Addresses</div>\n";
		$out .= "		<div style=\"float:left; width:250px;\">\n";
		$out .= "			<div style=\"font-weight:bold;\">Billing Address</div>\n";
		$out .= "			<div style=\"padding-left:20px;\">\n";
		$out .= "				<div style=\"\">".$this->BillingAddress->DisplayFormatted()."</div>\n";
		$out .= "			</div>\n";
		$out .= "		</div>\n";
		$out .= "		<div style=\"float:left; width:250px;\">\n";
		$out .= "			<div style=\"font-weight:bold;\">Shipping Address</div>\n";
		$out .= "			<div style=\"padding-left:20px;\">\n";
		$out .= "				<div style=\"\">".$this->ShippingAddress->DisplayFormatted()."</div>\n";
		$out .= "			</div>\n";
		$out .= "		</div>\n";
		$out .= "		<div class=\"clearfloat\"></div>\n";
		$out .= "		<div style=\"width:676px;height:30px;\">&nbsp;</div>\n";
		$out .= "		<div class=\"subtitle\">Recent Orders</div>\n";
		$out .= "		<div style=\"width:676px;\">\n";
		
		// ***INLINESQL***
		// $sql = "select distinct top 5 o.ordid, o.orderdate, o.status, isnull(i.tracknum, la.tracknum) as tracknum ".
		// 	"from cc_orders as o ".
		// 	"	inner join cc_orders_items as i on (i.ordid = o.ordid) ".
		// 	"	left join lsi_orderitems as li on (li.ordid = i.ordid and li.linenum = i.linenum) ".
		// 	"	left join lsi_asncarton as la on (la.ponumber = li.ponumber and la.linenum = li.linenum) ".
		// 	"where o.custid = ".smartQuote($this->CustomerID)." ".
		// 	"order by o.orderdate desc";
		// $rs = $this->_db->get_results($sql);
		// if (count($rs) == 0)
		// {
			$out .= "			<div style=\"padding-left:20px;\">\n";
			$out .= "				<div style=\"font-style:italic;\">No orders found</div>\n";
			$out .= "			</div>\n";
		// }
		// else
		// {
		// 	foreach ($rs as $row)
		// 	{
		// 		$status = $row->status;
		// 		if (!isBlank($row->tracknum))
		// 			$status = "Shipped";
		// 		if ($row->status == "Paid")
		// 			$status = "Processing";
				
		// 		$out .= "			<div style=\"padding-left:20px;\">\n";
		// 		$out .= "				<div style=\"float:left; width:150px;\"><a href=\"/profile/order/".$row->ordid."\">".$row->ordid."</a></div>\n";
		// 		$out .= "				<div style=\"float:left; width:200px;\">".date("F d, Y", strtotime($row->orderdate))."</div>\n";
		// 		$out .= "				<div style=\"float:left; width:200px;\">".$status."</div>\n";
		// 		$out .= "				<div class=\"clearfloat\"></div>\n";
		// 		$out .= "			</div>\n";
		// 	}
		// }
		
		$out .= "		</div>\n";
		$out .= "	</section>\n";
		
		return $this->PageWrapper("Account Home", $out);
	}
	
	private function FormatFileSize($bytes)
	{
		if(!empty($bytes))
		{
			$s = array('bytes', 'KB', 'MB', 'GB', 'TB', 'PB');
			$e = floor(log($bytes)/log(1024));
			
			$output = sprintf('%.2f '.$s[$e], ($bytes/pow(1024, floor($e))));
			
			return $output;
		}
	}
	
	public function ShowHistory()
	{
		$out = "";
		
		$out .= "	<p>View previous orders.</p>\n";
		$out .= "	<section class=\"info-box\">\n";
		
		// ***INLINESQL***
		// $sql = "select distinct o.ordid, o.orderdate, o.status, isnull(i.tracknum, la.tracknum) as tracknum ".
		// 	"from cc_orders as o ".
		// 	"	inner join cc_orders_items as i on (i.ordid = o.ordid) ".
		// 	"	left join lsi_orderitems as li on (li.ordid = i.ordid and li.linenum = i.linenum) ".
		// 	"	left join lsi_asncarton as la on (la.ponumber = li.ponumber and la.linenum = li.linenum) ".
		// 	"where o.custid = ".smartQuote($this->CustomerID)." ".
		// 	"order by o.orderdate desc";
		// $rs = $this->_db->get_results($sql);
		// if (count($rs) == 0)
		// {
			$out .= "			<div style=\"padding-left:20px;\">\n";
			$out .= "				<div style=\"font-style:italic;\">No orders found</div>\n";
			$out .= "			</div>\n";
		// }
		// else
		// {
		// 	foreach ($rs as $row)
		// 	{
		// 		$status = $row->status;
		// 		if (!isBlank($row->tracknum))
		// 			$status = "Shipped";
		// 		if ($row->status == "Paid")
		// 			$status = "Processing";
				
		// 		$out .= "			<div style=\"padding-left:20px;\">\n";
		// 		$out .= "				<div style=\"float:left; width:225px;\"><a href=\"/profile/order/".$row->ordid."\">".$row->ordid."</a></div>\n";
		// 		$out .= "				<div style=\"float:left; width:275px;\">".date("F d, Y", strtotime($row->orderdate))."</div>\n";
		// 		$out .= "				<div style=\"float:left; width:150px;\">".$status."</div>\n";
		// 		$out .= "				<div class=\"clearfloat\"></div>\n";
		// 		$out .= "			</div>\n";
		// 	}
		// }
		
		$out .= "		<p>	\n";
		$out .= "			<a class=\"alignright button\" href=\"/profile/view\">Go Back</a>\n";
		$out .= "		</p>\n";
		$out .= "	</section>\n";
		
		return $this->PageWrapper("Payment Information", $out);
	}
	
	public function ShowCreditCards()
	{
		$out = "";
		
		$out .= "	<p>Manage your saved credit card information.</p>\n";
		$out .= "	<section class=\"info-box stacked\">\n";
		$out .= "		<p>\n";
		$out .= "			<button class=\"button green\" onclick=\"acctAddNewCard();\">Add New Credit Card</button>\n";
		$out .= "		</p>\n";
		$r = new RestRunner();

		$result = $r->Get($this->CustomerApi."/payments/all/".$_SESSION["__account__"]->RowID);
		if (count($result) > 0)
		{
			foreach ($result as $item)
			{
				$cc = new CreditCard();
				$cc->CardID = $item->payId;
				$cc->RowID = $item->payId;
				$cc->CustID = $this->RowID;
				$cc->CardType = $item->cardType;
				$cc->CardName = $item->cardName;
				$cc->CardNumber = Utilities::DecryptValue("payment", $item->cardNumber);
				$cc->ExpirationMonth = intval($item->expirationMonth);
				$cc->ExpirationYear = intval($item->expirationYear);
				$cc->CVV = Utilities::DecryptValue("payment", $item->cvv);
				
				$out .= $cc->DisplayStacked();
			}
		}
		else
		{
			$out .= "		<p>You have no saved credit cards.</p>\n";
		}
		$out .= "		<p>	\n";
		$out .= "			<a class=\"alignright button\" href=\"/profile/view\">Go Back</a>\n";
		$out .= "		</p>\n";
		$out .= "	</section>\n";
		
		return $this->PageWrapper("Payment Information", $out);
	}
	
	public function UpdateAddress($addrtype)
	{
		$out = "";
		$label = "";
		$action = "";
		$initdisplay = "";
		
		switch (strtoupper($addrtype))
		{
			case "B":
				$label = "Billing";
				$action = "billupdate";
				$address = &$this->BillingAddress;
				$address->AddressType = $addrtype;
				break;
			case "S":
				$label = "Shipping";
				$action = "shipupdate";
				$address = &$this->ShippingAddress;
				$address->AddressType = $addrtype;
				break;
		}
		
		$out .= "	<p>Update your ".strtolower($label)." address.</p>\n";
		$out .= "	<section class=\"info-box\">\n";
		$out .= "		<form action=\"/profile/".strtolower($label)."/update\" method=\"post\">\n";
		if ($this->LastMessage != "")
		{
			$msg = $this->LastMessage;
			if ($msg == "saved")
			{
				$out .= "		<p class=\"confirm\">\n";
				$out .= "			Your information has been updated.\n";
				$out .= "		</p>\n";
			}
			else
			{
				$out .= "		<p class=\"warning\">\n";
				$out .= "			".$msg."\n";
				$out .= "		</p>\n";
			}
			$this->LastMessage = "";
		}
		$out .= "		<p>\n";
		$out .= "			<label for=\"info_contact\">Contact Name</label>\n";
		$out .= "			<input type=\"text\" name=\"info_contact\" id=\"info_contact\" value=\"".$address->Contact."\" />\n";
		$out .= "		</p>\n";
		$out .= "		<p>\n";
		$out .= "			<label for=\"info_address1\">Address</label>\n";
		$out .= "			<input type=\"text\" name=\"info_address1\" id=\"info_address1\" value=\"".$address->Address1."\" />\n";
		$out .= "		</p>\n";
		$out .= "		<p>\n";
		$out .= "			<label for=\"info_address2\">&nbsp;</label>\n";
		$out .= "			<input type=\"text\" name=\"info_address2\" id=\"info_address2\" value=\"".$address->Address2."\" />\n";
		$out .= "		</p>\n";
		$out .= "		<p>\n";
		$out .= "			<label for=\"info_city\">City</label>\n";
		$out .= "			<input type=\"text\" name=\"info_city\" id=\"info_city\" value=\"".$address->City."\" />\n";
		$out .= "		</p>\n";
		$out .= "		<p>\n";
		$out .= "			<label for=\"info_state\">State</label>\n";
		$out .= "			<select name=\"info_state\" id=\"info_state\" /></div>\n";

		$states = Utilities::GetStates();
		foreach ($states as $x)
		{
			$state = (object) $x;
			$strsel = "";
			if ($state->Abbr == $address->State)
				$strsel = " selected";
			$out .= "				<option value=\"".$state->Abbr."\"".$strsel.">".$state->Name."</option>\n";
		}

		$out .= "			</select>\n";
		$out .= "		</p>\n";
		$out .= "		<p>\n";
		$out .= "			<label for=\"info_zip\">Zip</label>\n";
		$out .= "			<input type=\"text\" name=\"info_zip\" id=\"info_zip\" value=\"".$address->Zip."\" />\n";
		$out .= "		</p>\n";
		$out .= "		<p>\n";
		$out .= "			<label for=\"info_phone\">Phone</label>\n";
		$out .= "			<input type=\"text\" name=\"info_phone\" id=\"info_phone\" value=\"".$address->Phone."\" />\n";
		$out .= "		</p>\n";
		$out .= "		<p>	\n";
		$out .= "			<input type=\"submit\" class=\"alignright green button\" value=\"Continue\" />\n";
		$out .= "			<a class=\"alignright button\" href=\"/profile/view\">Go Back</a>\n";
		$out .= "		</p>\n";
		$out .= "		<input type=\"hidden\" name=\"action\" value=\"".$action."\">\n";
		$out .= "		</form>\n";
		$out .= "	</section>\n";
		
		return $this->PageWrapper($label." Address", $out);
	}
	
	public function SaveAddress($addrtype, $contact, $address1, $address2, $city, $state, $zip, $phone)
	{
		switch (strtoupper($addrtype))
		{
			case "B":
				$address = &$this->BillingAddress;
				break;
			case "S":
				$address = &$this->ShippingAddress;
				break;
		}
		
		if (!$address->AddressID) $address->AddressID = 0;
		if ($address->CustomerID == '') $address->CustomerID = $this->CustomerID;
		
		$address->Contact = $contact;
		$address->Address1 = $address1;
		$address->Address2 = $address2;
		$address->City = $city;
		$address->State = $state;
		$address->Zip = $zip;
		$address->Phone = $phone;
		
		$address->SaveAddress();
	}
	
	public function UpdatePersonalInfo()
	{
		$out = "";
		
		$out .= "	<p>All fields on this page are required. Using your email address and password, you will be able to update your information.</p>\n";
		$out .= "	<section class=\"info-box\">\n";
		$out .= "		<form action=\"/profile/info/update\" method=\"post\">\n";
		if ($this->LastMessage != "")
		{
			$msg = $this->LastMessage;
			if ($this->LastMessage == "saved")
			{
				$out .= "		<p class=\"confirm\">\n";
				$out .= "			Your information has been updated.\n";
				$out .= "		</p>\n";
			}
			else
			{
				$out .= "		<p class=\"warning\">\n";
				$out .= "			".$msg."\n";
				$out .= "		</p>\n";
			}
			$this->LastMessage = "";
		}
		$out .= "		<p>\n";
		$out .= "			<label for=\"info_firstname\">First Name</label>\n";
		$out .= "			<input type=\"text\" name=\"info_firstname\" id=\"info_firstname\" value=\"".$this->FirstName."\" />\n";
		$out .= "		</p>\n";
		$out .= "		<p>\n";
		$out .= "			<label for=\"info_lastname\">Last Name</label>\n";
		$out .= "			<input type=\"text\" name=\"info_lastname\" id=\"info_lastname\" value=\"".$this->LastName."\" />\n";
		$out .= "		</p>\n";
		$out .= "		<p style=\"display:none;\">\n";
		$out .= "			<label for=\"info_ssn\">Social Security Number</label>\n";
		$out .= "			<input type=\"text\" name=\"info_ssn\" id=\"info_ssn\" value=\"".(!$this->SSN ? "" : Utilities::DecryptValue("account", $this->SSN))."\" />\n";
		$out .= "		</p>\n";
		$out .= "		<p>\n";
		$out .= "			<label for=\"info_birthday\">Birthday</label>\n";
		$out .= "			<input type=\"text\" readonly=\"readonly\" name=\"info_birthday\" id=\"info_birthday\" value=\"".(!$this->Birthday ? "" : date("m/d/Y", strtotime(Utilities::DecryptValue("account", $this->Birthday))))."\" />\n";
		$out .= "		</p>\n";
		$out .= "		<p>	\n";
		$out .= "			<input type=\"submit\" class=\"alignright green button\" value=\"Continue\" />\n";
		$out .= "			<a class=\"alignright button\" href=\"/profile/view\">Go Back</a>\n";
		$out .= "		</p>\n";
		$out .= "		<input type=\"hidden\" name=\"action\" value=\"infoupdate\">\n";
		$out .= "		</form>\n";
		$out .= "	</section>\n";
		
		return $this->PageWrapper("Personal Information", $out);
	}
	
	public function SavePersonalInfo($firstname, $lastname, $ssn, $birthday)
	{
		$request = $this->CustomerApi."/customers/info/".$custid;
		$rr = new RestRunner();
		$retval = $rr->Put($request, $this->OutputJson());
		
		$this->FirstName = $firstname;
		$this->LastName = $lastname;
		$this->SSN = $ssn;
		$this->Birthday = $birthday;
	}
	
	public function UpdateLoginInfo()
	{
		$out = "";
		
		$out .= "	<p>WARNING: Your e-mail address is used for logging in. Make sure you use an active account in case you need to recover your login information.</p>\n";
		$out .= "	<section class=\"info-box\">\n";
		$out .= "		<form action=\"/profile/account/update\" method=\"post\">\n";
		if ($this->LastMessage != "" && substr($this->LastMessage, 0, 2) == "l:")
		{
			$msg = substr($this->LastMessage, 2);
			if ($msg == "saved")
			{
				$out .= "		<p class=\"confirm\">\n";
				$out .= "			Your e-mail address has been updated.\n";
				$out .= "		</p>\n";
			}
			else
			{
				$out .= "		<p class=\"warning\">\n";
				$out .= "			".$msg."\n";
				$out .= "		</p>\n";
			}
			$this->LastMessage = "";
		}
		$out .= "		<p>\n";
		$out .= "			<label for=\"info_email\">Email Address</label>\n";
		$out .= "			<input type=\"text\" name=\"info_email\" id=\"info_email\" value=\"".Utilities::DecryptValue("account", $this->Email)."\" />\n";
		$out .= "		</p>\n";
		$out .= "		<p>	\n";
		$out .= "			<input type=\"submit\" class=\"alignright green button\" value=\"Continue\" />\n";
		$out .= "			<a class=\"alignright button\" href=\"/profile/view\">Go Back</a>\n";
		$out .= "		</p>\n";
		$out .= "		<input type=\"hidden\" name=\"action\" value=\"accountupdate\">\n";
		$out .= "		</form>\n";
		$out .= "	</section>\n";
		$out .= "	<p>Use the section below to change your password.</p>\n";
		$out .= "	<section class=\"info-box\">\n";
		$out .= "		<form action=\"/profile/password/update\" method=\"post\">\n";
		if ($this->LastMessage != "" && substr($this->LastMessage, 0, 2) == "p:")
		{
			$msg = substr($this->LastMessage, 2);
			if ($msg == "saved")
			{
				$out .= "		<p class=\"confirm\">\n";
				$out .= "			Your password has been updated.\n";
				$out .= "		</p>\n";
			}
			else
			{
				$out .= "		<p class=\"warning\">\n";
				$out .= "			".$msg."\n";
				$out .= "		</p>\n";
			}
			$this->LastMessage = "";
		}
		$out .= "		<p>\n";
		$out .= "			<label for=\"info_password\">New Password</label>\n";
		$out .= "			<input type=\"password\" name=\"info_password\" id=\"info_password\" />\n";
		$out .= "		</p>\n";
		$out .= "		<p>\n";
		$out .= "			<label for=\"info_passwordc\">Confirm Password</label>\n";
		$out .= "			<input type=\"password\" name=\"info_passwordc\" id=\"info_passwordc\" />\n";
		$out .= "		</p>\n";
		$out .= "		<p>	\n";
		$out .= "			<input type=\"submit\" class=\"alignright green button\" value=\"Continue\" />\n";
		$out .= "			<a class=\"alignright button\" href=\"/profile/view\">Go Back</a>\n";
		$out .= "		</p>\n";
		$out .= "		<input type=\"hidden\" name=\"action\" value=\"changepassword\">\n";
		$out .= "		</form>\n";
		$out .= "	</section>\n";
		
		$this->LastMessage = "";
		
		return $this->PageWrapper("Update Account Information", $out);
	}
	
	public function SaveEmail($email)
	{
		$oldemail = Utilities::DecryptValue("account", $this->Email);
		$newemail = Utilities::DecryptValue("account", $email);

		// Update the customer database
		$request = $this->CustomerApi."/customers/email/".$custid;
		$rr = new RestRunner();
		$retval = $rr->Put($request, $this->OutputJson());

		// Update the login information in Vault
		//
		// get the password
		$request = $this->VaultUrl."/v1/usercreds/data/".$oldemail;
		$rr = new RestRunner();
		$rr->SetHeader("X-Vault-Token", getenv("VAULT_TOKEN"));
		$retval = $rr->Get($request);
		$password = $retval->data->data->password;

		// create the new record
		$request = $this->VaultUrl."/v1/usercreds/data/".$newemail;
		$rr = new RestRunner();
		$rr->SetHeader("X-Vault-Token", getenv("VAULT_TOKEN"));
		$retval = $rr->Post($request, "{\"data\": { \"username\": \"".$newemail."\", \"password\": \"".$password."\", \"customerno\": \"".$this->CustomerID."\" } }");

		// destroy the previous one
		$request = $this->VaultUrl."/v1/usercreds/metadata/".$oldemail;
		$rr = new RestRunner();
		$rr->SetHeader("X-Vault-Token", getenv("VAULT_TOKEN"));
		$retval = $rr->Delete($request);

		$this->Email = $email;
	}
	
	public function SavePassword($pass)
	{
		$email = Utilities::DecryptValue("account", $this->Email);

		$request = $this->VaultUrl."/v1/usercreds/data/".$email;
		$rr = new RestRunner();
		$rr->SetHeader("X-Vault-Token", getenv("VAULT_TOKEN"));
		$retval = $rr->Post($request, "{\"data\": { \"username\": \"".$email."\", \"password\": \"".$pass."\", \"customerno\": \"".$this->CustomerID."\" } }");
	}
	
	public function PageWrapper($title, $body)
	{
		$out = "";
		
		$out .= $this->HideSidebar();
		$out .= "<div class=\"content\">\n";
		$out .= $this->AccountSidebar("view");
		$out .= "	<article style=\"width:730px;\">\n";
		$out .= "		<h2>".$title."</h2>\n";
		$out .= "		".$body."\n";
		$out .= "	</article>\n";
		$out .= "</div>\n";
		
		return $out;
	}
	
	public function DeleteCreditCard($cardid)
	{
		$request = $this->CustomerApi."/payments/".$cardid;
		$rr = new RestRunner();
		$retval = $rr->Delete($request);
	}
	
	public function SaveCreditCard($name, $type, $number, $cvv, $month, $year)
	{
		if ($this->CustomerID != "")
		{
			$xc = new CreditCard();
			$xc->CustID = $this->RowID;
			$xc->CardName = $name;
			$xc->CardNumber = $number;
			$xc->CardType = $type;
			$xc->CVV = $cvv;
			$xc->ExpirationMonth = $month;
			$xc->ExpirationYear = $year;
			
			$request = $this->CustomerApi."/payments";
			$rr = new RestRunner();
			$rr->SetHeader("Content-Type", "application/json");
			$retval = $rr->Post($request, $xc->OutputJson());
		}
	}
	
	public function AddNewCard()
	{
		$out = "";
		
		$out .= "	<div class=\"order-signin\" style=\"\">\n";
		$out .= "		<img src=\"/framework/img/close.png\" style=\"height:32px; width:32px; position:absolute; right:-12px; top:-12px; cursor:pointer;\" onclick=\"unpopWindow();\" />\n";
		$out .= "		<div class=\"order-summary-heading\">ADD NEW CREDIT CARD</div>\n";
		$out .= "		<div style=\"margin-right:10px;width:500px;\">\n";
		$out .= "			<div class=\"address-line\">\n";
		$out .= "				<div class=\"address-label\" style=\"width:144px;\">Name on Credit Card: </div>\n";
		$out .= "				<div class=\"address-input\" style=\"width:200px;\"><input type=\"text\" maxlength=\"35\" name=\"pay_new_cardname\" id=\"pay_new_cardname\" value=\"\" style=\"width:195px;\" /></div>\n";
		$out .= "				<div class=\"clearfloat\"></div>\n";
		$out .= "			</div>\n";
		$out .= "			<div class=\"address-line\">\n";
		$out .= "				<div class=\"address-label\" style=\"width:144px;\">Card Types: </div>\n";
		$out .= "				<div class=\"address-input\" style=\"width:200px;\">";
		$out .= "					<img src=\"/framework/img/VS_cards.png\" id=\"pay_cardtype_VS\" style=\"width:45px;float:left;margin-right:5px;\" />\n";
		$out .= "					<img src=\"/framework/img/MC_cards.png\" id=\"pay_cardtype_MC\" style=\"width:45px;float:left;margin-right:5px;\" />\n";
		$out .= "					<img src=\"/framework/img/AX_cards.png\" id=\"pay_cardtype_AX\" style=\"width:45px;float:left;margin-right:5px;\" />\n";
		$out .= "					<img src=\"/framework/img/DI_cards.png\" id=\"pay_cardtype_DI\" style=\"width:45px;float:left;margin-right:5px;\" />\n";
		$out .= "				</div>\n";
		$out .= "				<div class=\"clearfloat\"></div>\n";
		$out .= "			</div>\n";
		$out .= "			<div class=\"address-line\">\n";
		$out .= "				<div class=\"address-label\" style=\"width:144px;\">Card Number: </div>\n";
		$out .= "				<div class=\"address-input\" style=\"width:200px;\"><input type=\"text\" maxlength=\"16\" name=\"pay_new_cardnum\" id=\"pay_new_cardnum\" value=\"\" onkeypress=\"return ccNumbersOnly(this, event);\" style=\"width:195px;\" /></div>\n";
		$out .= "				<div class=\"clearfloat\"></div>\n";
		$out .= "			</div>\n";
		$out .= "			<div class=\"address-line\">\n";
		$out .= "				<div class=\"address-label\" style=\"width:144px;\">CVV Number: </div>\n";
		$out .= "				<div class=\"address-input\" style=\"width:200px;\"><input type=\"text\" maxlength=\"4\" name=\"pay_new_cvvnum\" id=\"pay_new_cvvnum\" value=\"\" onkeypress=\"return ccNumbersOnly(this, event);\" style=\"width:60px;\" /></div>\n";
		$out .= "				<div class=\"clearfloat\"></div>\n";
		$out .= "			</div>\n";
		$out .= "			<div class=\"address-line\">\n";
		$out .= "				<div class=\"address-label\" style=\"width:144px;\">Expiration: </div>\n";
		$out .= "				<div class=\"address-input\" style=\"width:200px;\">";
		$out .= "					<select name=\"pay_new_expmonth\" id=\"pay_new_expmonth\" style=\"width:110px;\">\n";
		$out .= "						<option value=\"1\">January</option>\n";
		$out .= "						<option value=\"2\">February</option>\n";
		$out .= "						<option value=\"3\">March</option>\n";
		$out .= "						<option value=\"4\">April</option>\n";
		$out .= "						<option value=\"5\">May</option>\n";
		$out .= "						<option value=\"6\">June</option>\n";
		$out .= "						<option value=\"7\">July</option>\n";
		$out .= "						<option value=\"8\">August</option>\n";
		$out .= "						<option value=\"9\">September</option>\n";
		$out .= "						<option value=\"10\">October</option>\n";
		$out .= "						<option value=\"11\">November</option>\n";
		$out .= "						<option value=\"12\">December</option>\n";
		$out .= "					</select>\n";
		$out .= "					<select name=\"pay_new_expyear\" id=\"pay_new_expyear\" style=\"width:75px;\">\n";
		for ($i = 0; $i < 10; $i++)
		{
			$out .= "						<option value=\"".(intval(date("Y")) + $i)."\">".(intval(date("Y")) + $i)."</option>\n";
		}
		$out .= "					</select>\n";
		$out .= "				</div>\n";
		$out .= "				<input type=\"hidden\" name=\"pay_new_cardtype\" id=\"pay_new_cardtype\" value=\"\" />\n";
		$out .= "				<div class=\"clearfloat\"></div>\n";
		$out .= "			</div>\n";
		$out .= "		</div>\n";
		$out .= "		<div class=\"clearfloat\"></div>\n";
		$out .= "		<div class=\"order-signin-error\" id=\"signin-error\"></div>";
		$out .= "		<div class=\"order-signin-controls\">\n";
		$out .= "			<div class=\"order-summary-continue\" style=\"float:right;\"><button class=\"green button\" name=\"close_button\" id=\"close_button\" onclick=\"acctSaveNewCard();\">Save</button></div>\n";
		$out .= "			<div class=\"clearfloat\"></div>\n";
		$out .= "		</div>\n";
		$out .= "		<div class=\"clearfloat\"></div>\n";
		$out .= "	</div>\n";
		
		return $out;
	}
	
	public function RecoverPassword()
	{
		$out = "";
		
		$out .= "<div class=\"content\">";
		$out .= $this->HideSidebar();
		$out .= "	<div style=\"height:100px;\">&nbsp;</div>";
		$out .= "	<div class=\"login-screen\">";
		$out .= "		<div class=\"order-signin-error\" style=\"margin-bottom:20px;text-align:center;\" id=\"signin-error\"></div>";
		$out .= "		<div class=\"text\">Password Recovery</div>";
		$out .= "		<div class=\"text\" style=\"font-size:16px; font-weight:normal;\">Enter your e-mail address below, click Send Password, and a new password will be generated and e-mailed to you.</div>";
		$out .= "		<div style=\"margin:10px;\"><input type=\"text\" value=\"\" name=\"login_username\" id=\"login_username\" placeholder=\"E-mail\"></div>";
		$out .= "		<div style=\"margin:10px;\"><button type=\"button\" onclick=\"acctResetPassword();\">Send Password</button></div>";
		$out .= "	</div>";
		$out .= "	<div style=\"height:100px;\">&nbsp;</div>";
		$out .= "</div>";
		
		return $out;
	}
	
	public function SetNewPassword($id)
	{
		$token = base64url_decode($id);
		$msg = "";
		$rdo = "";
		$dis = "";
		
		// ***INLINESQL***
		// $sql = "select count(*) as cnt from pw_customer_reset where token = ".smartQuote($token)." and dateused is null";
		// $cnt = $this->_db->get_var($sql);
		// if ($cnt === 0)
		// {
		// 	$msg = "Sorry, this password reset request is no longer valid.";
		// 	$rdo = " readonly=\"readonly\" ";
		// 	$dis = " disabled=\"disabled\" ";
		// }
		
		$out = "";
		
		$out .= "<div class=\"content\">";
		$out .= $this->HideSidebar();
		$out .= "	<div style=\"height:100px;\">&nbsp;</div>";
		$out .= "	<div class=\"login-screen\">";
		$out .= "		<div class=\"order-signin-error\" style=\"margin-bottom:30px;text-align:center;line-height:normal;float:none;\" id=\"signin-error\">".$msg."</div>";
		$out .= "		<div class=\"text\">Password Recovery</div>";
		$out .= "		<div class=\"text\" style=\"font-size:16px; font-weight:normal;\">Enter your new password below:</div>";
		$out .= "		<div style=\"margin:10px;\"><input type=\"password\" ".$rdo." value=\"\" name=\"login_password\" id=\"login_password\" placeholder=\"New Password\"></div>";
		$out .= "		<div style=\"margin:10px;\"><input type=\"password\" ".$rdo." value=\"\" name=\"login_passwordc\" id=\"login_passwordc\" placeholder=\"Confirm Password\"></div>";
		$out .= "		<div style=\"margin:10px;\"><button type=\"button\" ".$dis." onclick=\"acctSavePassword('".$id."');\">Save Password</button></div>";
		$out .= "	</div>";
		$out .= "	<div style=\"height:100px;\">&nbsp;</div>";
		$out .= "</div>";
		
		return $out;
	}
	
	public function SaveNewPassword($token, $pwd, $pwdc)
	{
		$retval = array();
		$retval["Error"] = "";
		$retval["Body"] = "";
		$token = base64url_decode($token);
		$custid = "";
		
		// ***INLINESQL***
		// $sql = "select custid from pw_customer_reset where token = ".smartQuote($token)." and dateused is null";
		// $row = $this->_db->get_row($sql);
		// if (count($row) == 0)
		// {
		// 	$retval["Error"] = "Sorry, this password reset request is no longer valid.";
		// 	return $retval;
		// }
		// else
		// {
		// 	$custid = $row->custid;
		// }
		
		if (strlen($pwd) < 8)
		{
			$retval["Error"] = "Your new password must be at least 8 characters long";
			return $retval;
		}
		
		if ($pwd != $pwdc)
		{
			$retval["Error"] = "Your passwords don't match. Please try again.";
			return $retval;
		}
		
		// ***INLINESQL***
		// $sql = "update pw_customer_reset set dateused = getdate() where token = ".smartQuote($token);
		// $this->_db->query($sql);
		
		// $sql = "update pw_user set password = ".smartQuote($pwd, true)." where custid = ".smartQuote($custid);
		// $this->_db->query($sql);
		
		$out = "";
		
		$out .= "	<div class=\"order-signin\" style=\"\">\n";
		$out .= "		<img src=\"/framework/img/close.png\" style=\"height:32px; width:32px; position:absolute; right:-12px; top:-12px; cursor:pointer;\" onclick=\"unpopWindow();\" />\n";
		$out .= "		<div class=\"order-summary-heading\">Password Recovery</div>\n";
		$out .= "		<div style=\"padding:15px;font-size:16px;\">Your password has been reset. Click Close to login now.</div>\n";
		$out .= "		<div class=\"clearfloat\"></div>\n";
		$out .= "		<div class=\"order-signin-controls\">\n";
		$out .= "			<div class=\"order-summary-continue\"><input class=\"green button\" name=\"close_button\" id=\"close_button\" value=\"Close\" onclick=\"unpopWindow();\" type=\"button\" /></div>\n";
		$out .= "			<div class=\"clearfloat\"></div>\n";
		$out .= "		</div>\n";
		$out .= "		<div class=\"clearfloat\"></div>\n";
		$out .= "	</div>\n";
		
		$this->LastPage = "";
		
		$retval["Body"] = $out;
		
		return $retval;
	}
	
	public function ResetPassword($email)
	{
		$retval = array();
		$retval["Error"] = "";
		$retval["Body"] = "";
		$body = "";
		$url = "www.java-perks.com";
		$custid = "";
		$uid = "";
		
		if (!filter_var($email, FILTER_VALIDATE_EMAIL))
		{
			$retval["Error"] = "Please enter a valid e-mail address";
			return $retval;
		}
		
		// ***INLINESQL***
		// $sql = "select custid from pw_user where email = ".smartQuote($email);
		// $row = $this->_db->get_row($sql);
		// if (count($row) == 0)
		// {
		// 	$retval["Error"] = "Sorry, that e-mail address wasn't found in our system.";
		// 	return $retval;
		// }
		// else
		// {
		// 	$custid = $row->custid;
		// }
		
		// $sql = "select newid() as uid";
		// $uid = mssql_guid_string($this->_db->get_var($sql));
		
		// $sql = "insert into pw_customer_reset(token, custid) values(".smartQuote($uid).", ".smartQuote($custid).")";
		// $this->_db->query($sql);
		
		$body .= "<html>";
		$body .= "	<head>\n";
		$body .= "		<title>java-perks.com: Reset Password</title>\n";
		$body .= "	</head>\n";
		$body .= "	<body style=\"margin:0;padding:0\" bgcolor=\"#FFF9EE\">\n";
		$body .= "		<table align=\"center\" cellspacing=\"0\" cellpadding=\"0\" bgcolor=\"#FFF9EE\">\n";
		$body .= "			<tr>\n";
		$body .= "				<td style=\"padding:18px 10px 20px 10px\">\n";
		$body .= "					<table align=\"center\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\" width=\"702\">\n";
		$body .= "						<tr>\n";
		$body .= "							<td>\n";
		$body .= "								<table align=\"center\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\" width=\"702\">\n";
		$body .= "									<tr>\n";
		$body .= "										<td style=\"padding-bottom:8px;padding-left:2px\" valign=\"bottom\" align=\"left\">\n";
		$body .= "											<img src=\"https://".$url."/framework/img/wp-mail-header.png\" width=\"279\" height=\"69\" style=\"display:block;margin:0\" border=\"0\" alt=\"Java Persk\">\n";
		$body .= "										</td>\n";
		$body .= "										<td style=\"padding-bottom:10px;\" valign=\"bottom\" align=\"right\">\n";
		$body .= "											<div style=\"font-family: Helvetica, sans-serif, Arial, Verdana;color:#444444;font-size:20px;line-height:1em !important\">Password Reset</div>\n";
		$body .= "										</td>\n";
		$body .= "									</tr>\n";
		$body .= "								</table>\n";
		$body .= "							</td>\n";
		$body .= "						</tr>\n";
		$body .= "						<tr>\n";
		$body .= "							<td>\n";
		$body .= "								<table style=\"-webkit-border-radius:5px;-moz-border-radius:5px;\" bgcolor=\"#ffffff\" align=\"center\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\" width=\"702\">\n";
		$body .= "									<tr>\n";
		$body .= "										<td bgcolor=\"#e3e3e3\" width=\"1\"></td>\n";
		$body .= "										<td bgcolor=\"#ffffff\" width=\"700\">\n";
		$body .= "											<table align=\"center\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\">\n";
		$body .= "												<tr>\n";
		$body .= "													<td style=\"padding:20px 10px 20px 10px;\">\n";
		$body .= "														<div style=\"font-family:Helvetica, sans-serif, Arial, Verdana;color:#000000;font-size:14px;\">\n";
		
		$body .= "														<p>Greetings,</p>\n";
		$body .= "														<p><a href=\"https://".$url."\">java-perks.com</a> received a password reset request for the user at this e-mail address. If you didn't request a new password, please <a href=\"https://www.java-perks.com/profile/login\">login to your account</a> to ensure your password is not changed by an unauthorized user.</p>\n";
		$body .= "														<p>If you wish to proceed and reset your password, please click the link below:</p>\n";
		$body .= "														<p><a href=\"https://".$url."/profile/reset-password/".base64url_encode($uid)."\">https://".$url."/profile/reset-password/".base64url_encode($uid)."</a></p>\n";
		$body .= "														<p>Thank you for choosing java-perks.com! If you have any questions, please contact us at <a href=\"mailto:support@java-perks.com\">support@java-perks.com</a>.</p>\n";
		$body .= "														<p>Regards,<br>Java Perks</p>\n";
		
		$body .= "														</div>\n";
		$body .= "													</td>\n";
		$body .= "												</tr>\n";
		$body .= "											</table>\n";
		$body .= "										</td>\n";
		$body .= "										<td bgcolor=\"#e3e3e3\" width=\"1\"></td>\n";
		$body .= "									</tr>\n";
		$body .= "								</table>\n";
		$body .= "							</td>\n";
		$body .= "						</tr>\n";
		$body .= "						<tr>\n";
		$body .= "							<td>\n";
		$body .= "								<table width=\"700\" align=\"center\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">\n";
		$body .= "									<tr><td style=\"padding-top:14px;-webkit-text-size-adjust:125%; text-align:center;\"><div style=\"font-size:10px; line-height:1.3em; color:#979797;font-family: Helvetica, sans-serif, Arial, Verdana\">Copyright &#169; 2012&nbsp;<a style=\"text-decoration:none !important;color:#979797\">Java Perks, LLC.</a>&#32;All rights reserved.</div></td></tr>\n";
		$body .= "								</table>\n";
		$body .= "							</td>\n";
		$body .= "						</tr>\n";
		$body .= "					</table>\n";
		$body .= "				</td>\n";
		$body .= "			</tr>\n";
		$body .= "		</table>\n";
		$body .= "	</body>";
		$body .= "</html>";
		
		require_once("../plugins/swift/lib/swift_required.php");
		
		$message = Swift_Message::newInstance();
		$message->setSubject("java-perks.com: Reset Password");
		$message->setFrom(array("no_reply@java-perks.com" => "Java Stop"));
		$message->setTo(array($email));
		$message->setBody($body, "text/html");
		
		$transport = Swift_SmtpTransport::newInstance("barracuda.java-perks.com", 25);
		//$transport->setUsername("noc");
		//$transport->setPassword("All4l0ve");
		
		$mailer = Swift_Mailer::newInstance($transport);
		$result = $mailer->send($message);
		
		$out = "";
		
		$out .= "	<div class=\"order-signin\" style=\"\">\n";
		$out .= "		<img src=\"/framework/img/close.png\" style=\"height:32px; width:32px; position:absolute; right:-12px; top:-12px; cursor:pointer;\" onclick=\"unpopWindow();\" />\n";
		$out .= "		<div class=\"order-summary-heading\">Password Recovery</div>\n";
		$out .= "		<div style=\"padding:15px;font-size:16px;\">An e-mail has been delivered to you with further instructions.</div>\n";
		$out .= "		<div class=\"clearfloat\"></div>\n";
		$out .= "		<div class=\"order-signin-controls\">\n";
		$out .= "			<div class=\"order-summary-continue\"><input class=\"green button\" name=\"close_button\" id=\"close_button\" value=\"Close\" onclick=\"unpopWindow();\" type=\"button\" /></div>\n";
		$out .= "			<div class=\"clearfloat\"></div>\n";
		$out .= "		</div>\n";
		$out .= "		<div class=\"clearfloat\"></div>\n";
		$out .= "	</div>\n";
		
		$retval["Body"] = $out;
		
		return $retval;
	}
	
	public function NotImplemented($title, $message)
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

	public function OutputJson()
	{
		$out = "";

		$out .= "{\n";
		$out .= "    \"custId\": ".$this->RowID.",";
		$out .= "    \"custNo\": \"".$this->CustomerID."\",";
		$out .= "    \"firstName\": \"".$this->FirstName."\",";
		$out .= "    \"lastName\": \"".$this->LastName."\",";
		$out .= "    \"email\": \"".$this->Email."\",";
		$out .= "    \"dob\": \"".$this->Birthday."\",";
		$out .= "    \"ssn\": \"".$this->SSN."\",";
		$out .= "    \"addresses\": [],";
		$out .= "    \"dateCreated\": 0";
		$out .= "}";

		return $out;
	}
}



class Address
{
	public $AddressID = 0;
	public $CustomerID = "";
	public $Contact = "";
	public $AddressType = "";
	public $Address1 = "";
	public $Address2 = "";
	public $City = "";
	public $State = "";
	public $Zip = "";
	public $Phone = "";
	
	public function __construct()
	{
	}
	
	public function DisplayFormatted()
	{
		$out = "";
		
		$out .= "			<div>".$this->Contact."</div>\n";
		$out .= "			<div>".$this->Address1."</div>\n";
		if (!isBlank($this->Address2))
		{
			$out .= "			<div>".$this->Address2."</div>\n";
		}
		$out .= "			<div>".$this->City.", ".$this->State."&nbsp;&nbsp;".$this->Zip."</div>\n";
		
		return $out;
	}
	
	public function GetAddress($addrid)
	{
		if ($addrid <= 0)
			throw new Exception("The address record could not be located.");
		
		$request = $this->CustomerApi."/customers/address/".$addrid;
		$rr = new RestRunner();
		$row = $rr->Get($request);
		if ($row)
		{
			$this->AddressID = $row->addrId;
			$this->CustomerID = $row->custId;
			$this->AddressType = $row->addrType;
			$this->Contact = $row->contact;
			$this->Address1 = $row->address1;
			$this->Address2 = $row->address2;
			$this->City = $row->city;
			$this->State = $row->state;
			$this->Zip = $row->zip;
			$this->Phone = $row->phone;
		}
		else
			throw new Exception("The address record could not be located.");
	}
	
	public function SaveAddress()
	{
		switch ($this->AddressType)
		{
			case "B":
				$this->Label = "Billing Address";
				break;
			case "S":
				$this->Label = "Shipping Address";
				break;
		}
		
		$request = $this->CustomerApi."/customers/address/".$this->CustomerID;
		$rr = new RestRunner();
		$retval = $rr->Put($request, $this->OutputJson());
	}

	public function OutputJson()
	{
		$out = "";

		$out .= "{";
		$out .= "    \"addrId\": ".$this->AddressID.",";
		$out .= "    \"custId\": ".$this->CustomerID.",";
		$out .= "    \"contact\": \"".$this->Contact."\",";
		$out .= "    \"address1\": \"".$this->Address1."\",";
		$out .= "    \"address2\": \"".$this->Address2."\",";
		$out .= "    \"city\": \"".$this->City."\",";
		$out .= "    \"state\": \"".$this->State."\",";
		$out .= "    \"zip\": \"".$this->Zip."\",";
		$out .= "    \"phone\": \"".$this->Phone."\",";
		$out .= "    \"addrType\": \"".$this->AddressType."\"";
		$out .= "}";
	
		return $out;
	}
}

class CreditCard
{
	public $ID = 0;
	public $RowID = "";
	public $CustID = "";
	public $CardName = "";
	public $CardNumber = "";
	public $CardType = "";
	public $CVV = "";
	public $ExpirationMonth = "";
	public $ExpirationYear = "";
	public $Save = false;
	
	const cryptcode = "1fadedbead";
	
	public function __construct()
	{
	}
	
	public function DisplayFormatted()
	{
		$out = "";
		
		$out .= "<div style=\"font-weight:bold;\">".$this->CardTypeName()."</div>\n";
		$out .= "<div>".$this->CardName."</div>\n";
		$out .= "<div>Ends with ".$this->HiddenCardNumber()."</div>\n";
		$out .= "<div>Expires ".$this->Expiration()."</div>\n";
		
		return $out;
	}
	
	public function DisplayStacked()
	{
		$out = "";
		
		$out .= "		<section class=\"three-col\">\n";
		$out .= "			<ul>\n";
		$out .= "				<li><h4>".$this->CardTypeName()."</h4></li>\n";
		$out .= "				<li>".$this->FormatCardNumber()."</li>\n";
		if (!isBlank($this->CVV))
		{
			$out .= "				<li>CVV: <span class=\"gray\">xxx</span></li>\n";
		}
		$out .= "				<li>".$this->CardName."</li>\n";
		$out .= "				<li>".$this->Expiration()."</li>\n";
		$out .= "				<li><a href=\"javascript:acctDeleteCard('".$this->RowID."');\" class=\"delete\">Delete</a></li>\n";
		$out .= "			</ul>\n";
		$out .= "		</section>\n";
		
		return $out;
	}
	
	public function IsValidCard()
	{
		if (trim($this->CardNumber) == "")
			return false;
		
		$len = strlen($this->CardNumber);
		$digit = intval(substr($this->CardNumber, -1, 1));
		$mult = 1;
		$total = 0;
		$tmp = 0;
		$i = 0;
		$new = 0;
		$tmpstr = "";
		
		for ($i = $len - 2; $i >= 0; $i--)
		{
			if ($mult == 1)
				$mult = 2;
			else
				$mult = 1;
			
			$tmp = intval(substr($this->CardNumber, $i, 1)) * $mult;
			if ($tmp > 9)
			{
				$tmpstr = strval($tmp);
				$total += intval(substr($tmpstr, 0, 1)) + intval(substr($tmpstr, 1, 1));
			}
			else
				$total += $tmp;
		}
		
		$new = $total;
		for ($i = 0; $i <=9; $i++)
		{
			if (($new % 10) === 0)
				break;
			else
				$new++;
		}
		
		if (($new - $total) === $digit)
			return true;
		else
			return false;
	}
	
	public function IsExpired()
	{
		if ($this->ExpirationYear < date("Y"))
			return true;
		
		if ($this->ExpirationYear == intval(date("Y")) && $this->ExpirationMonth < intval(date("n")))
			return true;
		
		return false;
	}
	
	public function HiddenCardNumber()
	{
		return substr($this->CardNumber, -4);
	}
	
	public function FormatCardNumber()
	{
		$hideit = true;

		if ($hideit)
		{
			switch($this->CardType)
			{
				case "AX";
					return "<span class=\"gray\">xxxx-xxxxxx-</span>".substr($this->CardNumber, -5);
				default:
					return "<span class=\"gray\">xxxx-xxxx-xxxx-</span>".substr($this->CardNumber, -4);
			}
		}
		else
		{
			switch($this->CardType)
			{
				case "AX";
					return "<span class=\"gray\">".substr($this->CardNumber, 0, 4)."-".substr($this->CardNumber, 4, 6)."-</span>".substr($this->CardNumber, -5);
				default:
					return "<span class=\"gray\">".substr($this->CardNumber, 0, 4)."-".substr($this->CardNumber, 4, 4)."-".substr($this->CardNumber, 8, 4)."-</span>".substr($this->CardNumber, -4);
			}
		}
	}
	
	public function Expiration()
	{
		return date('F', mktime(0, 0, 0, $this->ExpirationMonth, 1))." ".$this->ExpirationYear;
	}
	
	public function CardTypeName()
	{
		switch (strtoupper($this->CardType))
		{
			case "VS":
				return "Visa";
			case "MC":
				return "MasterCard";
			case "AX":
				return "American Express";
			case "DI":
				return "Discover Card";
			default:
				return "Unknown";
		}
	}
	
	public function DecodeNumber($instr)
	{
		$retval = "";
		
		$tmp = pack("H*", str_replace("%", "", $instr));
		$retval = rc4(self::cryptcode, $tmp);
		
		return $retval;
	}
	
	public function EncodeNumber($instr)
	{
		$retval = "";
		
		$tmp = unpack("H*hex", rc4(self::cryptcode, $instr));
		for ($i = 0; $i < strlen($tmp["hex"]); $i += 2)
		{
			$retval .= "%".substr($tmp["hex"], $i, 2);
		}
		
		return strtoupper($retval);
	}

	public function OutputJson()
	{
		$out = "";

		$out .="{";
		$out .="	\"payId\": ".$this->ID.", ";
		$out .="	\"custId\": ".$this->CustID.", ";
		$out .="	\"cardName\": \"".$this->CardName."\", ";
		$out .="	\"cardNumber\": \"".$this->CardNumber."\", ";
		$out .="	\"cardType\": \"".$this->CardType."\", ";
		$out .="	\"cvv\": \"".$this->CVV."\", ";
		$out .="	\"expirationMonth\": \"".$this->ExpirationMonth."\", ";
		$out .="	\"expirationYear\": \"".$this->ExpirationYear."\" ";
		$out .="}";
		
		return $out;
	}
}





?>