<?php
$url="https://www.pays.top/app/pay.php";
$appid="Gp20201201194421";
$key="IWZCMzWd7vUE56lfda35XrFkSpCDSlnz";

$data=json_decode(file_get_contents("./taotaoing.txt"),true);


/*$data["gw_mchid"]=$appid;
$data["gw_notify"]="http://ceshi.freewing15.xyz/zfbjjknotify.php";
$data["gw_order"]="T".date("YmdHis").rand(100,999);
$data["gw_price"]=2.99*100;
$data["gw_rand"]=rand(100,999);
$data["gw_type"]=0;*/
unset($data["gw_sign"]);
ksort($data);
reset($data);
$str="";
foreach($data as $k=>$v){
    $str.=$k."=".$v."&";
}
echo strtolower(md5($str."key=".$key));exit;
//$data["gw_sign"]=strtolower(md5($str."key=".$key));
$data["gw_extra"]="";
$data["gw_return"]="";

$sHtml = "<form id='alipaysubmit' name='alipaysubmit' action='".$url."' method='POST' style='display:none;'>";
foreach($data as $k=>$v){
    $sHtml.= "<input type='hidden' name='".$k."' value='".$v."'/>";
}

//submit按钮控件请不要含有name属性
$sHtml = $sHtml."<input type='submit' value='提交'></form>";
$sHtml = $sHtml."<script>document.forms['alipaysubmit'].submit();</script>";
echo $sHtml;
?>