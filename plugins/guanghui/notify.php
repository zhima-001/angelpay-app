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
//?income=10000&payOrderId=P01202208111432132621673&amount=10000&mchId=1012&productId=8037&mchOrderNo=2022081114321232900&paySuccTime=1660199695000&sign=AD711C6F051D3D8AACBC96231A8E9936&channelOrderNo=C04166019953480700394127&backType=2&reqTime=20220811143455&param1=&param2=&appId=&status=2

        $returnArray = array( // 返回字段
            "income" => $_REQUEST["income"],
            "payOrderId" => $_REQUEST["payOrderId"], // 商户ID
            "amount" =>  $_REQUEST["amount"], // 支付流水号

            "mchId" =>  $_REQUEST["mchId"], // 订单号
            
            "productId" =>  $_REQUEST["productId"], // 交易金额
            "mchOrderNo" =>  $_REQUEST["mchOrderNo"], // 交易时间
            "paySuccTime" => $_REQUEST["paySuccTime"],
            "channelOrderNo"=>$_REQUEST['channelOrderNo'],
            "backType"=> $_REQUEST["backType"],
            "reqTime"=>$_REQUEST['reqTime'],
            "status"=> $_REQUEST["status"],

        );
      
        ksort($returnArray);
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


        $returnArray['name'] ='光辉支付';
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