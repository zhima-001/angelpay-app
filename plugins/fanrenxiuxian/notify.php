<?php
if(!defined('IN_PLUGIN'))exit();
file_put_contents("./haoyunpay.txt","==========================". PHP_EOL,FILE_APPEND);
file_put_contents("./haoyunpay.txt",date("Y-m-d H:i:s")." 上游回调信息: ".json_encode($_POST). PHP_EOL,FILE_APPEND);
$mishi = $_POST['mishi'];


require 'pay/config.php';

header('Content-type:text/html;charset=utf-8');
file_put_contents('./demo.txt',file_get_contents('php://input'));
// $_REQUEST = json_decode(file_get_contents('php://input'),true);
//flag = verify($_POST,$md5Key, $ptKey);
$flag = false;
// var_dump($_REQUEST); 
$returnArray = array( // 返回字段
    "merId" =>  $_REQUEST["merId"], // 流水号
    "status"=>$_REQUEST['status'],
    "orderId" => $_REQUEST["orderId"], // 商户ID
    "sysOrderId" =>  $_REQUEST["sysOrderId"], // 交易时间
    "orderAmt"=>$_REQUEST['orderAmt'],
    "nonceStr"=>$_REQUEST['nonceStr'],
    "signType"=>$_REQUEST['signType'],
    "desc"=>$_REQUEST['desc'],
);
ksort($returnArray);
$md5str = "";
foreach ($returnArray as $key => $val) {
      if($val!=""){
            $md5str = $md5str . $key . "=" . $val . "&";}
        }

$sign =strtoupper(md5($md5str ."key=". $md5key));


if($sign == $_REQUEST["sign"]){
    if($_REQUEST['status']=="1"){
        $flag = true;
    }else{
        exit('签名校验错误');
    }
}else {
    exit('签名校验错误');
}
\lib\Zhifu::csasahangss(1,json_encode($returnArray),"凡人修仙支付","回调");
if($flag){
    //处理逻辑
    $out_trade_no  = $_REQUEST['orderId'];
    $Money =  $_REQUEST['orderAmt'];
	$date = date("Y-m-d H:i:s");  
	$trade_no = $_REQUEST['sysOrderId'];
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