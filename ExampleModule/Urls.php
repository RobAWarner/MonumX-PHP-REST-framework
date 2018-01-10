<?php
use MonumX\Routing\Router;

Router::register("/v1/example/<someParam|string:17:19>", "ExampleView");
?>