<?php
namespace MonumX\Exceptions;

use MonumX\Files\Paths;

class Backtrace {
    private $_backtrace = array();
    
    public function __construct($backtrace = array(), $exception = null) {
        if (!is_array($backtrace) || empty($backtrace)) {
            // Get the backtrace
            $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        }

        if (!is_null($exception)) {
            $exceptionTrace = array(
                'file' => $exception->getFile() ?? 'unknown',
                'line' => $exception->getLine() ?? 'unknown'
            );
            array_unshift($backtrace, $exceptionTrace);
        }

        // Remove some sensitive information
        foreach ($backtrace as &$trace) {
            if (isset($trace['file'])) {
                $trace['file'] = Paths::removePathFromString($trace['file']);
            }
            if (isset($trace['args'])) {
                unset($trace['args']);
            }
        }

        $this->_backtrace = $backtrace;
    }

    public function get() {
        return $this->_backtrace;
    }

    public function readable() {
        $readableBacktrace = "";
        foreach ($this->_backtrace as $index => $trace) {
            $readableBacktrace .= '#' . $index . ' ' . ($trace['file'] ?? '?') . ' line ' . ($trace['line'] ?? '?') . ': ' . ($trace['function'] ?? '?') . PHP_EOL;
        }
        return $readableBacktrace;
    }

    public function debugPrint() {
        print("<pre>" . $this->readable() . "</pre>");
    }
}
?>