<?php
namespace MonumX\HTTP;

use MonumX\Http\Requester;
use MonumX\Exceptions\BadRequestException;

class Request {
    public $requester = null;
    private $_urlParams = array();
    private $_getParams = array();
    private $_dataParams = array();

    public function __construct(array $urlParams = array(), array $getParams = array(), array $dataParams = array()) {
        $this->_urlParams = $urlParams;
        $this->_getParams = $getParams;
        $this->_dataParams = $dataParams;

        $this->requester = new Requester();
    }

    // Request params
    public function urlParam(string $key) {
        $key = strtolower($key);
        if (isset($this->_urlParams[$key])) {
            return $this->_urlParams[$key];
        } else {
            return false;
        }
    }

    // URL GET params
    public function getParam(string $key, bool $required = false) {
        $key = strtolower($key);
        if (isset($this->_getParams[$key])) {
            return $this->_getParams[$key];
        } else {
            if ($required) {
                throw new BadRequestException('GET parameter \'' . $key . '\' is required but was not set');
            }
            return false;
        }
    }

    // Data (POST, PUT etc) params
    public function dataParam($key, bool $required = false) {
        $key = strtolower($key);
        if (isset($this->_getParams[$key])) {
            return $this->_getParams[$key];
        } else {
            if ($required) {
                throw new BadRequestException('Field \'' . $key . '\' is required but was not set');
            }
            return false;
        }
    }

    // Request method
    public function method() {
        return strtolower($_SERVER['REQUEST_METHOD']);
    }

    public function isMethod($method) {
        $method = strtolower($method);
        return $this->method() == $method;
    }

    // Request headers
    public function headers() {
        if (function_exists('apache_request_headers')) {
            $headers = apache_request_headers();
        } else {
            $headers = array();
            $http_regex = '/\AHTTP_/';

            foreach ($_SERVER as $key => $value) {
               if (preg_match($http_regex, $key)) {
                 $header_key = preg_replace($http_regex, '', $key);
                 $matches = array();
                 $matches = explode('_', $header_key);
                 if (count($matches) > 0 and strlen($header_key) > 2 ) {
                     foreach ($matches as $ak_key => $ak_val)
                        $matches[$ak_key] = ucfirst($ak_val);
                    $header_key = implode('-', $matches);
                 }
                 $headers[strtoupper($header_key)] = $value;
               }
             }
        }

        return $headers;
    }

    public function header($key) {
        $key = strtoupper($key);
        $headers = $this->headers();

        if (isset($headers[$key])) {
            return $headers[$key];
        } else {
            return false;
        }
    }

    // HTTPS
    public function isSecure() {
        return ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') || (!empty($_SERVER['HTTP_HTTPS']) && $_SERVER['HTTP_HTTPS'] != 'off') || $_SERVER['REQUEST_SCHEME'] == 'https' || $_SERVER['SERVER_PORT'] == 443) ? true : false;
    }
}
?>
