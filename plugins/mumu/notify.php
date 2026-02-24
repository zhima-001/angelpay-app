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
            "out_trade_id" => $_REQUEST["out_trade_id"], // 商户ID
            "transaction_id" =>  $_REQUEST["transaction_id"], // 订单号
            "status" =>  $_REQUEST["status"], // 交易金额
            "merchant_id" =>  $_REQUEST["merchant_id"], // 交易时间
            "money" =>  $_REQUEST["money"], // 流水号
  
        );
      
       ksort($returnArray);
        foreach ($returnArray as $key => $item) {
            if (!empty($item)) {         //剔除参数值为空的参数
                $newArr[] = $key . '=' . $item;     // 整合新的参数数组
            }
        }
        $stringA = implode("&", $newArr);         //使用 & 符号连接参数
        //echo($md5str . "key=" . $Md5key);
        $sign = strtolower(md5($stringA . $md5key));
        if($sign == $_REQUEST["sign"]){
           
		    $flag = true;
        }else {
          
            exit('签名校验错误');
        }
	    
        $returnArray['name'] ='木木支付';
        $out_trade_no = daddslashes($returnArray["out_trade_id"]);
        //日志开始
	$shuju = $returnArray;
	$Money = $_REQUEST["money"];

	$shuju1 = json_encode($shuju,true);
	$DB->exec("INSERT INTO `pre_notify_rizhi` (`content`,`addtime`, `jine`,`zt`) VALUES (:content,:addtime, :jine,:zt)", [':content'=>$shuju1,':addtime'=>time(),':jine'=>$Money,':zt'=>'ok']);

if($flag){
    //处理逻辑
	
	$orderAmt = $Money;
	$trade_no =  $_REQUEST["out_trade_id"];
	$out_trade_no =  $_REQUEST["out_trade_id"];
	//echo "update `pre_order` set `api_trade_no` ='$trade_no',`endtime` ='$date',`date` =NOW(),`randmoney` = $orderAmt where `trade_no`='$out_trade_no'";
		if($DB->exec("update `pre_order` set `status` ='1' where `trade_no`='$out_trade_no'")){
			//echo "$orderAmt";
				$DB->exec("update `pre_order` set `api_trade_no` ='$trade_no',`endtime` ='$date',`date` =NOW(),`randmoney` = $orderAmt where `trade_no`='$out_trade_no'");
				$DB->exec("update `pay_rand` set `status` ='1',`orderno` ='0',`url` = '0', `reorder` = '' where `orderno`='$out_trade_no'");
				file_put_contents("./haoyunpay.txt",date("Y-m-d H:i:s")." 准备处理订单信息: ".json_encode($order). PHP_EOL,FILE_APPEND);
				file_put_contents("./haoyunpay.txt","==========================". PHP_EOL,FILE_APPEND);
				processOrder($order);
			}

    exit('SUCCESS');
}

file_put_contents('./shibai.txt',file_get_contents('php://input'));
exit('sign error');

?>