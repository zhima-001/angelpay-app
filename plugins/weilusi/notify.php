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
$sign = $_REQUEST['sign'];
$return_arr = array(
    'amount'=>$_REQUEST['amount'],
    'merchantId'=>$_REQUEST['merchantId'],
    'notifyTime'=>$_REQUEST['notifyTime'],
    'outTradeNo'=>$_REQUEST['outTradeNo'],
    'realAmount'=>$_REQUEST['realAmount'],
    'status'=>$_REQUEST['status'],
    'subject'=>$_REQUEST['subject'],
    'tradeNo'=>$_REQUEST['tradeNo'],
    'passageTradeNo'=>$_REQUEST['passageTradeNo'],
    'body'=>$_REQUEST['body'],
    

);
ksort($return_arr);
$md5str = "";
foreach ($return_arr as $key => $val) {
    $md5str = $md5str . $key . "=" . $val . "&";
}
$md5str = substr($md5str,0,-1);
$sign = strtolower(md5($md5str  . $md5key));

$mysign =$sign;




if ( $_REQUEST['sign'] == $mysign) {
    if ($_REQUEST['status'] == '2') {//支付成功
        //支付成功 更改支付状态 完善支付逻辑
       $flag = true;
     
    } else { //支付失败
        echo 'fail';
    }
} else {
    echo 'sign error';
    exit();
}

	$Money = $_REQUEST["amount"];


if($flag){
    //处理逻辑
	$out_trade_no = $_REQUEST['outTradeNo'];
	$trade_no = $_REQUEST['outTradeNo'];
	$orderAmt = $Money;
	$date = date("Y-m-d H:i:s",time());
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

exit('sign error');

?>