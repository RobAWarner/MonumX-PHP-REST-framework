<?php
namespace MonumX\Files;

use MonumX\Config;
use MonumX\Files\{File, Paths};
use MonumX\Exceptions\{ApplicationException, ConfigurationException};

class Cache {
    private $_dataDirectory;
    private $_cacheFolder;
    private $_cacheFolderExists = false;
    private $_cacheFile;
    private $_cachePath;

    public function __construct(string $cacheFolder, string $fileName) {
        // Is folder name valid?
        if (!$this->_isNameFolderValid($cacheFolder)) {
            throw new ApplicationException('Tried to initiate a new instance of Cache with an invalid folder name: \'' . $cacheFolder . '\'', array('backtrace' => false));
        }

        // Is file name valid?
        if (!$this->_isNameValid($fileName)) {
            throw new ApplicationException('Tried to initiate a new instance of Cache with an invalid file name: \'' . $fileName . '\'', array('backtrace' => false));
        }

        // Get data folder
        $dataPath = Config::get('settings', 'dataFolder', '.data');
        $realPath = realpath($dataPath);
        if ($dataPath != '.data') {
            if ($realPath === false) {
                throw new ConfigurationException('A custom data folder was specified but it does not exist.');
            }
            $dataPath = $realPath;
        } else {
            if ($realPath === false) {
                $madeDataDir = @mkdir('.data', 0777, true);
                if ($madeDataDir === false) {
                    throw new ApplicationException('Failed to create the cache data directory.', array('backtrace' => false));
                }
                if ($realPath === false) {
                    throw new ApplicationException('Failed to create the cache data directory.', array('backtrace' => false));
                }
            }
            $dataPath = $realPath;
        }

        // Cache folder
        $this->_cacheFolder = Paths::join($dataPath, $cacheFolder);
        $this->_cacheFolderExists = @file_exists($this->_cacheFolder);
        
        $this->_cacheFile = strtolower($fileName);
        $this->_dataDirectory = $dataPath;
        $this->_cachePath = Paths::join($this->_dataDirectory, $cacheFolder, $this->_cacheFile . '.cache');
    }

    public function path() {
        return $this->_cachePath;
    }

    public function exists() {
        return file_exists($this->path());
    }

    public function valid(int $expirySeconds = 0) {
        $age = $this->age();
        if ($age !== false) {
            return ($this->age() <= $expirySeconds);
        }
        return false;
    }

    public function age() {
        $writeTime = @filemtime($this->path());
        if ($writeTime === false || !is_int($writeTime)) {
            return false;
        }
        $age = time() - $writeTime;
        return ($age < 0) ? 0 : $age;
    }

    public function get(bool $autoDecode = true) {
        $cacheContent = @file_get_contents($this->path());
        if ($cacheContent !== false) {
            if ($autoDecode) {
                $decoded = $this->_decodeJson($cacheContent);
                return $decoded ? $cacheContent : false;
            } else {
                return $cacheContent;
            }
        }
        return false;
    }

    public function write($content, bool $autoEncode = true, int $encodeFlags = 0) {
        $this->_checkCacheFolder();
        mb_internal_encoding('UTF-8');
        if ($autoEncode) {
            if (is_array($content) || is_object($content)) {
                $content = @json_encode($content, $encodeFlags);
                if (json_last_error() != JSON_ERROR_NONE)
                    return false;
            }
        }
        return (file_put_contents($this->path(), $content) === false ? false : true);
    }
    
    public function rename(string $newName, bool $useNewName = true) {
        if ($this->exists()) {
            $newPath = Paths::join($this->_cacheFolder, $newName . '.cache');
            $renamed = @rename($this->path($fileName), $newPath);
            if ($useNewName && $renamed === true) {
                $this->_cachePath = $newPath;
            }
            return $renamed;
        }
        return false;
    }

    public function remove() {
        if ($this->exists()) {
            return @unlink($this->path($fileName));
        }
        return false;
    }

    private function _isNameValid(string $name) {
        return @preg_match('/^[a-z0-9\-\_\.]+$/i', $name);
    }

    private function _isNameFolderValid(string $name) {
        return @preg_match('/^[a-z0-9\-\_\.][a-z0-9\-\_\.\/\\][a-z0-9\-\_\.]+$/i', $name);
    }

    private function _checkCacheFolder() {
        if (!$this->_cacheFolderExists) {
            $created = @mkdir($this->_cacheFolder, 0777, true);
            if ($created !== true) {
                throw new ApplicationException('Failed to create a cache directory.', array('backtrace' => false));
            }
            $this->_cacheFolderExists = true;
        }
    }

    private function _decodeJson(&$json) {
        if (strlen($json) < 2) {
            return false;
        }
        $json = json_decode($json, true);
        return (json_last_error() === JSON_ERROR_NONE);
    }
}
?>