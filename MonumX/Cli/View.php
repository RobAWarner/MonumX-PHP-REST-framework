<?php
namespace MonumX\Cli;

use MonumX\Modules;
use MonumX\Cli\Tasks;
use MonumX\Exceptions\{RoutingException};

class View {
    private $_viewName;
    private $_viewModule;

    public function __construct(string $viewName) {
        if (!$this->_validName($viewName)) {
            throw new RoutingException('Tried to create a new instance of a cli view with an invalid name "' . $viewName . '"');
        }

        if (strpos($viewName, '.') !== false) {
            $fragments = explode('.', $viewName, 2);
            $viewName = $fragments[1];
            $module = $fragments[0];

            if ($module == 'self') {
                $module = Tasks::currentModule();
            } else if (!Modules::exists($module)) {
                throw new RoutingException('Tried to load cli view "' . $viewName . '" from module "' . $module . '", but the module is not present');
            }
        } else {
            $module = Tasks::currentModule();
        }

        $this->_viewName = $viewName;
        $this->_viewModule = $module;

        $loaded = Modules::loadCliView($module, $viewName);
        if (!$loaded) {
            throw new RoutingException('Failed to load cli view "' . $viewName . '" from module "' . $module . '", the view file may not exist');
        }

        if (!class_exists('\MonumX\Tasks\\' . $this->_viewName)) {
            throw new RoutingException('Failed to load cli view "' . $viewName . '" from module "' . $module . '". A class with the view name does not exist, remember view names are case sensitive');
        }
    }

    public function execute(\MonumX\Cli\Request $request, \MonumX\Cli\Response $response) {
        $viewMethod = 'run';
        $viewClass = '\MonumX\Tasks\\' . $this->_viewName;
        if (!method_exists($viewClass, $viewMethod)) {
            throw new RoutingException('Run method not specified for Cli view \'' . $this->_viewName . '\'');
        }

        $view = new $viewClass;
        $view->$viewMethod($request, $response);
    }

    private function _validName(string $name) {
        return @preg_match('/^[a-z0-9\-\_]+(?:\.[a-z0-9\-\_]+)?$/i', $name) ? true : false;
    }
}
?>