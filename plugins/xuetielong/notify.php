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
// $_REQUEST = json_decode(file_get_contents('php://input'),true);

//http://ceshi.freewing1688.xyz/pay/leikesasi/notify/2022112311170890689/?ifCode=payEco&amount=100&payOrderId=P29539662737408&mchOrderNo=2022112311170890689&subject=Merchandise&wayCode=ALI_WAP&sign=8977288C1E05F1AEE61DDC590D890DBF&channelOrderNo=007722112311171100198010003207&reqTime=1669173477953&body=Merchandise&createdAt=1669173431726&appId=637cb564e4b05ed52a1d41ed&clientIp=45.77.137.213&successTime=1669173478000&currency=cny&state=2&mchNo=M1669117284

$fxid = $_REQUEST['fxid']; //商户编号
$fxddh = $_REQUEST['fxddh']; //商户订单号
$fxorder = $_REQUEST['fxorder']; //平台订单号
$fxdesc = $_REQUEST['fxdesc']; //商品名称
$fxfee = $_REQUEST['fxfee']; //交易金额
$fxattch = $_REQUEST['fxattch']; //附加信息
$fxstatus = $_REQUEST['fxstatus']; //订单状态
$fxtime = $_REQUEST['fxtime']; //支付时间
$fxsign = $_REQUEST['fxsign']; //md5验证签名串

$data = array(
    "fxid" => $fxid, //商户号
    "fxddh" => $fxddh, //商户订单号
    "fxaction" => "orderquery"//查询动作
);

$data["fxsign"] = md5($data["fxid"] . $data["fxddh"] . $data["fxaction"] . $md5key); //加密

$mysign = md5($fxstatus . $fxid . $fxddh . $fxfee . $md5key); //验证签名


if ($fxsign == $mysign) {
    if ($fxstatus == '1') {//支付成功
        //支付成功 更改支付状态 完善支付逻辑

          $flag = true;
    } else { //支付失败
        echo 'fail';
        exit();
    }
} else {
    echo 'sign error';
     exit();
}


\lib\Zhifu::csasahangss(1,json_encode($returnArray),"雪铁龙支付","回调");
if($flag){
    //处理逻辑
    $out_trade_no  = $_REQUEST['fxddh'];
    $Money =  $_REQUEST['fxfee'];
	$date = date("Y-m-d H:i:s");  
	$trade_no = $out_trade_no;
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