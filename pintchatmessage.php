<?php

class RocketToTelegramPinSync
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

    // 获取所有房间信息
    private function getRooms()
    {
        $stmt = $this->pdo->query("SELECT roomid, chatid, channel FROM pay_rcroom");
        return $stmt->fetchAll();
    }
    //管理员账号权限授权：
    public function guanliyuan($chatid){
        $kefu_sql = "select * FROM pay_rckefu where typelist ='1'";
        $kefu_query2 = $this->pdo->query($kefu_sql);
        $chatinfo = $kefu_query2->fetchAll();
        if(time()<$chatinfo[0]['updatetime']){
           return array('adminUserId'=>$chatinfo[0]['userId'],'adminToken'=>$chatinfo[0]['authToken'],'username'=>$chatinfo[0]['username']);
        }
        
        //这里需要管理员账号跟密码：
        $loginData = [
            'user' => $chatinfo[0]['username'], // 用户名
            'password' => $chatinfo[0]['password'] // 密码
        ];
        $loginUrl = $this->chat_url."api/v1/login";
        // 发起登录请求
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $loginUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($loginData),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json'
            ]
        ]);
        
        $response = curl_exec($curl);
        curl_close($curl);
        
        $data = json_decode($response, true);
        
        if (!isset($data['data']['authToken'])) {
           $this->xiaoxi("管理员信息异常",$chatid);
        }
        $authToken = $data['data']['authToken'];
        $userId = $data['data']['userId'];
        $updatetime = time()+(90*24*60*60);
        //修改增加：
        $id = $chatinfo[0]['id'];
        $this->pdo->exec("UPDATE pay_rckefu SET userId='".$userId."',authToken='".$authToken."',updatetime='".$updatetime."' WHERE id='" . $id . "'");
        return array('adminUserId'=>$userId,'adminToken'=>$authToken,'username'=>$chatinfo[0]['username']);
    }
    // 获取 Rocket.Chat 房间的置顶消息
    private function getPinnedMessage($roomId,$chatid)
    {
        //https://ccc.zmchat.xyz/api/v1/chat.getPinnedMessages?roomId=672039224512925822c794c5
        //https://kefu.epij.top/api/v1/chat.getPinnedMessages?roomId=672039224512925822c794c5
        $url = $this->chat_url."/api/v1/chat.getPinnedMessages?roomId=$roomId";
        $kefu_data = $this->guanliyuan($chatid);
        $authToken = $kefu_data['adminToken'];
        $adminUserId =$kefu_data['adminUserId'];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "X-Auth-Token: $authToken",
            "X-User-Id: $adminUserId",
            "Content-Type: application/json"
        ]);

        $response = curl_exec($ch); 
        curl_close($ch);
        
        $data = json_decode($response, true);
        return $data ?$data:null;
    }

    // 获取 Telegram 消息 ID 对应的 Rocket.Chat 消息 ID
    private function getTelegramMessageId($rcMessageId)
    {
        
        $kefu_sql = "SELECT * FROM pay_tgrcinfo WHERE rc_id ='" . $rcMessageId . "'";
        $kefu_query = $this->pdo->query($kefu_sql);
        $kefu_info = $kefu_query->fetchAll();
        if(!$kefu_info){
            return array(false,false,false);
        }else{
            return array(true,$kefu_info[0]['status'],$kefu_info[0]['tg_id']);
        }
       
    }

    // 置顶 Telegram 消息
    private function pinTelegramMessage($chatId, $messageId, $token)
    {
        $url = "https://api.telegram.org/bot$token/pinChatMessage";
        $data = [
            'chat_id' => $chatId,
            'message_id' => $messageId,
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        $response = curl_exec($ch);
        curl_close($ch);

        $result = json_decode($response, true);
     
        if (isset($result['ok']) && $result['ok'] === true) {
            return true;
        } else {
             return false;
        }
    }
    // 取消 Telegram 上的置顶消息
    private function unpinTelegramMessage($chatId, $messageId, $token)
    {
        $url = "https://api.telegram.org/bot$token/unpinChatMessage";
        $data = [
            'chat_id' => $chatId,
            'message_id' => $messageId,
        ];
    
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    
        $response = curl_exec($ch);
        curl_close($ch);
    
        $result = json_decode($response, true);
    
        return isset($result['ok']) && $result['ok'] === true;
    }
    public function sendToTelegram($messageText, $chat_id, $channel,$tgMessageId="")
    {
        $url = 'https://api.telegram.org/bot' . $channel . '/sendMessage';
        $data = [
            'chat_id' => $chat_id,
            'text' => $messageText
        ];
        if(!empty($tgMessageId)){
              $data['reply_to_message_id'] = $tgMessageId;// 引用对应的 Telegram 消息
        }
        var_dump($data);
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
    // 检查并同步置顶消息
    public function syncPinnedMessages()
    {
        $rooms = $this->getRooms();
       
        
        foreach ($rooms as $room) {
            $roomId = $room['roomid'];
            $chatId = $room['chatid'];
            $token = $room['channel'];
           
            // 获取最新的置顶消息
            $pinnedMessage = $this->getPinnedMessage($roomId, $chatId);
            
            if(count($pinnedMessage['messages'])>0){
                //遍历查询是不是已经记录成功了：
                foreach ($pinnedMessage['messages'] as $key=>$value){
                    $rcMessageId = $value['_id'];
                    // return array(true,$kefu_info[0]['status'],$kefu_info[0]['tg_id']);
                    $tgMessageinfo = $this->getTelegramMessageId($rcMessageId);
                    if($tgMessageinfo[0] && $tgMessageinfo[1] == '0'){
                        //存在并且状态没有置顶成功，那就需要执行以下置顶了：
                        $tgMessageId =$tgMessageinfo[2];
                        $result_status = $this->pinTelegramMessage($chatId, $tgMessageId, $token);
                        if($result_status){
                            //把状态修改一下:
                            $this->pdo->exec("UPDATE pay_tgrcinfo SET status='1' WHERE rc_id='" . $rcMessageId . "'");
                            echo "置顶成功";
                        }else{
                            
                            $messageText = "置顶传递失败，请把绑定的tgbot设置为群管理";
                            echo "111=>".$messageText."<br>";
                            echo "222=>".$chatId."<br>";
                            echo "333=>".$token."<br>";
                            $this->sendToTelegram($messageText, $chatId, $token);
                        }
                    }
                }
            }
            //这里还要查询一下是不是有取消置顶的情况：
             // 检查是否有需要取消置顶的消息
            $kefu_sql = "SELECT rc_id, tg_id FROM pay_tgrcinfo WHERE chatid ='" . $chatId . "' AND status = '1'";
            $kefu_query = $this->pdo->query($kefu_sql);
            $kefu_info = $kefu_query->fetchAll();
    
            $currentPinnedRcIds = array_column($pinnedMessage['messages'], '_id');
            
            foreach ($kefu_info as $info) {
                if (!in_array($info['rc_id'], $currentPinnedRcIds)) {
                    // 取消 Telegram 上的置顶消息
                    $this->unpinTelegramMessage($chatId, $info['tg_id'], $token);
                    // 更新数据库中的状态
                    $this->pdo->exec("UPDATE pay_tgrcinfo SET status='0' WHERE rc_id='" . $info['rc_id'] . "'");
                }
            }
           
        }
    }
}

$sync = new RocketToTelegramPinSync();
$sync->syncPinnedMessages();