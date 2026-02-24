<?php


if(!defined('IN_PLUGIN'))exit();
file_put_contents("./haoyunpay.txt","==========================". PHP_EOL,FILE_APPEND);
file_put_contents("./haoyunpay.txt",date("Y-m-d H:i:s")." 上游回调信息: ".file_get_contents('php://input'). PHP_EOL,FILE_APPEND);



require 'pay/config.php';


file_put_contents('./demo.txt',file_get_contents('php://input'));
$data = json_decode(file_get_contents('php://input'),true); 

$_REQUEST = $data;

$flag = false;


        $returnArray = array( // 返回字段
            "transactionalNumber" => $_REQUEST['data']["transactionalNumber"], // 商户ID
            "orderNo" =>  $_REQUEST['data']["orderNo"], // 订单号
            "amount" =>  $_REQUEST['data']["amount"], // 交易金额
        );
      
        ksort($returnArray);
        reset($returnArray);
        $md5str = "";
        foreach ($returnArray as $key => $val) {
            $md5str = $md5str . $key . "=" . $val . "&";
        }
        
        //echo($md5str . "key=" . $Md5key);
        $sign = strtolower(md5($md5str . "key=" . $md5key));
        if($sign == $_REQUEST["sign"]){
        
           
			    $flag = true;
		   
        }else {
            
            $DB->exec("INSERT INTO `pre_orderinfo` (`content`,`order_sn`, `createtime`,`status`) VALUES (:content,:order_sn, :createtime,:status)", [':content'=>json_encode($_REQUEST)."===>".$sign,':order_sn'=>$_REQUEST["orderNo"],':createtime'=>time(),':status'=>'3']);
            exit('签名校验错误');
        }
	    
        $returnArray['name'] ='HW支付';
        $out_trade_no = daddslashes($returnArray["orderNo"]);
        //日志开始
	$shuju = $returnArray;
	$Money = $_REQUEST["amount"];
	$shuju['hebing']=$md5str . "key=" . $md5key;
    $shuju['mysign']=$sign;
	$shuju['ordersign']=$_REQUEST["sign"];
	$shuju['md5key']=$md5key;
	
	$trade_no = $_REQUEST['data']["transactionalNumber"];
	$date =date("Y-m-d H:i:s");
	$dates =date("Y-m-d");
	$orderAmt = $returnArray['amount'];
	
	$shuju1 = json_encode($shuju,true);
	

	    
	\lib\Zhifu::csasahangss(1,json_encode($shuju),"HW支付","回调");
    // echo "update `pre_order` set `api_trade_no` ='$trade_no',`endtime` ='$date',`date` ='$dates',`randmoney` = $orderAmt where `trade_no`='$out_trade_no'";
     
if($flag){
    //处理逻辑
	

	//echo "update `pre_order` set `api_trade_no` ='$trade_no',`endtime` ='$date',`date` =NOW(),`randmoney` = $orderAmt where `trade_no`='$out_trade_no'";
		if($DB->exec("update `pre_order` set `status` ='1' where `trade_no`='$out_trade_no'")){
		
			 //       $DB->exec("update `pre_order` set `api_trade_no` ='$trade_no',`endtime` ='$date',`date` ='$dates',`randmoney` = $orderAmt where `trade_no`='$out_trade_no'");
				// $DB->exec("update `pay_rand` set `status` ='1',`orderno` ='0',`url` = '0', `reorder` = '' where `orderno`='$out_trade_no'");
				
				$zsqsq = $DB->exec("update `pre_order` set `api_trade_no` ='$trade_no',`endtime` ='$date',`date` =NOW(),`randmoney` = $orderAmt where `trade_no`='$out_trade_no'");
				$DB->exec("update `pay_rand` set `status` ='1',`orderno` ='0',`url` = '0', `reorder` = '' where `orderno`='$out_trade_no'");
				
				file_put_contents("./haoyunpay.txt",date("Y-m-d H:i:s")." 准备处理订单信息: ".json_encode($order). PHP_EOL,FILE_APPEND);
				file_put_contents("./haoyunpay.txt","==========================". PHP_EOL,FILE_APPEND);
				processOrder($order);
		$parameter = array(
                    'chat_id' => $conf['bchatid'],
                    'parse_mode' => 'HTML',
                    'text' => "notify_order_no==".$trade_no
                );
                http_post_data('sendMessage', json_encode($parameter));
			exit("ok");
		    
		}else{
			  //   exit("sasawq");
			  	exit("等待重新回调");
			}
			

	

    
}else{

}


exit('sign error');

?>