<?php
$nosession = true;
require './includes/common.php';

$type=isset($_REQUEST['type'])?daddslashes($_REQUEST['type']):exit('No type!');
$trade_no=isset($_REQUEST['trade_no'])?daddslashes($_REQUEST['trade_no']):exit('No trade_no!');

@header('Content-Type: text/html; charset=UTF-8');

$row=$DB->getRow("SELECT * FROM ".DBQZ."_order WHERE trade_no='{$trade_no}' limit 1");
if($row['status']>=1){
	//$url=creat_callback($row);
	//exit('{"code":1,"msg":"付款成功","backurl":"'.$url['return'].'"}');
	$id=$row['channel'];
	$channel=$DB->getRow("SELECT * FROM ".DBQZ."_channel WHERE id='{$id}' limit 1");
	$payname=$channel["plugin"];
	exit('{"code":1,"msg":"付款成功","backurl":"/pay/'.$payname.'/return/'.$trade_no.'/"}');
}else{
	exit('{"code":-1,"msg":"未付款"}');
}

?>