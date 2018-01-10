<?php
namespace MonumX\Exceptions;

class ApplicationException extends \Exception {
    public function __construct(string $errorMessage = '', array $data = array()) {
        parent::__construct($errorMessage);

        if (isset($data['backtrace']) && !empty($data['backtrace'])) {
            $this->backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        }

        if (is_array($data) && count($data) > 0) {
            $this->data = $data;
        }
    }

    public function getBacktrace() {
        return $this->backtrace ?? null;
    }

    public function getData() {
        return $this->data ?? array();
    }

    public function getHTTPStatusCode(int $default = 500) {
        if (isset($this->data) && isset($this->data['HTTP_STATUS_CODE']) && is_numeric($this->data['HTTP_STATUS_CODE'])) {
            return $this->data['HTTP_STATUS_CODE'];
        } else {
            return $default;
        }
    }
}
?>