<?php
namespace MonumX\Routing;

use MonumX\Modules;
use MonumX\Routing\Router;
use MonumX\Exceptions\{RoutingException, MethodNotAllowedException};

class View {
    private $_viewName;
    private $_viewModule;

    public function __construct(string $viewName) {
        if (!$this->_validName($viewName)) {
            throw new RoutingException('Tried to create a new instance of a view with an invalid name "' . $viewName . '"');
        }

        if (strpos($viewName, '.') !== false) {
            $fragments = explode('.', $viewName, 2);
            $viewName = $fragments[1];
            $module = $fragments[0];

            if ($module == 'self') {
                $module = Router::currentModule();
            } else if (!Modules::exists($module)) {
                throw new RoutingException('Tried to load view "' . $viewName . '" from module "' . $module . '", but the module is not present');
            }
        } else {
            $module = Router::currentModule();
        }

        $this->_viewName = $viewName;
        $this->_viewModule = $module;

        $loaded = Modules::loadView($module, $viewName);
        if (!$loaded) {
            throw new RoutingException('Failed to load view "' . $viewName . '" from module "' . $module . '", the view file may not exist');
        }

        if (!class_exists('\MonumX\Views\\' . $this->_viewName)) {
            throw new RoutingException('Failed to load view "' . $viewName . '" from module "' . $module . '". A class with the view name does not exist, remember view names are case sensitive');
        }
    }

    public function execute(\MonumX\Http\Request $request, \MonumX\Http\Response $response) {
        $viewMethod = $request->method();
        $viewClass = '\MonumX\Views\\' . $this->_viewName;
        if (!method_exists($viewClass, $viewMethod)) {
            $viewMethod = 'all';
            if (!method_exists($viewClass, $viewMethod)) {
                throw new MethodNotAllowedException();
            }
        }

        $view = new $viewClass;
        $view->$viewMethod($request, $response);
    }

    private function _validName(string $name) {
        return @preg_match('/^[a-z0-9\-\_]+(?:\.[a-z0-9\-\_]+)?$/i', $name) ? true : false;
    }
}
?>