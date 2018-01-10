<?php
namespace MonumX\Cli;

class Cli {
    private static $_cliArguments;

    public static function setArguments(array $arguments) {
        self::$_cliArguments = $arguments;
    }

    public static function arguments(bool $removeFile = true) {
        if ($removeFile) {
            $args = self::$_cliArguments;
            array_splice($args, 0, 1);
            return $args;
        }
        return self::$_cliArguments;
    }

    public static function argumentString(bool $removeFile = true) {
        $args = self::arguments($removeFile);
        return implode(' ', $args);
    }

    public static function colorString(string $string, string $color) {
        return "\033[" . self::_getColor($color) . "m" . $string . " \033[0m";
    }

    public static function print($content) {
        if (is_array($content) || is_object($content)) {
            print(print_r($content, true) . PHP_EOL);
        } else {
            print($content . PHP_EOL);
        }
    }

    public static function printColor($content, string $color) {
        if (is_array($content) || is_object($content)) {
            $content = self::colorString(print_r($content, true), $color);
            print($content . PHP_EOL);
        } else {
            $content = self::colorString($content, $color);
            print($content . PHP_EOL);
        }
    }

    public static function printSection(string $title, $content) {
        self::print('--- ' . $title . ' ---');
        self::print($content);
        self::print('--- End ' . $title . ' ---');
    }

    public static function printSectionColor(string $title, $content, string $color, string $bodyColor = 'none') {
        if ($bodyColor === 'none') {
            $bodyColor = $color;
        }
        self::printColor('--- ' . $title . ' ---', $color);
        self::printColor($content, $bodyColor);
        self::printColor('--- End ' . $title . ' ---', $color);
    }

    private static function _getColor(string $color) {
        switch ($color) {
            case 'red':     return "31";
            case 'green':   return "32";
            case 'yellow':  return "33";
            case 'blue':    return "34";
            case 'cyan':    return "36";
            default:        return "37"; // White
        }
    }
}
?>