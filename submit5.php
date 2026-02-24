<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>支付宝</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <script src="./assets/js/new/jquery.min.js"></script>
    <!-- 最新版本的 Bootstrap 核心 CSS 文件 -->
    <link rel="stylesheet" href="./assets/js/new/bootstrap.min.css" integrity="sha384-HSMxcRTRxnN+Bdg0JdbxYKrThecOKuH5zCYotlSAcp1+c8xmyTe9GYg1l9a69psu" crossorigin="anonymous">

    <!-- 可选的 Bootstrap 主题文件（一般不用引入） -->
    <link rel="stylesheet" href="./assets/js/new/bootstrap-theme.min.css" integrity="sha384-6pzBo3FDv/PJ8r2KRkGHifhEocL+1X2rVCTTkUfGk7/0pbek5mMa1upzvWbrUbOZ" crossorigin="anonymous">

    <!-- 最新的 Bootstrap 核心 JavaScript 文件 -->
    <script src="./assets/js/new/bootstrap.min.js" integrity="sha384-aJ21OjlMXNL5UyIl/XNwTMqvzeRMZH2w8c5cRVpzpU8Y5bApTppSuUkhZXN0VxHd" crossorigin="anonymous"></script>
    <script src="./assets/js/new/layer.js"></script>
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

	$csrf_token = md5(mt_rand(0,999).time());
	$_SESSION['csrf_tokens'] = $csrf_token;

        //调整页面的信息：
        if($order['status'] == '1'){
             sysmsg('订单已经处理完毕！');
        }
        $now_time = time();
        if($now_time>strtotime($order['addtime'])+600){
             sysmsg('订单已经失效');
        }
        if(!empty($order['sjm'])){
             sysmsg('订单正在处理中！');
        }
        
        
    
?>
<div class="container-fluid">
    <div class="form-group" style="margin-top: 15px">
        <div class="row">
            <div class="col-lg-12" style="text-align: center">

                <h3 style="color: red;font-weight: 800">支付宝口令红包</h3>
            </div>
        </div>
    </div>
    <div class="input-group">
        <span class="input-group-addon" id="basic-addon1" style="color:black;font-weight: 600">充值金额</span>
        <input type="text" id="money" name="money" class="form-control" placeholder="充值金额" value="<?php echo $order['money']?>" readonly="readonly" aria-describedby="basic-addon1">
    </div>
    <div class="input-group">
        <span class="input-group-addon" id="basic-addon1" style="color:black;font-weight: 600">支付方式</span>
        <input type="text" class="form-control" placeholder="支付宝" readonly="readonly" aria-describedby="basic-addon1">
    </div>
    <div class="input-group">
        <span class="input-group-addon" id="basic-addon1" style="color:black;font-weight: 600">红包口令</span>
        <input type="text" class="form-control" placeholder="红包口令" name="kouling" aria-describedby="basic-addon1">
    </div>
    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token?>">
    <div class="form-group" style="margin-top: 15px">
        <div class="row">
            <div class="col-lg-12">
                <button type="button" id="tijiao" onclick="submitPay(this)" class="btn btn-default btn-lg btn-block" style="background: red"><span style="color: white">确认支付</span></button>

            </div>
        </div>
    </div>
    <div class="form-group" style="margin-top: 15px">
        <div class="row">
            <div class="col-lg-12">
                <button type="button" class="btn btn-default btn-lg btn-block" style="background: blue"><span style="color: white">跳转到支付宝红包</span></button>

            </div>
        </div>
    </div>

    <div class="form-group" style="margin-top: 15px">
        <div class="row">
            <div class="col-lg-12" style="text-align: center">
                <div id="timer" style="color:red"></div>
                <div id="warring" style="color:red"></div>
            </div>
        </div>
    </div>
    <div class="form-group" style="margin-top: 15px">
        xxxxxxx
        我是一系列的文本
    </div>
</div>



<script>
    <?php
        $now_time = time();
        $end_time = strtotime($order['addtime'])+600-$now_time;
        
        
    ?>

    var maxtime =<?php echo $end_time?>; //一个小时，按秒计算，自己调整!
    function CountDown() {
          if (maxtime >= 0) {
              minutes = Math.floor(maxtime / 60);
              seconds = Math.floor(maxtime % 60);
              msg = "<span style='font-weight:600;color:blue'>距离结束还有:</span>" + minutes + "分" + seconds + "秒";
              document.all["timer"].innerHTML = msg;
              --maxtime;
         } else{
             clearInterval(timer);
        }
   }
   timer = setInterval("CountDown()", 1000);
   
   function submitPay(obj){
    	var csrf_token=$("input[name='csrf_token']").val();
    	var money=$("input[name='money']").val();
    	var kouling=$("input[name='kouling']").val();
    	if(kouling==''){ 
    		layer.alert("支付宝口令红包不能为空");
    		return false;
    	}
    	var trade_no = "<?php echo $trade_no?>";
    	var ii = layer.load();
    	$.ajax({
    		type: "POST",
    		dataType: "json",
    		data: {money:money, csrf_tokens:csrf_token,'trade_no':trade_no,'kouling':kouling},
    		url: "ajax2.php?act=alipyhuidiao",
    		success: function (data, textStatus) {
    			layer.close(ii);
    			if (data.code == -1) {
    				layer.alert(data.msg, {icon: 2});
    				
    			}else{
    			   // var iis = layer.load();
    				layer.alert(data.msg, {icon: 0,closeBtn: 0});
    			    $(".layui-layer-btn").css("display","none");
    			    
    			}
    		},
    		error: function (data) {
    			layer.msg('服务器错误', {icon: 2});
    		}
    	});
    	return false;

    }
    function findreturns() {
        var trade_no = "<?php echo $trade_no?>";
        $.ajax({
    		type: "POST",
    		dataType: "json",
    		data: {'trade_no':trade_no},
    		url: "ajax2.php?act=alipyhuidiaochaxun",
    		success: function (data, textStatus) {
    		
    			if (data.code == 1) {
    			 
                    window.location.href="/paypage/success2.php?trade_no="+trade_no;
    			}
    		},
    		error: function (data) {
    			layer.msg('服务器错误', {icon: 2});
    		}
    	});
          
   }
   timersss = setInterval("findreturns()", 1000);
</script>

</head>
</body>
</html>



