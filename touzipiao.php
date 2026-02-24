<?php



class Http
{

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


    public static function get($url, $params = [], $options = [])
    {
        $req = self::sendRequest($url, $params, 'GET', $options);
        return $req['ret'] ? $req['msg'] : '';
    }

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
$type = $_POST['paytype'];
$huidiaourl =  $_POST['notify_url'];
$returnurl = $_POST['return_url'];
$mysgin = "";
$domain = "";
$pid = "1";
//$money = $_POST['money'];
$money = $_POST['money'];

require './includes/common.php';
$xiadanUrl =$conf['localurl'];
// 从 config.txt 获取 xiadan 对应的 URL

/*
array(10) {
  ["clientip"]=>
  string(9) "127.0.0.1"
  ["money"]=>
  string(3) "100"
  ["name"]=>
  string(3) "VIP"
  ["notify_url"]=>
  string(38) "http://cny.eshuzi.top/api/index/notify"
  ["out_trade_no"]=>
  string(21) "czYLSZZH1681540689229"
  ["paytype"]=>
  string(6) "alipay"
  ["pid"]=>
  string(4) "1000"
  ["return_url"]=>
  string(38) "http://cny.eshuzi.top/api/index/notify"
  ["sign_type"]=>
  string(3) "MD5"
  ["sign"]=>
  string(32) "2c7a0759aa841ffbac286d09a9c5f75c"
}

clientip=127.0.0.1&money=100&name=VIP&notify_url=http://ccc.eshuzi.top/api/index/notify&out_trade_no=czYLSZZH1681558647239&pid=1000&return_url=http://ccc.eshuzi.top/api/index/notify&type=alipayOLX80wBVaF80xElzuuWARoEXzrvpjAbr

*/

$queryArr = array(
        'pid' => $_POST['pid'],
         'type' => $_POST['type'],
        'out_trade_no' =>$_POST['out_trade_no'],
        'notify_url' => $huidiaourl,
        'return_url' => $returnurl,
        'name' =>$_POST['name'],
       
        'clientip' => $_POST['clientip'],
         'money' =>  $_POST['money'],
       
); 

$appkey = "yP0dzJmh0Vje8L8pGg80hPqc8HHgyHgG";

ksort($queryArr);
reset($queryArr);
$arg  = "";
foreach ($queryArr as $key=>$val) {
    $arg.=$key."=".$val."&";
}
//去掉最后一个&字符
$arg = substr($arg,0,-1);

$sign = md5($arg.$appkey);

if($_POST['sign']!=$sign){
    $return_arr = array("code" => "201", "msg" => "sign error");
    echo json_encode($return_arr);
    exit;
}
$queryArr['sitename']="telegram测试下单";
$queryArr['terminals']="APP"; 


$nosession = true;
require './includes/common.php';



$out_trade_no = daddslashes($queryArr['out_trade_no']);


$terminals = $queryArr['terminals'];

use \lib\PayUtils;
      
$pid = intval($queryArr['pid']);

$userrow = $DB->query("SELECT * FROM `pre_user` WHERE `uid`='{$pid}' LIMIT 1")->fetch();
$userstype = $userrow['userstype'];

$type = daddslashes($queryArr['type']);
$notify_url = htmlspecialchars(daddslashes($queryArr['notify_url']));
$return_url = htmlspecialchars(daddslashes($queryArr['return_url']));
$name = htmlspecialchars(daddslashes($queryArr['name']));
$money = daddslashes($queryArr['money']);
$sitename = urlencode(base64_encode($queryArr['sitename']));

$stype = 0;

$domain = "";

$trade_no = date("YmdHis") . rand(11111, 99999);

$user = $pid;

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
   $return_arr = array("code" => "201", "pay_url" => "");
    echo  json_encode($return_arr);
    exit;
}

$return_arr = array("code" => "200", "pay_url" => $xiadanUrl . "/pay/".$submitData['plugin']."/qrcode/".$trade_no."/");
echo  json_encode($return_arr);
exit;

?>
