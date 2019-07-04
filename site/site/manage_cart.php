<?php
include_once("framework/ini.php");
echo "<p>made it 1</p>";
include_once("framework/classes/CartHandler.php");
echo "<p>made it 2</p>";

$page = new CartHandler();

$page->Run();



?>