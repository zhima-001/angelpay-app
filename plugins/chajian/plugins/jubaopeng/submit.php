<?php
if(!defined('IN_PLUGIN'))exit();
include 'function.php';
//require_once(PAY_ROOT."inc/epay_submit.class.php");
$order= $DB->getRow("SELECT * FROM pre_order WHERE trade_no = '".$trade_no."'");

//if($order['money']<100)
//{
//	echo '最少支付100元';exit;
//}


//var_dump($channel);exit;
echo "<script>window.location.href='/pay/jubaopeng/qrcode/{$trade_no}/?sitename={$sitename}';</script>";

?>
