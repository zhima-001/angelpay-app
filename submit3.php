<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>正在为您跳转到支付页面，请稍候...</title>
    <style type="text/css">
        body {margin:0;padding:0;}
        p {position:absolute;
            left:50%;top:50%;
            width:330px;height:30px;
            margin:-35px 0 0 -160px;
            padding:20px;font:bold 14px/30px "宋体", Arial;
            background:#f9fafc url(../assets/img/loading.gif) no-repeat 20px 20px;
            text-indent:40px;border:1px solid #c5d0dc;}
        #waiting {font-family:Arial;}
    </style>
</head>
<body>
<?php
$is_defend=true;
$nosession = true;
require './includes/common.php';
$submit2=true;

@header('Content-Type: text/html; charset=UTF-8');

$typeid=intval($_GET['typeid']);  //支付方式：1：支付宝
$trade_no=daddslashes($_GET['trade_no']);  //创建的订单号


$order=$DB->getRow("SELECT * FROM pre_order WHERE trade_no='{$trade_no}' LIMIT 1");
if(!$order)sysmsg('该订单号不存在，请返回来源地重新发起请求！');

//获取用户信息：
$userrow = $DB->getRow("SELECT gid,mode FROM pre_user WHERE uid='{$order['uid']}' LIMIT 1");

// 获取订单支付方式ID、支付插件、支付通道、支付费率
$submitData = \lib\Channel::submit3($typeid, $userrow['gid']);

if($submitData){
    if($userrow['mode']==1 && $order['tid']!=4 || $order['tid']==2){
        $realmoney = round($order['money']*(100+100-$submitData['rate'])/100,2);
        $getmoney = $order['money'];
    }else{
        $realmoney = $order['money'];
        $getmoney = round($order['money']*$submitData['rate']/100,2);
    }
    $DB->exec("UPDATE pre_order SET type='{$submitData['typeid']}',channel='{$submitData['channel']}',realmoney='$realmoney',getmoney='$getmoney' WHERE trade_no='$trade_no'");



    $url1 = (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443') ? 'https://' : 'http://';
    $server_name = $url1 . $_SERVER['HTTP_HOST'];
    $c['pid'] = $order['uid'];
    $c['ddh'] =$trade_no;
    $c['henji'] = date("Y-m-d H:i:s",time()).'&nbsp;&nbsp;商户'.$c['pid'].'访问'.$server_name.$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING']."<br>";
    $c['getinfo'] = json_encode($order);
    $c['ip'] = $clientip;
    $c['addtime'] = time();
    //var_dump($c);
    $int_id = xieInfo($c);
    xieCuowu("生成订单，订单号：$trade_no",$int_id);
}else{
    sysmsg('<center>当前支付方式无法使用</center>', '跳转提示');
}

/*$order['type'] = $submitData['typeid'];
$order['channel'] = $submitData['channel'];
$order['typename'] = $submitData['typename'];
$order['apptype'] = explode(',',$submitData['apptype']);
$order['money'] = $realmoney;

$loadfile = \lib\Plugin::load2($submitData['plugin'], 'submit', $trade_no);
$channel = \lib\Channel::get($order['channel']);
if(!$channel || $channel['plugin']!=PAY_PLUGIN)sysmsg('当前支付通道信息不存在');
$channel['apptype'] = explode(',',$channel['apptype']);
$ordername = !empty($conf['ordername'])?ordername_replace($conf['ordername'],$order['name'],$order['uid']):$order['name'];
include $loadfile;*/
//先写死吧：
$channel_info = $DB->getRow("SELECT appid,appkey FROM pre_channel WHERE id = 28 LIMIT 1");
$params['mch_id'] = $channel_info['appid'];
$params['trade_type'] = "AliPaySPC";
$params['money'] = $order['money']; //支付金额
$params['out_order_no'] = $order['trade_no'];
$params['notify_url'] = $order['notify_url'];    //交易类型
$params['back_url'] = $order['notify_url'];              //签名
$params['mch_create_ip'] = $order['ip'];              //签名

$sign = \lib\Zhifu::sign($params,$channel_info);
$params['sign'] = $sign;              //签名
/*var_dump($params);
exit();*/
//var_dump($params);
$submitData = \lib\Zhifu::send_post($params); 
$datas = json_decode($submitData,true);

if(strpos($datas['message'],'guid') === false){ 
 var_dump($datas);
 exit();
}

echo "<script>";
echo "window.location.href='" .  $datas['message'] . "'";
echo ";</script>";
exit;

?>
<p>正在为您跳转到支付页面，请稍候...</p>
</body>
</html>
