<?php
require 'pay/config.php';

$flag = false;
// $_REQUEST = json_decode(file_get_contents('php://input'),true);
//http://ceshi.freewing1688.xyz/pay/bingtangxueli/notify/2022092214542761215/?income=10000&payOrderId=P01202209221454294570151&amount=10000&mchId=1105&productId=8032&mchOrderNo=2022092214542761215&paySuccTime=&sign=F79B0E6FA485A9EFE9469EB11E75974F&channelOrderNo=0220922145443485368096&backType=2&reqTime=20220922155704&param1=&param2=&appId=&status=2
$returnArray = array( // 返回字段
    "income"=>$_REQUEST['income'],
    "payOrderId" => $_REQUEST["payOrderId"], // 商户ID
        "amount" =>  $_REQUEST["amount"], // 流水号
      "mchId" =>  $_REQUEST["mchId"], // 订单号
      "productId" =>  $_REQUEST["productId"], // 交易金额
    "mchOrderNo" =>  $_REQUEST["mchOrderNo"], // 交易时间
    'channelOrderNo'=>$_REQUEST['channelOrderNo'],
    "backType"=>$_REQUEST['backType'],
    "reqTime"=>$_REQUEST['reqTime'],
    "status"=>$_REQUEST['status'],



);
ksort($returnArray);
$md5str = "";
foreach ($returnArray as $key => $val) {
    $md5str = $md5str . $key . "=" . $val . "&";
}
$sign = strtoupper(md5($md5str ."key=". $md5key)); 
if($sign == $_REQUEST["sign"]){
    if($_REQUEST['status']=="2"){
        $flag = true;
    }else{
        exit('签名校验错误');
    }
}else {
    exit('签名校验错误');
}
\lib\Zhifu::csasahangss(1,json_encode($returnArray),"冰糖雪梨支付","回调");
if($flag){
    //处理逻辑
    $out_trade_no =  $_REQUEST["mchOrderNo"];
    $Money = $_REQUEST['amount']/100;
	$date = date("Y-m-d H:i:s");  
	$trade_no = $out_trade_no;
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
          echo "OK";
          exit();
	}
    echo "OK";
    exit();
}else{
    exit('NO OK');
}
?>