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
/*

将签名参数的值用”&”拼接，最后再加上key，用urlencode编码，最后再用md5加密
例：
merchantID = mct10001
tradeNo = D17010169884
outTradeNo = 1701010001
payMoney = 10000
payTime = 1
status = 000
MD5key = 257D75FF0A56B391
签名明文：
signStr = mct10001&D17010169884&1701010001&10000&1&000&257D75FF06E80391
用urlencode编码
signStr = UrlEncode(signStr)



{
  "resultCode": "00",
  "message": "success",
  "merchantID": "mct10001",
  "tradeNo": "D17010169884",
  "outTradeNo": "1701010001",
  "payMoney": "10000",
  "payTime": "20220828115804",
  "status": "000",
  "remark": "",
  "sign": "4386ae4803ab191a5ff2038269cb009d"
}
*/

//签名参数：merchantID、tradeNo、outTradeNo、payMoney、payTime、status
        $returnArray = array( // 返回字段
            "merchantID" => $_REQUEST["merchantID"], // 商户ID
            "tradeNo" =>  $_REQUEST["tradeNo"], // 订单号
            "outTradeNo" =>  $_REQUEST["outTradeNo"], // 交易金额
            "payMoney" =>  $_REQUEST["payMoney"], // 交易时间
            "payTime" =>  $_REQUEST["payTime"], // 流水号
            "status" => $_REQUEST["status"],

        );

   

$md5str = "";
foreach ($returnArray as $key => $val) {
    $md5str = $md5str . $val . "&";
}

$sisgns = $md5str . $md5key; 
$md5_str = urlencode($sisgns);

$sign = md5($md5_str);
       
        if($sign == $_REQUEST["sign"]){
           // if($_REQUEST['status'] == "002"){
                $flag = true;
            // }else{
            //     exit("未支付");
            // }
			    
		    
        }else {
            
            exit('签名校验错误');
        }
	    
        $returnArray['name'] ='岩雀支付';
        $out_trade_no = daddslashes($returnArray["outTradeNo"]);
        //日志开始
	$shuju = $returnArray;
	$Money = $_REQUEST["payMoney"]/100;
	$shuju['hebing']=$md5str . "key=" . $md5key;
    $shuju['mysign']=$sign;
	$shuju['ordersign']=$_REQUEST["sign"];
	$shuju['md5key']=$md5key;
	$shuju1 = json_encode($shuju,true);
// 	$DB->exec("INSERT INTO `pre_notify_rizhi` (`content`,`addtime`, `jine`,`zt`) VALUES (:content,:addtime, :jine,:zt)", [':content'=>$shuju1,':addtime'=>time(),':jine'=>$Money,':zt'=>'ok']);
\lib\Zhifu::csasahangss(1,json_encode($shuju),"岩雀支付","回调");

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