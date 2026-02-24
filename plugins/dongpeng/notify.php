<?php
require 'pay/config.php';
$flag = false;
$_REQUEST = json_decode(file_get_contents('php://input'),true);
$returnArray = array( // 返回字段
    "Timestamp" => $_REQUEST["Timestamp"], // 商户ID
    "AccessKey" =>  $_REQUEST["AccessKey"], // 订单号
    "Amount" =>  $_REQUEST["Amount"], // 交易金额
    "Status" =>  $_REQUEST["Status"], // 交易时间
    "OrderNo" =>  $_REQUEST["OrderNo"], // 流水号
);
ksort($returnArray);
$md5str = "";
foreach ($returnArray as $key => $val) {
    $md5str = $md5str . $key . "=" . $val . "&";
}
$sign = strtolower(md5($md5str ."SecretKey=". $md5key)); 
if($sign == $_REQUEST["Sign"]){
    if($_REQUEST['Status']=="4"){
        $flag = true;
    }else{
        exit('签名校验错误');
    }
}else {
    exit('签名校验错误');
}
\lib\Zhifu::csasahangss(1,json_encode($returnArray),"东鹏支付","回调");
if($flag){
    //处理逻辑
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
        exit('ok');
	}
	exit('ok');
}else{
    exit('no ok');
}
?>