<?php
$nosession = true;
require './includes/common.php';

$trade_no=$_REQUEST['trade_no'];

if($_REQUEST['start']>0){
    $start=$_REQUEST['start']/1000;
}else{
    $row=$DB->getRow("select addtime from pre_order WHERE trade_no='$trade_no'");
    $start = strtotime($row['addtime']);
}


$end=$_REQUEST['end']/1000;
$ens = $end - $start;
$end_time = round($ens,2);
$row=$DB->exec("UPDATE pre_order SET shijian='{$end_time}' WHERE trade_no='$trade_no'");


if($row){

	exit('{"code":1,"msg":"付款成功"}');
}else{
	exit('{"code":-1,"msg":"未付款"}');
}

?>