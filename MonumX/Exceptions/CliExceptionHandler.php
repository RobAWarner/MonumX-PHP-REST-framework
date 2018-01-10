<?php
namespace MonumX\Exceptions;

use MonumX\Cli\{Cli, Response};

class CliExceptionHandler {
    public static function setHandler() {
        @set_exception_handler(array(__CLASS__, '_handleException'));
    }

    public static function _handleException($exception) {
        // Print a backtrace?
        $data = method_exists($exception, 'getData') ? $exception->getData() : array();
        if ($data['backtrace'] ?? true) {
            if (method_exists($exception, 'getBacktrace')) {
                $backtrace = new Backtrace($exception->getBacktrace(), $exception);
            } else if (method_exists($exception, 'getTrace')) {
                $backtrace = new Backtrace($exception->getTrace(), $exception);
            } else {
                $backtrace = new Backtrace(null, $exception);
            }
        }

        // Error info
        $errorMessage = 'ERROR: ' . $exception->getMessage();
        $errorBacktrace = $backtrace ?? null;

        $response = new Response();
        $response->exception($errorMessage, $errorBacktrace);
    }
}
?>