<?php
include_once("../ini.php");
include_once("../classes/ProductAjax.php");

$ajax = new ProductAjax($db);

$ajax->Process();


?>