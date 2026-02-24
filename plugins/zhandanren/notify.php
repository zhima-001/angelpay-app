<?php
if(!defined('IN_PLUGIN'))exit();
file_put_contents("./haoyunpay.txt","==========================". PHP_EOL,FILE_APPEND);
file_put_contents("./haoyunpay.txt",date("Y-m-d H:i:s")." 上游回调信息: ".json_encode($_POST). PHP_EOL,FILE_APPEND);
$mishi = $_POST['mishi'];


require 'pay/config.php';

header('Content-type:text/html;charset=utf-8');
file_put_contents('./demo.txt',file_get_contents('php://input'));




//$_REQUEST = json_decode(file_get_contents('php://input'),true);
//flag = verify($_POST,$md5Key, $ptKey);
$flag = false;
     $returnArray = array( // 返回字段
            "order_no" => $_REQUEST["order_no"], // 商户ID
            "merchant_no" =>  $_REQUEST["merchant_no"], // 订单号
            "out_order_no" =>  $_REQUEST["out_order_no"], // 交易金额
            "amount" =>  $_REQUEST["amount"], // 交易时间
         
            "pay_type"=>$_REQUEST["pay_type"],
            "status"=>$_REQUEST["status"],

            
         
        );
        
      

        $sign=md5($_REQUEST["order_no"].$_REQUEST["merchant_no"].$_REQUEST["out_order_no"].$_REQUEST["amount"].$_REQUEST["pay_type"].$_REQUEST["status"].$md5key);


        


if ($_REQUEST["sign"] == $sign) {
    if ($_REQUEST['status'] == '1') {//支付成功
        //支付成功 转入支付成功页面
        //echo 'success';
        $flag = true;
    } 
    
} 

	    
    $returnArray['name'] ='炸弹人支付';
    $out_trade_no = daddslashes($_REQUEST['out_order_no']);
        //日志开始
	$shuju = $returnArray;
	$Money = $_REQUEST["amount"];
	$shuju['hebing']=$md5str . "key=" . $md5key;
    $shuju['mysign']=$sign;
	$shuju['ordersign']=$_REQUEST["sign"];
	$shuju['md5key']=$md5key;
	$shuju1 = json_encode($shuju,true);
	$DB->exec("INSERT INTO `pre_notify_rizhi` (`content`,`addtime`, `jine`,`zt`) VALUES (:content,:addtime, :jine,:zt)", [':content'=>$shuju1,':addtime'=>time(),':jine'=>$Money,':zt'=>'ok']);

if($flag){
    //处理逻辑
	$date = date("Y-m-d H:i:s");
	$trade_no= $_REQUEST["out_order_no"];
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