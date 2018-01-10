<?php
namespace MonumX\Exceptions;

class ModelException extends ApplicationException {
    public function __construct(string $errorMessage = '', array $data = array()) {
        $errorMessage = strlen($errorMessage) > 0 ? $errorMessage : 'An unknown model/query error occured';
        $data = array(
            'http_status_code' => 500,
            'backtrace' => true
        );

        parent::__construct($errorMessage, $data);
    }
}
?>