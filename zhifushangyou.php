<?php
header('Content-Type: text/html; charset=utf-8');
mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');
/*require 'vendor/autoload.php';

use Telegram\Bot\Api;

include "two.php";*/


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
    private $link2 = "";
    private $token = "";
    private $pdo;
    private $chaojiyonghu;
    private $all_ming_list = array(
        '操作回调',           ///user10000  【实际操作命令】
    );

    public function __construct()
    {

        include "cron_jiqi.php";

        $this->chaojiyonghu = $chaojiyonghu;
        $this->jiqirenming = $jiqi_jiqirenming;
        $token2 = $token;
        $token_ziji = $token_ziji;
        $this->link = 'https://api.telegram.org/bot'.$token_ziji;
        $this->link2 = 'https://api.telegram.org/bot'.$token2;
        /*$dbHost = "127.0.0.1";
        $dbName = "chpay";
        $dbUser = "chpay";
        $dbPassword = "RpyZXiK4DLSscRTk";*/

        //$this->pdo = new PDO("mysql:host=" . $dbHost . ";dbname=" . $dbName, $dbUser, $dbPassword, array(PDO::ATTR_PERSISTENT => true));
        $this->pdo = new PDO(
            "mysql:host=" . $dbHost . ";dbname=" . $dbName . ";charset=utf8mb4",
            $dbUser,
            $dbPassword,
            [
                PDO::ATTR_PERSISTENT => true,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
            ]
        );
             // 确保数据库连接使用正确的字符集
        $this->pdo->exec("SET NAMES utf8mb4");
        $this->pdo->exec("SET CHARACTER SET utf8mb4");
        $this->pdo->exec("SET character_set_connection=utf8mb4");
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
              //$this->xiaoxinoend("进来了", $chatid);

            $this->processMessage($message, $chatid, $data, $userid);
        }


    }


    public function processMessage($message, $chatid, $userid)
    {
        if (strpos($message, '/start') !== false) {
            $this->start_hou($chatid);
        } else {
            $reply = $this->getReplyForMessage($message,$chatid);
            if ($reply) {
                $this->xiaoxi($reply, $chatid);
            }
        }
    }
    public function getRules()
    {
        $sql_info = "select * from pay_shangyouhuifu";
        $order_query2 = $this->pdo->query($sql_info);
        $chatinfo = $order_query2->fetchAll();
        return $chatinfo;
    }
    public function getReplyForMessage($message,$chatid)
    {
        $rules = $this->getRules();
        if($rules){
            foreach ($rules as $rule) {
                $conditions = explode(' 或 ', $rule['conditions']);
                foreach ($conditions as $condition) {
                    $subConditions = explode(' 且 ', $condition);
                    $match = true;
                    foreach ($subConditions as $subCondition) {
                        if (!preg_match('/' . $subCondition . '/', $message)) {
                            $match = false;
                            break;
                        }
                    }
                    if ($match) {
                        return str_replace(['<br>', '\\n'], "\n", $rule['reply_content']);

                        //return $rule['reply_content'];
                    }
                }
            }
        }

        return null;
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


    public function xiaoxidisanfang($msg, $chatid, $type = "0", $answer = "")
    {
        $parameter = array(
            'chat_id' => $chatid,
            'parse_mode' => 'HTML',
            'text' => $msg
        );
        $this->http_post_data2('sendMessage', json_encode($parameter));
        if ($type == "1") {
            $parameter = array(
                'callback_query_id' => $answer,
                'text' => "",
            );
            $this->http_post_data2('answerCallbackQuery', json_encode($parameter));
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
        $answer_id =  $data['callback_query']['id'];
        $from_id = $data['callback_query']['from']['id'];
        $userid = $from_id;
        $message_id = $data['callback_query']['message']['message_id'];

        $chatid = $chat_id;
        $username = $data['callback_query']['from']['username'];//用户名称

        $admin_id =$username;

        //已经支付成功,并且处理回调：
        if (strpos($text, 'chulihuidiao_') !== false) {

            $quanxian = "操作回调";
            $this->quanxian($chatid, $userid, $quanxian, $username);
            $api_order_arr  = explode("_",$text);
            $api_order = $api_order_arr[1];
            $chanel = $api_order_arr[2];


            $api_order_arr  = explode("_",$text);
             $apiorder = $api_order_arr[1];
             $ley = $api_order_arr[2];
           $sql_info = "select id,status from pay_tianshibudan where apiorder ='" . $api_order . "'";
             $order_query2 = $this->pdo->query($sql_info);
             $chatinfo = $order_query2->fetchAll();

             if(!$chatinfo){
                   $msg = "此补单已作废，请勿操作！";
                    $this->xiaoxi($msg, $chatid);
             }
                 //看看是不是已经处理了：
                if($chatinfo[0]['status']>0){
                    $msg = "此补单已经操作了，请勿重复操作！";
                    $this->xiaoxi($msg, $chatid);
                }
                $id_bu = $chatinfo[0]['id'];
                //随便哪一个订单明细 查询商户跟上游：
                $sql_info4 = "select uid,id,order_sn,trade_no from pay_budandetail where tianshibudan_id ='" . $id_bu . "' and channel='".$chanel."' and status='0'";
                $order_query4 = $this->pdo->query($sql_info4);
                $budandetail = $order_query4->fetchAll();
                if(!$budandetail){
                    $msg = "此补单已经操作了，请勿重复操作！";
                    $this->xiaoxi($msg, $chatid);
                }
                if(count($budandetail)>1){
                    //多个
                    $all_count = count($budandetail);
                    foreach($budandetail as $kes=>$sqa){
                        $sssx = "zheshiarray"."_".$kes;
                        $sssx = array();
                       $sssx[0] = array('text' => "天使订单号：".$sqa['trade_no'], "callback_data" => "zhifuqueding_" . $sqa['trade_no']."_".$id_bu."_".$chanel);

                        $keyboard['inline_keyboard'][]=$sssx;
                    }

                    $shangyou_msg = "请选择补单号：".$api_order."  中确定支付成功操作的订单号：\r\n\r\n";



                    $parameter = array(
                        'chat_id' => $chatid,
                        'text'=>$shangyou_msg,
                        'reply_markup' => $keyboard,
                        'parse_mode' => 'HTML',

                    );
                    // $this->xiaoxi(json_encode($parameter),$chatid);
                    $this->http_post_data('sendMessage',json_encode($parameter));
                    exit();
                }
                //单个：
                $res1 = $this->pdo->exec("UPDATE pay_tianshibudan SET status='1',admin_id='".$admin_id."',updatetime='".time()."' WHERE apiorder='" . $api_order . "'");

                $dingdanya = "";
                $trade_no_str = "";

                foreach ($budandetail as $key=>$vales){
                    $tians_id = $vales['id'];
                    $res2 = $this->pdo->exec("UPDATE pay_budandetail SET status='1' WHERE id='" . $tians_id . "'");


                    $dingdanya .= $vales['order_sn']."\r\n";
                    $trade_no_str .= $vales['trade_no']."\r\n";
                }
                if(!$budandetail){
                    $msg = "没有查询此补单详细信息";
                    $this->xiaoxi($msg, $chatid);
                }

                $uid = $budandetail[0]['uid'];


                if($res1){
                    //告知商户：
                    //查询商户：
                    $sql_info2 = "select chatid from pay_botsettle where merchant ='".$uid."' limit 1";

                    $order_query3 = $this->pdo->query($sql_info2);
                    $chatinfo2 = $order_query3->fetchAll();


                    $shanghu_chatid = $chatinfo2[0]['chatid'];
                    $tuisong_msg = "补单编号：<b>".$api_order."</b>\r\n状态：已支付,已回调\r\n系统订单号：".$trade_no_str."\r\n商户订单号:\r\n".$dingdanya;
                     $parameter = array(
                        'chat_id' => $shanghu_chatid,
                        'parse_mode' => 'HTML',
                        'text' => $tuisong_msg

                    );
                    $this->http_post_data2('sendMessage', json_encode($parameter));
                    $parameter = array(
                        'callback_query_id' => $answer_id,
                        'text' => "",
                    );
                    $this->http_post_data2('answerCallbackQuery', json_encode($parameter));
                    $trade_no = $budandetail[0]['trade_no'];
                     $res9 = $this->pdo->exec("UPDATE pay_order SET status='1' WHERE trade_no='" . $trade_no . "'");
                    //回调商户的后台：
                    $this->huidiaokan($uid,$budandetail[0]['trade_no'],$chatid);
                    if($huidiaokan){
                         $parameter = array(
                            'chat_id' => $shanghu_chatid,
                            'parse_mode' => 'HTML',
                            'text' => "补单号：".$api_order."支付成功，且已回调"
                        );

                    }else{
                        $parameter = array(
                            'chat_id' => $shanghu_chatid,
                            'parse_mode' => 'HTML',
                            'text' => "补单号：".$api_order."支付成功，但回调失败,请客服手动在后台操作回调操作"
                        );
                    }
                    $this->http_post_data2('sendMessage', json_encode($parameter));
                     $msg = "补单成功！";

                    $this->xiaoxi($msg, $chatid);
                }else{
                      $this->xiaoxi("操作失败", $chatid);
                }

        }
        //确定支付的订单号是哪个：
         if (strpos($text, 'zhifuqueding_') !== false) {
                $api_order_arr  = explode("_",$text);
                $trade_no = $api_order_arr[1];
                $budan_id = $api_order_arr[2];
                $channel = $api_order_arr[3];

                $sql_info2 = "select status,apiorder from pay_tianshibudan where id ='" . $budan_id . "'";

                $order_query2 = $this->pdo->query($sql_info2);
                $budanzhuinfo = $order_query2->fetchAll();
                 if($budanzhuinfo[0]['status']>0){
                    $pp_status = array(
                        '1'=>"已支付,已回调",
                        '2'=>"未支付"
                    );
                    $msg = "此补单号：".$budanzhuinfo[0]['apiorder']."   已经操作了，状态为：".$pp_status[$budanzhuinfo[0]['status']]."请勿重复操作！";
                    $this->xiaoxi($msg, $chatid);
                }


                $sql_info = "select uid,id,order_sn,trade_no from pay_budandetail where tianshibudan_id ='" . $budan_id . "' and channel='".$channel."' and status='0'";// and status='0'

                $order_query3 = $this->pdo->query($sql_info);
                $budaninfo = $order_query3->fetchAll();

                if(!$budaninfo){
                 $this->xiaoxi("补单订单号异常", $chatid);
                }
                //找到对应的 当前订单状态修改为已支付，其他的要设置为未支付:
                foreach ($budaninfo as $ksa=>$vaqs){
                     $tians_id = $vaqs['id'];
                    if($vaqs['trade_no'] != $trade_no){
                        //修改成未支付：
                        $res1 = $this->pdo->exec("UPDATE pay_budandetail SET status='2' WHERE id='" . $tians_id . "'");
                    }else{
                        //修改成已经支付：
                        $res2 = $this->pdo->exec("UPDATE pay_budandetail SET status='1' WHERE id='" . $tians_id . "'");

                    }

                }
                 $uid = $budaninfo[0]['uid'];
                //把当前补单修改为已支付：并且通知商户：
                 $res = $this->pdo->exec("UPDATE pay_tianshibudan SET status='1',admin_id='".$admin_id."',updatetime='".time()."' WHERE id='" . $budan_id . "'");
                 $res = true;

                 if($res){

                     //修改订单状态：
                     $res9 = $this->pdo->exec("UPDATE pay_order SET status='1' WHERE trade_no='" . $trade_no . "'");


                     $api_order_sql =  "select * from pay_tianshibudan where id ='" . $budan_id . "'";
                     $api_order_query3 = $this->pdo->query($api_order_sql);
                     $api_orderinfo = $api_order_query3->fetchAll();
                     $api_order = $api_orderinfo[0]['apiorder'];
                    //告知商户：
                    //查询商户：
                    $sql_info2 = "select chatid from pay_botsettle where merchant ='".$uid."' limit 1";

                    $order_query3 = $this->pdo->query($sql_info2);
                    $chatinfo2 = $order_query3->fetchAll();
                    $shanghu_chatid = $chatinfo2[0]['chatid'];
                    $tuisong_msg = "补单编号：<b>".$api_order."</b>\r\n处理意见：已支付，且回调\r\n相关单号:".$trade_no;
                     $parameter2 = array(
                        'chat_id' => $shanghu_chatid,
                        'parse_mode' => 'HTML',
                        'text' => $tuisong_msg

                    );
                    if($res9){
                         $this->http_post_data2('sendMessage', json_encode($parameter2));
                    }

                    $parameter1 = array(
                        'callback_query_id' => $answer_id,
                        'text' => "",
                    );
                    $this->http_post_data2('answerCallbackQuery', json_encode($parameter1));



                    $huidiaokan = $this->huidiaokan($uid,$trade_no,$chatid);

                    if($huidiaokan){
                         $parameter3 = array(
                            'chat_id' => $shanghu_chatid,
                            'parse_mode' => 'HTML',
                            'text' => "补单号：".$api_order."支付成功，且已回调"
                        );

                    }else{
                        $parameter3 = array(
                            'chat_id' => $shanghu_chatid,
                            'parse_mode' => 'HTML',
                             'text' => "补单号：".$api_order."支付成功，但回调失败,请客服手动在后台操作回调操作"
                        );
                    }
                    $this->http_post_data2('sendMessage', json_encode($parameter3));

                    $msg = "操作指定订单号： ".$trade_no."  成功！";
                    $this->xiaoxi($msg, $chatid);
                }


         }


        //没有支付成功。无需处理:
        if (strpos($text, 'meiyouzhifu_') !== false) {
             $api_order_arr  = explode("_",$text);
             $apiorder = $api_order_arr[1];
             $ley = $api_order_arr[2];
            //修改弹框内容：
             $inline_keyboard_arr[0] = array('text' => "超时支付，查询不到", "callback_data" => "chaoshijian_" . $apiorder."_".$ley);
             $inline_keyboard_arr2[0] = array('text' => "截图凭证不完整，查询不到", "callback_data" => "zhifupinzheng_" . $apiorder."_".$ley);
             $inline_keyboard_arr3[0] = array('text' => "单图不符，查询不到", "callback_data" => "dantubufu_" . $apiorder."_".$ley);

                $keyboard = [
                    'inline_keyboard' => [
                        $inline_keyboard_arr,
                        $inline_keyboard_arr2,
                        $inline_keyboard_arr3
                    ]
                ];

                $shangyou_msg = "补单号：".$apiorder."   此订单具体未支付的原因请选择：\r\n\r\n";



                $parameter = array(
                    'chat_id' => $chatid,
                    'text'=>$shangyou_msg,
                    'reply_markup' => $keyboard,
                    'parse_mode' => 'HTML',

                );
                // $this->xiaoxi(json_encode($parameter),$chatid);
                $this->http_post_data('sendMessage',json_encode($parameter));

        }
        //超时支付，查询不到
         if (strpos($text, 'chaoshijian') !== false) {
             $this->weizhifude($text,$chatid, $userid, $quanxian, $username,4);
         }
         //截图凭证不完整，查询不到
         if (strpos($text, 'zhifupinzheng') !== false) {
             $this->weizhifude($text,$chatid, $userid, $quanxian, $username,7);
         }
         //单图不符，查询不到
         if (strpos($text, 'dantubufu') !== false) {
             $this->weizhifude($text,$chatid, $userid, $quanxian, $username,6);
         }


        $parameter = array(
            'callback_query_id' => $data['callback_query']['id'],
            'text' => "",
        );
        $this->http_post_data('answerCallbackQuery', json_encode($parameter));


    }

    //通知商户回调：
    public function huidiaokan($uid,$trade_no,$chatid){
        //查询商户的key:

        $uid_sql = "select * from pay_user where uid ='" . $uid . "'";

        $uid_query3 = $this->pdo->query($uid_sql);
        $uidinfo = $uid_query3->fetchAll();
          $appkey =  $uidinfo[0]['key'];


        //查询订单信息:
        $order_sql = "select a.*,b.name as type_name from pay_order as a left join pay_type as b on b.id=a.type where a.trade_no ='" . $trade_no . "'";
        $order_query3 = $this->pdo->query($order_sql);
        $orderinfo = $order_query3->fetchAll();




        $huidiaourl =  $orderinfo[0]['notify_url'];
        $uid_sql = "";
            $data =array(
                'uid'=>$uid,
                "trade_no"=>$trade_no,
                "out_trade_no"=>$orderinfo[0]['out_trade_no'],
                "type"=>$orderinfo[0]['type_name'],
                "name"=>$orderinfo[0]['name'],
                "money"=>$orderinfo[0]['money'],

            );
    	    $array=array('pid'=>$data['uid'],'trade_no'=>$data['trade_no'],'out_trade_no'=>$data['out_trade_no'],'type'=>$type,'name'=>$data['name'],'money'=>(float)$data['money'],'trade_status'=>'TRADE_SUCCESS');






            $arg=$this->argSort($this->paraFilter($array));
	        $prestr=$this->createLinkstring($arg);
	        $urlstr=$this->createLinkstringUrlencode($arg);
	        $sign=$this->md5Sign($prestr, $appkey);

	        $url=$huidiaourl.'?'.$urlstr.'&sign='.$sign.'&sign_type=MD5';

	        $curl_get = $this->curl_get($url);
	        if(strpos($curl_get,'success')!==false || strpos($curl_get,'SUCCESS')!==false || strpos($curl_get,'Success')!==false){
	            $this->pdo->exec("UPDATE pay_order SET notify=0 WHERE trade_no='$trade_no'");
	            return true;
	        }else{
	            return false;
	        }

    }
    function curl_get($url)
    {
        $ch=curl_init($url);
        $httpheader[] = "Accept: */*";
        $httpheader[] = "Accept-Language: zh-CN,zh;q=0.8";
        $httpheader[] = "Connection: close";
        curl_setopt($ch, CURLOPT_HTTPHEADER, $httpheader);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Linux; U; Android 4.4.1; zh-cn; R815T Build/JOP40D) AppleWebKit/533.1 (KHTML, like Gecko)Version/4.0 MQQBrowser/4.5 Mobile Safari/533.1');
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        $content=curl_exec($ch);
        curl_close($ch);
        return($content);
    }

    public function weizhifude($text,$chatid, $userid, $quanxian, $username,$status){
        $status_arr = array(
            '1'=>"已支付",
            "2"=>"未支付",
            "3"=>"出补单时间",
            "5"=>"其他",
            '4'=>"超时支付，查询不到",
            "7"=>"截图凭证不完整，查询不到",
            "6"=>"单图不符，查询不到"
        );

            $api_order_arr  = explode("_",$text);
            $api_order = $api_order_arr[1];
            $chanel = $api_order_arr[2];
             $sql_info = "select id,status from pay_tianshibudan where apiorder ='" . $api_order . "'";
             $order_query2 = $this->pdo->query($sql_info);
             $chatinfo = $order_query2->fetchAll();

             if($chatinfo){
                 //看看是不是已经处理了：
                 if($chatinfo[0]['status']>0){
                      $msg = "此补单已经进行了，请勿重复操作！";
                      $this->xiaoxi($msg, $chatid);
                 }

                $id_bu = $chatinfo[0]['id'];




                //随便哪一个订单明细 查询商户跟上游：
                $sql_info4 = "select uid,id,order_sn,trade_no from pay_budandetail where tianshibudan_id ='" . $id_bu . "' and channel='".$chanel."'";
                $order_query4 = $this->pdo->query($sql_info4);

                $budandetail = $order_query4->fetchAll();

                $dingdanya = "";
                $trade_no_str = "";
                $kankan_status = true;
                foreach ($budandetail as $key=>$vales){
                    $tians_id = $vales['id'];
                    $res = $this->pdo->exec("UPDATE pay_budandetail SET status='".$status."' WHERE id='" . $tians_id . "'");

                    $dingdanya .= $vales['order_sn']."\r\n";
                    $trade_no_str .= $vales['trade_no']."\r\n";


                }


                 //这里需要查询一下是不是已经全部处理完毕：
                $sql_info5 = "select uid,id,order_sn,trade_no from pay_budandetail where tianshibudan_id ='" . $id_bu . "' and status='0'";
                $order_query5 = $this->pdo->query($sql_info5);
                $budandetail_cha = $order_query5->fetchAll();

                if(count($budandetail_cha)<=0){
                    //整个补单状态设置完毕
                    $res2 = $this->pdo->exec("UPDATE pay_tianshibudan SET status='2',admin_id='".$admin_id."',updatetime='".time()."' WHERE id='" . $id_bu . "'");
                }



                if(!$budandetail){
                    $msg = "没有查询此补单详细信息";
                    $this->xiaoxi($msg, $chatid);
                }

                $uid = $budandetail[0]['uid'];


                if($res2){

                    //告知商户：
                    //查询商户：
                    $sql_info2 = "select chatid from pay_botsettle where merchant ='".$uid."' limit 1";

                    $order_query3 = $this->pdo->query($sql_info2);
                    $chatinfo2 = $order_query3->fetchAll();
                    $shanghu_chatid = $chatinfo2[0]['chatid'];
                    $tuisong_msg = "补单编号：<b>".$api_order."</b>\r\n处理意见：".$status_arr[$status]."\r\n相关单号:".$dingdanya;
                     $parameter = array(
                        'chat_id' => $shanghu_chatid,
                        'parse_mode' => 'HTML',
                        'text' => $tuisong_msg

                    );
                    $this->http_post_data2('sendMessage', json_encode($parameter));
                    $parameter = array(
                        'callback_query_id' => $answer_id,
                        'text' => "",
                    );
                    $this->http_post_data2('answerCallbackQuery', json_encode($parameter));

                    $msg = "操作成功！";
                    $this->xiaoxi($msg, $chatid,1,$answer_id);
                }else{
                    $msg = "操作成功！";
                    $this->xiaoxi($msg, $chatid,1,$answer_id);
                }
             }else{
                  $msg = "没有查询此补单信息";
                  $this->xiaoxi($msg, $chatid);
             }
    }

    public function md5Sign($prestr, $key) {
		$prestr = $prestr . $key;
		return md5($prestr);
	}
    public function createLinkstringUrlencode($para) {
		$arg  = "";
		foreach ($para as $key=>$val) {
			$arg.=$key."=".urlencode($val)."&";
		}
		//去掉最后一个&字符
		$arg = substr($arg,0,-1);

		return $arg;
	}
    public function createLinkstring($para) {
		$arg  = "";
		foreach ($para as $key=>$val) {
			$arg.=$key."=".$val."&";
		}
		//去掉最后一个&字符
		$arg = substr($arg,0,-1);

		return $arg;
	}
    public function argSort($para) {
		ksort($para);
		reset($para);
		return $para;
	}
    public function paraFilter($para) {
		$para_filter = array();
		foreach ($para as $key=>$val) {
			if($key == "sign" || $key == "sign_type" || $val == "" || $key == "stype" || $key == "request_method" || $key == "u_channel" )continue;
			else $para_filter[$key] = $para[$key];
		}
		return $para_filter;
	}
	public function send_shanghupost($url, $post_data)
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

        // 设置CURL选项以确保正确处理UTF-8编码
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate');
        curl_setopt($ch, CURLOPT_ACCEPT_ENCODING, 'gzip, deflate');

        ob_start();

        curl_exec($ch);

        $return_content = ob_get_contents();

        //echo $return_content."


        ob_end_clean();

        $return_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        // return array($return_code, $return_content);

        return $return_content;

    }

    //post的json数据请求
    public function http_post_data2($action, $data_string)
    {
        //这里，
        /*$sql= "insert into wolive_tests (content) values ('".json_encode($data)."')";
        $this->pdo->exec($sql);*/

        $url = $this->link2 . "/" . $action . "?";
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_POST, 1);

        curl_setopt($ch, CURLOPT_URL, $url);

        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);

        curl_setopt($ch, CURLOPT_HTTPHEADER, array(

                'Content-Type: application/json; charset=utf-8',

                'Content-Length: ' . strlen($data_string))

        );

        // 设置CURL选项以确保正确处理UTF-8编码
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate');
        curl_setopt($ch, CURLOPT_ACCEPT_ENCODING, 'gzip, deflate');

        ob_start();

        curl_exec($ch);

        $return_content = ob_get_contents();

        //echo $return_content."


        ob_end_clean();

        $return_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        // return array($return_code, $return_content);

        return $return_content;

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



    public function quanxian($chatid, $userid, $quanxian, $username)
    {
        $username = "@" . $username;
        return true;
        if (!in_array($userid, $this->chaojiyonghu)) {

            $set_sql1 = "select * FROM pay_zuren where typelist ='3' and username='" . $userid . "'";

            $order_query2 = $this->pdo->query($set_sql1);
            $order_info2 = $order_query2->fetchAll();
            $is_ok = true;

            if (!$order_info2) {
                $set_sqlqw = "select * FROM pay_zuren where typelist ='3' and username='" . $username . "'";

                $order_queryq1 = $this->pdo->query($set_sqlqw);
                $order_infoq2 = $order_queryq1->fetchAll();
                if (!$order_infoq2) {
                    $parameter = array(
                        'chat_id' => $chatid,
                        'parse_mode' => 'HTML',
                        'text' => "你没有当前在权限用户组内,请联系楚歌@fu_008添加权限",
                    );
                    $this->http_post_data('sendMessage', json_encode($parameter));
                    exit();

                } else {
                    $yonghuzu_id_data = $order_infoq2[0]['yonghuzu_id'];
                    $is_ok = false;
                }
            } else {
                $yonghuzu_id_data = $order_info2[0]['yonghuzu_id'];
                $is_ok = false;
            }


            if ($is_ok) {

                $parameter = array(
                    'chat_id' => $chatid,
                    'parse_mode' => 'HTML',
                    'text' => "你没有当前在权限用户组内,请联系楚歌@fu_008添加权限",
                );
                $this->http_post_data('sendMessage', json_encode($parameter));
                exit();
            }

            $set_sql2 = "select * FROM pay_yonghuzu where typelist ='3' and id='" . $yonghuzu_id_data . "'";
            $order_query3 = $this->pdo->query($set_sql2);
            $order_info3 = $order_query3->fetchAll();

            if (empty($order_info3[0]['mingling'])) {
                $parameter = array(
                    'chat_id' => $chatid,
                    'parse_mode' => 'HTML',
                    'text' => "当前用户组没有此项权限,请联系楚歌@fu_008添加",
                );
                $this->http_post_data('sendMessage', json_encode($parameter));
                exit();
            }
            $all_mingling_arr = explode(",", $order_info3[0]['mingling']);
            if (!in_array($quanxian, $all_mingling_arr)) {
                $parameter = array(
                    'chat_id' => $chatid,
                    'parse_mode' => 'HTML',
                    'text' => "你没有当前   <b>" . $quanxian . "</b>   操作此命令,请联系楚歌@fu_008添加",
                );
                $this->http_post_data('sendMessage', json_encode($parameter));
                exit();
            }

        }
    }



    //系统后台：
    public function start_hou($chatid)
    {
        $keyboard2 = [
            'keyboard' => [
                [

                    ['text' => '补单'],
                    ['text' => '投诉'],
                    ['text' => '账单'],
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
}

$oen = new five();
$oen->index();

?>
