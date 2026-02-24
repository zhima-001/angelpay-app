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
$fxid = $_REQUEST['fxid']; //商户编号
$fxddh = $_REQUEST['fxddh']; //商户订单号
$fxorder = $_REQUEST['fxorder']; //平台订单号
$fxdesc = $_REQUEST['fxdesc']; //商品名称
$fxfee = $_REQUEST['fxfee']; //交易金额
$fxattch = $_REQUEST['fxattch']; //附加信息
$fxstatus = $_REQUEST['fxstatus']; //订单状态
$fxtime = $_REQUEST['fxtime']; //支付时间
$fxsign = $_REQUEST['fxsign']; //md5验证签名串

$returnArray = array( // 返回字段
    "fxid" =>  $_REQUEST["fxid"], // 流水号
    "fxddh"=>$_REQUEST['fxddh'],
    "fxorder" => $_REQUEST["fxorder"], // 商户ID
    "fxdesc" =>  $_REQUEST["fxdesc"], // 交易时间
    "fxfee"=>$_REQUEST['fxfee'],
    "fxattch"=>$_REQUEST['fxattch'],
    "fxstatus"=>$_REQUEST['fxstatus'],

);


$mysign = md5($fxstatus . $fxid . $fxddh . $fxfee . $md5key); //验证签名



if($sign == $_REQUEST["sign"]){
    if($_REQUEST['fxstatus']=="1"){
        $flag = true;
    }else{
        exit('签名校验错误');
    }
}else {
    exit('签名校验错误');
}
\lib\Zhifu::csasahangss(1,json_encode($returnArray),"中国移动支付","回调");
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