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



        $returnArray = array( // 返回字段
            "merchantNum" => $_REQUEST["merchantNum"], // 商户ID
            "orderNo" =>  $_REQUEST["orderNo"], // 订单号
            "platformOrderNo" =>  $_REQUEST["platformOrderNo"], // 交易金额
            "amount" =>  $_REQUEST["amount"], // 交易时间
            "actualPayAmount" =>  $_REQUEST["actualPayAmount"], // 流水号
            "state" => $_REQUEST["state"],
            "payTime" => $_REQUEST["payTime"]
        );
        //$md5key = "商户APIKEY"; //商户APIKEY,商户后台API管理获取
        ksort($returnArray);
        reset($returnArray);
        $md5str = "";
        foreach ($returnArray as $key => $val) {
            $md5str = $md5str . $key . "=" . $val . "&";
        }
        $sign = strtolower(md5($md5str . "key=" . $md5key)); 
        if ($sign == $_REQUEST["signAscll"]) {
                   $flag = true;
        }else{
              exit('订单签名不正确！');
        }




$returnArray['name'] ='陀螺战士支付';
$out_trade_no = daddslashes($returnArray["orderNo"]);
//日志开始
	$shuju = $returnArray;
	$Money = $_REQUEST["amount"];
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
