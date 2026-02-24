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

function aes_decrypt($data)
    {
      
        $key = "fAZaDMiKTgqHzxcJ";
        $encrypted = base64_decode($data);
       
        return openssl_decrypt($encrypted, 'AES-128-ECB', $key, OPENSSL_RAW_DATA);
    }
$data=$_REQUEST['content'];
$key = "fAZaDMiKTgqHzxcJ";
$encrypted = base64_decode($data);
       
$contents= json_decode(openssl_decrypt($encrypted, 'AES-128-ECB', $key, OPENSSL_RAW_DATA),true);
//$contents = aes_decrypt($_REQUEST['content']);

$returnArray = array( // 返回字段
    "orderno" =>  $_REQUEST["orderno"], // 流水号
    "mno"=>$_REQUEST['mno'],
);
$sign_pp = $contents['sign'];
unset($contents['sign']);
//去除空字段
$param = array_filter($contents);
//参数排序
ksort($contents);

$nativepp = urldecode(http_build_query($contents) . "&key=" . $md5key);

$sign = md5($nativepp);


if($sign == $sign_pp){
    if($contents['status']=="1"){
        $flag = true;
    }else{
        exit('签名校验错误');
    }
}else {
    exit('签名校验错误');
}
\lib\Zhifu::csasahangss(1,json_encode($returnArray),"润达医药支付","回调");
if($flag){
    //处理逻辑
    $out_trade_no  = $contents['orderno'];
   
    $Money =  $contents['amount'];
	$date = date("Y-m-d H:i:s");  
	$trade_no = $contents['c_orderno'];
	$orderAmt = $Money;
    if($DB->exec("update `pre_order` set `status` ='1' where `trade_no`='$out_trade_no'")){
	    $DB->exec("update `pre_order` set `api_trade_no` ='$trade_no',`endtime` ='$date',`date` =NOW(),`randmoney` = $orderAmt where `trade_no`='$out_trade_no'");
		$DB->exec("update `pay_rand` set `status` ='1',`orderno` ='0',`url` = '0', `reorder` = '' where `orderno`='$out_trade_no'");
        processOrder($order);
		$parameter = array(
         'chat_id' => $conf['bchatid'], 
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