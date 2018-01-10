<?php
namespace MonumX\Exceptions;

class NotFoundException extends ApplicationException {
    public function __construct() {
        $errorMessage = 'Resource not found';
        $data = array(
            'http_status_code' => 404,
            'backtrace' => false
        );

        parent::__construct($errorMessage, $data);
    }
}
?>