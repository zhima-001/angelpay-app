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

//md5(订单状态+商务号+商户订单号+支付金额+商户秘钥)
//umId=938&umNo=2022082810463929747&order_no=20220828104641495157&amount=100.00&pay_success_time=20220828105035&status=SUCCESS&sign=a5698c7e2460ba48f3bf5dfed1fc36df&back_params=
        $returnArray = array( // 返回字段
            "umId" => $_REQUEST["umId"], // 商户ID
            "umNo" => $_REQUEST["umNo"], // 商户ID
            "order_no" => $_REQUEST["order_no"], // 商户ID
            "amount" => $_REQUEST["amount"], // 商户ID
            "pay_success_time" => $_REQUEST["pay_success_time"], // 商户ID
            "status" => $_REQUEST["status"], // 商户ID
        );

   
ksort($returnArray);
$md5str = "";
foreach ($returnArray as $key => $val) {
    $md5str = $md5str . $key . "=" . $val . "&";
}
$md5str = substr($md5str,0,-1);
$sign = strtolower(md5($md5str . $md5key)); 
       
        if($sign == $_REQUEST["sign"]){
            if($_REQUEST['status'] == "SUCCESS"){
                $flag = true;
            }else{
                exit("未支付");
            }
			    
		    
        }else {
            
            exit('签名校验错误');
        }
	    
        $returnArray['name'] ='执法官支付';
        $out_trade_no = daddslashes($returnArray["umNo"]);
$Money = $returnArray["amount"];

// 	$DB->exec("INSERT INTO `pre_notify_rizhi` (`content`,`addtime`, `jine`,`zt`) VALUES (:content,:addtime, :jine,:zt)", [':content'=>$shuju1,':addtime'=>time(),':jine'=>$Money,':zt'=>'ok']);
\lib\Zhifu::csasahangss(1,json_encode($_REQUEST),"执法官支付","回调");

if($flag){
    //处理逻辑
	$date = date("Y-m-d H:i:s");  
	$trade_no = $out_trade_no;
	$orderAmt = $Money;

	//echo "update `pre_order` set `api_trade_no` ='$trade_no',`endtime` ='$date',`date` =NOW(),`randmoney` = $orderAmt where `trade_no`='$out_trade_no'";
		if($DB->exec("update `pre_order` set `status` ='1' where `trade_no`='$out_trade_no'")){
			//echo "$orderAmt";
				$DB->exec("update `pre_order` set `api_trade_no` ='$trade_no',`endtime` ='$date',`date` =NOW(),`randmoney` = $orderAmt where `trade_no`='$out_trade_no'");
				$DB->exec("update `pay_rand` set `status` ='1',`orderno` ='0',`url` = '0', `reorder` = '' where `orderno`='$out_trade_no'");
				file_put_contents("./haoyunpay.txt",date("Y-m-d H:i:s")." 准备处理订单信息: ".json_encode($order). PHP_EOL,FILE_APPEND);
				file_put_contents("./haoyunpay.txt","==========================". PHP_EOL,FILE_APPEND);
				processOrder($order);
			}exit('SUCCESS');
}

exit('sign error');

?>