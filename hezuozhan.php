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
    private $token = '';  //token
    private $link = "";

    private $jiqirenminghezuo;
    private $pdo;
    private $pdo2;

    public function __construct()
    {

        include "cron_jiqi.php";

        $this->link = 'https://api.telegram.org/bot' . $token_hezuo . '';

        $this->jiqirenminghezuo = $jiqirenminghezuo;
        $this->pdo = new PDO("mysql:host=" . $dbHost . ";dbname=" . $dbName, $dbUser, $dbPassword, array(PDO::ATTR_PERSISTENT => true));
        /*
        154.202.59.93
        sql_154_202_59_9
        WdBcPxdcCG4YeFmj
        */

        // $this->pdo2 = new PDO("mysql:host=" . $dbHost2 . ";dbname=" . $dbName2, $dbUser2, $dbPassword2, array(PDO::ATTR_PERSISTENT => true));



    }


    public function index()
    {


        $data = json_decode(file_get_contents('php://input'), TRUE); //读取json并对其格式化

        $sql = "insert into pay_jiqi (content) values ('" . json_encode($data) . "')";
        $this->pdo->exec($sql);

        if ($data['callback_query']) {
            $this->callback($data);
        } else {
            $chatid = $data['message']['chat']['id'];//获取chatid
            $message = $data['message']['text'];//获取message
            $userid = $data['message']['from']['id'];//获取message
            $username =$data['message']['from']['username'];//用户名称

            $this->message($message, $chatid, $userid, $data,$username);

        }


    }

    function getlastMonthDays($date){
        $timestamp=strtotime($date);
        $firstday=date('Y-m-01',strtotime(date('Y',$timestamp).'-'.(date('m',$timestamp)-1).'-01'));
        $lastday_one=date('Y-m-d',strtotime("$firstday +1 month -1 day"));
        $lastday_two=date('Y-m-d',strtotime("$firstday +2 month -1 day"));
        return array($lastday_one,$lastday_two);
    }

    public function message($message, $chatid, $userid, $data,$username)
    {
        //添加数据库配置信息：
        if (strpos($message, '#tianjia_peizhi_#') !== false) {
            $roll_arr = explode("#tianjia_peizhi_#", $message);

            //查看支付商是否已经存在：

            $changes = explode("\n", trim($roll_arr[1]));

            if (count($changes) != 5) {
                $parameter = array(
                    'chat_id' => $chatid,
                    'text' => "参数不全,请核对后再添加！" . json_encode($changes),
                );
                $this->http_post_data('sendMessage', json_encode($parameter));
                exit();
            }
            $add_zhangzhang_arr = explode("==", trim($changes[0]));
            $add_zhangzhang = $add_zhangzhang_arr[1];


            $add_sjkip_arr = explode("==", trim($changes[1]));
            $add_sjkip = $add_sjkip_arr[1];

            $add_sjkname_arr = explode("==", trim($changes[2]));
            $add_sjkname = $add_sjkname_arr[1];

            $add_sjkroot_arr = explode("==", trim($changes[3]));
            $add_sjkroot = $add_sjkroot_arr[1];

            $add_sjkpass_arr = explode("==", trim($changes[4]));
            $add_sjkpass = $add_sjkpass_arr[1];

            $createtime = date("Y-m-d H:i:s",time());

            $set_sql = "insert into pay_hezuodb (tgurl,chat_id,dbhost,dbname,dbuser,dbpass,createtime) values ('" . $add_zhangzhang . "','". $chatid . "','" . $add_sjkip . "','" . $add_sjkname . "','" . $add_sjkroot . "','" . $add_sjkpass . "','" . $createtime . "')";



            $chang_status = $this->pdo->exec($set_sql);
            if ($chang_status) {
                $msg = "添加配置信息成功!";
            } else {
                $msg = "添加配置信息失败!";
            }
            $parameter = array(
                'chat_id' => $chatid,
                'parse_mode' => 'HTML',
                'text' => $msg
            );

            $this->http_post_data('sendMessage', json_encode($parameter));
            exit();


        }
        if (strpos($message, '/tjyh') !== false) {

            $set_sql1 = "select * FROM pay_hezuodb where chat_id = '".$chatid."'";
            $order_query2 = $this->pdo->query($set_sql1);
            $order_info2 = $order_query2->fetchAll();

            if($order_info2){


                $msg = "<b>当前群配置详情：</b>\r\n";

                foreach ($order_info2 as $key => $ve) {
                    $msg .= "群chat_id:".$ve['chat_id']."\r\n";
                    $msg .= "群主TG:".$ve['tgurl']."\r\n";
                    $msg .= "数据库名远程IP:".$ve['dbhost']."\r\n";
                    $msg .= "数据库名:".$ve['dbname']."\r\n";
                    $msg .= "数据库登陆账号:".$ve['dbuser']."\r\n";
                    $msg .= "数据库登陆密码:".$ve['dbpass']."\r\n";
                    $inline_keyboard_arr4[$key] = array('text' => "删除", "callback_data" => "shanchudb###" . $ve['id']);
                }

                $keyboard = [
                    'inline_keyboard' => [
                        $inline_keyboard_arr4,
                    ]
                ];

                $parameter = array(
                    "chat_id" => $chatid,
                    "text" => $msg,
                    "parse_mode" => "HTML",
                    'reply_markup' => $keyboard,
                    'disable_web_page_preview' => true,

                );
                $this->http_post_data('sendMessage', json_encode($parameter));
                exit();
            }



            $messages = "未查询配置信息\r\n";
            $switch_inline_query_current_msg = "#tianjia_peizhi_#\r\n群主TG==@chengu123\r\n数据库名远程IP==127.0.0.1\r\n数据库名==pay_jilv\r\n数据库登陆账号==root\r\n数据库登陆密码==123456";
            $inline_keyboard_arr3[0] = array('text' => "马上添加 ", "switch_inline_query_current_chat" => $switch_inline_query_current_msg);
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






            $parameter = array(
                "chat_id" => $chatid,
                "text" => $message,
                "parse_mode" => "HTML",
            );
            $this->http_post_data('sendMessage', json_encode($parameter));
            exit();

        }





        if (strpos($message, '广告管理') !== false) {
            $this->guanlian($chatid,$message);


            $set_sql1 = "select * FROM pay_hezuoname where chat_id ='".$chatid."' group by name";

            $order_query2 = $this->pdo->query($set_sql1);
            $order_info2 = $order_query2->fetchAll();
            if(!$order_info2){

                $parameter = array(
                    'chat_id' => $chatid,
                    'parse_mode' => 'HTML',
                    'text' => "查询异常"
                );

                $this->http_post_data('sendMessage', json_encode($parameter));
                exit();
            }

            $zhan_id = $order_info2[0]['id'];


            $set_sql1 = "select * FROM pay_hezuoname where id ='".$zhan_id."'";
            $order_query2 = $this->pdo->query($set_sql1);
            $order_info2 = $order_query2->fetchAll();

            $set_sql2 = "select * FROM pay_hezuodh where hezuoname_id ='".$zhan_id."'";
            $order_query3 = $this->pdo->query($set_sql2);
            $order_info3 = $order_query3->fetchAll();



            if(!$order_info3){

                $messages = "未查询到站点下的导航信息\r\n";
                $switch_inline_query_current_msg = "#tianjia_daohang_#".$zhan_id."###_#\r\n导航名称==制服导航\r\n广告标题==极品xx内射\r\n标志(唯一性)==101\r\n站长链接==www.zfp10.buzz\r\n链接地址==www.baidu.com?channel=101&type=1\r\n续费时间==2022-11-9\r\n位置==视频区第三个\r\n导航站长tg账号==@chengu123\r\n邮箱==ceshi@gmail.com\r\n金额==100U\r\n备注==靠谱导航";
                $inline_keyboard_arr3[0] = array('text' => "马上添加 ", "switch_inline_query_current_chat" => $switch_inline_query_current_msg);
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
            }else{
                $messages = "";
                foreach ($order_info3 as $kqa=>$vesq){

                    $messages .= ($kqa+1).":  <b><a href='https://t.me/".$this->jiqirenminghezuo."?start=daohang_detail" . $vesq['id'] . "'>" . $vesq['channel']."--".$vesq['name'] . "</a></b>  <b><a href='https://t.me/".$this->jiqirenminghezuo."?start=deletedaohang" . $vesq['id'] . "'>删除</a></b>\r\n";
                }
                $switch_inline_query_current_msg = "#tianjia_daohang_#".$zhan_id."###_#\r\n导航名称==制服导航\r\n广告标题==极品xx内射\r\n标志(唯一性)==101\r\n站长链接==www.zfp10.buzz\r\n链接地址==www.baidu.com?channel=101&type=1\r\n续费时间==2022-11-9\r\n位置==视频区第三个\r\n导航站长tg账号==@chengu123\r\n邮箱==ceshi@gmail.com\r\n金额==100U\r\n备注==靠谱导航";
                $inline_keyboard_arr3[0] = array('text' => "继续添加 ", "switch_inline_query_current_chat" => $switch_inline_query_current_msg);
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





            $parameter = array(
                "chat_id" => $chatid,
                "text" => $message,
                "parse_mode" => "HTML",
            );
            $this->http_post_data('sendMessage', json_encode($parameter));
            exit();

        }
        //今日转化
        if (strpos($message, '今日转化') !== false) {

            $this->guanlian($chatid,$message);

            $set_sql1 = "select title,channel,name FROM pay_hezuodh where chat_id = '".$chatid."' group by channel";

            $order_query2 = $this->pdo->query($set_sql1);
            $order_info2 = $order_query2->fetchAll();
            if(!$order_info2){
                $parameter = array(
                    "chat_id" => $chatid,
                    "text" => "未查询到当前群绑定配置的广告信息",
                    "parse_mode" => "HTML",
                );
                $this->http_post_data('sendMessage', json_encode($parameter));
                exit();
            }

            $message = "今日转化\r\n\r\n";

            foreach($order_info2 as $ke=>$ve){
                //注意:【2/60/345】，其中2是成功支付的，60是拉单数，345是流量数，转化率=成功订单/总流量
                $link_time = strtotime(date("Y-m-d"));
                $visit_sql ="select ip from pay_jilvvisit where channel='".$ve['channel']."' and createtime='".$link_time."' group by ip";

                $this->peizhidb($chatid);

                $order_visit = $this->pdo2->query($visit_sql);
                $visit_info = $order_visit->fetchAll();


                //所有的IP数据：
                $all_ip_arr = array();
                foreach ($visit_info as $k1=>$v1){
                    $all_ip_arr[] = $v1['ip'];
                }

                $all_liuliang = count($all_ip_arr); //流量数



                $chunk_result = array_chunk($all_ip_arr, 10);



                $all_ladan =0;      //拉单数
                $all_zhifu =0;      //成功支付数
                $all_price =0;      //成功支付数
                for($i=0;$i<count($chunk_result);$i++){
                    $ip_str = "";
                    for($j=0;$j<count($chunk_result[$i]);$j++){
                        $ip_str .= "'".$chunk_result[$i][$j]."',";
                    }
                    $ip_str = substr($ip_str,0,-1);
                    $visit_sql_o ="select status,price from pay_order_ip where ip in (".$ip_str.") and createtime='".$link_time."'";


                    $order_visit_o = $this->pdo->query($visit_sql_o);
                    $visit_info_o = $order_visit_o->fetchAll();






                    if($visit_info_o){
                        $all_ladan+=count($visit_info_o);
                        foreach ($visit_info_o as $klq=>$klw){
                            if($klw['status'] =="1"){
                                $all_zhifu += 1;
                                $all_price += $klw['price'];
                            }
                        }
                    }
                }
                if($all_zhifu>0){
                    $zhuanhualv = (sprintf("%.2f",($all_zhifu/$all_ladan)*100))."%";
                }else{
                    $zhuanhualv = "0%";
                }

                /*
                ✅黑鲨导航
                ♒️转化率：2%【2/60/353】
                💰收入:30元
                */

                $message .= "✅".$ve['channel']."--".$ve['name']."\r\n";
                $message .= "♒️转化率". $zhuanhualv."【".$all_zhifu."/".$all_ladan."/".$all_liuliang."】\r\n";
                $message .= "✅收入".$all_price."元\r\n\r\n";

            }




            $parameter = array(
                "chat_id" => $chatid,
                "text" => $message,
                "parse_mode" => "HTML",
            );
            $this->http_post_data('sendMessage', json_encode($parameter));
            exit();

        }
        //昨日转化
        if (strpos($message, '昨日转化') !== false) {
            $this->guanlian($chatid,$message);
            $set_sql1 = "select title,name,channel FROM pay_hezuodh where chat_id = '".$chatid."' group by channel";

            $order_query2 = $this->pdo->query($set_sql1);
            $order_info2 = $order_query2->fetchAll();
            if(!$order_info2){
                $parameter = array(
                    "chat_id" => $chatid,
                    "text" => "未查询到当前群绑定配置的广告信息",
                    "parse_mode" => "HTML",
                );
                $this->http_post_data('sendMessage', json_encode($parameter));
                exit();
            }


            $message = "昨日转化：\r\n\r\n";
            foreach($order_info2 as $ke=>$ve){
                //注意:【2/60/345】，其中2是成功支付的，60是拉单数，345是流量数，转化率=成功订单/总流量
                $link_time = strtotime(date("Y-m-d",strtotime("-1 day")));
                $visit_sql ="select ip from pay_jilvvisit where channel='".$ve['channel']."' and createtime='".$link_time."' group by ip";

                $this->peizhidb($chatid);
                $order_visit = $this->pdo2->query($visit_sql);
                $visit_info = $order_visit->fetchAll();



                //所有的IP数据：
                $all_ip_arr = array();
                foreach ($visit_info as $k1=>$v1){
                    $all_ip_arr[] = $v1['ip'];
                }

                $all_liuliang = count($all_ip_arr); //流量数


                $chunk_result = array_chunk($all_ip_arr, 10);

                $all_ladan =0;      //拉单数
                $all_zhifu =0;      //成功支付数
                $all_price =0;      //成功支付数
                for($i=0;$i<count($chunk_result);$i++){
                    $ip_str = "";
                    for($j=0;$j<count($chunk_result[$i]);$j++){
                        $ip_str .= "'".$chunk_result[$i][$j]."',";
                    }
                    $ip_str = substr($ip_str,0,-1);
                    $visit_sql_o ="select status,price from pay_order_ip where ip in (".$ip_str.") and createtime ='".$link_time."'";



                    $order_visit_o = $this->pdo->query($visit_sql_o);
                    $visit_info_o = $order_visit_o->fetchAll();
                    if($visit_info_o){
                        $all_ladan+=count($visit_info_o);
                        foreach ($visit_info_o as $klq=>$klw){
                            if($klw['status'] =="1"){
                                $all_zhifu += 1;
                                $all_price += $klw['price'];
                            }
                        }
                    }
                }
                if($all_zhifu>0){
                    $zhuanhualv = (sprintf("%.2f",($all_zhifu/$all_ladan)*100))."%";
                }else{
                    $zhuanhualv = "0%";
                }

                /*
                ✅黑鲨导航
                ♒️转化率：2%【2/60/353】
                💰收入:30元
                */

                $message .= "✅".$ve['channel']."--".$ve['name']."\r\n";
                $message .= "♒️转化率". $zhuanhualv."【".$all_zhifu."/".$all_ladan."/".$all_liuliang."】\r\n";
                $message .= "✅收入".$all_price."元\r\n\r\n";

            }


            $parameter = array(
                "chat_id" => $chatid,
                "text" => $message,
                "parse_mode" => "HTML",
            );
            $this->http_post_data('sendMessage', json_encode($parameter));
            exit();

        }

        //转化统计【默认查询第一周的】
        if (strpos($message, '转化统计') !== false) {
            $this->guanlian($chatid,$message);
            $set_sql1 = "select title,channel,name FROM pay_hezuodh where chat_id = '".$chatid."' group by title";

            $order_query2 = $this->pdo->query($set_sql1);
            $order_info2 = $order_query2->fetchAll();
            if(!$order_info2){
                $parameter = array(
                    "chat_id" => $chatid,
                    "text" => "未查询到当前群绑定配置的广告信息",
                    "parse_mode" => "HTML",
                );
                $this->http_post_data('sendMessage', json_encode($parameter));
                exit();
            }
            $year = date('m');
            $month = date('m');

            //当前月的第一天
            $now_month_last_day = strtotime(date('Y-m-1'));
            //当前月的最后一天
            $now_month_end_day = strtotime(date('Y-m-d',strtotime(date('Y-m-1',strtotime('next month').'-1 day'))));

            //上月的第一天
            $last_month_last_day = strtotime(date('Y-m-1',strtotime('last month')));
            $last_month_end_day = strtotime(date('Y-m-d',strtotime(date('Y-m-1').'-1 day')));

            $now_month = date('Y年m月',$now_month_last_day);
            $last_month = date('Y年m月',$last_month_last_day);
            $msg_one = "当前月_".$now_month;
            $msg_two = "当前月_".$last_month;
            $inline_keyboard_arr = array();
            $inline_keyboard_arr[0] = array('text' => "最近二周", "callback_data" => "最近两周");
            $inline_keyboard_arr[1] = array('text' => "最近一月", "callback_data" => "最近一月");
            $inline_keyboard_arr[2] = array('text' => $now_month, "callback_data" => $msg_one);
            $inline_keyboard_arr[3] = array('text' => $last_month, "callback_data" => $msg_two);

            $message = "最近一周转化：\r\n\r\n";
            foreach($order_info2 as $ke=>$ve){

                //注意:【2/60/345】，其中2是成功支付的，60是拉单数，345是流量数，转化率=成功订单/总流量
                $link_time_start= strtotime(date("Y-m-d",strtotime("-7 day")));
                $link_time_end = strtotime(date("Y-m-d"));

                $visit_sql ="select ip from pay_jilvvisit where channel='".$ve['channel']."' and createtime BETWEEN  '".$link_time_start."' and '".$link_time_end."' group by ip";

                $this->peizhidb($chatid);
                $order_visit = $this->pdo2->query($visit_sql);
                $visit_info = $order_visit->fetchAll();
                //所有的IP数据：
                $all_ip_arr = array();
                foreach ($visit_info as $k1=>$v1){
                    $all_ip_arr[] = $v1['ip'];
                }

                $all_liuliang = count($all_ip_arr); //流量数



                $chunk_result = array_chunk($all_ip_arr, 10);

                $all_ladan =0;      //拉单数
                $all_zhifu =0;      //成功支付数
                $all_price =0;      //成功支付数
                for($i=0;$i<count($chunk_result);$i++){
                    $ip_str = "";
                    for($j=0;$j<count($chunk_result[$i]);$j++){
                        $ip_str .= "'".$chunk_result[$i][$j]."',";
                    }
                    $ip_str = substr($ip_str,0,-1);
                    $visit_sql_o ="select status,price from pay_order_ip where ip in (".$ip_str.") and createtime BETWEEN '".$link_time_start."' and '".$link_time_end."'";

                    $order_visit_o = $this->pdo->query($visit_sql_o);
                    $visit_info_o = $order_visit_o->fetchAll();
                    if($visit_info_o){
                        $all_ladan+=count($visit_info_o);
                        foreach ($visit_info_o as $klq=>$klw){
                            if($klw['status'] =="1"){
                                $all_zhifu += 1;
                                $all_price += $klw['price'];
                            }
                        }
                    }
                }
                if($all_zhifu>0){
                    $zhuanhualv = (sprintf("%.2f",($all_zhifu/$all_ladan)*100))."%";
                }else{
                    $zhuanhualv = "0%";
                }

                /*
                ✅黑鲨导航
                ♒️转化率：2%【2/60/353】
                💰收入:30元
                */

                $message .= "✅".$ve['channel']."--".$ve['name']."\r\n";
                $message .= "♒️转化率". $zhuanhualv."【".$all_zhifu."/".$all_ladan."/".$all_liuliang."】\r\n";
                $message .= "✅收入".$all_price."元\r\n\r\n";

            }



            $keyboard = [
                'inline_keyboard' => [
                    $inline_keyboard_arr,

                ]
            ];
            $parameter = array(
                "chat_id" => $chatid,
                "text" => $message,
                "parse_mode" => "HTML",
                'reply_markup' => $keyboard,
                'disable_web_page_preview' => true,
            );
            $this->http_post_data('sendMessage', json_encode($parameter));
            exit();

        }



        //添加站点：
        if (strpos($message, '#tianjia_zhandian_#') !== false) {
            $roll_arr = explode("#tianjia_zhandian_#", $message);
            //查看支付商是否已经存在：

            $changes = explode("\n", trim($roll_arr[1]));
            if (count($changes) != 5) {
                $parameter = array(
                    'chat_id' => $chatid,
                    'text' => "参数不全,请核对后再添加！" . json_encode($changes),
                );
                $this->http_post_data('sendMessage', json_encode($parameter));
                exit();
            }
            $add_name_arr = explode("=", trim($changes[0]));
            $add_name = $add_name_arr[1];

            $set_sql1 = "select * FROM pay_hezuoname where name ='".$add_name."'";
            $order_query2 = $this->pdo->query($set_sql1);
            $order_info2 = $order_query2->fetchAll();
            if($order_info2){
                $parameter = array(
                    'chat_id' => $chatid,
                    'text' => "当前站点已经存在!禁止重复添加！",
                );
                $this->http_post_data('sendMessage', json_encode($parameter));
                exit();
            }

            $add_yuming_arr = explode("=", trim($changes[1]));
            $add_yuming = $add_yuming_arr[1];

            $add_fuwuqi_arr = explode("=", trim($changes[2]));
            $add_fuwuqi = $add_fuwuqi_arr[1];

            $add_baota_arr = explode("=", trim($changes[3]));
            $add_baota = $add_baota_arr[1];

            $add_beizhu_arr = explode("=", trim($changes[4]));
            $add_beizhu = $add_beizhu_arr[1];

            $set_sql = "insert into pay_hezuoname (name,linkurl,rooturl,baota,remarks,chat_id) values ('" . $add_name . "','" . $add_yuming . "','" . $add_fuwuqi . "','" . $add_baota . "','" . $add_beizhu . "','" . $chatid . "')";
            $chang_status = $this->pdo->exec($set_sql);
            if ($chang_status) {
                $msg = "添加站点成功!";
            } else {
                $msg = "添加站点失败!";
            }
            $parameter = array(
                'chat_id' => $chatid,
                'parse_mode' => 'HTML',
                'text' => $msg
            );

            $this->http_post_data('sendMessage', json_encode($parameter));
            exit();


        }

        //添加站点下的导航：
        if (strpos($message, '#tianjia_daohang_#') !== false) {


            $roll_arr = explode("#tianjia_daohang_#", $message);
            //查看支付商是否已经存在：
            $roll_arr2 =explode("###_#", $roll_arr[1]);

            $zhandian_id = $roll_arr2[0];



            $changes = explode("\n", trim($roll_arr2[1]));
            if (count($changes) != 11) {
                $parameter = array(
                    'chat_id' => $chatid,
                    'text' => "参数不全,请核对后再添加！" . json_encode($changes),
                );
                $this->http_post_data('sendMessage', json_encode($parameter));
                exit();
            }
            $add_name_arr = explode("==", trim($changes[0]));
            $add_name = $add_name_arr[1];

            $add_title_arr = explode("==", trim($changes[1]));
            $add_title = $add_title_arr[1];

            $add_channel_arr = explode("==", trim($changes[2]));
            $add_channel = $add_channel_arr[1];

            $add_daohangurl_arr = explode("==", trim($changes[3]));
            $add_daohang = $add_daohangurl_arr[1];

            $add_linkurl_arr = explode("==", trim($changes[4]));
            $add_linkurl = $add_linkurl_arr[1];

            $add_starttime_arr = explode("==", trim($changes[5]));
            $add_starttime = $add_starttime_arr[1];

            $add_weizhi_arr = explode("==", trim($changes[6]));
            $add_weizhi = $add_weizhi_arr[1];

            $add_tgurl_arr = explode("==", trim($changes[7]));
            $add_tgurl = $add_tgurl_arr[1];

            $add_email_arr = explode("==", trim($changes[8]));
            $add_email = $add_email_arr[1];

            $add_price_arr = explode("==", trim($changes[9]));
            $add_price = $add_price_arr[1];

            $add_remarks_arr = explode("==", trim($changes[10]));
            $add_remarks = $add_remarks_arr[1];

            $set_sql = "insert into pay_hezuodh (hezuoname_id,name,title,daohangurl,linkurl,starttime,channel,tgurl,email,price,remarks,weizhi,chat_id) values ('" . $zhandian_id . "','" . $add_name ."','" . $add_title . "','" . $add_daohang . "','" . $add_linkurl . "','" . $add_starttime . "','" . $add_channel . "','" . $add_tgurl . "','" . $add_email . "','" . $add_price . "','" . $add_remarks . "','" . $add_weizhi ."','" . $chatid . "')";
            $chang_status = $this->pdo->exec($set_sql);
            if ($chang_status) {
                $msg = "添加导航下广告成功!";
            } else {
                $msg = "添加导航下广告失败!";
            }
            $parameter = array(
                'chat_id' => $chatid,
                'parse_mode' => 'HTML',
                'text' => $msg
            );

            $this->http_post_data('sendMessage', json_encode($parameter));
            exit();


        }

        //机器人单聊：
        if (strpos($message, '/start') !== false) {
            //站点信息展示
            if (strpos($message, 'zhandian_detail') !== false) {
                $instruction_arr = explode("zhandian_detail", $message);
                $id = $instruction_arr[1];
                $set_sql1 = "select * FROM pay_hezuoname where id ='".$id."'";
                $order_query2 = $this->pdo->query($set_sql1);
                $order_info2 = $order_query2->fetchAll();
                $detai_info =$order_info2[0];
                $messages = " 
            🅿️站点名称:" . $detai_info['name'] . "
🆔站点永久域名:" . $detai_info['linkurl'] . "
📱服务器root:" . $detai_info['rooturl'] . "
🧑宝塔地址:" . $detai_info['baota'] . "
💰备注:" . $detai_info['remarks'];

                $parameter = array(
                    'chat_id' => $chatid,
                    'parse_mode' => 'HTML',
                    'text' => $messages
                );
                $this->http_post_data('sendMessage', json_encode($parameter));
                exit();
            }
            //删除站点
            if (strpos($message, 'deletezhan') !== false) {
                $instruction_arr = explode("deletezhan", $message);
                $id = $instruction_arr[1];
                $sql_info1 = "delete from pay_hezuoname where id ='" . $id . "'";
                $this->pdo->exec($sql_info1);

                $sql_info2 = "delete from pay_hezuodh where hezuoname_id ='" . $id . "'";
                $this->pdo->exec($sql_info2);
                $parameter = array(
                    'chat_id' => $chatid,
                    'parse_mode' => 'HTML',
                    'text' => "删除站点成功"
                );
                $this->http_post_data('sendMessage', json_encode($parameter));
                exit();

            }

            //导航广告信息展示
            if (strpos($message, 'daohang_detail') !== false) {
                $instruction_arr = explode("daohang_detail", $message);
                $id = $instruction_arr[1];
                $set_sql1 = "select * FROM pay_hezuodh where id ='".$id."'";
                $order_query2 = $this->pdo->query($set_sql1);
                $order_info2 = $order_query2->fetchAll();
                $detai_info =$order_info2[0];
                $messages = " 
            ✅导航名称:" . $detai_info['name'] . "
🅿️广告标题:" . $detai_info['title'] . "
🔎渠道编号:" . $detai_info['channel'] . "
🧑位置:" . $detai_info['weizhi'] . "
🆔站长地址:" . $detai_info['daohangurl'] . "
🦋链接地址:" . $detai_info['linkurl'] . "
📱续费时间:" . $detai_info['starttime'] . "
♻tg账号:" . $detai_info['tgurl'] ."
🔧邮箱:" . $detai_info['email'] . "
🔎金额:" . $detai_info['price'] . "U
💰备注:" . $detai_info['remarks'];

                $parameter = array(
                    'chat_id' => $chatid,
                    'parse_mode' => 'HTML',
                    'text' => $messages
                );
                $this->http_post_data('sendMessage', json_encode($parameter));
                exit();
            }
            //导航广告信息删除
            if (strpos($message, 'deletedaohang') !== false) {
                $instruction_arr = explode("deletedaohang", $message);
                $id = $instruction_arr[1];

                $sql_info2 = "delete from pay_hezuodh where id ='" . $id . "'";
                $this->pdo->exec($sql_info2);
                $parameter = array(
                    'chat_id' => $chatid,
                    'parse_mode' => 'HTML',
                    'text' => "删除导航信息成功"
                );
                $this->http_post_data('sendMessage', json_encode($parameter));
                exit();

            }

            $this->start($chatid);
        }

        //站点管理
        if (strpos($message, '站点管理') !== false) {
            $this->guanlian($chatid,$message);
            $set_sql1 = "select * FROM pay_hezuoname where chat_id = '".$chatid."' group by name";
            $order_query2 = $this->pdo->query($set_sql1);
            $order_info2 = $order_query2->fetchAll();
            if($order_info2){
                $msg = "<b>(按钮详情)站点列表：</b>\r\n\r\n";
                $inline_keyboard_arr = array();
                $switch_inline_query_current_msg = "#tianjia_zhandian_#\r\n站点名称=制服导航\r\n站点永久域名=www.baidu.com\r\n服务器root=127.0.0.1\r\n宝塔地址=127.0.0.1:7800/xxyyoo\r\n备注=备注信息";
                // $inline_keyboard_arr3[0] = array('text' => "继续添加 ", "switch_inline_query_current_chat" => $switch_inline_query_current_msg);
                foreach ($order_info2 as $key => $ve) {
                    $k = $key+1;
                    $msg .= $k.":  <b><a href='https://t.me/".$this->jiqirenminghezuo."?start=zhandian_detail" . $ve['id'] . "'>" . $ve['name'] . " ----</a></b>  <b><a href='https://t.me/".$this->jiqirenminghezuo."?start=deletezhan" . $ve['id'] . "'>删除</a></b>\r\n";


                    $inline_keyboard_arr4[$key] = array('text' => "查看广告列表", "callback_data" => "detailzhan###" . $ve['id']);

                }

                $keyboard = [
                    'inline_keyboard' => [
                        $inline_keyboard_arr4,
                        // $inline_keyboard_arr3

                    ]
                ];

                $parameter = array(
                    "chat_id" => $chatid,
                    "text" => $msg,
                    "parse_mode" => "HTML",
                    'reply_markup' => $keyboard,
                    'disable_web_page_preview' => true,

                );
                $this->http_post_data('sendMessage', json_encode($parameter));
                exit();
            }else{
                /*
                站点名称:
                站点当前域名:
                服务器root:
                宝塔:
                备注:

                */
                $messages = "未查询站点信息\r\n";
                $switch_inline_query_current_msg = "#tianjia_zhandian_#\r\n站点名称=制服导航\r\n站点永久域名=www.baidu.com\r\n服务器root=127.0.0.1\r\n宝塔地址=127.0.0.1:7800/xxyyoo\r\n备注=备注信息";
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
        }

    }

    public function peizhidb($chat_id){
        $set_sql1 = "select * FROM pay_hezuodb where chat_id = '".$chat_id."'";

        $order_query2 = $this->pdo->query($set_sql1);
        $order_info2 = $order_query2->fetchAll();

        $dbHost2 =$order_info2[0]['dbhost'];
        $dbName2 =$order_info2[0]['dbname'];
        $dbUser2 =$order_info2[0]['dbuser'];
        $dbPassword2 =$order_info2[0]['dbpass'];




        try{
            $pdo2= new PDO("mysql:host=" . $dbHost2 . ";dbname=" . $dbName2, $dbUser2, $dbPassword2, array(PDO::ATTR_PERSISTENT => true));
        }catch(PDOException $e){
            $parameter = array(
                'chat_id' => $chat_id,
                'parse_mode' => 'HTML',
                'text' => "数据库连接失败！请让站长检查",
            );

            $this->http_post_data('sendMessage', json_encode($parameter));
            exit();
        }


        $this->pdo2=$pdo2;

    }


    public function callback($data)
    {


        $text = $data['callback_query']['data'];
        $message = $text;
        $chat_id = $data['callback_query']['message']['chat']['id'];
        $chatid = $chat_id;
        $from_id = $data['callback_query']['from']['id'];
        $username = $data['callback_query']['from']['username'];
        $message_id = $data['callback_query']['message']['message_id'];

        $year = date('m');
        $month = date('m');

        //当前月的第一天
        $now_month_last_day = strtotime(date('Y-m-1'));
        //当前月的最后一天
        $now_month_end_day = strtotime(date('Y-m-d',strtotime(date('Y-m-1',strtotime('next month').'-1 day'))));

        //上月的第一天
        $last_month_last_day = strtotime(date('Y-m-1',strtotime('last month')));
        $last_month_end_day = strtotime(date('Y-m-d',strtotime(date('Y-m-1').'-1 day')));

        $now_month = date('Y年m月',$now_month_last_day);
        $last_month = date('Y年m月',$last_month_last_day);



        $msg_one = "当前月_".$now_month;
        $msg_two = "上个月_".$last_month;

        $inline_keyboard_arr = array();
        $inline_keyboard_arr[0] = array('text' => "最近二周", "callback_data" => "最近两周");
        $inline_keyboard_arr[1] = array('text' => "最近一月", "callback_data" => "最近一月");
        $inline_keyboard_arr[2] = array('text' => $now_month, "callback_data" => $msg_one);
        $inline_keyboard_arr[3] = array('text' => $last_month, "callback_data" => $msg_two);

        //删除群配置信息：
        if (strpos($text, 'shanchudb') !== false) {
            $instruction_arr = explode("shanchudb###", $text);
            $zhan_id = $instruction_arr[1];
            $sql_info2 = "delete from pay_hezuodb where id ='" . $zhan_id . "'";
            $this->pdo->exec($sql_info2);
            $parameter = array(
                'chat_id' => $chat_id,
                'parse_mode' => 'HTML',
                'text' => "删除配置信息成功"
            );
            $this->http_post_data('sendMessage', json_encode($parameter));
            exit();
        }

        //站点详情
        if (strpos($text, 'detailzhan') !== false) {
            $instruction_arr = explode("detailzhan###", $text);
            $zhan_id = $instruction_arr[1];
            $set_sql1 = "select * FROM pay_hezuoname where id ='".$zhan_id."'";
            $order_query2 = $this->pdo->query($set_sql1);
            $order_info2 = $order_query2->fetchAll();
            if(!$order_info2){

                $parameter = array(
                    'chat_id' => $chat_id,
                    'parse_mode' => 'HTML',
                    'text' => "查询异常"
                );

                $this->http_post_data('sendMessage', json_encode($parameter));
                exit();
            }

            $set_sql2 = "select * FROM pay_hezuodh where hezuoname_id ='".$zhan_id."'";
            $order_query3 = $this->pdo->query($set_sql2);
            $order_info3 = $order_query3->fetchAll();
            if(!$order_info3){

                $messages = "未查询到站点下的导航信息\r\n";
                $switch_inline_query_current_msg = "#tianjia_daohang_#".$zhan_id."###_#\r\n导航名称==制服导航\r\n广告标题==极品xx内射\r\n标志(唯一性)==101\r\n站长链接==www.zfp10.buzz\r\n链接地址==www.baidu.com?channel=101&type=1\r\n续费时间==2022-11-9\r\n位置==视频区第三个\r\n导航站长tg账号==@chengu123\r\n邮箱==ceshi@gmail.com\r\n金额==100U\r\n备注==靠谱导航";
                $inline_keyboard_arr3[0] = array('text' => "马上添加 ", "switch_inline_query_current_chat" => $switch_inline_query_current_msg);
                $keyboard = [
                    'inline_keyboard' => [
                        $inline_keyboard_arr3,
                    ]
                ];

                $parameter = array(
                    'chat_id' => $chat_id,
                    'parse_mode' => 'HTML',
                    'text' => $messages,
                    'reply_markup' => $keyboard,
                    'disable_web_page_preview' => true,

                );

                $this->http_post_data('sendMessage', json_encode($parameter));
                exit();
            }else{
                $messages = "";
                foreach ($order_info3 as $kqa=>$vesq){

                    $messages .= ($kqa+1).":  <b><a href='https://t.me/".$this->jiqirenminghezuo."?start=daohang_detail" . $vesq['id'] . "'>" . $vesq['channel']."--".$vesq['name'] . "</a></b>  <b><a href='https://t.me/".$this->jiqirenminghezuo."?start=deletedaohang" . $vesq['id'] . "'>删除</a></b>\r\n";
                }
                $switch_inline_query_current_msg = "#tianjia_daohang_#".$zhan_id."###_#\r\n导航名称==制服导航\r\n广告标题==极品xx内射\r\n标志(唯一性)==101\r\n站长链接==www.zfp10.buzz\r\n链接地址==www.baidu.com?channel=101&type=1\r\n续费时间==2022-11-9\r\n位置==视频区第三个\r\n导航站长tg账号==@chengu123\r\n邮箱==ceshi@gmail.com\r\n金额==100U\r\n备注==靠谱导航";
                $inline_keyboard_arr3[0] = array('text' => "继续添加 ", "switch_inline_query_current_chat" => $switch_inline_query_current_msg);
                $keyboard = [
                    'inline_keyboard' => [
                        $inline_keyboard_arr3,
                    ]
                ];

                $parameter = array(
                    'chat_id' => $chat_id,
                    'parse_mode' => 'HTML',
                    'text' => $messages,
                    'reply_markup' => $keyboard,
                    'disable_web_page_preview' => true,

                );

                $this->http_post_data('sendMessage', json_encode($parameter));
                exit();
            }
        }
        //转化统计【默认查询第一周的】
        if (strpos($message, '最近两周') !== false) {
            $set_sql1 = "select title,channel,name FROM pay_hezuodh where chat_id = '".$chatid."' group by channel";

            $order_query2 = $this->pdo->query($set_sql1);
            $order_info2 = $order_query2->fetchAll();



            $message = "最近两周转化：\r\n\r\n";
            foreach($order_info2 as $ke=>$ve){

                //注意:【2/60/345】，其中2是成功支付的，60是拉单数，345是流量数，转化率=成功订单/总流量
                $link_time_start= strtotime(date("Y-m-d",strtotime("-14 day")));
                $link_time_end = strtotime(date("Y-m-d"));

                $visit_sql ="select ip from pay_jilvvisit where channel='".$ve['channel']."' and createtime BETWEEN  '".$link_time_start."' and '".$link_time_end."' group by ip";

                $this->peizhidb($chatid);
                $order_visit = $this->pdo2->query($visit_sql);
                $visit_info = $order_visit->fetchAll();
                //所有的IP数据：
                $all_ip_arr = array();
                foreach ($visit_info as $k1=>$v1){
                    $all_ip_arr[] = $v1['ip'];
                }

                $all_liuliang = count($all_ip_arr); //流量数



                $chunk_result = array_chunk($all_ip_arr, 10);

                $all_ladan =0;      //拉单数
                $all_zhifu =0;      //成功支付数
                $all_price =0;      //成功支付数
                for($i=0;$i<count($chunk_result);$i++){
                    $ip_str = "";
                    for($j=0;$j<count($chunk_result[$i]);$j++){
                        $ip_str .= "'".$chunk_result[$i][$j]."',";
                    }
                    $ip_str = substr($ip_str,0,-1);
                    $visit_sql_o ="select status,price from pay_order_ip where ip in (".$ip_str.") and createtime BETWEEN '".$link_time_start."' and '".$link_time_end."'";

                    $order_visit_o = $this->pdo->query($visit_sql_o);
                    $visit_info_o = $order_visit_o->fetchAll();
                    if($visit_info_o){
                        $all_ladan+=count($visit_info_o);
                        foreach ($visit_info_o as $klq=>$klw){
                            if($klw['status'] =="1"){
                                $all_zhifu += 1;
                                $all_price += $klw['price'];
                            }
                        }
                    }
                }
                if($all_zhifu>0){
                    $zhuanhualv = (sprintf("%.2f",($all_zhifu/$all_ladan)*100))."%";
                }else{
                    $zhuanhualv = "0%";
                }

                /*
                ✅黑鲨导航
                ♒️转化率：2%【2/60/353】
                💰收入:30元
                */


                $message .= "✅".$ve['channel']."--".$ve['name']."\r\n";
                $message .= "♒️转化率". $zhuanhualv."【".$all_zhifu."/".$all_ladan."/".$all_liuliang."】\r\n";
                $message .= "✅收入".$all_price."元\r\n\r\n";

            }




            $keyboard = [
                'inline_keyboard' => [
                    $inline_keyboard_arr,

                ]
            ];
            $parameter = array(
                "chat_id" => $chatid,
                "text" => $message,
                "parse_mode" => "HTML",
                'reply_markup' => $keyboard,
                'disable_web_page_preview' => true,
            );
            $this->http_post_data('sendMessage', json_encode($parameter));
            exit();

        }
        //转化统计【默认查询第一周的】
        if (strpos($message, '最近一月') !== false) {
            $set_sql1 = "select title,channel,name FROM pay_hezuodh where chat_id = '".$chatid."' group by channel";

            $order_query2 = $this->pdo->query($set_sql1);
            $order_info2 = $order_query2->fetchAll();


            $message = "最近一月转化：\r\n\r\n";
            foreach($order_info2 as $ke=>$ve){

                //注意:【2/60/345】，其中2是成功支付的，60是拉单数，345是流量数，转化率=成功订单/总流量
                $link_time_start= strtotime(date("Y-m-d",strtotime("-30 day")));
                $link_time_end = strtotime(date("Y-m-d"));

                $visit_sql ="select ip from pay_jilvvisit where channel='".$ve['channel']."' and createtime BETWEEN  '".$link_time_start."' and '".$link_time_end."' group by ip";
                $this->peizhidb($chatid);
                $order_visit = $this->pdo2->query($visit_sql);
                $visit_info = $order_visit->fetchAll();
                //所有的IP数据：
                $all_ip_arr = array();
                foreach ($visit_info as $k1=>$v1){
                    $all_ip_arr[] = $v1['ip'];
                }

                $all_liuliang = count($all_ip_arr); //流量数



                $chunk_result = array_chunk($all_ip_arr, 10);

                $all_ladan =0;      //拉单数
                $all_zhifu =0;      //成功支付数
                $all_price =0;      //成功支付数
                for($i=0;$i<count($chunk_result);$i++){
                    $ip_str = "";
                    for($j=0;$j<count($chunk_result[$i]);$j++){
                        $ip_str .= "'".$chunk_result[$i][$j]."',";
                    }
                    $ip_str = substr($ip_str,0,-1);
                    $visit_sql_o ="select status,price from pay_order_ip where ip in (".$ip_str.") and createtime BETWEEN '".$link_time_start."' and '".$link_time_end."'";

                    $order_visit_o = $this->pdo->query($visit_sql_o);
                    $visit_info_o = $order_visit_o->fetchAll();
                    if($visit_info_o){
                        $all_ladan+=count($visit_info_o);
                        foreach ($visit_info_o as $klq=>$klw){
                            if($klw['status'] =="1"){
                                $all_zhifu += 1;
                                $all_price += $klw['price'];
                            }
                        }
                    }
                }
                if($all_zhifu>0){
                    $zhuanhualv = (sprintf("%.2f",($all_zhifu/$all_ladan)*100))."%";
                }else{
                    $zhuanhualv = "0%";
                }

                /*
                ✅黑鲨导航
                ♒️转化率：2%【2/60/353】
                💰收入:30元
                */

                $message .= "✅".$ve['title']."\r\n";
                $message .= "♒️转化率". $zhuanhualv."【".$all_zhifu."/".$all_ladan."/".$all_liuliang."】\r\n";
                $message .= "✅收入".$all_price."元\r\n\r\n";

            }




            $keyboard = [
                'inline_keyboard' => [
                    $inline_keyboard_arr,

                ]
            ];
            $parameter = array(
                "chat_id" => $chatid,
                "text" => $message,
                "parse_mode" => "HTML",
                'reply_markup' => $keyboard,
                'disable_web_page_preview' => true,
            );
            $this->http_post_data('sendMessage', json_encode($parameter));
            exit();

        }
        //转化统计【默认查询第一周的】
        if (strpos($message, '当前月') !== false) {
            $set_sql1 = "select title,channel,name FROM pay_hezuodh where chat_id = '".$chatid."' group by channel";

            $order_query2 = $this->pdo->query($set_sql1);
            $order_info2 = $order_query2->fetchAll();

            $text_arr = explode("_",$message);


            $message = $text_arr[1]."转化：\r\n\r\n";
            foreach($order_info2 as $ke=>$ve){



                $visit_sql ="select ip from pay_jilvvisit where channel='".$ve['channel']."' and createtime BETWEEN  '".$now_month_last_day."' and '".$now_month_end_day."' group by ip";
                $this->peizhidb($chatid);
                $order_visit = $this->pdo2->query($visit_sql);
                $visit_info = $order_visit->fetchAll();
                //所有的IP数据：
                $all_ip_arr = array();
                foreach ($visit_info as $k1=>$v1){
                    $all_ip_arr[] = $v1['ip'];
                }

                $all_liuliang = count($all_ip_arr); //流量数



                $chunk_result = array_chunk($all_ip_arr, 10);

                $all_ladan =0;      //拉单数
                $all_zhifu =0;      //成功支付数
                $all_price =0;      //成功支付数
                for($i=0;$i<count($chunk_result);$i++){
                    $ip_str = "";
                    for($j=0;$j<count($chunk_result[$i]);$j++){
                        $ip_str .= "'".$chunk_result[$i][$j]."',";
                    }
                    $ip_str = substr($ip_str,0,-1);
                    $visit_sql_o ="select status,price from pay_order_ip where ip in (".$ip_str.") and createtime BETWEEN '".$now_month_last_day."' and '".$now_month_end_day."'";

                    $order_visit_o = $this->pdo->query($visit_sql_o);
                    $visit_info_o = $order_visit_o->fetchAll();
                    if($visit_info_o){
                        $all_ladan+=count($visit_info_o);
                        foreach ($visit_info_o as $klq=>$klw){
                            if($klw['status'] =="1"){
                                $all_zhifu += 1;
                                $all_price += $klw['price'];
                            }
                        }
                    }
                }
                if($all_zhifu>0){
                    $zhuanhualv = (sprintf("%.2f",($all_zhifu/$all_ladan)*100))."%";
                }else{
                    $zhuanhualv = "0%";
                }

                /*
                ✅黑鲨导航
                ♒️转化率：2%【2/60/353】
                💰收入:30元
                */

                //  $message .= "✅".$ve['channel']."--".$ve['name']."\r\n";
                $message .= "✅".$ve['channel']."--".$ve['name']."\r\n";
                $message .= "♒️转化率". $zhuanhualv."【".$all_zhifu."/".$all_ladan."/".$all_liuliang."】\r\n";
                $message .= "✅收入".$all_price."元\r\n\r\n";

            }




            $keyboard = [
                'inline_keyboard' => [
                    $inline_keyboard_arr,

                ]
            ];
            $parameter = array(
                "chat_id" => $chatid,
                "text" => $message,
                "parse_mode" => "HTML",
                'reply_markup' => $keyboard,
                'disable_web_page_preview' => true,
            );
            $this->http_post_data('sendMessage', json_encode($parameter));
            exit();

        }
        //转化统计【默认查询第一周的】
        if (strpos($message, '上个月') !== false) {
            $set_sql1 = "select title,channel,name FROM pay_hezuodh where chat_id = '".$chatid."' group by channel";

            $order_query2 = $this->pdo->query($set_sql1);
            $order_info2 = $order_query2->fetchAll();



            $text_arr = explode("_",$message);


            $message = $text_arr[1]."转化：\r\n\r\n";
            foreach($order_info2 as $ke=>$ve){

                //注意:【2/60/345】，其中2是成功支付的，60是拉单数，345是流量数，转化率=成功订单/总流量
                $link_time_start= strtotime(date("Y-m-d",strtotime("-7 day")));
                $link_time_end = strtotime(date("Y-m-d"));

                $visit_sql ="select ip from pay_jilvvisit where channel='".$ve['channel']."' and createtime BETWEEN  '".$last_month_last_day."' and '".$last_month_end_day."' group by ip";
                $this->peizhidb($chatid);
                $order_visit = $this->pdo2->query($visit_sql);
                $visit_info = $order_visit->fetchAll();
                //所有的IP数据：
                $all_ip_arr = array();
                foreach ($visit_info as $k1=>$v1){
                    $all_ip_arr[] = $v1['ip'];
                }

                $all_liuliang = count($all_ip_arr); //流量数



                $chunk_result = array_chunk($all_ip_arr, 10);

                $all_ladan =0;      //拉单数
                $all_zhifu =0;      //成功支付数
                $all_price =0;      //成功支付数
                for($i=0;$i<count($chunk_result);$i++){
                    $ip_str = "";
                    for($j=0;$j<count($chunk_result[$i]);$j++){
                        $ip_str .= "'".$chunk_result[$i][$j]."',";
                    }
                    $ip_str = substr($ip_str,0,-1);
                    $visit_sql_o ="select status,price from pay_order_ip where ip in (".$ip_str.") and createtime BETWEEN '".$last_month_last_day."' and '".$last_month_end_day."'";

                    $order_visit_o = $this->pdo2->query($visit_sql_o);
                    $visit_info_o = $order_visit_o->fetchAll();
                    if($visit_info_o){
                        $all_ladan+=count($visit_info_o);
                        foreach ($visit_info_o as $klq=>$klw){
                            if($klw['status'] =="1"){
                                $all_zhifu += 1;
                                $all_price += $klw['price'];
                            }
                        }
                    }
                }
                if($all_zhifu>0){
                    $zhuanhualv = (sprintf("%.2f",($all_zhifu/$all_ladan)*100))."%";
                }else{
                    $zhuanhualv = "0%";
                }

                /*
                ✅黑鲨导航
                ♒️转化率：2%【2/60/353】
                💰收入:30元
                */

                //$message .= "✅".$ve['title']."\r\n";
                $message .= "✅".$ve['channel']."--".$ve['name']."\r\n";
                $message .= "♒️转化率". $zhuanhualv."【".$all_zhifu."/".$all_ladan."/".$all_liuliang."】\r\n";
                $message .= "✅收入".$all_price."元\r\n\r\n";

            }



            $keyboard = [
                'inline_keyboard' => [
                    $inline_keyboard_arr,

                ]
            ];
            $parameter = array(
                "chat_id" => $chatid,
                "text" => $message,
                "parse_mode" => "HTML",
                'reply_markup' => $keyboard,
                'disable_web_page_preview' => true,
            );
            $this->http_post_data('sendMessage', json_encode($parameter));
            exit();

        }

    }


    public function guanlian($chatid,$message){
        $set_sql1 = "select * FROM pay_hezuodb where chat_id = '".$chatid."'";
        $order_query2 = $this->pdo->query($set_sql1);
        $order_info2 = $order_query2->fetchAll();
        if(!$order_info2){
            $parameter = array(
                'chat_id' => $chatid,
                'parse_mode' => 'HTML',
                'text' => "当前群未关联用户数据信息,设置用户信息：/tjyh"
            );

            $this->http_post_data('sendMessage', json_encode($parameter));
            exit();
        }
    }




    //系统后台：
    public function start($chatid)
    {
        $keyboard2 = [
            'keyboard' => [
                [

                    ['text' => '今日转化'],
                    ['text' => '昨日转化'],
                    ['text' => '转化统计'],
                    ['text' => '站点管理'],
                ],
                [['text' => '广告管理']],
            ],
            'resize_keyboard' => true,
            'one_time_keyboard' => false,

        ];
        $encodedKeyboard2 = json_encode($keyboard2);


        $parameter = array(
            'chat_id' => $chatid,
            'text' => "后台管理",
            'reply_markup' => $encodedKeyboard2
        );
        //设置当前用户进入后台：


        //发送消息

        $this->http_post_data('sendMessage', json_encode($parameter));
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


}

$oen = new five();
$oen->index();


?>