<?php
if(!defined('IN_PLUGIN'))exit();
file_put_contents("./haoyunpay.txt","==========================". PHP_EOL,FILE_APPEND);
file_put_contents("./haoyunpay.txt",date("Y-m-d H:i:s")." 上游回调信息: ".json_encode($_POST). PHP_EOL,FILE_APPEND);
file_put_contents("./haoyunpay.txt","==========================". PHP_EOL,FILE_APPEND);
file_put_contents("./haoyunpay.txt",date("Y-m-d H:i:s")." 上游回调信息: ".json_encode(file_get_contents('php://input')). PHP_EOL,FILE_APPEND);
$mishi = $_POST['mishi'];


require 'pay/config.php';

header('Content-type:text/html;charset=utf-8');
file_put_contents('./demo.txt',file_get_contents('php://input'));

$DB->exec("INSERT INTO `pre_orderinfo` (`content`,`order_sn`, `createtime`,`status`) VALUES (:content,:order_sn, :createtime,:status)", [':content'=>json_encode($_REQUEST),':order_sn'=>$_REQUEST['mchOrderNo'],':createtime'=>time(),':status'=>'0']); 



$flag = false;

$returnArray = array(
	'payOrderId' => $_REQUEST['payOrderId'],//开户账号
	'amount' => $_REQUEST['amount'], //alipay:支付宝,tenpay:财付通,qqpay:QQ钱包,wxpay:微信支付 
	'mchId' => $_REQUEST['mchId'],
	'productId' => $_REQUEST['productId'],
	'mchOrderNo' => $_REQUEST['mchOrderNo'],
	'paySuccTime' => $_REQUEST['paySuccTime'],
	'sign' => $_REQUEST['sign'],
	'channelOrderNo' => $_REQUEST['channelOrderNo'],
	'backType' => $_REQUEST['backType'],
	'status' => $_REQUEST['status'],
	'appId'=>$_REQUEST['appId'],
	'income'=>$_REQUEST['income'],
);


//md5(订单状态+商务号+商户订单号+支付金额+商户秘钥)
        $para_filter = array();
    		foreach($returnArray as $key => $val){
    			if($key == "sign" || $val == ""){
    				continue;
    			}else{
    				$para_filter[$key] = $returnArray[$key];
    			}
    	}
   	    ksort($para_filter);
		reset($para_filter);    
        $arg  = "";
	
        foreach ($para_filter as $key => $val) {
            $arg = $arg . $key . "=" . $val . "&";
        }
	
		
		//如果存在转义字符，那么去掉转义

		$mgsign =  strtoupper(md5($arg . "key=" . $md5key));
		
		
		
        if($mgsign == $_REQUEST['sign']){
              $flag = true;
        } 
			  
	
        $returnArray['name'] ='聚合支付';
        $out_trade_no = daddslashes($returnArray["mchOrderNo"]);
        //日志开始
	$shuju = $returnArray;
	$Money = $_REQUEST["amount"];

	$shuju['ordersign']=$_REQUEST["sign"];
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

    exit("success");	

}

file_put_contents('./shibai.txt',file_get_contents('php://input'));
exit('sign error');

?>