<?php
if(!defined('IN_PLUGIN'))exit();
file_put_contents("./haoyunpay.txt","==========================". PHP_EOL,FILE_APPEND);
file_put_contents("./haoyunpay.txt",date("Y-m-d H:i:s")." 上游回调信息: ".json_encode($_POST). PHP_EOL,FILE_APPEND);
$mishi = $_POST['mishi'];


require 'pay/config.php';

header('Content-type:text/html;charset=utf-8');
file_put_contents('./demo.txt',file_get_contents('php://input'));



$data = file_get_contents('php://input');
$retuns_data = json_decode($data,true);
//flag = verify($_POST,$md5Key, $ptKey);
$flag = false;

//md5(订单状态+商务号+商户订单号+支付金额+商户秘钥)

         $returnArray = array( // 返回字段
            "mch_id" => $retuns_data["mch_id"], // 商户ID
            "trade_no" =>  $retuns_data["trade_no"], // 订单号
            "out_trade_no" =>  $retuns_data["out_trade_no"], // 交易金额
            "money" =>  $retuns_data["money"], // 交易时间
            "notify_time" =>  $retuns_data["notify_time"], // 流水号
            "status"=>$retuns_data["status"],
            "original_trade_no"=>$retuns_data["original_trade_no"],  
            'subject'=>$retuns_data["subject"],
           // 'body'=>$retuns_data["body"],
        );
      // var_dump($retuns_data);
        ksort($returnArray);
        foreach ($returnArray as $key => $item) {
           // if (!empty($item)) {         //剔除参数值为空的参数
                $newArr[] = $key . '=' . $item;     // 整合新的参数数组
            //}
        }
        $stringA = implode("&", $newArr);         //使用 & 符号连接参数
        //echo($md5str . "key=" . $Md5key);
        //var_dump($stringA . $md5key);
        $sign = strtoupper(md5($stringA . $md5key));
        
        if($sign == $retuns_data["sign"]){
           
		    $flag = true;
        }else {
         // echo $sign;
            exit('签名校验错误');
        }
	    
        $returnArray['name'] ='狮子狗支付';
        $out_trade_no = daddslashes($returnArray["out_trade_no"]);
        //日志开始
	$shuju = $returnArray;
	$Money = $retuns_data["money"];

	$shuju1 = json_encode($shuju,true);
	$DB->exec("INSERT INTO `pre_notify_rizhi` (`content`,`addtime`, `jine`,`zt`) VALUES (:content,:addtime, :jine,:zt)", [':content'=>$shuju1,':addtime'=>time(),':jine'=>$Money,':zt'=>'ok']);

if($flag){
    //处理逻辑
	
	$orderAmt = $Money;
	$trade_no =  $retuns_data["trade_no"];
	$out_trade_no =  $retuns_data["out_trade_no"];
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