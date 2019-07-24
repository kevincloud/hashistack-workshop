<?php

class ApplicationSettings
{
	public $SiteURL = "";
	public $SSLURL = "";
	public $SiteHost = "";
	
	public function __construct()
	{
		$this->Load();
	}

	public function Load()
	{
		$this->SiteHost = "www.java-perks.com";
		$this->SiteURL = "http://www.java-perks.com";
		$this->SSLURL = "http://www.java-perks.com";
	}
}







?>