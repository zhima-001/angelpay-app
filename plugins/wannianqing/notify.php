<?php
if(!defined('IN_PLUGIN'))exit();
file_put_contents("./haoyunpay.txt","==========================". PHP_EOL,FILE_APPEND);
file_put_contents("./haoyunpay.txt",date("Y-m-d H:i:s")." 上游回调信息: ".$_POST. PHP_EOL,FILE_APPEND);


require 'pay/config.php';

header('Content-type:text/html;charset=utf-8');
file_put_contents('./demo.txt',file_get_contents('php://input'));

$content = file_get_contents('php://input');
$_POST    = (array)json_decode($content, true);

$flag = false;

//md5(订单状态+商务号+商户订单号+支付金额+商户秘钥)
        $returnArray = array( // 返回字段
            "merchant_no" => $_POST["merchant_no"], // 商户ID
            "pay_code" =>  $_POST["pay_code"], // 订单号
            "order_amount" =>  $_POST["order_amount"], // 交易金额
            "order_actual_money" =>  $_POST["order_actual_money"], // 交易时间
            "order_no" =>  $_POST["order_no"], // 流水号
            "attach" => $_POST["attach"],
             "sign" => $_POST["sign"],
        );
      
   
        //echo($md5str . "key=" . $Md5key);
        //$sign = strtoupper(md5($md5str . "key=" . $md5key));
        $sign = md5("key=".$returnArray['merchant_no'].$md5key."&order_amount=".$returnArray['order_amount']."&order_no=".$returnArray['order_no']."&pay_code=".$returnArray['pay_code']);
     
        
        if($sign == $_POST["sign"]){
			    $flag = true;
        }else {
 
            exit('签名校验错误'); 
        }
	    
    $returnArray['name'] ='万年青支付';
    $out_trade_no = daddslashes($returnArray["order_no"]);
        //日志开始
	$shuju = $returnArray;
	$Money = $_POST["order_amount"];
	$shuju['hebing']=$md5str . "key=" . $md5key;
    $shuju['mysign']=$sign;
	$shuju['ordersign']=$_POST["sign"];
	$shuju['md5key']=$md5key;
	$shuju1 = json_encode($shuju,true);
	$DB->exec("INSERT INTO `pre_notify_rizhi` (`content`,`addtime`, `jine`,`zt`) VALUES (:content,:addtime, :jine,:zt)", [':content'=>$shuju1,':addtime'=>time(),':jine'=>$Money,':zt'=>'ok']);

if($flag){
    //处理逻辑
	$trade_no = $out_trade_no;
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