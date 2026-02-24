<?php


class Http
{

    public function sendPostRequest($url, $data = [], $headers = [])
    {
        // 初始化cURL会话
        $ch = curl_init($url);

        // 将传递的数据格式化为URL编码字符串
        $postData = http_build_query($data);

        // 设置cURL选项
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);  // 指定请求方式为POST
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);  // 传递POST数据
        if (!empty($headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);  // 设置HTTP请求头
        }

        // 执行请求并获取响应
        $response = curl_exec($ch);

        // 检查是否发生错误
        if (curl_errno($ch)) {
            $error_msg = curl_error($ch);
            curl_close($ch);
            return "cURL Error: $error_msg";
        }

        // 关闭cURL会话
        curl_close($ch);

        // 返回响应
        return $response;
    }

    /**
     * 发送一个POST请求
     * @param string $url 请求URL
     * @param array $params 请求参数
     * @param array $options 扩展参数
     * @return mixed|string
     */
    public static function post($url, $params = [], $options = [])
    {
        $req = self::sendRequest($url, $params, 'POST', $options);
//        return $req['ret'] ? $req['msg'] : '';
        return $req;
    }

    public static function http_post_data_two($url, $data_string)
    {

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_POST, 1);

        curl_setopt($ch, CURLOPT_URL, $url);

        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);

        curl_setopt($ch, CURLOPT_HTTPHEADER, array(


                'Content-Type: application/json; charset=utf-8',

                'Content-Length: ' . strlen($data_string))

        );

        ob_start();

        curl_exec($ch);

        $return_content = ob_get_contents();

        //echo $return_content."


        ob_end_clean();

        $return_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        // return array($return_code, $return_content);

        return $return_content;

    }


    /**
     * 发送一个GET请求
     * @param string $url 请求URL
     * @param array $params 请求参数
     * @param array $options 扩展参数
     * @return mixed|string
     */
    public static function get($url, $params = [], $options = [])
    {
        $req = self::sendRequest($url, $params, 'GET', $options);
        return $req['ret'] ? $req['msg'] : '';
    }

    /**
     * CURL发送Request请求,含POST和REQUEST
     * @param string $url 请求的链接
     * @param mixed $params 传递的参数
     * @param string $method 请求的方法
     * @param mixed $options CURL的参数
     * @return array
     */
    public static function sendRequest($url, $params = [], $method = 'POST', $options = [])
    {
        $method = strtoupper($method);
        $protocol = substr($url, 0, 5);
        $query_string = is_array($params) ? http_build_query($params) : $params;

        $ch = curl_init();
        $defaults = [];
        if ('GET' == $method) {
            $geturl = $query_string ? $url . (stripos($url, "?") !== false ? "&" : "?") . $query_string : $url;
            $defaults[CURLOPT_URL] = $geturl;
        } else {
            $defaults[CURLOPT_URL] = $url;
            if ($method == 'POST') {
                $defaults[CURLOPT_POST] = 1;
            } else {
                $defaults[CURLOPT_CUSTOMREQUEST] = $method;
            }
            $defaults[CURLOPT_POSTFIELDS] = $query_string;
        }

        $defaults[CURLOPT_HEADER] = false;
        $defaults[CURLOPT_USERAGENT] = "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/45.0.2454.98 Safari/537.36";
        $defaults[CURLOPT_FOLLOWLOCATION] = true;
        $defaults[CURLOPT_RETURNTRANSFER] = true;
        $defaults[CURLOPT_CONNECTTIMEOUT] = 3;
        $defaults[CURLOPT_TIMEOUT] = 30;

        // disable 100-continue
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:'));

        if ('https' == $protocol) {
            $defaults[CURLOPT_SSL_VERIFYPEER] = false;
            $defaults[CURLOPT_SSL_VERIFYHOST] = false;
        }

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
        return [
            'ret' => true,
            'msg' => $ret,
        ];
    }

    /**
     * 异步发送一个请求
     * @param string $url 请求的链接
     * @param mixed $params 请求的参数
     * @param string $method 请求的方法
     * @return boolean TRUE
     */
    public static function sendAsyncRequest($url, $params = [], $method = 'POST')
    {
        $method = strtoupper($method);
        $method = $method == 'POST' ? 'POST' : 'GET';
        //构造传递的参数
        if (is_array($params)) {
            $post_params = [];
            foreach ($params as $k => &$v) {
                if (is_array($v)) {
                    $v = implode(',', $v);
                }
                $post_params[] = $k . '=' . urlencode($v);
            }
            $post_string = implode('&', $post_params);
        } else {
            $post_string = $params;
        }
        $parts = parse_url($url);
        //构造查询的参数
        if ($method == 'GET' && $post_string) {
            $parts['query'] = isset($parts['query']) ? $parts['query'] . '&' . $post_string : $post_string;
            $post_string = '';
        }
        $parts['query'] = isset($parts['query']) && $parts['query'] ? '?' . $parts['query'] : '';
        //发送socket请求,获得连接句柄
        $fp = fsockopen($parts['host'], isset($parts['port']) ? $parts['port'] : 80, $errno, $errstr, 3);
        if (!$fp) {
            return false;
        }
        //设置超时时间
        stream_set_timeout($fp, 3);
        $out = "{$method} {$parts['path']}{$parts['query']} HTTP/1.1\r\n";
        $out .= "Host: {$parts['host']}\r\n";
        $out .= "Content-Type: application/x-www-form-urlencoded\r\n";
        $out .= "Content-Length: " . strlen($post_string) . "\r\n";
        $out .= "Connection: Close\r\n\r\n";
        if ($post_string !== '') {
            $out .= $post_string;
        }
        fwrite($fp, $out);
        //不用关心服务器返回结果
        //echo fread($fp, 1024);
        fclose($fp);
        return true;
    }

    /**
     * 发送文件到客户端
     * @param string $file
     * @param bool $delaftersend
     * @param bool $exitaftersend
     */
    public static function sendToBrowser($file, $delaftersend = true, $exitaftersend = true)
    {
        if (file_exists($file) && is_readable($file)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment;filename = ' . basename($file));
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate, post-check = 0, pre-check = 0');
            header('Pragma: public');
            header('Content-Length: ' . filesize($file));
            ob_clean();
            flush();
            readfile($file);
            if ($delaftersend) {
                unlink($file);
            }
            if ($exitaftersend) {
                exit;
            }
        }
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

        $this->token = "7475775639:AAFPR_aTywRDs0aqmMO_IgZoEjq_EZzyq6Y";
        $this->chat_url = $chat_url;
        $this->rocket_url = $rocket_url;

        $this->link = 'https://api.telegram.org/bot' . $this->token . '';
        $this->pdo = new PDO("mysql:host=" . $dbHost . ";dbname=" . $dbName, $dbUser, $dbPassword, array(PDO::ATTR_PERSISTENT => true));

    }

    public function index()
    {


        $data = json_decode(file_get_contents('php://input'), TRUE); //读取json并对其格式化
        $datatype = $data['message']['chat']['type'];//获取message


 
        if ($data['callback_query']) {
            $this->callback($data);
        } else {
            $chatid = $data['message']['chat']['id'];//获取chatid
            
            //这里还要把对应的客服拉到群里面去：
            //$this->xiaoxinoend(json_encode($data),$chatid);
            
            $message = $data['message']['text'];//获取message
            $userid = $data['message']['from']['id'];//获取message
            $username = $data['message']['from']['username'];//用户名称
            
            //消息ID：记录一下，找到他跟rc的关系：
            $tg_xiaoxi_id =  $data['message']['message_id'];
            
            
            //这里需要判断一下是不是正在进行客服聊天，如果是的话，需要直接走客服逻辑:
            $kefu_sql = "select * FROM pay_userchat where typelist ='1' and chat_id ='" . $chatid . "' and channel='".$this->token."' and status='0'";
            $kefu_query = $this->pdo->query($kefu_sql);
            $kefu_info = $kefu_query->fetchAll();
           //  $this->xiaoxinoend("mx机器人===》".$kefu_sql,$chatid);
            if ($kefu_info) {
                
                //这里还要监听一下消息是不是发送了？
                
                //这里需要查询这个用户是不是在对应的群组列表里面：
                //查询是不是存在：
                $room_sql = "select * FROM pay_rcroom where chatid ='".$chatid."'"; // and channel='".$this->token."'
                $room_query = $this->pdo->query($room_sql); 
                $roominfo = $room_query->fetchAll();
                
                if($roominfo){
                    //当前房间号 ：
                    $roomId = $roominfo[0]['roomid'];
                    
                    
                    //pst1:先查询有没这个用户的rc账号
                    $youke_sql = "select * FROM pay_userchatrc where tg_from_id ='" . $userid . "'";
                    $youke_query = $this->pdo->query($youke_sql);
                    $youke_info = $kefu_query->fetchAll();
                    if(!$youke_info){
                        //rc账号不存在的话，需要创建rc账号：
                        //$usercreate===> return array($updatetime,$name,$rc_userid,$usertoken,$tg_userid);
                        $usercreate = $this->usercreate($userid,$username,$chatid);
                        
                        $rc_userId = $usercreate[2];
                        $rc_usertoken = $usercreate[3];
                    }else{
                        $rc_userId = $youke_info[0]['userid'];
                        $rc_usertoken = $youke_info[0]['usertoken'];
                    }
                    
           
     
                    //pst2: 查询当前用户在不在群或者是私聊里面
                    $chaxun_sql = "select * FROM pay_rcroomdetail where room_id ='".$roomId."' and tg_from_id ='" . $userid . "'";
                    $cha_query = $this->pdo->query($chaxun_sql);
                    $cha_info = $cha_query->fetchAll(); 
            
                    //如果不在这个群里或者一对一里面，需要添加：
                    if(!$cha_info){
                        $add_result = $this->addUserToGroup($roomId, $rc_userId,$userid,$chatid,$rc_usertoken);
                        //$this->xiaoxi($add_result, $chatid);
                    }

                }else{
                    
                    $delete_sql2 = "DELETE FROM pay_userchat where typelist ='1' and chat_id ='" . $chatid . "' and channel='".$this->token."'";
                    $this->pdo->exec($delete_sql2); 
                    $this->xiaoxi("系统异常,请重新发起会话！", $chatid);
                }
                
                $token = $rc_usertoken;
                $room_id = $roomId;
                $agentId = $rc_userId;
                $message_kefu = $data['message'];
                $chatId = $chatid;
                //群名：
                $room_name = $roominfo[0]['roomname'];
                //这里先查询 这个群 是不是已经有机器人绑定了：
               
               
                    //当前的机器人不是对应的，就不执行后续的：
                if($roominfo[0]['channel']  != $this->token){
                        return false;
                }
               //进行置顶操作逻辑：
               if (isset($data['message']['pinned_message'])) {
                    // 获取被置顶消息的 ID 和内容
                    $tg_message_id = $data['message']['pinned_message']['message_id'];
                   
                    $result_zhidingxiaoxi = $this->zhidingxiaoxi($tg_message_id,$chatid);
                    if($result_zhidingxiaoxi['success']){
                        //置顶成功：
                        $this->pdo->exec("UPDATE pay_tgrcinfo SET status='1' WHERE tg_id='" . $tg_message_id . "'");
                        $this->xiaoxi("对应信息，置顶成功！",$chatid);
                    }
                    
               }
               // 检查是否是取消置顶事件
                if (isset($data['message']['unpinned_message'])) {
                    $chatid = $data['message']['chat']['id'];  // 获取chatid
                    $tg_message_id = $data['message']['unpinned_message']['message_id'];  // 获取被取消置顶的消息ID
                     $this->xiaoxinoend($tg_message_id,$chatid);
                    // 调用取消置顶处理函数
                    $this->cancelPin($tg_message_id, $chatid);
                }
               
               
                // 获取引用的消息 ID
                $tg_yinyong_message_id = $message_kefu['reply_to_message']['message_id'];
                        // 获取 Rocket.Chat 中对应的消息 ID
                $rc_yinyong_message_id = $this->getRocketMessageId($tg_yinyong_message_id, $chatid);
             
                $quoted_message_link = "{$this->chat_url}/channel/{$room_name}?msg={$rc_yinyong_message_id}";
        
                //检查是否引用了消息
                if (isset($message_kefu['reply_to_message'])) {
                        $message = "> 引用的消息内容: \n[点击查看原消息]($quoted_message_link)\n\n" . $message;
                }
                if (isset($message_kefu['text'])) {
                    // 处理文字消息
                    $text = $message_kefu['text'];
                    if (isset($message_kefu['reply_to_message'])) {
                        $text = $message;
                    }    
                        
                    $rc_xiaoxi_id = $this->sendMessageToGroup($token,$agentId,$room_id,$text,$chatid);
                } elseif (isset($message_kefu['photo'])) {
                    // 处理图片消息
                    $photo = $message_kefu['photo'];
                    $fileId = end($photo)['file_id'];

                    $filePath = $this->getTelegramFilePath($fileId);

                    $localFilePath = $this->downloadTelegramFile($filePath);
                    if ($localFilePath == "error") {
                        $this->xiaoxi("发送照片失败,请稍后再试试！", $chatid);
                    }
                    $description = "";
                    if (!empty($message_kefu['caption'])) {
                        $description = $message_kefu['caption'];
                    }
                    if (isset($message_kefu['reply_to_message'])) {
                         $description = "> 引用的消息内容: \n[点击查看原消息]($quoted_message_link)\n\n" . $description;
                    }
                    
                    $fileUrl = $this->uploadFileToLiveChatRoom($localFilePath, $room_id, $token,$agentId,$description, $chatid);
                    $rc_xiaoxi_id = $fileUrl['message']['_id'];
                                               

                    if ($fileUrl && isset($fileUrl['file'])) {
                        $fileUrl = $this->chat_url ."/". $fileUrl['file']['path'];
                        $info = $this->sendMessageToGroup($room_id, $token2, "![image]($fileUrl)");

                    }

                }elseif (isset($message_kefu['animation'])) {
                        // 处理GIF动画消息
                        $animation = $message_kefu['animation'];
                        $fileId = $animation['file_id']; // 直接获取 file_id
                    
                        $filePath = $this->getTelegramFilePath($fileId);
                    
                        // 如果 filePath 为空或者下载失败，处理错误
                        if (!$filePath) {
                            $this->xiaoxi("无法获取动画文件的路径，请稍后再试。", $chatid);
                            return;
                        }
                    
                        $localFilePath = $this->downloadTelegramFile($filePath);
                        if ($localFilePath == "error") {
                            $this->xiaoxi("下载动画文件失败，请稍后再试！", $chatid);
                            return;
                        }
                    
                        $description = "";
                        if (!empty($message_kefu['caption'])) {
                            $description = $message_kefu['caption'];
                        }
                        if (isset($message_kefu['reply_to_message'])) {
                         $description = "> 引用的消息内容: \n[点击查看原消息]($quoted_message_link)\n\n" . $description;
                        }
                        $fileUrl = $this->uploadFileToLiveChatRoom($localFilePath, $room_id, $token,$agentId,$description, $chatid);
                        $rc_xiaoxi_id = $fileUrl['message']['_id'];
                        if ($fileUrl && isset($fileUrl['file'])) {
                            $fileUrl = $this->chat_url .'/'. $fileUrl['file']['path'];
                           
                            $this->sendImageMessage($room_id, $token, "![animation]($fileUrl)"); 
                        } else {
                            $this->xiaoxi("上传动画文件失败，请稍后再试！", $chatid);
                        }
                }elseif (isset($message_kefu['sticker'])) {
                        // 处理GIF动画消息
                        $animation = $message_kefu['sticker'];
                        $fileId = $animation['file_id']; // 直接获取 file_id
                    
                        $filePath = $this->getTelegramFilePath($fileId);
                    
                        // 如果 filePath 为空或者下载失败，处理错误
                        if (!$filePath) {
                            $this->xiaoxi("无法获取贴纸文件的路径，请稍后再试。", $chatid);
                            return;
                        }
                    
                        $localFilePath = $this->downloadTelegramFile($filePath);
                        if ($localFilePath == "error") {
                            $this->xiaoxi("下载贴纸文件失败，请稍后再试！", $chatid);
                            return;
                        }
                    
                        $description = "";
                        if (!empty($message_kefu['caption'])) {
                            $description = $message_kefu['caption'];
                        }
                        if (isset($message_kefu['reply_to_message'])) {
                            $description = "> 引用的消息内容: \n[点击查看原消息]($quoted_message_link)\n\n" . $description;
                        }
                        $fileUrl = $this->uploadFileToLiveChatRoom($localFilePath, $room_id, $token,$agentId,$description, $chatid);
                        $rc_xiaoxi_id = $fileUrl['message']['_id'];
                       
                       if (!$fileUrl['success']) {
                           $this->xiaoxi("上传贴纸文件失败，请稍后再试！", $chatid);
                        } 
                }elseif (isset($message_kefu['document'])) {
                        // 处理GIF动画消息
                        $animation = $message_kefu['document'];
                        $fileId = $animation['file_id']; // 直接获取 file_id
                    
                        $filePath = $this->getTelegramFilePath($fileId);
                    
                        // 如果 filePath 为空或者下载失败，处理错误
                        if (!$filePath) {
                            $this->xiaoxi("无法获取贴纸文件的路径，请稍后再试。", $chatid);
                            return;
                        }
                    
                        $localFilePath = $this->downloadTelegramFile($filePath);
                        if ($localFilePath == "error") {
                            $this->xiaoxi("下载贴纸文件失败，请稍后再试！", $chatid);
                            return;
                        }
                    
                        $description = "";
                        if (!empty($message_kefu['caption'])) {
                            $description = $message_kefu['caption'];
                        }
                        if (isset($message_kefu['reply_to_message'])) {
                             $description = "> 引用的消息内容: \n[点击查看原消息]($quoted_message_link)\n\n" . $description;
                        }
                        $fileUrl = $this->uploadFileToLiveChatRoom($localFilePath, $room_id, $token,$agentId,$description, $chatid);
                        
                        $rc_xiaoxi_id = $fileUrl['message']['_id'];
                        if (!$fileUrl['success']) {
                            // $fileUrl = $this->chat_url . $fileUrl['file']['path'];
                            // $this->sendImageMessage($room_id, $token2, "![document]($fileUrl)"); 
                            // $this->xiaoxi("上传了一个文件给客服！", $chatid);
                             $this->xiaoxi("上传文件失败，请稍后再试！", $chatid);
                        } else {
                           
                        }
                }elseif (isset($message_kefu['video'])) {
                        // 处理GIF动画消息
                        $animation = $message_kefu['video'];
                        $fileId = $animation['file_id']; // 直接获取 file_id
                    
                        $filePath = $this->getTelegramFilePath($fileId);
                    
                        // 如果 filePath 为空或者下载失败，处理错误
                        if (!$filePath) {
                            $this->xiaoxi("无法视频文件的路径，请稍后再试。", $chatid);
                            return;
                        }
                    
                        $localFilePath = $this->downloadTelegramFile($filePath);
                        if ($localFilePath == "error") {
                            $this->xiaoxi("下载视频文件失败，请稍后再试！", $chatid);
                            return;
                        }
                    
                        $description = "";
                        if (!empty($message_kefu['caption'])) {
                            $description = $message_kefu['caption'];
                        }
                        if (isset($message_kefu['reply_to_message'])) {
                             $description = "> 引用的消息内容: \n[点击查看原消息]($quoted_message_link)\n\n" . $description;
                        }
                        $fileUrl = $this->uploadFileToLiveChatRoom($localFilePath, $room_id, $token,$agentId,$description, $chatid);
                        $rc_xiaoxi_id = $fileUrl['message']['_id'];
                        if (!$fileUrl['success']) {
                            // $fileUrl = $this->chat_url . $fileUrl['file']['path'];
                            // $this->sendImageMessage($room_id, $token2, "![document]($fileUrl)"); 
                            // $this->xiaoxi("上传了一个文件给客服！", $chatid);
                             $this->xiaoxi("上传视频失败，请稍后再试！", $chatid);
                        } 
                 }
                 
                 //存储他们的对应关系：
                $set_sql = "INSERT INTO pay_tgrcinfo (chatid,tg_id, rc_id, createtime) 
                        VALUES (:chatid, :tg_id, :rc_id, :createtime)";
                $this->pdo->prepare($set_sql)->execute([
                    ':chatid' => $chatid,
                    ':tg_id' => $tg_xiaoxi_id,
                    ':rc_id' => $rc_xiaoxi_id,
                    ':createtime' => time(),
                ]);
                 
            }else{
                //进入这里的 肯定是群不存在的：
               $this->message($message, $chatid, $data, $userid);
            }

        }

    }
    public function cancelPin($tg_message_id, $chatid)
    {
        // 第一步：根据Telegram消息ID获取对应的Rocket.Chat消息ID
        $query = "SELECT * FROM pay_tgrcinfo WHERE tg_id = :tg_id AND chatid = :chatid";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([':tg_id' => $tg_message_id, ':chatid' => $chatid]);
        $result = $stmt->fetchAll();
    
        if (!$result) {
            $this->xiaoxinoend("找不到对应的Rocket.Chat消息，无法取消置顶。", $chatid);
            return;
        }
    
        $rc_message_id = $result[0]['rc_id'];
    
        // 第二步：在Rocket.Chat中取消置顶
        $url = $this->chat_url . '/api/v1/chat.unpinMessage';
        
        $kefu_data = $this->guanliyuan($chatid);
        $authToken = $kefu_data['adminToken'];
        $adminUserId = $kefu_data['adminUserId'];
    
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode([
                'messageId' => $rc_message_id, 
            ]),
            CURLOPT_HTTPHEADER => [
                'X-Auth-Token: ' . $authToken,
                'X-User-Id: ' . $adminUserId,
                'Content-Type: application/json'
            ]
        ]);
    
        $response = curl_exec($curl);
        curl_close($curl);
    
        $response_data = json_decode($response, true);
    
        if ($response_data['success']) {
            // 第三步：更新数据库中该消息的状态为未置顶
            $this->pdo->exec("UPDATE pay_tgrcinfo SET status='0' WHERE tg_id='" . $tg_message_id . "' AND chatid='" . $chatid . "'");
            $this->xiaoxinoend("取消置顶成功！", $chatid);
        } else {
            $this->xiaoxinoend("取消置顶失败，请稍后再试。", $chatid); 
        }
    }
    public function message($message, $chatid, $data, $tg_userid)
    {
        
        
            //先去选择对应的客服部门： 
            $bottoken = $this->token;
            $http = new Http();
            $info = $http->sendPostRequest($this->rocket_url."/api/Index/kefu", ['bottoken' => $bottoken]);
            $info_arr = json_decode($info, true);
                
            if ($info_arr['code'] == "0") {
                $this->xiaoxi($info_arr['msg'], $chatid);
            }
            $kefu_name = $info_arr['data'];
                
            // 检查是否是新创建的群组 --- 检查是否是已有群组中拉入机器人
            if ((isset($data['message']['group_chat_created']) && $data['message']['group_chat_created']) || (isset($data['message']['new_chat_participant'])) || ($data['message']['chat']['type'] =="group")) {
                
                
                 //$this->xiaoxinoend("请将机器人设置为管理员!方可为群服务！",$chatid);
                
                  $typeli = 1;
                  $message = "注意，你被管理员拉入了群聊，注意群消息！";
                //这里就直接创建群就好了：
                 $usercreate = "";
                 $roomId = $this->usercreatequnliao($chatid,$usercreate,$kefu_name,$tg_userid,$typeli);
                 
                 //判断这个客服在不在对应的群里：
                 $info2 = $http->sendPostRequest($this->rocket_url."/api/Index/kefuuser", ['username' => $kefu_name]);
                 $info_arr2 = json_decode($info2, true);
                 if ($info_arr2['code'] == "0") {
                    $this->xiaoxi($info_arr2['msg'], $chatid);
                 }
               
                 $kefu_password = $info_arr2['data']['password'];
                 $kefu_userid = $info_arr2['data']['userid'];
                 $kefu_token = $info_arr2['data']['token'];
                 //先判断当前是不是有对应的userid跟token
                 if(!empty($kefu_token)){
                     //拉人进群：
                      $add_result = $this->addUserToGroup($roomId, $kefu_userid,$tg_userid,$chatid,$kefu_token);
                 }else{
                     //登录账号：
                     // $data = array('userId'=>$UserId,'authToken'=>$authToken);
                      $login_info = $this->userlogin($kefu_name,$kefu_password,$chatid);
                      $rc_userId = $login_info['userId'];
                      $rc_usertoken = $login_info['authToken'];
                      if(!empty($rc_usertoken)){
                          //这里要把那个数据存在rc的后台去：
                           $info2 = $http->sendPostRequest($this->rocket_url."/api/Index/updatekefuuser", ['username' => $kefu_name,'rc_user_id'=>$rc_userId,'rc_user_token'=>$rc_usertoken]);
                      }
                      //拉人进群：
                      $add_result = $this->addUserToGroup($roomId, $rc_userId,$tg_userid,$chatid,$rc_usertoken);
                      
                 }
                 
             
                
               
                
                
                
            }else{
                $typeli = 0;
                $username = $data['message']['from']['username'];//用户名称

                $uid = 0;
         
                //这里查询需不需要创建用户：
                //array($updatetime,$name,$rc_userid,$usertoken,$tg_userid)
                $usercreate = $this->usercreate($tg_userid,$username,$chatid);
                 
                //第二步：创建群聊，查询是不是存在群聊：
                $roomId = $this->usercreatequnliao($chatid,$usercreate,$kefu_name,$tg_userid,$typeli);
                 
                //游客主动先发消息：我需要客服服务 ，  我是客服：xx ,请简要说出你的需求！
               
                $visitorToken=$usercreate[3]; 
                $agent_id = $usercreate[2];
                $message = "你好，我需要客服帮助！";
            }
   
            $status = "0";
            $channel = $this->token;
            $createtime = time();
            if($typeli ==0){
                $depart_info = 'yiduiyi';
            }else{
                $depart_info = 'duoduiduo';
            }
            
            $set_sql = "insert into pay_userchat (typelist,depart_info,channel,status,visitorToken,room_id,agent_id,user_id,createtime,chat_id,kefu_name) values ('1','" . $depart_info . "','" . $channel . "','" . $status . "','" . $visitorToken . "', '" . $roomId . "','" . $agent_id . "','" . $uid . "','" . $createtime . "','" . $chatid . "','" . $kefu_name . "')";
            $this->pdo->exec($set_sql);
             if($typeli ==0){
                $send_result = $this->sendMessageToGroup($visitorToken,$agent_id,$roomId,$message,$chatid);
                if(!$send_result){
                    //记录有正在通话的记录
                    $this->xiaoxi("联系客服异常".$send_result,$chatid);
                } 
             }else{
                 if (isset($message_kefu['text'])) {
                     //如果有消息的情况：
                     
                 }
             }
            
            if($typeli ==0){
                $this->xiaoxi("客服:" . $kefu_name . ",为你开启服务，请简要说出你的需求", $chatid);
            }else{
                $this->xiaoxi("客服:" . $kefu_name . ",加入群聊！", $chatid);
            }

    }
    public function getMessageContent($messageId)
    {
        // API 端点
        $getMessageUrl = $this->chat_url."/api/v1/chat.getMessage?msgId={$messageId}";
          // 管理员的 Auth Token 和 User ID
        $kefu_data = $this->guanliyuan($chatid);

        $authToken = $kefu_data['adminToken'];
        $adminUserId =$kefu_data['adminUserId'];
        // 发起 GET 请求
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $getMessageUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'X-Auth-Token: ' .$authToken, // Rocket.Chat 认证 Token
                'X-User-Id: ' . $adminUserId,    // Rocket.Chat 用户 ID
                'Content-Type: application/json'
            ]
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        // 将响应内容解析为 JSON
        $data = json_decode($response, true);

        // 检查是否成功获取消息
        if ($data['success']) {
            return $data['message']['msg']; // 返回消息内容
        } else {
            return "获取消息内容失败: " . $data['error'];
        }
    }
    private function getRocketMessageId($tg_yinyong_message_id, $chatid)
    {
        $sql = "SELECT * FROM pay_tgrcinfo WHERE tg_id = :tg_id AND chatid = :chatid";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':tg_id' => $tg_yinyong_message_id, ':chatid' => $chatid]);
        $result = $stmt->fetchAll();
        return $result ? $result[0]['rc_id'] : null;
    }
    public function zhidingxiaoxi($tg_message_id,$chatid){
        //找到对应关系：
        $chaxun_sql = "select * FROM pay_tgrcinfo where tg_id ='".$tg_message_id."' and chatid='".$chatid."'";
        $cha_query = $this->pdo->query($chaxun_sql);
        $cha_info = $cha_query->fetchAll(); 
        if(!$cha_info){
            $this->xiaoxinoend("在客服系统中没有找到这个消息，无法进行置顶到客服系统！",$chatid);
        }
        //对应的消息置顶：
        $rc_xiaoxi_id = $cha_info[0]['rc_id'];
        //置顶操作：
        $url = $this->chat_url . '/api/v1/chat.pinMessage';
         // 管理员的 Auth Token 和 User ID
        $kefu_data = $this->guanliyuan($chatid);

        $authToken = $kefu_data['adminToken'];
        $adminUserId =$kefu_data['adminUserId'];
        
        $curl = curl_init();
        
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode([
                'messageId' => $rc_xiaoxi_id, 
            ]),
            CURLOPT_HTTPHEADER => [
                'X-Auth-Token: ' . $authToken, // 管理员的认证 Token
                'X-User-Id: ' . $adminUserId, // 管理员的 User ID
                'Content-Type: application/json'
            ]
        ]);
        
        $response = curl_exec($curl);
        curl_close($curl);
      
        $response = json_decode($response,true);
        return $response;
        
    }
    //获取群的信息：
    public function groupinfo($groupID,$chatid){
        //https://ccc.zmchat.xyz/api/v1/groups.info?roomName=nihaoya
        $group_url = $this->chat_url . "/api/v1/groups.info?roomId=".$groupID;
        $kefu_data = $this->guanliyuan($chatid);

        $curl = curl_init();
        
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => false,
          
            CURLOPT_HTTPHEADER => [
                'X-Auth-Token: ' . $authToken, // 管理员的认证 Token
                'X-User-Id: ' . $adminUserId, // 管理员的 User ID
                'Content-Type: application/json'
            ]
        ]);
        
        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }
    /**
     * 将用户添加到指定的群组
     */
    public function addUserToGroup($roomId, $rc_userId,$tg_from_id,$chatid,$rc_usertoken)
    {
        

        // 调用API将用户添加到群组
       // $url = $this->chat_url . 'api/v1/groups.invite';
       $url = $this->chat_url . '/api/v1/channels.invite';
         // 管理员的 Auth Token 和 User ID
        $kefu_data = $this->guanliyuan($chatid);
             

        $authToken = $kefu_data['adminToken'];
        $adminUserId =$kefu_data['adminUserId'];
        
        $curl = curl_init();
        
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode([
                'roomId' => $roomId, 
                'userId' => $rc_userId    
            ]),
            CURLOPT_HTTPHEADER => [
                'X-Auth-Token: ' . $authToken, // 管理员的认证 Token
                'X-User-Id: ' . $adminUserId, // 管理员的 User ID
                'Content-Type: application/json'
            ]
        ]);
        
        $response = curl_exec($curl);
        curl_close($curl);
        $response = json_decode($response,true);
    
        if ($response['success']) {
            //把用户存入对应的用户组里面去：
            $createtime = time();
            $set_sql = "insert into pay_rcroomdetail (room_id,tg_from_id,rc_userId,createtime,visitorToken) values ('" . $roomId . "','" . $tg_from_id . "','" . $rc_userId . "','".$createtime."','".$rc_usertoken."')";
            $this->pdo->exec($set_sql);
 
            return true;
        } else {
            //$this->xiaoxi("添加用户到群失败：" . $response['error'],$chatid);
        }
    }
    //发送消息：
    public function sendMessageToGroup($senduserToken,$senduserId,$roomId,$message,$chatid) {

        
        // API URL for posting a message
        $postMessageUrl = $this->chat_url . '/api/v1/chat.postMessage';
        
        // 对应发送人的 Auth Token 和 User ID
       // $authToken = "mGdVT6XWMggPECwwtzSrGelq-Tu3jUqCWHoA76vRwWK";
        //$adminUserId = "rjgcf3CnC3HZ3RMFy";
        
        // 群组的 ID
        //$roomId = $this->request->post('roomId');
        //$roomId = "670a443a43ae56d4d6d15ac3"; // 刚刚创建的群组ID
        
        // 要发送的消息内容
        //$message = "你好,我需要客服服务！";
        
        // 发起 POST 请求，向群组发送消息
        $curl = curl_init();
        
        curl_setopt_array($curl, [
            CURLOPT_URL => $postMessageUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode([
                'roomId' => $roomId, // 群组ID
                'text' => $message    // 要发送的消息内容
            ]),
            CURLOPT_HTTPHEADER => [
                'X-Auth-Token: ' . $senduserToken, // 管理员的认证 Token
                'X-User-Id: ' . $senduserId, // 管理员的 User ID
                'Content-Type: application/json'
            ]
        ]);
        
        $response = curl_exec($curl);
        curl_close($curl);
        
        $data = json_decode($response, true);
       // $this->xiaoxinoend($response,$chatid);
        if (isset($data['success']) && $data['success'] == true) {
            //返回发送消息成功的ID
           return $data['message']['_id'];
        } else {
            //这里如果发生了异常，基本就是群被删除了，这个时候就删除后台关于这个的信息：
            // $delete_sql = "DELETE FROM pay_userchat WHERE visitorToken='" . $senduserToken . "' and agent_id='".$senduserId."' and room_id='".$roomId."'";
            // $this->pdo->exec($delete_sql); 
            // $delete_sql2 = "DELETE FROM pay_rcroom WHERE chatid='" . $chatid . "' and roomid='".$roomId."'";
            // $this->pdo->exec($delete_sql2); 
            
           $this->xiaoxi("之前的聊天群出现异常，请重新发去客服需求".$response,$chatid);
           
        }
    }
    //游客主动与对应的客服进行聊天---建立群：
    //$usercreate ==> array($updatetime,$name,$rc_userid,$usertoken,$tg_userid)
    public function usercreatequnliao($chatid,$usercreate,$kefu_name,$tg_userid,$type=0){
 

        // API URL for creating a group
        $createGroupUrl = $this->chat_url . '/api/v1/groups.create';
        
        // 管理员的 Auth Token 和 User ID
        $kefu_data = $this->guanliyuan($chatid);
             

        $authToken = $kefu_data['adminToken'];
        $adminUserId = $kefu_data['adminUserId'];
        /*$authToken = "mGdVT6XWMggPECwwtzSrGelq-Tu3jUqCWHoA76vRwWK";
        $adminUserId = "rjgcf3CnC3HZ3RMFy";*/
        // 定义群组名称和群组成员
        
        $qunming = date("Y/m/d H:i:s");
        
        $youkename = "";
        if($type=="1"){
            //群：

            $name = $this->generateRandomEnglishString($chatid);
            
            $groupName ="新群组"."-".$qunming."-".$name."--public"; // 群组名称
            $members = [$kefu_name]; // 群组成员，客服1和客服2的用户名

        }else{
            //私人一对一：
            $youkename = $usercreate[1];
            $rc_userId =  $usercreate[2];
            $groupName = "新群组".'-'.$qunming."-".$youkename."--private"; // 群组名称
            $members = [$kefu_name, $youkename]; // 群组成员，客服1和客服2的用户名

        }
        
        //查询是不是存在：
        $room_sql = "select * FROM pay_rcroom where chatid ='".$chatid."'";
        $room_query = $this->pdo->query($room_sql); 
        $roominfo = $room_query->fetchAll();
        
        if($roominfo){
             return $roominfo[0]['roomid'];
        }


        // 发起 POST 请求，创建群组
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => $createGroupUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode([
                'name' => $groupName, // 群组名称
                'members' => $members // 群组成员
            ]),
            //CURLOPT_POSTFIELDS => $ssss,
            CURLOPT_HTTPHEADER => [
                'X-Auth-Token: ' . $authToken, // 管理员的认证 Token
                'X-User-Id: ' . $adminUserId, // 管理员的 User ID
                'Content-Type: application/json'
            ]
        ]);
    
        $response = curl_exec($curl);
        curl_close($curl);
        $createtime = time();
        $data = json_decode($response, true);
        if (isset($data['success']) && $data['success'] == true) {
            $roomid = $data['group']['_id'];

            // $groupinfo = $this->groupinfo($roomid,$chatid);
            // $this->xiaoxi($groupinfo, $chatid);

            
            
            
            $set_sql = "INSERT INTO pay_rcroom (channel,roomid, roomname, kefuname, youkename, createtime, chatid) 
                        VALUES (:channel, :roomid, :groupname, :kefuname, :youkename, :createtime, :chatid)";
            $this->pdo->prepare($set_sql)->execute([
                ':roomid' => $roomid,
                ':groupname' => $groupName,
                ':kefuname' => $kefu_name,
                ':youkename' => $youkename,
                ':createtime' => $createtime,
                ':chatid' => $chatid,
                ':channel'=> $this->token
            ]);
    
            // 调用 groups.setType API 将群组转换为公开群组
            $setGroupPublic =  $this->setGroupPublic($roomid, $authToken, $adminUserId);
            //如果是一对一 也需要单独存储起来：
            if($type=="0"){
                $visitorToken = $usercreate[3];
                $set_sql = "insert into pay_rcroomdetail (room_id,tg_from_id,rc_userId,createtime,visitorToken) values ('" . $roomid . "','" . $tg_userid . "','" . $rc_userId . "','".$createtime."','".$visitorToken."')";
                $this->pdo->exec($set_sql);
            }

            return $roomid;
        } else {
            //这里可能是已经存在这个群了：
            if($data['errorType'] =="error-duplicate-channel-name"){
                //直接获取这个频道信息即可：
                $getChannelInfo = $this->getChannelInfo($authToken, $adminUserId, $groupName);
                if($getChannelInfo['success']){
                    $roomid = $getChannelInfo['channel']['_id'];
                }
        
                $set_sql = "INSERT INTO pay_rcroom (channel,roomid, roomname, kefuname, youkename, createtime, chatid) 
                        VALUES (:channel, :roomid, :groupname, :kefuname, :youkename, :createtime, :chatid)";
                $this->pdo->prepare($set_sql)->execute([
                    ':roomid' => $roomid,
                    ':groupname' => $groupName,
                    ':kefuname' => $kefu_name,
                    ':youkename' => $youkename,
                    ':createtime' => $createtime,
                    ':chatid' => $chatid,
                    ':channel'=>$this->token
                ]);
                return $roomid;
            }else{
                $this->xiaoxi("创建群组失败,请联系管理员" . $response, $chatid);
            }
            
        }
    }
    public function setGroupPublic($roomId, $authToken, $adminUserId) {
        // API URL for setting group type
        $setTypeUrl = $this->chat_url . '/api/v1/groups.setType';
    
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $setTypeUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode([
                'roomId' => $roomId,
                'type' => 'c' // 'c' 表示公开群组
            ]),
            CURLOPT_HTTPHEADER => [
                'X-Auth-Token: ' . $authToken,
                'X-User-Id: ' . $adminUserId,
                'Content-Type: application/json'
            ]
        ]);
    
        $response = curl_exec($curl);
        curl_close($curl);
    
        $data = json_decode($response, true);
    
        if (!isset($data['success']) || $data['success'] != true) {
            // 如果修改失败，记录日志或通知管理员
            $this->xiaoxi("将群组设置为公开失败: " . $response, $roomId);
        }
        return $response;
    }
    //检查token是不是过期了
    function isTokenValid($authToken, $userId) {
        $meUrl = $this->chat_url . '/api/v1/me';
    
        // 发起请求检查 token 是否有效
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $meUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'X-Auth-Token: ' . $authToken, // 需要验证的 authToken
                'X-User-Id: ' . $userId, // 对应的 User ID
                'Content-Type: application/json'
            ]
        ]);
    
        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
    
        if ($httpCode === 200) {
            $data = json_decode($response, true);
            if (isset($data['success']) && $data['success'] == true) {
                return true; // Token 有效
            }
        }
    
        return false; // Token 无效或过期
    }
    function getChannelInfo($authToken, $userId, $channelName) {
        // Rocket.Chat API URL
        $apiUrl = $this->chat_url.'/api/v1/channels.info?roomName=' . urlencode($channelName);
        
        // Initialize cURL session
        $ch = curl_init();
    
        // Set cURL options
        curl_setopt($ch, CURLOPT_URL, $apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'X-Auth-Token: ' . $authToken,
            'X-User-Id: ' . $userId,
            'Content-Type: application/json'
        ]);
    
        // Execute cURL request
        $response = curl_exec($ch);
    
        // Check for errors
        if (curl_errno($ch)) {
            return 'cURL Error: ' . curl_error($ch);
        }
    
        // Close cURL session
        curl_close($ch);
    
        // Decode the JSON response
        $data = json_decode($response, true);
        return $data;
       
    }
    //游客登录
    public function userlogin($username,$password,$chatid){
        $loginUrl = $this->chat_url . '/api/v1/login';
   
        $loginData = [
            'user' => $username, // 用户名
            'password' => $password // 密码
        ];
        
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
        
        if (isset($data['data']['authToken'])) {
            $authToken = $data['data']['authToken'];
            $UserId = $data['data']['userId'];
            $data = array('userId'=>$UserId,'authToken'=>$authToken);
            return $data;

        } else {
            $this->xiaoxi("登录失败",$chatid);
      
        }

    }
    //将chatid转化成：对应的字符串
    function generateRandomEnglishString($inputString) {
        // 使用哈希算法将输入字符串转换为种子值（crc32 返回一个整数）
        $seed = crc32($inputString);
        mt_srand($seed); // 使用种子初始化随机数生成器
    
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';
    
        // 限制随机字符串的最大长度为 8
        $maxLength = 8;
        $length = min(strlen($inputString), $maxLength);
    
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[mt_rand(0, strlen($characters) - 1)];
        }
    
        return $randomString;
    }
    //创建新用户：
    public function usercreate($tg_userid,$username,$chatid)
    { 
        
        $createtime =time();
        $updatetime = $createtime+(90*24*60*60);
        
        //查询用户是不是存在了：
        $kefu_sql = "select * FROM pay_userchatrc where  tg_from_id='".$tg_userid."' ";
        $kefu_query2 = $this->pdo->query($kefu_sql);
        $chatinfo = $kefu_query2->fetchAll();
        if($chatinfo){
            return array($chatinfo[0]['updatetime'],$chatinfo[0]['username'],$chatinfo[0]['userid'],$chatinfo[0]['usertoken'],$tg_userid);
        }
        
        
        //先查询是不是客服账号的tg:
        $gongsi_kefu_sql = "select * FROM pay_rckefu where  tg_from_id='".$tg_userid."' and typelist ='0' ";
        $gongsi_kefu_query2 = $this->pdo->query($gongsi_kefu_sql);
        $gongsi_kefu_info = $gongsi_kefu_query2->fetchAll();
        if($gongsi_kefu_info){
            $username = $gongsi_kefu_info['username'];
            $password = $gongsi_kefu_info['password'];
            $kefuemail = "kefudegmail@gmail.com";
            //这里需要再去执行一次登录：
            $userlogin = $this->userlogin($username,$password,$chatid);
            //{"code":1,"msg":"返回成功","time":"1728896943","data":{"userId":"xKJ7WZXDhwi2Ntv97","authToken":"ewscjf2Xy_krpcqEp9SUbfSr7vINO8a3DEAcoM_4eGV"}}
            $rc_userid =$userlogin['userId'];
            $usertoken = $userlogin['authToken'];
           
            $set_sql = "insert into pay_userchatrc (tg_from_id,username,password,gmail,chatid,createtime,userid,usertoken,updatetime) values ('" . $tg_userid . "','" . $username . "','" . $password . "','" . $kefuemail . "','" . $chatid . "', '" . $createtime . "','" . $rc_userid . "','" . $usertoken . "','" .$updatetime . "')";
            $this->pdo->exec($set_sql);
            
           return array($updatetime,$name,$rc_userid,$usertoken,$tg_userid);
        }
        
        $nickname = $this->nickname(1);
        $name = $this->generateRandomEnglishString($tg_userid);
        // API URL for creating a new user
        $createUserUrl = $this->chat_url . '/api/v1/users.create';
        $email =  $name.'@gmail.com';
        // 用户信息
        $userData = [
            'name' => $name,
            'email' => $email,
            'username' => $name,
            'password' => $name,
            'roles' => ['user'],
            'nickname' => $nickname // 添加昵称字段
        ];
        
        // 使用 curl 发起 POST 请求
        $curl = curl_init();
        
        
        //这里需要管理员账号跟密码：
        $kefu_data = $this->guanliyuan($chatid); 
        $authToken = $kefu_data['adminToken'];
        $adminUserId = $kefu_data['adminUserId'];
        
       /*$authToken = "mGdVT6XWMggPECwwtzSrGelq-Tu3jUqCWHoA76vRwWK";
        $adminUserId = "rjgcf3CnC3HZ3RMFy";*/
        curl_setopt_array($curl, [
            CURLOPT_URL => $createUserUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($userData),
            CURLOPT_HTTPHEADER => [
                'X-Auth-Token: ' . $authToken, // 管理员的 Auth Token
                'X-User-Id: ' . $adminUserId,   // 管理员的 User ID
                'Content-Type: application/json'
            ]
        ]);
        
        $response = curl_exec($curl);
        curl_close($curl);
        
        $data = json_decode($response, true);
        
        if (isset($data['success']) && $data['success'] == true) {
            //将用户信息存储起来：
       
            //这里需要再去执行一次登录：
            $userlogin = $this->userlogin($name,$name,$chatid);
            //{"code":1,"msg":"返回成功","time":"1728896943","data":{"userId":"xKJ7WZXDhwi2Ntv97","authToken":"ewscjf2Xy_krpcqEp9SUbfSr7vINO8a3DEAcoM_4eGV"}}
            $rc_userid =$userlogin['userId'];
            $usertoken = $userlogin['authToken'];
           
            $set_sql = "insert into pay_userchatrc (tg_from_id,username,password,gmail,chatid,createtime,userid,usertoken,updatetime) values ('" . $tg_userid . "','" . $name . "','" . $name . "','" . $email . "','" . $chatid . "', '" . $createtime . "','" . $rc_userid . "','" . $usertoken . "','" .$updatetime . "')";
            $this->pdo->exec($set_sql);
            
           return array($updatetime,$name,$rc_userid,$usertoken,$tg_userid);

           // $this->xiaoxinoend("创建用户成功",$chatid);
        } else {
            //如果存在：
            $data_error = $name." is already in use :( [error-field-unavailable]";
            if($data['details']['method'] =="insertOrUpdateUser"){
                $userlogin = $this->userlogin($name,$name,$chatid);
                $rc_userid =$userlogin['userId'];
                $usertoken = $userlogin['authToken'];
                
                $set_sql = "insert into pay_userchatrc (tg_from_id,username,password,gmail,chatid,createtime,userid,usertoken,updatetime) values ('" . $tg_userid . "','" . $name . "','" . $name . "','" . $email . "','" . $chatid . "', '" . $createtime . "','" . $rc_userid . "','" . $usertoken . "','" .$updatetime . "')";
                $this->pdo->exec($set_sql);
                return array($updatetime,$name,$rc_userid,$usertoken,$tg_userid);
            }else{
                //$this->xiaoxi("创建用户失败".$response,$chatid);
            }

        }

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
        $loginUrl = $this->chat_url."/api/v1/login";
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
    public function shujuku($sql)
    {
        $order_query = $this->pdo->query($sql);
        $info = $order_query->fetchAll();
        return $info;
    }
    public function xiaoxi($msg, $chatid, $type = "0", $answer = "")
    {
        $parameter = array(
            'chat_id' => $chatid,
            'parse_mode' => 'HTML',
            'text' => $msg
        );
        $this->http_post_data('sendMessage', json_encode($parameter));
        if ($type == "1") {
            $parameter = array(
                'callback_query_id' => $answer,
                'text' => "",
            );
            $this->http_post_data('answerCallbackQuery', json_encode($parameter));
        }

        exit();
    }
    public function xiaoxinoend($msg, $chatid)
    {
        $parameter = array(
            'chat_id' => $chatid,
            'parse_mode' => 'HTML',
            'text' => $msg
        );
        $this->http_post_data('sendMessage', json_encode($parameter));
    }
    public function kefuya($chatid, $department)
    {
        $sql_info = "select * from pay_botsettle where chatid ='" . $chatid . "'";
        $order_query2 = $this->pdo->query($sql_info);
        $chatinfo = $order_query2->fetchAll();
        $uid = $chatinfo['0']['merchant'];

        $visitorToken = $this->generateVisitorToken($chatid,$department);
        // Rocket.Chat 服务器地址
        $serverUrl = $this->chat_url;

        // 创建访客
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $serverUrl . '/api/v1/livechat/visitor');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
            'visitor' => [
                'token' => $visitorToken,
                'name' => "游客来电--用户TG" . $this->jiami($chatid),
                
                'email' => 'visitor@example.com',
                "department" => $department,  //天使技术部A，上游客服部B
            ]
        ]));

        $response = curl_exec($ch);
        if (!$response) {
            $this->xiaoxi("客服人工座席忙,请稍后再请求123！", $chatid);
        }

        $visitorData = json_decode($response, true);

        //第二步：通过token拿到数据先：
        //https://ccc.zmchat.xyz/api/v1/livechat/room?token=bc2a23307a699c2909a54c5948c2b05c036c92dc200568646cb5b10cabb4a0d5
        $url2 = $serverUrl . "/api/v1/livechat/room?token=" . $visitorToken;
        $headers2 = [];
        $response2 = $this->httpGet($url2, $headers2);
        if (!$response2) {
            $this->xiaoxi("客服人工座席忙,请稍后再请求456！", $chatid);
        }
        if(!$response2['success']){
                $this->xiaoxi("很抱歉，当前暂无在线的人工客服!".$response2['error'], $chatid);
        }
       
        $room_id = $response2['room']['_id'];

       

        //再去请求获取用户信息：
        //https://ccc.zmchat.xyz/api/v1/livechat/agent.info/MPSvGLEJgvGzNgg7x/5a65a366b07dcabef66ce3b624b83dcd7c01cc4599881fffdff79eff8fc6f6a2
        // 使用示例
        $url3 = $serverUrl . '/api/v1/livechat/agent.info/' . $room_id . "/" . $visitorToken;
        $headers3 = [
            //'Authorization: Bearer your_token_here',
            //'Content-Type: application/json'
        ];

        $response3 = $this->httpGet($url3, $headers3);
       
        if (!$response3) { 
            $this->xiaoxi("客服人工座席忙,请稍后再请求789！", $chatid);
        }
        if($response2['invalid-agent']=="invalid-agent"){
             $this->xiaoxi("没有找到对应的客服", $chatid); 
        }
         
        $agent_id = $response3['agent']['_id'];
        $kefu_username = $response3['agent']['username'];

        //这里来一个记录，表示当前商户正在对话，进行中：
        $status = "0";
        $channel = $this->token;
        $createtime = time();
        $set_sql = "insert into pay_userchat (depart_info,channel,status,visitorToken,room_id,agent_id,user_id,createtime,chat_id,kefu_name) values ('" . $department . "','" . $channel . "','" . $status . "','" . $visitorToken . "', '" . $room_id . "','" . $agent_id . "','" . $uid . "','" . $createtime . "','" . $chatid . "','" . $kefu_username . "')";
        $this->pdo->exec($set_sql);
        $this->xiaoxi("客服:" . $kefu_username . ",为你开启服务，请简要说出你的需求", $chatid);
    }
    public function callback($data)
    {

        $text = $data['callback_query']['data'];
        $chat_id = $data['callback_query']['message']['chat']['id'];
        $from_id = $data['callback_query']['from']['id'];
        $userid = $from_id;
        $message_id = $data['callback_query']['message']['message_id'];
        $set_sqlq = "select * FROM pay_type where status='1'";
        $order_query_q = $this->pdo->query($set_sqlq);
        $user_type = $order_query_q->fetchAll();
        $new_type = array();
        $chatid = $chat_id;
        $username = $data['message']['from']['username'];//用户名称


       
        

        


        //关闭当前客服会话
        if (strpos($text, '关闭当前客服会话') !== false) {
            $channel = $this->token;
            $res = $this->pdo->exec("UPDATE pay_userchat SET status='1' WHERE channel ='".$channel."' and chat_id='" . $chatid . "'");
            $this->xiaoxi("关闭当前客服会话成功", $chatid);
        }
        
         $kefu_sql = "select * FROM pay_userchat where chat_id ='" . $chatid . "' and channel='".$this->token."' and status='0'";
        $order_query2 = $this->pdo->query($kefu_sql);
        $chatinfo = $order_query2->fetchAll();
        if ($chatinfo) {
                $inline_keyboard_arr9[0] = array('text' => "关闭当前会话 ", "callback_data" => "关闭当前客服会话");
                $keyboard = [
                    'inline_keyboard' => [
                        $inline_keyboard_arr9,

                    ]
                ];

                $parameter = array(
                    'chat_id' => $chatid,
                    'parse_mode' => 'HTML',
                    'text' => "你已经有一个正在通讯的会话！",
                    'reply_markup' => $keyboard,
                    'disable_web_page_preview' => true,
                );

                $this->http_post_data('sendMessage', json_encode($parameter));
                exit();

            }

        

        if (strpos($text, 'xunzhaokefu_') !== false) {
            $kefupei = explode("kefu_", $text);
            $this->kefuya($chatid, $kefupei[1]);
        }


        $parameter = array(
            'callback_query_id' => $data['callback_query']['id'],
            'text' => "",
        );
        $this->http_post_data('answerCallbackQuery', json_encode($parameter));


    }
    //post的array数据请求
    public function send_post($url, $post_data)
    {

        $postdata = http_build_query($post_data);
        $options = array(
            'http' => array(
                'method' => 'POST',
                'header' => 'Content-type:application/x-www-form-urlencoded',
                'content' => $postdata,
                'timeout' => 15 * 60 // 超时时间（单位:s）
            )
        );
        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);

        return $result;
    }
    //post的json数据请求
    public function http_post_data($action, $data_string)
    {
        //这里，
        /*$sql= "insert into wolive_tests (content) values ('".json_encode($data)."')";
        $this->pdo->exec($sql);*/

        $url = $this->link . "/" . $action . "?";
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_POST, 1);

        curl_setopt($ch, CURLOPT_URL, $url);

        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);

        curl_setopt($ch, CURLOPT_HTTPHEADER, array(

                'Content-Type: application/json; charset=utf-8',

                'Content-Length: ' . strlen($data_string))

        );

        ob_start();

        curl_exec($ch);

        $return_content = ob_get_contents();

        //echo $return_content."


        ob_end_clean();

        $return_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        // return array($return_code, $return_content);

        return $return_content;

    }
    function generateVisitorToken($chatid,$department)
    {
        
        $bottoken = $this->token;
        $info = Http::sendPostRequest($this->rocket_url."/api/Index/rctype", ['bottoken' => $bottoken]);
        $info_arr = json_decode($info, true);
        
        if($info_arr['code']=="1"){
            if($info_arr['data'] == "0" ){
                $kefu_sql = "select * FROM pay_userchat where chat_id ='" . $chatid . "' and channel='".$this->token."' and status='1' and depart_info='".$department."'";
                $kefu_query = $this->pdo->query($kefu_sql);
                $kefu_info = $kefu_query->fetchAll();
                if($kefu_info){
                    return $kefu_info[0]['visitorToken'];
                }
                return bin2hex(random_bytes(32)); // 生成64字符的十六进制字符串
            }
        }else{
            return bin2hex(random_bytes(32)); // 生成64字符的十六进制字符串

        }
        
        
    }
    /**
     * 发起一个GET请求
     *
     * @param string $url 请求的URL
     * @param array $headers 可选的HTTP头信息
     * @return mixed 响应数据或错误信息
     */
    function httpGet($url, $headers = [])
    {
        // 初始化 cURL
        $ch = curl_init();

        // 设置 cURL 选项
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        // 如果有HTTP头信息，则设置
        if (!empty($headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        // 执行请求并获取响应
        $response = curl_exec($ch);

        // 检查是否有错误发生
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
            return false;
        }

        // 关闭 cURL 会话
        curl_close($ch);

        // 返回响应
        return json_decode($response, true);
    }
    /**
     * 从 Telegram 获取文件路径
     */
    function getTelegramFilePath($fileId)
    {
        $url = "https://api.telegram.org/bot" . $this->token . "/getFile?file_id=" . $fileId;
        $response = file_get_contents($url);
        $fileData = json_decode($response, true);
        return "https://api.telegram.org/file/bot" . $this->token . "/" . $fileData['result']['file_path'];
    }

    /**
     * 生成随机字符串
     *
     * @param int $length 字符串长度
     * @return string 随机字符串
     */
    function generateRandomString($length = 10)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    /**
     * 下载 Telegram 文件到本地
     */

    function downloadTelegramFile($fileUrl)
    {
        $saveDir = __DIR__ . "/upload/shanghuchat";
        // 检查并创建保存目录
        if (!is_dir($saveDir)) {
            mkdir($saveDir, 0777, true);
        }

        // 生成随机文件名
        $fileExtension = pathinfo($fileUrl, PATHINFO_EXTENSION);
        $randomFileName = $this->generateRandomString(10) . '.' . $fileExtension;
        $localFilePath = $saveDir . '/' . $randomFileName;

        // 打开文件句柄
        $fileHandler = fopen($fileUrl, 'rb');
        if ($fileHandler === false) {
            return "error";
            return 'Failed to open URL: ' . $fileUrl;
        }

        // 打开本地文件句柄
        $localFileHandler = fopen($localFilePath, 'wb');
        if ($localFileHandler === false) {
            fclose($fileHandler);
            return "error";
            return 'Failed to create local file: ' . $localFilePath;
        }

        // 将远程文件内容写入本地文件
        while (!feof($fileHandler)) { 
            fwrite($localFileHandler, fread($fileHandler, 8192));
        }

        // 关闭文件句柄
        fclose($fileHandler);
        fclose($localFileHandler);

        // 检查文件是否存在
        if (file_exists($localFilePath)) {
            return $localFilePath;
        } else {
            return "error";
            return 'Failed to download file to local path: ' . $localFilePath;
        }
    }

    /**
     * 上传文件到 Rocket.Chat
     */
    function uploadImage($filePath, $visitorToken)
    {
        $url = $this->chat_url . '/api/v1/livechat/upload/' . $visitorToken;

        // 初始化 cURL
        $ch = curl_init();

        // 设置 cURL 选项
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: multipart/form-data'
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, [
            'file' => new CURLFile($filePath)
        ]);

        // 执行请求并获取响应
        $response = curl_exec($ch);

        // 检查是否有错误发生
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
            return false;
        }

        // 关闭 cURL 会话
        curl_close($ch);

        // 返回响应
        $uploadResponse = json_decode($response, true);
        $fileId = $uploadResponse['file']['_id'];
        return $fileId;

    }

    /**
     * 发送包含图片的消息
     */
    function sendImageMessage($roomId, $visitorToken, $text)
    {
        $url = $this->chat_url . '/api/v1/livechat/message';
        $postData = [
            'rid' => $roomId,
            'msg' => $text,
            'token' => $visitorToken
        ];
        $headers = [
            'Content-Type: application/json'
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }

        curl_close($ch);
        return $response;

    }

    /**
     * 发送消息到 Rocket.Chat 的 Live Chat
     */
    function sendMessageToRocketChat($text, $room_id, $token, $agentId, $kefu_username, $chatid)
    {
        $url = $this->chat_url . '/api/v1/livechat/message';
        $postData = [
            'rid' => $room_id,
            'msg' => $text,
            'token' => $token,
            'agent' => [
                'agentId' => $agentId,
                'username' => $kefu_username
            ]
        ];
        $headers = [
            'Content-Type: application/json'
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }

        curl_close($ch);
    }

    /**
     * 上传图片到 Rocket.Chat 的 Live Chat
     *
     * @param string $filePath 文件路径
     * @param string $visitorToken 访客 Token
     * @return mixed 文件 URL 或错误信息
     */
 
    
    function uploadFileToLiveChatRoom($filePath, $roomId, $authToken, $userId, $description = '', $chatid)
    {
        $url = $this->chat_url . '/api/v1/rooms.upload/' . $roomId;
    
        // 准备 POST 数据
        $postData = [
            'file' => new CURLFile($filePath),
            'description' => $description,
        ];
    
        // 设置请求头，包含身份认证信息
        $headers = [
            'X-Auth-Token: ' . $authToken,
            'X-User-Id: ' . $userId,
        ];
    
        // 初始化 cURL
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    
        // 执行请求
        $response = curl_exec($ch);
    
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
            return false;
        }
    
        // 关闭 cURL
        curl_close($ch);
    
        // 解析响应并返回
        return json_decode($response, true);
    }
    
    
    
    public function jiami($input) {
        $char_set = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    
        $binaryString = '';
        
        // 将每个字符转换为 8 位的二进制
        foreach (str_split($input) as $char) {
            $binaryString .= str_pad(decbin(ord($char)), 8, '0', STR_PAD_LEFT);
        }
    
        // 将二进制字符串按 6 位一组分割并映射到字符集
        $encoded = '';
        foreach (str_split($binaryString, 6) as $chunk) {
            // 如果分割后的块不足 6 位，补全
            $chunk = str_pad($chunk, 6, '0', STR_PAD_RIGHT);
            $encoded .= $char_set[bindec($chunk)];
        }
    
        return $encoded;
    }
    // 将编码后的字符串解码回原始字符串
    public function jiemi($input) {
        $char_set = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $binaryString = '';
    
        // 将每个字符转换回二进制
        foreach (str_split($input) as $char) {
            $binaryString .= str_pad(decbin(strpos($char_set, $char)), 6, '0', STR_PAD_LEFT);
        }
    
        // 将二进制按 8 位一组转回字符
        $text = '';
        foreach (str_split($binaryString, 8) as $chunk) {
            $text .= chr(bindec($chunk));
        }
    
        return $text;
    }


    public function curlpostjson($serverUrl,$data){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $serverUrl );
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    
        $response = curl_exec($ch);
        return $response;
    }
    function nickname($type = 1){
        /**
         * 随机昵称 形容词
         */
        $nicheng_tou=['迷你的','鲜艳的','飞快的','真实的','清新的','幸福的','可耐的','快乐的','冷静的','醉熏的','潇洒的','糊涂的','积极的','冷酷的','深情的','粗暴的',
            '温柔的','可爱的','愉快的','义气的','认真的','威武的','帅气的','传统的','潇洒的','漂亮的','自然的','专一的','听话的','昏睡的','狂野的','等待的','搞怪的',
            '幽默的','魁梧的','活泼的','开心的','高兴的','超帅的','留胡子的','坦率的','直率的','轻松的','痴情的','完美的','精明的','无聊的','有魅力的','丰富的','繁荣的',
            '饱满的','炙热的','暴躁的','碧蓝的','俊逸的','英勇的','健忘的','故意的','无心的','土豪的','朴实的','兴奋的','幸福的','淡定的','不安的','阔达的','孤独的',
            '独特的','疯狂的','时尚的','落后的','风趣的','忧伤的','大胆的','爱笑的','矮小的','健康的','合适的','玩命的','沉默的','斯文的','香蕉','苹果','鲤鱼','鳗鱼',
            '任性的','细心的','粗心的','大意的','甜甜的','酷酷的','健壮的','英俊的','霸气的','阳光的','默默的','大力的','孝顺的','忧虑的','着急的','紧张的','善良的',
            '凶狠的','害怕的','重要的','危机的','欢喜的','欣慰的','满意的','跳跃的','诚心的','称心的','如意的','怡然的','娇气的','无奈的','无语的','激动的','愤怒的',
            '美好的','感动的','激情的','激昂的','震动的','虚拟的','超级的','寒冷的','精明的','明理的','犹豫的','忧郁的','寂寞的','奋斗的','勤奋的','现代的','过时的',
            '稳重的','热情的','含蓄的','开放的','无辜的','多情的','纯真的','拉长的','热心的','从容的','体贴的','风中的','曾经的','追寻的','儒雅的','优雅的','开朗的',
            '外向的','内向的','清爽的','文艺的','长情的','平常的','单身的','伶俐的','高大的','懦弱的','柔弱的','爱笑的','乐观的','耍酷的','酷炫的','神勇的','年轻的',
            '唠叨的','瘦瘦的','无情的','包容的','顺心的','畅快的','舒适的','靓丽的','负责的','背后的','简单的','谦让的','彩色的','缥缈的','欢呼的','生动的','复杂的',
            '慈祥的','仁爱的','魔幻的','虚幻的','淡然的','受伤的','雪白的','高高的','糟糕的','顺利的','闪闪的','羞涩的','缓慢的','迅速的','优秀的','聪明的','含糊的',
            '俏皮的','淡淡的','坚强的','平淡的','欣喜的','能干的','灵巧的','友好的','机智的','机灵的','正直的','谨慎的','俭朴的','殷勤的','虚心的','辛勤的','自觉的',
            '无私的','无限的','踏实的','老实的','现实的','可靠的','务实的','拼搏的','个性的','粗犷的','活力的','成就的','勤劳的','单纯的','落寞的','朴素的','悲凉的',
            '忧心的','洁净的','清秀的','自由的','小巧的','单薄的','贪玩的','刻苦的','干净的','壮观的','和谐的','文静的','调皮的','害羞的','安详的','自信的','端庄的',
            '坚定的','美满的','舒心的','温暖的','专注的','勤恳的','美丽的','腼腆的','优美的','甜美的','甜蜜的','整齐的','动人的','典雅的','尊敬的','舒服的','妩媚的',
            '秀丽的','喜悦的','甜美的','彪壮的','强健的','大方的','俊秀的','聪慧的','迷人的','陶醉的','悦耳的','动听的','明亮的','结实的','魁梧的','标致的','清脆的',
            '敏感的','光亮的','大气的','老迟到的','知性的','冷傲的','呆萌的','野性的','隐形的','笑点低的','微笑的','笨笨的','难过的','沉静的','火星上的','失眠的',
            '安静的','纯情的','要减肥的','迷路的','烂漫的','哭泣的','贤惠的','苗条的','温婉的','发嗲的','会撒娇的','贪玩的','执着的','眯眯眼的','花痴的','想人陪的',
            '眼睛大的','高贵的','傲娇的','心灵美的','爱撒娇的','细腻的','天真的','怕黑的','感性的','飘逸的','怕孤独的','忐忑的','高挑的','傻傻的','冷艳的','爱听歌的',
            '还单身的','怕孤单的','懵懂的'];
        $nicheng_wei=['嚓茶','皮皮虾','皮卡丘','马里奥','小霸王','凉面','便当','毛豆','花生','可乐','灯泡','哈密瓜','野狼','背包','眼神','缘分','雪碧','人生','牛排',
            '蚂蚁','飞鸟','灰狼','斑马','汉堡','悟空','巨人','绿茶','自行车','保温杯','大碗','墨镜','魔镜','煎饼','月饼','月亮','星星','芝麻','啤酒','玫瑰',
            '大叔','小伙','哈密瓜，数据线','太阳','树叶','芹菜','黄蜂','蜜粉','蜜蜂','信封','西装','外套','裙子','大象','猫咪','母鸡','路灯','蓝天','白云',
            '星月','彩虹','微笑','摩托','板栗','高山','大地','大树','电灯胆','砖头','楼房','水池','鸡翅','蜻蜓','红牛','咖啡','机器猫','枕头','大船','诺言',
            '钢笔','刺猬','天空','飞机','大炮','冬天','洋葱','春天','夏天','秋天','冬日','航空','毛衣','豌豆','黑米','玉米','眼睛','老鼠','白羊','帅哥','美女',
            '季节','鲜花','服饰','裙子','白开水','秀发','大山','火车','汽车','歌曲','舞蹈','老师','导师','方盒','大米','麦片','水杯','水壶','手套','鞋子','自行车',
            '鼠标','手机','电脑','书本','奇迹','身影','香烟','夕阳','台灯','宝贝','未来','皮带','钥匙','心锁','故事','花瓣','滑板','画笔','画板','学姐','店员',
            '电源','饼干','宝马','过客','大白','时光','石头','钻石','河马','犀牛','西牛','绿草','抽屉','柜子','往事','寒风','路人','橘子','耳机','鸵鸟','朋友',
            '苗条','铅笔','钢笔','硬币','热狗','大侠','御姐','萝莉','毛巾','期待','盼望','白昼','黑夜','大门','黑裤','钢铁侠','哑铃','板凳','枫叶','荷花','乌龟',
            '仙人掌','衬衫','大神','草丛','早晨','心情','茉莉','流沙','蜗牛','战斗机','冥王星','猎豹','棒球','篮球','乐曲','电话','网络','世界','中心','鱼','鸡','狗',
            '老虎','鸭子','雨','羽毛','翅膀','外套','火','丝袜','书包','钢笔','冷风','八宝粥','烤鸡','大雁','音响','招牌','胡萝卜','冰棍','帽子','菠萝','蛋挞','香水',
            '泥猴桃','吐司','溪流','黄豆','樱桃','小鸽子','小蝴蝶','爆米花','花卷','小鸭子','小海豚','日记本','小熊猫','小懒猪','小懒虫','荔枝','镜子','曲奇','金针菇',
            '小松鼠','小虾米','酒窝','紫菜','金鱼','柚子','果汁','百褶裙','项链','帆布鞋','火龙果','奇异果','煎蛋','唇彩','小土豆','高跟鞋','戒指','雪糕','睫毛','铃铛',
            '手链','香氛','红酒','月光','酸奶','银耳汤','咖啡豆','小蜜蜂','小蚂蚁','蜡烛','棉花糖','向日葵','水蜜桃','小蝴蝶','小刺猬','小丸子','指甲油','康乃馨','糖豆',
            '薯片','口红','超短裙','乌冬面','冰淇淋','棒棒糖','长颈鹿','豆芽','发箍','发卡','发夹','发带','铃铛','小馒头','小笼包','小甜瓜','冬瓜','香菇','小兔子',
            '含羞草','短靴','睫毛膏','小蘑菇','跳跳糖','小白菜','草莓','柠檬','月饼','百合','纸鹤','小天鹅','云朵','芒果','面包','海燕','小猫咪','龙猫','唇膏','鞋垫',
            '羊','黑猫','白猫','万宝路','金毛','山水','音响','纸飞机','烧鹅'];
        /**
         * 百家姓
         */
        $arrXing=['赵','钱','孙','李','周','吴','郑','王','冯','陈','褚','卫','蒋','沈','韩','杨','朱','秦','尤','许','何','吕','施','张','孔','曹','严','华','金','魏','陶','姜','戚','谢','邹',
            '喻','柏','水','窦','章','云','苏','潘','葛','奚','范','彭','郎','鲁','韦','昌','马','苗','凤','花','方','任','袁','柳','鲍','史','唐','费','薛','雷','贺','倪','汤','滕','殷','罗',
            '毕','郝','安','常','傅','卞','齐','元','顾','孟','平','黄','穆','萧','尹','姚','邵','湛','汪','祁','毛','狄','米','伏','成','戴','谈','宋','茅','庞','熊','纪','舒','屈','项','祝',
            '董','梁','杜','阮','蓝','闵','季','贾','路','娄','江','童','颜','郭','梅','盛','林','钟','徐','邱','骆','高','夏','蔡','田','樊','胡','凌','霍','虞','万','支','柯','管','卢','莫',
            '柯','房','裘','缪','解','应','宗','丁','宣','邓','单','杭','洪','包','诸','左','石','崔','吉','龚','程','嵇','邢','裴','陆','荣','翁','荀','于','惠','甄','曲','封','储','仲','伊',
            '宁','仇','甘','武','符','刘','景','詹','龙','叶','幸','司','黎','溥','印','怀','蒲','邰','从','索','赖','卓','屠','池','乔','胥','闻','莘','党','翟','谭','贡','劳','逄','姬','申',
            '扶','堵','冉','宰','雍','桑','寿','通','燕','浦','尚','农','温','别','庄','晏','柴','瞿','阎','连','习','容','向','古','易','廖','庾','终','步','都','耿','满','弘','匡','国','文',
            '寇','广','禄','阙','东','欧','利','师','巩','聂','关','荆','司马','上官','欧阳','夏侯','诸葛','闻人','东方','赫连','皇甫','尉迟','公羊','澹台','公冶','宗政','濮阳','淳于','单于','太叔',
            '申屠','公孙','仲孙','轩辕','令狐','徐离','宇文','长孙','慕容','司徒','司空','皮'];
        /**
         * 名
         */
        $arrMing=['伟','刚','勇','毅','俊','峰','强','军','平','保','东','文','辉','力','明','永','健','世','广','志','义','兴','良','海','山','仁','波','宁','贵','福','生','龙','元','全'
            ,'国','胜','学','祥','才','发','武','新','利','清','飞','彬','富','顺','信','子','杰','涛','昌','成','康','星','光','天','达','安','岩','中','茂','进','林','有','坚','和','彪','博','诚'
            ,'先','敬','震','振','壮','会','思','群','豪','心','邦','承','乐','绍','功','松','善','厚','庆','磊','民','友','裕','河','哲','江','超','浩','亮','政','谦','亨','奇','固','之','轮','翰'
            ,'朗','伯','宏','言','若','鸣','朋','斌','梁','栋','维','启','克','伦','翔','旭','鹏','泽','晨','辰','士','以','建','家','致','树','炎','德','行','时','泰','盛','雄','琛','钧','冠','策'
            ,'腾','楠','榕','风','航','弘','秀','娟','英','华','慧','巧','美','娜','静','淑','惠','珠','翠','雅','芝','玉','萍','红','娥','玲','芬','芳','燕','彩','春','菊','兰','凤','洁','梅','琳'
            ,'素','云','莲','真','环','雪','荣','爱','妹','霞','香','月','莺','媛','艳','瑞','凡','佳','嘉','琼','勤','珍','贞','莉','桂','娣','叶','璧','璐','娅','琦','晶','妍','茜','秋','珊','莎'
            ,'锦','黛','青','倩','婷','姣','婉','娴','瑾','颖','露','瑶','怡','婵','雁','蓓','纨','仪','荷','丹','蓉','眉','君','琴','蕊','薇','菁','梦','岚','苑','婕','馨','瑗','琰','韵','融','园'
            ,'艺','咏','卿','聪','澜','纯','毓','悦','昭','冰','爽','琬','茗','羽','希','欣','飘','育','滢','馥','筠','柔','竹','霭','凝','晓','欢','霄','枫','芸','菲','寒','伊','亚','宜','可','姬'
            ,'舒','影','荔','枝','丽','阳','妮','宝','贝','初','程','梵','罡','恒','鸿','桦','骅','剑','娇','纪','宽','苛','灵','玛','媚','琪','晴','容','睿','烁','堂','唯','威','韦','雯','苇','萱'
            ,'阅','彦','宇','雨','洋','忠','宗','曼','紫','逸','贤','蝶','菡','绿','蓝','儿','翠','烟'];
        switch ($type){
            case 1:
                $tou_num=rand(0,count($nicheng_tou)-1);
                $wei_num=rand(0,count($nicheng_wei)-1);
                $nicheng=$nicheng_tou[$tou_num].$nicheng_wei[$wei_num];
            case 2:
                $nicheng=$arrXing[mt_rand(0,count($arrXing)-1)];
                for($i=1;$i<=3;$i++)
                {
                    $nicheng .=(mt_rand(0,1)?$arrMing[mt_rand(0,count($arrMing)-1)]:$arrMing[mt_rand(0,count($arrMing)-1)]);
                }
        }
        return $nicheng;
}


}

$oen = new five();
$oen->index();

?>

