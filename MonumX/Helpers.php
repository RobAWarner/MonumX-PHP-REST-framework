<?php
namespace MonumX;

class Helpers {
    public static function array_change_key_case_recursive(array $array, int $case = CASE_LOWER) {
        return array_map(function($item){
            if (is_array($item))
                $item = self::array_change_key_case_recursive($item);
            return $item;
        }, array_change_key_case($array, $case));
    }

    public static function array_search_recursive($needle, array $haystack) {
        foreach ($haystack as $key => $value) {
            $currentKey = $key;
            if ($needle === $value || (is_array($value) && self::array_search_recursive($needle, $value) !== false)) {
                return $currentKey;
            }
        }
        return false;
    }
}
?>