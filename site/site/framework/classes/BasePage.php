<?php

abstract class BasePage
{
	private $_defpath = "/framework";
	private $_styles = array();
	private $_scripts = array();
	private $_headitems = array();
	private $_menuitems = array();
	
	private $_title = "Java Perks - Online Coffee Equipment Store";
	private $_description = "A wide variety of coffee products to make the most of your anytime-time-of-the-day coffee";
	private $_copyright = "&copy; Copyright HashiCorp 2019. All Rights Reserved.";
	private $_author = "HashiCorp";
	private $_pagename = "Java Perks";
	private $_storeid = 1;
	private $_loggedin = false;
	private $_landingpage = false;
	private $ProductApi;
	private $VaultUrl;
	
	protected $Cart;
	protected $Account;
	protected $PageVariables = array();
	protected $Action = "";
	protected $Post = false;
	protected $Get = false;
	protected $Options = array();
	
	/*
	Public Member Functions
	*/
		
	public function __construct()
	{
		global $productapi;
		set_error_handler(array("BasePage", "ErrorHandler"));
		
		$this->ProductApi = $productapi;
		$this->Initialize();
	}
	
	protected function Initialize()
	{
		$this->Action = "";
		
		$this->_copyright = "&copy; Copyright HashiCorp ".date("Y").". All Rights Reserved.";
		
		$this->AddStyleSheet("/framework/css/style.css");
		$this->AddStyleSheet("/framework/css/jquery-ui.css");
		$this->AddJavascript("/framework/js/jquery.min.js");
		$this->AddJavascript("/framework/js/jquery-ui.min.js");
		$this->AddJavascript("/framework/js/functions.js");
		$this->AddJavascript("/framework/js/modernizr-1.7.min.js");
		$this->AddJavascript("/framework/js/cart.js");
		
		$this->AddMenuItem("Brewing Equipment", "/products/categories/Brewing-Equipment");
		$this->AddMenuItem("Barista Tools", "/products/categories/Barista-Tools");
		$this->AddMenuItem("Espresso Machines", "/products/categories/Espresso-Machines");
		$this->AddMenuItem("Appliances", "/products/categories/Kitchen-Appliances");
		
		foreach ($_GET as $key => $value)
		{
			if ($key == "action")
			{
				if ($this->Action == "")
				{
					$this->Action = strtolower(trim($value));
					$this->Get = true;
				}
			}
			else
			{
				if (!array_key_exists($key, $this->PageVariables))
				{
					$this->PageVariables[$key] = $value;
				}
			}
		}
		
		foreach ($_POST as $key => $value)
		{
			if ($key == "action")
			{
				if ($this->Action == "")
				{
					$this->Action = strtolower(trim($value));
					$this->Post = true;
				}
			}
			else
			{
				if (!array_key_exists($key, $this->PageVariables))
				{
					$this->PageVariables[$key] = $value;
				}
			}
		}

		foreach ($_REQUEST as $key => $value)
		{
			if ($key == "action")
			{
				if ($this->Action == "")
					$this->Action = strtolower(trim($value));
			}
			else
			{
				if (!array_key_exists($key, $this->PageVariables))
				{
					$this->PageVariables[$key] = $value;
				}
			}
		}
		
		if (!isset($_SESSION["__account__"]))
		{
			$_SESSION["__account__"] = new Account();
			
			if (isset($_COOKIE["__custid__"]))
			{
				if ($_COOKIE["__custid__"] != "")
				{
					$_SESSION["__account__"]->GetAccount($_COOKIE["__custid__"]);
					// ***INLINESQL***
					// $sql = "update pw_customer_reset set dateused = getdate(), bypassed = 1 where custid = ".smartQuote($_COOKIE["__custid__"])." and dateused is null";
					// $this->_db->query($sql);
				}
			}
		}
		
		if (!isset($_SESSION["__cart__"]))
			$_SESSION["__cart__"] = new ShoppingCart();
		
		$this->Cart = &$_SESSION["__cart__"];
		$this->Account = &$_SESSION["__account__"];
		$this->_loggedin = $this->Account->LoggedIn();
	}
	
	public static function ErrorHandler($errno, $errstr, $errfile, $errline)
	{
		// ***INLINESQL***
		// global $db;
		
		$userid = "";
		$account = NULL;
		$trace = array_reverse(debug_backtrace());
		$text = "";
		$actual_link = "https://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
		
		if (isset($_SESSION["__account__"]))
		{
			$account = $_SESSION["__account__"];
			if (isset($account->CustomerID))
				$userid = $account->CustomerID;
		}
		
		array_pop($trace);
		
		foreach($trace as $item)
		{
			$text .= (isset($item['file']) ? $item['file'] : '<unknown file>') . ' ' . (isset($item['line']) ? $item['line'] : '<unknown line>') . ' calling ' . $item['function'] . '()' . "\n";
		}
		
		// ***INLINESQL***
		// $sql = "insert into cc_store_errors(err_number, err_message, err_file, err_line, err_trace, ip_address, referrer, custid, url) values(".smartQuote($errno).", ".smartQuote($errstr).", ".smartQuote($errfile).", ".smartQuote($errline).", ".smartQuote($text).", ".smartQuote($_SERVER['REMOTE_ADDR']).", ".smartQuote($_SERVER['HTTP_REFERER']).", ".smartQuote($userid).", ".smartQuote($actual_link).")";
		// $db->query($sql);
		
		/* Don't execute PHP internal error handler */
		return true;
	}
	
	public function SetAction($action)
	{
		$this->Action = $action;
	}
	
	public function SetOption($key, $value)
	{
		$this->Options[$key] = $value;
	}
	
	protected function Redirect($url)
	{
		header("Location: ".$url);
		exit();
	}
	
	abstract protected function Run();
	
	public function BeginPage()
	{
		echo $this->PageOpen();
		echo $this->Head();
		echo $this->BodyOpen();
	}
	
	public function EndPage()
	{
		echo $this->BodyClose();
		echo $this->PageClose();
	}

	public function AddMetaData($type, $key, $content)
	{
		$this->_metaitems[] = array('type' => $type, 'key' => $key, 'content' => $content);
	}
	
	public function AddStyleSheet($path)
	{
		$this->_styles[] = $path;
	}
	
	public function AddJavascript($path)
	{
		$this->_scripts[] = $path;
	}
	
	public function SetTitle($title)
	{
		$this->_title = $title;
	}
	
	public function SetDescription($descr)
	{
		$this->_description = $descr;
	}
	
	public function SetPageName($pagename)
	{
		$this->_pagename = $pagename;
	}
	
	public function SetStore($storeid)
	{
		$this->_storeid = $storeid;
	}
	
	public function AddMenuItem($label, $link)
	{
		$this->_menuitems[] = array($label, $link);
	}
	
	
	/*
	Private Member Functions
	*/
	
	
	private function PageOpen()
	{
		$out = "";
		
		$out .= "<!doctype html>\n";
		$out .= "<!--[if lt IE 7 ]> <html class=\"ie ie6 no-js\" lang=\"en\" xmlns:og=\"http://ogp.me/ns#\"> <![endif]-->\n";
		$out .= "<!--[if IE 7 ]>    <html class=\"ie ie7 no-js\" lang=\"en\" xmlns:og=\"http://ogp.me/ns#\"> <![endif]-->\n";
		$out .= "<!--[if IE 8 ]>    <html class=\"ie ie8 no-js\" lang=\"en\" xmlns:og=\"http://ogp.me/ns#\"> <![endif]-->\n";
		$out .= "<!--[if IE 9 ]>    <html class=\"ie ie9 no-js\" lang=\"en\" xmlns:og=\"http://ogp.me/ns#\"> <![endif]-->\n";
		$out .= "<!--[if gt IE 9]><!--><html class=\"no-js\" lang=\"en\" xmlns:og=\"http://ogp.me/ns#\"><!--<![endif]-->\n";
		$out .= "<!-- the \"no-js\" class is for Modernizr. -->\n";
		
		return $out;
	}
	
	private function MetaTags()
	{
		$out = "";
		
		$out .= "<meta charset=\"iso-8859-1\">\n";
		$out .= "<!-- Always force latest IE rendering engine (even in intranet) & Chrome Frame -->\n";
		$out .= "<meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge,chrome=1\">\n";
		$out .= "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=iso-8859-1\">\n";
		$out .= "<meta name=\"title\" content=\"".$this->_title."\">\n";
		$out .= "<meta name=\"description\" content=\"".$this->_description."\">\n";
		$out .= "<meta name=\"author\" content=\"".$this->_author."\">\n";
		$out .= "<meta name=\"copyright\" content=\"".$this->_copyright."\">\n";
		
		return $out;
	}
	
	private function ExtraMetaData()
	{
		$out = "";
		
		if (isset($this->_metaitems))
		{
			foreach ($this->_metaitems as $data)
			{
				$out .= "<meta ".$data['type']."=\"".$data['key']."\" content=\"".$data['content']."\">\n";
			}
		}
		
		return $out;
	}
	
	private function Icons()
	{
		$out = "";
	
		$out .= "<link rel=\"shortcut icon\" type=\"image/x-icon\" href=\"".$this->_defpath."/img/favicon.ico\">\n";
		$out .= "<link rel=\"apple-touch-icon\" href=\"".$this->_defpath."/img/favicon.png\">\n";
		$out .= "<link rel=\"icon\" type=\"image/png\" href=\"".$this->_defpath."/img/favicon.png\" />\n";
		
		return $out;
	}
	
	private function StyleSheets()
	{
		$out = "";
		
		if (isset($this->_styles))
		{
			foreach ($this->_styles as $path)
			{
				$out .= "<link rel=\"stylesheet\" href=\"".$path."\">\n";
			}
		}
		
		return $out;
	}
	
	private function Javascript()
	{
		$out = "";
	
		if (isset($this->_scripts))
		{
			foreach ($this->_scripts as $path)
			{
				$out .= "<script type=\"text/javascript\" src=\"".$path."\"></script>\n";
			}
		}
		
		return $out;
	}
	
	private function Head()
	{
		$out = "";
		
		$out .= "<head id=\"www-java-perks-com\" data-template-set=\"html5-reset\">\n";
		$out .= "<title>".$this->_title."</title>\n";
		$out .= $this->MetaTags();
		$out .= $this->ExtraMetaData();
		$out .= $this->Icons();
		$out .= $this->StyleSheets();
		$out .= $this->Javascript();
		$out .= "</head>\n";
		
		return $out;
	}
	
	private function Header()
	{
		$out = "";
	
		$out .= "<header>\n";
		$out .= "	<div id=\"info\">\n";
		$out .= "		<div id=\"phone\"><em>Order by Phone</em> <span id=\"number\">888.867.5309</span></div>\n";
		if ($this->_landingpage === false)
		{
			$out .= "		<nav id=\"customer-info\">\n";
			$out .= "			<ul>\n";
			$c = $_SESSION["__cart__"]->Count();
			if ($c == 1)
				$out .= "				<li><a href=\"/shop/cart/view\">My Cart: <strong>1 Item</strong></a></li>\n";
			else
				$out .= "				<li><a href=\"/shop/cart/view\">My Cart: <strong>".$c." Items</strong></a></li>\n";
			if ($this->_loggedin)
			{
				$out .= "				<li>Welcome ".$_SESSION["__account__"]->FirstName."!</li>\n";
				$out .= "				<li><a href=\"/profile/view\">My Account</a></li>\n";
				$out .= "				<li><a href=\"/profile/logout\">Sign Out</a></li>\n";
			}
			else
			{
				$out .= "				<li><a href=\"/profile/login\">Sign In</a></li>\n";
			}
			$out .= "			</ul>\n";
			$out .= "		</nav>\n";
		}
		$out .= "	</div>\n";
		if ($this->_landingpage === false)
			$out .= "	<h1><a id=\"logo\" href=\"/\">".$this->_pagename."</a></h1>\n";
		else
			$out .= "	<h1><a id=\"logo\" href=\"#\">".$this->_pagename."</a></h1>\n";
		$out .= "</header>\n";
		
		return $out;
	}
	
	private function Menu()
	{
		$out = "";
		
		$out .= "<nav class=\"main\">\n";
		$out .= "	<div class=\"search-box\">\n";
		$out .= "		<form action=\"/products/search\" method=\"post\"><input class=\"defaultText\" name=\"keywords\" type=\"text\" title=\"Search\"><input type=\"hidden\" name=\"action\" value=\"search\"></form>\n";
		$out .= "	</div>\n";
		$out .= "	<ol>\n";
		$out .= "		<li class=\"home\"><a href=\"/\">Home</a>\n";
		foreach ($this->_menuitems as $item)
		{
			$out .= "		<li><a href=\"".$item[1]."\">".$item[0]."</a></li>\n";
		}
		$out .= "	</ol>\n";
		$out .= "</nav>\n";
		
		return $out;
	}
	
	private function BodyOpen()
	{
		$out = "";
	
		$out .= "<body>\n";
		
		$out .= "<div class=\"wrapper\">\n";
		
		$out .= $this->Header();
		$out .= $this->Menu();
		
		$out .= "<div id=\"page\">\n";
		
		return $out;
	}
	
	private function BodyClose()
	{
		$out = "";
		
		$out .= "	<aside class=\"sidebar\">\n";
		$out .= $this->SidebarShop();
		$out .= $this->SidebarCategories();
		$out .= "	</aside>\n";
		$out .= "</div>\n";
		$out .= "<footer>\n";
		$out .= "	<p>".$this->_copyright."</p>\n";
		$out .= "</footer>\n";
		$out .= "</div>\n";
		$out .= "</body>";
		
		return $out;
	}
	
	private function SidebarShop()
	{
		$out = "";
		
		$out .= "		<nav class=\"shop\">\n";
		$out .= "			<h4>Shop</h4>\n";
		$out .= "			<ul>\n";
		$out .= "				<li><a href=\"/products/new-releases\">New Releases</a></li>\n";
		$out .= "				<li><a href=\"/products/coming-soon\">Coming Soon</a></li>\n";
		$out .= "				<li><a href=\"/products/popular\">Popular Products</a></li>\n";
		$out .= "			</ul>\n";
		$out .= "		</nav>\n";
		
		return $out;
	}
	
	private function SidebarCategories($catid=NULL)
	{
		$out = "";

		$rr = new RestRunner();
		$rs = $rr->Get($this->ProductApi."/category");
		if (!$rs)
			return $out;

		$out .= "		<nav class=\"categories\">\n";
		$out .= "			<h4>Browse</h4>\n";
		$out .= "			<ul style=\"list-style-type:none !important;\">\n";
		foreach ($rs as $cat)
		{
			$out .= "				<li style=\"list-style:none !important;\"><a href=\"/products/categories/".str_replace(" ", "-", $cat)."\">".$cat."</a></li>\n";
		}
		$out .= "			</ul>\n";
		$out .= "		</nav>\n";
		
		return $out;
	}
	
	private function PageClose()
	{
		$out = "";
		
		$out .= "</html>\n";
		
		return $out;
	}
}

?>