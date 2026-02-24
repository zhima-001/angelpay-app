<?php
$nosession = true;
require './includes/common.php';

// 引入机器人配置
include "cron_jiqi.php";

// 获取参数
$message = $_REQUEST['message'] ?: "默认消息";
$chat_id = $_REQUEST['chat_id'] ?: $laoban_chatid; // 默认发送给老板
$parse_mode = $_REQUEST['parse_mode'] ?: 'HTML'; // HTML 或 Markdown

/**
 * 简化的Telegram机器人类
 */
class SimpleTelegramBot {
    private $token;
    private $link;
    
    public function __construct($token) {
        $this->token = $token;
        $this->link = 'https://api.telegram.org/bot' . $token;
    }
    
    /**
     * 发送消息到Telegram
     */
    public function sendMessage($chat_id, $text, $parse_mode = 'HTML') {
        $parameter = array(
            'chat_id' => $chat_id,
            'parse_mode' => $parse_mode,
            'text' => $text,
            'disable_web_page_preview' => true
        );
        
        return $this->http_post_data('sendMessage', json_encode($parameter));
    }
    
    /**
     * 发送带键盘的消息
     */
    public function sendMessageWithKeyboard($chat_id, $text, $keyboard, $parse_mode = 'HTML') {
        $parameter = array(
            'chat_id' => $chat_id,
            'parse_mode' => $parse_mode,
            'text' => $text,
            'reply_markup' => $keyboard,
            'disable_web_page_preview' => true
        );
        
        return $this->http_post_data('sendMessage', json_encode($parameter));
    }
    
    /**
     * HTTP POST 请求
     */
    private function http_post_data($action, $data_string) {
        $url = $this->link . "/" . $action;
        $ch = curl_init();
        
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json; charset=utf-8',
            'Content-Length: ' . strlen($data_string)
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $return_content = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        return array(
            'http_code' => $http_code,
            'content' => $return_content
        );
    }
}

// 创建机器人实例
$bot = new SimpleTelegramBot($token);

// 发送消息
$result = $bot->sendMessage($chat_id, $message, $parse_mode);

// 记录日志
$log_message = "消息发送到 chat_id: {$chat_id}, 内容: {$message}, 结果: " . 
               ($result['http_code'] == 200 ? "成功" : "失败");
file_put_contents('simple_bot_message_log.txt', date('Y-m-d H:i:s') . " - " . $log_message . "\n", FILE_APPEND);

// 返回结果
if ($result['http_code'] == 200) {
    $response = json_decode($result['content'], true);
    if ($response && $response['ok']) {
        exit('{"code":1,"msg":"消息发送成功","message_id":"' . $response['result']['message_id'] . '"}');
    } else {
        exit('{"code":-1,"msg":"消息发送失败","error":"' . ($response['description'] ?? '未知错误') . '"}');
    }
} else {
    exit('{"code":-1,"msg":"HTTP请求失败","http_code":"' . $result['http_code'] . '"}');
}
?>

