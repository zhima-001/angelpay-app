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

//md5(订单状态+商务号+商户订单号+支付金额+商户秘钥)
//http://ceshi.freewing1688.xyz/pay/doukou/notify/2022071411354571422/?income=3000&payOrderId=P01202207141135471034809&amount=3000&mchId=20000051&productId=8049&mchOrderNo=2022071411354571422&paySuccTime=1657771112000&sign=21BFA3BE8AF875EFC6A0CF09994B4793&channelOrderNo=&backType=2&reqTime=20220714115832&param1=&param2=&appId=&status=2

        $returnArray = array( // 返回字段
            "p1_merchantno" => $_REQUEST["p1_merchantno"], // 商户ID
            "p2_amount" =>  $_REQUEST["p2_amount"], // 订单号
            "p3_orderno" =>  $_REQUEST["p3_orderno"], // 交易金额
            "p4_status" =>  $_REQUEST["p4_status"], // 交易时间
            "p5_producttype" =>  $_REQUEST["p5_producttype"], // 支付流水号
            "p6_requesttime" => $_REQUEST["p6_requesttime"],
            "p7_goodsname" => $_REQUEST["p7_goodsname"],
            "p8_tradetime" => $_REQUEST["p8_tradetime"],
            "p9_porderno" => $_REQUEST["p9_porderno"],
        );
      
        ksort($returnArray);
        reset($returnArray);
        $md5str = "";
        foreach ($returnArray as $key => $val) {
            $md5str = $md5str . $key . "=" . $val . "&";
        }
        $sign = strtoupper(md5($md5str . "key=" . $md5key));
        if ($sign == $_REQUEST["sign"]) {
            if ($_REQUEST["p4_status"] == "2") {
                  $flag = true;
                  
            }else{
                exit("no pay");
            }
        }else{
            exit("错误");
        }


        $returnArray['name'] ='剑魔支付';
        $out_trade_no = daddslashes($returnArray["p3_orderno"]);
        //日志开始
	$shuju = $returnArray;
	$Money = $_REQUEST["p2_amount"];
	$shuju['hebing']=$md5str . "key=" . $md5key;
    $shuju['mysign']=$sign;
	$shuju['ordersign']=$_REQUEST["sign"];
	$shuju['md5key']=$md5key;
	$shuju1 = json_encode($shuju,true);
// 	$DB->exec("INSERT INTO `pre_notify_rizhi` (`content`,`addtime`, `jine`,`zt`) VALUES (:content,:addtime, :jine,:zt)", [':content'=>$shuju1,':addtime'=>time(),':jine'=>$Money,':zt'=>'ok']);
\lib\Zhifu::csasahangss(1,json_encode($shuju),"剑魔支付","回调");


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
				$parameter = array(
                    'chat_id' => $conf['bchatid'],
                    'parse_mode' => 'HTML',
                    'text' => "notify_order_no==".$trade_no
                );
                http_post_data('sendMessage', json_encode($parameter));
			exit('SUCCESS ');
}

exit('sign error');

?>