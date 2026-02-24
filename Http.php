<?php
// Http.php

class Http
{
    /**
     * 发送 POST 请求
     */
    public static function post($url, $data = [], $headers = [])
    {
        $ch = curl_init($url);
        $postData = json_encode($data);

        $defaultHeaders = [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($postData),
        ];

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array_merge($defaultHeaders, $headers));

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            $error_msg = curl_error($ch);
            curl_close($ch);
            return ['success' => false, 'error' => $error_msg];
        }

        curl_close($ch);

        return json_decode($response, true);
    }

    /**
     * 发送 GET 请求
     */
    public static function get($url, $headers = [])
    {
        $ch = curl_init($url);

        $defaultHeaders = [
            'Content-Type: application/json',
        ];

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array_merge($defaultHeaders, $headers));

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            $error_msg = curl_error($ch);
            curl_close($ch);
            return ['success' => false, 'error' => $error_msg];
        }

        curl_close($ch);

        return json_decode($response, true);
    }
}
?>
