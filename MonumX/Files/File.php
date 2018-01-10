<?php
namespace MonumX\Files;

use MonumX\Files\Paths;

class File {
    private $_filePath = false;

    public function __construct(string $filePath) {
        // Does file have absolute path?
        if (substr($filePath, 0, 1) == '/') {
            $this->_filePath = $filePath;
        } else if (strlen($filePath) > 0) {
            $this->_filePath = Paths::getPath($filePath);
        }
    }

    public function path() {
        return $this->_filePath;
    }

    public function exists() {
        if (!$this->_filePath) {
            return false;
        }
        return file_exists($this->_filePath);
    }

    public function load(bool $required = true) {
        if (!$this->_filePath) {
            return false;
        }
        if ($required) {
            return (require $this->_filePath) == TRUE;
        } else {
            return (@include $this->_filePath) == TRUE;
        }
    }

    public function loadOnce(bool $required = true) {
        if (!$this->_filePath) {
            return false;
        }
        if ($required) {
            return (require_once $this->_filePath) == TRUE;
        } else {
            return (@include_once $this->_filePath) == TRUE;
        }
    }

    public function read() {

    }

    public function write($content) {

    }
}
?>