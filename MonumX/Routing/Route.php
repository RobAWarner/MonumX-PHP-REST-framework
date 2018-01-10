<?php
namespace MonumX\Routing;

use MonumX\{Config, Validator};
use MonumX\Routing\{View, Router};
use MonumX\Exceptions\RoutingException;

class Route {
    private $_url = null;
    private $_urlRegex = null;
    private $_urlParms = array();

    private $_viewName = null;
    private $_module = null;

    public function __construct(string $url, string $view, string $module, array $options = array()) {
        // Ensure we have a URL
        if (strlen($url) < 1 || !Router::isUrlValid($url)) {
            throw new RoutingException('A route is being registered without a valid URL pattern defined.');
        }

        // Ensure we have a view
        if (strlen($view) < 1) {
            throw new RoutingException('A route is being registered without a valid view defined.');
        }

        $this->_url = $url;
        $this->_viewName = $view;
        $this->_module = $module;

        // Add module
        $addModuleToUrl = Config::get('monumx', 'addModuleToRouteUrl', Config::get('monumx', 'routesMatchModules', true));
        if ($addModuleToUrl) {
            if (substr($this->_url, 0, 1) !== '/') {
                $this->_url = '/' . $this->_url;
            }
            $this->_url = '/' . strtolower($this->_module) . $this->_url;
        }

        // Generare the regex, url params etc
        $this->_processRouteUrl();

        $this->_processRouteOptions($options);
    }

    // Does the route match a given url
    public function doesMatchUrl(string $url) {
        if (!is_null($this->_urlRegex)) {
            // Run route regex
            if (@preg_match('/' . $this->_urlRegex . '/i', $url, $matches)) {
                // Did we get URL params?
                if (count($matches) > 1) {
                    // Remove the first index as this is the full match
                    $_matches = $matches;
                    array_splice($_matches, 0, 1);
                    
                    // Does the param have any validators
                    foreach ($_matches as $index => $match) {
                        if (isset($this->_urlParms[$index]['validator'])) {
                            $validator = new Validator($this->_urlParms[$index]['validator']);
                            // Test the param
                            if (!$validator->test($match, ($this->_urlParms[$index]['validator_params'] ?? array()))) {
                                return false;
                            }
                        }
                    }

                    // Store the match data
                    foreach ($_matches as $index => $match) {
                        $this->_urlParms[$index]['data'] = $match;
                    }

                    $matches = $_matches;
                }
                
                return $matches;
            }
        }
        return false;
    }

    public function getUrlParams() {
        $params = array();
        foreach ($this->_urlParms as $param) {
            if (isset($param['name'], $param['data'])) {
                $params[$param['name']] = $param['data'];
            }
        }
        return $params;
    }

    public function loadView() {
        $view = new View($this->_viewName);
        return $view;
    }

    private function _processRouteUrl() {
        $url = strtolower($this->_url);

        // Add leading slash
        if (substr($url, 0, 1) !== '/') {
            $url = '/' . $url;
        }
        // Remove trailing slash
        $url = preg_replace('/\/$/i', '', $url);

        // Find URL params
        preg_match_all('/\/\<([^\>]+)>/i', $url, $paramMatches, PREG_SET_ORDER);
        if (count($paramMatches) > 0) {
            // Save the param name
            foreach ($paramMatches as $param) {
                if (isset($param[1]) && strlen($param[1]) > 0) {
                    // Ensure parent property is set
                    if (!isset($this->_urlParms)) {
                        $this->_urlParms = array();
                    }

                    $urlParam = array();

                    // Does param contain a filter?
                    if (strpos($param[1], '|') > -1) {
                        $paramFragments = explode('|', $param[1], 2);
                        $urlParam['name'] = $paramFragments[0];

                        if (strpos($paramFragments[1], ':')) {
                            $validatorFragments = explode(':', $paramFragments[1]);
                            $urlParam['validator'] = $validatorFragments[0];

                            array_splice($validatorFragments, 0, 1);
                            $urlParam['validator_params'] = $validatorFragments;
                        } else {
                            $urlParam['validator'] = $paramFragments[1];
                        }
                    } else {
                        $urlParam['name'] = $param[1];
                    }

                    array_push($this->_urlParms, $urlParam);
                }
            }
        }

        // Create regex for URL
        $this->_urlRegex = preg_quote($url, '/');

        // Replace params
        if (count($paramMatches) > 0) {
            $this->_urlRegex = preg_replace('/\\\<[^\>]+\\\>/i', '([^\/]+)', $this->_urlRegex);
        }

        // Regex start and end of string
        $this->_urlRegex = '^' . $this->_urlRegex;
        if (substr($this->_urlRegex, -2) == '\$') {
            $this->_urlRegex = substr($this->_urlRegex, 0, -2) . '$';
        } else {
            $this->_urlRegex .= '$';
        }
    }

    // Process route options
    private function _processRouteOptions(array $otions) {

    }
}
?>