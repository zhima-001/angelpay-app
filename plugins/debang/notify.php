<?php
if(!defined('IN_PLUGIN'))exit();
file_put_contents("./haoyunpay.txt","==========================". PHP_EOL,FILE_APPEND);
file_put_contents("./haoyunpay.txt",date("Y-m-d H:i:s")." 上游回调信息: ".json_encode($_POST). PHP_EOL,FILE_APPEND);
$mishi = $_POST['mishi'];


require 'pay/config.php';

header('Content-type:text/html;charset=utf-8');
file_put_contents('./demo.txt',file_get_contents('php://input'));



$_REQUEST = json_decode(file_get_contents('php://input'),true);
//flag = verify($_POST,$md5Key, $ptKey);
$flag = false;

//md5(订单状态+商务号+商户订单号+支付金额+商户秘钥)
//http://ceshi.freewing1688.xyz/pay/doukou/notify/2022071411354571422/?income=3000&payOrderId=P01202207141135471034809&amount=3000&mchId=20000051&productId=8049&mchOrderNo=2022071411354571422&paySuccTime=1657771112000&sign=21BFA3BE8AF875EFC6A0CF09994B4793&channelOrderNo=&backType=2&reqTime=20220714115832&param1=&param2=&appId=&status=2

        /*
        "mch_id": "60",
    "trade_no": "X19912917444985605",
    "out_trade_no": "2022071814491285626",
    "money": "100",
    "notify_time": "2022-07-18 15: 15: 30",
    "state": "2",
    "psg_trade_no": "P0110202207181449135797084",
    "subject": "svip",*/
        $returnArray = array( // 返回字段
            "mch_id" => $_REQUEST["mch_id"], // 商户ID
            "trade_no" =>  $_REQUEST["trade_no"], // 订单号
            "out_trade_no" =>  $_REQUEST["out_trade_no"], // 交易金额
            "money" =>  $_REQUEST["money"], // 交易时间
            "notify_time" =>  $_REQUEST["notify_time"], // 流水号
            'state'=>$_REQUEST['state'],
            'psg_trade_no'=>$_REQUEST['psg_trade_no'],
            "subject" => $_REQUEST["subject"],
            
          
        );

   
ksort($returnArray);
$md5str = "";
foreach ($returnArray as $key => $val) {
    $md5str = $md5str . $key . "=" . $val . "&";
}

$sign = strtoupper(md5($md5str. "key=".$md5key)); 

        if($sign == $_REQUEST["sign"]){
            if($_REQUEST['state'] == "2"){
                $flag = true;
            }else{
                exit("未支付");
            }
			    
		    
        }else {
            
            exit('签名校验错误');
        }
	    
        $returnArray['name'] ='德邦支付';
        $out_trade_no = daddslashes($returnArray["out_trade_no"]);
        //日志开始
	$shuju = $returnArray;
	$Money = $_REQUEST["money"];
	$shuju['hebing']=$md5str . "key=" . $md5key;
    $shuju['mysign']=$sign;
	$shuju['ordersign']=$_REQUEST["sign"];
	$shuju['md5key']=$md5key;
	$shuju1 = json_encode($shuju,true);
	$DB->exec("INSERT INTO `pre_notify_rizhi` (`content`,`addtime`, `jine`,`zt`) VALUES (:content,:addtime, :jine,:zt)", [':content'=>$shuju1,':addtime'=>time(),':jine'=>$Money,':zt'=>'ok']);

if($flag){
    //处理逻辑
	$date = date("Y-m-d H:i:s");  
	$trade_no = $out_trade_no;
	$orderAmt = $Money;

	//echo "update `pre_order` set `api_trade_no` ='$trade_no',`endtime` ='$date',`date` =NOW(),`randmoney` = $orderAmt where `trade_no`='$out_trade_no'";
		if($DB->exec("update `pre_order` set `status` ='1' where `trade_no`='$out_trade_no'")){
			//echo "$orderAmt";
				$DB->exec("update `pre_order` set `api_trade_no` ='$trade_no',`endtime` ='$date',`date` =NOW(),`randmoney` = $orderAmt where `trade_no`='$out_trade_no'");
				$DB->exec("update `pay_rand` set `status` ='1',`orderno` ='0',`url` = '0', `reorder` = '' where `orderno`='$out_trade_no'");
				file_put_contents("./haoyunpay.txt",date("Y-m-d H:i:s")." 准备处理订单信息: ".json_encode($order). PHP_EOL,FILE_APPEND);
				file_put_contents("./haoyunpay.txt","==========================". PHP_EOL,FILE_APPEND);
				processOrder($order);
			}exit('SUCCESS');
}

exit('sign error');

?>