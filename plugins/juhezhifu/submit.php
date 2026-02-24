<?php
if(!defined('IN_PLUGIN'))exit();
include 'function.php';
//require_once(PAY_ROOT."inc/epay_submit.class.php");
$order= $DB->getRow("SELECT * FROM pre_order WHERE trade_no = '".$trade_no."'");
	
// $DB->exec("INSERT INTO `pre_orderinfo` (`content`,`order_sn`, `createtime`,'status') VALUES (:content,:order_sn, :createtime,:status)", [':content'=>"123",':order_sn'=>"123",':createtime'=>time(),':status'=>"0"]);
// $_REQUEST = array("orderid"=>"123345");
// var_dump($DB->exec("INSERT INTO `pre_orderinfo` (`content`,`order_sn`, `createtime`,`status`) VALUES (:content,:order_sn, :createtime,:status)", [':content'=>json_encode($_REQUEST),':order_sn'=>$_REQUEST["orderid"],':createtime'=>time(),':status'=>'1']));
// exit();

if($order["money"]%2!=0){
    echo "金额错误";exit;
}
if($order['money']<4)
{
	echo '最少支付4元';exit;
}


//var_dump($channel);exit;
echo "<script>window.location.href='/pay/juhezhifu/qrcode/{$trade_no}/?sitename={$sitename}';</script>";

?>