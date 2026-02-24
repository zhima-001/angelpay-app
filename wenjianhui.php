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
    // 用于发送消息到 Telegram
    public function sendToTelegram($messageText, $chat_id, $channel, $tgMessageId)
    {
        $url = 'https://api.telegram.org/bot' . $channel . '/sendMessage';
        $data = [
            'chat_id' => $chat_id,
            'text' => $messageText
        ];
        if (!empty($tgMessageId)) {
            // 检查消息 ID 是否存在
            if ($tgMessageId && !$this->checkMessageExists($chat_id, $tgMessageId, $channel)) {
               $tgMessageId = null; // 如果消息不存在，将其置为 null
            }else{
               $data['reply_to_message_id'] = $tgMessageId;// 引用对应的 Telegram 消息
           }
            //$data['reply_to_message_id'] = $tgMessageId;
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
    
    // 下载远程文件到临时文件
    private function downloadFile($url, $tempDir = null)
    {
        if ($tempDir === null) {
            $tempDir = sys_get_temp_dir();
        }
        
        // 创建临时文件
        $tempFile = tempnam($tempDir, 'telegram_upload_');
        
        // 下载文件
        $ch = curl_init($url);
        $fp = fopen($tempFile, 'wb');
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        fclose($fp);
        
        if ($httpCode !== 200) {
            @unlink($tempFile);
            return false;
        }
        
        return $tempFile;
    }
    
    // 用于发送图片到 Telegram
    public function sendImageToTelegram($caption, $imageUrl, $chat_id, $channel, $tgMessageId)
    {
        // 先下载图片到临时文件
        $tempFile = $this->downloadFile($imageUrl);
        if ($tempFile === false) {
            return null;
        }
        
        $url = 'https://api.telegram.org/bot' . $channel . '/sendPhoto';
        $data = [
            'chat_id' => $chat_id,
            'caption' => $caption,
            'photo' => new CURLFile($tempFile)
        ];
        if (!empty($tgMessageId)) {
            // 检查消息 ID 是否存在
            if ($tgMessageId && !$this->checkMessageExists($chat_id, $tgMessageId, $channel)) {
               $tgMessageId = null; // 如果消息不存在，将其置为 null
            }else{
               $data['reply_to_message_id'] = $tgMessageId;// 引用对应的 Telegram 消息
           }
        }
    
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        // 删除临时文件
        @unlink($tempFile);

        if ($httpCode === 200) {
            $responseData = json_decode($response, true);
            if (isset($responseData['result']['message_id'])) {
                return $responseData['result']['message_id'];
            }
        }
        return null;
    }
    
    // 用于发送视频到 Telegram
    public function sendVideoToTelegram($caption, $videoUrl, $chat_id, $channel, $tgMessageId)
    {
        $url = 'https://api.telegram.org/bot' . $channel . '/sendVideo';
        $data = [
            'chat_id' => $chat_id,
            'video' => $videoUrl,
            'caption' => $caption
        ];
        if (!empty($tgMessageId)) {
            // 检查消息 ID 是否存在
            if ($tgMessageId && !$this->checkMessageExists($chat_id, $tgMessageId, $channel)) {
               $tgMessageId = null; // 如果消息不存在，将其置为 null
            }else{
               $data['reply_to_message_id'] = $tgMessageId;// 引用对应的 Telegram 消息
           }
            //$data['reply_to_message_id'] = $tgMessageId;
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
    
    // 用于发送文档到 Telegram
    public function sendDocumentToTelegram($caption, $File, $chat_id, $channel, $tgMessageId)
    {
        // 先下载文件到临时文件
        $tempFile = $this->downloadFile($File);
        if ($tempFile === false) {
            return null;
        }
        
        // 获取原始文件名
        $fileName = basename(parse_url($File, PHP_URL_PATH));
        if (empty($fileName)) {
            $fileName = 'document';
        }
        
        $url = 'https://api.telegram.org/bot' . $channel . '/sendDocument';
        $postData = [
            'chat_id' => $chat_id,
            'document' => new CURLFile($tempFile, null, $fileName),
            'caption' => $caption
        ];
        if (!empty($tgMessageId)) {
            // 检查消息 ID 是否存在
            if ($tgMessageId && !$this->checkMessageExists($chat_id, $tgMessageId, $channel)) {
               $tgMessageId = null; // 如果消息不存在，将其置为 null
            }else{
               $postData['reply_to_message_id'] = $tgMessageId;// 引用对应的 Telegram 消息
           }
        }
    
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        // 删除临时文件
        @unlink($tempFile);
    
        if ($httpCode === 200) {
            $responseData = json_decode($response, true);
            if (isset($responseData['result']['message_id'])) {
                return $responseData['result']['message_id'];
            }
        }
        return null;
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

        // 判断API请求是否成功并返回客服列表
        if ($result['code'] === 1 && isset($result['data'])) {
            $kefuList = $result['data'];
            foreach ($kefuList as $kefu) {
                if ($kefu['username'] === $kefu_name) {
                    return true;
                }
            }
        }
        return false;
    }

    public function index()
    {
        // 获取原始POST数据
        $rawPostData = file_get_contents('php://input');

        // 将JSON数据转换为PHP数组
        $data = json_decode($rawPostData, true);
        file_put_contents('./webhook_log2.txt', print_r($rawPostData, true), FILE_APPEND);
        
        // 处理接收到的数据
        if ($data) {
            // 获取房间 ID 和客服名称
            $room_id = $data['channel_id'];
            $kefu_name = $data['user_name'];
            $rc_message_id = $data['message_id'];
           
            // 验证是否为公司客服
            if (!$this->isKefu($kefu_name)) {
                echo json_encode(['status' => 'error', 'message' => '非公司客服,无需同步给tg']);
                return;
            }
            $kefucha_sql = "SELECT * FROM pay_userchat WHERE kefu_name ='" . $kefu_name . "'";
            $kefucha_query = $this->pdo->query($kefucha_sql);
            $kefucha_info = $kefucha_query->fetchAll(); 
            // 查询客服信息
            //AND kefu_name='" . $kefu_name . "'
            $kefu_sql = "SELECT * FROM pay_userchat WHERE room_id ='" . $room_id . "' AND status='0' ";
            $kefu_query = $this->pdo->query($kefu_sql);
            $kefu_info = $kefu_query->fetchAll();

            if ($kefu_info) {
                $chat_id = $kefu_info[0]['chat_id'];
                $channel = $kefu_info[0]['channel'];
                //这里需要改下，是拿当前客服的channel 
               
                //$channel = $kefucha_info[0]['channel']; 
                $imageExtensions = array('jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp');
                $file_type =$data['message']['file']['format']; 
                $reply_to_message_id = "";
                // 检查消息是否为回复消息
                
                $rc_replied_message_id = "";
                
           
                $pattern = '/msg=([a-zA-Z0-9-_]+)/';// 正则提取 msg 参数
                if (preg_match($pattern, $data['message']['msg'], $matches)) {
                        $rc_replied_message_id = $matches[1];
                }
                
                // if (isset($data['message']['attachments'][0]['message_link'])) {
                //     $message_link = $data['message']['attachments'][0]['message_link'];
                //     // 从 message_link 中提取被引用的 Rocket.Chat 消息 ID
                //     parse_str(parse_url($message_link, PHP_URL_QUERY), $query_params);
                //     $rc_replied_message_id = $query_params['msg'] ?? null;

                    if ($rc_replied_message_id) {
                        // 查询数据库，获取对应的 Telegram 消息 ID
                        $query = "SELECT tg_id FROM pay_tgrcinfo WHERE rc_id = :rc_id";
                        $stmt = $this->pdo->prepare($query);
                        $stmt->execute([':rc_id' => $rc_replied_message_id]);
                        $result = $stmt->fetch(PDO::FETCH_ASSOC);
                        if ($result) {
                            $reply_to_message_id = $result['tg_id'];
                        }
                    }
                //}
                 
                    
               
                // 检查是否为图片消息 //isset($data['message']['file']) && !empty($data['message']['file']['_id']) &&
                if (in_array($file_type,$imageExtensions)) {
             
                    //$imageUrl =  $this->chat_url.'/file-upload/' . $data['message']['file']['_id'] . '/' . urlencode($data['message']['file']['name']);
                    $imageUrl = $this->chat_url . $data['message']['attachments'][0]['image_url'];
                    // 发送图片到 Telegram
                    // $caption = $data['message']['attachments'][0]['description'];
                    if(!empty($data['message']['attachments'][0]['description'])){
                        $description = $data['message']['attachments'][0]['description'];
                        // 转换@提及
                        $description = $this->convertRcMentionsToTg($description);
                        $caption ="客服[".$kefu_name."]:发送了一个图片给你,并且附上了一句话：".$description;
                    }else{
                        $caption ="客服[".$kefu_name."]:发送了一个图片给你";
                    }
                  
                    // $this->sendToTelegram($imageUrl, $chat_id, $channel);
                    $message_id = $this->sendImageToTelegram($caption, $imageUrl, $chat_id, $channel,$reply_to_message_id); 
                
                    if(!empty($message_id)){
                        $set_sql = "INSERT INTO pay_tgrcinfo (chatid,tg_id, rc_id, createtime) 
                            VALUES (:chatid, :tg_id, :rc_id, :createtime)";
                        $this->pdo->prepare($set_sql)->execute([
                            ':chatid' => $chat_id,
                            ':tg_id' => $message_id,
                            ':rc_id' => $rc_message_id,
                            ':createtime' => time(),
                        ]);
                    }
                }
                // 检查是否为视频消息
                if (isset($data['message']['file']) && !empty($data['message']['file']['_id']) && strpos($data['message']['file']['type'], 'video/') === 0) {
                    $videoUrl =  $this->chat_url.'/file-upload/' . $data['message']['file']['_id'] . '/' . urlencode($data['message']['file']['name']);
                    // 发送视频到 Telegram
                    // $caption = isset($data['message']['attachments'][0]['description']) ? $data['message']['attachments'][0]['description'] : '视频消息';
                    if(!empty($data['message']['attachments'][0]['description'])){
                        $description = $data['message']['attachments'][0]['description'];
                        // 转换@提及
                        $description = $this->convertRcMentionsToTg($description);
                        $caption ="客服[".$kefu_name."]:发送了一个视频给你,并且附上了一句话：！".$description;
                    }else{
                        $caption ="客服[".$kefu_name."]:发送了一个视频给你";
                    }
                    
                    
                    $message_id = $this->sendVideoToTelegram($caption, $videoUrl, $chat_id, $channel,$reply_to_message_id);
                    if(!empty($message_id)){
                        $set_sql = "INSERT INTO pay_tgrcinfo (chatid,tg_id, rc_id, createtime) 
                            VALUES (:chatid, :tg_id, :rc_id, :createtime)";
                        $this->pdo->prepare($set_sql)->execute([
                            ':chatid' => $chat_id,
                            ':tg_id' => $message_id,
                            ':rc_id' => $rc_message_id,
                            ':createtime' => time(),
                        ]);
                    }
                    exit();
                }

                
                // 检查是否为 文件
                if ($data['message']['attachments'][0]['type'] == 'file') {
                    // 优先使用 fileUpload.publicFilePath，如果不存在则手动构建
                    if (isset($data['message']['fileUpload']['publicFilePath']) && !empty($data['message']['fileUpload']['publicFilePath'])) {
                        $documentUrl = $data['message']['fileUpload']['publicFilePath'];
                    } else {
                        $documentUrl = $this->chat_url . '/file-upload/' . $data['message']['file']['_id'] . '/' . urlencode($data['message']['file']['name']);
                    }
                    // 发送文件到 Telegram
    
                     if(!empty($data['message']['attachments'][0]['description'])){
                        $description = $data['message']['attachments'][0]['description'];
                        // 转换@提及
                        $description = $this->convertRcMentionsToTg($description);
                        $caption ="客服[".$kefu_name."]:发送了一个文件给你,并且附上了一句话：！".$description;
                    }else{
                        $caption ="客服[".$kefu_name."]:发送了一个文件给你".$file_type;
                    }
                    
                    $message_id = $this->sendDocumentToTelegram($caption, $documentUrl, $chat_id, $channel,$reply_to_message_id);
                    if(!empty($message_id)){
                        $set_sql = "INSERT INTO pay_tgrcinfo (chatid,tg_id, rc_id, createtime) 
                            VALUES (:chatid, :tg_id, :rc_id, :createtime)";
                        $this->pdo->prepare($set_sql)->execute([
                            ':chatid' => $chat_id,
                            ':tg_id' => $message_id,
                            ':rc_id' => $rc_message_id,
                            ':createtime' => time(),
                        ]);
                    }
                }
                
            }

            // 返回成功响应
            echo json_encode(['status' => 'success']);
        } else {
            // 返回错误响应
            echo json_encode(['status' => 'error', 'message' => 'Invalid data']);
        }
    }
}

$oen = new five();
$oen->index();
