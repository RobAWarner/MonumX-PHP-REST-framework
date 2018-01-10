<?php
namespace MonumX\Exceptions;

class ConfigurationException extends ApplicationException {
    public function __construct(string $errorMessage = '', string $configGroup = '', string $configKey = '') {
        $errorMessage = strlen($errorMessage) > 0 ? $errorMessage : 'An unknown configuration error occured';
        if (strlen($configGroup) > 0 && strlen($configKey) > 0) {
            $errorMessage = $configGroup . '.' . $configKey . ': ' . $errorMessage;
        }
        $data = array(
            'http_status_code' => 500,
            'backtrace' => false
        );

        parent::__construct($errorMessage, $data);
    }
}
?>