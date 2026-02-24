<?php

if(!defined('IN_PLUGIN'))exit();
file_put_contents("./haoyunpay.txt","==========================". PHP_EOL,FILE_APPEND);
file_put_contents("./haoyunpay.txt",date("Y-m-d H:i:s")." 上游回调信息: ".json_encode($_POST). PHP_EOL,FILE_APPEND);
$mishi = $_POST['mishi'];


require 'pay/config.php';




file_put_contents('./demo.txt',file_get_contents('php://input'));

//$info = file_get_contents('php://input');


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

$mysign = md5($fxstatus . $fxid . $fxddh . $fxfee . $md5key); //验证签名
//记录回调数据到文件，以便排错
if ($fxloaderror == 1)
    file_put_contents('./demo.txt', '异步：' . serialize($_REQUEST) . "\r\n", FILE_APPEND);

if ($fxsign == $mysign) {
    if ($fxstatus == '1') {//支付成功
        //支付成功 更改支付状态 完善支付逻辑
       $flag = true;
        //echo 'success';
    } else { //支付失败
        echo 'fail';
        exit();
    }
} else {
    echo 'sign error';
     exit();
}



$returnArray = array( // 返回字段
            "fxid" => $_REQUEST["fxid"], // 商户ID
            "fxddh" =>  $_REQUEST["fxddh"], // 订单号
            "fxorder" =>  $_REQUEST["fxorder"], // 交易金额
            "fxdesc" =>  $_REQUEST["fxdesc"], // 交易时间
            "fxfee" =>  $_REQUEST["fxfee"], // 支付流水号
            "fxattch" => $_REQUEST["fxattch"],
        );
       




$returnArray['name'] ='奥巴马支付';
$out_trade_no = daddslashes($fxddh);
//日志开始
	$shuju = $returnArray;
	$Money = $_REQUEST["fxfee"];
	$shuju1 = json_encode($shuju,true);
	$DB->exec("INSERT INTO `pre_notify_rizhi` (`content`,`addtime`, `jine`,`zt`) VALUES (:content,:addtime, :jine,:zt)", [':content'=>$shuju1,':addtime'=>time(),':jine'=>$Money,':zt'=>'ok']);

if($flag){
    //处理逻辑

	$orderAmt = $Money;
	$date = date("Y-m-d H:i:s");
	//echo "update `pre_order` set `api_trade_no` ='$trade_no',`endtime` ='$date',`date` =NOW(),`randmoney` = $orderAmt where `trade_no`='$out_trade_no'";
		if($DB->exec("update `pre_order` set `status` ='1' where `trade_no`='$out_trade_no'")){
			//echo "$orderAmt";
				$DB->exec("update `pre_order` set `api_trade_no` ='$trade_no',`endtime` ='$date',`date` =NOW(),`randmoney` = $orderAmt where `trade_no`='$out_trade_no'");
				$DB->exec("update `pay_rand` set `status` ='1',`orderno` ='0',`url` = '0', `reorder` = '' where `orderno`='$out_trade_no'");
				file_put_contents("./haoyunpay.txt",date("Y-m-d H:i:s")." 准备处理订单信息: ".json_encode($order). PHP_EOL,FILE_APPEND);
				file_put_contents("./haoyunpay.txt","==========================". PHP_EOL,FILE_APPEND);
				processOrder($order);
			}
    exit('success');
}
file_put_contents('./shibai.txt',file_get_contents('php://input'));
exit('sign error');

?>
