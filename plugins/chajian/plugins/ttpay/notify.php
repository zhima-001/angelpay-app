<?php
if(!defined('IN_PLUGIN'))exit();
file_put_contents("./ttpaynotify.txt","==========================". PHP_EOL,FILE_APPEND);
file_put_contents("./ttpaynotify.txt",date("Y-m-d H:i:s")." 上游回调信息: ".json_encode($_POST). PHP_EOL,FILE_APPEND);
//file_put_contents("./taotaoing1.txt",json_encode($_POST));exit;
//{"tradeNo":"2021072822001417201447243391","Money":"1","title":"\u6536\u94b1\u7801\u6536\u6b3e","memo":"zfbjk.com","alipay_account":"muw09245172k@163.com","Sign":"15D01BAE94C5237AC7F7CFCD561994EB","Gateway":"alipay","Paytime":"2021-07-28 01:53:14"}
//$_POST=json_decode(file_get_contents("./taotaoing1.txt"),true);
//{"name":"支付宝","money":"1.66","time":"2021-09-09 17:14:59","timestamp":1631178899535,"phone":"111111111112"} 
//$data=json_decode(file_get_contents('php://input'),true);

/*$tradeNo = isset($_POST['tradeNo'])?$_POST['tradeNo']:'';
$Money = isset($_POST['Money'])?$_POST['Money']:0;
$title = isset($_POST['title'])?$_POST['title']:'';
$memo = isset($_POST['memo'])?$_POST['memo']:'';
$alipay_account = isset($_POST['alipay_account'])?$_POST['alipay_account']:'';
$Gateway = isset($_POST['Gateway'])?$_POST['Gateway']:'';
$Sign = isset($_POST['Sign'])?$_POST['Sign']:'';
/*$time=time()-300;
$row= $DB->getRow("SELECT * FROM pay_rand WHERE zfb = '".$alipay_account."' and money='".$Money."' and time > '".$time."' LIMIT 1");
if($row==""){
    echo "SELECT * FROM pay_rand WHERE zfb = '".$alipay_account."' and money='".$Money."' and time > '".$time."' LIMIT 1";
    exit;
}else{
    //$orderno=$row["orderno"];
    //$order= $DB->getRow("SELECT * FROM pay_order WHERE out_trade_no  = '".$orderno."' LIMIT 1");
    //$channel_id=$order["channel"];
    //$channel= $DB->getRow("SELECT * FROM pay_channel WHERE id  = '".$channel_id."' LIMIT 1");
    //print_R($orderno);
}*/
//echo $channel["appid"] . $channel["appkey"] . $tradeNo . $Money . $title . $memo;
//echo strtoupper(md5($channel["appid"] . $channel["appkey"] . $tradeNo . $Money . $title . $memo));
//商户ID号+商户密钥+tradeNo+Money+title+memo
//$title=iconv("UTF-8","gbk//TRANSLIT",$title);
if(true){
    //file_put_contents("./taotaoing222.txt","sss");
    //$DB->getRow("SELECT * FROM pay_rand WHERE zfb = '".$alipay_account."' and money='".$money."' and time > '".$time."' LIMIT 1");
	//商户订单号
	$out_trade_no = daddslashes($order["trade_no"]);

	//支付宝交易号
	$trade_no = daddslashes($order["trade_no"]);
    //echo $order["out_trade_no"];
	//交易状态
	//$trade_status = $_GET['trade_status'];

	//交易金额
	//$money = $_GET['money'];

    //if ($_GET['trade_status'] == 'TRADE_SUCCESS') {
		//付款完成后，支付宝系统发送该交易状态通知
		//if($out_trade_no == TRADE_NO && round($money,2)==round($order['money'],2) && $order['status']==0){
			if($DB->exec("update `pre_order` set `status` ='1' where `trade_no`='$out_trade_no'")){
				$DB->exec("update `pre_order` set `api_trade_no` ='$trade_no',`endtime` ='$date',`date` =NOW() where `trade_no`='$out_trade_no'");
				$DB->exec("update `pay_rand` set `status` ='1',`orderno` ='0',`url` = '0', `reorder` = '' where `orderno`='$out_trade_no'");
				file_put_contents("./ttpaynotify.txt",date("Y-m-d H:i:s")." 准备处理订单信息: ".json_encode($order). PHP_EOL,FILE_APPEND);
				file_put_contents("./ttpaynotify.txt","==========================". PHP_EOL,FILE_APPEND);
				processOrder($order);
			}
		//}
    //}
    //file_put_contents("./ok.txt",$money);
	echo "ok";
}else {
    //验证失败
    echo "fail";
}
?>