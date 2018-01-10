<?php
namespace MonumX;

class Debugging {
    public static function prePrint($input, bool $forceDie = false) {
        print '<pre>' . htmlspecialchars(print_r($input, true)) . '</pre>';
        if ($forceDie) {
            die();
        }
    }
}
?>