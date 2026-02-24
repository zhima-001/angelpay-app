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



//flag = verify($_POST,$md5Key, $ptKey);
$flag = false;
$data = [];

$data['orderid']   = $_POST['orderid'];//第三方订单号
$data['transaction_id']    = $_POST['transaction_id'];//金额，元单位
$data['amount']   = $_POST['amount'];//平台订单号
$data['returncode']=$_POST['returncode'];

ksort($data);//对关联数组按照键名进行升序排序
$sign = '';
foreach ($data as $k => $v) {
    $sign.=$k.'='.$v.'&';
}
$sign = trim($sign,'&');
$sign = strtoupper(md5($sign.$md5key));//MD5key加密，同时所有字符串转换成大写
$data['sign'] = $_POST['sign'];//商户号


if($data['sign'] == $sign){
    //回调成功逻辑数据库处理
    //可能存在多次回调，注意判断业务订单是否已处理，业务订单已处理的仍然返回success
    //回调成功逻辑end
    if($data['returncode']=='00'){
        $flag = true;
    }else{
          echo 'fail';
            exit;
    }
    
   
}else{
    //只有签名失败的情况下返回fail
    echo 'fail';
    exit;
}


	    
        $data['name'] ='石头人支付';
        $out_trade_no = daddslashes($data["orderid"]);
        //日志开始
	$shuju = $data;
	$Money = $data["amount"];
	$shuju['hebing']=$md5str . "key=" . $md5key;
    $shuju['mysign']=$sign;
	$shuju['ordersign']=$data["sign"];
	$shuju['md5key']=$md5key;
	$shuju1 = json_encode($shuju,true);
	$DB->exec("INSERT INTO `pre_notify_rizhi` (`content`,`addtime`, `jine`,`zt`) VALUES (:content,:addtime, :jine,:zt)", [':content'=>$shuju1,':addtime'=>time(),':jine'=>$Money,':zt'=>'ok']);

if($flag){
    //处理逻辑
	
	$orderAmt = $Money;
	$trade_no = daddslashes($data["transaction_id"]);
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