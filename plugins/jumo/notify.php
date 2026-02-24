<?php
if(!defined('IN_PLUGIN'))exit();
file_put_contents("./haoyunpay.txt","==========================". PHP_EOL,FILE_APPEND);
file_put_contents("./haoyunpay.txt",date("Y-m-d H:i:s")." 上游回调信息: ".json_encode($_POST). PHP_EOL,FILE_APPEND);
$mishi = $_POST['mishi'];


require 'pay/config.php';

header('Content-type:text/html;charset=utf-8');
file_put_contents('./demo.txt',file_get_contents('php://input'));


function getSign(array $data, $appSecret)
{
    ksort($data);
    $need = [];
    foreach ($data as $key => $value) {
        if (! $value || $key == 'sign') {
            continue;
        }
        $need[] = "{$key}={$value}";
    }
    $string = implode('&', $need).$appSecret;

    return strtoupper(md5($string));
}
$flag = false;
    $returnArray = array( // 返回字段
            "memberid" => $_REQUEST["memberid"], // 商户ID
            "orderid" =>  $_REQUEST["orderid"], // 订单号
            "amount" =>  $_REQUEST["amount"], // 交易金额
            "datetime" =>  $_REQUEST["datetime"], // 交易时间
            "transaction_id" =>  $_REQUEST["transaction_id"], // 支付流水号
            "returncode" => $_REQUEST["returncode"],
        );
  
        ksort($returnArray);
        reset($returnArray);
        $md5str = "";
        foreach ($returnArray as $key => $val) {
            $md5str = $md5str . $key . "=" . $val . "&";
        }
      
      //  $sign =  strtoupper(md5($md5str . "key=" . $md5key));//旧算法
      var_dump($md5str."key=".$md5key);
        $sign = strtoupper(md5(strtoupper(md5($md5str."key=".$md5key))));//MD5key加密，同时所有字符串转换成大写
      //  exit($md5str . "key=" . $md5key);
      var_dump($sign);
        if ($sign == $_REQUEST["sign"]) {
            if ($_REQUEST["returncode"] == "00") {
                   $flag = true; 
                
            }else{
                 exit("fail ");
            }
        }else {
            exit("sign fail");
        }





	    
        $returnArray['name'] ='巨魔支付';
        $out_trade_no = daddslashes($returnArray["orderid"]);
        //日志开始
	$shuju = $returnArray;
	$Money = $returnArray["amount"];
	$shuju['hebing']=$md5str . "key=" . $md5key;
    $shuju['mysign']=$sign;
	$shuju['ordersign']=$returnArray["sign"];
	$shuju['md5key']=$md5key;
	$shuju1 = json_encode($shuju,true);
	$DB->exec("INSERT INTO `pre_notify_rizhi` (`content`,`addtime`, `jine`,`zt`) VALUES (:content,:addtime, :jine,:zt)", [':content'=>$shuju1,':addtime'=>time(),':jine'=>$Money,':zt'=>'ok']);

if($flag){
    //处理逻辑
	
	$orderAmt = $Money;
	$trade_no = daddslashes($returnArray["transaction_id"]);
	$date = date("Y-m-d H:i:s");

		if($DB->exec("update `pre_order` set `status` ='1' where `trade_no`='$out_trade_no'")){
			//echo "$orderAmt";
				$DB->exec("update `pre_order` set `api_trade_no` ='$trade_no',`endtime` ='$date',`date` =NOW(),`randmoney` = $orderAmt where `trade_no`='$out_trade_no'");
				$DB->exec("update `pay_rand` set `status` ='1',`orderno` ='0',`url` = '0', `reorder` = '' where `orderno`='$out_trade_no'");
				file_put_contents("./haoyunpay.txt",date("Y-m-d H:i:s")." 准备处理订单信息: ".json_encode($order). PHP_EOL,FILE_APPEND);
				file_put_contents("./haoyunpay.txt","==========================". PHP_EOL,FILE_APPEND);
				processOrder($order);
			}

      exit("OK");
}

file_put_contents('./shibai.txt',file_get_contents('php://input'));
exit('sign error');

?>