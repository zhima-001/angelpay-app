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
}

class five
{
    private $link = "";
    private $chat_url = "";
    private $rocket_url = "";
    private $token = "";
    private $pdo;

    public function __construct()
    {
        include "rocket_jiqi.php";
        $this->token = $ma_token;
        $this->chat_url = $chat_url;
        $this->rocket_url = $rocket_url;
        $this->link = 'https://api.telegram.org/bot' . $this->token;
        $this->pdo = new PDO("mysql:host=" . $dbHost . ";dbname=" . $dbName, $dbUser, $dbPassword, [PDO::ATTR_PERSISTENT => true]);
    }

    public function index()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $chatid = $data['message']['chat']['id'];
        $message = $data['message']['text'];
        $this->message($message, $chatid, $data, $data['message']['from']['id']);
    }

    public function message($message, $chatid, $data, $tg_userid)
    {
        $this->xiaoxi("收到的消息: $message", $chatid);
    }

    public function xiaoxi($msg, $chatid)
    {
        $parameter = ['chat_id' => $chatid, 'text' => $msg];
        $this->http_post_data('sendMessage', json_encode($parameter));
    }

    public function http_post_data($action, $data_string)
    {
        $url = $this->link . '/' . $action;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }
}

$instance = new five();
$instance->index();

?>