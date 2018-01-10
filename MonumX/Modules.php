<?php
namespace MonumX;

use MonumX\Files\{FileList, Paths};

class Modules {
    private static $_modules = null;

    public static function list() {
        if (is_array(self::$_modules)) {
            return self::$_modules;
        } else {
            self::$_modules = array();
            
            $modules = new FileList(Paths::root() . "*", GLOB_ONLYDIR);
            foreach ($modules->list() as $modulePath) {
                $moduleName = basename($modulePath);
                if (strtolower($moduleName) == 'monumx') {
                    continue;
                }

                self::$_modules[strtolower($moduleName)] = $moduleName;
            }

            return self::$_modules;
        }
    }

    public static function exists(string $moduleName) {
        $moduleName = strtolower($moduleName);
        $modules = self::list();
        return $modules[$moduleName] ?? false;
    }

    public static function loadUrls(string $viewName) {
        
    }

    public static function loadView(string $moduleName, string $viewName) {
        $viewFile = 'Views' . Paths::separator() . $viewName . '.php';
        $filePath = Paths::moduleFile($moduleName, $viewFile);
        
        if (file_exists($filePath)) {
            return (require_once $filePath) != FALSE;
        }

        return false;
    }

    public static function loadCliView(string $moduleName, string $viewName) {
        $viewFile = 'Cli' . Paths::separator() . $viewName . '.php';
        $filePath = Paths::moduleFile($moduleName, $viewFile);
        
        if (file_exists($filePath)) {
            return (require_once $filePath) != FALSE;
        }

        return false;
    }
}
?>