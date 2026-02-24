<?php
// RocketChatAPI.php

require 'Http.php'; // 确保路径正确，如果放在其他目录，调整路径

class RocketChatAPI
{
    private $serverUrl;
    private $authToken;
    private $userId;

    public function __construct($serverUrl)
    {
        $this->serverUrl = rtrim($serverUrl, '/');
    }

    /**
     * 管理员登录，获取 authToken 和 userId
     */
    public function adminLogin($username, $password)
    {
        $url = $this->serverUrl . '/api/v1/login';
        $data = [
            'user' => $username,
            'password' => $password,
        ];

        $response = Http::post($url, $data);

        if (isset($response['status']) && $response['status'] === 'success') {
            $this->authToken = $response['data']['authToken'];
            $this->userId = $response['data']['userId'];
            return true;
        }

        return false;
    }

    /**
     * 创建频道
     */
    public function createChannel($name, $members = [])
    {
        $url = $this->serverUrl . '/api/v1/channels.create';
        $data = [
            'name' => $name,
            'members' => $members,
        ];

        $headers = [
            'X-Auth-Token: ' . $this->authToken,
            'X-User-Id: ' . $this->userId,
        ];

        return Http::post($url, $data, $headers);
    }

    /**
     * 发送消息
     */
    public function postMessage($roomId, $text)
    {
        $url = $this->serverUrl . '/api/v1/chat.postMessage';
        $data = [
            'roomId' => $roomId,
            'text' => $text,
        ];

        $headers = [
            'X-Auth-Token: ' . $this->authToken,
            'X-User-Id: ' . $this->userId,
        ];

        return Http::post($url, $data, $headers);
    }

    /**
     * 上传文件
     */
    public function uploadFile($roomId, $filePath, $description = '')
    {
        $url = $this->serverUrl . '/api/v1/rooms.upload/' . $roomId;
        $postData = [
            'description' => $description,
            'file' => new CURLFile($filePath),
        ];

        $headers = [
            'X-Auth-Token: ' . $this->authToken,
            'X-User-Id: ' . $this->userId,
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            $error_msg = curl_error($ch);
            curl_close($ch);
            return ['success' => false, 'error' => $error_msg];
        }

        curl_close($ch);

        return json_decode($response, true);
    }

    // 根据需要添加其他 API 方法
}
?>
