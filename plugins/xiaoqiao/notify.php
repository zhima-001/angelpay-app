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
$data['appid'] = $_REQUEST['appid'];
$data['out_trade_no'] = $_REQUEST['out_trade_no'];
$data['pay_id'] = $_REQUEST['pay_id'];
$data['pay_no'] = $_REQUEST['pay_no'];
$data['code'] = $_REQUEST['code'];
$data['source_amount'] =$_REQUEST['source_amount'];//原订单金额
$data['version'] = $_REQUEST['version'];
$data['amount'] =$_REQUEST['amount'];//实际支付金额(上分以这个为主)
ksort($data);
$reqData = array();
foreach ($data as $k => $v) {
    $reqData[] = $k . '=' . $v;
}
$str = implode('&', $reqData);
$sign = md5($str .$md5key );
if($sign==$_REQUEST['sign']){
    if($data['code']=='00'){ 
        //业务....
        $flag = true;
        //echo 'success';
    }else{
         echo '订单状态异常';
    exit();
    }
}else{
    echo '签名不正确';
    exit();
}




	    
        $returnArray['name'] ='小乔支付';
        $out_trade_no = daddslashes($_REQUEST["out_trade_no"]);
        //日志开始
	$shuju = $data;
	$Money = $_REQUEST["amount"];
	$shuju['hebing']=$md5str . "key=" . $md5key;
    $shuju['mysign']=$sign;
	$shuju['ordersign']=$_REQUEST["sign"];
	$shuju['md5key']=$md5key;
	$shuju1 = json_encode($shuju,true);
/*	$DB->exec("INSERT INTO `pre_notify_rizhi` (`content`,`addtime`, `jine`,`zt`) VALUES (:content,:addtime, :jine,:zt)", [':content'=>$shuju1,':addtime'=>time(),':jine'=>$Money,':zt'=>'ok']);*/

\lib\Zhifu::csasahangss(1,json_encode($shuju),"小乔支付","回调");

if($flag){
    //处理逻辑
	
	$orderAmt = $Money;
	$trade_no = daddslashes($_REQUEST["out_trade_no"]);
	$date = date("Y-m-d H:i:s");

		if($DB->exec("update `pre_order` set `status` ='1' where `trade_no`='$out_trade_no'")){
			//echo "$orderAmt";
				$DB->exec("update `pre_order` set `api_trade_no` ='$trade_no',`endtime` ='$date',`date` =NOW(),`randmoney` = $orderAmt where `trade_no`='$out_trade_no'");
				$DB->exec("update `pay_rand` set `status` ='1',`orderno` ='0',`url` = '0', `reorder` = '' where `orderno`='$out_trade_no'");
				file_put_contents("./haoyunpay.txt",date("Y-m-d H:i:s")." 准备处理订单信息: ".json_encode($order). PHP_EOL,FILE_APPEND);
				file_put_contents("./haoyunpay.txt","==========================". PHP_EOL,FILE_APPEND);
				processOrder($order);
			}
	
				$parameter = array(
                    'chat_id' => $conf['bchatid'],
                    'parse_mode' => 'HTML',
                    'text' => "notify_order_no==".$trade_no
                );
                http_post_data('sendMessage', json_encode($parameter));
    exit('success');
}

file_put_contents('./shibai.txt',file_get_contents('php://input'));
exit('sign error');

?>