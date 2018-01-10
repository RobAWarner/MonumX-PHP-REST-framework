<?php
namespace MonumX\Cli;

use MonumX\Cli\Cli;

class Response {
    public function print($content) {
        Cli::print($content);
    }

    public function printColor($content, string $color) {
        Cli::printColor($content, $color);
    }

    public function printSuccess(string $message) {
        Cli::printColor($message, 'green');
    }

    public function error(string $message, bool $end = true) {
        Cli::printColor("\033[4m" . 'ERROR: ' . $message, 'red');
        if ($end) {
            die();
        }
    }

    public function exception(string $message, $backtrace = null) {
        Cli::printSectionColor('ERROR CAUGHT', $message, 'red');
        if (!is_null($backtrace) && $backtrace instanceof \MonumX\Exceptions\Backtrace) {
            Cli::printSectionColor('ERROR BACKTRACE', $backtrace->get(), 'yellow');
        }
        die();
    }
}
?>