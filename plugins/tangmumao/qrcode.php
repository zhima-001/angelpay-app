<?php
$now_time1 = time();
if(!defined('IN_PLUGIN'))exit();

$rand= $DB->getRow("SELECT * FROM pay_rand WHERE orderno = '".TRADE_NO."' LIMIT 1");
$money=$rand['money'];


$order_info = $DB->getRow("SELECT * FROM pay_order WHERE trade_no = '" . TRADE_NO . "' LIMIT 1");

$out_trade_no = $order_info['out_trade_no']; //商户订单号
$tg_beizhu = $order_info['beizhu']; //是否是tg的订单
$tg_terminals = $order_info['terminals']; //是否是PC段

$djs=300-time()+$rand["time"];
$djs=$djs<0?0:$djs;
$gqsj = $rand['time']+300;//过期时间
$payurl=$rand['erweima'];
$count= $DB->getRow("SELECT count(*) FROM pay_order WHERE channel = '".$channel["id"]."'");
$count=$count['count(*)'];
$huiyuan = $DB->getRow("select kfqq,logo,kfurl from pre_user where uid=".$order['uid']);
$gqsj = strtotime($order['addtime'])+300;//过期时间
$djs=300-time()+strtotime($order['addtime']);
$money=$order['money'];
$djs=$djs<0?0:$djs;
$rand["orderno"] = TRADE_NO;
$leixing = $channel['type'];

if($leixing == '2')
{
	$l_type= '微信';
}
else
{
	$l_type= '支付宝';
}

$zhifu_leixing = $DB->getRow("SELECT * FROM pay_type WHERE id = ".$order['type']." LIMIT 1");
//var_dump($zhifu_leixing);

function getClientIPs($type = 0, $adv = false) {
    global $ip;
    $type = $type ? 1 : 0;
    if ($ip !== NULL)
        return $ip[$type];
    if ($adv) {
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $pos = array_search('unknown', $arr);
            if (false !== $pos)
                unset($arr[$pos]);
            $ip = trim($arr[0]);
        }elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
    } elseif (isset($_SERVER['REMOTE_ADDR'])) {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    // IP地址合法验证
    $long = sprintf("%u", ip2long($ip));
    $ip = $long ? array(
        $ip,
        $long) : array(
        '0.0.0.0',
        0);
    return $ip[$type];
}

require 'pay/config.php';
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


$api = $channel['apiurl'];//http://38.49.39.25:8888/api/unifiedorder
$huidiaourl = $channel['huidiaourl'];
if(empty($huidiaourl)){
    $huidiaourl = $conf['huidiaourl'];
}
function getUnixTimestamp ()
{
    list($s1, $s2) = explode(' ', microtime());
    return (float)sprintf('%.0f',(floatval($s1) + floatval($s2)) * 1000);
}
$pccode = $channel['pccode'];
$shortlink = $channel['shortlink'];
function getClientIP($type = 0, $adv = false) {
    global $ip;
    $type = $type ? 1 : 0;
    if ($ip !== NULL)
        return $ip[$type];
    if ($adv) {
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $pos = array_search('unknown', $arr);
            if (false !== $pos)
                unset($arr[$pos]);
            $ip = trim($arr[0]);
        }elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
    } elseif (isset($_SERVER['REMOTE_ADDR'])) {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    // IP地址合法验证
    $long = sprintf("%u", ip2long($ip));
    $ip = $long ? array(
        $ip,
        $long) : array(
        '0.0.0.0',
        0);
    return $ip[$type];
}
$weixinxianzhi =  99;//$channel['weixinxianzhi'];
$appxianzhi_url = $conf['appxianzhi'];
function getDomainName() {
    // 检查是否使用HTTPS
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    
    // 获取主机名
    $domainName = $_SERVER['HTTP_HOST'];
    
    return $protocol . $domainName;
}
if(empty($appxianzhi_url)){
    $appxianzhi_url = getDomainName();
}
$native = array(

        "merchantId"=>$merId,
        "orderId" => TRADE_NO, 
        "orderAmount" => $order['money'],
         //"notifyUrl" => $huidiaourl."huidiao.php/".TRADE_NO,
        "notifyUrl" => $huidiaourl."pay/tangmumao/notify/".TRADE_NO.'/',
        "returnUrl"=>$huidiaourl."pay/tangmumao/return/".TRADE_NO.'/',
        "channelType"=>$channel['appurl'],
); 
ksort($native);
$md5str = "";
foreach ($native as $key => $val) {
    $md5str = $md5str . $key . "=" . $val . "&";
}
//echo($md5str . "key=" . $Md5key);

$sign = md5($md5str  ."key=". $md5key);
$native['sign']=$sign;



//$submitData = \lib\Zhifu::http_post_data_two($api,json_encode($native));

$submitData = Http::post($api,$native);

//$resp = build_request($api, $data);
//echo '返回值:'.$resp;
//$submitData = json_decode($submitData,true);

/*
1，下单后上游给我们的响应和返回
2，支付成功后，上游给我们的回调和响应
*/
 

\lib\Zhifu::csasahangss(1,json_encode($submitData),"汤姆猫支付","下单");

//type : 0=使用商户链接  1=PC页面  2=使用PC页面的短码
$now_time2= time();

$end_time = $now_time2-$now_time1;
$userip = getClientIP(0,true);
//string(223) "{"code":1,"msg":"请求成功!","time":"1683186921","data":{"payurl":"http:\/\/pay.sjzxyswl.com\/index\/oldpay\/pay?i=20230435T168318692166940833","orderno":"2023050415552071901","sysorderno":"20230435T168318692166940833"}}"
$DB->exec("UPDATE pre_order SET shijian=shijian+'{$end_time}',ip='{$userip}' WHERE trade_no='".TRADE_NO."'");
if($tg_beizhu == "Tg"){
    
 if($submitData['code'] == "200"){        
        
        
        $arr = array(
           'status' => "success",
           'paytype' => $leixing,
           'trade_no' => TRADE_NO,
           'out_trade_no' => $out_trade_no,
           'pay_url' =>$submitData['data']['payUrl'],
           'type' => "0",
           "money"=>$order['money'],
           'return_url'=>$order_info['return_url']
        );
        if($tg_terminals == "PC"){
            if ($pccode == "1") {
                    if ($shortlink == "1") {
                        $arr['type'] =  "2";
        
                    } else {
                       $arr['type'] =  "1";
                    }
        
            
            }
        }
    
           
        
    
    } else {
        $arr = array(
            'status' => "error",
            'paytype' => $leixing,
            'trade_no' => TRADE_NO,
            'out_trade_no' => $out_trade_no,
            'pay_url' => "",
            'type' => "0",
            "money"=>$order['money'],
            'return_url'=>$order_info['return_url']
        );
       
    }
    
    
    echo json_encode($arr);
    exit();
}




 if($submitData['code'] == "200"){        
    $payUrl = $submitData['data']['payUrl']; 
    $payurl =$payUrl;
    $payurl2 = urlencode($payUrl);
    $gaibian = $DB->exec("UPDATE pre_order SET shangyouzhifu='{$payurl2}' WHERE trade_no='".TRADE_NO."'");
    if(!$gaibian){
          echo "拉取支付失败,请返回原来下单网页重新下单！";
          exit();
    }
     $agent = strtolower($_SERVER['HTTP_USER_AGENT']);
     if($pccode == "1"){
        if(strpos($agent, 'iphone') || strpos($agent, 'android')){
            
             ?>  
              <script type="text/javascript" src="/assets/111/js/jquery.min.js"></script>
            <script>

                    let nowTime = getNowTime();
                    window.localStorage.setItem("endTime",nowTime);
                    
                    //说明页面全部关闭，这时候可以传数据给后台
                     var startTime = window.localStorage.getItem("startTime");
                        //说明页面全部关闭，这时候可以传数据给后台
                        
                        var orderid = '<?=$order_info['trade_no'];?>';
                        $.post('/gaibian.php', {
                                end: nowTime,
                                start: startTime,
                                trade_no: orderid
                            }, function (ret) {
                               console.log(1);
                            });
           
                function getNowTime(){ 
                        let nowTime = new Date().getTime();
                        return nowTime;
                }
            
                
                </script>
            <?php
            
            echo "<span style='font-size:50px'>正在跳转，请不要关闭页面.....</span>";
            echo "<script>";
            echo "window.location.href='" .  $payurl . "'";
            echo ";</script>";
            exit;  
         }else{
             if($shortlink == "1"){
                  $DB->exec("INSERT INTO `pre_orderzhong` ( `order_sn`, `urlstr`) VALUES (:order_sn, :urlstr)", [':order_sn'=>TRADE_NO,':urlstr'=>$submitData['data']]);
             }
           
         }
    }else{
        
         ?>  
              <script type="text/javascript" src="/assets/111/js/jquery.min.js"></script>
            <script>

                    let nowTime = getNowTime();
                    window.localStorage.setItem("endTime",nowTime);
                    
                    //说明页面全部关闭，这时候可以传数据给后台
                     var startTime = window.localStorage.getItem("startTime");
                        //说明页面全部关闭，这时候可以传数据给后台
                        
                        var orderid = '<?=$order_info['trade_no'];?>';
                        $.post('/gaibian.php', {
                                end: nowTime,
                                start: startTime,
                                trade_no: orderid
                            }, function (ret) {
                               console.log(1);
                            });
           
                function getNowTime(){ 
                        let nowTime = new Date().getTime();
                        return nowTime;
                }
            
                
                </script>
            <?php
        
        echo "<p>正在为您跳转到支付页面，请稍候...</p>";
        echo "<script>";
        echo "window.location.href='" . $payurl. "'";
        echo ";</script>";
        exit;  
    }
     //if(strpos($agent, 'iphone') || strpos($agent, 'android')){
       
    //  }else{
    //     $DB->exec("INSERT INTO `pre_orderzhong` ( `order_sn`, `urlstr`) VALUES (:order_sn, :urlstr)", [':order_sn'=>TRADE_NO,':urlstr'=>$submitData["payurl"]]);
    //  }
   
}else{
    \lib\Zhifu::csasahangss(2,"单号:".TRADE_NO.":".json_encode($submitData),"长源电力支付","返回异常");
     $DB->exec("UPDATE pre_order SET ischeng='0' WHERE trade_no='".TRADE_NO."'");
    var_dump($submitData);
    echo "拉取支付失败";
    exit();
}

//	$payurl = $submitData['url'];
        /*if($shortlink == "1"){
             $payurl="https://".$_SERVER['HTTP_HOST']."/gongxifacai.php?order_sn=".TRADE_NO;
        }else{
           
              $payurl=$payUrl; 
        }*/
        if($weixinxianzhi == "0"){
             $payurl=$appxianzhi_url."/appxianzhi.php?order_sn=".TRADE_NO;
        }else{
             $payurl=$payurl;  
        }
?>  
<script>


window.addEventListener("beforeunload",function(){

    let nowTime = getNowTime();
    localStorage.setItem("endTime",nowTime);
    var startTime = localStorage.getItem("startTime");
    //说明页面全部关闭，这时候可以传数据给后台
    
    var orderid = '<?=$order_info['trade_no'];?>';
    $.post('/gaibian.php', {
            end: nowTime,
            start: startTime,
            trade_no: orderid
        }, function (ret) {
           console.log(1);
        });
    

 })
function getNowTime(){ 
        let nowTime = new Date().getTime();
        return nowTime;
}

</script>
<?php

    $agent = strtolower($_SERVER['HTTP_USER_AGENT']);
  
    if(!strpos($agent, 'iphone') && !strpos($agent, 'android')){
      
        if($l_type == "支付宝"){
            
    ?>
    
             <!DOCTYPE html><html><head><meta charset="utf-8">
        
        <meta name="keywords" content="">
        <meta name="description" content="">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>在线支付 - 支付宝 - 网上支付 安全快速！</title>
        <script type="text/javascript" src="/assets/111/js/jquery.min.js"></script>
        <script type="text/javascript" src="/assets/111/js/qrcode.js"></script>
        <script type="text/javascript" src="/assets/111/js/layer.js"></script>
        <link charset="utf-8" rel="stylesheet" href="/assets/111/css/front-old.css" media="all">
        <style>
            .switch-tip-icon-img {
                position: absolute;
                left: 70px;
                top: 70px;
                z-index: 11;
            }
            #codeico{
                position:fixed;
                z-index:9999999;
                width:43px; 
                height:43px;
                background:url('/public/image/alipay/T1Z5XfXdxmXXXXXXXX.png') no-repeat;
            }
            body{
                font-family:微软雅黑;	
            }
        </style>
    </head>

    <body>
        <div class="topbar">
            <div class="topbar-wrap fn-clear">
                <a href="" class="topbar-link-last" target="_blank" seed="goToHelp">常见问题</a>
                <span class="topbar-link-first">你好，欢迎使用支付宝付款！</span>
            </div>
        </div>
        <div id="header">
            <div class="header-container fn-clear">
                <div class="header-title">
                    <div class="alipay-logo">
                    </div>
                    <span class="logo-title">
                        我的收银台
                    </span>
                </div>
            </div>
        </div>

        <div id="container">
            <div id="content" class="fn-clear">
                <div id="J_order" class="order-area">
                    <div id="order" class="order order-bow">
                        <div class="orderDetail-base">
                            <div class="commodity-message-row">
                                <span class="first long-content">
                                    收款方：AP041                                </span> 交易单号：<?php echo $order['trade_no'] ; ?>　 ( 温馨提示：支付后可能会出现延迟30秒后提示成功，如有问题联请系客服)
                                <input id="order_num" value="F129575304149351" style="display:none">
                                <span class="second short-content">
                                    &nbsp;
                                </span>
                            </div>
                            <span class="payAmount-area" id="J_basePriceArea">
                                <strong class=" amount-font-22 ">
                                    <?=$order["money"]?></strong> 元
                            </span>

                        </div>
                    </div>
                </div>
                <!-- 操作区 -->
                <div class="cashier-center-container">
                    <div data-module="excashier/login/2015.08.02/loginPwdMemberT" id="J_loginPwdMemberTModule" class="cashiser-switch-wrapper fn-clear">
                        <!-- 扫码支付页面 -->
                        <div class="cashier-center-view view-qrcode fn-left" id="J_view_qr">

                            <!-- 扫码区域 -->
                            <div data-role="qrPayArea" class="qrcode-integration qrcode-area" id="J_qrPayArea">
                                <div class="qrcode-header">
                                    <div class="ft-center">
                                        扫一扫付款（元）
                                    </div>
                                    <div class="ft-center qrcode-header-money"><?=$order["money"]?></div>
                                    <input id="money" value="50.00" style="display:none">
                                </div>
                                <div class="qrcode-img-wrapper" id="payok">
                                    <div align="center">
                                    <!--     <div id="qrcode" class="qrimg"  width="210" height="210" style="position: relative;text-align: center;">
          
        </div>-->
                                        <font id="qrcode" class="qrimg"  width="168" height="168" >
                                            
                                            
                                        </font>
                                        <font id="queren"></font>
                                    </div>
                                    <div class="qrcode-img-explain fn-clear">
                                        <img class="fn-left" src="/assets/111/img/T1bdtfXfdiXXXXXXXX.png" alt="扫一扫标识">
                                        <div class="fn-left">
                                            打开手机支付宝<br><strong id="minute_show"><s></s>
                                                0分
                                            </strong>
                                            <strong id="second_show"><s></s>
                                                0秒
                                            </strong>过期</div>
                                    </div>
                                </div>
                                <div id="qrPayScanSuccess" class="mi-notice mi-notice-success  qrcode-notice fn-hide" style="display: none;margin-top: 5px;">
                                    <div class="mi-notice-cnt">
                                        <div class="mi-notice-title qrcode-notice-title">
                                            <i class="iconfont qrcode-notice-iconfont" title="扫描成功"></i>
                                            <p class="mi-notice-explain-other qrcode-notice-explain ft-break">
                                                <span class="ft-orange fn-mr5" data-role="qrPayAccount"></span>已创建订单，请在手机支付宝上完成付款
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 指引区域 -->
                            <div class="qrguide-area">
                                <img src="/assets/111/img/T13CpgXf8mXXXXXXXX.png" class="qrguide-area-img active">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div id="partner"><br><p>本站为第三方辅助软件服务商，与支付宝官方和淘宝网无任何关系<br>支付系统 不提供资金托管和结算，转账后将立即到达指定的账户。</p>
            <br><img alt="合作机构" src="/assets/111/img/2R3cKfrKqS.png"></div>
                <script type="text/javascript">
            var deadline_time = 0;
            var is = false;
            if (is == false) {
                deadline_time = 249;
                console.log(deadline_time);
                timer(deadline_time);
                is = true;
            }
            var intDiff = parseInt(deadline_time);//倒计时总秒数量
            function timer(intDiff) {
                console.log('start');
                console.log(intDiff);
                window.setInterval(function () {
                    var day = 0,
                            hour = 0,
                            minute = 0,
                            second = 0;//时间默认值       
                    if (intDiff > 0) {
                        day = Math.floor(intDiff / (60 * 60 * 24));
                        hour = Math.floor(intDiff / (60 * 60)) - (day * 24);
                        minute = Math.floor(intDiff / 60) - (day * 24 * 60) - (hour * 60);
                        second = Math.floor(intDiff) - (day * 24 * 60 * 60) - (hour * 60 * 60) - (minute * 60);
                    }
                    if (minute == 00 && second == 00)
                        document.getElementById('qrcode').innerHTML = '<br/><br/><br/><br/><br/><br/><br/><h2>二维码超时 请重新发起交易</h2><br/>';
                    if (minute <= 9)
                        minute = '0' + minute;
                    if (second <= 9)
                        second = '0' + second;
                    $('#day_show').html(day + "天");
                    $('#hour_show').html('<s id="h"></s>' + hour + '时');
                    $('#minute_show').html('<s></s>' + minute + '分');
                    $('#second_show').html('<s></s>' + second + '秒');
                    intDiff--;
                }, 1000);
            }


            // 订单详情
            $('#orderDetail .arrow').click(function (event) {
                if ($('#orderDetail').hasClass('detail-open')) {
                    $('#orderDetail .detail-ct').slideUp(500, function () {
                        $('#orderDetail').removeClass('detail-open');
                    });
                } else {
                    $('#orderDetail .detail-ct').slideDown(500, function () {
                        $('#orderDetail').addClass('detail-open');
                    });
                }
            });

    
        </script>

        <script language="Javascript">
            document.oncontextmenu = new Function("event.returnValue=false");
            document.onselectstart = new Function("event.returnValue=false");
        </script>
        <script type="text/javascript">
            document.oncontextmenu = function (e) {
                return false;
            }
        </script>
        <script type="text/javascript">
            document.onkeydown = function () {
                if (window.event && window.event.keyCode == 123) {
                    event.keyCode = 0;
                    event.returnValue = false;
                    return false;
                }
            };
            document.onkeydown = function (e) {
                e = window.event || e;
                var keycode = e.keyCode || e.which;
                if (keycode == 116) {
                    if (window.event) {// ie
                        try {
                            e.keyCode = 0;
                        } catch (e) {
                        }
                        e.returnValue = false;
                    } else {// firefox
                        e.preventDefault();
                    }
                }
            }
        </script>

<script>
    var openAliApp = 1;
</script>
<h1 class="scanheader text-center" style="display: none">
    
    <span class="alilogo" title="<?php echo $l_type ; ?>付款"></span>
   
   
</h1>




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
              //msg = "<span style='font-weight:600;color:blue'>距离结束还有:</span>" + minutes + "分" + seconds + "秒";
              
              $(".lbminitue").text(minutes);
               $(".lbseconds").text(seconds);
              //document.all["timer"].innerHTML = msg;
              --maxtime;
         } else{
             clearInterval(timer);
        }
   }
   timer = setInterval("CountDown()", 1000);
</script>


<?php if($rand["url"]==0){ ?>


<script>
	$ddjs_ = 0;
	function qh()
	{
		if($("#biaoshi").html()!="a")
		{
			if($ddjs_ == 0)
			{
				$ddjs_ = 1;
				$(".fk_djs").addClass("fk_hs");
			}
			else
			{
				$ddjs_ = 0;
				$(".fk_djs").removeClass("fk_hs");
			}
		}
	}
	setInterval('qh()',1000);
</script>



<?php } ?>







<script src="/assets/pay/js/qrcode.min.js"></script>
<script>
    $('.alreadypaid').click(function(){ispayed});



    function ispayed() {
		//return false;
        //	 $('#loading').show();
        //$('#msgContent p').html('请稍候，正在查询...');
        var orderid = '<?=$rand["orderno"]?>';
        $.post('/getshop.php', {
            type: "alipay",
            trade_no: orderid
        }, function (ret) {
            ret=eval('(' + ret + ')');
            if (ret.code == 1) {
                $('#msgContent p').html('请稍候，正在处理付款结果...');
                window.location.href = ret.backurl;
            }
        });

    }

    function toAliPay(){
        var payurl = '<?=$payurl?>';
        var qrcode = strToHexCharCode(payurl);
        var url = 'https://render.alipay.com/p/s/i?scheme=alipays://platformapi/startapp?saId=10000007&qrcode='+qrcode;
        //location.href = 'alipays://platformapi/startapp?appId=20000067&qrcode='+encodeURIComponent(url);
        location.href = 'alipays://platformapi/startapp?appId=10000007&qrcode='+qrcode;
        //location.href = payurl;
    }

    function toAliPay2(){
        var money = '<?=$money?>';
        //alipays://platformapi/startapp?appId=09999988&actionType=toAccount&goBack=NO&amount=2&userId=2088042281421811&memo=6666
        location.href = 'alipays://platformapi/startapp?appId=09999988&actionType=toAccount&goBack=NO&amount='+money+'&userId=2088042281421811&memo=6666';
    }

    function strToHexCharCode(str) {
        if(str === "")
            return "";
        var hexCharCode = [];
        //hexCharCode.push("%");
        for(var i = 0; i < str.length; i++) {
            hexCharCode.push((str.charCodeAt(i)).toString(16));
        }
        return '%'+hexCharCode.join("%");
    }

    function oderquery(t) {
        var orderid = '<?=$rand["orderno"]?>';
        $.post('/getshop.php', {
            type: "alipay",
            trade_no: orderid
        }, function (ret) {
            ret=eval('(' + ret + ')');
            if (ret.code == "1") {
                //$('#msgContent p').html('请稍候，正在处理付款结果...');
                window.location.href = ret.backurl;
            }
        });
        if(t<0) return;
        t = t + 1;
        setTimeout('oderquery(' + t + ')', 3000);
    }
    setTimeout('oderquery(1)', 100);

    if($('#qrcode').length > 0) {
        var qrcode = new QRCode(document.getElementById("qrcode"), {
            text: '<?=$payurl?>',
            width: 168,
            height: 168,
            colorDark: "#000000",
            colorLight: "#ffffff",
            correctLevel: QRCode.CorrectLevel.H
        });

        //$('#qrcode img').append($('#aliicon').html());
        // $('#aliicon').show();
        //location.href='{$order.pay_url}';
        $('#aliicon').css('display','block !important');
        //$('#topay').click();

        setTimeout(function () {
            console.log($('#qrcode').children());
            console.log($('#qrcode').children().eq(2));
            console.log($('#qrcode').children().eq(2).attr('src'));
            $("#saveQr").attr('href',$('#qrcode img').attr('src'));
            $('#aliicon').css('display','block !important');

            //$('#qrcode img').append($('#aliicon').html());
            //$('#aliicon').show();
        },100);
    }

    function copyToClipboard (text) {
        console.log('copyToClipboard')
        if(text.indexOf('-') !== -1) {
            let arr = text.split('-');
            text = arr[0] + arr[1];
        }
        var textArea = document.createElement("textarea");
        textArea.style.position = 'fixed';
        textArea.style.top = '0';
        textArea.style.left = '0';
        textArea.style.width = '2em';
        textArea.style.height = '2em';
        textArea.style.padding = '0';
        textArea.style.border = 'none';
        textArea.style.outline = 'none';
        textArea.style.boxShadow = 'none';
        textArea.style.background = 'transparent';
        textArea.value = text;
        document.body.appendChild(textArea);
        textArea.select();

        var successful = false
        try {
            var successful = document.execCommand('copy');
            if(successful) alert('成功复制到剪贴板');
            document.body.removeChild(textArea);
            return
        } catch (err) {
            console.log(err)
        }
        if(!successful){
            try{
                window.rmt.copyUrl(text);
            }catch (e) {
            }
            try{
                window.webkit.messageHandlers.copyUrl.postMessage({"params": ""+text});
            }catch (e) {
            }
        }

        document.body.removeChild(textArea);
    }

    $(function(){

    });


</script>




</body>
</html>
       
    
<?php
exit();
}
 }



function build_request($pay_url, $data)
    {
        $form = '<form style="display:none" name="order_form" method="post" action="' . $pay_url . '">';
        foreach ($data as $key => $val) {
            $form .= '<input type="hidden" name="' . $key . '" value="' . $val . '">';
        }
        $form .= '</form><script>document.order_form.submit();</script>';
        return $form;
    }
//echo $payurl;
function isMobile(){    
    $useragent=isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';    
    $useragent_commentsblock=preg_match('|\(.*?\)|',$useragent,$matches)>0?$matches[0]:'';      
    function CheckSubstrs($substrs,$text){    
        foreach($substrs as $substr)    
            if(false!==strpos($text,$substr)){    
                return true;    
            }    
            return false;    
    }  
    $mobile_os_list=array('Google Wireless Transcoder','Windows CE','WindowsCE','Symbian','Android','armv6l','armv5','Mobile','CentOS','mowser','AvantGo','Opera Mobi','J2ME/MIDP','Smartphone','Go.Web','Palm','iPAQ');  
    $mobile_token_list=array('Profile/MIDP','Configuration/CLDC-','160×160','176×220','240×240','240×320','320×240','UP.Browser','UP.Link','SymbianOS','PalmOS','PocketPC','SonyEricsson','Nokia','BlackBerry','Vodafone','BenQ','Novarra-Vision','Iris','NetFront','HTC_','Xda_','SAMSUNG-SGH','Wapaka','DoCoMo','iPhone','iPod');    
                
    $found_mobile=CheckSubstrs($mobile_os_list,$useragent_commentsblock) ||    
              CheckSubstrs($mobile_token_list,$useragent);    
                
    if ($found_mobile){    
        return true;    
    }else{    
        return false;    
    }    
}  
$agent = strtolower($_SERVER['HTTP_USER_AGENT']);
$is_ipad = (strpos($agent, 'ipad')) ? true : false;  
if (isMobile()||$is_ipad)  
{
	if($arr['data']['pay_url']!=''||1==1)
	{
?>
<script >


	document.location = '<?php echo $payurl;?>';

</script>
<?php 
exit;
}
}
?>
<!DOCTYPE html><html><head>
<link rel="icon" data-savepage-href="https://kkl.mylpwl.com/favicon.ico" >
<meta name="viewport" content="initial-scale=1, maximum-scale=1, user-scalable=no, width=device-width">
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="Content-Language" content="zh-cn">
<meta name="renderer" content="webkit">
<title>微信扫码支付</title>
<style data-savepage-href="/assets/css/wechat_pay.css?v=2" media="screen">@charset "UTF-8";html{font-size:62.5%;font-family:'helvetica neue',tahoma,arial,'hiragino sans gb','microsoft yahei','Simsun',sans-serif}body,div,dl,dt,dd,ul,ol,li,h1,h2,h3,h4,h5,h6,pre,code,form,fieldset,legend,input,button,textarea,p,blockquote,th,td,hr{margin:0;padding:0}body{line-height:1.333;font-size:12px}h1,h2,h3,h4,h5,h6{font-size:100%;font-family:arial,'hiragino sans gb','microsoft yahei','Simsun',sans-serif}input,textarea,select,button{font-size:12px;font-weight:normal}input[type="button"],input[type="submit"],select,button{cursor:pointer}table{border-collapse:collapse;border-spacing:0}address,caption,cite,code,dfn,em,th,var{font-style:normal;font-weight:normal}li{list-style:none}caption,th{text-align:left}q:before,q:after{content:''}abbr,acronym{border:0;font-variant:normal}sup{vertical-align:text-top}sub{vertical-align:text-bottom}fieldset,img,a img,iframe{border-width:0;border-style:none}img{-ms-interpolation-mode:bicubic}textarea{overflow-y:auto}legend{color:#000}a:link,a:visited{text-decoration:none}hr{height:0}label{cursor:pointer}.clearfix:after{content:"\200B";display:block;height:0;clear:both}.clearfix{*zoom:1}a{color:#328CE5}a:hover{color:#2b8ae8;text-decoration:none}a.hit{color:#C06C6C}a:focus{outline:none}.hit{color:#8DC27E}.txt_auxiliary{color:#A2A2A2}.clear{*zoom:1}.clear:before,.clear:after{content:"";display:table}.clear:after{clear:both}body,.body{background:#f7f7f7;height:100%}.mod-title{height:60px;line-height:60px;text-align:center;border-bottom:1px solid #ddd;background:#fff}.mod-title .ico-wechat{display:inline-block;width:41px;height:36px;background:/*savepage-url=./wechat-pay.png*/var(--savepage-url-3) 0 -115px no-repeat;vertical-align:middle;margin-right:7px}.mod-title .text{font-size:20px;color:#333;font-weight:normal;vertical-align:middle}.mod-ct{width:610px;padding:0 135px;margin:0 auto;margin-top:15px;background:#fff /*savepage-url=./wave.png*/ var(--savepage-url-4) top center repeat-x;text-align:center;color:#333;border:1px solid #e5e5e5;border-top:none}.mod-ct .order{font-size:20px;padding-top:30px}.mod-ct .amount{font-size:48px;margin-top:20px}.mod-ct .qr-image{margin-top:30px}.mod-ct .qr-image img{width:230px;height:230px}.mod-ct .detail{margin-top:60px;padding-top:25px}.mod-ct .detail .arrow .ico-arrow{display:inline-block;width:20px;height:11px;background:/*savepage-url=./wechat-pay.png*/var(--savepage-url-3) -25px -100px no-repeat}.mod-ct .detail .detail-ct{display:none;font-size:14px;text-align:right;line-height:28px}.mod-ct .detail .detail-ct dt{float:left}.mod-ct .detail-open{border-top:1px solid #e5e5e5}.mod-ct .detail .arrow{padding:6px 34px;border:1px solid #e5e5e5}.mod-ct .detail .arrow .ico-arrow{display:inline-block;width:20px;height:11px;background:/*savepage-url=./wechat-pay.png*/var(--savepage-url-3) -25px -100px no-repeat}.mod-ct .detail-open .arrow .ico-arrow{display:inline-block;width:20px;height:11px;background:/*savepage-url=./wechat-pay.png*/var(--savepage-url-3) 0 -100px no-repeat}.mod-ct .detail-open .detail-ct{display:block}.mod-ct .tip{margin-top:40px;border-top:1px dashed #e5e5e5;padding:30px 0;position:relative}.mod-ct .tip .ico-scan{display:inline-block;width:56px;height:55px;background:/*savepage-url=./wechat-pay.png*/var(--savepage-url-3) 0 0 no-repeat;vertical-align:middle;*display:inline;*zoom:1}.mod-ct .tip .tip-text{display:inline-block;vertical-align:middle;text-align:left;margin-left:23px;font-size:16px;line-height:28px;*display:inline;*zoom:1}.mod-ct .tip .dec{display:inline-block;width:22px;height:45px;background:/*savepage-url=./wechat-pay.png*/var(--savepage-url-3) 0 -55px no-repeat;position:absolute;top:-23px}.mod-ct .tip .dec-left{background-position:0 -55px;left:-136px}.mod-ct .tip .dec-right{background-position:-25px -55px;right:-136px}.foot{text-align:center;margin:30px auto;color:#888888;font-size:12px;line-height:20px;font-family:"simsun"}.foot .link{color:#0071ce}
@media (max-width:768px){.mod-ct{width:100%;padding:0;border:0}
.mod-ct .tip .dec-right{display:none}
.mod-ct .detail{margin-top:30px}
.mod-ct .detail .detail-ct{padding:0 19px;margin-bottom:19px}
}
.mobile-btn{margin-top:30px}
.mobile-tip{color:red;font-size:14px;max-width:92%;margin: 0 auto;}
.mobile-btn .mobile-tip{margin-bottom:30px;padding:15px 7px;border:1px dashed #06ae56;}
a.btn-copy-link{padding:10px 30px;cursor:pointer;color:#06ae56;background-color:#f2f2f2;font-size:20px;font-weight:700;}
</style>
<style data-savepage-href="/assets/js/new/layer.css?v=3.1.1">
    .layui-layer-imgbar,.layui-layer-imgtit a,.layui-layer-tab .layui-layer-title span,.layui-layer-title{text-overflow:ellipsis;white-space:nowrap}html #layuicss-layer{display:none;position:absolute;width:1989px}.layui-layer,.layui-layer-shade{position:fixed;_position:absolute;pointer-events:auto}.layui-layer-shade{top:0;left:0;width:100%;height:100%;_height:expression(document.body.offsetHeight+"px")}.layui-layer{-webkit-overflow-scrolling:touch;top:150px;left:0;margin:0;padding:0;background-color:#fff;-webkit-background-clip:content;border-radius:2px;box-shadow:1px 1px 50px rgba(0,0,0,.3)}.layui-layer-close{position:absolute}.layui-layer-content{position:relative}.layui-layer-border{border:1px solid #B2B2B2;border:1px solid rgba(0,0,0,.1);box-shadow:1px 1px 5px rgba(0,0,0,.2)}.layui-layer-load{background:/*savepage-url=loading-1.gif*/url() center center no-repeat #eee}.layui-layer-ico{background:/*savepage-url=icon.png*/url() no-repeat}.layui-layer-btn a,.layui-layer-dialog .layui-layer-ico,.layui-layer-setwin a{display:inline-block;*display:inline;*zoom:1;vertical-align:top}.layui-layer-move{display:none;position:fixed;*position:absolute;left:0;top:0;width:100%;height:100%;cursor:move;opacity:0;filter:alpha(opacity=0);background-color:#fff;z-index:2147483647}.layui-layer-resize{position:absolute;width:15px;height:15px;right:0;bottom:0;cursor:se-resize}.layer-anim{-webkit-animation-fill-mode:both;animation-fill-mode:both;-webkit-animation-duration:.3s;animation-duration:.3s}@-webkit-keyframes layer-bounceIn{0%{opacity:0;-webkit-transform:scale(.5);transform:scale(.5)}100%{opacity:1;-webkit-transform:scale(1);transform:scale(1)}}@keyframes layer-bounceIn{0%{opacity:0;-webkit-transform:scale(.5);-ms-transform:scale(.5);transform:scale(.5)}100%{opacity:1;-webkit-transform:scale(1);-ms-transform:scale(1);transform:scale(1)}}.layer-anim-00{-webkit-animation-name:layer-bounceIn;animation-name:layer-bounceIn}@-webkit-keyframes layer-zoomInDown{0%{opacity:0;-webkit-transform:scale(.1) translateY(-2000px);transform:scale(.1) translateY(-2000px);-webkit-animation-timing-function:ease-in-out;animation-timing-function:ease-in-out}60%{opacity:1;-webkit-transform:scale(.475) translateY(60px);transform:scale(.475) translateY(60px);-webkit-animation-timing-function:ease-out;animation-timing-function:ease-out}}@keyframes layer-zoomInDown{0%{opacity:0;-webkit-transform:scale(.1) translateY(-2000px);-ms-transform:scale(.1) translateY(-2000px);transform:scale(.1) translateY(-2000px);-webkit-animation-timing-function:ease-in-out;animation-timing-function:ease-in-out}60%{opacity:1;-webkit-transform:scale(.475) translateY(60px);-ms-transform:scale(.475) translateY(60px);transform:scale(.475) translateY(60px);-webkit-animation-timing-function:ease-out;animation-timing-function:ease-out}}.layer-anim-01{-webkit-animation-name:layer-zoomInDown;animation-name:layer-zoomInDown}@-webkit-keyframes layer-fadeInUpBig{0%{opacity:0;-webkit-transform:translateY(2000px);transform:translateY(2000px)}100%{opacity:1;-webkit-transform:translateY(0);transform:translateY(0)}}@keyframes layer-fadeInUpBig{0%{opacity:0;-webkit-transform:translateY(2000px);-ms-transform:translateY(2000px);transform:translateY(2000px)}100%{opacity:1;-webkit-transform:translateY(0);-ms-transform:translateY(0);transform:translateY(0)}}.layer-anim-02{-webkit-animation-name:layer-fadeInUpBig;animation-name:layer-fadeInUpBig}@-webkit-keyframes layer-zoomInLeft{0%{opacity:0;-webkit-transform:scale(.1) translateX(-2000px);transform:scale(.1) translateX(-2000px);-webkit-animation-timing-function:ease-in-out;animation-timing-function:ease-in-out}60%{opacity:1;-webkit-transform:scale(.475) translateX(48px);transform:scale(.475) translateX(48px);-webkit-animation-timing-function:ease-out;animation-timing-function:ease-out}}@keyframes layer-zoomInLeft{0%{opacity:0;-webkit-transform:scale(.1) translateX(-2000px);-ms-transform:scale(.1) translateX(-2000px);transform:scale(.1) translateX(-2000px);-webkit-animation-timing-function:ease-in-out;animation-timing-function:ease-in-out}60%{opacity:1;-webkit-transform:scale(.475) translateX(48px);-ms-transform:scale(.475) translateX(48px);transform:scale(.475) translateX(48px);-webkit-animation-timing-function:ease-out;animation-timing-function:ease-out}}.layer-anim-03{-webkit-animation-name:layer-zoomInLeft;animation-name:layer-zoomInLeft}@-webkit-keyframes layer-rollIn{0%{opacity:0;-webkit-transform:translateX(-100%) rotate(-120deg);transform:translateX(-100%) rotate(-120deg)}100%{opacity:1;-webkit-transform:translateX(0) rotate(0);transform:translateX(0) rotate(0)}}@keyframes layer-rollIn{0%{opacity:0;-webkit-transform:translateX(-100%) rotate(-120deg);-ms-transform:translateX(-100%) rotate(-120deg);transform:translateX(-100%) rotate(-120deg)}100%{opacity:1;-webkit-transform:translateX(0) rotate(0);-ms-transform:translateX(0) rotate(0);transform:translateX(0) rotate(0)}}.layer-anim-04{-webkit-animation-name:layer-rollIn;animation-name:layer-rollIn}@keyframes layer-fadeIn{0%{opacity:0}100%{opacity:1}}.layer-anim-05{-webkit-animation-name:layer-fadeIn;animation-name:layer-fadeIn}@-webkit-keyframes layer-shake{0%,100%{-webkit-transform:translateX(0);transform:translateX(0)}10%,30%,50%,70%,90%{-webkit-transform:translateX(-10px);transform:translateX(-10px)}20%,40%,60%,80%{-webkit-transform:translateX(10px);transform:translateX(10px)}}@keyframes layer-shake{0%,100%{-webkit-transform:translateX(0);-ms-transform:translateX(0);transform:translateX(0)}10%,30%,50%,70%,90%{-webkit-transform:translateX(-10px);-ms-transform:translateX(-10px);transform:translateX(-10px)}20%,40%,60%,80%{-webkit-transform:translateX(10px);-ms-transform:translateX(10px);transform:translateX(10px)}}.layer-anim-06{-webkit-animation-name:layer-shake;animation-name:layer-shake}@-webkit-keyframes fadeIn{0%{opacity:0}100%{opacity:1}}.layui-layer-title{padding:0 80px 0 20px;height:42px;line-height:42px;border-bottom:1px solid #eee;font-size:14px;color:#333;overflow:hidden;background-color:#F8F8F8;border-radius:2px 2px 0 0}.layui-layer-setwin{position:absolute;right:15px;*right:0;top:15px;font-size:0;line-height:initial}.layui-layer-setwin a{position:relative;width:16px;height:16px;margin-left:10px;font-size:12px;_overflow:hidden}.layui-layer-setwin .layui-layer-min cite{position:absolute;width:14px;height:2px;left:0;top:50%;margin-top:-1px;background-color:#2E2D3C;cursor:pointer;_overflow:hidden}.layui-layer-setwin .layui-layer-min:hover cite{background-color:#2D93CA}.layui-layer-setwin .layui-layer-max{background-position:-32px -40px}.layui-layer-setwin .layui-layer-max:hover{background-position:-16px -40px}.layui-layer-setwin .layui-layer-maxmin{background-position:-65px -40px}.layui-layer-setwin .layui-layer-maxmin:hover{background-position:-49px -40px}.layui-layer-setwin .layui-layer-close1{background-position:1px -40px;cursor:pointer}.layui-layer-setwin .layui-layer-close1:hover{opacity:.7}.layui-layer-setwin .layui-layer-close2{position:absolute;right:-28px;top:-28px;width:30px;height:30px;margin-left:0;background-position:-149px -31px;*right:-18px;_display:none}.layui-layer-setwin .layui-layer-close2:hover{background-position:-180px -31px}.layui-layer-btn{text-align:right;padding:0 15px 12px;pointer-events:auto;user-select:none;-webkit-user-select:none}.layui-layer-btn a{height:28px;line-height:28px;margin:5px 5px 0;padding:0 15px;border:1px solid #dedede;background-color:#fff;color:#333;border-radius:2px;font-weight:400;cursor:pointer;text-decoration:none}.layui-layer-btn a:hover{opacity:.9;text-decoration:none}.layui-layer-btn a:active{opacity:.8}.layui-layer-btn .layui-layer-btn0{border-color:#1E9FFF;background-color:#1E9FFF;color:#fff}.layui-layer-btn-l{text-align:left}.layui-layer-btn-c{text-align:center}.layui-layer-dialog{min-width:260px}.layui-layer-dialog .layui-layer-content{position:relative;padding:20px;line-height:24px;word-break:break-all;overflow:hidden;font-size:14px;overflow-x:hidden;overflow-y:auto}.layui-layer-dialog .layui-layer-content .layui-layer-ico{position:absolute;top:16px;left:15px;_left:-40px;width:30px;height:30px}.layui-layer-ico1{background-position:-30px 0}.layui-layer-ico2{background-position:-60px 0}.layui-layer-ico3{background-position:-90px 0}.layui-layer-ico4{background-position:-120px 0}.layui-layer-ico5{background-position:-150px 0}.layui-layer-ico6{background-position:-180px 0}.layui-layer-rim{border:6px solid #8D8D8D;border:6px solid rgba(0,0,0,.3);border-radius:5px;box-shadow:none}.layui-layer-msg{min-width:180px;border:1px solid #D3D4D3;box-shadow:none}.layui-layer-hui{min-width:100px;background-color:#000;filter:alpha(opacity=60);background-color:rgba(0,0,0,.6);color:#fff;border:none}.layui-layer-hui .layui-layer-content{padding:12px 25px;text-align:center}.layui-layer-dialog .layui-layer-padding{padding:20px 20px 20px 55px;text-align:left}.layui-layer-page .layui-layer-content{position:relative;overflow:auto}.layui-layer-iframe .layui-layer-btn,.layui-layer-page .layui-layer-btn{padding-top:10px}.layui-layer-nobg{background:0 0}.layui-layer-iframe iframe{display:block;width:100%}.layui-layer-loading{border-radius:100%;background:0 0;box-shadow:none;border:none}.layui-layer-loading .layui-layer-content{width:60px;height:24px;background:/*savepage-url=loading-0.gif*/url() no-repeat}.layui-layer-loading .layui-layer-loading1{width:37px;height:37px;background:/*savepage-url=loading-1.gif*/url() no-repeat}.layui-layer-ico16,.layui-layer-loading .layui-layer-loading2{width:32px;height:32px;background:/*savepage-url=loading-2.gif*/url() no-repeat}.layui-layer-tips{background:0 0;box-shadow:none;border:none}.layui-layer-tips .layui-layer-content{position:relative;line-height:22px;min-width:12px;padding:8px 15px;font-size:12px;_float:left;border-radius:2px;box-shadow:1px 1px 3px rgba(0,0,0,.2);background-color:#000;color:#fff}.layui-layer-tips .layui-layer-close{right:-2px;top:-1px}.layui-layer-tips i.layui-layer-TipsG{position:absolute;width:0;height:0;border-width:8px;border-color:transparent;border-style:dashed;*overflow:hidden}.layui-layer-tips i.layui-layer-TipsB,.layui-layer-tips i.layui-layer-TipsT{left:5px;border-right-style:solid;border-right-color:#000}.layui-layer-tips i.layui-layer-TipsT{bottom:-8px}.layui-layer-tips i.layui-layer-TipsB{top:-8px}.layui-layer-tips i.layui-layer-TipsL,.layui-layer-tips i.layui-layer-TipsR{top:5px;border-bottom-style:solid;border-bottom-color:#000}.layui-layer-tips i.layui-layer-TipsR{left:-8px}.layui-layer-tips i.layui-layer-TipsL{right:-8px}.layui-layer-lan[type=dialog]{min-width:280px}.layui-layer-lan .layui-layer-title{background:#4476A7;color:#fff;border:none}.layui-layer-lan .layui-layer-btn{padding:5px 10px 10px;text-align:right;border-top:1px solid #E9E7E7}.layui-layer-lan .layui-layer-btn a{background:#fff;border-color:#E9E7E7;color:#333}.layui-layer-lan .layui-layer-btn .layui-layer-btn1{background:#C9C5C5}.layui-layer-molv .layui-layer-title{background:#009f95;color:#fff;border:none}.layui-layer-molv .layui-layer-btn a{background:#009f95;border-color:#009f95}.layui-layer-molv .layui-layer-btn .layui-layer-btn1{background:#92B8B1}.layui-layer-iconext{background:/*savepage-url=icon-ext.png*/url() no-repeat}.layui-layer-prompt .layui-layer-input{display:block;width:230px;height:36px;margin:0 auto;line-height:30px;padding-left:10px;border:1px solid #e6e6e6;color:#333}.layui-layer-prompt textarea.layui-layer-input{width:300px;height:100px;line-height:20px;padding:6px 10px}.layui-layer-prompt .layui-layer-content{padding:20px}.layui-layer-prompt .layui-layer-btn{padding-top:0}.layui-layer-tab{box-shadow:1px 1px 50px rgba(0,0,0,.4)}.layui-layer-tab .layui-layer-title{padding-left:0;overflow:visible}.layui-layer-tab .layui-layer-title span{position:relative;float:left;min-width:80px;max-width:260px;padding:0 20px;text-align:center;overflow:hidden;cursor:pointer}.layui-layer-tab .layui-layer-title span.layui-this{height:43px;border-left:1px solid #eee;border-right:1px solid #eee;background-color:#fff;z-index:10}.layui-layer-tab .layui-layer-title span:first-child{border-left:none}.layui-layer-tabmain{line-height:24px;clear:both}.layui-layer-tabmain .layui-layer-tabli{display:none}.layui-layer-tabmain .layui-layer-tabli.layui-this{display:block}.layui-layer-photos{-webkit-animation-duration:.8s;animation-duration:.8s}.layui-layer-photos .layui-layer-content{overflow:hidden;text-align:center}.layui-layer-photos .layui-layer-phimg img{position:relative;width:100%;display:inline-block;*display:inline;*zoom:1;vertical-align:top}.layui-layer-imgbar,.layui-layer-imguide{display:none}.layui-layer-imgnext,.layui-layer-imgprev{position:absolute;top:50%;width:27px;_width:44px;height:44px;margin-top:-22px;outline:0;blr:expression(this.onFocus=this.blur())}.layui-layer-imgprev{left:10px;background-position:-5px -5px;_background-position:-70px -5px}.layui-layer-imgprev:hover{background-position:-33px -5px;_background-position:-120px -5px}.layui-layer-imgnext{right:10px;_right:8px;background-position:-5px -50px;_background-position:-70px -50px}.layui-layer-imgnext:hover{background-position:-33px -50px;_background-position:-120px -50px}.layui-layer-imgbar{position:absolute;left:0;bottom:0;width:100%;height:32px;line-height:32px;background-color:rgba(0,0,0,.8);background-color:#000\9;filter:Alpha(opacity=80);color:#fff;overflow:hidden;font-size:0}.layui-layer-imgtit *{display:inline-block;*display:inline;*zoom:1;vertical-align:top;font-size:12px}.layui-layer-imgtit a{max-width:65%;overflow:hidden;color:#fff}.layui-layer-imgtit a:hover{color:#fff;text-decoration:underline}.layui-layer-imgtit em{padding-left:10px;font-style:normal}@-webkit-keyframes layer-bounceOut{100%{opacity:0;-webkit-transform:scale(.7);transform:scale(.7)}30%{-webkit-transform:scale(1.05);transform:scale(1.05)}0%{-webkit-transform:scale(1);transform:scale(1)}}@keyframes layer-bounceOut{100%{opacity:0;-webkit-transform:scale(.7);-ms-transform:scale(.7);transform:scale(.7)}30%{-webkit-transform:scale(1.05);-ms-transform:scale(1.05);transform:scale(1.05)}0%{-webkit-transform:scale(1);-ms-transform:scale(1);transform:scale(1)}}.layer-anim-close{-webkit-animation-name:layer-bounceOut;animation-name:layer-bounceOut;-webkit-animation-fill-mode:both;animation-fill-mode:both;-webkit-animation-duration:.2s;animation-duration:.2s}@media screen and (max-width:1100px){.layui-layer-iframe{overflow-y:auto;-webkit-overflow-scrolling:touch}}</style>
<style id="savepage-cssvariables">
  :root {
    --savepage-url-3: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADgAAACXCAMAAAB3EmgXAAAABGdBTUEAALGPC/xhBQAAAAFzUkdCAK7OHOkAAANvaVRYdFhNTDpjb20uYWRvYmUueG1wAAAAAAA8P3hwYWNrZXQgYmVnaW49Iu+7vyIgaWQ9Ilc1TTBNcENlaGlIenJlU3pOVGN6a2M5ZCI/PiA8eDp4bXBtZXRhIHhtbG5zOng9ImFkb2JlOm5zOm1ldGEvIiB4OnhtcHRrPSJBZG9iZSBYTVAgQ29yZSA1LjUtYzAxNCA3OS4xNTE0ODEsIDIwMTMvMDMvMTMtMTI6MDk6MTUgICAgICAgICI+IDxyZGY6UkRGIHhtbG5zOnJkZj0iaHR0cDovL3d3dy53My5vcmcvMTk5OS8wMi8yMi1yZGYtc3ludGF4LW5zIyI+IDxyZGY6RGVzY3JpcHRpb24gcmRmOmFib3V0PSIiIHhtbG5zOnhtcE1NPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvbW0vIiB4bWxuczpzdFJlZj0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL3NUeXBlL1Jlc291cmNlUmVmIyIgeG1sbnM6eG1wPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvIiB4bXBNTTpPcmlnaW5hbERvY3VtZW50SUQ9InhtcC5kaWQ6ZWYyODkyMTMtZWMxOS00YjhlLTk1YTAtZDg4MjI4MmIyNmVkIiB4bXBNTTpEb2N1bWVudElEPSJ4bXAuZGlkOkJDNDBGQTRBRTM1RjExRTQ5RTgwRTdCMjNEOThDMjA2IiB4bXBNTTpJbnN0YW5jZUlEPSJ4bXAuaWlkOkJDNDBGQTQ5RTM1RjExRTQ5RTgwRTdCMjNEOThDMjA2IiB4bXA6Q3JlYXRvclRvb2w9IkFkb2JlIFBob3Rvc2hvcCBDQyAoTWFjaW50b3NoKSI+IDx4bXBNTTpEZXJpdmVkRnJvbSBzdFJlZjppbnN0YW5jZUlEPSJ4bXAuaWlkOjM2ZTUwMDMzLTI2ZDctYTc0Ny1iOGM3LWE5ZDljZDk2OGNmMyIgc3RSZWY6ZG9jdW1lbnRJRD0ieG1wLmRpZDpCNzNEN0M4Q0UzNUMxMUU0QjY3MEQwQjU2NkE4Q0UxMyIvPiA8L3JkZjpEZXNjcmlwdGlvbj4gPC9yZGY6UkRGPiA8L3g6eG1wbWV0YT4gPD94cGFja2V0IGVuZD0iciI/PhLgzx4AAAAZdEVYdFNvZnR3YXJlAEFkb2JlIEltYWdlUmVhZHlxyWU8AAAAQlBMVEVMaXHv7+8l0CUQzBDp6en8/Pz09PT////39/cAyACA5IDp+ulV21U71TvH88eT6JOg66B24na177XY99j0/fRm3mbKNClsAAAAAXRSTlMAQObYZgAAAzlJREFUaN7t2dlyrCAQAFBFWRRBcfn/X72NzigKDUpVbh5iP6QqwTOAbD2k4HzuVdXswZE4nqhUP3NecE0aN9IQgmheyKp5DptKFgZ+DpI/CDlAXaaAhvb8YfTQ2ALqlU+hBFREuoXHC19Y8Mx44V+HTWb8AnzH8YXZk/w9O16YB/Nyuezscc1X5yds3vLV7Aw5OyfP/hZQ13XJhLdMC2+FC1bCs8UeQlDBANMopIAYPClcaIOCFSgUoOj24BXacGt1IdR2PBSC0GIRgALamIBQKfMgq09PIJDW5QWWNb0D9zq/8FJfBNJNfiC71BeBIOkOqeciECbIDksmHkBhB2WFohaPoCg/sBQPIfTSQlo/hdBLCwM9TEBar9B/pSkIL8UWhFqagIxBAWPPoSihoBTPIbU7RLCLPwXFCsV/hL/Qx9zhyJ4AuVMue5JnL6vshfzdOp7C3M0qd3vM3ZCzj4DMQ8c/5ti9Yy54sNIkzD3K0eRBlBSFUFji6QplpQikK9TmTCyRIMEHXxKkNSWjN1IyeknJYKCoDQ+mYn+2bc/M2Exx/1trfzMhODTNcIK8c+TqOh6Aoy0ZT5AecnM0APWa6lbahXxWH7k6NXMfTp8UuZpcyOWadberI5L7UO5p+VrM4wUHDH8w2pQdIl1BO/+F2MtDX/cXYsOFDvAn0AmCTqk/FJfvonpoF6U6048Tjy9Rp2jq1em7ntG3oO78WzsyJKHswhd+ZIzDAb8rXGJwid0yKonCLn4/ua2NAFxSN5tLGA6J+pYp3FQZZaS3PdRjAMY6qNbBmGHpzR6ccNZtM6e3u0fvQezNVGbr2rhNQ+JBsr/zzusaTN/9j/oK9xrW2xa3a3CymeOjegw2Bj6fOF2DcXKvTwwKG3up0yrzXYdanScBDk8XSd5q6XBYHafybPxph8Nj3+2JPzztFTp3QIS7I3eJ8Qrdp8bTyJ1DXqG7NqBKg00/f5K7t2SEYPNW+3C4808KFVrI6gaUISjTbgjvOTrlWmyX0zdceF+dSKKd6E5OUamm+NmBHR194tChVXC6DMljbs1munE0inyvNJd+unGwdsdGM+lx1BPl/NaJTKrkRfLF/QOCidL5094dOAAAAABJRU5ErkJggg==);
    --savepage-url-4: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAB4AAAAHCAMAAAAoNw3DAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAA3NpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuNS1jMDE0IDc5LjE1MTQ4MSwgMjAxMy8wMy8xMy0xMjowOToxNSAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wTU09Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9tbS8iIHhtbG5zOnN0UmVmPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvc1R5cGUvUmVzb3VyY2VSZWYjIiB4bWxuczp4bXA9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC8iIHhtcE1NOk9yaWdpbmFsRG9jdW1lbnRJRD0ieG1wLmRpZDplZjI4OTIxMy1lYzE5LTRiOGUtOTVhMC1kODgyMjgyYjI2ZWQiIHhtcE1NOkRvY3VtZW50SUQ9InhtcC5kaWQ6QjY1RUZEQjFFMzVDMTFFNEI2NzBEMEI1NjZBOENFMTMiIHhtcE1NOkluc3RhbmNlSUQ9InhtcC5paWQ6QjY1RUZEQjBFMzVDMTFFNEI2NzBEMEI1NjZBOENFMTMiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENDIChNYWNpbnRvc2gpIj4gPHhtcE1NOkRlcml2ZWRGcm9tIHN0UmVmOmluc3RhbmNlSUQ9InhtcC5paWQ6ZjM0NDNlZmQtMDkwNy00NDc1LWJlOTYtNzRmOWRhZTg5MWVlIiBzdFJlZjpkb2N1bWVudElEPSJ4bXAuZGlkOmVmMjg5MjEzLWVjMTktNGI4ZS05NWEwLWQ4ODIyODJiMjZlZCIvPiA8L3JkZjpEZXNjcmlwdGlvbj4gPC9yZGY6UkRGPiA8L3g6eG1wbWV0YT4gPD94cGFja2V0IGVuZD0iciI/PqOuhZMAAAAtUExURff39////+7u7vX19fb29vr6+vHx8fT09PPz8+/v7+3t7erq6vDw8Pj4+Ozs7EoyMs4AAABBSURBVHjadMdLDsAgCAVAHqD4v/9xTWtMG6KzG6KXlEQ/rswU67VNAYR6qRkegY+VhkXjoTljS0N9i+DT2XUKMAClGgHHUVvOJwAAAABJRU5ErkJggg==);
  }
</style>

<meta name="savepage-title" content="微信扫码支付">
<meta name="savepage-pubdate" content="Unknown">
<meta name="savepage-date" content="Fri Apr 19 2024 16:23:46 GMT+0800 (伊尔库茨克标准时间)">
<meta name="savepage-state" content="Standard Items; Retain cross-origin frames; Merge CSS images; Remove unsaved URLs; Load lazy images in existing content; Max frame depth = 5; Max resource size = 50MB; Max resource time = 10s;">
<meta name="savepage-version" content="33.9">
<meta name="savepage-comments" content="">
  </head>
<body>
<div class="body">
<h1 class="mod-title">
<span class="ico-wechat"></span><span class="text">微信扫码支付</span>
</h1>
<div class="mod-ct">
<div class="order">
</div>
<div class="mobile-tip" style="display: none;">提示：二维码会风控，请复制下方链接支付</div>
<div class="amount">￥<?php echo $order["money"] ?></div>
<div align="center">
                    <span id="qrcode">
                    </span>
</div>

<div class="detail detail-open" id="orderDetail">
<dl class="detail-ct" style="display: block;">
<dt>商家</dt>
<dd id="storeName">在线充值</dd>

<dt>商户订单号</dt>
<dd id="billId"><?php echo $order["trade_no"] ?></dd>
<dt>创建时间</dt>
<dd id="createTime"><?php echo date('Y-m-d H:i:s',time())?></dd>
</dl>
<a href="javascript:void(0)" class="arrow"><i class="ico-arrow"></i></a>
</div>
<div class="tip">
<span class="dec dec-left"></span>
<span class="dec dec-right"></span>
<div class="ico-scan"></div>
<div class="tip-text">
<p>请使用微信扫一扫</p>
<p>扫描二维码完成支付</p>
</div>
</div>
<div class="tip-text">
</div>
</div>
<div class="foot">
<div class="inner">
<p>手机用户可保存上方二维码到手机中</p>
<p>在微信扫一扫中选择“相册”即可</p>
</div>
</div>
</div>
<script data-savepage-type="" type="text/plain" data-savepage-src="/assets/js/new/jquery/1.12.4/jquery.min.js"></script>
<script data-savepage-type="" type="text/plain" data-savepage-src="/assets/js/new/layer.min.js"></script>
<script data-savepage-type="" type="text/plain" data-savepage-src="/assets/js/new/jquery.qrcode.min.js"></script>
<script data-savepage-type="" type="text/plain" data-savepage-src="/assets/js/new/clipboard.min.js"></script>
<script data-savepage-type="" type="text/plain"></script>
<script src="/assets/weixinpay/jquery.js"></script>

<script type="text/javascript">
   

    function loadmsg() {
        $.ajax({
            type: "POST",
            dataType: "json",
            url: "/orderajax.html",
            timeout: 10000,
            data: {data: "DB83E9F1015071EE5E2E"},
            success: function (data, textStatus) {
                if (data.code == 1) {
                    document.getElementById('qrcode').innerHTML = '<br/><br/><br/><h4>支付成功！</h4><br/><br/>';
                 //   window.location.href = "";
                }
                if (data.code == 0) {
                    setTimeout("loadmsg()", 5000);
                }
            },
            error: function (XMLHttpRequest, textStatus, errorThrown) {
                if (textStatus == "timeout") {
                    setTimeout("loadmsg()", 5000);
                } else {
                    setTimeout("loadmsg()", 5000);
                }
            }
        });
    }

    window.onload = loadmsg();
</script>
<div id="mindzip-bubble-anchor" style="display: block;">
    <div id="mindzip-highlight-bubble" title="Save as a quote" style="display: none;">
        <button>
            <i class="iconMZlogo"></i>
        </button>
        <style>
            *, p, div {
                user-select: auto !important;
                -moz-user-select: auto !important;
                -webkit-user-select: auto !important;
            }

            <
            style

            /
            >
            <

            /
            div ></style>
    </div>
</div>
</body>
</html>


<script src="/assets/pay/js/common.min.js"></script>

<?php include 'pay_js.php'; ?>


<script src="/assets/pay/js/qrcode.min.js"></script>
<script>
    $('.alreadypaid').click(function(){ispayed});



    function ispayed() {
		//return false;
        //	 $('#loading').show();
        //$('#msgContent p').html('请稍候，正在查询...');
        var orderid = '<?=$rand["orderno"]?>';
        $.post('/getshop.php', {
            type: "alipay",
            trade_no: orderid
        }, function (ret) {
            ret=eval('(' + ret + ')');
            if (ret.code == 1) {
                $('#msgContent p').html('请稍候，正在处理付款结果...');
                window.location.href = ret.backurl;
            }
        });

    }

    function toAliPay(){
        var payurl = '<?=$payurl?>';
        var qrcode = strToHexCharCode(payurl);
        var url = 'https://render.alipay.com/p/s/i?scheme=alipays://platformapi/startapp?saId=10000007&qrcode='+qrcode;
        //location.href = 'alipays://platformapi/startapp?appId=20000067&qrcode='+encodeURIComponent(url);
        location.href = 'alipays://platformapi/startapp?appId=10000007&qrcode='+qrcode;
        //location.href = payurl;
    }

    function toAliPay2(){
        var money = '<?=$money?>';
        //alipays://platformapi/startapp?appId=09999988&actionType=toAccount&goBack=NO&amount=2&userId=2088042281421811&memo=6666
        location.href = 'alipays://platformapi/startapp?appId=09999988&actionType=toAccount&goBack=NO&amount='+money+'&userId=2088042281421811&memo=6666';
    }

    function strToHexCharCode(str) {
        if(str === "")
            return "";
        var hexCharCode = [];
        //hexCharCode.push("%");
        for(var i = 0; i < str.length; i++) {
            hexCharCode.push((str.charCodeAt(i)).toString(16));
        }
        return '%'+hexCharCode.join("%");
    }

    function oderquery(t) {
        var orderid = '<?=$rand["orderno"]?>';
        $.post('/getshop.php', {
            type: "alipay",
            trade_no: orderid
        }, function (ret) {
            ret=eval('(' + ret + ')');
            if (ret.code == "1") {
                //$('#msgContent p').html('请稍候，正在处理付款结果...');
                window.location.href = ret.backurl;
            }
        });
        if(t<0) return;
        t = t + 1;
        setTimeout('oderquery(' + t + ')', 3000);
    }
    setTimeout('oderquery(1)', 100);

    if($('#qrcode').length > 0) {
        var qrcode = new QRCode(document.getElementById("qrcode"), {
            text: '<?=$payurl?>',
            width: 210,
            height: 210,
            colorDark: "#000000",
            colorLight: "#ffffff",
            correctLevel: QRCode.CorrectLevel.H
        });

        //$('#qrcode img').append($('#aliicon').html());
        // $('#aliicon').show();
        //location.href='{$order.pay_url}';
        $('#aliicon').css('display','block !important');
        //$('#topay').click();

        setTimeout(function () {
            console.log($('#qrcode').children());
            console.log($('#qrcode').children().eq(2));
            console.log($('#qrcode').children().eq(2).attr('src'));
            $("#saveQr").attr('href',$('#qrcode img').attr('src'));
            $('#aliicon').css('display','block !important');

            //$('#qrcode img').append($('#aliicon').html());
            //$('#aliicon').show();
        },100);
    }

    function copyToClipboard (text) {
        console.log('copyToClipboard')
        if(text.indexOf('-') !== -1) {
            let arr = text.split('-');
            text = arr[0] + arr[1];
        }
        var textArea = document.createElement("textarea");
        textArea.style.position = 'fixed';
        textArea.style.top = '0';
        textArea.style.left = '0';
        textArea.style.width = '2em';
        textArea.style.height = '2em';
        textArea.style.padding = '0';
        textArea.style.border = 'none';
        textArea.style.outline = 'none';
        textArea.style.boxShadow = 'none';
        textArea.style.background = 'transparent';
        textArea.value = text;
        document.body.appendChild(textArea);
        textArea.select();

        var successful = false
        try {
            var successful = document.execCommand('copy');
            if(successful) alert('成功复制到剪贴板');
            document.body.removeChild(textArea);
            return
        } catch (err) {
            console.log(err)
        }
        if(!successful){
            try{
                window.rmt.copyUrl(text);
            }catch (e) {
            }
            try{
                window.webkit.messageHandlers.copyUrl.postMessage({"params": ""+text});
            }catch (e) {
            }
        }

        document.body.removeChild(textArea);
    }




</script>




</body>
</html>


