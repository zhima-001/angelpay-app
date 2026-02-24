<?php
if(!defined('IN_PLUGIN'))exit();
file_put_contents("./haoyunpay.txt","==========================". PHP_EOL,FILE_APPEND);
file_put_contents("./haoyunpay.txt",date("Y-m-d H:i:s")." 上游回调信息: ".json_encode($_POST). PHP_EOL,FILE_APPEND);
$mishi = $_POST['mishi'];


require 'pay/config.php';

header('Content-type:text/html;charset=utf-8');
file_put_contents('./demo.txt',file_get_contents('php://input'));



 $key = $md5key;          						//商户密钥
 $total_amount=$_REQUEST["total_amount"];        			//订单金额
 $out_trade_no=$_REQUEST["out_trade_no"];        			//订单号
 $trade_status=$_REQUEST["trade_status"];        			//订单状态：成功返回 SUCCESS，失败返回：Fail
 $trade_no=$_REQUEST["trade_no"];        					//支付系统订单号
 $extra_return_param=$_REQUEST["extra_return_param"];       //备注信息，
 $trade_time=$_REQUEST["trade_time"];        				//订单完成时间
 $sign=$_REQUEST["sign"];        							//591返回签名数据
 $param="out_trade_no=".$out_trade_no."&total_amount=".$total_amount."&trade_status=".$trade_status;  //拼接$param
 $paramMd5=md5($param.$key);          						//md5后加密之后的$param


$flag = false;

if($sign==$paramMd5){
 	if($trade_status== "SUCCESS"){      
        //可在此处增加操作数据库语句，实现自动下发，也可在其他文件导入该php，写入数据库
 		//echo "SUCCESS";
 		  $flag = true;
 	}
 	else {
		 echo "订单处理失败";
 	} 	
 }else{
 	echo "签名无效，视为无效数据!";
 }



        $returnArray = array( // 返回字段
            "total_amount" => $_REQUEST["total_amount"], // 商户ID
            "out_trade_no" =>  $_REQUEST["out_trade_no"], // 订单号
            "trade_status" =>  $_REQUEST["trade_status"], // 交易金额
         
            "trade_time" =>  $_REQUEST["trade_time"], // 流水号
            "sign" => $_REQUEST["sign"]
        );
     
        $returnArray['name'] ='拖拉机支付';
        $out_trade_no = daddslashes($_REQUEST["out_trade_no"]);
        //日志开始
	$shuju = $returnArray;
	$Money = $_REQUEST["total_amount"];

	$shuju1 = json_encode($shuju,true);
	$DB->exec("INSERT INTO `pre_notify_rizhi` (`content`,`addtime`, `jine`,`zt`) VALUES (:content,:addtime, :jine,:zt)", [':content'=>$shuju1,':addtime'=>time(),':jine'=>$Money,':zt'=>'ok']);

if($flag){
    //处理逻辑
	
	$orderAmt = $Money;
	$trade_no = $out_trade_no;
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

    $DB->exec("INSERT INTO `pre_ordererror` (`content`,`order_sn`, `createtime`) VALUES (:content,:order_sn, :createtime)", [':content'=>$shuju1,':order_sn'=>$_REQUEST["orderid"],':createtime'=>time()]);			
	
    exit('SUCCESS');
}else{
    $DB->exec("INSERT INTO `pre_ordererror` (`content`,`order_sn`, `createtime`) VALUES (:content,:order_sn, :createtime)", [':content'=>$shuju1,':order_sn'=>$_REQUEST["orderid"],':createtime'=>time()]);
}

file_put_contents('./shibai.txt',file_get_contents('php://input'));
exit('sign error');

?>