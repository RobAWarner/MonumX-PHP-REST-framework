<?php
namespace MonumX\Exceptions;

class RoutingException extends ApplicationException {
    public function __construct(string $errorMessage = '', array $data = array()) {
        $errorMessage = strlen($errorMessage) > 0 ? $errorMessage : 'An unknown routing error occured';
        $data = array(
            'http_status_code' => 500,
            'backtrace' => false
        );

        parent::__construct($errorMessage, $data);
    }
}
?>