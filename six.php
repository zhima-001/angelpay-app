<?php


class Http
{

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
        return $req['msg'];
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
    private $token = "";
    private $ownerAddress = "";
    private $telegram;
    private $pdo;
    private $jiqirenming;
    private $jilvqun_chat_id;
    
    private $neibu_chat_id;
    
    private $chaojiyonghu;
    private $all_ming_list = array(


    );

    public function __construct()
    {
        include "cron_jiqi.php"; 
        include "renwu_jiqi.php"; 
     
        $this->token = $token_renwu;
     
        $this->jilvqun_chat_id = $this_jilvqun_chat_id; //任务讨论群的chatid
        $this->jilvqun_chat_id_no = $this_jilvqun_chat_id_no;  //任务讨论群的chatid--->获取消息直链
        $this->pindaochatid = $this_pindaochatid;//任务频道的chatid
        $this->pindaochatid_no = $this_pindaochatid_no; //任务频道的chatid--->获取消息直链
        $this->wanchengchatid = $this_wanchengchatid;//完成任务通知，暂时不用
        $this->neibu_chat_id = $this_neibu_chat_id; //内部群chatid  限制访问使用机器人

        $this->jiqirenming = $this_jiqirenming; 
  
 
        $token = $this->token;
        $this->link = 'https://api.telegram.org/bot' . $token . '';
        $this->pdo = new PDO("mysql:host=" . $dbHost . ";dbname=" . $dbName, $dbUser, $dbPassword, array(PDO::ATTR_PERSISTENT => true));
    }


    public function index()
    {

        $data = json_decode(file_get_contents('php://input'), TRUE); //读取json并对其格式化
        $datatype = $data['message']['chat']['type'];//获取message


       $sql = "insert into pay_jiqi (content) values ('" . json_encode($data) . "')";
       $this->pdo->exec($sql);
      
      if($data['message']['chat']['type']=="private"){
          //查员工信息
          $usertg = $data['message']['chat']['username'];
         $set_sqlq = "select * FROM pay_jishuuser where tgname='".$usertg."'";
         $user_query_q = $this->pdo->query($set_sqlq);
         $user_all = $user_query_q->fetchAll();
          if($user_all){
              //更新最新的私聊频道
              $zuixin_chat = $data['message']['chat']['id'];
              $set_sql2 = "update pay_jishuuser set siliaourl ='" . $zuixin_chat . "' where  tgname='" . $usertg . "'";
              $this->pdo->exec($set_sql2);
          }
      }
       
        
        $is_tg = $data['message']['from']['first_name'];
        if($is_tg=="Telegram"){
            $chatid = $data['message']['chat']['id'];//获取chatid
            
            if($data['message']['caption']){
              
                 $text = $data['message']['caption'];
              
            }else{
                 $text = $data['message']['text'];

            }
           $media_group_id = $data['message']['media_group_id'];
            $textsq = $text;
            if(empty($textsq)){
                 $sql_info2 = "select * from pay_jishurenwu where xiaoxi_media_group_id='".$media_group_id."'";
                 $find_user_query2 = $this->pdo->query($sql_info2);
                 $find_renwu = $find_user_query2->fetchAll();
                 if($find_renwu[0]['is_tishi'] == "1"){
                    exit();
                 } 
            }
         
            /*$text_arr = explode("：",$text);
            $text_arr2 = explode("\n",$text_arr[1]);
            $need_name = $text_arr2[0];*/
          
           // $this->xiaoxinoend(json_encode($data),$chatid); 
             
              
           $sql_info2 = "select * from pay_jishurenwu where content='".$text."' and remark='初次发布任务' and status='0'";
           $find_user_query2 = $this->pdo->query($sql_info2);
           $find_renwu = $find_user_query2->fetchAll();
            
            
            
           $tg_username = $find_renwu[0]['jishuuser_id'];
           $sql_info = "select * from pay_jishuuser where id='".$tg_username."'";
           $find_user_query = $this->pdo->query($sql_info);
           $find_user = $find_user_query->fetchAll();
  
      
           $renwu_name ="RW".date('md').rand(1000,9999).rand(1000,9999);
         
           $jishuuser_id = $find_user[0]['id'];
           $jishuuser_tgname = $find_user[0]['tgname'];
           $jishuuser_tgusername = $find_user[0]['tgusername'];
            
            
            $renwu_id = $find_renwu[0]['id'];
        
            $message_id = $data['message']['message_id'];
            //这里还要单独的给记录群发操作按钮的按钮：
           $inline_keyboard_arr3[0] = array('text' => "接受任务", "callback_data" => "jieshourenwu_" . $renwu_id);
           $inline_keyboard_arr3[1] = array('text' => "关闭任务", "callback_data" => "guanbirenwu_" . $renwu_id); 
            $keyboard = [
                'inline_keyboard' => [
                    $inline_keyboard_arr3,
                ]
                
            ];
            
            //这里去修改一下频道的信息：
            
            
            $text = "@".$jishuuser_tgname." 收到新任务\r\n任务编号：".$renwu_name."\r\n请及时处理，超时10分钟将计入绩效考核评分。\r\n🔔当前任务负责人:". $jishuuser_tgusername."\r\n";
            
            $parameter = array(
           
               'chat_id' => $chatid,
                'parse_mode' => 'HTML',
                'text' =>  $text,
                'reply_markup' => $keyboard,
                'disable_web_page_preview' => true,
                'reply_to_message_id'=>$message_id
            );
           $this->http_post_data('sendMessage', json_encode($parameter));
           
           $set_sql2 = "update pay_jishurenwu set xiaoxi_media_group_id='".$media_group_id."',is_tishi='1',yuanshi_message_id ='" . $message_id . "' where  content='" . $textsq . "'";
           $chang_status = $this->pdo->exec($set_sql2);
           
           exit();
        }
        
        
        //频道信息：
        if(count($data['channel_post'])>0){
            //默认文字：
            $type="0";
            $chatid = $data['channel_post']['chat']['id'];//获取chatid
            $message_id = $data['channel_post']['message_id'];//获取message_id
            $update_id = $data['update_id'];
            if($data['channel_post']['caption']){
                $type="1";
                 $message = $data['channel_post']['caption'];
            }else{
                 $message = $data['channel_post']['text'];
          
            }
           $media_group_id = $data['channel_post']['media_group_id'];
            $parameter2 = array( 
                'chat_id' => $chatid,
                'message_id' => $message_id,
              
            );
          // $this->xiaoxinoend(json_encode($data),$chatid);
           if(empty($message)){
               //获取数量：
               $sql_info2 = "select * from pay_jishurenwu where media_group_id='".$media_group_id."'";
               $find_user_query2 = $this->pdo->query($sql_info2);
               $find_renwu = $find_user_query2->fetchAll();
               if($find_renwu){
                   $set_sql_jia = "update pay_jishurenwu set have_num=have_num+1 where  media_group_id='" . $media_group_id . "'";
                   $this->pdo->exec($set_sql_jia);
               }
               
                $have_file = count($data['channel_post']['photo'])-1;
                 $parameter3 = array(
                    'chat_id'=>$chatid,
                    'photo'=>$data['channel_post']['photo'][$have_file]['file_id'],
                    // 'caption'=>"任务编号：".$renwu_name."\r\n具体问题描述：".$message ,
                    'media_group_id'=>$data['channel_post']['media_group_id']
                );
                $this->http_post_data('sendMessage', json_encode($parameter3)); 
                exit();
            }
            
          
        
           
             
            
           $tg_username = $data['channel_post']['author_signature'];
           $sql_info = "select * from pay_jishuuser where tgusername='".$tg_username."'";
           $find_user_query = $this->pdo->query($sql_info);
           $find_user = $find_user_query->fetchAll();
           if(!$find_user){
               $this->xiaoxinoend("未找到员工".$tg_username."账号,禁止发不消息",$chatid);
               $this->http_post_data('deleteMessage', json_encode($parameter2));
                exit();
           }
      
           $renwu_name ="RW".date('md').rand(1000,9999).rand(1000,9999);
         
           $jishuuser_id = $find_user[0]['id'];
           $jishuuser_tgname = $find_user[0]['tgname'];
           $jishuuser_tgusername = $find_user[0]['tgusername'];
           
            
           //发布任务
           $set_sql_add = "insert into pay_jishurenwu (name,jishuuser_id,content,createtime,pjishuuser_id,status,remark,message_id,update_id,media_group_id,have_num) values ('" . $renwu_name . "','" . $jishuuser_id . "','" . $message . "','" . time() . "','" .$jishuuser_id. "','0','初次发布任务','".$message_id."','".$update_id."','".$media_group_id."','".$type."')";
    
           $this->pdo->exec($set_sql_add);
           $renwu_id = $this->pdo->lastInsertId();
             
           //记录日志：
          
           $set_sql_add2 = "insert into pay_jishurecord (jishurenwu_id,typelist,last_jishuuser_id,end_jishuuser_id,createtime,huafeitime,remark) values ('" . $renwu_id . "','".'3'."','" . $jishuuser_id . "','" . $jishuuser_id . "','" . time() . "','0','初次发布任务')";
             
           $order_info_add = $this->pdo->exec($set_sql_add2);
        
           //再次将此消息推送到谈论群去：
           
            
          //这里还要单独的给记录群发操作按钮的按钮：
           $inline_keyboard_arr3[0] = array('text' => "接受任务", "callback_data" => "jieshourenwu_" . $renwu_id);
           $inline_keyboard_arr3[1] = array('text' => "关闭任务", "callback_data" => "guanbirenwu_" . $renwu_id); 
            $keyboard = [
                'inline_keyboard' => [
                    $inline_keyboard_arr3,
                ]
                
            ];
            
            //这里去修改一下频道的信息：
            
            
            $text = "@".$jishuuser_tgname." 收到新任务\r\n任务编号：".$renwu_name."\r\n请及时处理，超时10分钟将计入绩效考核评分。\r\n🔔当前任务负责人:". $jishuuser_tgusername."\r\n";
            
            /*$parameter = array(
               'chat_id' => $this->jilvqun_chat_id,
                //'chat_id' => $chatid,
                'parse_mode' => 'HTML',
                'text' =>  $text,
                'reply_markup' => $keyboard,
                'disable_web_page_preview' => true,
                //'reply_to_message_id'=>$message_id
            );*/
            //$this->http_post_data('sendMessage', json_encode($parameter));
      
            $linkurl = "https://t.me/c/".$this->pindaochatid_no."/".($message_id);
           
      
           if($type=="1"){ 
                  $set_sql2 = "update pay_jishurenwu set linkurl='".$linkurl."',type='2',phone_id='".$parameter3['photo']."',fa_yuanshi_message_id ='" . $message_id . "' where  name='" . $renwu_name . "'";
                 $chang_status = $this->pdo->exec($set_sql2);
                /*$have_file = count($data['channel_post']['photo'])-1;
                 $parameter3 = array(
                    'chat_id'=>$chatid,
                    'photo'=>$data['channel_post']['photo'][$have_file]['file_id'],
                    'caption'=>"任务编号：".$renwu_name."\r\n具体问题描述：".$message  
                );
                $this->http_post_data('sendPhoto', json_encode($parameter3));*/
                
              
                
            }else{
                 $set_sql2 = "update pay_jishurenwu set linkurl='".$linkurl."',type='1',fa_yuanshi_message_id ='" . $message_id . "' where  name='" . $renwu_name . "'";
                   $chang_status = $this->pdo->exec($set_sql2);
                /* $parameter3 = array(
                    'chat_id'=>$chatid,
                    'text'=>"任务编号：".$renwu_name."\r\n具体问题描述：".$message 
                );
                $this->http_post_data('sendMessage', json_encode($parameter3));*/
            }
           
            //发送消息给私聊机器人：
            $this->lairenwule($jishuuser_tgname);
            
            /*$this->http_post_data('deleteMessage', json_encode($parameter2));
            exit();*/
        }
        
        
        if ($data['callback_query']) { 
            $this->callback($data);
        } else {
              $chatid = $data['message']['chat']['id'];//获取chatid
            $photo_field_id = "0";
            $media_group_id =0;
            $type = 1;
            if(count($data['message']['photo'])>0){
                $type = 2;
                  $message = $data['message']['caption'];//获取message
                     $have_file = count($data['message']['photo'])-1;
                    $photo_field_id =$data['message']['photo'][$have_file]['file_id'];
            }else{
                
                 $message = $data['message']['text'];//获取message
            }
            //$this->xiaoxinoend(json_encode($data),$chatid);
          
            if(!empty($data['message']['media_group_id'])){
                  $media_group_id = $data['message']['media_group_id'];
                  $set_sqlq = "select * FROM pay_jishurenwu where photo_field_id='".$media_group_id."' and zhuanyu_type='2'";
                 $user_query_q = $this->pdo->query($set_sqlq);
                 $user_all = $user_query_q->fetchAll();
                 if($user_all){
                    $renwu_id = $user_all[0]['id'];
                    $set_sql_add9 = "insert into pay_jishuzhuanyi (media_group_id,photo_field_id,renwu_id) values ('" . $media_group_id."','".$photo_field_id."','".$renwu_id."')";
                  $this->pdo->exec($set_sql_add9);  
                 }

            }
           
            $userid = $data['message']['from']['id'];//获取message
            $this->message($message, $chatid, $data, $userid,$photo_field_id,$media_group_id);
        }


    }

    public function lairenwule($tgname){
        /*$text = "@xxxxxx
⚛️共收到2个任务，请尽快领取，切勿超时

⏰(1分钟前收到)
🔍https://t.me/c/1907713519/185
⏰(5分钟前收到)
🔍https://t.me/c/1907713519/185";*/
           $sql_info = "select * from pay_jishuuser where tgname='".$tgname."'";
           $find_user_query = $this->pdo->query($sql_info);
           $find_user = $find_user_query->fetchAll();
           $fasong_chat_id = $find_user[0]['siliaourl'];
           
           //查看任务：
           $sql_info2 = "select * from pay_jishurenwu where pjishuuser_id='".$find_user[0]['id']."'";
           $find_user_query2 = $this->pdo->query($sql_info2);
           $find_renwu = $find_user_query2->fetchAll();
           $have_count = count($find_renwu);
            $text = "@".$tgname." ⚛️共收到".$have_count."个任务，请尽快领取，切勿超时\r\n\r\n";
            foreach ($find_renwu as $k=>$v){
                $n = ceil((time()-$v['createtime'])/60);
                $text .= "⏰(".$n."分钟前收到)\r\n";
                $text .= "🔍"."<a href ='".$v['linkurl']."'>".$v['linkurl']."</a>"."\r\n";
            }
            $parameter = array(
                'chat_id' => $fasong_chat_id,
                'parse_mode' => 'HTML',
                'text' =>$text
            );
            $this->http_post_data('sendMessage', json_encode($parameter));
           
    }

    public function chaojiyonghuquanxian($userid, $chatid)
    {
        $chuge_userid_arr = $this->chaojiyonghu;
        if (!in_array($userid, $chuge_userid_arr)) {
            $ids_str = implode(",", $chuge_userid_arr);
            $parameter = array(
                'chat_id' => $chatid,
                'parse_mode' => 'HTML',
                'text' => "仅Tg_ID:" . $ids_str . "有此权限！"
            );
            $this->http_post_data('sendMessage', json_encode($parameter));
            exit();
        }
    }

    public function message($message, $chatid, $data, $userid,$photo_field_id,$media_group_id)
    {
        $from_username =   $data['message']['from']['username'];   
        $set_sqlq1 = "select * FROM pay_jishuuser where tgname='".$from_username."'";
        $user_query_q2 = $this->pdo->query($set_sqlq1);
        $user_find = $user_query_q2->fetchAll();
        $now_user_id = $user_find[0]['id'];
       
        $message_id = $data['message']['message_id'];
        //查看所有员工：
         $set_sqlq = "select * FROM pay_jishuuser";
         $user_query_q = $this->pdo->query($set_sqlq);
         $user_all = $user_query_q->fetchAll();
        
         $user_arr = array(); 
         foreach ($user_all as $key=>$value){
             $user_arr[$value['id']] = array('tgusername'=>$value['tgusername'],'name'=>$value['name'],'tgname'=>$value['tgname']); 
          } 
          
        if($message == "清空所有数据"){
            if($from_username !="QingLang1688"){
                 $parameter = array(
                'chat_id' => $chatid,
                'parse_mode' => 'HTML',
                'text' => "仅晴朗有此权限！"
            );
            $this->http_post_data('sendMessage', json_encode($parameter));
            exit();
            }
            
            //pay_jiqi   pay_jishurecord   pay_jishurenwu   pay_jishurenwuwancheng   pay_jishutianxie
            $del_sql1 = "DELETE FROM pay_jiqi";
            $this->pdo->exec($del_sql1);
            
            $del_sql2 = "DELETE FROM pay_jishurecord";
            $this->pdo->exec($del_sql2);
            
            $del_sql3 = "DELETE FROM pay_jishurenwu";
            $this->pdo->exec($del_sql3);
            
            $del_sql4 = "DELETE FROM pay_jishurenwuwancheng";
            $this->pdo->exec($del_sql4);
            
            $del_sql5 = "DELETE FROM pay_jishutianxie";
            $this->pdo->exec($del_sql5);
            
            $del_sq6 = "DELETE FROM pay_jishushuom";
            $this->pdo->exec($del_sq6);
            
            $del_sq6 = "DELETE FROM pay_jishuzhuanyi";
            $this->pdo->exec($del_sq6);
             $this->xiaoxi("清理完成！",$chatid);
        }  
        if($message=="当前任务"){
            $text = "🔴当前任务:\r\n";
            $set_renwu = "select * FROM pay_jishurenwu where status !='2'";
            $renwu_query_q = $this->pdo->query($set_renwu);
            $renwu_all = $renwu_query_q->fetchAll();
            $all_user_renwu = array();
            $now_time = time();
            if(count($renwu_all)<=0){
                $this->xiaoxi("当前无任务正在进行！",$chatid);
            }
            
            foreach ($renwu_all as $k=>$v){
                //查询用时：
                $record_sql = "select * from pay_jishurecord where jishurenwu_id='".$v['id']."'";
                $record_query_q = $this->pdo->query($record_sql);
                $record_all = $record_query_q->fetchAll();
                $havetime = ceil((($now_time-$record_all[0]['createtime'])/60));
                
                if($v['typelist']=="2"){
                     //待接受
                     $all_user_renwu[$v['pjishuuser_id']]['daijieshou'][] = $v['name'];
                     $all_user_renwu[$v['pjishuuser_id']]['daijieshou_time'] += $havetime;
                }else{
                     //进行中
                     $all_user_renwu[$v['pjishuuser_id']]['jinxing'][] = $v['name'];
                     $all_user_renwu[$v['pjishuuser_id']]['jinxing_time'] += $havetime;
                }
            }
            foreach ($all_user_renwu as $kv=>$vv){
                $jieshou_num = count($vv['daijieshou']);
                $jinxing_num = count($vv['jinxing']);
                
                if($vv['daijieshou_time']>0){
                     $pingjun_jieshou = round($vv['daijieshou_time']/$jieshou_num);
                }else{
                    $pingjun_jieshou =0.00;
                }
               
                $pingjun_jieshou .= "分钟";
                
               
                if($vv['jinxing_time']>0){
                     $pingjun_jinxing = round($vv['jinxing_time']/$jinxing_num,2);
                }else{
                     $pingjun_jinxing =0.00;
                }
              
                $pingjun_jinxing .= "分钟";
                
               
                
                $text .= "🆔【".$user_arr[$kv]['tgusername']."】\r\n✅待接(<a href='" . $this->jiqirenming . "?start=daijieshou_".$kv."'>".$jieshou_num." </a>)平均".$pingjun_jieshou."\r\n✅进行(<a href='" . $this->jiqirenming . "?start=jinxing_".$kv."'>".$jinxing_num." </a>) 平均".$pingjun_jinxing."\r\n\r\n";
            }
            
            $this->xiaoxi($text,$chatid);
        }
        if($message=="其他时间任务"){ 
            $start_time = date('Y-m-d 00:00:00');
            $end_time = date('Y-m-d H:i:s',strtotime("+1 day"));
            
            $messages = "请输入具体时间范围,格式如下：\r\n时间范围=".$start_time."#".$end_time."\r\n";
            $switch_inline_query_current_msg = "#jutideshijian\r\n时间范围=".$start_time."#".$end_time;
            $inline_keyboard_arr3[0] = array('text' => "马上添加一个试试 ", "switch_inline_query_current_chat" => $switch_inline_query_current_msg);
            $keyboard = [
                'inline_keyboard' => [
                    $inline_keyboard_arr3,
                ]
            ];
            $parameter = array(
                'chat_id' => $chatid,
                'parse_mode' => 'HTML',
                'text' => $messages,
                'reply_markup' => $keyboard,
                'disable_web_page_preview' => true,
    
            );
    
            $this->http_post_data('sendMessage', json_encode($parameter));
            exit();
        }
        if($message=="今天任务" || $message=="昨天任务" || strpos($message,'jutideshijian')){
            if($message=="今天任务"){
                $pp ="今日";
                $start_time = strtotime(date('Y-m-d'));
                $end_time = strtotime(date('Y-m-d',strtotime("+1 day")));
            }elseif($message=="昨天任务" ){
                $pp ="昨天";
                $start_time = strtotime(date('Y-m-d',strtotime("-1 day")));
                $end_time = strtotime(date('Y-m-d'));
              
            }else{
                $shij_arr =explode('jutideshijian',$message);
                $p_arr = explode("\n",$shij_arr[1]);
                $p2_arr = explode("=",$p_arr[1]);
                $sj = $p2_arr[1];
               
                $p2_arr = explode("#",$sj);
                $start_time = strtotime($p2_arr[0]);
                $end_time = strtotime($p2_arr[1]);
                
                $pp ="指定时间";
               
            }

            
            $renwu_sqlq2 = "select * FROM pay_jishurenwu where createtime between '".$start_time."' and '".$end_time."'"; 
            $renwu_query_q = $this->pdo->query($renwu_sqlq2);
            $renwu_all = $renwu_query_q->fetchAll();
            if(!$renwu_all){
               $text = "<b>📊".$pp."统计

✅".$pp."发布( 0 )


✅".$pp."完成( 0 )


✅总耗时统计:


🗂完成任务详情:</b>";
       
               $parameter = array(
                'chat_id' => $chatid,
                'parse_mode' => 'HTML',
                'text' => $text, 
            
            );

            $this->http_post_data('sendMessage', json_encode($parameter));

            exit();
          
            }
            
            
            $text = "<b>📊".$pp."统计</b>\r\n\r\n";
            $text .= "<b>📮".$pp."发布( ".count($renwu_all)." )</b>\r\n";
            $user_arr_renwu = array();
            $user_arr_renwup = array();
            $user_arr_renwu_wancheng = array();
            $now_time = time();
            $renwu_shijian = array();
            $renwu_shijian_all = array();
            $wancheng = 0;
            foreach ($renwu_all as $key=>$val){
                $user_arr_renwu_fabu[$val['jishuuser_id']] +=1;
                //查询这个任务涉及到那些人：
                $record_sql = "select * from pay_jishurecord where jishurenwu_id='".$val['id']."'";
                $record_query_q = $this->pdo->query($record_sql);
                $record_all2 = $record_query_q->fetchAll();
                foreach ($record_all2 as $vp=>$veq){
                   
                    //任务总共消耗的时间
                    $renwu_shijian_all[$veq['end_jishuuser_id']]['all_time'] += $veq['huafeitime'];
                    $renwu_shijian_all[$veq['end_jishuuser_id']]['jishurenwu_id'] =$veq['jishurenwu_id'];
                }
                
                
                if($val['status'] =="2"){
                    $wancheng +=1;
                    //查询这个任务涉及到那些人：
                    $record_sql = "select * from pay_jishurecord where jishurenwu_id='".$val['id']."'";
                    $record_query_q = $this->pdo->query($record_sql);
                    $record_all = $record_query_q->fetchAll();
                    foreach ($record_all as $vp=>$veq){
                         $user_arr_renwup[$val['pjishuuser_id']][$veq['jishurenwu_id']] = $val['name'];
                        
                        //这个任务牵涉到那些人：
                         $user_arr_renwu[$val['pjishuuser_id']][$veq['jishurenwu_id']] = $val['name'];
                         //这个任务下每个人花费多少时间
                         $user_arr_renwu[$val['pjishuuser_id']]['wancheng'] += $veq['huafeitime'];
                         //任务总共消耗的时间
                         $renwu_shijian[$veq['jishurenwu_id']]['all_time'] += $veq['huafeitime'];
                         $renwu_shijian[$veq['jishurenwu_id']]['name'] =$val['name'];
                    }
                }
            }
       
            //今日发布：
            foreach ($user_arr_renwu_fabu as $kf=>$vf){
                $text .="👤".$user_arr[$kf]['tgusername']."(<a href='".$this->jiqirenming."?start=faburenwuren_".$kf."_".$start_time."_".$end_time."'>".$vf."</a>)\r\n";
            }
            
            $text .= "\r\n✅<b>".$pp."完成( ".$wancheng. ")</b>\r\n";
            $pp_test = "";
            
            //$this->xiaoxi(json_encode($user_arr_renwu),$chatid);
            
            //完成数据统计：
            $renwu_sqlq3 = "select * FROM pay_jishurenwuwancheng where createtime between '".$start_time."' and '".$end_time."'"; 
           
            $wancheng_query_q = $this->pdo->query($renwu_sqlq3);
            $wancheng_all = $wancheng_query_q->fetchAll();
            
             
            $wancheng_user = array();
            $wancheng_user2 = array();
            foreach ($wancheng_all as $kw=>$vw){
                $wancheng_user[$vw['jishuuser_id']] +=$vw['huafeitime'];
                $wancheng_user2[$vw['jishuuser_id']][$vw['renwu_id']] +=1;
            }

            foreach ($wancheng_user as $p=>$v){
                 $jigrenwu = count($wancheng_user2[$p]);
                 $geren_time = ceil(($v/$jigrenwu));
                 $text .= "👤<a href='".$this->jiqirenming."?start=gerenxinxi_".$p."'>".$user_arr[$p]['tgusername']."</a>(<a href='".$this->jiqirenming."?start=jinriwancheng_".$p."'> ".$jigrenwu." </a>)完--平均".$geren_time."min\r\n";
       
            }
            $text .="\r\n📊<b>总耗时统计:</b>\r\n";
            foreach ($renwu_shijian_all as $sk=>$vk){
           
                 $text .="👤".$user_arr[$sk]['tgusername'].$vk['all_time']."min\r\n";
            }
            
            $text .= "\r\n🗂<b>完成任务详情:</b>\r\n";
            foreach ($renwu_shijian as $rk=>$vr){ 
                $text .= "<a href='" . $this->jiqirenming . "?start=xiangqingrenwuid_".$rk."'>".$vr['name']."</a>(".$vr['all_time']."min)\r\n";
            }
            
            $this->xiaoxi($text,$chatid);
        }
        if(strpos($message, '/tj') !== false){
            $info = "<b>请选择要统计的日期</b>";
            $inline_keyboard_arr3[0] = array('text' => "今天", "callback_data" => "chatongji_0");
            $inline_keyboard_arr3[1] = array('text' => "昨天", "callback_data" => "chatongji_1");
            $inline_keyboard_arr3[2] = array('text' => "时间段", "callback_data" => "chatongji_2");
             $keyboard = [
                    'inline_keyboard' => [
                        $inline_keyboard_arr3,
                    ]
                ];
            $parameter = array(
                'chat_id' => $chatid,
                'parse_mode' => 'HTML',
                'text' => $info,
                'reply_markup' => $keyboard,
                'disable_web_page_preview' => true,
            );

            $this->http_post_data('sendMessage', json_encode($parameter));

            exit();    
        }

        if (strpos($message, '#renwu_tianxie_shuoming_') !== false) {
            $info = explode("_", $message);
            
            //任务编号：
           
            $renwu_id_arr = explode("\n", $info[3]);
            $renwu_id =$renwu_id_arr[0]; 
             
             //查询这个任务是谁的：
            $renwu_sql = "select * FROM pay_jishurenwu where id='".$renwu_id."'";
            $renwu_query_q = $this->pdo->query($renwu_sql);
            $renwu_info_arr = $renwu_query_q->fetchAll();
            $renwu_info = $renwu_info_arr[0];
            
      
            
            if($renwu_info['pjishuuser_id'] != $now_user_id){
                
                
                
                $test = "@".$user_find[0]['tgname']." 这个任务你不是受理人，请勿填写转移说明！";
                 $parameter = array(
                    'chat_id' => $this->jilvqun_chat_id,
                    'parse_mode' => 'HTML',
                    'text' => $test,
                    'reply_to_message_id'=>$renwu_info['yuanshi_message_id']
                    
                );
               
                $this->http_post_data('sendMessage', json_encode($parameter));
                exit();
            }
            //pay_jishushuom
            //查询是否已经存在了：
              $shuom_sql2 = "select * FROM pay_jishushuom where renwu_id='".$renwu_id."' and user_id='".$now_user_id."'";
            $shuom_query_q2 = $this->pdo->query($shuom_sql2);
            $shuom_info_arr = $shuom_query_q2->fetchAll();
             if($shuom_info_arr){
                $test2 = "@".$user_find[0]['tgname']." 这个任务你已经填写了转移说明！请勿再次操作！直接点击选择你要转的人！";
                 $parameter = array(
                    'chat_id' => $this->jilvqun_chat_id,
                    'parse_mode' => 'HTML',
                    'text' => $test2,
                    'reply_to_message_id'=>$renwu_info['yuanshi_message_id']
                    
                );
               
                $this->http_post_data('sendMessage', json_encode($parameter));
                exit();
            }
            $set_sql_add9 = "insert into pay_jishushuom (user_id,renwu_id) values ('" . $now_user_id."','".$renwu_id."')";
                $this->pdo->exec($set_sql_add9); 
             $parameter2 = array(
                'chat_id' => $chatid,
                'message_id'=>$message_id,
            );
            $this->http_post_data('deleteMessage', json_encode($parameter2));
            
            
            
           
          
            $pp_arr = explode(":",$renwu_id_arr[1]);
             //说明：
            $pp_info = $pp_arr[1];
         
            //记录信息，将最新的任务责任调整为最新的人：
            if($media_group_id=="0" || empty($media_group_id)){
                 $set_sql2 = "update pay_jishurenwu set zhuanyu_type='1',photo_field_id='".$photo_field_id."',remark ='" . $pp_info . "' where id='" . $renwu_id . "'";
                 $this->pdo->exec($set_sql2); 
            }else{
                $set_sql2 = "update pay_jishurenwu set zhuanyu_type='2',photo_field_id='".$media_group_id."',remark ='" . $pp_info . "' where id='" . $renwu_id . "'";
                 $this->pdo->exec($set_sql2); 
                 
                $set_sql_add9 = "insert into pay_jishuzhuanyi (media_group_id,photo_field_id,renwu_id) values ('" . $media_group_id."','".$photo_field_id."','".$renwu_id."')";
                $this->pdo->exec($set_sql_add9); 
            }
           
                $parameter2 = array(
                    'chat_id' => $chatid,
                    'message_id'=>$message_id-1,
                );
                $this->http_post_data('deleteMessage', json_encode($parameter2));    
                sleep(2);
            if($renwu_info['zhuanyu_type']=="2"){
                $renwu_sql = "select * FROM pay_jishuzhuanyi where renwu_id='".$renwu_id."'";
                $renwu_query_q = $this->pdo->query($renwu_sql);
                $renwu_info_arr = $renwu_query_q->fetchAll();
                $a_all = count($renwu_info_arr);
                 
                for($i=0;$i<=$a_all+2;$i++){
                    $s = $i+1;
                     $parameter2 = array(
                        'chat_id' => $chatid,
                        'message_id'=>$message_id+$s, 
                    );
                     $this->http_post_data('deleteMessage', json_encode($parameter2)); 

               }
               
                   
            }  
           
           
        
           
             $renwu_sql2 = "select * FROM pay_jishuuser where id='".$renwu_info['pjishuuser_id']."'";
            $renwu_query_q2 = $this->pdo->query($renwu_sql2);
            $user_info_arr = $renwu_query_q2->fetchAll();
              $tgname =$user_info_arr[0]['tgname'] ;        
            //转移需要告诉用户需要转移谁？
            $info = "@".$tgname."<b> 请选择要转移的部门</b>\r\n\r\n";
            
            
            
           
            $keyp =0;
            $keyp2=0;
             //查看所有员工：
         $set_sqlq = "select * FROM pay_jishuuser";
         $user_query_q = $this->pdo->query($set_sqlq);
         $user_all = $user_query_q->fetchAll();
         $all_user = count($user_all);
         $yiban = $all_user/2;
             foreach ($user_all as $key=>$value){
                 $p = $keyp+1;
                 $info .= $p.":" .$value['name']."-->". $value['tgusername']."\r\n";
                 if($key<$yiban){
                       $inline_keyboard_arr3[$keyp] = array('text' => $p, "callback_data" => "chengzhuanyirenyuan_" . $value['id']."_".$renwu_id."_".$now_user_id);

                 }else{
                     
                    $inline_keyboard_arr4[$keyp2] = array('text' => $p, "callback_data" => "chengzhuanyirenyuan_" . $value['id']."_".$renwu_id."_".$now_user_id);
                    $keyp2++;
                 }
                 
                $keyp++;
             }
            
            $keyboard = [
                    'inline_keyboard' => [
                        $inline_keyboard_arr3,
                        $inline_keyboard_arr4
                    ]
                ];
            $parameter = array(
                'chat_id' => $chatid,
                'parse_mode' => 'HTML',
                'text' => $info,
                'reply_markup' => $keyboard,
                'disable_web_page_preview' => true,
                'reply_to_message_id'=>$renwu_info['yuanshi_message_id']
            );
            
          
            

            $this->http_post_data('sendMessage', json_encode($parameter));
            
            
            exit();
            
            
        }
        //开始：
        if (strpos($message, '/start') !== false) {
            //faburenwuren_8_1683043200_1683129600
            //查看发布：
            if(strpos($message, 'faburenwuren_') !== false ){
                $fabu_arr = explode("_",$message);
                $user_id = $fabu_arr[1];
                $start_time = $fabu_arr[2];
                $end_time = $fabu_arr[3];
                 $renwu_sqlq2 = "select * FROM pay_jishurenwu where createtime between '".$start_time."' and '".$end_time."' and jishuuser_id = '".$user_id."'"; 
      
                $renwu_query_q = $this->pdo->query($renwu_sqlq2);
                $renwu_all = $renwu_query_q->fetchAll();
                $text ="";
                
                //分类：
                $renwu_arr2 = array();
                 foreach ($renwu_all as $ke=>$ve){
                     $renwu_arr2[$ve['jishuuser_id']][] =array('id'=>$ve['id'],'name'=>$ve['name']);
                     
                     $rk = $ve['id'];
                     $text .= "🗂".$user_arr[$ve['jishuuser_id']]['tgusername']."-发布的任务:\r\n";
                     $text .= "<a href='" . $this->jiqirenming . "?start=xiangqingrenwuid_".$rk."'>".$ve['name']."</a>\r\n";
                    /*🗂皓泽-发布的任务:
RW050368575055 (https://t.me/testfaburenwu_bot?start=xiangqingrenwuid_192)*/
                }
                
              /* foreach ($renwu_arr2 as $ke=>$ve){
                    $rk = $ve['id'];
                     $text .= "🗂".$user_arr[$ve['jishuuser_id']]['tgusername']."-发布的任务:\r\n";
                     $text .= "<a href='" . $this->jiqirenming . "?start=xiangqingrenwuid_".$rk."'>".$ve['name']."</a>\r\n";
                }*/
                
                
                $this->xiaoxi($text,$chatid);
            }
            
            if(strpos($message, 'jinriwancheng_') !== false ){
                  $renwuid_arr = explode("_",$message);
                  $user_id = $renwuid_arr[1];
               
                  
                  $renwu_sqlq2 = "select * from pay_jishurenwuwancheng where jishuuser_id='".$user_id."' group by renwu_id";
                  
                  $renwu_query_q = $this->pdo->query($renwu_sqlq2);
                $renwu_all = $renwu_query_q->fetchAll();
            
                $set_sqlq1 = "select * FROM pay_jishuuser where id='".$user_id."'";
                $user_query_q2 = $this->pdo->query($set_sqlq1);
                $user_find = $user_query_q2->fetchAll();
                
                
                $test = "🆔".$user_find[0]['tgusername']."\r\n🗂完成任务详情:\r\n\r\n";
                foreach ($renwu_all as $ke=>$ve){
                    $set_sqlq12 = "select * FROM pay_jishurenwu where id='".$ve['renwu_id']."'";
                    $user_query_q22 = $this->pdo->query($set_sqlq12);
                    $user_find2 = $user_query_q22->fetchAll();
                    $renwu_name =$user_find2[0]['name']; 
                     $test .= "<a href='" . $this->jiqirenming . "?start=xiangqingrenwuid_".$ve['renwu_id']."'>".$renwu_name."</a>(".$ve['huafeitime']."min)\r\n";
                
                }
                $this->xiaoxi($test,$chatid);
                
             }
            if(strpos($message, 'gerenxinxi_') !== false ){
                 $renwuid_arr = explode("_",$message);
                 $user_id = $renwuid_arr[1];
                  $renwu_sqlq2 = "select * from pay_jishurecord where last_jishuuser_id='".$user_id."'";
                  $renwu_query_q = $this->pdo->query($renwu_sqlq2);
                $renwu_all = $renwu_query_q->fetchAll();
                /*
                🟡发布任务:5
🟡关闭任务:5
🟡转移任务:20

🟠接任务数:5
🟠接任务最长响应:25min
🟠接任务平均响应:8min

🔴完成任务:5
🔴完成任务最长时间:130min
🔴完成任务平均时间:5min
                
                */
                $fabu_num = 0;
                $guanbi_num = 0;
                $zhuanyi_num = 0;
                $jieshou_num = 0;
                $jieshou_time = 0;
                $jieshou_time_long = 0;
                foreach ($renwu_all as $k=>$v){
                    if($v['remark']=="初次发布任务"){
                        $fabu_num +=1;
                    }elseif($v['remark']=="接收任务"){
                        $jieshou_num +=1;
                        $jieshou_time +=$v['huafeitime'];
                        if($v['huafeitime']>$jieshou_time_long){
                            $jieshou_time_long = $v['huafeitime'];
                        }
                    }elseif($v['remark']=="完成任务"){
                        $guanbi_num +=1;
                    }else{
                        //转移任务
                        $zhuanyi_num +=1;
                    }
                }
                //查看完成记录信息：
                $wancheng_sql = "select * from pay_jishurenwuwancheng where jishuuser_id ='".$user_id."'";
                $wancheng_query_q = $this->pdo->query($wancheng_sql);
                $wancheng_all = $wancheng_query_q->fetchAll();
                $wancheng = count($wancheng_all);
                $all_wancheng =0;
                $zui_wan = 0;
                foreach ($wancheng_all as $ks=>$vw){
                    $all_wancheng +=$vw['huafeitime'];
                    if($vw['huafeitime']>$zui_wan){
                        $zui_wan = $vw['huafeitime'];
                    }
                }
                $pingjun_wancheng = 0;
               
                if($all_wancheng>0){
                    $pingjun_wancheng = $all_wancheng/$all_wancheng;
                    
                }
                
                $ping = $jieshou_time/$jieshou_num;
                $text = "🟡发布任务:".$fabu_num."
🟡关闭任务:".$guanbi_num."
🟡转移任务:".$zhuanyi_num."\r\n
🟠接任务数:".$jieshou_num."
🟠接任务最长响应:".$jieshou_time_long."min
🟠接任务平均响应:".$ping."min\r\n
🔴完成任务:".$wancheng."
🔴完成任务最长时间:".$zui_wan."min
🔴完成任务平均时间:".$ping."min";
                 
                $this->xiaoxi($text,$chatid);
            }
            if(strpos($message, 'xiangqingrenwuid_') !== false ){
                $renwuid_arr = explode("_",$message);
                $renwu_id = $renwuid_arr[1];
                $renwu_sqlq2 = "select * from pay_jishurenwu where id='".$renwu_id."'";
                  $renwu_query_q = $this->pdo->query($renwu_sqlq2);
                $renwu_all = $renwu_query_q->fetchAll();
                $renwu_info = $renwu_all[0];
                
                $renwu_record_sql = "select * FROM pay_jishurecord where jishurenwu_id='".$renwu_id."' order by id asc";
                $renwu_record_query_q = $this->pdo->query($renwu_record_sql);
                $renwu_record_info_arr = $renwu_record_query_q->fetchAll();
            
            
             $zerenren = $user_arr[$renwu_info['pjishuuser_id']]['tgusername'];
            $faburen = $user_arr[$renwu_info['jishuuser_id']]['tgusername'];
            $fabushijian = date('Y-m-d H:i:s',$renwu_info['createtime']);
            $have_time = ceil((time()-$renwu_info['createtime'])/60)>0?ceil((time()-$renwu_info['createtime'])/60):0;
            
            $info = "🧑‍🏫当前责任人:".$zerenren."
🆔任务编号: ".$renwu_info['name']."
🧑‍🏫发布人: ".$faburen."
⏰发布时间:".$fabushijian."
⌛️ 已发布:".$have_time."分钟\r\n\r\n
📘转移事件:\r\n";

  
            
            $zui_last_jishuuser_id = "";
            $zui_end_jishuuser_id = "";
            $zui_cretetime = "";
            $haoshi_info = "\r\n\r\n🕰总耗时统计\r\n";
            
            $people_haoshi = array();
            
            foreach ($renwu_record_info_arr as $k=>$v){
                $huafeitime = $v['huafeitime'];
                
                if($v['remark'] =="完成任务"){
                     $info .="🚉".$user_arr[$v['last_jishuuser_id']]['tgusername']."关闭任务(".$huafeitime."分钟后)\n\r";
                     continue;
                 }
                
                if($v['typelist']=="3"){
                     //发布任务
                     $info .="🚉".$user_arr[$v['last_jishuuser_id']]['tgusername']."初次发布任务\n\r";
                }elseif($v['typelist']=="0"){
                    
                     $info .="🚉".$user_arr[$v['last_jishuuser_id']]['tgusername']."接受任务(".$huafeitime."分钟后)\n\r";
                }elseif($v['typelist']=="1"){
                     $infop ="🚉".$user_arr[$v['last_jishuuser_id']]['tgusername']."-->".$user_arr[$v['end_jishuuser_id']]['tgusername'];
                     $info .= "<a href='".$this->jiqirenming."?start=zhuanyishuoming_" . $v['id'] . "'>" . $infop . "</a>转移任务(".$huafeitime."分钟后)\n\r";
                }else{
                   
                }
                if(array_key_exists($v['last_jishuuser_id'],$people_haoshi)){
                    $people_haoshi[$v['last_jishuuser_id']] += $huafeitime;
                }else{
                    $people_haoshi[$v['last_jishuuser_id']] = $huafeitime;
                }
               
         
             
               
               $zui_last_jishuuser_id = $v['last_jishuuser_id'];
               $zui_end_jishuuser_id = $v['end_jishuuser_id']; 
               $zui_cretetime = $v['createtime'];
            }
            foreach ($people_haoshi as $ku=>$vu){
                      $haoshi_info .="⏱".$user_arr[$ku]['tgusername'].$vu."分钟\r\n";
            }
            
            
            $info .=$haoshi_info;
            
            $info .= "\r\n<a href='".$renwu_info['linkurl']."'>快捷查看任务详情信息</a>";
            $this->xiaoxi($info,$chatid);
            
            }
            //查看用户待接收的任务信息
            if (strpos($message, 'daijieshou_') !== false || strpos($message, 'jinxing_') !== false) {
                $user_arr = explode("_",$message);
                $user_id = $user_arr[1];
                $set_sqlq1 = "select * FROM pay_jishuuser where id='".$user_id."'";
                $user_query_q2 = $this->pdo->query($set_sqlq1);
                $user_find = $user_query_q2->fetchAll();
                $user_info = $user_find[0];
                
                 if (strpos($message, 'jinxing_') !== false) {
                     //查看用户进行中的任务信息：
                      $renwu_sqlq2 = "select * FROM pay_jishurenwu where pjishuuser_id='".$user_id."' and typelist='1'"; 
                }else{
                    //待接收：
                     $renwu_sqlq2 = "select * FROM pay_jishurenwu where pjishuuser_id='".$user_id."' and typelist='2'"; 
                }
                
                $renwu_query_q = $this->pdo->query($renwu_sqlq2);
                $renwu_all = $renwu_query_q->fetchAll();
                 if (strpos($message, 'jinxing_') !== false) {
                        $text = "@".$user_info['tgname']." ⚛️共收到".count($renwu_all)."个任务，请尽快处理，切勿超时\r\n\r\n";
                 }else{
                      $text = "@".$user_info['tgname']." ⚛️共收到".count($renwu_all)."个任务，请尽快领取，切勿超时\r\n\r\n";
                 }
             
                foreach ($renwu_all as $ke=>$v){
                    $guoqu = ceil((time()-$v['createtime'])/60);
                    $text .= "⏰(".$guoqu."分钟前收到)🔍\r\n";
                    $text .="<a href='".$v['linkurl']."'>".$v['linkurl']."</a>\r\n";
                }
                $this->xiaoxi($text,$chatid);
            }
            if (strpos($message, 'zhuanyishuoming_') !== false) {
                   //说明这个转移描述：
                   $zhuan_arr = explode("_",$message);
                   $record_id = $zhuan_arr[1];
                   $sql_info = "select * from pay_jishurecord where id ='" . $record_id . "'";
                   $record_query2 = $this->pdo->query($sql_info);
                   $record_info_arr = $record_query2->fetchAll();
                   $record_info = $record_info_arr[0];
                   
                
                   if($record_info['photo_field_id']=="0" || empty($record_info['photo_field_id'])){
                        $info = "转移说明：".$record_info['remark'];
                   
                        $parameter = array(
                            'chat_id' => $chatid,
                            'parse_mode' => 'HTML',
                            'text' =>"<b>". $info."</b>",
                        );
                        $this->http_post_data('sendMessage', json_encode($parameter));
                        exit();
                   }else{
                        $info = "转移说明：".$record_info['remark'];
                        
                        if (strpos($record_info['photo_field_id'], ',') !== false) {
                            $pase = explode(",",$record_info['photo_field_id']);
                            
                            foreach ($pase as $kqa=>$cvaq){
                                $parameter = array(
                                    'chat_id' => $chatid,
                                    'parse_mode' => 'HTML',
                                    'photo' => $cvaq,
                                );
                                $this->http_post_data('sendPhoto', json_encode($parameter));
                             
                            }
                            $parameter = array(
                                'chat_id' => $chatid,
                                'parse_mode' => 'HTML',
                                'text' =>"<b>". $info."</b>",
                            );
                            $this->http_post_data('sendMessage', json_encode($parameter));
                            exit();
                        }else{
                            $parameter = array(
                                'chat_id' => $chatid,
                                'parse_mode' => 'HTML',
                                'photo' => $record_info['photo_field_id'],
                                'caption' =>"<b>".$info."</b>",
                            );
                            $this->http_post_data('sendPhoto', json_encode($parameter));
                            exit();
                        }
                        
                        
                   }    
                   
                  
                   
            }
            
            if($chatid != $this->neibu_chat_id){
                $this->xiaoxi("别瞎搞事情！",$chatid);
            }
            
            $this->start($chatid);
            
           
        }
        
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





    public function callback($data)
    {

        $text = $data['callback_query']['data'];
        $chat_id = $data['callback_query']['message']['chat']['id'];
        $from_id = $data['callback_query']['from']['id'];
        $from_username = $data['callback_query']['from']['username'];
        
        $set_sqlq1 = "select * FROM pay_jishuuser where tgname='".$from_username."'";
        $user_query_q2 = $this->pdo->query($set_sqlq1);
        $user_find = $user_query_q2->fetchAll();
        $now_user_id = $user_find[0]['id'];
        
        $userid = $from_id;
        $message_id = $data['callback_query']['message']['message_id'];
 
        $chatid = $chat_id;

        $username = $data['message']['from']['username'];//用户名称
         //查看所有员工：
         $set_sqlq = "select * FROM pay_jishuuser";
         $user_query_q = $this->pdo->query($set_sqlq);
         $user_all = $user_query_q->fetchAll();
        
         $user_arr = array();
         foreach ($user_all as $key=>$value){
             $user_arr[$value['id']] = array('tgusername'=>$value['tgusername'],'name'=>$value['name'],'tgname'=>$value['tgname']); 
          } 
    
        //今日统计：
        if(strpos($text,"chatongji_")!== false){
            $pp_arr = explode("_",$text);
            if($pp_arr[1]=="0"){
                $start_time = strtotime(date('Y-m-d'));
                $end_time = strtotime(date('Y-m-d','+1 day'));
            }
            
           $renwu_sqlq = "select * FROM pay_jishurenwu where status ='2' and createtime>='".$start_time."'and createtime <='".$end_time."";
            $renwu_query_q = $this->pdo->query($renwu_sqlq);
            $renwu_all = $renwu_query_q->fetchAll();
        }
    
        //接受任务：
        if(strpos($text,"jieshourenwu_")!== false){
           // $this->xiaoxi("123",$this->jilvqun_chat_id);
            $renwu_arr = explode("_",$text);
            $renwu_id= $renwu_arr[1];
            $renwu_sql = "select * FROM pay_jishurenwu where id='".$renwu_id."'";
            $renwu_query_q = $this->pdo->query($renwu_sql);
            $renwu_info_arr = $renwu_query_q->fetchAll();
            $renwu_info = $renwu_info_arr[0];
            
            if($now_user_id != $renwu_info['pjishuuser_id']){
                $test = "@".$user_find[0]['tgname']." 这个任务没有划分给你,你不需要接收此任务";
                $parameter = array(
                    'chat_id' => $this->jilvqun_chat_id,
                    'parse_mode' => 'HTML',
                    'text' => $test,
                 
                    'reply_to_message_id'=>$renwu_info['yuanshi_message_id']
                    
                );
               
                $this->http_post_data('sendMessage', json_encode($parameter));
                $parameter = array(
                    'callback_query_id' => $data['callback_query']['id'],
                    'text' => "",
                );
                $this->http_post_data('answerCallbackQuery', json_encode($parameter));
                exit();
           
                 
            }
            $renwu_record_sql_o = "select * FROM pay_jishurecord where jishurenwu_id='".$renwu_id."' order by id desc limit 1";
            $renwu_record_query_qo = $this->pdo->query($renwu_record_sql_o);
            $renwu_record_info_arr2 = $renwu_record_query_qo->fetchAll();
            $renwu_record_info2 = $renwu_record_info_arr2[0];
            $zui_last_jishuuser_id = $renwu_record_info2['last_jishuuser_id'];
            $zui_end_jishuuser_id = $renwu_record_info2['end_jishuuser_id'];
            $zui_cretetime = $renwu_record_info2['createtime'];
              
           //记录信息，将最新的任务责任调整为最新的人：
           $set_sql2 = "update pay_jishurenwu set typelist='1',status='1',pjishuuser_id ='" . $now_user_id . "' where id='" . $renwu_id . "'";
           $this->pdo->exec($set_sql2); 
           //添加记录日志：
           $yong_time = ceil((time()-$zui_cretetime)/60);
           $set_sql_add2 = "insert into pay_jishurecord (jishurenwu_id,typelist,last_jishuuser_id,end_jishuuser_id,createtime,huafeitime,remark) values ('" . $renwu_id . "','".'0'."','" . $zui_end_jishuuser_id . "','" . $now_user_id . "','" . time() . "',$yong_time,'接收任务')";
           $order_info_add = $this->pdo->exec($set_sql_add2);
              
            $zerenren = $user_arr[$renwu_info['pjishuuser_id']]['tgusername'];
            $faburen = $user_arr[$renwu_info['jishuuser_id']]['tgusername'];
            $fabushijian = date('Y-m-d H:i:s',$renwu_info['createtime']);
            $have_time = ceil((time()-$renwu_info['createtime'])/60)>0?ceil((time()-$renwu_info['createtime'])/60):0;
            
            //查看记录日志：
            $renwu_record_sql = "select * FROM pay_jishurecord where jishurenwu_id='".$renwu_id."' order by id asc";
            $renwu_record_query_q = $this->pdo->query($renwu_record_sql);
            $renwu_record_info_arr = $renwu_record_query_q->fetchAll();
            
            $info = "🧑‍🏫当前责任人:".$zerenren."
🆔任务编号: ".$renwu_info['name']."
🧑‍🏫发布人: ".$faburen."
⏰发布时间:".$fabushijian."
⌛️ 已发布:".$have_time."分钟\r\n\r\n
📘转移事件:\r\n";

        
        /*皓泽接受任务(1分钟后)
        🚉皓泽→小北 (https://g.com/) (1分钟后)
        🚉小北接受任务 (5分钟后)
        🚉小北→皓泽 (10分钟后)
        🚉皓泽关闭任务(30分钟后)*/
        
        /*🕰总耗时统计
        ⏱皓泽:2分钟
        ⏱小北:3分钟
        ⏱zelly:20分钟";*/
            
            $zui_last_jishuuser_id = "";
            $zui_end_jishuuser_id = "";
            $zui_cretetime = "";
            $haoshi_info = "\r\n\r\n🕰总耗时统计\r\n";
            
            $people_haoshi = array();
            
            foreach ($renwu_record_info_arr as $k=>$v){
                $huafeitime = $v['huafeitime'];
                if($v['typelist']=="3"){
                     //发布任务
                     $info .="🚉".$user_arr[$v['last_jishuuser_id']]['tgusername']."初次发布任务\n\r";
                }elseif($v['typelist']=="0"){
                     $info .="🚉".$user_arr[$v['last_jishuuser_id']]['tgusername']."接受任务(".$huafeitime."分钟后)\n\r";
                }elseif($v['typelist']=="1"){
                     $infop ="🚉".$user_arr[$v['last_jishuuser_id']]['tgusername']."-->".$user_arr[$v['end_jishuuser_id']]['tgusername'];
                     $info .= "<a href='".$this->jiqirenming."?start=zhuanyishuoming_" . $v['id'] . "'>" . $infop . "</a>转移任务(".$huafeitime."分钟后)\n\r";
                }else{
                     $info .="🚉".$user_arr[$v['last_jishuuser_id']]['tgusername']."关闭任务(".$huafeitime."分钟后)\n\r";
                }
                if(array_key_exists($v['last_jishuuser_id'],$people_haoshi)){
                    $people_haoshi[$v['last_jishuuser_id']] += $huafeitime;
                }else{
                    $people_haoshi[$v['last_jishuuser_id']] = $huafeitime;
                }
               
         
             
               
               $zui_last_jishuuser_id = $v['last_jishuuser_id'];
               $zui_end_jishuuser_id = $v['end_jishuuser_id']; 
               $zui_cretetime = $v['createtime'];
            }
            foreach ($people_haoshi as $ku=>$vu){
                      $haoshi_info .="⏱".$user_arr[$ku]['tgusername'].$vu."分钟\r\n";
            }
           // $this->xiaoxinoend(json_encode($people_haoshi),$chatid);
            
            $new_info = $info.$haoshi_info;

           $inline_keyboard_arr3[0] = array('text' => "转移任务", "callback_data" => "zhuanyirenyuan_" . $renwu_id);
           $inline_keyboard_arr3[1] = array('text' => "关闭任务", "callback_data" => "guanbirenwu_" . $renwu_id);

             $keyboard = [
                    'inline_keyboard' => [
                        $inline_keyboard_arr3,
                    ]
                ];
            $parameter = array(
                'chat_id' => $this->jilvqun_chat_id,
                'parse_mode' => 'HTML',
                'text' => $new_info,
                'reply_markup' => $keyboard, 
                'disable_web_page_preview' => true,
                'reply_to_message_id'=>$renwu_info['yuanshi_message_id']
                
            );
            $parameter2 = array(
                'chat_id' => $this->jilvqun_chat_id,
                'message_id'=>$message_id,
            );
          
            $this->http_post_data('sendMessage', json_encode($parameter));
            
            $this->http_post_data('deleteMessage', json_encode($parameter2));
        
            
        }
        //关闭任务：
        if (strpos($text, 'guanbirenwu_') !== false) {
            $renwu_arr = explode("_",$text);
            $renwu_id= $renwu_arr[1];
            
            $renwu_sql = "select * FROM pay_jishurenwu where id='".$renwu_id."'";
            $renwu_query_q = $this->pdo->query($renwu_sql);
            $renwu_info_arr = $renwu_query_q->fetchAll();
            $renwu_info = $renwu_info_arr[0];
            
            
            if($now_user_id != $renwu_info['pjishuuser_id']){
                $text = "@".$user_find[0]['tgname']." 这个任务是由：".$user_arr[$renwu_info['pjishuuser_id']]['tgusername']."负责的！你无权关闭！";
                
                  $parameter = array(
                    'chat_id' => $this->jilvqun_chat_id,
                    'parse_mode' => 'HTML',
                    'text' => $text,
                    'reply_to_message_id'=>$renwu_info['yuanshi_message_id']
                );
               
                $this->http_post_data('sendMessage', json_encode($parameter));
                $parameter = array(
                    'callback_query_id' => $data['callback_query']['id'],
                    'text' => "",
                );
                $this->http_post_data('answerCallbackQuery', json_encode($parameter));
                exit();
                

            }
            
            if($now_user_id != $renwu_info['jishuuser_id']){
                $text = "@".$user_find[0]['tgname']." 这个任务是由：".$user_arr[$renwu_info['jishuuser_id']]['tgusername']."创建的！你无权关闭！";
                  $parameter = array(
                    'chat_id' => $this->jilvqun_chat_id,
                    'parse_mode' => 'HTML',
                    'text' => $text,
                    'reply_to_message_id'=>$renwu_info['yuanshi_message_id']
                );
                $this->http_post_data('sendMessage', json_encode($parameter));
                $parameter = array(
                    'callback_query_id' => $data['callback_query']['id'],
                    'text' => "",
                );
                $this->http_post_data('answerCallbackQuery', json_encode($parameter));
                exit();
            }
            
            
            //找到最新的一条操作日志数据信息
            $renwu_record_sql = "select * FROM pay_jishurecord where jishurenwu_id='".$renwu_id."' order by id desc";
            $renwu_record_query_q = $this->pdo->query($renwu_record_sql);
            $renwu_record_info_arr = $renwu_record_query_q->fetchAll();
            $renwu_record_info = $renwu_record_info_arr[0];   
            
             //添加记录日志：
           $zui_cretetime = $renwu_record_info['createtime'];
           $yong_time = ceil((time()-$zui_cretetime)/60); 
           $zui_end_jishuuser_id = $renwu_record_info['end_jishuuser_id'];
            
            //记录信息，将最新的任务责任调整为最新的人：
           $set_sql2 = "update pay_jishurenwu set status='2',pjishuuser_id ='" . $now_user_id . "' where id='" . $renwu_id . "'";
           $this->pdo->exec($set_sql2); 
           //添加记录日志：
           $yong_time = ceil((time()-$zui_cretetime)/60);
           $set_sql_add2 = "insert into pay_jishurecord (jishurenwu_id,typelist,last_jishuuser_id,end_jishuuser_id,createtime,huafeitime,remark) values ('" . $renwu_id . "','".'0'."','" . $zui_end_jishuuser_id . "','" . $now_user_id . "','" . time() . "',$yong_time,'完成任务')";
             
           $order_info_add = $this->pdo->exec($set_sql_add2);
           $renwu_name = $renwu_info['name'];
           $message_pindao_id=$renwu_info['message_id'];
           
            $parameter4 = array(
                'chat_id' => $chatid,
                'message_id'=>$message_id,
            );
            $this->http_post_data('deleteMessage', json_encode($parameter4));
            
            
            for($i=0;$i<$renwu_info['have_num'];$i++){
                $parameter2 = array(
                    'chat_id' => $this->pindaochatid,
                    'message_id'=>$renwu_info['message_id']+$i,
                );
                $this->http_post_data('deleteMessage', json_encode($parameter2));
            }

            
            
            //记录这个任务总共消耗多少人力：
            $renwu_record_sql2 = "select * FROM pay_jishurecord where jishurenwu_id='".$renwu_id."' order by id desc";
            $renwu_record_query_q2 = $this->pdo->query($renwu_record_sql2);
            $renwu_record_info_arr2 = $renwu_record_query_q2->fetchAll();
            $user_renwu_arr = array();
            foreach($renwu_record_info_arr2 as $kev=>$vev){
                $user_renwu_arr[$vev['end_jishuuser_id']] +=$vev['huafeitime'];
            }
            
            foreach ($user_renwu_arr as $wk=>$vw){
                 $set_sql_add2 = "insert into pay_jishurenwuwancheng (renwu_id,jishuuser_id,huafeitime,createtime) values ('" . $renwu_id . "','".$wk."','" . $vw . "','" . time() . "')"; 
                 $this->pdo->exec($set_sql_add2);
            }
            
            
             $parameter1 = array(
                'chat_id' => $chatid,
                'message_id'=>$message_id,
            );
            $this->http_post_data('deleteMessage', json_encode($parameter1)); 
      
            
            
            $parameter2 = array(
                'chat_id' => $this->pindaochatid,
                'message_id'=>$message_pindao_id,
            );
            $this->http_post_data('deleteMessage', json_encode($parameter2));
            
       
            
             /*if($renwu_info['type']=="2"){ 
                 $parameter3 = array(
                    'chat_id'=>$this->wanchengchatid,
                    'photo'=>$renwu_info['phone_id'],
                    'caption'=>$renwu_info['content']
                );
                $this->http_post_data('sendPhoto', json_encode($parameter3));
                
              
                
            }else{
                 $parameter3 = array(
                    'chat_id'=>$this->wanchengchatid,
                    'text'=>$renwu_info['content']
                );
                $this->http_post_data('sendMessage', json_encode($parameter3));
            }*/
        
        }
        
        //转移任务---实际操作：
        if(strpos($text, 'chengzhuanyirenyuan_') !== false){
            $renwu_arr = explode("_",$text);
            $chuliren_id= $renwu_arr[1];
            $renwu_id= $renwu_arr[2];
            $dianjiren_user= $renwu_arr[3];
           
            
            $renwu_sql = "select * FROM pay_jishurenwu where id='".$renwu_id."'";
            $renwu_query_q = $this->pdo->query($renwu_sql);
            $renwu_info_arr = $renwu_query_q->fetchAll();
            $renwu_info = $renwu_info_arr[0];
            
            $renwu_sql2 = "select * FROM pay_jishuuser where id='".$now_user_id."'";
            $renwu_query_q2 = $this->pdo->query($renwu_sql2);
            $renwu_info_arr2 = $renwu_query_q2->fetchAll();
            
            if($dianjiren_user != $now_user_id){
                $text = "@".$renwu_info_arr2[0]['tgname']." 不要给我瞎点！没有at你！";
                
                  $parameter = array(
                    'chat_id' => $this->jilvqun_chat_id,
                    'parse_mode' => 'HTML',
                    'text' => $text,
                    'reply_to_message_id'=>$renwu_info['yuanshi_message_id']
                    
                );
               
                $this->http_post_data('sendMessage', json_encode($parameter));
                $parameter = array(
                    'callback_query_id' => $data['callback_query']['id'],
                    'text' => "",
                );
                $this->http_post_data('answerCallbackQuery', json_encode($parameter));

                exit();
            }
            
            
            
            
            if($now_user_id != $renwu_info['pjishuuser_id']){
                
                
                $text = "@".$renwu_info_arr2[0]['tgname']." 这个任务当前的处理人：".$user_arr[$renwu_info['pjishuuser_id']]['tgusername']."！你无权转移任务！";
                
                  $parameter = array(
                    'chat_id' => $this->jilvqun_chat_id,
                    'parse_mode' => 'HTML',
                    'text' => $text,
                 
                    'reply_to_message_id'=>$renwu_info['yuanshi_message_id']
                    
                );
               
                $this->http_post_data('sendMessage', json_encode($parameter));
                $parameter = array(
                    'callback_query_id' => $data['callback_query']['id'],
                    'text' => "",
                );
                $this->http_post_data('answerCallbackQuery', json_encode($parameter));

                exit();
                
                
            }
            
           if($now_user_id ==$chuliren_id){
                 $text = "@".$renwu_info_arr2[0]['tgname']." 不可以将任务转移给自己！";
                
                  $parameter = array(
                    'chat_id' => $this->jilvqun_chat_id,
                    'parse_mode' => 'HTML', 
                    'text' => $text,
                 
                    'reply_to_message_id'=>$renwu_info['yuanshi_message_id']
                    
                );
               
                $this->http_post_data('sendMessage', json_encode($parameter));
                $parameter = array(
                    'callback_query_id' => $data['callback_query']['id'],
                    'text' => "",
                );
                $this->http_post_data('answerCallbackQuery', json_encode($parameter));

                exit();
             }
             
            $del_sql9 = "DELETE FROM pay_jishushuom where user_id='".$now_user_id."' and renwu_id='".$renwu_id."'";
            $this->pdo->exec($del_sql9);
             
            
            //找到最新的一条操作日志数据信息
            $renwu_record_sql = "select * FROM pay_jishurecord where jishurenwu_id='".$renwu_id."' order by id desc";
            $renwu_record_query_q = $this->pdo->query($renwu_record_sql);
            $renwu_record_info_arr = $renwu_record_query_q->fetchAll();
            $renwu_record_info = $renwu_record_info_arr[0];   
            
            
            
              //记录信息，将最新的任务责任调整为最新的人：
           $set_sql2 = "update pay_jishurenwu set typelist='2',pjishuuser_id ='" . $chuliren_id . "' where id='" . $renwu_id . "'";
           $this->pdo->exec($set_sql2);  
           //添加记录日志：
           $zui_cretetime = $renwu_record_info['createtime'];
           $yong_time = ceil((time()-$zui_cretetime)/60); 
           $zui_end_jishuuser_id = $renwu_record_info['end_jishuuser_id'];
           
           $remark = $renwu_info['remark'];
          
           if($renwu_info['zhuanyu_type']=="2"){
               $media_group_id = $renwu_info['photo_field_id'];
               $renwu_sql = "select * FROM pay_jishuzhuanyi where renwu_id='".$renwu_id."' and media_group_id='".$media_group_id."'";
                $renwu_query_q = $this->pdo->query($renwu_sql);
                $renwu_info_arr = $renwu_query_q->fetchAll();
                $photo_field_ids = "";
                foreach ($renwu_info_arr as $ks=>$vs){
                    $photo_field_ids .=$vs['photo_field_id'].",";
                }
                $photo_field_id = substr($photo_field_ids,0,-1);
                
           }else{
                $photo_field_id = $renwu_info['photo_field_id'];
           }
           
           
           $set_sql_add2 = "insert into pay_jishurecord (jishurenwu_id,typelist,last_jishuuser_id,end_jishuuser_id,createtime,huafeitime,remark,photo_field_id) values ('" . $renwu_id . "','".'1'."','" . $zui_end_jishuuser_id . "','" . $chuliren_id . "','" . time() . "',$yong_time,'".$remark."','".$photo_field_id."')";
          $this->pdo->exec($set_sql_add2);  
          
          
          $del_sql5 = "DELETE FROM pay_jishuzhuanyi where renwu_id='".$renwu_id."' and media_group_id='".$media_group_id."'";
          $this->pdo->exec($del_sql5);
          /*
          @xxxx 收到新任务，请及时处理，超时没有接受任务将计入绩效考核评分。

            当前任务负责人:小北
            按钮1:接受任务
            按钮2:关闭任务
          */
          $zhuanyi_sql = "select * from pay_jishuuser where id='".$chuliren_id."'";
          $zhuanyi_query_q = $this->pdo->query($zhuanyi_sql);
          $zhuanyi_info_arr = $zhuanyi_query_q->fetchAll();
          $zhuanyi_info  = $zhuanyi_info_arr[0];
          $msg = "@".$zhuanyi_info['tgname']." 收到新任务\r\n任务编号：".$renwu_info['name']."\r\n请及时处理，超时没有接受任务将计入绩效考核评分.\r\n\r\n当前任务负责人:".$zhuanyi_info['tgusername']."\r\n";
          
          $inline_keyboard_arr3[0] = array('text' => "接受任务", "callback_data" => "jieshourenwu_" . $renwu_id);
          $inline_keyboard_arr3[1] = array('text' => "关闭任务", "callback_data" => "guanbirenwu_" . $renwu_id);
            
             
        
             $keyboard = [
                    'inline_keyboard' => [
                        $inline_keyboard_arr3,
                    ]
                ];
            $parameter = array(
                'chat_id' => $chatid,
                'parse_mode' => 'HTML',
                'text' => $msg,
                'reply_markup' => $keyboard,
                'disable_web_page_preview' => true,
                'reply_to_message_id'=>$renwu_info['yuanshi_message_id']
            );
            $parameter2 = array(
                'chat_id' => $chatid,
                'message_id'=>$message_id,
            );
            $this->http_post_data('deleteMessage', json_encode($parameter2));
            
             //告诉最新的人，这个任务：
            $this->lairenwule($zhuanyi_info['tgname']);
            
            $this->http_post_data('sendMessage', json_encode($parameter));
            exit();
        }
        
         //转移任务前夕
        if (strpos($text, 'zhuanyirenyuan_') !== false) { 
            $renwu_arr = explode("_",$text);
            $renwu_id= $renwu_arr[1];
            $renwu_sql = "select * FROM pay_jishurenwu where id='".$renwu_id."'";
            $renwu_query_q = $this->pdo->query($renwu_sql);
            $renwu_info_arr = $renwu_query_q->fetchAll();
            $renwu_info = $renwu_info_arr[0];
            $renwu_sql2 = "select * FROM pay_jishuuser where id='".$now_user_id."'";
            $renwu_query_q2 = $this->pdo->query($renwu_sql2);
            $renwu_info_arr2 = $renwu_query_q2->fetchAll();
            
            if($now_user_id != $renwu_info['pjishuuser_id']){
               
                
                $text = "@".$renwu_info_arr2[0]['tgname']." 这个任务当前的处理人：".$user_arr[$renwu_info['pjishuuser_id']]['tgusername']."！你无权转移任务！";
                
                  $parameter = array(
                    'chat_id' => $this->jilvqun_chat_id,
                    'parse_mode' => 'HTML',
                    'text' => $text,
                 
                    'reply_to_message_id'=>$renwu_info['yuanshi_message_id']
                    
                );
               
                $this->http_post_data('sendMessage', json_encode($parameter));
                $parameter = array(
                    'callback_query_id' => $data['callback_query']['id'],
                    'text' => "",
                );
                $this->http_post_data('answerCallbackQuery', json_encode($parameter));

                exit();
                
                
            }
                
                $messages ="@".$renwu_info_arr2[0]['tgname']." \r\n";
                $messages .= "请填写转移说明(必填)\r\n请先复制下方文字后,将xxxx修改为你需要的转移任务说明清晰\r\n\r\n";
                $messages .= "`#renwu_tianxie_shuoming_".$renwu_id."\r\n必要说明:xxxx`";
            

                $parameter = array(
                    'chat_id' => $chatid,
                    'parse_mode' => 'MarkDown',
                    'text' => $messages,
         
                    'reply_to_message_id'=>$renwu_info['yuanshi_message_id']

                );

                $this->http_post_data('sendMessage', json_encode($parameter));

                //pay_jishushuom
                
              
                $parameter2 = array(
                    'chat_id' => $chatid,
                    'message_id'=>$message_id,
                );
                $this->http_post_data('deleteMessage', json_encode($parameter2));
            
        }
    


        $parameter = array(
            'callback_query_id' => $data['callback_query']['id'],
            'text' => "",
        );
        $this->http_post_data('answerCallbackQuery', json_encode($parameter));
        exit();

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


    //系统后台：
    public function start($chatid)
    {
        $keyboard2 = [
            'keyboard' => [
                [
                    ['text' => '当前任务'],
                    ['text' => '今天任务'],
                    ['text' => '昨天任务']
                   
                ],
                [
                   
                    ['text' => '其他时间任务'],
                    ['text'=>"清空所有数据"]
                ],
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


        $parameter = array(
            'chat_id' => $chatid,
            'text' => "你好:" . "欢迎使用本系统后台！",
            'reply_markup' => $encodedKeyboard2
        );
        //设置当前用户进入后台：


        //发送消息

        $this->http_post_data('sendMessage', json_encode($parameter));
        exit();

    }
    

    public function quanxian($chatid, $userid, $quanxian, $username)
    {
        $username = "@" . $username;
        if (!in_array($userid, $this->chaojiyonghu)) {

            $set_sql1 = "select * FROM pay_zuren where typelist ='2' and username='" . $username . "'";
            $order_query2 = $this->pdo->query($set_sql1);
            $order_info2 = $order_query2->fetchAll();
            if (!$order_info2) {
                $parameter = array(
                    'chat_id' => $chatid,
                    'parse_mode' => 'HTML',
                    //'text' => "你没有当前   <b>" . $quanxian . "</b>   操作此命令,请联系晴朗@QingLang1688添加权限",
                    'text' => "你没有当前在权限用户组内,请联系晴朗@QingLang1688添加权限",
                );
                $this->http_post_data('sendMessage', json_encode($parameter));
                exit();
            }

            $set_sql2 = "select * FROM pay_yonghuzu where typelist ='1' and id='" . $order_info2[0]['yonghuzu_id'] . "'";
            $order_query3 = $this->pdo->query($set_sql2);
            $order_info3 = $order_query3->fetchAll();

            if (empty($order_info3[0]['mingling'])) {
                $parameter = array(
                    'chat_id' => $chatid,
                    'parse_mode' => 'HTML',
                    'text' => "当前用户组没有此项权限,请联系晴朗@QingLang1688添加",
                );
                $this->http_post_data('sendMessage', json_encode($parameter));
                exit();
            }
            $all_mingling_arr = explode(",", $order_info3[0]['mingling']);
            if (!in_array($quanxian, $all_mingling_arr)) {
                $parameter = array(
                    'chat_id' => $chatid,
                    'parse_mode' => 'HTML',
                    'text' => "你没有当前   <b>" . $quanxian . "</b>   操作此命令,请联系晴朗@QingLang1688添加",
                );
                $this->http_post_data('sendMessage', json_encode($parameter));
                exit();
            }

        }


    }


}

$oen = new five();
$oen->index();

?>
