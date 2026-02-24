<?php
//echo "Success";exit;
file_put_contents("./news.txt",json_encode($_POST));
//$_POST=json_decode(file_get_contents("./tttss.txt"),true);
$nosession = true;
require './includes/common.php';

$Money = isset($_POST['Money'])?daddslashes($_POST['Money']):0;
$alipay_account = isset($_POST['alipay_account'])?daddslashes($_POST['alipay_account']):'';
$Paytime = isset($_POST['Paytime'])?$_POST['Paytime']:'';
$time=strtotime($Paytime)-300;
$row= $DB->getRow("SELECT * FROM pay_rand WHERE type = 'zfbjk' and zfb = '".$alipay_account."' and money='".$Money."' and time > '".$time."' LIMIT 1");
//$row= $DB->getRow("SELECT * FROM pay_rand WHERE zfb = '".$alipay_account."' and money='".$Money."' LIMIT 1");
//echo "SELECT * FROM pay_rand WHERE zfb = '".$alipay_account."' and money='".$Money."' and time > '".$time."' LIMIT 1";
//print_r($row);exit;
if($row!=""){
    $url="https://".$_SERVER['HTTP_HOST']."/pay/zfbjk/notify/".$row["orderno"]."/";
    echo http_request($url,$_POST);
}else{
    echo "error";
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