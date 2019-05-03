<?php

class ApplicationSettings
{
	public $SiteURL = "";
	public $SSLURL = "";
	public $SiteHost = "";
	
	// ***INLINESQL***
	// private $_db;
	
	// public function __construct($db)
	// {
	// 	$this->_db = $db;
		
	// 	$this->Load();
	// }
	
	public function __construct()
	{
		$this->Load();
	}

	public function Load()
	{
		$this->SiteHost = "www.cloudapp.com";
		$this->SiteURL = "http://www.cloudapp.com";
		$this->SSLURL = "http://www.cloudapp.com";
	}
}







?>