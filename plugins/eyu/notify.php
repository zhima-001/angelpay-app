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
//payOrderId=P01924120220727021919493&amount=10000&mchId=20000048&productId=8007&mchOrderNo=2022072714191824975&appId=507f73cf3bce42dfa00ab18a0743b549&paySuccTime=1658903003000&sign=04B125C5448A64072B2F7D3DDBC1136B&channelOrderNo=P01924120220727021919493&backType=2&status=2

        $returnArray = array( // 返回字段
            "payOrderId" => $_REQUEST["payOrderId"], // 商户ID
              "amount" => $_REQUEST["amount"],
            "mchId" =>  $_REQUEST["mchId"], // 订单号
              "productId"=>$_REQUEST['productId'],
              "mchOrderNo"=>$_REQUEST['mchOrderNo'],
                   "appId" =>  $_REQUEST["appId"], // 交易时间
                     "paySuccTime"=>$_REQUEST['paySuccTime'],
                     
            "channelOrderNo" =>  $_REQUEST["channelOrderNo"], // 交易金额
       
                      "backType"=>$_REQUEST['backType'],

          
            "status"=>$_REQUEST['status'],
       
          
          
        );
      
        ksort($returnArray);
        reset($returnArray);
        $md5str = "";
        foreach ($returnArray as $key => $val) {
            $md5str = $md5str . $key . "=" . $val . "&";
        }
        $sign = strtoupper(md5($md5str . "key=" . $md5key));
        if ($sign == $_REQUEST["sign"]) {
            if ($_REQUEST["status"] == "2") {
                  $flag = true;
                  
            }else{
                exit("no pay");
            }
        }else{
            exit("错误");
        }


        $returnArray['name'] ='鳄鱼支付';
        $out_trade_no = daddslashes($returnArray["mchOrderNo"]);
        //日志开始
	$shuju = $returnArray;
	$Money = $_REQUEST["amount"]/100;
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
			}exit('success');
}

exit('sign error');

?>