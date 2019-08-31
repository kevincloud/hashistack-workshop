<?php

//////////////////////////////////////////////////////////////////////////////////
// PHP AND SERVER INITIALIZATION
//////////////////////////////////////////////////////////////////////////////////

// if ($_SERVER['SERVER_PORT'] != 443)
// {
// 	header("HTTP/1.1 301 Moved Permanently");
// 	header("Location: https://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
// 	exit();
// }

// Set error reporting for development mode
ini_set('display_errors', 'On');
ini_set('display_startup_errors', 'On');
error_reporting(E_ALL);
$region = "us-east-1";

// ***INLINESQL***
// include_once("plugins/ezsql/ez_sql_core.php");
// include_once("plugins/ezsql/ez_sql_mssql.php");

// AUTO INCLUDE LOCAL CLASSES
//spl_autoload_register(function ($class) {
//    include_once 'classes/' . $class . '.php';
//});

include_once("classes/ApplicationSettings.php");
include_once("classes/Utilities.php");
include_once("classes/BasePage.php");
include_once("classes/RestRunner.php");
include_once("classes/AjaxHandler.php");
include_once("classes/Product.php");
include_once("classes/Account.php");
include_once("classes/Order.php");
include_once("classes/Invoice.php");
include_once("classes/ShoppingCart.php");

if(file_exists('config.php'))
    include_once("config.php");

// Set the default time zone
date_default_timezone_set('America/New_York');

// set the Content-Type header with character set
header('Content-Type: text/html; charset=iso-8859-1');

// Initialize the session
@session_start();

// Regenerate session ID to reduce risk of session cloning
// if (!isset($_SESSION['userID']))
// {
// 	session_regenerate_id();
// }

// Open and clear the output buffer with gzip compression enabled
ob_start("ob_gzhandler");
ob_implicit_flush(false);



//////////////////////////////////////////////////////////////////////////////////
// API INITIALIZATION
//////////////////////////////////////////////////////////////////////////////////

$authapi = "http://auth-api.service.".$region.".consul:5825";
$productapi = "http://product-api.service.".$region.".consul:5821";
$customerapi = "http://customer-api.service.".$region.".consul:5822";
$cartapi = "http://cart-api.service.".$region.".consul:5823";
$vaulturl = "http://vault-main.service.".$region.".consul:8200";
$orderapi = "http://order-api.service.".$region.".consul:5826";

//////////////////////////////////////////////////////////////////////////////////
// UNIVERSAL FUNCTIONS INITIALIZATION
//////////////////////////////////////////////////////////////////////////////////

include_once("functions.php");





?>
