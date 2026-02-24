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
        $returnArray = array( // 返回字段
            "state" => $_REQUEST["state"], // 商户ID
            "payState" =>  $_REQUEST["payState"], // 订单号
             "data" =>  $_REQUEST["data"], // 交易时间
            "message" =>  $_REQUEST["message"], // 交易金额
            "merchantId" =>  $_REQUEST["merchantId"], // 流水号
             "money"=>$_REQUEST['money'],

            "timeSpan" => $_REQUEST["timeSpan"],
            "orderNo"=>$_REQUEST['orderNo'],
           "platOrderNo"=>$_REQUEST['platOrderNo'],
         
        );
        ksort($returnArray);
       // reset($returnArray);
        $md5str = "";
        foreach ($returnArray as $key => $val) {
            $md5str = $md5str . $key . "=" . $val . "&";
        }
 
        $sign = strtoupper(md5($md5str . "key=" . $md5key));
        
        
        
        ksort($returnArray);        //将参数数组按照参数名ASCII码从小到大排序
        foreach ($returnArray as $key => $item) {
        //if (!empty($item)) {         //剔除参数值为空的参数
            $newArr[] = $key . '=' . $item;     // 整合新的参数数组
        //}
}
$stringA = implode("&", $newArr);         //使用 & 符号连接参数
$stringSignTemp = $stringA ."&key=". $md5key;

$mysign = strtoupper(md5($stringSignTemp));


if ($_REQUEST["sign"] == $mysign) {
    if ($_REQUEST['payState'] == '0') {//支付成功 
        //支付成功 更改支付状态 完善支付逻辑
       $flag = true;
     
    } else { //支付失败
        echo 'fail';
    }
} else {
    echo 'sign error';
}

$Money = $_REQUEST["money"];


if($flag){
    //处理逻辑
	$out_trade_no = $_REQUEST['orderNo'];
	$trade_no = $_REQUEST['orderNo'];
	$date = date("Y-m-d H:i:s",time());
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