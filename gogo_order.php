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
    
  /*  private $token = '5313902856:AAEIQRhZIH6DOc2itLEig_D9ojdtOCkiAgY';  //token
    private $ownerAddress = "TVTEdbTeBaTQjXccezvsqtNDbRb3zjJhb9";*/
    private $link = "";
     private $token = "";
    private $ownerAddress = "";
    private $telegram;
    private $pdo;

    public function __construct()
    {
        
        
         include "cron_jiqi.php";
         
        $this->token =$token;
        $this->ownerAddress = $ownerAddress;
        $token = $this->token;
        $this->link = 'https://api.telegram.org/bot' . $token . '';


        /*$dbHost = "127.0.0.1";
        $dbName = "tianshi_com";
        $dbUser = "tianshi_com";
        $dbPassword = "8jzy3yhwGD6GNXi8";*/
        /*$dbHost = "127.0.0.1";
        $dbName = "chpay";
        $dbUser = "chpay";
        $dbPassword = "RpyZXiK4DLSscRTk";*/

        $this->pdo = new PDO("mysql:host=" . $dbHost . ";dbname=" . $dbName, $dbUser, $dbPassword, array(PDO::ATTR_PERSISTENT => true));


    }


    public function index()
    {

       /* $now_time = date("Y-m-d H-i-s", time() - 3 * 60 * 60);
        $end_time = date("Y-m-d H-i-s", time());
//        $find_sql = "SELECT type,channel,money,status from pay_order where  addtime between '".$now_time ."' and '". $end_time."'";
        $find_sql = "SELECT channel from pay_order where  addtime between '" . $now_time . "' and '" . $end_time . "' group by channel";

        $qss = $this->pdo->query($find_sql);
        $rsss = $qss->fetchAll();
        $find_channel = array();
        foreach ($rsss as $row) {
            $find_channel[] = $row['channel'];
        }
        $str_channel = implode(",", $find_channel);


        $channel = [];
        $sql1 = "SELECT id,name FROM pay_channel WHERE id in (" . $str_channel . ")";
        $q = $this->pdo->query($sql1);
        $rs = $q->fetchAll();

        foreach ($rs as $row) {
            $channel[$row['id']] = $row['name'];
        }


        $messages = "";
        foreach ($channel as $ke => $vsq) {
            //查询最近的订单
            //15-30-40-50-60
            $usets = array(60, 50, 40, 30, 20, 1);  
            $counts = count($usets);
            for ($i = 0; $i < $counts; $i++) {

                $find_sql = "select * from (select * from (SELECT * FROM `pay_order` where channel = '" . $ke . "' order by trade_no desc ) as A limit " . $usets[$i] . ") as B WHERE status='0'";
                $rs_T = $this->pdo->query($find_sql);
                $row_t = $rs_T->fetchAll();

                if (count($row_t) >= $usets[$i]) {
                    $messages .= $ke . "-" . $vsq . ": " . $usets[$i] . " 单未支付\r\n";
                }
            }


            if (!empty($messages)) {
                $messages .= "\r\n请留意" . " @fu_008  @ZiKLX  @yiyi0530";

                //发消息到指定群里去:
                $parameter = array(
                    'chat_id' => "-637823644",
                    'parse_mode' => 'HTML',
                    'text' => $messages
                );
                $this->http_post_data('sendMessage', json_encode($parameter));

            }
        }*/

        $find_sql2 = "SELECT * from pay_userpayorder ";

        $qss2 = $this->pdo->query($find_sql2);
        $rsss2 = $qss2->fetchAll();
 
        foreach ($rsss2 as $key2 => $value2) {
            $now_time = date("Y-m-d H-i-s", time() - $value2['jiansuotime'] * 60);
            $end_time = date("Y-m-d H-i-s", time());
            
            if($value2['uid'] == "0000"){
                 $find_sql = "SELECT channel from pay_order where  addtime between '" . $now_time . "' and '" . $end_time . "' group by channel";
            }else{
                $find_sql = "SELECT channel from pay_order where uid ='" . $value2['uid'] . "' and addtime between '" . $now_time . "' and '" . $end_time . "' group by channel";
            }
            
           /* var_dump($find_sql);
            echo "<br/>";*/


            $qss = $this->pdo->query($find_sql);
            $rsss = $qss->fetchAll();
            $find_channel = array();
            foreach ($rsss as $row) {
                $find_channel[] = $row['channel'];
            }
            $str_channel = implode(",", $find_channel);
                

            $channel = [];
            $sql1 = "SELECT id,name FROM pay_channel WHERE id in (" . $str_channel . ")";
           
            $q = $this->pdo->query($sql1);
            $rs = $q->fetchAll();

            foreach ($rs as $row) {
                $channel[$row['id']] = $row['name'];
            }

 
            $messages = "";
            $all_key = array();
            $all_ke_str = array();
            foreach ($channel as $ke => $vsq) {
                //查询最近的订单
                //15-30-40-50-60
                $finds = $value2['dingdanshu'];
                $usets = explode(",", $finds);
                rsort($usets);
                //$usets =array(60,50,40,30,20,10);
                $counts = count($usets);
               // var_dump($usets);
                

                for ($i = 0; $i < $counts; $i++) {

                    if($value2['uid'] == "0000"){
                       $find_sql = "select * from (select * from (SELECT * FROM `pay_order` where channel = '" . $ke . "' order by trade_no desc ) as A limit " . $usets[$i] . ") as B WHERE status='0' and addtime between '" . $now_time . "' and '" . $end_time."'"; 
                    }else{
                        $find_sql = "select * from (select * from (SELECT * FROM `pay_order` where channel = '" . $ke . "' and uid='" . $value2['uid'] . "' order by trade_no desc ) as A limit " . $usets[$i] . ") as B WHERE status='0' and addtime between '" . $now_time . "' and '" . $end_time."'"; 
                    }
                   echo "<br/>";
                    var_dump($find_sql);
                    echo "<br/>"; 
                    
                    $rs_T = $this->pdo->query($find_sql);
                    $row_t = $rs_T->fetchAll();
                    echo "<br/>";
                    var_dump($find_sql);
                    echo "<br/>"; 
                    var_dump(count($row_t)); 
                     echo "<br/>";
                    if (count($row_t) >= $usets[$i]) {
                       // echo $find_sql."==>突破了：".$usets[$i]."===>".count($row_t)."<br>";
                         if(!in_array($ke,$all_key)){
                             $all_key[] = $ke;
                            // $messages .= "编号ID：".$ke."-名称".$vsq."连续出现: ".$usets[$i]." 单未支付！\r\n";
                       
                              $all_ke_str[$ke] = $ke . "-" . $vsq . ": " . count($row_t) . " 单未支付\r\n";
                        }
                      
                        
                          
                    }
                    //  var_dump($find_sql);
                    //  echo "<br>";
                }


                // exit();
                //查询当前用户有没有使用这个通道过：
               
               
            }
             
             if (count($all_key) > 0) {
                    $str_key = implode(",", $all_key);
                    $find_user_sql = "select * from pay_userjiange where uid='" . $value2['uid'] . "' and keid in(" . $str_key . ")";
                    $rs_T_U = $this->pdo->query($find_user_sql);
                    $row_t_u = $rs_T_U->fetchAll();
                    echo "<br>";
                   var_dump($all_ke_str);
                   
                    $have_tongzhi = array();
                    if($row_t_u){
                        foreach ($row_t_u as $k3 => $v3) {
                        $have_tongzhi[] = $v3['keid'];
                        $jiangetime = $value2['jiangetime'] * 60; //间隔多少秒才通知
                      
                            if ($v3['createtime'] + $jiangetime > time()) {
                                //移除通知：
                                unset($all_ke_str[$v3['keid']]);

                            }else{
                               $this->pdo->exec("UPDATE pay_userjiange SET createtime='" . time() . "' WHERE uid='" . $value2['uid'] . "'");

                            }
                        }
                    }
                    
                           var_dump($all_ke_str);
                    
                    //差异的 记录到表中去
                    //$have_tongzhi = array(121,122,123); 
                    $result_chayi = array();
                    if(count($have_tongzhi)>count($all_key)){ 
                        for($k=0;$k<count($have_tongzhi);$k++){
                            //for($ks=0;$ks<count($all_key);$ks++){
                                if(!in_array($have_tongzhi[$k],$all_key)){
                                    $result_chayi[] = $have_tongzhi[$k];
                                }
                            //}
                            
                        }
                         for($k=0;$k<count($all_key);$k++){
                              if(!in_array($all_key[$k],$have_tongzhi)){
                                 if(!in_array($all_key[$k],$result_chayi)){
                                        $result_chayi[] = $all_key[$k];
                                    }
                              }
                         }
                    }else{
                        for($k=0;$k<count($all_key);$k++){
                            //for($ks=0;$ks<count($all_key);$ks++){
                                if(!in_array($all_key[$k],$have_tongzhi)){
                                    $result_chayi[] = $all_key[$k];
                                }
                            //}
                            
                        }
                        for($k=0;$k<count($have_tongzhi);$k++){
                            if(!in_array($have_tongzhi[$k],$all_key)){
                                if(!in_array($have_tongzhi[$k],$result_chayi)){
                                    $result_chayi[] = $have_tongzhi[$k];
                                }
                            }
                             
                         }
                    }
                   
                    //$result_chayi = array_diff($have_tongzhi, $all_key);
                    $result_chayi_count = count($result_chayi);
                    //  var_dump($result_chayi);
                    //  exit();
                    if ($result_chayi_count > 0) {
                        for ($i = 0; $i < $result_chayi_count; $i++) {
                            $this->pdo->exec("INSERT INTO `pay_userjiange` (`uid`, `keid`,`createtime`) VALUES ('" . $value2['uid'] . "', '" . $result_chayi[$i] . "', '" . time() . "')");
                        }
                    }

                    if (count($all_ke_str) > 0) {
                        $messages .= implode("\n", $all_ke_str);
                    }


                }
           
            if (!empty($messages)) {
                $messages .= "\r\n请留意" . " " . $value2['tuisong'];

                $inline_keyboard_arr2[0] = array('text' => "通知人", "callback_data" => "fanhuiuser_people_" . $value2['uid']);
                $inline_keyboard_arr2[1] = array('text' => "通知单数", "callback_data" => "fanhuiuser_danshu_" . $value2['uid']);
                $inline_keyboard_arr2[2] = array('text' => "时间范围", "callback_data" => "fanhuiuser_fanwei_" . $value2['uid']);
                $inline_keyboard_arr2[3] = array('text' => "通知间隔", "callback_data" => "fanhuiuser_jiange_" . $value2['uid']);
                $keyboard = [
                    'inline_keyboard' => [
                        $inline_keyboard_arr2
                    ]
                ];
                $parameter = array(
                    'chat_id' => $value2['chat_id'],
                    'parse_mode' => 'HTML',
                    'text' => $messages,
                    'reply_markup' => $keyboard,
                    'disable_web_page_preview' => true
                );


                $this->http_post_data('sendMessage', json_encode($parameter));
            }


        }


        return true;


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


}

$oen = new five();
$oen->index();

?>
