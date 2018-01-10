<?php
namespace MonumX;

use MonumX\Config;
use MonumX\Cli\{Cli, Tasks};
use MonumX\Exceptions\CliExceptionHandler;

class CliApp {
    public function __construct(array $arguments) {
        if (php_sapi_name() != 'cli') {
            die();
        }

        // Set exception handler
        CliExceptionHandler::setHandler();

        // Set arguments
        Cli::setArguments($arguments);

        // Load the config file
        Config::load();
    }

    public function run() {
        Tasks::run();
    }
}
?>