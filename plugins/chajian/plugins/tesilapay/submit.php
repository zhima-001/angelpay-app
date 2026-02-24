<?php
if(!defined('IN_PLUGIN'))exit();
include 'function.php';
//require_once(PAY_ROOT."inc/epay_submit.class.php");
$order= $DB->getRow("SELECT * FROM pre_order WHERE trade_no = '".$trade_no."'");
if($order["money"]%2!=0){
    echo "金额错误";exit;
}
if($order['money']<4)
{
	echo '最少支付4元';exit;
}


//var_dump($channel);exit;
echo "<script>window.location.href='/pay/tesilapay/qrcode/{$trade_no}/?sitename={$sitename}';</script>";

?>