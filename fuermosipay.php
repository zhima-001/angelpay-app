<?php

//echo "Success";exit;
file_put_contents("./news.txt",json_encode($_POST));
$shuju = file_get_contents('php://input');
$f = fopen("shuju.txt",'a');
fwrite($f,$shuju);
fclose($f);
//$_POST=json_decode(file_get_contents("./tttss.txt"),true);
$nosession = true;
require './includes/common.php';
//$shuju ='{"name":"支付宝","money":"5.21","time":"2021-11-01 00:18:49","timestamp":1635697129263,"phone":"123456"}';
$sj_arr= json_decode($shuju,true);





$Money = isset($sj_arr['money'])?daddslashes($sj_arr['money']):0;
$alipay_account = isset($sj_arr['phone'])?daddslashes($sj_arr['phone']):'';
$Paytime = isset($sj_arr['timestamp'])?$sj_arr['timestamp']:'';
$time=time()-360;


$content = json_encode($_POST).json_encode($_GET)."SELECT * FROM pay_rand WHERE type = 'fuermosipay' and zfb = '".$alipay_account."' and money='".$Money."' and time > '".$time."' LIMIT 1";
$file = fopen("huidiao.txt","a");

fwrite($file,$content);
fclose($file);

$row= $DB->getRow("SELECT * FROM pay_rand WHERE type = 'fuermosipay' and zfb = '".$alipay_account."' and money='".$Money."' and time > '".$time."' LIMIT 1");
//$row= $DB->getRow("SELECT * FROM pay_rand WHERE zfb = '".$alipay_account."' and money='".$Money."' LIMIT 1");
//echo "SELECT * FROM pay_rand WHERE zfb = '".$alipay_account."' and money='".$Money."' and time > '".$time."' LIMIT 1";
//print_r($row);exit;





if($row!=""){
    $url="https://".$_SERVER['HTTP_HOST']."/pay/fuermosipay/notify/".$row["orderno"]."/";
	$_POST['mishi'] = md5("aya".$row['orderno'].'7758521');
	
	//日志开始
	$Money = isset($sj_arr['money'])?daddslashes($sj_arr['money']):0;
	
	$DB->exec("INSERT INTO `pre_notify_rizhi` (`content`,`addtime`, `jine`,`zt`) VALUES (:content,:addtime, :jine,:zt)", [':content'=>$shuju,':addtime'=>time(),':jine'=>$Money,':zt'=>'ok']);
//日志结束
	
    echo http_request($url,$_POST);
}else{
	
	//日志开始
	$Money = isset($sj_arr['money'])?daddslashes($sj_arr['money']):0;
	
	$DB->exec("INSERT INTO `pre_notify_rizhi` (`content`,`addtime`, `jine`,`zt`) VALUES (:content,:addtime, :jine,:zt)", [':content'=>$shuju,':addtime'=>time(),':jine'=>$Money,':zt'=>'fail']);
//日志结束
	
    echo "ok";exit;
}

function http_request($url, $data = null)
{
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
    if (!empty($data)){
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    }
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
    $output = curl_exec($curl);
    curl_close($curl);
    return $output;
}
?>