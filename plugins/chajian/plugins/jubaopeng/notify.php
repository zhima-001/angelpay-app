<?php

if(!defined('IN_PLUGIN'))exit();
file_put_contents("./haoyunpay.txt","==========================". PHP_EOL,FILE_APPEND);
file_put_contents("./haoyunpay.txt",date("Y-m-d H:i:s")." 上游回调信息: ".json_encode($_POST). PHP_EOL,FILE_APPEND);
$mishi = $_POST['mishi'];


require 'pay/config.php';




file_put_contents('./demo.txt',file_get_contents('php://input'));

//$info = file_get_contents('php://input');

$info_arr = $_GET;
//flag = verify($_POST,$md5Key, $ptKey);
$flag = false;



//var_dump($info_arr);
//echo "<br/>";
/*$params = [
    'mch_id'=>$info_arr['mch_id'],
    'pass_code'=>$channel['appurl'],
    'subject'=>$info_arr['subject'],
    'out_trade_no'=>$info_arr['out_trade_no'],
    'money'=> $info_arr['money'],
    'client_ip'=>$order['ip'],
    'notify_url' => "https://".$_SERVER['HTTP_HOST']."/pay/biyadiapay/notify/".$info_arr['out_trade_no'].'/',
    'return_url' => "https://".$_SERVER['HTTP_HOST']."/pay/biyadiapay/return/".$info_arr['out_trade_no'].'/',
    'timestamp'=> $info_arr['notify_time']
];*/
echo $md5key;
$params = [
    'parter'=>$info_arr['parter'],
    "orderid"=>$info_arr['orderid'],
    "opstate"=>$info_arr['opstate'],
    'ovalue'=> $info_arr['ovalue'],
    'sysorderid'=> $info_arr['sysorderid'],
    "systime"=>$info_arr['systime'],
    "sign"=> $info_arr['sign'],
 
];

$paramsss = [
        'orderid'=>$info_arr['orderid'],
        "opstate"=>$info_arr['opstate'],
        'ovalue'=>$info_arr['ovalue'],
];
//生成签名 请求参数按照Ascii编码排序
//ksort($params);        //将参数数组按照参数名ASCII码从小到大排序
foreach ($paramsss as $key => $item) {
    //if (!empty($item)) {         //剔除参数值为空的参数
        $newArr[] = $key . '=' . $item;     // 整合新的参数数组
    //}
}

//parter=9927&ovalue=100.0091b7e9be97a84ed09cc48777f21dae09
$stringA = implode("&", $newArr);         //使用 & 符号连接参数
$stringSignTemp = $stringA . $md5key;

$sign = md5($stringSignTemp);      //将所有字符转换为大写

//e9f432399ea824190f2021aa7dc3cab9
//6f0f9b2104bae7749964b5b9d004285a
if($sign == $info_arr['sign']){
    $flag = true;
}else{
//    var_dump($params);
//    var_dump($sign);
//    var_dump($info_arr['sign']);

    exit('订单签名不正确！');
}


$returnArray = [
    'parter'=>$info_arr['parter'],
    "orderid"=>$info_arr['orderid'],
    "opstate"=>$info_arr['opstate'],
    'ovalue'=> $info_arr['ovalue'],
    'sysorderid'=> $info_arr['sysorderid'],
    "systime"=>$info_arr['systime'],
    "sign"=> $info_arr['sign'],
 
];


$returnArray['name'] ='聚宝盆支付';
$out_trade_no = daddslashes($returnArray["orderid"]);
//日志开始
	$shuju = $returnArray;
	$Money = $_REQUEST["ovalue"];
	$shuju1 = json_encode($shuju,true);
	$DB->exec("INSERT INTO `pre_notify_rizhi` (`content`,`addtime`, `jine`,`zt`) VALUES (:content,:addtime, :jine,:zt)", [':content'=>$shuju1,':addtime'=>time(),':jine'=>$Money,':zt'=>'ok']);

if($flag){
    //处理逻辑

	$orderAmt = $Money;
	$date = date("Y-m-d H:i:s");
	//echo "update `pre_order` set `api_trade_no` ='$trade_no',`endtime` ='$date',`date` =NOW(),`randmoney` = $orderAmt where `trade_no`='$out_trade_no'";
		if($DB->exec("update `pre_order` set `status` ='1' where `trade_no`='$out_trade_no'")){
			//echo "$orderAmt";
				$DB->exec("update `pre_order` set `api_trade_no` ='$trade_no',`endtime` ='$date',`date` =NOW(),`randmoney` = $orderAmt where `trade_no`='$out_trade_no'");
				$DB->exec("update `pay_rand` set `status` ='1',`orderno` ='0',`url` = '0', `reorder` = '' where `orderno`='$out_trade_no'");
				file_put_contents("./haoyunpay.txt",date("Y-m-d H:i:s")." 准备处理订单信息: ".json_encode($order). PHP_EOL,FILE_APPEND);
				file_put_contents("./haoyunpay.txt","==========================". PHP_EOL,FILE_APPEND);
				processOrder($order);
			}
    exit('SUCCESS');
}
file_put_contents('./shibai.txt',file_get_contents('php://input'));
exit('sign error');

?>
