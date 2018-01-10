<?php
global $config;

// Generic Settings
$config['monumx'] = array(
    'debug' => true,
    'routesMatchModules' => true,
    'addModuleToRouteUrl' => true,
    'tasksMatchModules' => true,
    'addModuleToTask' => true,
    'baseUrl' => '/api',
    'dataFolder' => '.data'
);

// Database config
$config['database'] = array(
    'host' => 'localhost',
    'username' => 'username',
    'password' => 'password',
    'database' => 'database'
);

// Custom config
$config['custom'] = array(
    'apiKey' => 'someapikey',
);
?>