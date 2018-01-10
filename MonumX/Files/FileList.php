<?php
namespace MonumX\Files;

use MonumX\Files\Paths;

class FileList {
    private $_files = array();
    private $_filesStripped = null;
    private $_basePath = null;
    private $_basePathRegex = null;

    public function __construct(string $pattern, int $flags = 0) {
        $files = glob($pattern, $flags);
        if ($files !== FALSE && count($files) > 0) {
            $this->_files = $files;
            
            // Base path
            $pathFragments = explode(Paths::separator(), $pattern);
            // if (count($pathFragments) == 1 && strlen($pathFragments[0]) > 0) {
            //     $this->_basePath = $pathFragments[0] . Paths::separator();
            // } else
            $subdirCount = 1;
            $i = 1;

            foreach ($pathFragments as $fragment) {
                if (count($pathFragments) != $i && $fragment == '*') {
                    $subdirCount++;
                }
                $i++;
            }

            if (count($pathFragments) > 1) {
                for ($i = $subdirCount; $i > 0; $i--) {
                    unset($pathFragments[count($pathFragments) - 1]);
                }
                $this->_basePath = implode(Paths::separator(), $pathFragments) . Paths::separator();
            }

            if (!is_null($this->_basePath)) {
                $this->_basePathRegex = '/^' . preg_quote($this->_basePath, '/') . '/i';
            }
        }
    }

    public function list() {
        return $this->_files;
    }

    public function files() {
        return array_map(array($this, '_mapRemoveBase'), $this->_files);
    }
    public function contains(string $file, bool $caseInsensitive = true) {
        if (!$caseInsensitive) {
            return array_search($file, $this->_stipImageNames());
            // return array_search($file, $this->_files);
        } else {
            $index = array_search($file, array_map('strtolower', $this->_stipImageNames()));
            return $index === FALSE ? false : $this->_mapRemoveBase($this->_files[$index]);
        }
    }

    private function _stipImageNames() {
        if (is_null($this->_filesStripped)) {
            $this->_filesStripped = array_map(array($this, '_mapRemoveBase'), $this->_files);
        }
        return $this->_filesStripped;
    }

    private function _mapRemoveBase($a) {
        if (is_null($this->_basePathRegex)) {
            return $a;
        } else {
            return @preg_replace($this->_basePathRegex, '', $a);
        }
    }
}
?>