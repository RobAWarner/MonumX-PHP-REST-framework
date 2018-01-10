<?php
namespace MonumX\Files;

use MonumX\Modules;
use MonumX\Files\FileList;

class Paths {
    private static $_rootPath = null;

    // Get MonumX root
    public static function root() {
        self::_checkRoot();
        return self::$_rootPath;
    }

    // Get path to core module
    public static function core() {
        self::_checkRoot();
        return self::join(self::$_rootPath, 'core');
    }

    // Get path for specific module
    public static function module(string $module) {
        self::_checkRoot();

        $module = Modules::exists($module) ?? $module;
        return self::join(self::$_rootPath, $module);
    }

    // Get path for specific module
    public static function moduleFile(string $module, string $file) {
        self::_checkRoot();
        return self::join(self::module($module), $file);
    }

    // Create a path string from root
    public static function getPath(string $path) {
        self::_checkRoot();
        return self::join(self::$_rootPath, $path);
    }

    public static function separator() {
        return DIRECTORY_SEPARATOR;
    }

    public static function removePathFromString(string $input) {
        if (!is_null(self::$_rootPath)) {
            $pathQuoted = preg_quote(self::$_rootPath, '#');
            $input = preg_replace('#' . $pathQuoted . '#i', '', $input);
        }
        return $input;
    }

    // Join a path
    public static function join(string ...$segments) {
        $output = '';
        foreach ($segments as $index => $segment) {
            if (substr($segment, 0, 1) == '.') {
                continue;
            }
            $segment = preg_replace('(\/+$|\\+$)', '', $segment);
            $output .= ($index > 0 ? DIRECTORY_SEPARATOR : '') . $segment;
        }
        return $output;
    }

    // Check if root path has been set
    private static function _checkRoot() {
        if (is_null(self::$_rootPath)) {
            self::$_rootPath = dirname(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR;
            @chdir(self::$_rootPath);
        }
    }
}
?>
