<?php
namespace MonumX\Http;

class Response {
    private $_outputBuffer = array();
    private $_httpStatusCode = null;
    private $_headers = array();
    private $_contentType;

    public function __construct() {
        // Request content type
        if (preg_match('/^text\/html\,/i', $_SERVER['HTTP_ACCEPT'])) {
            $this->_contentType = 'html';
            $this->_headers['Content-Type'] = 'text/html; charset=utf-8';
        } else {
            $this->_contentType = 'json';
            $this->_headers['Content-Type'] = 'application/json; charset=utf-8';
        }
    }

    // Add key/value to the output
    public function addValue(string $key, $content) {
        $this->_outputBuffer[$key] = $content;
    }

    // Add array to the output
    public function serialize(array $content) {
        $this->_outputBuffer = array_merge($this->_outputBuffer, $content);
    }

    // Render an error
    public function error(string $error, int $code = 500, $backtrace = null) {
        // Clear output
        ob_end_clean();

        // Set HTTP status code
        http_response_code($code);

        if ($this->_contentType == 'html') {
            print($this->_safeHtml($error) . "<br />" . PHP_EOL);
            if (!is_null($backtrace) && $backtrace instanceof \MonumX\Exceptions\Backtrace) {
                print('<pre>' . $this->_safeHtml(print_r($backtrace->get(), true)) . '</pre>');
            }
        } else {
            // JSON output
            $output = array('error' => $error);
            if (!is_null($backtrace) && $backtrace instanceof \MonumX\Exceptions\Backtrace) {
                $output['backtrace'] = $backtrace->get();
            }

            $encoded = json_encode($output);
            if ($encoded) {
                print ($encoded);
            }
        }

        die();
    }

    // Clear output
    public function clear() {
        $this->$_outputBuffer = array();
    }

    // Set status code
    public function status(int $code) {
        $this->_httpStatusCode = $code;
    }

    // Add a header
    public function header(string $header, $value) {
        $this->_headers[$header] = $value;
    }

    // Execute the response
    public function execute(bool $endApp = true, bool $clearOutput = true) {
        if ($clearOutput) {
            ob_end_clean();
        }

        if ($this->_httpStatusCode != null) {
            http_response_code($this->_httpStatusCode);
        }

        foreach($this->_headers as $header => $value) {
            header(trim($header) . ': ' . trim($value));
        }

        if ($this->_contentType == 'html') {
            print('<pre>' . $this->_safeHtml(print_r($this->_outputBuffer, true)) . '</pre>');
        } else {
            print($this->_getOutput());
        }

        if ($endApp) {
            die();
        }
    }

    private function _getOutput() {
        // Merge array
        $encoded = json_encode($this->_outputBuffer);
        return $encoded === false ? '' : $encoded;
        // $output = '';
        // foreach ($this->_outputBuffer as $key => $content) {
        //     if (is_array($content) || is_object($content)) {
        //         if (is_string($key)) {
        //             $encoded = json_encode(array($key => $content));
        //         } else {
        //             $encoded = json_encode($content);
        //         } 
        //         if ($encoded) {
        //             $output .= $encoded;
        //         }
        //     } else {
        //         $output .= $content;
        //     }
        // }
        // return $output;
    }

    private function _safeHtml($input) {
        if (is_array($input)) {
            $return = array();
            foreach($input as $key => $value) {
                $return[$key] = $this->_safeHtml($value);
            }
        } else {
            $return = htmlspecialchars($input, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }
        return $return;
    }
}
?>