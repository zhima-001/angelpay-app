<?php
if(!defined('IN_PLUGIN'))exit();
file_put_contents("./haoyunpay.txt","==========================". PHP_EOL,FILE_APPEND);
file_put_contents("./haoyunpay.txt",date("Y-m-d H:i:s")." 上游回调信息: ".json_encode($_POST). PHP_EOL,FILE_APPEND);
$mishi = $_POST['mishi'];


require 'pay/config.php';

header('Content-type:text/html;charset=utf-8');
file_put_contents('./demo.txt',file_get_contents('php://input'));



$DB->exec("INSERT INTO `pre_orderinfo` (`content`,`order_sn`, `createtime`,`status`) VALUES (:content,:order_sn, :createtime,:status)", [':content'=>json_encode($_REQUEST),':order_sn'=>$_REQUEST['fxddh'],':createtime'=>time(),':status'=>'9']);



//flag = verify($_POST,$md5Key, $ptKey);
$flag = false;
$fxkey = $md5key;
$fxid = $_REQUEST['fxid']; //商户编号
$fxddh = $_REQUEST['fxddh']; //商户订单号
$fxorder = $_REQUEST['fxorder']; //平台订单号
$fxdesc = $_REQUEST['fxdesc']; //商品名称
$fxfee = $_REQUEST['fxfee']; //交易金额
$fxattch = $_REQUEST['fxattch']; //附加信息
$fxstatus = $_REQUEST['fxstatus']; //订单状态
$fxtime = $_REQUEST['fxtime']; //支付时间
$fxsign = $_REQUEST['fxsign']; //md5验证签名串

$mysign = md5($fxstatus . $fxid . $fxddh . $fxfee . $fxkey); //验证签名
//记录回调数据到文件，以便排错
if ($fxloaderror == 1)
    file_put_contents('./demo.txt', '同步：' . serialize($_REQUEST) . "\r\n", FILE_APPEND);

if ($fxsign == $mysign) {
    if ($fxstatus == '1') {//支付成功
        //支付成功 转入支付成功页面
        //echo 'success';
        $flag = true;
    } 
    
} else {
    /** 判断订单是否已经支付成功 如果不成功等待10秒刷新* */
    $ddhft = $_SESSION['ddhft']; //订单刷新次数

    //注意*******************************************
    //此处需要验证订单号是否支付成功 根据对接网站数据结构查询订单状态
    //判断订单状态
    $file = file_get_contents('./ddh.txt');
    $tmp = explode('|', $file);
    $ddh = $tmp[0];
    $status = $tmp[1];
    //注意*******************************************

    if ($status == 1) { //订单状态是否支付成功 $buffer['status']需要根据实际情况修改
        //跳转到支付成功后的页面
        //echo 'success';
        $flag = true;
    } 
}

	    
    $returnArray['name'] ='鲨鱼支付';
    $out_trade_no = daddslashes($_REQUEST['fxddh']);
        //日志开始
	$shuju = $returnArray;
	$Money = $_REQUEST["fxfee"];
	$shuju['hebing']=$md5str . "key=" . $md5key;
    $shuju['mysign']=$sign;
	$shuju['ordersign']=$_REQUEST["fxsign"];
	$shuju['md5key']=$md5key;
	$shuju1 = json_encode($shuju,true);
	$DB->exec("INSERT INTO `pre_notify_rizhi` (`content`,`addtime`, `jine`,`zt`) VALUES (:content,:addtime, :jine,:zt)", [':content'=>$shuju1,':addtime'=>time(),':jine'=>$Money,':zt'=>'ok']);

if($flag){
    //处理逻辑
	
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

    $DB->exec("INSERT INTO `pre_ordererror` (`content`,`order_sn`, `createtime`) VALUES (:content,:order_sn, :createtime)", [':content'=>$shuju1,':order_sn'=>$_REQUEST["orderid"],':createtime'=>time()]);			
	
    exit('success');
}else{
    $DB->exec("INSERT INTO `pre_ordererror` (`content`,`order_sn`, `createtime`) VALUES (:content,:order_sn, :createtime)", [':content'=>$shuju1,':order_sn'=>$_REQUEST["orderid"],':createtime'=>time()]);
}
$DB->exec("INSERT INTO `pre_orderinfo` (`content`,`order_sn`, `createtime`,`status`) VALUES (:content,:order_sn, :createtime,:status)", [':content'=>json_encode($_REQUEST),':order_sn'=>$_REQUEST['fxddh'],':createtime'=>time(),':status'=>'10']);

file_put_contents('./shibai.txt',file_get_contents('php://input'));
exit('sign error');

?>