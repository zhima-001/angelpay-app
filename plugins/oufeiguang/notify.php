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
$result =  file_get_contents('php://input');
$CC = json_decode($result);
 $account=$CC->account;

$transaction_id=$CC->transaction_id;
$charset=$CC->charset;
$nonce_str=$CC->nonce_str;
$out_transaction_id=$CC->out_transaction_id;
$merchant_id=$CC->merchant_id;
$fee_type=$CC->fee_type;
$version=$CC->version;
$pay_result=$CC->pay_result;
$real_amount=$CC->real_amount;
$real_pay_amount=$CC->real_pay_amount;
$out_trade_no=$CC->out_trade_no;
$total_amount=$CC->total_amount;
$trade_type=$CC->trade_type;
$result_code=$CC->result_code;
$time_end=$CC->time_end;
$sign_type=$CC->sign_type;
$status=$CC->status;
$sign=$CC->sign;

$mysign=md5('account='.$account.'&charset='.$charset.'&fee_type='.$fee_type.'&merchant_id='.$merchant_id.'&nonce_str='.$nonce_str.'&out_trade_no='.$out_trade_no.'&out_transaction_id='.$out_transaction_id.'&pay_result='.$pay_result.'&real_amount='.$real_amount.'&real_pay_amount='.$real_pay_amount.'&result_code='.$result_code.'&sign_type='.$sign_type.'&status='.$status.'&time_end='.$time_end.'&total_amount='.$total_amount.'&trade_type='.$trade_type.'&transaction_id='.$transaction_id.'&version='.$version.'&key='.$md5key);


if(strcasecmp($sign,$mysign)==0){	
    if($status=='0'){
            $flag = true;
    }else{
        exit('签名校验错误');
    }
} else {
    echo 'signerr';
}


\lib\Zhifu::csasahangss(1,json_encode($CC),"欧菲光支付","回调");
if($flag){
    //处理逻辑
    $out_trade_no  = $out_trade_no;
    $Money =  $total_amount;
	$date = date("Y-m-d H:i:s");  
	$trade_no = $transaction_id;
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