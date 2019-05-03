<?php
include_once("../ini.php");
include_once("../classes/AccountAjax.php");

$ajax = new AccountAjax($db);

$ajax->Process();



?>