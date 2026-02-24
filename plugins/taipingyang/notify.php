<?php
if(!defined('IN_PLUGIN'))exit();
file_put_contents("./haoyunpay.txt","==========================". PHP_EOL,FILE_APPEND);
file_put_contents("./haoyunpay.txt",date("Y-m-d H:i:s")." 上游回调信息: ".json_encode($_POST). PHP_EOL,FILE_APPEND);
$mishi = $_POST['mishi'];

//money=50.00&pt_order=ZZHF20230823161324728&sh_order=2023082316132391607&status=success&time=1692778472&sign=ba47275ef588202896f25af11ecc9cf8&
require 'pay/config.php';

header('Content-type:text/html;charset=utf-8');
file_put_contents('./demo.txt',file_get_contents('php://input'));

//flag = verify($_POST,$md5Key, $ptKey);
$flag = false;

$returnArray = array( // 返回字段
    "sh_order" =>  $_REQUEST["sh_order"], // 流水号
    "pt_order"=>$_REQUEST['pt_order'],
    "money" => $_REQUEST["money"], // 商户ID
   // "old_money" =>  $_REQUEST["old_money"], // 交易时间
    "time"=>$_REQUEST['time'],
    "status"=>$_REQUEST['status'],


);
ksort($returnArray);
$md5str = "";
foreach ($returnArray as $key => $val) {
    $md5str = $md5str . $key . "=" . $val . "&";
}

$sign =md5($md5str."key=". $md5key); 


if($sign == $_REQUEST["sign"]){
    if($_REQUEST['status']=="success"){
        $flag = true;
    }else{
        exit('签名校验错误');
    }
}else {
    exit('签名校验错误');
}
\lib\Zhifu::csasahangss(1,json_encode($returnArray),"太平洋支付","回调");
if($flag){
    //处理逻辑
    $out_trade_no  = $_REQUEST['sh_order'];
    $Money =  $_REQUEST['money'];
	$date = date("Y-m-d H:i:s");  
	$trade_no = $_REQUEST['pt_order'];
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