<?php
namespace MonumX\Exceptions;

use MonumX\Http\Response;
use MonumX\Exceptions\Backtrace;

class ExceptionHandler {
    public static function setHandler() {
        @set_exception_handler(array(__CLASS__, '_handleException'));
        @set_error_handler(array(__CLASS__, '_handleErrors'));
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
        $errorCode = $data['http_status_code'] ?? 500;
        $errorCode = 200;
        $errorBacktrace = $backtrace ?? null;

        $response = new Response();
        $response->error($errorMessage, $errorCode, $errorBacktrace);
    }

    public static function _handleErrors($errno, $error, $file, $line) {
        if (!(error_reporting() & $errno)) {
            return false;
        }

        $backtrace = new Backtrace(array(
            array(
                'error' => $error,
                'file' => $file,
                'line' => $line
            )
        ));

        $response = new Response();
        $response->error('Internal PHP error', 200, $backtrace);

        switch ($errno) {
            case E_ERROR:
                break;
            case E_WARNING:
                break;
            case E_PARSE:
                break;
        }
        die("PHP ERROR");
    }
}
?>