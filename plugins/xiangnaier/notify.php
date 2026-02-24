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
$_REQUEST = json_decode(file_get_contents('php://input'),true);
//md5(订单状态+商务号+商户订单号+支付金额+商户秘钥)

        $returnArray = array( // 返回字段
            "merid" => $_REQUEST["merid"], // 商户ID
            "payid" =>  $_REQUEST["payid"], // 订单号
            "tradeid" =>  $_REQUEST["tradeid"], // 交易金额
            "reqid" =>  $_REQUEST["reqid"], // 交易时间
            "money" =>  $_REQUEST["money"], // 流水号
            "status" => $_REQUEST["status"]
        );
        //签名：(md5(merid+reqid+payid+tradeid+money+status+key))
        //签名：(md5(merid+reqid+payid+tradeid+money+status+key))
        $sign = md5($_REQUEST["merid"].$_REQUEST["reqid"].$_REQUEST["payid"].$_REQUEST["tradeid"].$_REQUEST["money"].$_REQUEST["status"].$md5key);
        //echo $_REQUEST["merid"].$_REQUEST["reqid"].$_REQUEST["payid"].$_REQUEST["tradeid"].$_REQUEST["money"].$_REQUEST["status"].$md5key;
        //exit();
        if($sign == $_REQUEST["sign"]){
			 $flag = true;
        }else {
            exit('签名校验错误');
        }

        $returnArray['name'] ='香奶儿支付';
        $out_trade_no = daddslashes($returnArray["reqid"]);
        //日志开始
	$shuju = $returnArray;
	$Money = $_REQUEST["money"];
	$shuju['hebing']=$md5str . "key=" . $md5key;
    $shuju['mysign']=$sign;
	$shuju['ordersign']=$_REQUEST["sign"];
	$shuju['md5key']=$md5key;
	$shuju1 = json_encode($shuju,true);
	$DB->exec("INSERT INTO `pre_notify_rizhi` (`content`,`addtime`, `jine`,`zt`) VALUES (:content,:addtime, :jine,:zt)", [':content'=>$shuju1,':addtime'=>time(),':jine'=>$Money,':zt'=>'ok']);

if($flag){
    //处理逻辑
	$trade_no = $out_trade_no;
	$date = date("Y-m-d H:i:s");
	$orderAmt = $Money;
	//echo "update `pre_order` set `api_trade_no` ='$trade_no',`endtime` ='$date',`date` =NOW(),`randmoney` = $orderAmt where `trade_no`='$out_trade_no'";
		if($DB->exec("update `pre_order` set `status` ='1' where `trade_no`='$out_trade_no'")){
			//echo "$orderAmt";
				$DB->exec("update `pre_order` set `api_trade_no` ='$trade_no',`endtime` ='$date',`date` =NOW(),`randmoney` = $orderAmt where `trade_no`='$out_trade_no'");
				$DB->exec("update `pay_rand` set `status` ='1',`orderno` ='0',`url` = '0', `reorder` = '' where `orderno`='$out_trade_no'");
				file_put_contents("./haoyunpay.txt",date("Y-m-d H:i:s")." 准备处理订单信息: ".json_encode($order). PHP_EOL,FILE_APPEND);
				file_put_contents("./haoyunpay.txt","==========================". PHP_EOL,FILE_APPEND);
				processOrder($order);
			}
   echo "OK";
   exit();
}else{
    $DB->exec("INSERT INTO `pre_ordererror` (`content`,`order_sn`, `createtime`) VALUES (:content,:order_sn, :createtime)", [':content'=>$shuju1,':order_sn'=>$_REQUEST["orderid"],':createtime'=>time()]);
}

file_put_contents('./shibai.txt',file_get_contents('php://input'));
exit('sign error');

?>