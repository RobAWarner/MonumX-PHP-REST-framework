<?php
namespace MonumX\Exceptions;

class BadRequestException extends ApplicationException {
    public function __construct(string $errorMessage = '') {
        $errorMessage = 'Bad request' . (strlen($errorMessage) > 0 ? ', ' . $errorMessage : '');
        $data = array(
            'http_status_code' => 400,
            'backtrace' => false
        );

        parent::__construct($errorMessage, $data);
    }
}
?>