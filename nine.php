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

        //include "cron_jiqi.php";
        
        $dbHost = "127.0.0.1";  //不用改
        $dbName = "tianshi_ceshi_zhandian_kkwl";  //数据库名
        $dbUser = "tianshi_ceshi_zhandian_kkwl"; //数据库登陆名
        $dbPassword = "aRTpiCcJ2rn6pnzt3q1c"; //数据库登陆名密码
        $this->token = "8574522036:AAFSmvLc8wCre4uGj1ijfeUYoWaSrhriDJQ"; //一对一机器人  @yiduiyirockbot
        $this->chat_url = "https://ceshi.rocketchattongxunxitong.top";
        $this->rocket_url = "https://ceshizhandian.rctgshuangxiangtongxunxitong.top";
        //$this->chat_url = $chat_url;
        //$token = $this->token;
        
        
        
        $this->link = 'https://api.telegram.org/bot' . $this->token . '';
        $this->pdo = new PDO("mysql:host=" . $dbHost . ";dbname=" . $dbName, $dbUser, $dbPassword, array(PDO::ATTR_PERSISTENT => true));


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
        return $response;
    }

    /**
     * 上传图片到 Rocket.Chat 的 Live Chat
     *
     * @param string $filePath 文件路径
     * @param string $visitorToken 访客 Token
     * @return mixed 文件 URL 或错误信息
     */
    function uploadFileToLiveChatRoom($filePath, $roomId, $visitorToken, $description = '', $chatid)
    {
        $url = $this->chat_url . '/api/v1/livechat/upload/' . $roomId;
        $postData = [
            'file' => new CURLFile($filePath),
            'description' => $description,
        ];
        $headers = [
            'x-visitor-token: ' . $visitorToken,
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);


        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
            return false;
        }

        $result = json_decode($response, true);
        curl_close($ch);

        return $result;
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
    function jiemi($input) {
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

    
    public function index()
    {


        $data = json_decode(file_get_contents('php://input'), TRUE); //读取json并对其格式化
        $datatype = $data['message']['chat']['type'];//获取message


 
        if ($data['callback_query']) {
            $this->callback($data);
        } else {


            $chatid = $data['message']['chat']['id'];//获取chatid

            $message = $data['message']['text'];//获取message
            $userid = $data['message']['from']['id'];//获取message

            //这里需要判断一下是不是正在进行客服聊天，如果是的话，需要直接走客服逻辑:
            $kefu_sql = "select * FROM pay_userchat where chat_id ='" . $chatid . "' and channel='".$this->token."' and status='0'";
            $kefu_query = $this->pdo->query($kefu_sql);
            $kefu_info = $kefu_query->fetchAll();
            
            if ($kefu_info) {
                $token = $kefu_info[0]['visitorToken'];
                $room_id = $kefu_info[0]['room_id'];
                $agentId = $kefu_info[0]['agent_id'];
                $kefu_username = $kefu_info[0]['kefu_name'];
                $message_kefu = $data['message'];
                $chatId = $message_kefu['chat']['id'];
                if (isset($message_kefu['text'])) {
                    // 处理文字消息
                    $text = $message_kefu['text'];
                    
                    // 处理@提及转换：将Telegram的@tg_user_name转换为Rocket.Chat的@username
                    $self = $this;
                    $processAtMentions = function($text, $message_data) use ($chatid, $self) {
                        if (empty($text)) {
                            return $text;
                        }
                        
                        // 方法1: 通过entities字段获取@提及（更准确）
                        $entities = [];
                        if (isset($message_data['entities'])) {
                            $entities = $message_data['entities'];
                        } elseif (isset($message_data['caption_entities'])) {
                            $entities = $message_data['caption_entities'];
                        }
                        
                        $mentions = [];
                        foreach ($entities as $entity) {
                            if (isset($entity['type']) && $entity['type'] === 'mention') {
                                $offset = $entity['offset'];
                                $length = $entity['length'];
                                $tg_username = substr($text, $offset + 1, $length - 1); // 去掉@符号
                                
                                // 查询pay_userchatrc表中对应的username
                                $at_sql = "SELECT username FROM pay_userchatrc WHERE tg_user_name = :tg_user_name LIMIT 1";
                                $at_stmt = $self->pdo->prepare($at_sql);
                                $at_stmt->execute([':tg_user_name' => $tg_username]);
                                $at_result = $at_stmt->fetch(PDO::FETCH_ASSOC);
                                
                                if ($at_result && !empty($at_result['username'])) {
                                    $mentions[] = [
                                        'tg_username' => $tg_username,
                                        'rc_username' => $at_result['username'],
                                        'offset' => $offset,
                                        'length' => $length
                                    ];
                                }
                            }
                        }
                        
                        // 方法2: 如果entities没有找到，使用正则表达式匹配@username
                        if (empty($mentions)) {
                            preg_match_all('/@(\w+)/', $text, $matches, PREG_OFFSET_CAPTURE);
                            if (!empty($matches[1])) {
                                foreach ($matches[1] as $match) {
                                    $tg_username = $match[0];
                                    $offset = $match[1] - 1; // 减去@符号的位置
                                    
                                    // 查询pay_userchatrc表中对应的username
                                    $at_sql = "SELECT username FROM pay_userchatrc WHERE tg_user_name = :tg_user_name LIMIT 1";
                                    $at_stmt = $self->pdo->prepare($at_sql);
                                    $at_stmt->execute([':tg_user_name' => $tg_username]);
                                    $at_result = $at_stmt->fetch(PDO::FETCH_ASSOC);
                                    
                                    if ($at_result && !empty($at_result['username'])) {
                                        $mentions[] = [
                                            'tg_username' => $tg_username,
                                            'rc_username' => $at_result['username'],
                                            'offset' => $offset,
                                            'length' => strlen($tg_username) + 1
                                        ];
                                    }
                                }
                            }
                        }
                        
                        // 从后往前替换，避免偏移量变化的问题
                        if (!empty($mentions)) {
                            // 按offset降序排序
                            usort($mentions, function($a, $b) {
                                return $b['offset'] - $a['offset'];
                            });
                            
                            foreach ($mentions as $mention) {
                                $old_text = '@' . $mention['tg_username'];
                                $new_text = '@' . $mention['rc_username'];
                                $text = substr_replace($text, $new_text, $mention['offset'], $mention['length']);
                            }
                        }
                        
                        return $text;
                    };
                    
                    // 转换@提及
                    $text = $processAtMentions($text, $message_kefu);
                    
                    // 调用关键字匹配接口进行替换
                    $text = $this->matchKeywords($text);

                  $resulyt1=  $this->sendMessageToRocketChat($text, $room_id, $token, $agentId, $kefu_username, $chatid);
                  //{"success":false,"error":"invalid-token"}
                  
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
                    $fileUrl = $this->uploadFileToLiveChatRoom($localFilePath, $room_id, $token, $description, $chatid);
                    // $this->xiaoxi($fileUrl, $chatid);
                    if ($fileUrl && isset($fileUrl['file'])) {
                        $fileUrl = $this->chat_url . $fileUrl['file']['path'];
                        $this->sendImageMessage($room_id, $token, "![image]($fileUrl)");
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
                        
                        $fileUrl = $this->uploadFileToLiveChatRoom($localFilePath, $room_id, $token, $description, $chatid);
                        if ($fileUrl && isset($fileUrl['file'])) {
                            $fileUrl = $this->chat_url . $fileUrl['file']['path'];
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
                        
                        $fileUrl = $this->uploadFileToLiveChatRoom($localFilePath, $room_id, $token, $description, $chatid);
                        if ($fileUrl && isset($fileUrl['file'])) {
                            $fileUrl = $this->chat_url . $fileUrl['file']['path'];
                            $this->sendImageMessage($room_id, $token, "![sticker]($fileUrl)"); 
                        } else {
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
                        
                        $fileUrl = $this->uploadFileToLiveChatRoom($localFilePath, $room_id, $token, $description, $chatid);
                        if ($fileUrl && isset($fileUrl['file'])) {
                            $fileUrl = $this->chat_url . $fileUrl['file']['path'];
                            $this->sendImageMessage($room_id, $token, "![document]($fileUrl)"); 
                        } else {
                            $this->xiaoxi("上传贴纸文件失败，请稍后再试！", $chatid);
                        }
                 }


                $kefu_xiaoxi_url = $this->chat_url . "/api/v1/livechat/message";
                $qingqiuti = array(
                    'token' => $kefu_info['0']['visitorToken'],
                    'rid' => $kefu_info['0']['room_id'],
                    'msg' => "",
                    "agent" => array(
                        "agentId" => $kefu_info['0']['agent_id'],
                        'username' => $kefu_info['0']['kefu_name'],
                    )
                );
            }


            $this->message($message, $chatid, $data, $userid);
        }


    }

    public function message($message, $chatid, $data, $userid)
    {

        $sql_info = "select * from pay_botsettle where chatid ='" . $chatid . "'";
        $order_query2 = $this->pdo->query($sql_info);
        $userbotsettle_info2 = $order_query2->fetchAll();

        $dapid = $userbotsettle_info2[0]['merchant'];

        $username = $data['message']['from']['username'];//用户名称

        if (strpos($message, '呼叫24h客服') !== false) {

            $kefu_sql = "select * FROM pay_userchat where chat_id ='" . $chatid . "' and channel='".$this->token."' and status='0'";

            $kefu_query = $this->pdo->query($kefu_sql);
            $kefu_info = $kefu_query->fetchAll();
            if ($kefu_info) {
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

            //先去选择对应的客服部门： 
            $bottoken = $this->token;
            $info = Http::sendPostRequest($this->rocket_url."/api/Index/index", ['bottoken' => $bottoken]);
            $info_arr = json_decode($info, true);
            if ($info_arr['code'] == "0") {
                $this->xiaoxi($info_arr['msg'], $chatid);
            }

            if (count($info_arr['data']) > 1) {
                $inline_keyboard_arr = array();
                for ($i = 0; $i < count($info_arr['data']); $i++) {
                    $inline_keyboard_arr[] = array("text" => $info_arr['data'][$i], "callback_data" => "xunzhaokefu_" . $info_arr['data'][$i]);
                }

                $msg = "请选择你要的客服类型:";
                $keyboard = [
                    'inline_keyboard' => [
                        $inline_keyboard_arr,

                    ]
                ];

                $parameter = array(
                    "chat_id" => $chatid,
                    "text" => $msg,
                    "parse_mode" => "HTML",
                    "disable_web_page_preview" => true,
                    'reply_markup' => $keyboard
                );
                $this->http_post_data('sendMessage', json_encode($parameter));
                exit();
            }
            $depart_info = $info_arr['data'][0];


            //$quanxian = "呼叫24h客服";
            //$this->quanxian($chatid, $userid, $quanxian, $username);
            // $parameter = array( 
            //     'chat_id' => $chatid, 
            //     'text' => "正在唤起客服系统~请稍后~",
            //     'show_alert' => true
            // );
            $sql_info = "select * from pay_botsettle where chatid ='" . $chatid . "'";
            $order_query2 = $this->pdo->query($sql_info);
            $chatinfo = $order_query2->fetchAll();
            $uid = $chatinfo['0']['merchant'];
           
            $visitorToken = $this->generateVisitorToken($chatid,$depart_info);
          
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
                    "department" => $depart_info,  //天使技术部A，上游客服部B
                ]
            ]));
            
           
             // 设置超时时间
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
            // SSL 验证（如果需要可以设置为 false，但不推荐）
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

            $response = curl_exec($ch);
            
            // 检查 cURL 错误
            if (curl_errno($ch)) {
                $error_msg = curl_error($ch);
                $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
                $this->xiaoxi("客服系统连接失败: " . $error_msg . " (HTTP: " . $http_code . ")", $chatid);
                return;
            }
            
            // 获取 HTTP 状态码
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            // 检查响应是否为空
            if (!$response) {
                $this->xiaoxi("客服人工座席忙,请稍后再请求！(HTTP状态码: " . $http_code . ")", $chatid);
                return;
            }

            $visitorData = json_decode($response, true);
            
            // 检查 JSON 解析是否成功
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->xiaoxi("客服系统响应格式错误: " . json_last_error_msg() . " 响应内容: " . substr($response, 0, 200), $chatid);
                return;
            }
            
            // 检查 API 返回的 success 字段
            if (!isset($visitorData['success']) || !$visitorData['success']) {
                $error_msg = isset($visitorData['error']) ? $visitorData['error'] : '未知错误';
                $this->xiaoxi("注册访客失败: " . $error_msg . " (HTTP: " . $http_code . ")", $chatid);
                return;
            }


            $visitorData = json_decode($response, true);

            //第二步：通过token拿到数据先：
            //https://ccc.zmchat.xyz/api/v1/livechat/room?token=bc2a23307a699c2909a54c5948c2b05c036c92dc200568646cb5b10cabb4a0d5
            $url2 = $serverUrl . "/api/v1/livechat/room?token=" . $visitorToken;
            $headers2 = [];
            $response2 = $this->httpGet($url2, $headers2);
            if (!$response2) {
                $this->xiaoxi("客服人工座席忙,请稍后再请求456！".$url2, $chatid);
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
            
            $agent_id = $response3['agent']['_id'];
            $kefu_username = $response3['agent']['username'];

            //这里来一个记录，表示当前商户正在对话，进行中：
            $status = "0";
            $channel = $this->token;
            $createtime = time();
            $set_sql = "insert into pay_userchat (depart_info,channel,status,visitorToken,room_id,agent_id,user_id,createtime,chat_id,kefu_name) values ('" . $depart_info . "','" . $channel . "','" . $status . "','" . $visitorToken . "', '" . $room_id . "','" . $agent_id . "','" . $uid . "','" . $createtime . "','" . $chatid . "','" . $kefu_username . "')";
            $this->pdo->exec($set_sql);
            $this->xiaoxi("客服:" . $kefu_username . ",为你开启服务，请简要说出你的需求", $chatid);
        }


        //开始：
        if (strpos($message, '/start') !== false) {

            $keyboard2 = [
                'keyboard' => [
                    [
                        ['text' => '呼叫24h客服'],
                    ]
                ],
                //可选。请求客户端垂直调整键盘大小以获得最佳适配（例如，如果只有两行按钮，则使键盘更小）。默认为false，在这种情况下，自定义键盘始终与应用程序的标准键盘高度相同。
                'resize_keyboard' => true,
                //可选。要求客户在使用后立即隐藏键盘。键盘仍然可用，但客户端会在聊天中自动显示常用的字母键盘——用户可以在输入字段中按下一个特殊的按钮来再次看到自定义键盘。默认为false。
                'one_time_keyboard' => false,
                //string 可选。键盘处于活动状态时要在输入字段中显示的占位符；1-64 个字符
                //'input_field_placeholder'=>'',
                //可选。如果您只想向特定用户显示键盘，请使用此参数。目标：1），其在用户@mentioned文本的的消息对象; 2）如果机器人的消息是回复（有reply_to_message_id），原始消息的发件人。

                //'selective'=>''
            ];
            $encodedKeyboard2 = json_encode($keyboard2);

            /* $botId = $this->response->getId();
             $firstName = $this->response->getFirstName();
             $lastName = $this->response->getLastName();
             $userName = $this->response->getUsername();*/
            $parameter = array(
                'chat_id' => $chatid,
                'text' => "你好:" . "欢迎使用本系统！",
                'reply_markup' => $encodedKeyboard2
            );
            //发送消息

            $this->http_post_data('sendMessage', json_encode($parameter));
            exit();
        } else {
            if ($data['message']['new_chat_member']) {
                $parameter = array(
                    'chat_id' => $chatid,
                    'text' => "欢迎：" . $data['message']['new_chat_member']['first_name'] . $data['message']['new_chat_member']['last_name'] . "入群！",
                );
                $this->http_post_data('sendMessage', json_encode($parameter));

            }

        }

    }


    /**
     * 调用关键字匹配接口
     * @param string $text 原始文字
     * @return string 替换后的文字
     */
    public function matchKeywords($text)
    {
        if(empty($text)){
            return $text;
        }
        
        $url = $this->rocket_url . "/api/Index/matchKeywords";
        
        // 使用 curl 发送 POST 请求
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(['text' => $text]));
        curl_setopt($ch, CURLOPT_TIMEOUT, 5); // 设置超时时间
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        // 如果请求失败，返回原始文字
        if($httpCode != 200 || empty($response)){
            return $text;
        }
        
        // 解析返回的 JSON
        $result = json_decode($response, true);
        
        // 如果解析成功且返回了替换后的文字，则使用替换后的文字
        if(isset($result['data']) && !empty($result['data'])){
            return $result['data'];
        }
        
        // 否则返回原始文字
        return $text;
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


}

$oen = new five();
$oen->index();

?>
