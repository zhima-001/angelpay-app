<?php


class five
{
     public function __construct()
    {

        include "cron_jiqi.php";
        $this->chat_url=$chat_url;
        $this->pdo = new PDO("mysql:host=" . $dbHost . ";dbname=" . $dbName, $dbUser, $dbPassword, array(PDO::ATTR_PERSISTENT => true));
    }
    public function sendToTelegram($messageText,$chat_id,$channel) {
        $url = 'https://api.telegram.org/bot' .$channel. '/sendMessage';
        $data = [
            'chat_id' => $chat_id,
            'text' => $messageText
        ];
    
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
    
    
    function sendFileToTelegram($File, $caption,$chat_id,$channel) {
        $url = 'https://api.telegram.org/bot' .  $channel. '/sendDocument';
        $postData = [
            'chat_id' => $chat_id,
            'document' => new CURLFile($File), 
            'caption' => $caption 
        ];
    
        // 初始化 cURL
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    
        // 执行请求并获取响应
        $response = curl_exec($ch);
    
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        } else {
            echo 'Response: ' . $response;
        }
    
        // 关闭 cURL 会话
        curl_close($ch);
    }
    
    function sendVideoToTelegram($photoUrl, $caption,$chat_id,$channel) {
        $url = 'https://api.telegram.org/bot' .  $channel. '/sendVideo';
        $postData = [
            'chat_id' => $chat_id,
            'video' => $photoUrl,
            'caption' => $caption 
        ];
    
        // 初始化 cURL
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    
        // 执行请求并获取响应
        $response = curl_exec($ch);
    
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        } else {
            echo 'Response: ' . $response;
        }
    
        // 关闭 cURL 会话
        curl_close($ch);
    }
    
    function sendGifToTelegram($photoUrl, $caption,$chat_id,$channel) {
        $url = 'https://api.telegram.org/bot' .  $channel. '/sendAnimation';
        $postData = [
            'chat_id' => $chat_id,
            'animation' => $photoUrl,
            'caption' => $caption 
        ];
    
        // 初始化 cURL
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    
        // 执行请求并获取响应
        $response = curl_exec($ch);
    
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        } else {
            echo 'Response: ' . $response;
        }
    
        // 关闭 cURL 会话
        curl_close($ch);
    }
    function sendPhotoToTelegram($photoUrl, $caption,$chat_id,$channel) {
        $url = 'https://api.telegram.org/bot' .  $channel. '/sendPhoto';
        $postData = [
            'chat_id' => $chat_id,
            'photo' => $photoUrl,
            'caption' => $caption
        ];
    
        // 初始化 cURL
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    
        // 执行请求并获取响应
        $response = curl_exec($ch);
    
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        } else {
            echo 'Response: ' . $response;
        }
    
        // 关闭 cURL 会话
        curl_close($ch);
    }
    public function index(){
        // 获取原始POST数据
        $rawPostData = file_get_contents('php://input');
       
        // 将JSON数据转换为PHP数组
        $data = json_decode($rawPostData, true);
        file_put_contents('./webhook_log.txt', print_r($data, true), FILE_APPEND);
        // 处理接收到的数据
        if ($data) {
            // 根据事件类型处理数据
            if ($data['type'] == 'Message') {
                
                $room_id =  $data['_id'];
                $kefu_sql = "select * FROM pay_userchat where room_id ='" . $room_id . "' and status='0'"; 
                $kefu_query = $this->pdo->query($kefu_sql);
                $kefu_info = $kefu_query->fetchAll();
                if($kefu_info){
                    
                    //gif：
                    if($data['messages'][0]['file']['type'] == "image/gif"){
                        $caption = $data['messages'][0]['attachments'][0]['description'];
                        $messageText ="客服[".$data['agent']['username']."]发送了一张gif并且附带一句话：".$caption;
                             // 获取图片 URL 和说明
                        $photoUrl = $data['messages'][0]['fileUpload']['publicFilePath'];
                       
                         // 发送消息到 Telegram
                        $chat_id = $kefu_info[0]['chat_id'];
                        $channel = $kefu_info[0]['channel'];
                        $this->sendGifToTelegram($photoUrl, $messageText,$chat_id,$channel);
                        exit();
                    }
                    //mp4： 
                     if($data['messages'][0]['file']['type'] == "video/mp4"){
                        $caption = $data['messages'][0]['attachments'][0]['description'];
                        $messageText ="客服[".$data['agent']['username']."]发送了一段视频并且附带一句话：".$caption;
                             // 获取图片 URL 和说明
                        $photoUrl = $data['messages'][0]['fileUpload']['publicFilePath'];
                       
                         // 发送消息到 Telegram
                        $chat_id = $kefu_info[0]['chat_id'];
                        $channel = $kefu_info[0]['channel'];
                        $this->sendVideoToTelegram($photoUrl, $messageText,$chat_id,$channel);
                        exit();
                    }
                    //application/vnd.android.package-archive  文件：  application/pdf pdf文件：  xlsx文件
                    if($data['messages'][0]['file']['type'] == "application/vnd.android.package-archive" || $data['messages'][0]['file']['type'] == "application/pdf" || $data['messages'][0]['file']['type'] == "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"){
                        $caption = $data['messages'][0]['attachments'][0]['description'];
                        $messageText ="客服[".$data['agent']['username']."]发送了一个文件并且附带一句话：".$caption;
                             // 获取图片 URL 和说明
                        $publicFilePath = $data['messages'][0]['fileUpload']['publicFilePath'];
                       
                         // 发送消息到 Telegram
                        $chat_id = $kefu_info[0]['chat_id'];
                        $channel = $kefu_info[0]['channel'];
                        $this->sendFileToTelegram($publicFilePath, $messageText,$chat_id,$channel);
                        exit(); 
                    }
                    
           
                    
                    //这里先看看是不是发送的一张照片：
                    if(!empty($data['messages'][0]['file']['name'])){
                         // 获取消息内容
                          
                         if($data['messages'][0]['attachments'][0]['description']){
                             $caption = $data['messages'][0]['attachments'][0]['description'];
                             $messageText ="客服[".$data['agent']['username']."]发送了一张图片并且附带一句话：".$caption;
                         }else{
                             $messageText ="客服[".$data['agent']['username']."]发送了一张图片";
                         }
                        
                        
                        // 获取图片 URL 和说明
                        $photoUrl = $data['messages'][0]['fileUpload']['publicFilePath'];
                       
                         // 发送消息到 Telegram
                        $chat_id = $kefu_info[0]['chat_id'];
                        $channel = $kefu_info[0]['channel'];
                        $this->sendPhotoToTelegram($photoUrl, $messageText,$chat_id,$channel);
                        exit();
                    }
                    
                    
                    //这里要去更新一下token = 
                    $visitorToken = $data['visitor']['token'];
                    // $set_gaitoken="update pay_userchat set visitorToken ='" . $visitorToken . "' where  room_id='" . $room_id . "'";
                    // $this->pdo->exec($set_gaitoken);
                
                    // 获取消息内容
                    $messageText ="客服[".$data['agent']['username']."]：".$data['messages'][0]['msg'];
                    // 发送消息到 Telegram
                    $chat_id = $kefu_info[0]['chat_id'];
                    $channel = $kefu_info[0]['channel'];
                    $this->sendToTelegram($messageText,$chat_id,$channel);
                }
                
        
                
            }
            if ($data['type'] == 'LivechatSession') {
                
                $room_id =  $data['_id'];
                $kefu_sql = "select * FROM pay_userchat where room_id ='" . $room_id . "'"; 
                $kefu_query = $this->pdo->query($kefu_sql);
                $kefu_info = $kefu_query->fetchAll();
                if($kefu_info){
                    
                    $set_status="delete from  pay_userchat where  room_id='" . $room_id . "'";
                    $this->pdo->exec($set_status);
                    // 获取消息内容
                    $messageText ="客服[".$data['agent']['username']."]关闭了你的临时客服会话";
                    // 发送消息到 Telegram
                    $chat_id = $kefu_info[0]['chat_id'];
                    $channel = $kefu_info[0]['channel'];
                    $this->sendToTelegram($messageText,$chat_id,$channel);
                    //这里可以做一个消息信息列表的转发：
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
?>
