<?php

class Http
{
    public function sendPostRequest($url, $data = [], $headers = [])
    {
        $ch = curl_init($url);
        $postData = http_build_query($data);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        if (!empty($headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            $error_msg = curl_error($ch);
            curl_close($ch);
            return "cURL Error: $error_msg";
        }

        curl_close($ch);
        return $response;
    }

    public static function post($url, $params = [], $options = [])
    {
        $req = self::sendRequest($url, $params, 'POST', $options);
        return $req;
    }

    public static function http_post_data_two($url, $data_string)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json; charset=utf-8',
            'Content-Length: ' . strlen($data_string)
        ]);

        ob_start();
        curl_exec($ch);
        $return_content = ob_get_contents();
        ob_end_clean();
        return $return_content;
    }

    public static function get($url, $params = [], $options = [])
    {
        $req = self::sendRequest($url, $params, 'GET', $options);
        return $req['ret'] ? $req['msg'] : '';
    }

    public static function sendRequest($url, $params = [], $method = 'POST', $options = [])
    {
        $method = strtoupper($method);
        $query_string = is_array($params) ? http_build_query($params) : $params;
        $ch = curl_init();
        $defaults = [];

        if ($method == 'GET') {
            $geturl = $query_string ? $url . (strpos($url, "?") !== false ? "&" : "?") . $query_string : $url;
            $defaults[CURLOPT_URL] = $geturl;
        } else {
            $defaults[CURLOPT_URL] = $url;
            $defaults[CURLOPT_POST] = ($method == 'POST');
            $defaults[CURLOPT_CUSTOMREQUEST] = $method;
            $defaults[CURLOPT_POSTFIELDS] = $query_string;
        }

        $defaults[CURLOPT_HEADER] = false;
        $defaults[CURLOPT_USERAGENT] = "Mozilla/5.0";
        $defaults[CURLOPT_FOLLOWLOCATION] = true;
        $defaults[CURLOPT_RETURNTRANSFER] = true;
        $defaults[CURLOPT_CONNECTTIMEOUT] = 3;
        $defaults[CURLOPT_TIMEOUT] = 30;

        curl_setopt_array($ch, (array)$options + $defaults);

        $ret = curl_exec($ch);
        $err = curl_error($ch);

        if (false === $ret || !empty($err)) {
            $errno = curl_errno($ch);
            $info = curl_getinfo($ch);
            curl_close($ch);
            return [
                'ret' => false,
                'errno' => $errno,
                'msg' => $err,
                'info' => $info,
            ];
        }

        curl_close($ch);
        return ['ret' => true, 'msg' => $ret];
    }

    public static function sendAsyncRequest($url, $params = [], $method = 'POST')
    {
        $method = strtoupper($method) == 'POST' ? 'POST' : 'GET';
        $post_string = is_array($params) ? http_build_query($params) : $params;
        $parts = parse_url($url);
        $fp = fsockopen($parts['host'], $parts['port'] ?? 80, $errno, $errstr, 3);

        if (!$fp) {
            return false;
        }

        stream_set_timeout($fp, 3);
        $out = "{$method} {$parts['path']}?{$parts['query']} HTTP/1.1\r\n";
        $out .= "Host: {$parts['host']}\r\n";
        $out .= "Content-Type: application/x-www-form-urlencoded\r\n";
        $out .= "Content-Length: " . strlen($post_string) . "\r\n";
        $out .= "Connection: Close\r\n\r\n";
        $out .= $post_string;

        fwrite($fp, $out);
        fclose($fp);
        return true;
    }
}

class five
{
    private $link;
    private $chat_url;
    private $rocket_url;
    private $pdo;

    public function __construct()
    {
        include "rocket_jiqi.php";
        $this->token = '{$ma_token}';
        $this->link = 'https://api.telegram.org/bot' . $this->token;
        $this->pdo = new PDO("mysql:host={$dbHost};dbname={$dbName}", $dbUser, $dbPassword, [PDO::ATTR_PERSISTENT => true]);
    }

    public function index()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $chatid = $data['message']['chat']['id'];
        $message = $data['message']['text'];
        $this->xiaoxi("Message received: {$message}", $chatid);
    }

    public function xiaoxi($msg, $chatid)
    {
        $parameter = ['chat_id' => $chatid, 'text' => $msg];
        $this->http_post_data('sendMessage', json_encode($parameter));
    }

    public function http_post_data($action, $data_string)
    {
        $url = $this->link . "/" . $action;
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_exec($ch);
        curl_close($ch);
    }
}

$five = new five();
$five->index();
