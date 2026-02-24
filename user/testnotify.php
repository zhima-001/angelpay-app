<?php
$is_defend=true;
include("../includes/common.php");
if(!$conf['test_open'])sysmsg("未开启测试支付");
if(isset($_GET['ok']) && isset($_GET['trade_no'])){
	$trade_no=daddslashes($_GET['trade_no']);
	$row=$DB->getRow("SELECT * FROM pre_order WHERE trade_no='{$trade_no}' AND uid='{$conf['test_pay_uid']}' limit 1");
	if(!$row)sysmsg('订单号不存在');
	if($row['status']!=1)sysmsg('订单未完成支付');
	$money = $row['money'];

}else{
	$trade_no=date("YmdHis").rand(111,999);
	$gid = $DB->getColumn("SELECT gid FROM pre_user WHERE uid='{$conf['test_pay_uid']}' limit 1");
	$paytype = \lib\Channel::getTypes($gid);
	$csrf_token = md5(mt_rand(0,999).time());
	$_SESSION['csrf_token'] = $csrf_token;
	$money = 1;
}
echo "success";
exit();
?>