<?php
namespace MonumX\Http;

class QueryParams {
    public static function list() {
        $get = $_GET;
        array_walk($get, function(&$item, &$key) { $item = trim($item); $key = trim($key); });
        return $get;
    }

    public static function get(string $paramKey) {
        if (isset($_GET[$paramKey]) && !empty($_GET[$paramKey])) {
            return trim($_GET[$paramKey]);
        }
        return false;
    }
}
?>