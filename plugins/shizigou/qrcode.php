<?php

if(!defined('IN_PLUGIN'))exit();

$rand= $DB->getRow("SELECT * FROM pay_rand WHERE orderno = '".TRADE_NO."' LIMIT 1");
$money=$rand['money'];
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



require 'pay/config.php';


//请求地址
$api = "http://206.119.73.78:15789/api/unifiedorder";


$native = array(
 "mch_id" => $merId, //商户号
   "out_trade_no" => TRADE_NO, //商户订单号
       "pass_code" => $channel['appurl'], //支付类型 此处可选项为 微信公众号：wxgzh   微信H5网页：wxwap  微信扫码：wxsm   支付宝H5网页：zfbwap  支付宝扫码：zfbsm 等参考API
"subject"=>"VIP",

   "amount" => $order['money'], //支付金额 单位元
    "notify_url" => "https://huitiao2.tshuitiao123.xyz/pay/shizigou/notify/".TRADE_NO.'/', //异步回调 , 支付结果以异步为准
    "return_url" => "https://huitiao2.tshuitiao123.xyz/pay/shizigou/return/".TRADE_NO.'/', //同步回调 不作为最终支付结果为准，请以异步回调为准

   "client_ip"=>"127.0.0.1",
   "timestamp"=>date("Y-m-d H:i:s")

 
 
);

ksort($native);
foreach ($native as $key => $item) {
    if (!empty($item)) {         //剔除参数值为空的参数
        $newArr[] = $key . '=' . $item;     // 整合新的参数数组
    }
}
$stringA = implode("&", $newArr);         //使用 & 符号连接参数
//echo($md5str . "key=" . $Md5key);
$sign = strtoupper(md5($stringA . $md5key));
$native["sign"] = $sign;




//$submitData = \lib\Zhifu::http_posts_data($native);
$submitData =\lib\Zhifu::http_post_data_two($api,json_encode($native));


//$resp = build_request($api, $data);
//echo '返回值:'.$resp;
$submitData = json_decode($submitData,true);


if($submitData['msg'] == "ok"){
    
     $agent = strtolower($_SERVER['HTTP_USER_AGENT']);
     if(strpos($agent, 'iphone') || strpos($agent, 'android')){
         echo "<p>正在为您跳转到支付页面，请稍候...</p>";
        echo "<script>";
        echo "window.location.href='" .  $submitData['data']['pay_url'] . "'";
        echo ";</script>";
        exit; 
     }

}else{
    echo "拉取支付失败";
    exit();
}

	$payurl = $submitData;

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
<!DocType html>
<html>
<head>
    <meta charset="utf-8">
   <title><?php echo $l_type ; ?>付款</title>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <script type="text/javascript">(window.NREUM||(NREUM={})).loader_config={licenseKey:"16917b5244",applicationID:"431645161"};window.NREUM||(NREUM={}),__nr_require=function(e,t,n){function r(n){if(!t[n]){var i=t[n]={exports:{}};e[n][0].call(i.exports,function(t){var i=e[n][1][t];return r(i||t)},i,i.exports)}return t[n].exports}if("function"==typeof __nr_require)return __nr_require;for(var i=0;i<n.length;i++)r(n[i]);return r}({1:[function(e,t,n){function r(){}function i(e,t,n){return function(){return o(e,[u.now()].concat(c(arguments)),t?null:this,n),t?void 0:this}}var o=e("handle"),a=e(6),c=e(7),f=e("ee").get("tracer"),u=e("loader"),s=NREUM;"undefined"==typeof window.newrelic&&(newrelic=s);var d=["setPageViewName","setCustomAttribute","setErrorHandler","finished","addToTrace","inlineHit","addRelease"],p="api-",l=p+"ixn-";a(d,function(e,t){s[t]=i(p+t,!0,"api")}),s.addPageAction=i(p+"addPageAction",!0),s.setCurrentRouteName=i(p+"routeName",!0),t.exports=newrelic,s.interaction=function(){return(new r).get()};var m=r.prototype={createTracer:function(e,t){var n={},r=this,i="function"==typeof t;return o(l+"tracer",[u.now(),e,n],r),function(){if(f.emit((i?"":"no-")+"fn-start",[u.now(),r,i],n),i)try{return t.apply(this,arguments)}catch(e){throw f.emit("fn-err",[arguments,this,e],n),e}finally{f.emit("fn-end",[u.now()],n)}}}};a("actionText,setName,setAttribute,save,ignore,onEnd,getContext,end,get".split(","),function(e,t){m[t]=i(l+t)}),newrelic.noticeError=function(e,t){"string"==typeof e&&(e=new Error(e)),o("err",[e,u.now(),!1,t])}},{}],2:[function(e,t,n){function r(){return c.exists&&performance.now?Math.round(performance.now()):(o=Math.max((new Date).getTime(),o))-a}function i(){return o}var o=(new Date).getTime(),a=o,c=e(8);t.exports=r,t.exports.offset=a,t.exports.getLastTimestamp=i},{}],3:[function(e,t,n){function r(e,t){var n=e.getEntries();n.forEach(function(e){"first-paint"===e.name?d("timing",["fp",Math.floor(e.startTime)]):"first-contentful-paint"===e.name&&d("timing",["fcp",Math.floor(e.startTime)])})}function i(e,t){var n=e.getEntries();n.length>0&&d("lcp",[n[n.length-1]])}function o(e){e.getEntries().forEach(function(e){e.hadRecentInput||d("cls",[e])})}function a(e){if(e instanceof m&&!g){var t=Math.round(e.timeStamp),n={type:e.type};t<=p.now()?n.fid=p.now()-t:t>p.offset&&t<=Date.now()?(t-=p.offset,n.fid=p.now()-t):t=p.now(),g=!0,d("timing",["fi",t,n])}}function c(e){d("pageHide",[p.now(),e])}if(!("init"in NREUM&&"page_view_timing"in NREUM.init&&"enabled"in NREUM.init.page_view_timing&&NREUM.init.page_view_timing.enabled===!1)){var f,u,s,d=e("handle"),p=e("loader"),l=e(5),m=NREUM.o.EV;if("PerformanceObserver"in window&&"function"==typeof window.PerformanceObserver){f=new PerformanceObserver(r);try{f.observe({entryTypes:["paint"]})}catch(v){}u=new PerformanceObserver(i);try{u.observe({entryTypes:["largest-contentful-paint"]})}catch(v){}s=new PerformanceObserver(o);try{s.observe({type:"layout-shift",buffered:!0})}catch(v){}}if("addEventListener"in document){var g=!1,y=["click","keydown","mousedown","pointerdown","touchstart"];y.forEach(function(e){document.addEventListener(e,a,!1)})}l(c)}},{}],4:[function(e,t,n){function r(e,t){if(!i)return!1;if(e!==i)return!1;if(!t)return!0;if(!o)return!1;for(var n=o.split("."),r=t.split("."),a=0;a<r.length;a++)if(r[a]!==n[a])return!1;return!0}var i=null,o=null,a=/Version\/(\S+)\s+Safari/;if(navigator.userAgent){var c=navigator.userAgent,f=c.match(a);f&&c.indexOf("Chrome")===-1&&c.indexOf("Chromium")===-1&&(i="Safari",o=f[1])}t.exports={agent:i,version:o,match:r}},{}],5:[function(e,t,n){function r(e){function t(){e(a&&document[a]?document[a]:document[i]?"hidden":"visible")}"addEventListener"in document&&o&&document.addEventListener(o,t,!1)}t.exports=r;var i,o,a;"undefined"!=typeof document.hidden?(i="hidden",o="visibilitychange",a="visibilityState"):"undefined"!=typeof document.msHidden?(i="msHidden",o="msvisibilitychange"):"undefined"!=typeof document.webkitHidden&&(i="webkitHidden",o="webkitvisibilitychange",a="webkitVisibilityState")},{}],6:[function(e,t,n){function r(e,t){var n=[],r="",o=0;for(r in e)i.call(e,r)&&(n[o]=t(r,e[r]),o+=1);return n}var i=Object.prototype.hasOwnProperty;t.exports=r},{}],7:[function(e,t,n){function r(e,t,n){t||(t=0),"undefined"==typeof n&&(n=e?e.length:0);for(var r=-1,i=n-t||0,o=Array(i<0?0:i);++r<i;)o[r]=e[t+r];return o}t.exports=r},{}],8:[function(e,t,n){t.exports={exists:"undefined"!=typeof window.performance&&window.performance.timing&&"undefined"!=typeof window.performance.timing.navigationStart}},{}],ee:[function(e,t,n){function r(){}function i(e){function t(e){return e&&e instanceof r?e:e?f(e,c,o):o()}function n(n,r,i,o){if(!p.aborted||o){e&&e(n,r,i);for(var a=t(i),c=v(n),f=c.length,u=0;u<f;u++)c[u].apply(a,r);var d=s[w[n]];return d&&d.push([b,n,r,a]),a}}function l(e,t){h[e]=v(e).concat(t)}function m(e,t){var n=h[e];if(n)for(var r=0;r<n.length;r++)n[r]===t&&n.splice(r,1)}function v(e){return h[e]||[]}function g(e){return d[e]=d[e]||i(n)}function y(e,t){u(e,function(e,n){t=t||"feature",w[n]=t,t in s||(s[t]=[])})}var h={},w={},b={on:l,addEventListener:l,removeEventListener:m,emit:n,get:g,listeners:v,context:t,buffer:y,abort:a,aborted:!1};return b}function o(){return new r}function a(){(s.api||s.feature)&&(p.aborted=!0,s=p.backlog={})}var c="nr@context",f=e("gos"),u=e(6),s={},d={},p=t.exports=i();p.backlog=s},{}],gos:[function(e,t,n){function r(e,t,n){if(i.call(e,t))return e[t];var r=n();if(Object.defineProperty&&Object.keys)try{return Object.defineProperty(e,t,{value:r,writable:!0,enumerable:!1}),r}catch(o){}return e[t]=r,r}var i=Object.prototype.hasOwnProperty;t.exports=r},{}],handle:[function(e,t,n){function r(e,t,n,r){i.buffer([e],r),i.emit(e,t,n)}var i=e("ee").get("handle");t.exports=r,r.ee=i},{}],id:[function(e,t,n){function r(e){var t=typeof e;return!e||"object"!==t&&"function"!==t?-1:e===window?0:a(e,o,function(){return i++})}var i=1,o="nr@id",a=e("gos");t.exports=r},{}],loader:[function(e,t,n){function r(){if(!E++){var e=b.info=NREUM.info,t=p.getElementsByTagName("script")[0];if(setTimeout(u.abort,3e4),!(e&&e.licenseKey&&e.applicationID&&t))return u.abort();f(h,function(t,n){e[t]||(e[t]=n)});var n=a();c("mark",["onload",n+b.offset],null,"api"),c("timing",["load",n]);var r=p.createElement("script");r.src="https://"+e.agent,t.parentNode.insertBefore(r,t)}}function i(){"complete"===p.readyState&&o()}function o(){c("mark",["domContent",a()+b.offset],null,"api")}var a=e(2),c=e("handle"),f=e(6),u=e("ee"),s=e(4),d=window,p=d.document,l="addEventListener",m="attachEvent",v=d.XMLHttpRequest,g=v&&v.prototype;NREUM.o={ST:setTimeout,SI:d.setImmediate,CT:clearTimeout,XHR:v,REQ:d.Request,EV:d.Event,PR:d.Promise,MO:d.MutationObserver};var y=""+location,h={beacon:"bam.nr-data.net",errorBeacon:"bam.nr-data.net",agent:"js-agent.newrelic.com/nr-1184.min.js"},w=v&&g&&g[l]&&!/CriOS/.test(navigator.userAgent),b=t.exports={offset:a.getLastTimestamp(),now:a,origin:y,features:{},xhrWrappable:w,userAgent:s};e(1),e(3),p[l]?(p[l]("DOMContentLoaded",o,!1),d[l]("load",r,!1)):(p[m]("onreadystatechange",i),d[m]("onload",r)),c("mark",["firstbyte",a.getLastTimestamp()],null,"api");var E=0},{}],"wrap-function":[function(e,t,n){function r(e){return!(e&&e instanceof Function&&e.apply&&!e[a])}var i=e("ee"),o=e(7),a="nr@original",c=Object.prototype.hasOwnProperty,f=!1;t.exports=function(e,t){function n(e,t,n,i){function nrWrapper(){var r,a,c,f;try{a=this,r=o(arguments),c="function"==typeof n?n(r,a):n||{}}catch(u){p([u,"",[r,a,i],c])}s(t+"start",[r,a,i],c);try{return f=e.apply(a,r)}catch(d){throw s(t+"err",[r,a,d],c),d}finally{s(t+"end",[r,a,f],c)}}return r(e)?e:(t||(t=""),nrWrapper[a]=e,d(e,nrWrapper),nrWrapper)}function u(e,t,i,o){i||(i="");var a,c,f,u="-"===i.charAt(0);for(f=0;f<t.length;f++)c=t[f],a=e[c],r(a)||(e[c]=n(a,u?c+i:i,o,c))}function s(n,r,i){if(!f||t){var o=f;f=!0;try{e.emit(n,r,i,t)}catch(a){p([a,n,r,i])}f=o}}function d(e,t){if(Object.defineProperty&&Object.keys)try{var n=Object.keys(e);return n.forEach(function(n){Object.defineProperty(t,n,{get:function(){return e[n]},set:function(t){return e[n]=t,t}})}),t}catch(r){p([r])}for(var i in e)c.call(e,i)&&(t[i]=e[i]);return t}function p(t){try{e.emit("internal-error",t)}catch(n){}}return e||(e=i),n.inPlace=u,n.flag=a,n}},{}]},{},["loader"]);</script>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1.0, user-scalable=no">
    <meta name="description" content="">
    <meta name="keywords" content="">
    <link rel="stylesheet" href="/assets/pay/css/pay_1.86-min.css?5=61">
    <style>
        #qrcode img{
            display: inline !important;
        }
        .alipng{
            position: relative !important;
            left: 30% !important;;
        }
        #qrcode{
            margin-top: 10px;
            margin-bottom: 10px;
        }
        .label-info{background:#f00;}
    </style>
</head>

<body class="scanpay" style="background: #FFF;padding:0px;">

<script>
    var openAliApp = 1;
</script>
<h1 class="scanheader text-center" style="display: none">
    
    <span class="alilogo" title="<?php echo $l_type ; ?>付款"></span>
   
   
</h1>

<div class="scanheader text-center" style="padding: 10px 0 0 0;height: 38px; line-height: 38px;background: #dbe8f9;border-bottom: 0px;margin: 0px;padding: 5px 0 0 0;">


    <button onclick="window.open('{$shop.kflj}')" style="display:none;margin-top:2px;float: right;margin-right: 10px;line-height: 30px;height: 30px; text-align: center;vertical-align: middle;border-radius: 50px;background-color: #4144e7;border: 0px;color:#FFF;padding-left: 15px;padding-right: 15px;">
        <svg style=" top: 4px; position: relative;" t="1617635039376" class="icon" viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" p-id="20" height="20"   ><path d="M830.976 177.152c-84.992-82.432-196.608-126.976-315.392-124.928-227.84 4.608-417.28 185.344-431.104 411.648-5.12 82.432 12.288 163.84 51.2 236.544L92.16 904.704v3.072c0 17.92 14.336 32.256 32.256 32.256h4.096l196.096-55.296c66.56 33.792 141.312 50.176 216.064 47.616 114.688-4.096 221.696-52.736 301.056-136.192 79.872-83.456 122.88-192.512 121.856-307.2 0-118.784-47.616-229.376-132.608-311.808z m-291.84 693.76c-66.048 2.048-132.096-12.8-190.464-43.008-1.536-1.024-2.56-1.536-3.584-2.048l-10.752-7.168-170.496 48.128 35.84-167.424v-10.752l-3.072-6.656c-1.024-2.048-2.048-4.096-3.584-6.144l-1.536-3.0720.5120.512c-33.28-62.464-48.64-132.608-44.032-203.776 11.776-194.56 174.592-350.208 370.688-354.304 101.888-2.048 198.144 36.352 271.36 107.52 73.216 71.168 113.664 166.4 114.688 268.288 1.536 202.24-161.792 373.248-364.544 380.928z" fill="#ffffff" p-id="2493"></path><path d="M320 483.84m-52.224 0a52.224 52.224 0 1 0 104.448 0 52.224 52.224 0 1 0-104.448 0Z" fill="#ffffff" p-id="2494"></path><path d="M512 483.84m-52.224 0a52.224 52.224 0 1 0 104.448 0 52.224 52.224 0 1 0-104.448 0Z" fill="#ffffff" p-id="2495"></path><path d="M701.42588 532.072903a52.224 52.224 0 1 0 39.970519-96.49737 52.224 52.224 0 1 0-39.970519 96.49737Z" fill="#ffffff" p-id="2496"></path></svg>
        天使客服</button>

    <p style="margin-top:2px;float: right;margin-right: 10px;line-height: 28px;height: 30px; text-align: center;vertical-align: middle;border-radius: 50px;  ">
        <a id="kfbtn" href="javascript:$('#payTipModal').modal('show')"  ><img style="height: 25px;margin-right:5px;margin-top: -5px;"  src="/assets/pay/img/kf.jpg?5=1"/> 在线(24h)</a>
    </p>

</div>

<div class="scanheader text-center" style="padding: 10px 0 0 0;height: 34px; line-height: 20px; border-bottom: 0px;margin: 0px;padding: 5px 0 0 0;">
    <div class="help-block text-center cpcon" style=" color:#333;font-size:12px;">
        <span>订单号:<?=$rand["orderno"]?></span>
        <button class="btn btn-danger1 btn-xs cpamountbtn"  data-clipboard-action="copy" data-clipboard-text="<?php echo $order['trade_no'] ; ?>" data-copydone="已复制到剪切板" style=" background-color: #00a0e7;color: #FFF;">
            复制
        </button>
        <span class="cpdone hide" style="color:green"></span>
    </div>

</div>

<div class="scanbody " style="background: #FFF;border-bottom: 0px;margin-top: 0px; padding-top: 1px;margin-bottom: 0px;">

    <p style="display:none;text-align: left; padding-left: 15px;font-weight: 900;color:#333">
        <a href="#" target="_blank" style="color:#333;text-decoration-line: none;"><img style="height: 25px;margin-right:5px;"  src="/assets/pay/img/qq.jpg"/> 在线客服 <?=$conf["kfqq"]?></a>
    </p>



    <div style='font-size:10px;padding-left: 15px;
    padding-right: 15px;display: none'>
        <p class="help-block"><strong style='color:red;font-weight:900;'>我们承诺：只要付过款，必定会开通。</strong></p>
        <p class="help-block"><strong style='color:red;font-weight:900;'>任何时候，支付时请不要在微信或<?php echo $l_type ; ?>里留言、不要投诉，也不要联系收款方，否则永久封号！！！</strong></p>
        <p class="help-block">微信、<?php echo $l_type ; ?>付款后积分自动到账，整个过程无需人工干预，99％的情况下 积分3分钟内到账</p>
        <p class="help-block">付款时请一定填对金额，否则将导致积分不到账,不到帐可联系<strong style='color:red;font-weight:900;'>售后客服qq:<?php echo $huiyuan['kfqq']; ?></strong>，如果她有事不在，给客服妹子一点时间，她一定会在<strong style='color:red;font-weight:900;'> 8 小时</strong>内看到留言并处理回复，给你一个满意处理结果</p>

    </div>
    <div class="price" style='position: relative;margin-top: -10px;'>
        <a style='display:none;position: absolute; left: 20px;  top: 25px;font-size: 14px;' target='_blank' href='{$goods.kf}'>
            <svg style='text-align: center;  vertical-align: middle; top: -2px; position: relative;' t="1616328077793" class="icon" viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" p-id="2397" width="25" height="25"><path d="M870.1 562.4v-25.5c2-116.5-32.2-214.5-99.6-284-32.2-33.2-71-58.2-116-75.6-7.2-62.8-74.6-112.4-156.8-112.4-82.2 0-150.2 49.5-156.8 112.4-45 17.4-83.8 42.4-116 75.6-67.4 69.5-101.7 167-99.6 283.5v31.2c-17.4 17.9-31.2 44.4-20.9 82.2 7.7 28.1 28.1 48.5 53.1 57.7 12.8 8.2 27.6 12.8 43.9 12.8 32.2 141 148.1 237 299.9 237 151.7 0 267.7-96 299.9-237.5 1 0 2 0.5 3.1 0.5 19.9 0 38.3-7.2 53.1-18.9 18.9-10.7 33.7-29.1 39.8-52.1 10.7-41-7.2-69.1-27.1-86.9z m-88.9 77.1c0 18.9-1.5 36.8-4.1 54.1-49.5 79.7-131.3 131.8-232.9 143-7.7-10.2-23-16.9-40.9-16.9-25.5 0-46 13.8-46 31.2 0 16.9 20.4 31.2 46 31.2 23 0 41.4-11.2 45.5-26.1 32.7-3.6 63.9-11.7 93.5-23.5 41.9-16.9 78.7-41.9 109.8-73.6 5.1-5.1 9.7-10.2 14.3-15.8-36.8 113-135.4 185.5-263.6 185.5h-1c-165 0-279.9-119-279.9-289.1l0.5-16.3c0-7.2 1-26.6 2-45 68.5-25 130.3-120.6 155.3-164.5 23 31.2 75.6 91.4 156.3 122.6 30.1 11.7 60.8 17.9 90.9 24.5 59.8 12.8 116.5 25 154.8 75.1l1.5 1.5-1 2.1z m0 0" fill="#1B4BEE" p-id="2398"></path></svg>
            联系客服</a>
        ￥<span style="color:red;font-size:50px;text-align:center"><?=$order["money"]?></span>
        <?php if($rand["url"]==0){ ?>
        <small style="font-size:45%;display:block;color:red;font-weight:900">付错金额不能开通.</small>
        <?php } ?>
        <?php if($rand["url"]>0){ ?>
        <small style="font-size:45%;display:block;color:#000;font-size:12px;font-weight:900">如无法跳转<?php echo $l_type ; ?>  ，请截屏保存二维码，到<?php echo $l_type ; ?>  扫一扫付款</small>
        <?php } ?>

    </div>
    <div class="qrimgcon" >

        <?php if(1){ ?>
        <div id="qrcode" class="qrimg"  width="210" height="210" style="position: relative;text-align: center;">
          
        </div>

        <?php if(checkmobile()!=true || $rand["url"]==0){ ?>
        <!--show in mobile-->
        <div class="mobtipbtn hidden-sm hidden-md hidden-lg">
            <a id="saveQr" href="#" class="btn btn-primary btn-block">1. 截屏或长按二维码保存</a>
        </div>
        <div class="mobtipbtn hidden-sm hidden-md hidden-lg" style="padding-top:10px">
            <!--<a href="alipay://{$order.pay_url}" class="btn btn-primary btn-block">2. 点击<?php echo $l_type ; ?>扫一扫从相册打开付款</a>-->
            <?php if($rand["url"]==0){ ?>
                <a href="javascript:;" onclick="if(openAliApp && confirm('请不要关闭此网页！！\n付款后一定要切换回浏览器此页面，才能完成付款！')){location.href='<?=$payurl?>';}" class="btn btn-primary btn-block">2. 点击
                    <?php echo $l_type ; ?>
                    扫一扫从相册打开付款</a>

            <?php } ?>

         

        </div>
        <?php } ?>
        <?php if(checkmobile()==true & $rand["url"]>0){ ?>
        <div class="mobtipbtn hidden-sm hidden-md hidden-lg" style="padding-top:10px">
            <!-- <a id="topay" style="    background-color: #4295d5; border-color: #4295d5;"  href="javascript:;" onclick="toAliPay()" class="btn btn-primary btn-block"> 打开<?php echo $l_type ; ?>付款</a> -->
            <a id="topay" style="    background-color: #4295d5; border-color: #4295d5;"  href='<?=$payurl?>' class="btn btn-primary btn-block"> 打开<?php echo $l_type ; ?>付款</a>

        </div>
        <?php } ?>
        <?php } ?>

        <a   style="display:none;background-color: #4295d5; border-color: #4295d5;"  href="javascript:;" onclick="toAliPay2()" class="btn btn-primary btn-block"> 测试<?php echo $l_type ; ?>付款</a>


   
        <div class="scantida" addtime="<?=$rand['time']?>" data-timeout="<?=$djs?>" data-orderid="<?=$rand["orderno"]?>" data-return_url="/pay_back?orderid=<?php echo $order['trade_no'] ; ?>">
            <h3 class="text-center">支付后耐心等待跳转，请勿关闭</h3>
            <div class="text-center">
                <span class="label label-info lbminitue">-</span>
                <span class="label label-info lbseconds">-</span>
            </div>
        </div>

        <?php if(checkmobile()!=true){ ?>
        <div class="mobtipbtn uppayimg " style="padding-top:10px">
            <a href="javascript:$('#payTipModal').modal('show')" class="btn btn-warning btn-block">还没到账? 点这里完成付款</a>
        </div>
        <?php } ?>
    </div>


   

    <?php if($order["type"]==1){ ?>
    <div class="scantip" style="display: none">
        <div class="tipico"></div>
        <div class="tiptext">
            打开<?php echo $l_type ; ?> [扫一扫]，输入金额: <strong><?=$rand["money"]?></strong>
            <br>
            <strong>请务必输入正确，否则导致购买失败</strong>
        </div>

    </div>
    <?php } ?>

    <div class="paidtip hide" data-redirecttime="3">
        <span class="glyphicon glyphicon-ok"></span>
        <br>付款成功，自动跳转中...
    </div>
</div>
<?php if($rand["url"]==0){ ?>
<script type="text/template" id="payTipDescTemplate">
    <div class="text-center">
        <h4>您的付款金额是</h4>
        <p>
            <span class="badge" style="font-size:36px;background-color:red;color:white;vertical-align:bottom"><?=$order["money"]?></span>
        </p>
        <p class="help-block">
            <strong>付款时请一定填入上面的金额</strong>！
            <br><strong class="label label-danger">如果输入不一致</strong>，将导致：
            <br>
            <strong class="label label-danger">购买失败！</strong>
            <strong class="label label-danger">购买失败！</strong>
            <strong class="label label-danger">购买失败！</strong>
            <br>
            （重要的事情说三遍！）
        </p>
        <p class="text-center" style="margin-top:30px;font-weight:bold">
            <!--<small>如果识别二维码时提示无法付款，请后退换微信付款；</small>-->
            <!--<br>-->
            支付后耐心等待跳转，请勿关闭
        </p>
    </div>
</script>

<script>
    var PayTip_Title = '本次购买付款金额确认';
    var PayTip_Description = document.getElementById('payTipDescTemplate').innerHTML;
</script>
<?php } ?>


<div class="footer"  style="background-color: #f1f4fb;padding-bottom: 4px;margin-top: 0px;">
    本页由天使支付集团提供，已提供 <span style="color:#8aadbe"><?=$count?> 次</span>成功支付
</div>

<div class="modal fade" tabindex="-1" role="dialog" id="commonTipModal">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="tipModalTitle">{Title}</h4>
            </div>
            <div class="modal-body">
                {Content}
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-dismiss="modal">记住了，马上支付</button>
            </div>
        </div>
    </div>
</div>

<?php if($rand["url"]==0){ ?>
<div class="modal fade" tabindex="-1" role="dialog" id="backTipModal">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">付款提示</h4>
            </div>
            <div class="modal-body">
                <p class="help-block"><strong style='color:red;font-weight:900;'>我们承诺：只要付过款，必定会开通。</strong></p>
                <p class="help-block">99％的情况下，3分钟内到账</p>
                <p class="help-block">付款时请一定填对金额，否则将导致积分不到账,不到帐可联系<strong style='color:red;font-weight:900;'>售后客服qq<?php echo $huiyuan['kfqq']; ?></strong></p>
                <p class="help-block">或者点击下方头像联系在线售后客服</p>
                <p style="text-align: left; padding-left: 19px;font-weight: 900;color:#608da0">
                    <a id="kfbtn" href="<?php echo $huiyuan['kfurl']; ?>" target="_blank"><img style="height: 25px;margin-right:5px;"  src="/assets/pay/img/kf.jpg"/> 在线客服(24h)</a>
                </p>
            </div>
            <div class="modal-footer" style="text-align:left">
                <button type="button" class="btn btn-sm btn-primary alreadypaid" data-dismiss="modal">✔已付款</button>
                &nbsp;&nbsp;
                <button type="button" class="btn btn-sm btn-info" data-dismiss="modal">还没，继续</button>
                &nbsp;&nbsp;
                <button class="btn btn-sm btn-default pull-right" type="button" id="errorQR" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                    ⚠付款被限点我
                    <span class="caret"></span>
                </button>
                <div class="dropdown" id="errorQRDropdown">
                    <ul class="dropdown-menu" aria-labelledby="errorQR">
                        <li class="text-center"><h4 style="margin:5px 0 0 0">请选择</h4></li>
                        <li role="separator"><hr style="margin:5px 0"></li>
                        <li><a href="javascript:;" class="backBtn4Fail">收款二维码被限制</a></li>
                        <li><a href="javascript:;" class="backBtn4Fail">收款账号涉嫌刷单、欺诈</a></li>
                        <li><a href="javascript:;" class="backBtn4Fail">不支持海外账号支付</a></li>
                        <li><a href="javascript:;" class="backBtn4Fail">其它原因</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
<?php } ?>
<div class="modal fade" tabindex="-1" role="dialog" id="payTipModal">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">付款提示</h4>
            </div>
            <div class="modal-body">
                <p class="help-block"><strong style='color:red;font-weight:900;'>我们承诺：只要付过款，必定会开通。</strong></p>
                <p class="help-block">99％的情况下，3分钟内到账</p>
                <p class="help-block">付款时请一定填对金额，否则将导致积分不到账,不到帐可联系<strong style='color:red;font-weight:900;'>售后客服qq<?php echo $huiyuan['kfqq']; ?></strong></p>
                <p class="help-block">或者点击下方头像联系在线售后客服</p>
                <p style="text-align: left; padding-left: 19px;font-weight: 900;color:#608da0">
                    <a id="kfbtn" href="<?php echo $huiyuan['kfurl']; ?>" target="_blank"><img style="height: 25px;margin-right:5px;"  src="/assets/pay/img/kf.jpg"/> 在线客服(24h)</a>
                </p>
            </div>

        </div>
    </div>
</div>


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
<script>
    <?php
        $now_time = time();
        $end_time = strtotime($order['addtime'])+180-$now_time;
        
        
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



</body>
</html>


