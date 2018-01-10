<?php
namespace MonumX;

use MonumX\Files\File;
use MonumX\Exceptions\ConfigurationException;

class Config {
    private static $_config = array();

    public static function load() {
        $config = array();
        
        $configFile = new File('config.php');
        if ($configFile->exists()) {
            @require_once($configFile->path());

            if (is_array($config)) {
                self::$_config = $config;
            }
        }
    }

    public static function has(string $configGroup, string $configKey) {
        if (isset(self::$_config[$configGroup], self::$_config[$configGroup][$configKey])) {
            return true;
        }

        return false;
    }

    public static function get(string $configGroup, string $configKey, $defaultValue = null) {
        if (self::has($configGroup, $configKey)) {
            return self::$_config[$configGroup][$configKey];
        }

        return $defaultValue;
    }

    public static function require(string $configGroup, string $configKey) {
        if (!self::has($configGroup, $configKey)) {
            throw new ConfigurationException('Configuration is required but was not set', $configGroup, $configKey);
        }
        return self::get($configGroup, $configKey);
    }

    public static function set(string $configGroup, string $configKey, $value) {
        if (!isset(self::$_config[$configGroup])) {
            self::$_config[$configGroup] = array();
        }
        self::$_config[$configGroup][$configKey] = $value;
    }
}
?>