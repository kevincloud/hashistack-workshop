<?php
include_once("framework/ini.php");
include_once("framework/classes/CartHandler.php");
include_once("framework/classes/ShippingUPS.php");
include_once("framework/classes/ShippingUSPS.php");

$page = new CartHandler();

$page->Run();



?>