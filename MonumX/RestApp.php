<?php
namespace MonumX;

use MonumX\Config;
use MonumX\Routing\Router;
use MonumX\Exceptions\ExceptionHandler;

class RestApp {
    const VERSION = '0.2.1';
    public static $time;

    public function __construct() {
	      self::$time = microtime(true);
        // Enable output buffering so we may clear it later if needed
        ob_start();

        // Set exception handler
        ExceptionHandler::setHandler();
        
        // Load the config file
        Config::load();
    }
    public function __destruct() {
        // echo '<br>Run Time: ' . number_format(microtime(true) - self::$time, 10) ;
    }

    public function run() {
        // Execute route
        Router::run();
    }

    public function addMiddleware(string $middlewareName) {
        // Todo
    }
}
?>