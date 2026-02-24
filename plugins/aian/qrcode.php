<?php

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
function getSign($data, $signkey){
        $data = array_filter($data); //去空
        ksort($data); //排序
        $tmp_string = http_build_query($data); //进行键值对排列  a=1&b=2&c=3
        $tmp_string = urldecode($tmp_string); //参数无需进行urlencode ,上一步进行了urlencode,这里还原一下
        return md5( $tmp_string .'&key='. $signkey );  //签名生成
    }


$api = $channel['apiurl'];//http://38.49.39.25:8888/api/unifiedorder
$huidiaourl = $channel['huidiaourl'];
if(empty($huidiaourl)){
    $huidiaourl = $conf['huidiaourl'];
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

$native = array(

        "merchantId"=>$merId,
        "orderId" => TRADE_NO,
        "channelType" => $channel['appurl'],
        "notifyUrl" => $huidiaourl."pay/aian/notify/".TRADE_NO.'/',
        "returnUrl" => $huidiaourl."pay/aian/return/".TRADE_NO.'/', 
        "orderAmount" => $order['money'],
        'isForm'=>2
); 
ksort($native);
$md5str = "";
foreach ($native as $key => $val) {
    $md5str = $md5str . $key . "=" . $val . "&";
}

$sign =getSign($native, $md5key);
$native["sign"] = $sign;
// $native['isForm']="2";





$submitData = Http::post($api,$native);



//$resp = build_request($api, $data);
//echo '返回值:'.$resp;
$submitData = json_decode($submitData,true);
/*
1，下单后上游给我们的响应和返回
2，支付成功后，上游给我们的回调和响应
*/

 

\lib\Zhifu::csasahangss(1,json_encode($submitData),"埃安支付","下单");

//type : 0=使用商户链接  1=PC页面  2=使用PC页面的短码

if($tg_beizhu == "Tg"){
    
    if($submitData['code'] == "200"){
        $arr = array(
           'status' => "success",
           'paytype' => $leixing,
           'trade_no' => TRADE_NO,
           'out_trade_no' => $out_trade_no,
           'pay_url' => $submitData['data']['payUrl'],
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
    
    
     $agent = strtolower($_SERVER['HTTP_USER_AGENT']);
    if($pccode == "1"){
        if(strpos($agent, 'iphone') || strpos($agent, 'android')){
            echo "<p>正在为您跳转到支付页面，请稍候...</p>";
            echo "<script>";
            echo "window.location.href='" .  $submitData['data']['payUrl'] . "'";
            echo ";</script>";
            exit;  
         }else{
             if($shortlink == "1"){
                  $DB->exec("INSERT INTO `pre_orderzhong` ( `order_sn`, `urlstr`) VALUES (:order_sn, :urlstr)", [':order_sn'=>TRADE_NO,':urlstr'=>$submitData['payUrl']]);
             }
           
         }
    }else{
        echo "<p>正在为您跳转到支付页面，请稍候...</p>";
        echo "<script>";
        echo "window.location.href='" .  $submitData['data']['payUrl']. "'";
        echo ";</script>";
        exit;  
    }
     //if(strpos($agent, 'iphone') || strpos($agent, 'android')){
       
    //  }else{
    //     $DB->exec("INSERT INTO `pre_orderzhong` ( `order_sn`, `urlstr`) VALUES (:order_sn, :urlstr)", [':order_sn'=>TRADE_NO,':urlstr'=>$submitData["payurl"]]);
    //  }
   
}else{
    var_dump($submitData);
    echo "拉取支付失败";
    exit();
}

//	$payurl = $submitData['url'];
        if($shortlink == "1"){
             $payurl="https://".$_SERVER['HTTP_HOST']."/gongxifacai.php?order_sn=".TRADE_NO;
        }else{
           
              $payurl=$submitData['data']['payUrl']; 
        }


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
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="author" content="YKFAKA">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta http-equiv="Access-Control-Allow-Origin" content="*">
    <link rel="stylesheet" href="/assets/weixinpay/pay_iconfont.css">
    <link rel="stylesheet" href="/assets/weixinpay/pay_style.css">
    <script type="text/javascript" src="/assets/weixinpay/qrcode.js"></script>
    <title>微信付款</title>
    <style data-cursor="cursor">body,
    .cursorsDefault,
    input[type=range]::-webkit-slider-thumb,
    input[type=range]::-webkit-slider-runnable-track {
        cursor: url("data:image/svg+xml;utf8,%0D%0A%0D%0A%3Csvg%20width%3D%2226px%22%20height%3D%2226px%22%20version%3D%221.1%22%20id%3D%22Layer_1%22%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20xmlns%3Axlink%3D%22http%3A%2F%2Fwww.w3.org%2F1999%2Fxlink%22%20x%3D%220px%22%20y%3D%220px%22%20viewBox%3D%220%200%20967.6%20965.4%22%20style%3D%22enable-background%3Anew%200%200%20967.6%20965.4%3B%22%20xml%3Aspace%3D%22preserve%22%20%20%3E%0D%0A%3Cstyle%20type%3D%22text%2Fcss%22%3E%0D%0A%09.st0%7Bfill%3A%231A9C00%3B%7D%0D%0A%09.st1%7Bfill%3Anone%3B%7D%0D%0A%09.st2%7Bfill%3A%236DB70E%3B%7D%0D%0A%09.st3%7Bfill%3A%23D0021F%3B%7D%0D%0A%09.st4%7Bfill%3A%23EE244F%3B%7D%0D%0A%09.st5%7Bfill%3A%230D1930%3B%7D%0D%0A%3C%2Fstyle%3E%0D%0A%3Cpath%20class%3D%22st0%22%20d%3D%22M290.5%2C965.4C361.9%2C952%2C566%2C904.3%2C741.3%2C722.4c175.1-181.5%2C215.5-386.9%2C226.2-458.7L3.8%2C2.5L290.5%2C965.4z%22%3E%3C%2Fpath%3E%0D%0A%3Cg%3E%0D%0A%09%3Cpath%20class%3D%22st1%22%20d%3D%22M309.6%2C961.8c0.3-0.2%2C0.7-0.3%2C1-0.5c-7.5%2C1.6-14.3%2C3-20.1%2C4.1l-45.2-151.9L135.5%2C923.1l40.2%2C40.2%0D%0A%09%09c1.8-0.2%2C3.5-0.3%2C5.2-0.3c5.5%2C0%2C10.8%2C0.7%2C15.8%2C2c34.4%2C0.1%2C68.8%2C1%2C103.2%2C1C303%2C964.4%2C306.2%2C962.9%2C309.6%2C961.8z%22%3E%3C%2Fpath%3E%0D%0A%09%3Cpath%20class%3D%22st1%22%20d%3D%22M352.1%2C957.9c0.5-0.1%2C0.9-0.3%2C1.4-0.4c38.2-7.6%2C77.5-13.3%2C116.5-14.4c34.3-1%2C68.5%2C0.1%2C102.7-3.2%0D%0A%09%09c8.1-1.6%2C16-3.9%2C24-6c15.1-4.1%2C30.7-6.8%2C46.3-7.7c10.3-0.6%2C20.4%2C2.3%2C29.4%2C7.1l129.8-129.8l-70.8-70.8%0D%0A%09%09C586%2C878.3%2C423.1%2C934.2%2C333.3%2C956.1c0.6%2C0%2C1.1%2C0%2C1.7%2C0C340.7%2C956.2%2C346.5%2C956.8%2C352.1%2C957.9z%22%3E%3C%2Fpath%3E%0D%0A%09%3Cpath%20class%3D%22st2%22%20d%3D%22M310.9%2C961.2c6.9-3.4%2C14.3-5.1%2C22.1-5c89.8-21.9%2C252.8-77.7%2C398.3-223.6L528.7%2C529.8L245.3%2C813.4l45.2%2C151.9%0D%0A%09%09C296.4%2C964.2%2C303.3%2C962.8%2C310.9%2C961.2z%22%3E%3C%2Fpath%3E%0D%0A%3C%2Fg%3E%0D%0A%3Cpath%20class%3D%22st3%22%20d%3D%22M662.2%2C646.3c142.7-148%2C195.9-311.7%2C216.6-406.2L4%2C3l260.2%2C874.5C358%2C853.2%2C519.6%2C794.1%2C662.2%2C646.3z%22%3E%3C%2Fpath%3E%0D%0A%3Cg%3E%0D%0A%09%3Cpath%20class%3D%22st1%22%20d%3D%22M636.1%2C671.1c-84.4%2C85.6-175%2C140.6-253.9%2C176.1c5.1-0.1%2C10.3-0.3%2C15.4-0.8c11.6-2.7%2C22.6-8.1%2C34.4-10.5%0D%0A%09%09c6.7-2.5%2C13.5-5.1%2C20.1-7.8c21.3-9.4%2C42.1-19.9%2C62.1-31.8c17.9-10.7%2C35.8-22.2%2C52.7-34.6c6.5-5.5%2C12.6-11.4%2C18.2-17.8%0D%0A%09%09c14.3-20.1%2C29.2-39.9%2C45.4-58.5c3.1-3.6%2C6.8-6.6%2C10.9-9.1L636.1%2C671.1z%22%3E%3C%2Fpath%3E%0D%0A%09%3Cpath%20class%3D%22st4%22%20d%3D%22M263.3%2C876.9c92.3-23.7%2C250.2-81.4%2C391-224.3L4.7%2C3L3%2C2.5L263.3%2C876.9z%22%3E%3C%2Fpath%3E%0D%0A%3C%2Fg%3E%0D%0A%3Cellipse%20transform%3D%22matrix(0.7071%20-0.7071%200.7071%200.7071%20-174.33%20272.0702)%22%20class%3D%22st5%22%20cx%3D%22241.3%22%20cy%3D%22346.5%22%20rx%3D%2224.1%22%20ry%3D%2236.4%22%3E%3C%2Fellipse%3E%0D%0A%3Cellipse%20transform%3D%22matrix(0.7071%20-0.7071%200.7071%200.7071%20-257.9868%20412.3861)%22%20class%3D%22st5%22%20cx%3D%22368.8%22%20cy%3D%22517.6%22%20rx%3D%2224.1%22%20ry%3D%2236.4%22%3E%3C%2Fellipse%3E%0D%0A%3Cellipse%20transform%3D%22matrix(0.7071%20-0.7071%200.7071%200.7071%20-152.8403%20370.9521)%22%20class%3D%22st5%22%20cx%3D%22371.4%22%20cy%3D%22370%22%20rx%3D%2224.1%22%20ry%3D%2236.4%22%3E%3C%2Fellipse%3E%0D%0A%3Cellipse%20transform%3D%22matrix(0.7071%20-0.7071%200.7071%200.7071%20-204.3166%20495.1171)%22%20class%3D%22st5%22%20cx%3D%22495.5%22%20cy%3D%22494.2%22%20rx%3D%2224.1%22%20ry%3D%2236.4%22%3E%3C%2Fellipse%3E%0D%0A%3Cellipse%20transform%3D%22matrix(0.7071%20-0.7071%200.7071%200.7071%20-97.2462%20478.979)%22%20class%3D%22st5%22%20cx%3D%22529.6%22%20cy%3D%22356.9%22%20rx%3D%2224.1%22%20ry%3D%2236.4%22%3E%3C%2Fellipse%3E%0D%0A%3Cellipse%20transform%3D%22matrix(0.7071%20-0.7071%200.7071%200.7071%20-68.2423%20314.9851)%22%20class%3D%22st5%22%20cx%3D%22346.1%22%20cy%3D%22239.9%22%20rx%3D%2224.1%22%20ry%3D%2236.4%22%3E%3C%2Fellipse%3E%0D%0A%3C%2Fsvg%3E%0D%0A") 0 0, auto !important;
    }

    a,
    button,
    .cursorsHover,
    [cursorshover] {
        cursor: url("data:image/svg+xml;utf8,%0D%0A%0D%0A%3Csvg%20width%3D%2226px%22%20height%3D%2226px%22%20version%3D%221.1%22%20id%3D%22Layer_1%22%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20xmlns%3Axlink%3D%22http%3A%2F%2Fwww.w3.org%2F1999%2Fxlink%22%20x%3D%220px%22%20y%3D%220px%22%20viewBox%3D%220%200%20936.2%20766.1%22%20style%3D%22enable-background%3Anew%200%200%20936.2%20766.1%3B%22%20xml%3Aspace%3D%22preserve%22%20%20%3E%0D%0A%3Cstyle%20type%3D%22text%2Fcss%22%3E%0D%0A%09.st0%7Bfill%3A%231A9C00%3B%7D%0D%0A%09.st1%7Bfill%3Anone%3B%7D%0D%0A%09.st2%7Bfill%3A%236DB70E%3B%7D%0D%0A%09.st3%7Bfill%3A%23DA002E%3B%7D%0D%0A%09.st4%7Bfill%3A%23EE244F%3B%7D%0D%0A%09.st5%7Bfill%3A%2300182D%3B%7D%0D%0A%3C%2Fstyle%3E%0D%0A%3Cpath%20class%3D%22st0%22%20d%3D%22M493.6%2C292.7c-5.8%2C6.1-12.4%2C11.3-19.9%2C15.5c-11.7%2C9.7-25.2%2C15.9-40.5%2C18.7c-15%2C3.9-29.9%2C4-44.6%2C0.2%0D%0A%09c-15.7-1.3-30.1-6.5-43.3-15.6c-26.7-17-43.3-43.2-51.3-73.1c-4.4-20-3.6-39.9%2C2.3-59.5c1.9-4.2%2C3.9-8.4%2C5.8-12.6l-2.7-1.8%0D%0A%09c-9.4%2C8-21.1%2C13.9-32.1%2C16.3c-18.1%2C4-38.9%2C0.4-54.6-9.6c-16.6-10.5-26.9-26.8-31.8-45.4c-3.4-12.7-2-26%2C2.1-38.2L53.9%2C2.3%0D%0A%09C-57.7%2C239.6%2C17.6%2C523.5%2C227.3%2C670.9c218.8%2C153.8%2C526.5%2C118.6%2C708.9-85.8L493.6%2C292.7z%22%3E%3C%2Fpath%3E%0D%0A%3Cg%3E%0D%0A%09%3Cpath%20class%3D%22st1%22%20d%3D%22M53.7%2C3.5c-3-0.2-5.9-0.5-8.8-0.9C40.1%2C10%2C34.5%2C16.8%2C27.9%2C23c-1.7%2C14.3-5.9%2C27.9-12.7%2C40.8%0D%0A%09%09C14%2C67.7%2C12.6%2C71.4%2C11%2C75l2%2C14.6c0.5%2C12.4-0.9%2C24.3-3.9%2C35.7c0.1%2C7.9-0.6%2C15.7-2%2C23.2c0.2%2C12-1.3%2C23.5-4.4%2C34.5%0D%0A%09%09c-0.4%2C4.6-1.1%2C9.1-2%2C13.6c0.4%2C3.3%2C0.9%2C6.7%2C1.3%2C10c0.3%2C7.6-0.1%2C14.9-1.1%2C22.1c0.7%2C5.3%2C1.4%2C10.6%2C2.1%2C15.9c0.4%2C9.6-0.4%2C18.8-2.1%2C27.8%0D%0A%09%09c0.4%2C2.7%2C0.7%2C5.5%2C1.1%2C8.2c0%2C0.2%2C0%2C0.4%2C0%2C0.7l1%2C7.3c0%2C0.3%2C0%2C0.5%2C0%2C0.8c1%2C7.4%2C2%2C14.8%2C3%2C22.2c0.3%2C7.7-0.1%2C15.3-1.2%2C22.6%0D%0A%09%09c0.7%2C5.5%2C1.5%2C10.9%2C2.2%2C16.4c0.4%2C9.8-0.4%2C19.3-2.2%2C28.4c0.1%2C0.5%2C0.1%2C1%2C0.2%2C1.6c0.2%2C5.3%2C0.1%2C10.5-0.4%2C15.7c3%2C10.3%2C6.6%2C20.4%2C10.5%2C30.4%0D%0A%09%09c6.3%2C23.4%2C7.2%2C46.8%2C2.8%2C70.1c0%2C0.2%2C0%2C0.3%2C0.1%2C0.5c0.4%2C9.3-0.3%2C18.4-2%2C27.2c1.2%2C1.3%2C2.4%2C2.6%2C3.6%2C3.8c16.4%2C17.7%2C30.7%2C36.9%2C45.3%2C56.1%0D%0A%09%09c6.6%2C8.7%2C12.5%2C17.9%2C17.1%2C27.8c6.6%2C14.4%2C12.9%2C29.1%2C18.5%2C43.9c0.9%2C2.3%2C1.6%2C4.6%2C2.3%2C6.9c0.8%2C2.7%2C1.5%2C5.5%2C2%2C8.3%0D%0A%09%09c1.8%2C9.4%2C3.1%2C18.9%2C4.2%2C28.4l91.1%2C58l47.2-74.2c-6.6-4.2-13.1-8.6-19.5-13.1C18.7%2C523.3-56.9%2C240.5%2C53.7%2C3.5z%22%3E%3C%2Fpath%3E%0D%0A%09%3Cpath%20class%3D%22st1%22%20d%3D%22M225.4%2C6.2c-1.3%2C0.5-2.6%2C0.9-4%2C1.4c-18.5%2C9.7-38.5%2C14.2-59.8%2C13.4c-26.1-0.1-50.5-6.2-73.2-18.4%0D%0A%09%09c-7.7%2C1.2-15.6%2C1.7-23.8%2C1.4c-2.5%2C0-4.9-0.1-7.4-0.2l126.4%2C83.5c-4.2%2C12.3-5.5%2C25.5-2.1%2C38.2c5%2C18.6%2C15.3%2C34.9%2C31.8%2C45.4%0D%0A%09%09c15.7%2C10%2C36.5%2C13.6%2C54.6%2C9.6c11-2.5%2C22.7-8.3%2C32.1-16.3l2.7%2C1.8c-1.9%2C4.2-3.9%2C8.4-5.8%2C12.6c-5.9%2C19.6-6.7%2C39.5-2.3%2C59.5%0D%0A%09%09c8%2C29.9%2C24.6%2C56.1%2C51.3%2C73.1c13.2%2C9.1%2C27.7%2C14.3%2C43.3%2C15.6c14.7%2C3.8%2C29.6%2C3.7%2C44.6-0.2c15.3-2.8%2C28.8-9.1%2C40.5-18.7%0D%0A%09%09c7.5-4.3%2C14.1-9.5%2C19.9-15.5l1.4%2C0.9l52.1-82L225.4%2C6.2z%22%3E%3C%2Fpath%3E%0D%0A%09%3Cpath%20class%3D%22st2%22%20d%3D%22M494.2%2C292.3c-5.8%2C6.1-12.4%2C11.2-19.9%2C15.5c-11.7%2C9.7-25.2%2C15.9-40.5%2C18.7c-15%2C3.9-29.9%2C4-44.6%2C0.2%0D%0A%09%09c-15.7-1.3-30.1-6.5-43.3-15.6c-26.7-17-43.3-43.2-51.3-73.1c-4.4-20-3.6-39.9%2C2.3-59.5c1.9-4.2%2C3.9-8.4%2C5.8-12.6l-2.7-1.8%0D%0A%09%09c-9.4%2C8-21.1%2C13.9-32.1%2C16.3c-18.1%2C4-38.9%2C0.4-54.6-9.6c-16.6-10.5-26.9-26.8-31.8-45.4c-3.4-12.7-2-26%2C2.1-38.2L57.3%2C3.7%0D%0A%09%09c-1.2-0.1-2.4-0.1-3.5-0.2c-110.6%2C237-35.1%2C519.8%2C174.2%2C666.9c6.4%2C4.5%2C13%2C8.9%2C19.5%2C13.1l248.2-390.3L494.2%2C292.3z%22%3E%3C%2Fpath%3E%0D%0A%3C%2Fg%3E%0D%0A%3Cpath%20class%3D%22st3%22%20d%3D%22M492.2%2C292c-5.8%2C6-12.4%2C11.2-19.8%2C15.4c-11.7%2C9.7-25.2%2C15.9-40.5%2C18.7c-15%2C3.9-29.9%2C4-44.6%2C0.2%0D%0A%09c-15.7-1.3-30.1-6.5-43.3-15.6c-26.7-17-43.3-43.2-51.3-73.1c-4.4-20-3.6-39.9%2C2.3-59.5c1.9-4.2%2C3.9-8.3%2C5.8-12.5l-1.1-0.7%0D%0A%09c-9.6%2C8.4-21.7%2C14.7-33.2%2C17.2c-18.1%2C4-38.9%2C0.4-54.6-9.6c-16.6-10.5-26.9-26.8-31.8-45.4c-3.5-13.2-1.9-26.9%2C2.6-39.6l-50-33%0D%0A%09C42.1%2C247%2C103.2%2C477.2%2C273.3%2C596.7c177.5%2C124.7%2C427%2C96.2%2C574.9-69.6L492.2%2C292z%22%3E%3C%2Fpath%3E%0D%0A%3Cg%3E%0D%0A%09%3Cpath%20class%3D%22st1%22%20d%3D%22M494.8%2C294.5L293.6%2C610.8c175.7%2C108.7%2C411.9%2C76.2%2C554.2-83.3L494.8%2C294.5z%22%3E%3C%2Fpath%3E%0D%0A%09%3Cpath%20class%3D%22st4%22%20d%3D%22M491.7%2C292.4c-5.8%2C6-12.4%2C11.2-19.8%2C15.4c-11.7%2C9.7-25.2%2C15.9-40.5%2C18.7c-15%2C3.9-29.9%2C4-44.6%2C0.2%0D%0A%09%09c-15.7-1.3-30.1-6.5-43.3-15.6c-26.7-17-43.3-43.2-51.3-73.1c-4.4-20-3.6-39.9%2C2.3-59.5c1.9-4.2%2C3.9-8.3%2C5.8-12.5l-1.1-0.7%0D%0A%09%09c-9.6%2C8.4-21.7%2C14.7-33.2%2C17.2c-18.1%2C4-38.9%2C0.4-54.6-9.6c-16.6-10.5-26.9-26.8-31.8-45.4c-3.5-13.2-1.9-26.9%2C2.6-39.6l-50-33%0D%0A%09%09c-90.5%2C192.6-29.4%2C422.8%2C140.7%2C542.3c6.8%2C4.8%2C13.7%2C9.3%2C20.7%2C13.6l201.2-316.4L491.7%2C292.4z%22%3E%3C%2Fpath%3E%0D%0A%3C%2Fg%3E%0D%0A%3Cellipse%20transform%3D%22matrix(0.5367%20-0.8438%200.8438%200.5367%20-111.2746%20264.9641)%22%20class%3D%22st5%22%20cx%3D%22185.6%22%20cy%3D%22233.8%22%20rx%3D%2212.1%22%20ry%3D%2223%22%3E%3C%2Fellipse%3E%0D%0A%3Cellipse%20transform%3D%22matrix(0.5367%20-0.8438%200.8438%200.5367%20-209.936%20331.9243)%22%20class%3D%22st5%22%20cx%3D%22197.3%22%20cy%3D%22357.1%22%20rx%3D%2212.1%22%20ry%3D%2223%22%3E%3C%2Fellipse%3E%0D%0A%3Cellipse%20transform%3D%22matrix(0.5367%20-0.8438%200.8438%200.5367%20-155.7375%20403.0641)%22%20class%3D%22st5%22%20cx%3D%22289.2%22%20cy%3D%22343.3%22%20rx%3D%2212.1%22%20ry%3D%2223%22%3E%3C%2Fellipse%3E%0D%0A%3Cellipse%20transform%3D%22matrix(0.5367%20-0.8438%200.8438%200.5367%20-254.3027%20470.0304)%22%20class%3D%22st5%22%20cx%3D%22300.9%22%20cy%3D%22466.6%22%20rx%3D%2212.1%22%20ry%3D%2223%22%3E%3C%2Fellipse%3E%0D%0A%3Cellipse%20transform%3D%22matrix(0.5367%20-0.8438%200.8438%200.5367%20-302.8737%20585.6184)%22%20class%3D%22st5%22%20cx%3D%22381.8%22%20cy%3D%22568.6%22%20rx%3D%2212.1%22%20ry%3D%2223%22%3E%3C%2Fellipse%3E%0D%0A%3Cellipse%20transform%3D%22matrix(0.5367%20-0.8438%200.8438%200.5367%20-218.8018%20658.7197)%22%20class%3D%22st5%22%20cx%3D%22490.4%22%20cy%3D%22528.6%22%20rx%3D%2212.1%22%20ry%3D%2223%22%3E%3C%2Fellipse%3E%0D%0A%3Cellipse%20transform%3D%22matrix(0.5367%20-0.8438%200.8438%200.5367%20-271.0748%20729.7089)%22%20class%3D%22st5%22%20cx%3D%22528.9%22%20cy%3D%22611.7%22%20rx%3D%2212.1%22%20ry%3D%2223%22%3E%3C%2Fellipse%3E%0D%0A%3Cellipse%20transform%3D%22matrix(0.5367%20-0.8438%200.8438%200.5367%20-170.672%20791.7002)%22%20class%3D%22st5%22%20cx%3D%22635.6%22%20cy%3D%22551.3%22%20rx%3D%2212.1%22%20ry%3D%2223%22%3E%3C%2Fellipse%3E%0D%0A%3Cellipse%20transform%3D%22matrix(0.5367%20-0.8438%200.8438%200.5367%20-115.1466%20687.6194)%22%20class%3D%22st5%22%20cx%3D%22568.6%22%20cy%3D%22448.7%22%20rx%3D%2212.1%22%20ry%3D%2223%22%3E%3C%2Fellipse%3E%0D%0A%3C%2Fsvg%3E%0D%0A") 0 0, auto !important;
    }

    </style>
    <style>@font-face {
        font-family: "mindzip";
        src: url("chrome-extension://cmkhjlckcaeahimgmlnihmphjkcccopm/fonts/mindzip.eot");
        src: url("chrome-extension://cmkhjlckcaeahimgmlnihmphjkcccopm/fonts/mindzip.eot?#iefix") format("embedded-opentype"),
        url("chrome-extension://cmkhjlckcaeahimgmlnihmphjkcccopm/fonts/mindzip.woff") format("woff"),
        url("chrome-extension://cmkhjlckcaeahimgmlnihmphjkcccopm/fonts/mindzip.ttf") format("truetype"),
        url("chrome-extension://cmkhjlckcaeahimgmlnihmphjkcccopm/fonts/mindzip.svg#mindzip") format("svg");
        font-weight: normal;
        font-style: normal;
        d
    }

    @font-face {
        font-family: "mindzip-icons";
        src: url("chrome-extension://cmkhjlckcaeahimgmlnihmphjkcccopm/fonts/MZ-iconFont.eot");
        src: url("chrome-extension://cmkhjlckcaeahimgmlnihmphjkcccopm/fonts/MZ-iconFont.eot?#iefix") format("embedded-opentype"),
        url("chrome-extension://cmkhjlckcaeahimgmlnihmphjkcccopm/fonts/MZ-iconFont.woff") format("woff"),
        url("chrome-extension://cmkhjlckcaeahimgmlnihmphjkcccopm/fonts/MZ-iconFont.ttf") format("truetype"),
        url("chrome-extension://cmkhjlckcaeahimgmlnihmphjkcccopm/fonts/MZ-iconFont.svg#mindzip") format("svg");
        font-weight: normal;
        font-style: normal;
        d
    }</style>
</head>

<body>
<div style="margin-top:50px;">
    <div class="prcen-x touying paymentBox">
        <div class="dn" style="display: block;">
            <div class="h60px  tac lh60 pr prcen-xy-parent-dja" id="wxicon">
                <i class="lightgreen fs42 mr10"></i>
            </div>
            <div class="bg2 tac pb30">
                <p class="ptb15">
                    <span class="fs18 ml10">请支付</span>
                    <span class="fs42 englishFont fwb red price"><?php echo $order["money"] ?></span>
                    <span class="fs18 ml10">元</span>
                </p>
                <div align="center">
                    <span id="qrcode">
                    </span>
                </div>
                <p class="mt15 fs16">
                    <!--<span class="fs16 fwb" style="color:red;">请务必使用【手机浏览器】扫一扫，直接微信扫会"失败"</span> <br>-->
                    <!--<span class="fs16 fwb" style="color:red;">请务必使用【手机浏览器】扫一扫，直接微信扫会"失败"</span>-->
                    <!--<br><br>-->
                    请于<span class="red ml5 fwb  lastTime"><?php echo date('Y-m-d H:i:s',$gqsj)?></span>前完成支付
                </p>
                <span class="fs16 mb25 mt10" style="color:red;">付款成功后稍等片刻会自动跳转</span>
                <p class="fs16 fwb mtb10">订单编号:
                    <span class="ml10 orderNumber"><?php echo $order["trade_no"] ?></span><br>
                    <!--订单名称:
                    <span class="ml10 orderNumber"><?php echo $order["trade_no"] ?></span>-->
                </p>
            </div>
            <div class="tac h60px lh60 pr prcen-xy-parent-dja">
                <i class="blue fwb fs42"></i>
                <span class="fs20 ml10 dib">打开微信【扫一扫】付款</span>
            </div>
        </div>
        <div class="h60px tac lh60 bg2">
            <span style="text-align:center">© 2022 YKFAKA</span>
        </div>
    </div>
</div>
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


