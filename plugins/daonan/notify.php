<?php
if(!defined('IN_PLUGIN'))exit();
file_put_contents("./haoyunpay.txt","==========================". PHP_EOL,FILE_APPEND);
file_put_contents("./haoyunpay.txt",date("Y-m-d H:i:s")." 上游回调信息: ".json_encode($_POST). PHP_EOL,FILE_APPEND);
$mishi = $_POST['mishi'];


require 'pay/config.php';

header('Content-type:text/html;charset=utf-8');
file_put_contents('./demo.txt',file_get_contents('php://input'));




//flag = verify($_POST,$md5Key, $ptKey);
$flag = false;

//md5(订单状态+商务号+商户订单号+支付金额+商户秘钥)
//http://ceshi.freewing1688.xyz/pay/doukou/notify/2022071411354571422/?income=3000&payOrderId=P01202207141135471034809&amount=3000&mchId=20000051&productId=8049&mchOrderNo=2022071411354571422&paySuccTime=1657771112000&sign=21BFA3BE8AF875EFC6A0CF09994B4793&channelOrderNo=&backType=2&reqTime=20220714115832&param1=&param2=&appId=&status=2
        $returnArray = array( // 返回字段
            "memberid" => $_REQUEST["memberid"], // 商户ID
            "orderid" =>  $_REQUEST["orderid"], // 订单号
            "amount" =>  $_REQUEST["amount"], // 交易金额
            "datetime" =>  $_REQUEST["datetime"], // 交易时间
            "transaction_id" =>  $_REQUEST["transaction_id"], // 流水号
            "returncode" => $_REQUEST["returncode"]
        );
      
        ksort($returnArray);
        reset($returnArray);
        $md5str = "";
        
       
        foreach ($returnArray as $key => $val) {
            $md5str = $md5str . $key . "=" . $val . "&";
        }
        $sign = strtoupper(md5($md5str . "key=" . $md5key)); 
 
        if ($sign == $_REQUEST["sign"]) {
             
            if ($_REQUEST["returncode"] == "00") {
                  $flag = true;
                   
            }else{
             
            }
        }else{
            echo "21";
        }
        
if($flag){
    //处理逻辑
    $out_trade_no =  $_REQUEST["orderid"];
	$date = date("Y-m-d H:i:s");  
	$trade_no = $out_trade_no;
	$orderAmt =  $_REQUEST["amount"];

	//echo "update `pre_order` set `api_trade_no` ='$trade_no',`endtime` ='$date',`date` =NOW(),`randmoney` = $orderAmt where `trade_no`='$out_trade_no'";
		if($DB->exec("update `pre_order` set `status` ='1' where `trade_no`='$out_trade_no'")){
			//echo "$orderAmt";
				$DB->exec("update `pre_order` set `api_trade_no` ='$trade_no',`endtime` ='$date',`date` =NOW(),`randmoney` = $orderAmt where `trade_no`='$out_trade_no'");
				$DB->exec("update `pay_rand` set `status` ='1',`orderno` ='0',`url` = '0', `reorder` = '' where `orderno`='$out_trade_no'");
				file_put_contents("./haoyunpay.txt",date("Y-m-d H:i:s")." 准备处理订单信息: ".json_encode($order). PHP_EOL,FILE_APPEND);
				file_put_contents("./haoyunpay.txt","==========================". PHP_EOL,FILE_APPEND);
				processOrder($order);
			}exit('ok');
}

exit('sign error');

?>