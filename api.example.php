<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
require_once("MonumX/autoloader.php");

header("Access-Control-Allow-Origin: *");

$app = new MonumX\RestApp();
$app->run();
?>