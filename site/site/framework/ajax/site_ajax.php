<?php
include_once("../ini.php");
include_once("../classes/SiteAjax.php");

$ajax = new SiteAjax($db);

$ajax->Process();



?>