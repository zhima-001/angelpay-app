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
//amount=10000&cid=1&code=101&oid=2022060411234543791&pid=11880032&ramount=10000&sid=20220604062000454243&sign=9fc285b6b2c30cfa91d8e548897fa3b0&stime=2022-06-04%2011:27:27&uid=123
        $returnArray = array( // 返回字段
            "mch_id" => $_REQUEST["mch_id"], // 商户ID
            "out_trade_no" =>  $_REQUEST["out_trade_no"], // 订单号
            "order_no" =>  $_REQUEST["order_no"], // 交易金额
            'pay_time'=>$_REQUEST['pay_time'],
             'timestamp'=>$_REQUEST['timestamp'],
              'total_fee'=>$_REQUEST['total_fee'],
            'mch_secret'=>$md5key
        );
      
        ksort($returnArray);
        $md5str = "";
        foreach ($returnArray as $key => $val) {
            $md5str = $md5str . $key . "=" . $val . "&";
        }
      
        $md5str = substr($md5str,0,-1);
        // echo $md5str;
        // exit();
        //echo($md5str . "key=" . $Md5key);
        //$sign = strtolower(md5($md5str ."key=". $md5key));
        $sign = strtoupper(md5($md5str));
        
        if($sign == $_REQUEST["sign"]){
  
            //if ($_REQUEST["paymentStatus"] == "pay") {
         
			    $flag = true;
		    //}
        }else {

            exit('签名校验错误');
        }
	    
        $returnArray['name'] ='轮子妈支付';
        $out_trade_no = daddslashes($returnArray["out_trade_no"]);
        //日志开始
	$shuju = $returnArray;
	$Money = $_REQUEST["total_fee"];
	$shuju['hebing']=$md5str . "key=" . $md5key;
    $shuju['mysign']=$sign;
	$shuju['ordersign']=$_REQUEST["sign"];
	$shuju['md5key']=$md5key;
	$shuju1 = json_encode($shuju,true);
	$DB->exec("INSERT INTO `pre_notify_rizhi` (`content`,`addtime`, `jine`,`zt`) VALUES (:content,:addtime, :jine,:zt)", [':content'=>$shuju1,':addtime'=>time(),':jine'=>$Money,':zt'=>'ok']);

if($flag){
    //处理逻辑
	
	$orderAmt = $Money;
	$trade_no = daddslashes($returnArray["out_trade_no"]);
	$date = date("Y-m-d H:i:s");

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