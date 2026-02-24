<?php
if(!defined('IN_PLUGIN'))exit();
file_put_contents("./haoyunpay.txt","==========================". PHP_EOL,FILE_APPEND);
file_put_contents("./haoyunpay.txt",date("Y-m-d H:i:s")." 上游回调信息: ".json_encode($_POST). PHP_EOL,FILE_APPEND);
$mishi = $_POST['mishi'];

header('Content-type:text/html;charset=utf-8');
file_put_contents('./demo.txt',file_get_contents('php://input'));

require 'pay/config.php';

function verify($data,$md5Key,$pubKey){
    //验签
    ksort($data);
    reset($data);
    $arg = '';
    foreach ($data as $key => $val) {
        //空值不参与签名
        if ($val == '' || $key == 'sign') {
            continue;
        }
        $arg .= ($key . '=' . $val . '&');
    }
    $arg = $arg . 'key=' . $md5Key;
    $signData = strtoupper(md5($arg));
    $rsa = new Rsa($pubKey, '');
    if ($rsa->verify($signData, $data['sign']) == 1) {
        return true;
    }
    return false;
}




$md5key="FzeUqJYstoDLTmRIVOAWpbyZrfNnEXgi";
$publicKey = "MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDNWM+R/kWyKqtzIX8FYINkRy48XBfBcnqFyOhXWL5et8ldWiUL/mtgxQU2ZMjZ9Oa7I5niQLK8YXEbXn1Sh9s+80oxELfySigSs9BzX9wVxjPtVdUt0hiFmyjbNUf2n8mgIP5AobaX+zmisEd2/Xq8tuzHe1CVAyD0gTYQ0Wtb6QIDAQAB";


$flag = verify($_POST,$md5Key,$publicKey);
var_dump($flag);
if($flag){
    //处理逻辑
     //处理逻辑
    $out_trade_no  = $_POST['orderId'];
    $Money =  $_POST['orderAmt'];
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
    \lib\Zhifu::csasahangss(1,json_encode($_POST),"潘森支付","回调");

}

exit('sign error');


// //flag = verify($_POST,$md5Key, $ptKey);
// $flag = false;
// // $_REQUEST = json_decode(file_get_contents('php://input'),true);
// $returnArray = array( // 返回字段
//     "memberid" => $_REQUEST["memberid"], // 商户ID
//     "orderid" =>  $_REQUEST["orderid"], // 订单号
//     "amount" =>  $_REQUEST["amount"], // 交易金额
//     "true_amount" =>  $_REQUEST["true_amount"], // 交易时间
//     "transaction_id" =>  $_REQUEST["transaction_id"], // 流水号
//     "datetime"=>$_REQUEST['datetime'],
//     "returncode"=>$_REQUEST['returncode']
// );
// ksort($returnArray);
// $md5str = "";
// foreach ($returnArray as $key => $val) {
//     $md5str = $md5str . $key . "=" . $val . "&";
// }
// $sign = strtoupper(md5($md5str ."key=". $md5key)); 
// if($sign == $_REQUEST["sign"]){
//     if($_REQUEST['returncode']=="00"){
//         $flag = true;
//     }else{
//         exit('签名校验错误');
//     }
// }else {
//     exit('签名校验错误');
// }

// if($flag){
//     //处理逻辑
//     $out_trade_no  = $_REQUEST['orderid'];
//     $Money =  $_REQUEST['amount'];
// 	$date = date("Y-m-d H:i:s");  
// 	$trade_no = $out_trade_no;
// 	$orderAmt = $Money;
//     if($DB->exec("update `pre_order` set `status` ='1' where `trade_no`='$out_trade_no'")){
// 	    $DB->exec("update `pre_order` set `api_trade_no` ='$trade_no',`endtime` ='$date',`date` =NOW(),`randmoney` = $orderAmt where `trade_no`='$out_trade_no'");
// 		$DB->exec("update `pay_rand` set `status` ='1',`orderno` ='0',`url` = '0', `reorder` = '' where `orderno`='$out_trade_no'");
//         processOrder($order);
// 		$parameter = array(
//          'chat_id' => $conf['bchatid'], 
//           'parse_mode' => 'HTML',
//           'text' => "notify_order_no==".$trade_no
//         );
//         http_post_data('sendMessage', json_encode($parameter));
//         echo "OK";
//         exit();
// 	}
//     echo "OK";
//     exit();
// }else{
//       echo "NO OK";
//      exit();
// }
?>