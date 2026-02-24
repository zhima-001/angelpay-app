<?php
$zhi = 0;
if(isset($_GET['pid'])){
	$queryArr=$_GET;
	$is_defend=true;
}elseif(isset($_POST['pid'])){
	$queryArr=$_POST;
}else{
	$zhi = 1;
}
$nosession = true;
require './includes/common.php';
if($queryArr['request_method']!="JSON"){
@header('Content-Type: text/html; charset=UTF-8');
?>
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
    <script>
        var nowTimes = getNowTime();
        //其实说明是页面是刷新后加载的，不应该统计开始时间
        window.localStorage.setItem("startTime",nowTimes);
        window.localStorage.setItem("flag", "1");

        function getNowTime(){
            let nowTime = new Date().getTime();
            return nowTime;
        }
    </script>
</head>
<body>
    <?php
}

//写入日志信息
	$url1 = (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443') ? 'https://' : 'http://';
	$server_name = $url1 . $_SERVER['HTTP_HOST'];
	$c['pid'] = isset($queryArr['pid'])?$queryArr['pid']:0;
	$c['ddh'] =0;
	$c['henji'] = date("Y-m-d H:i:s",time()).'&nbsp;&nbsp;商户'.$c['pid'].'访问'.$server_name.$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING']."<br>";
	$c['getinfo'] = isset($queryArr)?json_encode($queryArr):'';
	$c['ip'] = $clientip;
	$c['addtime'] = time();
	//var_dump($c);
	 $int_id = xieInfo($c);
	 $usid = "用户ID：".$c['pid'];
	if($zhi == 1)
	{
		@header('Content-Type: text/html; charset=UTF-8');
		//xieCuowu('你还未配置支付接口商户！',$int_id);
		\lib\Zhifu::csasahangss(2,"还未配置支付接口商户",$usid);
		exit('你还未配置支付接口商户！');
	}
	//$_SESSION['int_id'] = $int_id;
	$_SESSION['int_id'] = "1";
	//$DB->exec("UPDATE `pre_rizhi` SET `ddh` =:ddh WHERE `id`=:id", [':ddh'=>$trade_no,':id'=>$int_id]);
//end 写入日志信息

use \lib\PayUtils;
$prestr=PayUtils::createLinkstring(PayUtils::argSort(PayUtils::paraFilter($queryArr)));
$pid=intval($queryArr['pid']);
if(empty($pid))sysmsg('PID不存在');
$userrow=$DB->query("SELECT `uid`,`gid`,`key`,`mode`,`pay`,`cert`,`status`,`userstype`,`jsonpayurl` FROM `pre_user` WHERE `uid`='{$pid}' LIMIT 1")->fetch();

$userstype = $userrow['userstype'];
$user_jsonpayurl = $userrow['jsonpayurl'];
if(!$userrow)sysmsg('商户不存在！');
if(!PayUtils::md5Verify($prestr, $queryArr['sign'], $userrow['key']))
{
	sysmsg('签名校验失败，请返回重试！');
}

if($userrow['status']==0 || $userrow['pay']==0){
    \lib\Zhifu::csasahangss(2,"商户已封禁，无法支付！",$usid,"","");
    sysmsg('商户已封禁，无法支付！');

}

$type=daddslashes($queryArr['type']);
//$type = "AliPayNX";
$out_trade_no=daddslashes($queryArr['out_trade_no']);
$notify_url=htmlspecialchars(daddslashes($queryArr['notify_url']));
$return_url=htmlspecialchars(daddslashes($queryArr['return_url']));
$name=htmlspecialchars(daddslashes($queryArr['name']));
$money=daddslashes($queryArr['money']);
$sitename=urlencode(base64_encode($queryArr['sitename']));
if(!empty($queryArr['stype'])){
     $stype = $queryArr['stype'];
}else{
    $stype = 0;
}


if(empty($out_trade_no))sysmsg('订单号(out_trade_no)不能为空');
if(empty($notify_url))sysmsg('通知地址(notify_url)不能为空');
if(empty($return_url))sysmsg('回调地址(return_url)不能为空');
if(empty($name))sysmsg('商品名称(name)不能为空');
if(empty($money))sysmsg('金额(money)不能为空');
if($money<=0 || !is_numeric($money) || !preg_match('/^[0-9.]+$/', $money))sysmsg('金额不合法');
if($conf['pay_maxmoney']>0 && $money>$conf['pay_maxmoney'])sysmsg('最大支付金额是'.$conf['pay_maxmoney'].'元');
if($conf['pay_minmoney']>0 && $money<$conf['pay_minmoney'])sysmsg('最小支付金额是'.$conf['pay_minmoney'].'元');
if(!preg_match('/^[a-zA-Z0-9.\_\-|]+$/',$out_trade_no))sysmsg('订单号(out_trade_no)格式不正确');

$domain=getdomain($notify_url);

if(!empty($conf['blockname'])){
	$block_name = explode('|',$conf['blockname']);
	foreach($block_name as $rows){
		if(!empty($rows) && strpos($name,$rows)!==false){
			$DB->exec("INSERT INTO `pre_risk` (`uid`, `url`, `content`, `date`) VALUES (:uid, :domain, :rows, NOW())", [':uid'=>$pid,':domain'=>$domain,':rows'=>$rows]);
			sysmsg($conf['blockalert']?$conf['blockalert']:'该商品禁止出售');
		}
	}
}
if($conf['cert_force']==1 && $userrow['cert']==0){
	sysmsg('当前商户未完成实名认证，无法收款');
}

$trade_no=date("YmdHis").rand(11111,99999);

$agent = strtolower($_SERVER['HTTP_USER_AGENT']);
    if(strpos($agent, 'iphone')){
            $terminals = 'IOS';
    }elseif ( strpos($agent, 'android')){
            $terminals = 'Android';
    }else{
            $terminals = 'PC';
    }
$insert_result = $DB->exec("INSERT INTO `pre_order` (`trade_no`,`out_trade_no`,`uid`,`addtime`,`name`,`money`,`notify_url`,`return_url`,`domain`,`ip`,`status`,`terminals`) VALUES (:trade_no, :out_trade_no, :uid, NOW(), :name, :money, :notify_url, :return_url, :domain, :clientip, 0,:terminals)", [':trade_no'=>$trade_no, ':out_trade_no'=>$out_trade_no, ':uid'=>$pid, ':name'=>$name, ':money'=>$money, ':notify_url'=>$notify_url, ':return_url'=>$return_url, ':domain'=>$domain, ':clientip'=>$clientip,':terminals'=>$terminals]);
if(!$insert_result){
    sysmsg('创建订单失败，请返回重试！');
}else{
    $tstaq = "生成订单，订单号：$trade_no";
    $queryArrs = "";
    foreach($queryArr as $k=>$v){
        $queryArrs .= $k.":".$v."\r\n";
    }
    \lib\Zhifu::csasahangss(0,"订单参数信息：\r\n".$queryArrs,$usid,$tstaq);
}

if($pid == "1062"){
    //生成IP订单号：
    //orderip($clientip,$money,$trade_no,'0');
}




//xieCuowu("生成订单，订单号：$trade_no",$int_id);

//\lib\Zhifu::csasahangss(0,"订单参数信息");


$DB->exec("UPDATE `pre_rizhi` SET `ddh` =:ddh WHERE `id`=:id", [':ddh'=>$trade_no,':id'=>$int_id]);
if(empty($type)){



	echo "<script>window.location.href='./cashier.php?trade_no={$trade_no}&sitename={$sitename}';</script>";
	exit;
}



// 获取订单支付方式ID、支付插件、支付通道、支付费率
$submitData = \lib\Channel::submit($type, $userrow['gid'],$money,$terminals,$stype,$userstype);
// var_dump($_POST);


// exit();
if($submitData){
	if($userrow['mode']==1){
		$realmoney = round($money*(100+100-$submitData['rate'])/100,2);
		$getmoney = $money;
	}else{
		$realmoney = $money;
		$getmoney = round($money*$submitData['rate']/100,2);
	}
	$DB->exec("UPDATE pre_order SET type='{$submitData['typeid']}',channel='{$submitData['channel']}',realmoney='$realmoney',getmoney='$getmoney' WHERE trade_no='$trade_no'");
}else{ //选择其他支付方式

      \lib\Zhifu::csasahangss(2,"用户下单失败，未查询到支付通道！",$usid,"","");

	echo "<script>window.location.href='./cashier.php?trade_no={$trade_no}&sitename={$sitename}&other=1';</script>";
	exit;
}

$order['trade_no'] = $trade_no;
$order['out_trade_no'] = $out_trade_no;
$order['uid'] = $pid;
$order['addtime'] = $date;
$order['name'] = $name;
$order['money'] = $realmoney;
$order['type'] = $submitData['typeid'];
$order['channel'] = $submitData['channel'];
$order['typename'] = $submitData['typename'];
$order['apptype'] = explode(',',$submitData['apptype']);

if($queryArr['request_method'] =="JSON"){
    $channel = \lib\Channel::get($order['channel']);
    	/**
     * PHP判断当前协议是否为HTTPS
     */
	    if ( !empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off') {
	         $url_top = "https://";
	    } elseif ( isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https' ) {
	         $url_top = "https://";
	    } elseif ( !empty($_SERVER['HTTP_FRONT_END_HTTPS']) && strtolower($_SERVER['HTTP_FRONT_END_HTTPS']) !== 'off') {
	        $url_top = "https://";
	    }else{
	         $url_top = "http://";
	    }
    if(empty($user_jsonpayurl)){
         $jsondiaourl = $conf['jsondiaourl'];
    }else{
         $jsondiaourl = $user_jsonpayurl;

    }

    // $url_top = (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443') ? 'https://' : 'http://';
    $pay_url = $jsondiaourl."/pay/".$submitData['plugin']."/submit/{$trade_no}/?sitename={$sitename}&trade_no={$trade_no}";
    $return_json = array(
        'code'=>200,
        'pay_url'=>$pay_url
    );
    echo json_encode($return_json);
    exit();

}else{
    $loadfile = \lib\Plugin::load2($submitData['plugin'], 'submit', $trade_no);
    $channel = \lib\Channel::get($order['channel']);
    if(!$channel || $channel['plugin']!=PAY_PLUGIN)sysmsg('当前支付通道信息不存在');
    $channel['apptype'] = explode(',',$channel['apptype']);
    $ordername = !empty($conf['ordername'])?ordername_replace($conf['ordername'],$order['name'],$order['uid']):$order['name'];


    include $loadfile;
}
?>
<p>正在为您跳转到支付页面，请稍候...</p>
</body>
</html>
