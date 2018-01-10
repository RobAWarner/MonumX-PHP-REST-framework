<?php
namespace MonumX;

use MonumX\Exceptions\ApplicationException;

class Validator {
    private $_validtor = null;
    private static $_hasInit = false;
    private static $_validators = array();
    private static $_loadedModules = array();

    public static function init() {
        if (!self::$_hasInit) {
            self::$_hasInit = true;
            self::_registerDefaultValidators();
        }
    }

    // Create a new instance of a validator
    public function __construct(string $validatorName) {
        self::init();

        if (!self::hasValidator($validatorName)) {
            throw new ApplicationException('Tried to use validator "' . $validatorName . '" but it has not been registerd');
        }

        $this->_validtor = $validatorName;
    }

    // Check an input string against this validator
    public function test(string $input, array $arguments = array()) {
        array_unshift($arguments, $input);
        return @call_user_func_array(self::$_validators[$this->_validtor]['callback'], $arguments);
    }

    // Register a new validator
    public static function register(string $validatorName, $validatorCallback, array $validatorArguments = array()) {
        self::init();
        
        if (self::hasValidator($validatorName)) {
            throw new ApplicationException('Tried to register validator "' . $validatorName . '" but one with this name is already registered');
        }

        // Check name
        if (!self::_isNameValid($validatorName)) {
            throw new ApplicationException('Tried to register a validator with an invalid name "' . $validatorName . '"');
        }

        // Check arguments
        $requiredArgumentCount = 0;
        $totalArgumentCount = 0;

        if (!empty($validatorArguments)) {
            $_optionalArguments = false;
            foreach ($validatorArguments as $argument => $argumentType) {
                if (substr($argument, 0, 1) == '?') {
                    $_optionalArguments = true;
                    $totalArgumentCount++;
                } else {
                    if ($_optionalArguments) {
                        throw new ApplicationException('Tried to register valiadtor "' . $validatorName . '" but it contains non-optional arguments after optional ones');
                    }

                    $requiredArgumentCount++;
                    $totalArgumentCount++;
                }
            }
        }

        // Store the valiadtor
        $validator = array(
            'callback' => $validatorCallback,
            'arguments' => $validatorArguments,
            'requiredArguments' => $requiredArgumentCount,
            'totalArguments' => $totalArgumentCount
        );

        self::$_validators[$validatorName] = $validator;
    }

    // Check if a validator has been registered
    public static function hasValidator(string $validatorName) {
        return isset(self::$_validators[$validatorName]);
    }

    private static function _isNameValid(string $validatorName) {
        return @preg_match('/^[a-z0-9\-\_]+(?:\.[a-z0-9\-\_]+)?$/i', $validatorName);
    }

    private static function _registerDefaultValidators() {
        self::register('int', 'self::_intValiadtor', array('?min' => 'int', '?max' => 'int'));
        self::register('string', 'self::_stringValiadtor', array('?min' => 'int', '?max' => 'int'));
    }

    static function _intValiadtor($input, $min = null, $max = null) {
        if (!preg_match('/^[0-9]+$/', $input)) {
            return false;
        }

        $input = intval($input);
        
        if (!is_null($min) && $input < intval($min)) {
            return false;
        }
        if (!is_null($max) && $input > intval($max)) {
            return false;
        }
        return true;
    }

    static function _stringValiadtor($input, $min = null, $max = null) {
        if (strlen($input) < 1) {
            return false;
        }

        if (!is_null($min) && strlen($input) < intval($min)) {
            return false;
        }
        if (!is_null($max) && strlen($input) > intval($max)) {
            return false;
        }
        return true;
    }
}
?>