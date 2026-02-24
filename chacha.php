<?php

class five
{
    private $chat_url = "";
    private $rocket_url = "";
    private $pdo;
    
    public function __construct()
    {
        include "rocket_jiqi.php";

        $this->chat_url = $chat_url;
        $this->rocket_url = $rocket_url;
        
        $this->pdo = new PDO("mysql:host=" . $dbHost . ";dbname=" . $dbName, $dbUser, $dbPassword, array(PDO::ATTR_PERSISTENT => true));
    }

    // 用于检查是否为公司客服
    private function isKefu($kefu_name)
    {
        $url = $this->rocket_url . "/api/Index/allkefu";
        $data = [
            'kefu_name' => $kefu_name
        ];

        $options = [
            'http' => [
                'header'  => "Content-Type: application/x-www-form-urlencoded\r\n",
                'method'  => 'POST',
                'content' => http_build_query($data)
            ]
        ];
        $context = stream_context_create($options);
        $response = file_get_contents($url, false, $context);
        $result = json_decode($response, true);

        // 检查 kefu_name 是否在客服列表中
       // 判断API请求是否成功并返回客服列表
        if ($result['code'] === 1 && isset($result['data'])) {
         
            $kefuList = $result['data'];
             
            foreach ($kefuList as $kefu) {
           
                if ($kefu['username'] == $kefu_name) {
                    return true;
                }
            }
        }
        return false;
    }

    // 用于发送消息到 Telegram
    public function sendToTelegram($messageText, $chat_id, $channel,$tgMessageId,$referencedRcMessageId)
    {
        $url = 'https://api.telegram.org/bot' . $channel . '/sendMessage';
        $data = [
            'chat_id' => $chat_id,
            'text' => $messageText
        ];
        if(!empty($tgMessageId)){
                // 检查消息 ID 是否存在
            if ($tgMessageId && !$this->checkMessageExists($chat_id, $tgMessageId, $channel)) {
               $tgMessageId = null; // 如果消息不存在，将其置为 null
            }else{
               $data['reply_to_message_id'] = $tgMessageId;// 引用对应的 Telegram 消息
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
            $responseData = json_decode($response, true); // 解码 JSON 数据
            if (isset($responseData['result']['message_id'])) {
                return $responseData['result']['message_id']; // 返回 message_id
            }
        }
        return null; // 发送失败或未能获取到 message_id 时返回 null
    }
    
    // 处理@提及反向转换：将Rocket.Chat的@username转换为Telegram的@tg_user_name
    private function convertRcMentionsToTg($text)
    {
        if (empty($text)) {
            return $text;
        }
        
        // 使用正则表达式匹配@username
        preg_match_all('/@(\w+)/', $text, $matches, PREG_OFFSET_CAPTURE);
        
        if (empty($matches[1])) {
            return $text;
        }
        
        $mentions = [];
        foreach ($matches[1] as $match) {
            $rc_username = $match[0];
            $offset = $match[1] - 1; // 减去@符号的位置
            
            // 查询pay_userchatrc表中对应的tg_user_name
            $at_sql = "SELECT tg_user_name FROM pay_userchatrc WHERE username = :username LIMIT 1";
            $at_stmt = $this->pdo->prepare($at_sql);
            $at_stmt->execute([':username' => $rc_username]);
            $at_result = $at_stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($at_result && !empty($at_result['tg_user_name'])) {
                $mentions[] = [
                    'rc_username' => $rc_username,
                    'tg_username' => $at_result['tg_user_name'],
                    'offset' => $offset,
                    'length' => strlen($rc_username) + 1
                ];
            }
        }
        
        // 从后往前替换，避免偏移量变化的问题
        if (!empty($mentions)) {
            // 按offset降序排序
            usort($mentions, function($a, $b) {
                return $b['offset'] - $a['offset'];
            });
            
            foreach ($mentions as $mention) {
                $old_text = '@' . $mention['rc_username'];
                $new_text = '@' . $mention['tg_username'];
                $text = substr_replace($text, $new_text, $mention['offset'], $mention['length']);
            }
        }
        
        return $text;
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
        $response = @file_get_contents($url, false, $context); // 使用 @ 忽略警告
    
        if ($response === false) {
            $error = error_get_last();
            if (strpos($error['message'], 'Bad Request: message to forward not found') !== false) {
                return false; // 消息不存在
            }
        } else {
            return true; // 消息存在
        }
    }
    
    // 用于发送图片到 Telegram
    public function sendImageToTelegram($caption, $imageUrl, $chat_id, $channel,$tgMessageId)
    {
        $url = 'https://api.telegram.org/bot' . $channel . '/sendPhoto';
        $data = [
            'chat_id' => $chat_id,
            'photo' => $imageUrl,
            'caption' => $caption 
        ];
        if(!empty($tgMessageId)){
              $data['reply_to_message_id'] = $tgMessageId;// 引用对应的 Telegram 消息
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
        file_put_contents('./webhook_log2.txt', print_r($data, true), FILE_APPEND);

        // 处理接收到的数据
        if ($data) {
            // 获取房间 ID 和客服名称
            $room_id = $data['channel_id'];
            $kefu_name = $data['user_name'];
            if(empty($data['text'])){
                    // 返回成功响应
                echo json_encode(['status' => 'success']);
                exit();
            }
            // 验证是否为公司客服
            if (!$this->isKefu($kefu_name)) {
                echo json_encode(['status' => 'error', 'message' => '非公司客服,无需同步给tg']);
                return;
            }
            $kefucha_sql = "SELECT * FROM pay_userchat WHERE kefu_name ='" . $kefu_name . "'";
            $kefucha_query = $this->pdo->query($kefucha_sql);
            $kefucha_info = $kefucha_query->fetchAll(); 
            
            
            // 查询客服信息
            $kefu_sql = "SELECT * FROM pay_userchat WHERE room_id ='" . $room_id . "' AND status='0' ";
            $kefu_query = $this->pdo->query($kefu_sql);
            $kefu_info = $kefu_query->fetchAll();

            if ($kefu_info) {
                $chat_id = $kefu_info[0]['chat_id'];
                $channel = $kefu_info[0]['channel'];
                //这里需要改下，是拿当前客服的channel 
               
                $channel = $kefucha_info[0]['channel'];
                
                
                $message_id = $data['message_id'];
                
                $siteUrl = $data['siteUrl'];
                //查询一下是不是存在引用：
                 // 判断 Rocket.Chat 消息是否为引用
                if (strpos($data['text'], '/channel/') !== false) {
                    // 如果消息包含 Rocket.Chat 链接，则视为引用消息
                    preg_match('/msg=([a-zA-Z0-9]+)/', $data['text'], $matches);
                    $referencedRcMessageId = $matches[1] ?? null;
            
                    if ($referencedRcMessageId) {
                        // 查找引用的 Rocket.Chat 消息对应的 Telegram 消息 ID
                        $query = "SELECT tg_id FROM pay_tgrcinfo WHERE rc_id = :rc_id";
                        $statement = $this->pdo->prepare($query);
                        $statement->execute([':rc_id' => $referencedRcMessageId]);
                        $tgMessageInfo = $statement->fetch();
            
                        if ($tgMessageInfo) {
                            $tgMessageId = $tgMessageInfo['tg_id'];
                        } else {
                            $tgMessageId = null;
                        }
                    }
                }
                if(!empty($tgMessageId)){
                    // 去除引用部分，仅发送纯文本内容
                    $rep_messageText = trim(preg_replace('/\[.*\]\(.*?\)/', '', $data['text']));
                    if(empty($rep_messageText)){
                        // 返回成功响应
                        echo json_encode(['status' => 'success']);
                        exit();
                    }
                }
                
                if(empty($rep_messageText)){
                    $rep_messageText = $data['text'];
                }
                
                // 转换@提及：将Rocket.Chat的@username转换为Telegram的@tg_user_name
                $rep_messageText = $this->convertRcMentionsToTg($rep_messageText);

                // 获取消息内容
                $messageText = "客服[" . $kefu_name . "]：" . $rep_messageText;
                // 发送文字消息到 Telegram
                $result_sendToTelegram = $this->sendToTelegram($messageText, $chat_id, $channel,$tgMessageId,$referencedRcMessageId);
                //这里发送成功了，需要记录一下关系：
                 
                if(!empty($result_sendToTelegram)){
                    $set_sql = "INSERT INTO pay_tgrcinfo (chatid,tg_id, rc_id, createtime) 
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
                    // 转换@提及
                    if (!empty($caption)) {
                        $caption = $this->convertRcMentionsToTg($caption);
                    }
                    $this->sendImageToTelegram($caption, $imageUrl, $chat_id, $channel,$tgMessageId);
                }
            }

            // 返回成功响应
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Invalid data']);
        }
    }
}

$oen = new five();
$oen->index();
