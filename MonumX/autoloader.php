<?php
require_once('Files/Paths.php');

use MonumX\Files\Paths;
use MonumX\Exceptions\ApplicationException;

spl_autoload_register(function($class) {
    // Replace slashes with correct directory separator
    if (Paths::separator() != '\\') {
        $segments = explode('\\', $class);
        $class = implode(Paths::separator(), $segments);
    }

    // Build file path
    $file = Paths::root() . $class . '.php';

    $loaded = (@require_once($file));

    // Does the file exist?
    if (!$loaded) {
        // Throw an exception here!
        if (class_exists('MonumX\Exceptions\ApplicationException')) {
            throw new ApplicationException("File not found " . $file);
        } else {
            die('Internal Server Error. Cannot loaded class "'.$class.'", file not found '.$file);
        }
    }
});
?>
