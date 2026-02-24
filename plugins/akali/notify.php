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

        $returnArray = array( // 返回字段
            "version" => $_REQUEST["version"], // 商户ID
            "partnerid" =>  $_REQUEST["partnerid"], // 订单号
            "partnerorderid" =>  $_REQUEST["partnerorderid"], // 交易金额
            "payamount" =>  $_REQUEST["payamount"], // 交易时间
            "orderstatus" =>  $_REQUEST["orderstatus"], // 流水号
            "orderno" => $_REQUEST["orderno"],
            'okordertime'=>$_REQUEST['okordertime'],
          
          'paytype'=>$_REQUEST['paytype'],
          'message'=>$_REQUEST['message'],
          'remark'=>$_REQUEST['remark'],
           
        );

   
       ksort($returnArray);
$md5str = "";
foreach ($returnArray as $key => $val) {
    $md5str = $md5str . $key . "=" . $val . "&";
}

//echo($md5str . "key=" . $Md5key);
$sign = md5($md5str . "key=" . $md5key);
         
        if($sign == $_REQUEST["sign"]){
            if($_REQUEST['orderstatus'] == "1"){
                $flag = true;
            }else{
                exit("未支付");
            }
			    
		    
        }else {
            
            exit('签名校验错误');
        }
	    
        $returnArray['name'] ='阿卡丽支付';
        $out_trade_no = daddslashes($returnArray["partnerorderid"]);
        //日志开始
	$shuju = $returnArray;
	$Money = $_REQUEST["payamount"]/100;
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
			}

    exit('success');
}

exit('sign error');

?>