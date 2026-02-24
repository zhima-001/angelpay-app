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
// $_REQUEST = json_decode(file_get_contents('php://input'),true);

//http://ceshi.freewing1688.xyz/pay/leikesasi/notify/2022112311170890689/?ifCode=payEco&amount=100&payOrderId=P29539662737408&mchOrderNo=2022112311170890689&subject=Merchandise&wayCode=ALI_WAP&sign=8977288C1E05F1AEE61DDC590D890DBF&channelOrderNo=007722112311171100198010003207&reqTime=1669173477953&body=Merchandise&createdAt=1669173431726&appId=637cb564e4b05ed52a1d41ed&clientIp=45.77.137.213&successTime=1669173478000&currency=cny&state=2&mchNo=M1669117284

$returnArray = array( // 返回字段
 "ifCode" =>  $_REQUEST["ifCode"], // 流水号
     "amount"=>$_REQUEST['amount'],
    "payOrderId" => $_REQUEST["payOrderId"], // 商户ID
     "mchOrderNo" =>  $_REQUEST["mchOrderNo"], // 交易时间
     "subject"=>$_REQUEST['subject'],
       "wayCode"=>$_REQUEST['wayCode'],
        "channelOrderNo"=>$_REQUEST['channelOrderNo'],
          "reqTime"=>$_REQUEST['reqTime'],
            "body"=>$_REQUEST['body'],
               "createdAt"=>$_REQUEST['createdAt'],
      "appId" =>  $_REQUEST["appId"], // 交易金额
          "clientIp" =>  $_REQUEST["clientIp"], // 订单号
               "successTime" =>  $_REQUEST["successTime"], // 订单号
                "currency"=>$_REQUEST['currency'],
                 "state"=>$_REQUEST['state'],
    "mchNo" =>  $_REQUEST["mchNo"], // 订单号

);
ksort($returnArray);
$md5str = "";
foreach ($returnArray as $key => $val) {
    $md5str = $md5str . $key . "=" . $val . "&";
}
$sign = strtoupper(md5($md5str ."key=". $md5key)); 
if($sign == $_REQUEST["sign"]){
    if($_REQUEST['state']=="2"){
        $flag = true;
    }else{
        exit('签名校验错误');
    }
}else {
    exit('签名校验错误');
}
\lib\Zhifu::csasahangss(1,json_encode($returnArray),"雷克萨斯支付","回调");
if($flag){
    //处理逻辑
    $out_trade_no  = $_REQUEST['mchOrderNo'];
    $Money =  $_REQUEST['amount']/100;
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