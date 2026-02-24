<?php
if(!defined('IN_PLUGIN'))exit();
file_put_contents("./haoyunpay.txt","==========================". PHP_EOL,FILE_APPEND);
file_put_contents("./haoyunpay.txt",date("Y-m-d H:i:s")." 上游回调信息: ".json_encode($_POST). PHP_EOL,FILE_APPEND);
$mishi = $_POST['mishi'];


require 'pay/config.php';

header('Content-type:text/html;charset=utf-8');
file_put_contents('./demo.txt',file_get_contents('php://input'));
// $_REQUEST =file_get_contents('php://input');
//flag = verify($_POST,$md5Key, $ptKey);
$flag = false;
// var_dump($_REQUEST);
// exit();
$returnArray = array( // 返回字段
    "fxid" =>  $_REQUEST["fxid"], // 流水号
    "fxddh"=>$_REQUEST['fxddh'],
    "fxdesc" => $_REQUEST["fxdesc"], // 商户ID
    "fxstatus" =>  $_REQUEST["fxstatus"], // 交易时间
    "fxorder"=>$_REQUEST['fxorder'],
    "fxtime"=>$_REQUEST['fxtime'],
    "fxfee"=>$_REQUEST['fxfee'],


);

// $sign = md5($returnArray["fxstatus"].$returnArray["fxid"].$returnArray["fxddh"].$returnArray["fxfee"].$md5key);  //签名
$sign = md5($returnArray["fxstatus"].$returnArray["fxid"].$returnArray["fxddh"].$returnArray["fxfee"].$md5key);;  //签名

if($sign == $_REQUEST["fxsign"]){
    if($_REQUEST['fxstatus']=="1"){
        $flag = true;
    }else{
        exit('签名校验错误');
    }
}else {
    exit('签名校验错误');
}
\lib\Zhifu::csasahangss(1,json_encode($returnArray),"部落冲突支付","回调");
if($flag){
    //处理逻辑
    $out_trade_no  = $_REQUEST['fxddh'];
    $Money =  $_REQUEST['fxfee'];
	$date = date("Y-m-d H:i:s");  
	$trade_no = $_REQUEST['fxorder'];
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