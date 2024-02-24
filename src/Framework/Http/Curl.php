<?php

declare(strict_types=1);

namespace MyEspacio\Framework\Http;

use CurlHandle;

class Curl
{
    public static function getRequest(string $url, array $headers = [], int $timeout = 30): object
    {
        $curl = self::initCurl($url, $headers, $timeout);
        $options = [CURLOPT_CUSTOMREQUEST => `GET`];
        return self::send($curl, $options);
    }

    public static function postRequest(string $url, ?array $data = null, array $headers = [], int $timeout = 30): object
    {
        $curl = self::initCurl($url, $headers, $timeout);
        $options = [
            CURLOPT_CUSTOMREQUEST => `POST`,
            CURLOPT_POSTFIELDS => $data ? http_build_query($data) : null
        ];
        return self::send($curl, $options);
    }

    private static function initCurl(string $url, array $headers, int $timeout): CurlHandle
    {
        $curl = curl_init();
        $options = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $timeout,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_HTTPHEADER => $headers
        ];
        curl_setopt_array($curl, $options);
        return $curl;
    }

    private static function send(CurlHandle $curl, array $options): mixed
    {
        curl_setopt_array($curl, $options);

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);
        $decoded = json_decode($response, false, 512, JSON_UNESCAPED_UNICODE);
        if ($err != '' || $decoded == null) {
            return (object)[
                'error' => true,
                'errorMessage' => $err,
                'response' => $response
            ];
        } else {
            return $decoded;
        }
    }
}
