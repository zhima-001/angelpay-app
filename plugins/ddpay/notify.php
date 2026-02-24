<?php
if(!defined('IN_PLUGIN'))exit();
//file_put_contents("./taotaoing1.txt",json_encode($_POST));exit;
//{"tradeNo":"2021072822001417201447243391","Money":"1","title":"\u6536\u94b1\u7801\u6536\u6b3e","memo":"zfbjk.com","alipay_account":"muw09245172k@163.com","Sign":"15D01BAE94C5237AC7F7CFCD561994EB","Gateway":"alipay","Paytime":"2021-07-28 01:53:14"}
//$_POST=json_decode(file_get_contents("./taotaoing1.txt"),true);
$data=$_POST;//json_decode(file_get_contents("./taotaoing.txt"),true);//$_POST;
unset($data["gw_sign"]);
ksort($data);
reset($data);
$str="";
foreach($data as $k=>$v){
    $str.=$k."=".$v."&";
}
$sign=strtolower(md5($str."key=".$channel["appkey"]));
if($sign==$_POST["gw_sign"]){
    //file_put_contents("./taotaoing222.txt","sss");
    //$DB->getRow("SELECT * FROM pay_rand WHERE zfb = '".$alipay_account."' and money='".$money."' and time > '".$time."' LIMIT 1");
	//商户订单号
	$out_trade_no = daddslashes($data["gw_order"]);

	//支付宝交易号
	$trade_no = daddslashes($data["gw_payno"]);
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
				$DB->exec("update `pay_rand` set `status` ='1',`orderno` ='0' where `orderno`='$out_trade_no'");
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