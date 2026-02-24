<?php
if(!defined('IN_PLUGIN'))exit();
file_put_contents("./haoyunpay.txt","==========================". PHP_EOL,FILE_APPEND);
file_put_contents("./haoyunpay.txt",date("Y-m-d H:i:s")." 上游回调信息: ".json_encode($_POST). PHP_EOL,FILE_APPEND);
$mishi = $_POST['mishi'];


require 'pay/config.php';

header('Content-type:text/html;charset=utf-8');
file_put_contents('./demo.txt',file_get_contents('php://input'));


$DB->exec("INSERT INTO `pre_orderinfo` (`content`,`order_sn`, `createtime`,`status`) VALUES (:content,:order_sn, :createtime,:status)", [':content'=>json_encode($_REQUEST),':order_sn'=>$_REQUEST['out_trade_no'],':createtime'=>time(),':status'=>'0']);

//flag = verify($_POST,$md5Key, $ptKey);
$flag = false;

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

if($sign==$paramMd5){
    if($trade_status== "SUCCESS"){
        //可在此处增加操作数据库语句，实现自动下发，也可在其他文件导入该php，写入数据库
      
        $flag = true;
    }else {
        exit( "订单处理失败");
    }
}else{
     exit( "签名无效，视为无效数据!");
   
}



//md5(订单状态+商务号+商户订单号+支付金额+商户秘钥)

        $returnArray = array( // 返回字段
        
            "out_trade_no" =>  $_REQUEST["out_trade_no"], // 订单号
            "total_amount" =>  $_REQUEST["total_amount"], // 交易金额
            "trade_time" =>  $_REQUEST["trade_time"], // 交易时间
         
            "trade_status" => $_REQUEST["trade_status"]
        );
      
     
        $returnArray['name'] ='非主流支付';
        $out_trade_no = daddslashes($returnArray["out_trade_no"]);
        //日志开始
	$shuju = $returnArray;
	$Money = $_REQUEST["total_amount"]/100;
	$shuju['hebing']=$md5str . "key=" . $md5key;
    $shuju['mysign']=$sign;
	$shuju['ordersign']=$_REQUEST["sign"];
	$shuju['md5key']=$paramMd5;
	$shuju1 = json_encode($shuju,true);
	$DB->exec("INSERT INTO `pre_notify_rizhi` (`content`,`addtime`, `jine`,`zt`) VALUES (:content,:addtime, :jine,:zt)", [':content'=>$shuju1,':addtime'=>time(),':jine'=>$Money,':zt'=>'ok']);

if($flag){
    //处理逻辑
	$trade_no = $out_trade_no;
	$date = date("Y-m-d H:i:s");
	$orderAmt = $Money;
	//echo "update `pre_order` set `api_trade_no` ='$trade_no',`endtime` ='$date',`date` =NOW(),`randmoney` = $orderAmt where `trade_no`='$out_trade_no'";
		if($DB->exec("update `pre_order` set `status` ='1' where `trade_no`='$out_trade_no'")){
		
				$DB->exec("update `pre_order` set `api_trade_no` ='$trade_no',`endtime` ='$date',`date` =NOW(),`randmoney` = $orderAmt where `trade_no`='$out_trade_no'");
				$DB->exec("update `pay_rand` set `status` ='1',`orderno` ='0',`url` = '0', `reorder` = '' where `orderno`='$out_trade_no'");
				file_put_contents("./haoyunpay.txt",date("Y-m-d H:i:s")." 准备处理订单信息: ".json_encode($order). PHP_EOL,FILE_APPEND);
				file_put_contents("./haoyunpay.txt","==========================". PHP_EOL,FILE_APPEND);
				processOrder($order);
			}

    $DB->exec("INSERT INTO `pre_ordererror` (`content`,`order_sn`, `createtime`) VALUES (:content,:order_sn, :createtime)", [':content'=>$shuju1,':order_sn'=>$_REQUEST["orderid"],':createtime'=>time()]);			
		
    exit('success');
}else{
    $DB->exec("INSERT INTO `pre_ordererror` (`content`,`order_sn`, `createtime`) VALUES (:content,:order_sn, :createtime)", [':content'=>$shuju1,':order_sn'=>$_REQUEST["orderid"],':createtime'=>time()]);
}

file_put_contents('./shibai.txt',file_get_contents('php://input'));
exit('sign error');

?>