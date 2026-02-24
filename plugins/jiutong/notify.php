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
            "merchant_sn" => $_REQUEST["merchant_sn"], // 商户ID
            "merchant_order" =>  $_REQUEST["merchant_order"], // 订单号
            "payment_status" =>  $_REQUEST["payment_status"], // 交易金额
            "payment_msg" =>  $_REQUEST["payment_msg"], // 交易时间
            "payment_sn" =>  $_REQUEST["payment_sn"], // 流水号
            "amount" => $_REQUEST["amount"],
            'payment_time'=>$_REQUEST['payment_time'],
          
           
        );

   
        //echo($md5str . "key=" . $Md5key);
         $string = '';
            foreach ($returnArray as $k => $v) {
                if (!$string) {
                    $string .= $k . '=' . $v;
                } else {
                    $string .= '&' . $k . '=' . $v;
                }
            }
        
            
            //$sign = hash("sha256", md5($string) . $md5key);
          $sign = hash("sha256",md5($string) . $md5key);
         
        if($sign == $_REQUEST["sign"]){
            if($_REQUEST['payment_status'] == "1"){
                $flag = true;
            }else{
                exit("未支付");
            }
			    
		    
        }else {
            
            exit('签名校验错误');
        }
	    
        $returnArray['name'] ='酒桶支付';
        $out_trade_no = daddslashes($returnArray["merchant_order"]);
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
			}

    exit('0000');
}

exit('sign error');

?>