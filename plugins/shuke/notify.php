<?php

if(!defined('IN_PLUGIN'))exit();
file_put_contents("./haoyunpay.txt","==========================". PHP_EOL,FILE_APPEND);
file_put_contents("./haoyunpay.txt",date("Y-m-d H:i:s")." 上游回调信息: ".json_encode($_POST). PHP_EOL,FILE_APPEND);
$mishi = $_POST['mishi'];


require 'pay/config.php';




file_put_contents('./demo.txt',file_get_contents('php://input'));

//$info = file_get_contents('php://input');


//flag = verify($_POST,$md5Key, $ptKey);
$flag = false;
$_REQUEST = json_decode(file_get_contents('php://input'),true); 

$DB->exec("INSERT INTO `pre_orderinfo` (`content`,`order_sn`, `createtime`,`status`) VALUES (:content,:order_sn, :createtime,:status)", [':content'=>json_encode($_REQUEST),':order_sn'=>$_REQUEST['out_trade_no'],':createtime'=>time(),':status'=>'0']); 

$returnArray = array( // 返回字段
            "mch_id" => $_REQUEST["mch_id"], // 商户ID
            "trade_no" =>  $_REQUEST["trade_no"], // 订单号
            "out_trade_no" =>  $_REQUEST["out_trade_no"], // 交易金额
            
            "money" =>  $_REQUEST["money"], // 支付流水号
            "status" => $_REQUEST["status"],
            "notify_time"=>$_REQUEST["notify_time"],
            "original_trade_no" => $_REQUEST["original_trade_no"],
            "notify_time"=>$_REQUEST["notify_time"],
            "subject"=>$_REQUEST["subject"],
        );
       //生成签名 请求参数按照Ascii编码排序
        ksort($returnArray);        //将参数数组按照参数名ASCII码从小到大排序
        foreach ($returnArray as $key => $item) {
            if (!empty($item)) {         //剔除参数值为空的参数
                $newArr[] = $key . '=' . $item;     // 整合新的参数数组
            }
        }
        $stringA = implode("&", $newArr);         //使用 & 符号连接参数
        $stringSignTemp = $stringA . $md5key; 
        
        $sign = strtoupper(md5($stringSignTemp));      //将所有字符转换为大写
        echo $sign;
        if ($sign == $_REQUEST["sign"]) {
            if ($_REQUEST["status"] == "2") {
                 
                   $flag = true;
            }
        }else {
            exit("签名异常！");
        }




    $returnArray['name'] ='舒克支付';
    $out_trade_no = daddslashes($returnArray["out_trade_no"]);
    //日志开始
	$shuju = $returnArray;
	$Money = $_REQUEST["money"];
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
