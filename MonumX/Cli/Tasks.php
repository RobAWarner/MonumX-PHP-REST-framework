<?php
namespace MonumX\Cli;

use MonumX\{Config, Modules};
use MonumX\Files\{File, FileList, Paths};
use MonumX\Cli\{Cli, Request, Response};
use MonumX\Exceptions\NotFoundException;

class Tasks {
    private static $_tasks = array();
    private static $_currentModule = null;
    
    // Register a route
    public static function register(string $command, string $task) {
        $task = new Task($command, $task, self::$_currentModule);

        array_push(self::$_tasks, $task);
    }

    // Find and execute a task
    public static function run(string $customTask = '') { 
        if (strlen($customTask) > 0) {
            $cmdArgs = explode(' ', $customTask);
        } else {
            $cmdArgs = Cli::arguments();
        }

        self::_loadTasks();

        foreach (self::$_tasks as $task) {
            // Does this route match the current URL?
            if ($task->doesMatchCommand($cmdArgs)) {
                // $cmdArgs = Cli::arguments();
                $cmdParams = $task->getCmdParams();

                $request = new Request($cmdArgs, $cmdParams);
                $response = new Response();

                $taskView = $task->loadView();
                $taskView->execute($request, $response);
                die();
            }
        }

        // No route found
        throw new NotFoundException();
    }

    public static function currentModule() {
        return self::$_currentModule;
    }

    public static function isCmdValid(string $command) {
        // EG: test update <paramName|validator:validatorParam1>
        return @preg_match('/^[a-z0-9\-\_\(\)\[\]\<\>\|\:\s\?]+$/i', $command) === 1;
    }

    private static function _loadTasks() {
        $loadAll = Config::get('monumx', 'tasksMatchModules', false) ? false : true;

        if ($loadAll) {
            
        } else {
            $module = self::_getModuleFromCommand();
            if (!$module) {
                throw new NotFoundException();
            }

            self::$_currentModule = $module;

            $tasksFile = new File(Paths::moduleFile($module, 'CliTasks.php'));
            if (!$tasksFile->exists()) {
                throw new NotFoundException();
            }

            $tasksFile->loadOnce();
        }
    }

    private static function _getModuleFromCommand() {
        $cmdArgs = Cli::arguments();
        return $cmdArgs[0] ?? false;
    }
}
?>