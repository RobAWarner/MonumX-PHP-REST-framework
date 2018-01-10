<?php
namespace MonumX\Http;

class Http {
    // HTTP GET request
    public static function get(string $url, bool $decode = false, int $timeout = 5) {
        $response = self::_curl($url, $timeout);

        if ($response === false)
            return false;

        if ($decode) {
            $response = self::_decodeResponse($response);
        }

        return $response;
    }

    public static function getMulti(array $urls, bool $decode = false, int $timeout = 5) {
        $urlsCount = count($urls);
        if ($urlsCount < 2) {
            return false;
        }

        $curlHandles = array();
        $curlMaster = curl_multi_init();

        for ($i = 0; $i < $urlsCount; $i++) {
            $curlHandles[$i] = curl_init($urls[$i]);
            curl_setopt($curlHandles[$i], CURLOPT_HEADER, 0);
            curl_setopt($curlHandles[$i], CURLOPT_CONNECTTIMEOUT, $timeout);
            curl_setopt($curlHandles[$i], CURLOPT_TIMEOUT, $timeout);
            curl_setopt($curlHandles[$i], CURLOPT_RETURNTRANSFER, true);
            curl_multi_add_handle($curlMaster, $curlHandles[$i]);
        }

        do {
            curl_multi_exec($curlMaster, $curlRunning);
        } while ($curlRunning > 0);

        $results = array();
        for ($i = 0; $i < $urlsCount; $i++) {
            $results[] = self::_decodeResponse(curl_multi_getcontent($curlHandles[$i]));
        }

        return $results;
    }

    // HTTP POST request
    public static function post(string $url, array $fields, bool $decode = false, int $timeout = 10) {
        $response = self::_curl($url, $timeout, array(
            'curl_params' => array(
                array(CURLOPT_POST, count($fields)),
                array(CURLOPT_POSTFIELDS, http_build_query($fields))
            )
        ));

        if ($response === false)
            return false;

        if ($decode) {
            $response = self::_decodeResponse($response);
        }

        return $response;
    }

    // Perform a request with CURL
    private static function _curl(string $url, int $timeout, array $data = array()) {
        $curl = curl_init();

        $agent = 'Mozilla/5.0 (Windows NT 6.2; WOW64; rv:17.0) Gecko/20100101 Firefox/17.' . rand(0, 9);

        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($curl, CURLOPT_USERAGENT, $agent);

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        if (isset($data['curl_params'])) {
            foreach ($data['curl_params'] as $curlParam) {
                curl_setopt($curl, $curlParam[0], $curlParam[1]);
            }
        }

        $response = curl_exec($curl);

        curl_close($curl);

        return $response;
    }

    // Decode a response
    private static function _decodeResponse($response) {
        if ($response === false) {
            return false;
        }
        $response = @json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE)
            return false;
        return $response;
    }
}
?>