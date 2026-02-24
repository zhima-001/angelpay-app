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
//pid=9&trade_no=2025082514282920131&out_trade_no=2025082514282830743&type=alipay&name=product&money=100&trade_status=TRADE_SUCCESS&sign=88f3e4d1d1d15cfd2d870ec34145429c&sign_type=MD5
/*{
    "state": 0,
    "payState": 0,
    "data": "",
    "message": "交易成功",
    "merchantId": "daqiao123",
    "money": "100.0000",
    "timeSpan": "1764125511082",
    "orderNo": "2025112610480739340",
    "platOrderNo": "20251126104808504612088",
    "sign": "7C7BCE40813CFB6920C8BC20175DD559"
}*/
$_REQUEST = $_POST;
if (empty($_REQUEST)) {
    $_REQUEST = json_decode(file_get_contents('php://input'), true);
    if (empty($_REQUEST)) {
        $_REQUEST = $_GET;
    }
}

$returnArray = array( // 返回字段
        "message" =>  $data["message"], 
        "okordertime"=>$_REQUEST['okordertime'],
        "orderno"=>$_REQUEST['orderno'],
        "orderstatus"=>$_REQUEST['orderstatus'],
        "partnerid" =>  $_REQUEST["partnerid"], // 流水号
        "partnerorderid"=>$_REQUEST['partnerorderid'],
        "payamount" => $_REQUEST["payamount"], // 商户ID 
        "paytype"=>$_REQUEST['paytype'],
        "version" =>  $_REQUEST["version"], 
        //"sign_type"=>$_REQUEST['sign_type'],
);
ksort($returnArray);
$md5str = "";
foreach ($returnArray as $key => $val) {
    // if(!empty($val)){
        $md5str = $md5str . $key . "=" . $val . "&";
    // }
    
}

$sign = md5($md5str."key=".$md5key); 

if(1 == $_REQUEST["orderstatus"]){ 
    
        $flag = true;
    
}else {
    exit('签名校验错误');
}
\lib\Zhifu::csasahangss(1,json_encode($returnArray),"群兴玩具支付","回调");
if($flag){
    //处理逻辑
    $out_trade_no  = $_REQUEST['partnerorderid'];
    $Money =  $_REQUEST['payamount']/100;
	$date = date("Y-m-d H:i:s");  
	$trade_no = $_REQUEST['orderno'];
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