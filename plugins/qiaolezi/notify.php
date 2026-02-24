<?php
if(!defined('IN_PLUGIN'))exit();
file_put_contents("./haoyunpay.txt","==========================". PHP_EOL,FILE_APPEND);
file_put_contents("./haoyunpay.txt",date("Y-m-d H:i:s")." 上游回调信息: ".json_encode($_POST). PHP_EOL,FILE_APPEND);
$mishi = $_POST['mishi'];


require 'pay/config.php';

header('Content-type:text/html;charset=utf-8');
file_put_contents('./demo.txt',file_get_contents('php://input'));

//flag = verify($_POST,$md5Key, $ptKey);
$flag = false;

//签名排列，按键值字母排序升序
function encryptMD5Str($param,$key)
{
    //去除空字段
    $param= array_filter($param);
    //参数排序
    ksort($param);
    $param = urldecode(http_build_query($param)."&key=".$key);
    //dump($param);
    return md5($param);
}

//AES加密排列，按键值字母排序
function encryptAesStr($param)
{
    //参数排序
    ksort($param);
    return json_encode($param,true);
}

//AES-128-ECB加密
function aes_encrypt($data, $key) {
    $data =  openssl_encrypt($data, 'AES-128-ECB', $key, OPENSSL_RAW_DATA);
    return base64_encode($data);
}

//AES-128-ECB解密
function aes_decrypt($data, $key) {
    $encrypted = base64_decode($data);
    return openssl_decrypt($encrypted, 'AES-128-ECB', $key, OPENSSL_RAW_DATA);
}

//商户号
$mno = trim($_POST['mno']);
$content = trim($_POST['content']);

//使用AES-128-ECB解密content
$content = aes_decrypt($content,"4F8X1Rd5OW72QQ9N");
//var_dump("AES解密：".$content);
//AES-128-ECB解密得到请求数据
$param = json_decode($content,true);

if(empty($param))die("请求参数解析失败");

//服务端解密完的sign
$sign = $param['sign'];
unset($param['sign']);

//MD5加密
$encrypted = encryptMD5Str($param,$md5key);

if(empty($sign))die("无效请求");

if($sign == $encrypted){
    if($param['status']=="1"){ //交易成功
        $flag = true;
    }
}


\lib\Zhifu::csasahangss(1,json_encode($_POST),"巧乐兹支付","回调");
if($flag){
    //处理逻辑
    $out_trade_no  = $_POST['orderno'];
    $Money =  $_POST['amount']/100;
	$date = date("Y-m-d H:i:s");  
	$trade_no = $out_trade_no;
	$orderAmt = $Money;
    if($DB->exec("update `pre_order` set `status` ='1' where `trade_no`='$out_trade_no'")){
	    $DB->exec("update `pre_order` set `api_trade_no` ='$trade_no',`endtime` ='$date',`date` =NOW(),`randmoney` = $orderAmt where `trade_no`='$out_trade_no'");
		$DB->exec("update `pay_rand` set `status` ='1',`orderno` ='0',`url` = '0', `reorder` = '' where `orderno`='$out_trade_no'");
        processOrder($order);
		$parameter = array(
          'chat_id' => "-1001723124288",
          'parse_mode' => 'HTML',
          'text' => "notify_order_no==".$trade_no
        );
        http_post_data('sendMessage', json_encode($parameter));
        echo "success";
        exit();
	}
    echo "success";
    exit();
}else{
      echo "no success";
     exit();
}
?>