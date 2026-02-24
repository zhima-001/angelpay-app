<?php
// handle_rocketchat_webhook.php

require 'config.php';
require 'db.php';
// 不再需要 require 'vendor/autoload.php'; 因为您提供的代码不需要
// require 'RocketChatAPI.php'; // 您的代码中已经处理了 Rocket.Chat API 的交互

class RocketChatWebhookHandler
{
    private $pdo;
    private $chat_url;
    private $rocket_url;

    public function __construct()
    {
        include "rocket_jiqi.php"; // 您的代码中包含的配置文件

        $this->chat_url = $chat_url;
        $this->rocket_url = $rocket_url;

        $this->pdo = getDBConnection();
    }

    // 用于检查是否为公司客服
    private function isKefu($kefu_name)
    {
        $url = $this->rocket_url . "/api/Index/allkefu";
        $data = [
            'kefu_name' => $kefu_name
        ];

        $response = Http::post($url, $data);

        // 检查 kefu_name 是否在客服列表中
        if ($response['code'] === 1 && isset($response['data'])) {
            $kefuList = $response['data'];

            foreach ($kefuList as $kefu) {
                if ($kefu['username'] == $kefu_name) {
                    return true;
                }
            }
        }
        return false;
    }

    // 用于发送消息到 Telegram
    public function sendToTelegram($messageText, $chat_id, $channel, $tgMessageId, $referencedRcMessageId)
    {
        $url = 'https://api.telegram.org/bot' . $channel . '/sendMessage';
        $data = [
            'chat_id' => $chat_id,
            'text' => $messageText
        ];
        if (!empty($tgMessageId)) {
            if ($tgMessageId && !$this->checkMessageExists($chat_id, $tgMessageId, $channel)) {
                $tgMessageId = null;
            } else {
                $data['reply_to_message_id'] = $tgMessageId;
            }
        }
        $options = [
            'http' => [
                'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
                'method' => 'POST',
                'content' => http_build_query($data)
            ]
        ];
        $context = stream_context_create($options);
        $response = file_get_contents($url, false, $context);

        if ($response !== false) {
            $responseData = json_decode($response, true);
            if (isset($responseData['result']['message_id'])) {
                return $responseData['result']['message_id'];
            }
        }
        return null;
    }

    // 检查消息是否存在的方法
    private function checkMessageExists($chat_id, $message_id, $channel)
    {
        $url = "https://api.telegram.org/bot$channel/forwardMessage";
        $data = [
            'chat_id' => $chat_id,
            'from_chat_id' => $chat_id,
            'message_id' => $message_id
        ];

        $options = [
            'http' => [
                'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
                'method' => 'POST',
                'content' => http_build_query($data)
            ]
        ];
        $context = stream_context_create($options);
        $response = @file_get_contents($url, false, $context);

        if ($response === false) {
            $error = error_get_last();
            if (strpos($error['message'], 'Bad Request: message to forward not found') !== false) {
                return false;
            }
        } else {
            return true;
        }
    }

    // 用于发送图片到 Telegram
    public function sendImageToTelegram($caption, $imageUrl, $chat_id, $channel, $tgMessageId)
    {
        $url = 'https://api.telegram.org/bot' . $channel . '/sendPhoto';
        $data = [
            'chat_id' => $chat_id,
            'photo' => $imageUrl,
            'caption' => $caption
        ];
        if (!empty($tgMessageId)) {
            $data['reply_to_message_id'] = $tgMessageId;
        }
        $options = [
            'http' => [
                'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
                'method' => 'POST',
                'content' => http_build_query($data)
            ]
        ];
        $context = stream_context_create($options);
        file_get_contents($url, false, $context);
    }

    public function index()
    {
        // 获取原始POST数据
        $rawPostData = file_get_contents('php://input');
        // 将JSON数据转换为PHP数组
        $data = json_decode($rawPostData, true);
        // 记录日志（可选）
        // file_put_contents('./webhook_log.txt', print_r($data, true), FILE_APPEND);

        // 处理接收到的数据
        if ($data) {
            // 获取房间 ID 和客服名称
            $room_id = $data['channel_id'];
            $kefu_name = $data['user_name'];
            if (empty($data['text'])) {
                // 返回成功响应
                echo json_encode(['status' => 'success']);
                exit();
            }
            // 验证是否为公司客服
            if (!$this->isKefu($kefu_name)) {
                echo json_encode(['status' => 'error', 'message' => '非公司客服,无需同步给tg']);
                return;
            }

            // 查询客服信息
            $kefu_sql = "SELECT * FROM pay_userchat WHERE room_id = :room_id AND status='0'";
            $stmt = $this->pdo->prepare($kefu_sql);
            $stmt->execute([':room_id' => $room_id]);
            $kefu_info = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ($kefu_info) {
                $chat_id = $kefu_info[0]['chat_id'];
                $channel = $kefu_info[0]['channel'];
                $message_id = $data['message_id'];
                $siteUrl = $data['siteUrl'];

                // 处理引用消息（如果有）
                // ...（根据您的需求处理）

                $messageText = "客服[" . $kefu_name . "]：" . $data['text'];
                // 发送文字消息到 Telegram
                $result_sendToTelegram = $this->sendToTelegram($messageText, $chat_id, $channel, null, null);

                // 记录消息对应关系
                if (!empty($result_sendToTelegram)) {
                    $set_sql = "INSERT INTO pay_tgrcinfo (chatid, tg_id, rc_id, createtime) 
                        VALUES (:chatid, :tg_id, :rc_id, :createtime)";
                    $this->pdo->prepare($set_sql)->execute([
                        ':chatid' => $chat_id,
                        ':tg_id' => $result_sendToTelegram,
                        ':rc_id' => $message_id,
                        ':createtime' => time(),
                    ]);
                }

                // 检查是否为图片消息
                if (isset($data['message']['file']) && !empty($data['message']['file']['_id'])) {
                    $imageUrl = $this->chat_url . '/file-upload/' . $data['message']['file']['_id'] . '/' . urlencode($data['message']['file']['name']);
                    $caption = $data['message']['attachments'][0]['description'];
                    $this->sendImageToTelegram($caption, $imageUrl, $chat_id, $channel, null);
                }
            }

            // 返回成功响应
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Invalid data']);
        }
    }
}

$handler = new RocketChatWebhookHandler();
$handler->index();
?>
