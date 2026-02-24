<?php
 
if(!defined('IN_PLUGIN'))exit();
file_put_contents("./haoyunpay.txt","==========================". PHP_EOL,FILE_APPEND);
file_put_contents("./haoyunpay.txt",date("Y-m-d H:i:s")." 上游回调信息: ".json_encode($_POST). PHP_EOL,FILE_APPEND);

require 'pay/config.php';
header('Content-type:text/html;charset=utf-8');
file_put_contents('./demo.txt',file_get_contents('php://input'));
//mid=220933019&orderid=2022092217144835021&ordernumber=2022092217144981571011&amount=100.0000&datetime=20220922172105&code=1&sign=381e840c992aef2946365c516ed6ea7a
$returnArray = array( // 返回字段
    "mid" => $_REQUEST["mid"], // 商户ID
    "orderid" =>  $_REQUEST["orderid"], // 订单号
    "amount" =>  $_REQUEST["amount"], // 交易金额
    "ordernumber" =>  $_REQUEST["ordernumber"], // 交易时间
    "datetime" =>  $_REQUEST["datetime"], // 流水号
    "code"=>$_REQUEST['code']
);
$changes_rul = $channel['huidiaourl'];
ksort($returnArray);
$md5str = "";
foreach ($returnArray as $key => $val) {
    $md5str = $md5str . $key . "=" . $val . "&";
}
$sign = strtolower(md5($md5str ."key=". $md5key)); 
if($sign == $_REQUEST["sign"]){
    if($_REQUEST['code']=="1"){
        $flag = true;
    }else{
        exit('签名校验错误');
    }
}else {
    exit('签名校验错误');
}
\lib\Zhifu::csasahangss(1,json_encode($returnArray),"健力宝支付","回调");
$url = $changes_rul."huidiao.php";
if($flag){
    //处理逻辑
    $out_trade_no = $_REQUEST["orderid"];
	$date = date("Y-m-d H:i:s");  
	$trade_no = $out_trade_no;
	$orderAmt = $_REQUEST['amount'];
    if($DB->exec("update `pre_order` set `status` ='1' where `trade_no`='$out_trade_no'")){
	    $DB->exec("update `pre_order` set `api_trade_no` ='$trade_no',`endtime` ='$date',`date` =NOW(),`randmoney` = $orderAmt where `trade_no`='$out_trade_no'");
		$DB->exec("update `pay_rand` set `status` ='1',`orderno` ='0',`url` = '0', `reorder` = '' where `orderno`='$out_trade_no'");
        $changs =  processOrder($order);
		$parameter = array(
           'chat_id' => "-1001723124288",
           'parse_mode' => 'HTML',
           'text' => "notify_order_no==".$trade_no
        );
        $chengs2 =http_post_data('sendMessage', json_encode($parameter));
          echo 'success';
        exit();
         header("Location: $url");
        exit();
	}
  echo 'success';
exit();
    header("Location: $url");
    exit();
}else{
  echo 'no success';

exit();
}
?>