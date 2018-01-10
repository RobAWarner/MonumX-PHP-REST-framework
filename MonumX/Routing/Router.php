<?php
namespace MonumX\Routing;

use MonumX\{Config, Modules};
use MonumX\Files\{File, FileList, Paths};
use MonumX\Http\{QueryParams, Request, Response};
use MonumX\Routing\Route;
use MonumX\Exceptions\NotFoundException;

class Router {
    private static $_routes = array();
    private static $_currentURL = null;
    private static $_currentModule = null;

    private static $_request = null;
    private static $_response = null;
    
    // Register a route
    public static function register(string $url, string $view, array $options = array()) {
        $route = new Route($url, $view, self::$_currentModule, $options);

        array_push(self::$_routes, $route);
    }

    // Find and execute a route
    public static function run() {
        $currentUrl = self::getCurrentUrl();
        self::_loadUrls();
        
        foreach (self::$_routes as $route) {
            // Does this route match the current URL?
            if ($route->doesMatchUrl($currentUrl)) {
                $urlParams = $route->getUrlParams();
                $getParams = QueryParams::list();

                self::$_request = new Request($urlParams, $getParams);
                self::$_response = new Response();

                $view = $route->loadView();
                $view->execute(self::$_request, self::$_response);

                self::$_response->execute();
            }
        }

        // No route found
        throw new NotFoundException();
    }

    public static function currentModule() {
        return self::$_currentModule;
    }

    public static function isUrlValid(string $url) {
        // EG: /module/v1/test/<paramName|validator:validatorParam1>
        if (@preg_match('/^[a-z0-9\/\-\_\.\~\<\>\|\:\$]+$/i', $url) === 1) {
            return true;
        }
        return false;
    }

    private static function _loadUrls() {
        $loadAllUrls = Config::get('monumx', 'routesMatchModules', false) ? false : true;

        if ($loadAllUrls) {
            
        } else {
            $module = self::getModuleFromUrl();
            if (!$module) {
                throw new NotFoundException();
            }

            self::$_currentModule = $module;

            $urlsFile = new File(Paths::moduleFile($module, 'Urls.php'));
            if (!$urlsFile->exists()) {
                throw new NotFoundException();
            }

            $urlsFile->loadOnce();
        }
    }

    private static function getCurrentUrl() {
        if (!is_null(self::$_currentURL)) {
            return self::$_currentURL;
        } else {
            $url = explode("?", strtolower($_SERVER['REQUEST_URI']))[0];

            // Base URL
            $baseUrl = Config::get('monumx', 'baseUrl', '');
            if (strlen($baseUrl) > 0) {
                $baseUrl = trim($baseUrl);

                // Ensure leading slash
                if (substr($baseUrl, 0, 1) !== '/') {
                    $baseUrl = '/' . $baseUrl;
                }

                // Remove trailing slash
                $url = preg_replace('/\/$/i', '', $url);

                // Quote string for regex
                $baseUrl = preg_quote($baseUrl, '/');
                $url = preg_replace('/^' . $baseUrl . '/i', '', $url);
            }

            // Remove trailing slash
            $url = preg_replace('/\/$/i', '', $url);

            self::$_currentURL = $url;

            return $url;
        }
    }

    private static function getModuleFromUrl() {
        $url = self::getCurrentUrl();
        $urlFragments = explode('/', substr($url, 1), 2);

        if (count($urlFragments) >= 1 && strlen($urlFragments[0]) > 0) {
            return Modules::exists($urlFragments[0]) ?? $urlFragments[0];
        }
        return false;
    }
}
?>