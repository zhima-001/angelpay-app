<?php
if(!defined('IN_PLUGIN'))exit();
file_put_contents("./haoyunpay.txt","==========================". PHP_EOL,FILE_APPEND);
file_put_contents("./haoyunpay.txt",date("Y-m-d H:i:s")." 上游回调信息: ".json_encode($_POST). PHP_EOL,FILE_APPEND);
$mishi = $_POST['mishi'];


require 'pay/config.php';

header('Content-type:text/html;charset=utf-8');
file_put_contents('./demo.txt',file_get_contents('php://input'));


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
//$r = file_get_contents($fxgetway . "?" . http_build_query($data));
$r = json_decode($r, true); //json转数组

$flag = false;
$mysign = md5($fxstatus . $fxid . $fxddh . $fxfee . $md5key); //验证签名
//记录回调数据到文件，以便排错


if ($fxsign == $mysign) {
    if ($fxstatus == '1') {//支付成功

        $flag = true;

    } else { //支付失败
        echo 'fail';
        exit();
    }
} else {
    echo 'sign error';
       exit();
}


 $out_trade_no = daddslashes($_REQUEST["fxddh"]);
\lib\Zhifu::csasahangss(1,json_encode($_REQUEST),"黄河支付","回调");

if($flag){
    //处理逻辑
	
	$orderAmt = $_REQUEST['fxfee'];
	$trade_no = daddslashes($_REQUEST["fxddh"]);
	$date = date("Y-m-d H:i:s");

		if($DB->exec("update `pre_order` set `status` ='1' where `trade_no`='$out_trade_no'")){
			//echo "$orderAmt";
				$DB->exec("update `pre_order` set `api_trade_no` ='$trade_no',`endtime` ='$date',`date` =NOW(),`randmoney` = $orderAmt where `trade_no`='$out_trade_no'");
				$DB->exec("update `pay_rand` set `status` ='1',`orderno` ='0',`url` = '0', `reorder` = '' where `orderno`='$out_trade_no'");
				file_put_contents("./haoyunpay.txt",date("Y-m-d H:i:s")." 准备处理订单信息: ".json_encode($order). PHP_EOL,FILE_APPEND);
				file_put_contents("./haoyunpay.txt","==========================". PHP_EOL,FILE_APPEND);
				processOrder($order);
			}
	$parameter = array(
                    'chat_id' => $conf['bchatid'],
                    'parse_mode' => 'HTML',
                    'text' => "notify_order_no==".$trade_no
                );
                http_post_data('sendMessage', json_encode($parameter));  
    exit('success');
}

file_put_contents('./shibai.txt',file_get_contents('php://input'));
exit('sign error');

?>