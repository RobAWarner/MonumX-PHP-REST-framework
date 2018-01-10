<?php
namespace MonumX\Exceptions;

class MethodNotAllowedException extends ApplicationException {
    public function __construct() {
        $errorMessage = 'Method not allowed';
        $data = array(
            'http_status_code' => 405,
            'backtrace' => false
        );

        parent::__construct($errorMessage, $data);
    }
}
?>