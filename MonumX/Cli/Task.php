<?php
namespace MonumX\Cli;

use MonumX\{Config, Validator};
use MonumX\Cli\{Tasks, View};
use MonumX\Exceptions\RoutingException;

class Task {
    private $_command = null;
    public $_cmdArgMatches = null;
    private $_cmdParams = array();
    private $_taskName = null;
    private $_module = null;

    public function __construct(string $command, string $task, string $module) {
        // Ensure we have a command
        if (strlen($command) < 1 || !Tasks::isCmdValid($command)) {
            throw new RoutingException('A route is being registered without a valid command pattern defined: \'' . $command . '\'');
        }

        // Ensure we have a task
        if (strlen($task) < 1) {
            throw new RoutingException('A route is being registered without a valid view defined.');
        }

        $this->_command = $command;
        $this->_taskName = $task;
        $this->_module = $module;

        // Add module
        $addModuleToCommand = Config::get('monumx', 'addModuleToTask', Config::get('monumx', 'tasksMatchModules', true));
        if ($addModuleToCommand) {
            $this->_command = strtolower($this->_module) . ' ' . $this->_command;
        }

        // Generare the regex, params etc
        $this->_processCommand();
    }

    // Does the route match a given url
    public function doesMatchCommand(array $commandArgs) {
        if (!is_null($this->_cmdArgMatches)) {
            $matches = array();

            for ($i = 0; $i < count($this->_cmdArgMatches); $i++) {
                if (!isset($commandArgs[$i])) {
                    if (isset($this->_cmdArgMatches[$i]['optional'])) {
                        break;
                    }
                    return false;
                }

                switch ($this->_cmdArgMatches[$i]['type']) {
                    case 'string':
                        if ($this->_cmdArgMatches[$i]['pattern'] !== strtolower($commandArgs[$i])) {
                            return false;
                        }
                        break;

                    case 'regex':
                        if (@preg_match('/' . $this->_cmdArgMatches[$i]['pattern'] . '/i', $commandArgs[$i], $_matches)) {
                            if (count($_matches) === 2) {
                                $match = $_matches[1];

                                if (isset($this->_cmdArgMatches[$i]['validator'])) {
                                    $validator = new Validator($this->_cmdArgMatches[$i]['validator']);

                                    // Test the param
                                    if (!$validator->test($match, ($this->_cmdArgMatches[$i]['validator_params'] ?? array()))) {
                                        return false;
                                    }
                                }

                                array_push($matches, $match);
                                break;
                            }
                        }
                        return false;
                }
            }

            // Store the match data
            foreach ($matches as $index => $match) {
                $this->_cmdParams[$index]['data'] = $match;
            }

            return true;
        }
        return false;
    }

    public function getCmdParams() {
        $params = array();
        foreach ($this->_cmdParams as $param) {
            if (isset($param['name'], $param['data'])) {
                $params[$param['name']] = $param['data'];
            }
        }
        return $params;
    }

    public function loadView() {
        $view = new View($this->_taskName);
        return $view;
    }

    private function _processCommand() {
        // Split
        $command = trim(strtolower($this->_command));
        $commandArgParts = explode(' ', $command);

        $commandArgs = array();
        $_optionalParam = false;

        foreach ($commandArgParts as $part) {
            if (@preg_match('/^\<([^\>]+)>$/i', $part, $partMatches)) {
                if (isset($partMatches[1]) && strlen($partMatches[1]) > 0) {
                    $param = $partMatches[1];
                    $cmdParam = array('pattern' => '([^\/]+)', 'type' => 'regex');

                    if ($_pos = strpos($param, '?') !== false) {
                        if ($_pos > 1) {
                            throw new RoutingException('A route is being registered with an invalid command pattern: \'' . $this->_command . '\'');
                        } else {
                            $_optionalParam = true;
                            $cmdParam['optional'] = true;
                            $param = substr($param, 1);
                        }
                    } else if ($_optionalParam) {
                        throw new RoutingException('A route is being registered with a non-optional param after an optional one: \'' . $this->_command . '\'');
                    }

                    // Does param contain a filter?
                    if (strpos($param, '|') > -1) {
                        $paramFragments = explode('|', $param, 2);
                        $cmdParam['name'] = $paramFragments[0];

                        if (strpos($paramFragments[1], ':')) {
                            $validatorFragments = explode(':', $paramFragments[1]);
                            $cmdParam['validator'] = $validatorFragments[0];

                            array_splice($validatorFragments, 0, 1);
                            $cmdParam['validator_params'] = $validatorFragments;
                        } else {
                            $cmdParam['validator'] = $paramFragments[1];
                        }
                    } else {
                        $cmdParam['name'] = $param;
                    }

                    array_push($this->_cmdParams, array('name' => $cmdParam['name']));
                    array_push($commandArgs, $cmdParam);
                }
            } else {
                array_push($commandArgs, array('pattern' => $part, 'type' => 'string'));
            }
        }

        $this->_cmdArgMatches = $commandArgs;
    }
}
?>