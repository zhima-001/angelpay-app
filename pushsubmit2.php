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
$queryArr = $_POST;

$nosession = true;
require './includes/common.php';



$out_trade_no = daddslashes($queryArr['out_trade_no']);


$terminals = $queryArr['terminals'];


//写入日志信息
$url1 = (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443') ? 'https://' : 'http://';
$server_name = $url1 . $_SERVER['HTTP_HOST'];
$c['pid'] = isset($queryArr['pid']) ? $queryArr['pid'] : 0;
$c['ddh'] = 0;
$c['henji'] = date("Y-m-d H:i:s", time()) . '&nbsp;&nbsp;商户' . $c['pid'] . '访问' . $server_name . $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING'] . "<br>";
$c['getinfo'] = isset($queryArr) ? json_encode($queryArr) : '';
$c['ip'] = $clientip;
$c['addtime'] = time();


use \lib\PayUtils;
      
$pid = intval($queryArr['pid']);
if (empty($pid)) {
    //sysmsg('PID不存在');
    $return_arr = array("code" => "201", "msg" => "Pid does not exist");
    echo json_encode($return_arr);
    exit();
}
$userrow = $DB->query("SELECT `uid`,`gid`,`key`,`mode`,`pay`,`cert`,`status`,`userstype` FROM `pre_user` WHERE `uid`='{$pid}' LIMIT 1")->fetch();
$userstype = $userrow['userstype'];

if (!$userrow) {
    $return_arr = array("code" => "201", "msg" => "Merchant does not exist!");
    echo json_encode($return_arr);
    exit();
}
if ($userrow['status'] == 0 || $userrow['pay'] == 0) {
    $return_arr = array("code" => "201", "msg" => "Merchants have been banned and cannot be paid");
    echo json_encode($return_arr);
    exit;
}
$type = daddslashes($queryArr['type']);
$notify_url = htmlspecialchars(daddslashes($queryArr['notify_url']));
$return_url = htmlspecialchars(daddslashes($queryArr['return_url']));
$name = htmlspecialchars(daddslashes($queryArr['name']));
$money = daddslashes($queryArr['money']);
$sitename = urlencode(base64_encode($queryArr['sitename']));
if (!empty($queryArr['stype'])) {
    $stype = $queryArr['stype'];
} else {
    $stype = 0;
}


if (empty($out_trade_no)) {
    $return_arr = array("code" => "201", "msg" => "Order number (out_trade_no) can't be empty");
    echo json_encode($return_arr);
    exit;
}
if (empty($name)) {
    $return_arr = array("code" => "201", "msg" => "Product Name (Name) can't be empty");
    echo json_encode($return_arr);
    exit;
}
if (empty($money)) {
    $return_arr = array("code" => "201", "msg" => "Money can't be empty");
    echo json_encode($return_arr);
    exit;
}
if ($money <= 0 || !is_numeric($money) || !preg_match('/^[0-9.]+$/', $money)) {
    $return_arr = array("code" => "201", "msg" => "The amount is not legal");
    echo json_encode($return_arr);
    exit;
}
if (!preg_match('/^[a-zA-Z0-9.\_\-|]+$/', $out_trade_no)) {
    $return_arr = array("code" => "201", "msg" => "The order number (out_trade_no) is not correct ");
    echo json_encode($return_arr);
    exit;
}

$domain = $queryArr['domain'];

if ($conf['cert_force'] == 1 && $userrow['cert'] == 0) {
    $return_arr = array("code" => "201", "msg" => "Current merchants have not completed real-name certification, unable to pay");
    echo json_encode($return_arr);
    exit;
}

$trade_no = date("YmdHis") . rand(11111, 99999);


$user = $pid;

$userrow = $DB->getRow("SELECT * FROM pre_user WHERE uid='{$pid}' limit 1");


$tg ="Telegram";
date_default_timezone_set('Asia/Shanghai');
$now_time = date("Y-m-d H:i:s");
if (!$DB->exec("INSERT INTO pre_order (trade_no,out_trade_no,uid,addtime,name,money,notify_url,return_url,domain,ip,status,terminals,beizhu) VALUES (:trade_no, :out_trade_no, :uid, :addtime, :name, :money, :notify_url, :return_url, :domain, :clientip, 0,:terminals,:beizhu)", [':trade_no' => $trade_no, ':out_trade_no' => $out_trade_no, ':uid' => $pid, ':name' => $name, ':money' => $money, ':notify_url' => $notify_url, ':return_url' => $return_url, ':domain' => $domain, ':clientip' => $clientip, ':terminals' => $terminals, ':beizhu' => $tg, ':addtime' => $now_time])) {

    $return_arr = array("code" => "201", "msg" => "Failed to create order, please go back and try again");
    echo json_encode($return_arr);
    exit;
  

};



$DB->exec("UPDATE `pre_rizhi` SET `ddh` =:ddh WHERE `id`=:id", [':ddh' => $trade_no, ':id' => $int_id]);

if (empty($type)) {
    // echo "请求方式错误！";
    $return_arr = array("code" => "201", "msg" => "Request method error!");
    echo json_encode($return_arr);
    exit;

}

// 获取订单支付方式ID、支付插件、支付通道、支付费率
$submitData = \lib\Channel::submit_chang($type, $userrow['gid'], $money, $terminals, $stype, $userstype);
/* 
array(6) {
  ["typeid"]=>
  string(1) "1"
  ["typename"]=>
  string(6) "alipay"
  ["plugin"]=>
  string(7) "huangzi"
  ["channel"]=>
  string(3) "139"
  ["rate"]=>
  string(3) "100"
  ["apptype"]=>
  string(0) ""
}
*/
if($submitData){
	if($userrow['mode']==1){
		$realmoney = round($money*(100+100-$submitData['rate'])/100,2);
		$getmoney = $money;
	}else{
		$realmoney = $money;
		$getmoney = round($money*$submitData['rate']/100,2);
	}
	$DB->exec("UPDATE pre_order SET type='{$submitData['typeid']}',channel='{$submitData['channel']}',realmoney='$realmoney',getmoney='$getmoney' WHERE trade_no='$trade_no'");
}else{ //选择其他支付方式

    echo "异常！";
    exit();
}

/*$order['trade_no'] = $trade_no;
$order['out_trade_no'] = $out_trade_no;
$order['uid'] = $pid;
$order['addtime'] = $date;
$order['name'] = $name;
$order['money'] = $realmoney;
$order['type'] = $submitData['typeid'];
$order['channel'] = $submitData['channel'];
$order['typename'] = $submitData['typename'];
$order['apptype'] = explode(',', $submitData['apptype']);

$channel = \lib\Channel::get($order['channel']);*/



echo $submitData['plugin']."__".$trade_no;
exit;

$pay_url = "https://".$_SERVER['HTTP_HOST']."/pay/".$submitData['plugin']."/qrcode/{$trade_no}/?sitename={$sitename}";

//把数据给用户：

// echo $pay_url;
// exit();
        $post_data = array();
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
        $get_pay_url = trim(file_get_contents($pay_url, false, $context));
        



// $DB->exec("INSERT INTO `pre_order_tg` (`trade_no`,`out_trade_no`,`pay_url`,`creat_time`) VALUES (:trade_no, :out_trade_no, :pay_url, NOW())", [':trade_no' => $trade_no, ':out_trade_no' => $out_trade_no, ':pay_url' => $get_pay_url]);
echo $get_pay_url;
exit();
// $DB->exec("INSERT INTO `pre_order_tg` (`trade_no`,`out_trade_no`,`pay_url`,`creat_time`) VALUES (:trade_no, :out_trade_no, :pay_url, NOW())", [':trade_no' => $trade_no, ':out_trade_no' => $out_trade_no, ':pay_url' => $pay_url]);

?>
