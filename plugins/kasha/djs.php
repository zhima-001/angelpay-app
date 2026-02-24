<?php 
	$rand= $DB->getRow("SELECT * FROM pre_order WHERE trade_no = '".TRADE_NO."' LIMIT 1");
	$djs=300-time()+strtotime($rand["addtime"]);

$arr['djs'] = $djs;
echo json_encode($arr);
?>