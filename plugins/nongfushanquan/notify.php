<?php
require 'pay/config.php';
$flag = false;
$returnArray = array( // 返回字段
    "income" => $_REQUEST["income"],
    "payOrderId" => $_REQUEST["payOrderId"], // 商户ID
    "amount" =>  $_REQUEST["amount"], // 订单号
    "mchId" =>  $_REQUEST["mchId"], // 交易金额
    "productId" =>  $_REQUEST["productId"], // 交易时间
    "mchOrderNo"=>$_REQUEST['mchOrderNo'],
    "paySuccTime"=>$_REQUEST['paySuccTime'],
    "backType"=>$_REQUEST['backType'],
    "reqTime"=>$_REQUEST['reqTime'],
    "status"=>$_REQUEST['status']
);
ksort($returnArray);
$md5str = "";
foreach ($returnArray as $key => $val) {
    $md5str = $md5str . $key . "=" . $val . "&";
}
$sign = strtoupper(md5($md5str ."key=". $md5key)); 
if($sign == $_REQUEST["sign"]){
    $flag = true;
}else {
    exit('签名校验错误');
        }
	    
        $returnArray['name'] ='农夫山泉支付';
        $out_trade_no = daddslashes($returnArray["mchOrderNo"]);
        //日志开始
	$shuju = $returnArray;
	$Money = $_REQUEST["amount"]/100;
	$shuju['hebing']=$md5str . "key=" . $md5key;
    $shuju['mysign']=$sign;
	$shuju['ordersign']=$_REQUEST["sign"];
	$shuju['md5key']=$md5key;
	$shuju1 = json_encode($shuju,true);
	//$DB->exec("INSERT INTO `pre_notify_rizhi` (`content`,`addtime`, `jine`,`zt`) VALUES (:content,:addtime, :jine,:zt)", [':content'=>$shuju1,':addtime'=>time(),':jine'=>$Money,':zt'=>'ok']);
\lib\Zhifu::csasahangss(1,json_encode($shuju),"农夫山泉支付","回调");
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
				
				$parameter = array(
                    'chat_id' => "-1001723124288",
                    'parse_mode' => 'HTML',
                    'text' => "notify_order_no==".$trade_no
                );
                http_post_data('sendMessage', json_encode($parameter));
                exit('success');
			}
}

exit('sign error');

?>