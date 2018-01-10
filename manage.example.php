#!/usr/bin/php
<?php
require_once("MonumX/autoloader.php");

$cli = new MonumX\CliApp($argv);
$cli->run();
?>