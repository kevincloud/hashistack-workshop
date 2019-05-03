<?php
include_once("../ini.php");
include_once("../classes/LandingCart.php");
include_once("../classes/LandingAjax.php");

$ajax = new LandingAjax($db);

$ajax->Process();



?>