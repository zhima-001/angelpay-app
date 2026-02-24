<?php
if(!defined('IN_PLUGIN'))exit();
file_put_contents("./haoyunpay.txt","==========================". PHP_EOL,FILE_APPEND);
file_put_contents("./haoyunpay.txt",date("Y-m-d H:i:s")." 上游回调信息: ".json_encode($_POST). PHP_EOL,FILE_APPEND);
$mishi = $_POST['mishi'];


require 'pay/config.php';

header('Content-type:text/html;charset=utf-8');
file_put_contents('./demo.txt',file_get_contents('php://input'));


if($_REQUEST["orderid"]){
     $DB->exec("INSERT INTO `pre_orderinfo` (`content`,`order_sn`, `createtime`,`status`) VALUES (:content,:order_sn, :createtime,:status)", [':content'=>json_encode($_REQUEST),':order_sn'=>$_REQUEST['orderid'],':createtime'=>time(),':status'=>'0']);
}else{
    $DB->exec("INSERT INTO `pre_orderinfo` (`content`,`order_sn`, `createtime`,`status`) VALUES (:content,:order_sn, :createtime,:status)", [':content'=>json_encode($_REQUEST),':order_sn'=>"",':createtime'=>time(),':status'=>'6']);
}


//flag = verify($_POST,$md5Key, $ptKey);
$flag = false;

//md5(订单状态+商务号+商户订单号+支付金额+商户秘钥)

        $returnArray = array( // 返回字段
            "merchantid" => $_REQUEST["merchantid"], // 商户ID
            "orderid" =>  $_REQUEST["orderid"], // 订单号
            "merchantorderid" =>  $_REQUEST["merchantorderid"], // 交易金额
            "status" =>  $_REQUEST["status"], // 交易时间
            "applyamount" =>  $_REQUEST["applyamount"], // 流水号
          
        );
      
        ksort($returnArray);
        reset($returnArray);
        $md5str = "";
        foreach ($returnArray as $key => $val) {
            $md5str = $md5str . $key . "=" . $val . "&";
        }
        
        //echo($md5str . "key=" . $Md5key);
        $sign = md5($md5str . "key=" . $md5key);
        if($sign == $_REQUEST["sign"]){
          
            if ($_REQUEST["status"] == "2") {
             
			    $flag = true;
		    }
        }
	    
        $returnArray['name'] ='诺手支付';
        $out_trade_no = daddslashes($returnArray["merchantorderid"]);
        //日志开始
	$shuju = $returnArray;
	$Money = $_REQUEST["applyamount"];
	$shuju['hebing']=$md5str . "key=" . $md5key;
    $shuju['mysign']=$sign;
	$shuju['ordersign']=$_REQUEST["sign"];
	$shuju['md5key']=$md5key;
	$shuju1 = json_encode($shuju,true);
	$DB->exec("INSERT INTO `pre_notify_rizhi` (`content`,`addtime`, `jine`,`zt`) VALUES (:content,:addtime, :jine,:zt)", [':content'=>$shuju1,':addtime'=>time(),':jine'=>$Money,':zt'=>'ok']);

if($flag){
    //处理逻辑
	$trade_no = $returnArray['orderid'];
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
	
    exit('success');
}

file_put_contents('./shibai.txt',file_get_contents('php://input'));
exit('sign error');

?>