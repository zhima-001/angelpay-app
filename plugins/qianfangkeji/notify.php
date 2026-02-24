<?php
if(!defined('IN_PLUGIN'))exit();
file_put_contents("./haoyunpay.txt","==========================". PHP_EOL,FILE_APPEND);
file_put_contents("./haoyunpay.txt",date("Y-m-d H:i:s")." 上游回调信息: ".json_encode($_POST). PHP_EOL,FILE_APPEND);
$mishi = $_POST['mishi'];


require 'pay/config.php';

header('Content-type:text/html;charset=utf-8');
file_put_contents('./demo.txt',file_get_contents('php://input'));
// $_REQUEST = json_decode(file_get_contents('php://input'),true);
//flag = verify($_POST,$md5Key, $ptKey);
$flag = false;
/*
merchantId  商户号
    orderId     商户订单号
    amount      订单金额
    status      订单状态 订单已支付才会回调,此参数值固定为 ok
    sign        回调签名

*/
$returnArray = array( // 返回字段
    "merchantId" =>  $_REQUEST["merchantId"], // 流水号
    "amount"=>$_REQUEST['amount'],
    "orderId" => $_REQUEST["orderId"], // 商户ID
    "status" =>  $_REQUEST["status"], // 交易时间

);



function gggg($data, $signkey){

        $data = array_filter($data); //去空
        ksort($data); //排序
        $tmp_string = http_build_query($data); //进行键值对排列  a=1&b=2&c=3
        $tmp_string = urldecode($tmp_string); //参数无需进行urlencode ,上一步进行了urlencode,这里还原一下
        return md5( $tmp_string .'&key='. $signkey );  //签名生成
};


$sign = gggg($returnArray,$md5key);


if($sign == $_REQUEST["sign"]){
    if($_REQUEST['status']=="ok"){
        $flag = true;
    }else{
        exit('签名校验错误');
    }
}else {
    exit('签名校验错误');
}
\lib\Zhifu::csasahangss(1,json_encode($returnArray),"千方科技支付","回调");
if($flag){
    //处理逻辑
    $out_trade_no  = $_REQUEST['orderId'];
    $Money =  $_REQUEST['amount'];
	$date = date("Y-m-d H:i:s");  
	$trade_no = $_REQUEST['orderId'];
	$orderAmt = $Money;
    if($DB->exec("update `pre_order` set `status` ='1' where `trade_no`='$out_trade_no'")){
	    $DB->exec("update `pre_order` set `api_trade_no` ='$trade_no',`endtime` ='$date',`date` =NOW(),`randmoney` = $orderAmt where `trade_no`='$out_trade_no'");
		$DB->exec("update `pay_rand` set `status` ='1',`orderno` ='0',`url` = '0', `reorder` = '' where `orderno`='$out_trade_no'");
        processOrder($order);
		$parameter = array(
         'chat_id' => $conf['bchatid'], 
          'parse_mode' => 'HTML',
          'text' => "notify_order_no==".$trade_no
        );
        http_post_data('sendMessage', json_encode($parameter));
        echo "success";
        exit();
	}
    echo "success";
    exit();
}else{
      echo "no success";
     exit();
}
?>