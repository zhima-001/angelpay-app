<?php
if(!defined('IN_PLUGIN'))exit();
file_put_contents("./haoyunpay.txt","==========================". PHP_EOL,FILE_APPEND);
file_put_contents("./haoyunpay.txt",date("Y-m-d H:i:s")." 上游回调信息: ".json_encode($_POST). PHP_EOL,FILE_APPEND);
$mishi = $_POST['mishi'];


require 'pay/config.php';

header('Content-type:text/html;charset=utf-8');
file_put_contents('./demo.txt',file_get_contents('php://input'));

function paramArraySign($paramArray, $mchKey){
		
		ksort($paramArray);  //字典排序
		reset($paramArray);
	
		$md5str = "";
		foreach ($paramArray as $key => $val) {
			if( strlen($key)  && strlen($val) ){
				$md5str = $md5str . $key . "=" . $val . "&";
			}
		}
		$sign = strtoupper(md5($md5str . "key=" . $mchKey));  //签名
		
		return $sign;
		
	}


//flag = verify($_POST,$md5Key, $ptKey);
    $resSign = $_REQUEST["sign"] ;
	
	$paramArray = array();
	
	if(isset($_REQUEST["payOrderId"]) ){
		$paramArray["payOrderId"] = $_REQUEST["payOrderId"];
	}

	if(isset($_REQUEST["income"]) ){
		$paramArray["income"] = $_REQUEST["income"];
	}
	
	if(isset($_REQUEST["mchId"]) ){
		$paramArray["mchId"] = $_REQUEST["mchId"];
	}
	
	if(isset($_REQUEST["appId"]) ){
		$paramArray["appId"] = $_REQUEST["appId"];
	}
	
	if(isset($_REQUEST["productId"]) ){
		$paramArray["productId"] = $_REQUEST["productId"];
	}
	
	if(isset($_REQUEST["mchOrderNo"]) ){
		$paramArray["mchOrderNo"] = $_REQUEST["mchOrderNo"];
	}
	
	if(isset($_REQUEST["amount"]) ){
		$paramArray["amount"] = $_REQUEST["amount"];
	}
	
	if(isset($_REQUEST["status"]) ){
		$paramArray["status"] = $_REQUEST["status"];
	}
	
	if(isset($_REQUEST["channelOrderNo"]) ){
		$paramArray["channelOrderNo"] = $_REQUEST["channelOrderNo"];
	}
	
	if(isset($_REQUEST["channelAttach"]) ){
		$paramArray["channelAttach"] = $_REQUEST["channelAttach"];
	}
	
	if(isset($_REQUEST["param1"]) ){
		$paramArray["param1"] = $_REQUEST["param1"];
	}
	
	if(isset($_REQUEST["param2"]) ){
		$paramArray["param2"] = $_REQUEST["param2"];
	}
	
	if(isset($_REQUEST["paySuccTime"]) ){
		$paramArray["paySuccTime"] = $_REQUEST["paySuccTime"];
	}
	
	if(isset($_REQUEST["backType"]) ){
		$paramArray["backType"] = $_REQUEST["backType"];
	}

	if(isset($_REQUEST["reqTime"]) ){
    		$paramArray["reqTime"] = $_REQUEST["reqTime"];
    }

	$sign = paramArraySign($paramArray, $md5key);  //签名

	if($resSign != $sign){  //验签失败
		echo "fail(verify fail)";
		exit;
	}else{
	    $flag = true;
	} 
	
        


	    
    $returnArray['name'] ='万豪支付';
    $out_trade_no = daddslashes($_REQUEST['mchOrderNo']);
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
	$trade_no= $_REQUEST["payOrderId"];
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

file_put_contents('./shibai.txt',file_get_contents('php://input'));
exit('sign error');

?>