<?php

if(!defined('IN_PLUGIN'))exit();

$rand= $DB->getRow("SELECT * FROM pay_rand WHERE orderno = '".TRADE_NO."' LIMIT 1");
$money=$rand['money'];

$djs=300-time()+0;
$djs=$djs<0?0:$djs;
$gqsj = 0+300;//过期时间
$payurl=$rand['erweima'];
$count= $DB->getRow("SELECT count(*) FROM pay_order WHERE channel = '".$channel["id"]."'");
$count=$count['count(*)'];
$huiyuan = $DB->getRow("select kfqq,logo,kfurl from pre_user where uid=".$order['uid']);
$gqsj = strtotime($order['addtime'])+300;//过期时间
$djs=300-time()+strtotime($order['addtime']);
$money=$order['money'];
$djs=$djs<0?0:$djs;
$rand["orderno"] = TRADE_NO;
$leixing = $channel['apptype'][0];
if($leixing == 'wxwap' || $leixing == '6002')
{
	$l_type= '微信';
}
else
{
	$l_type= '支付宝';
}

$zhifu_leixing = $DB->getRow("SELECT * FROM pay_type WHERE id = ".$order['type']." LIMIT 1");
//var_dump($zhifu_leixing);
$l_type = $zhifu_leixing['showname'];



require 'pay/config.php';

//请求地址
$api = "https://api.yimojo.com/v1/htpay/ht-create-order"; 

$native = array(
    "pay_memberid" => $pay_memberid,
    "pay_orderid" => $pay_orderid,
    "pay_amount" => $pay_amount,
    "pay_applydate" => $pay_applydate,
    "pay_bankcode" => $pay_bankcode,
    "pay_notifyurl" => $pay_notifyurl,
    "pay_callbackurl" => $pay_callbackurl,
);
ksort($native);
$md5str = "";
foreach ($native as $key => $val) {
    $md5str = $md5str . $key . "=" . $val . "&";
}
//echo($md5str . "key=" . $Md5key);
$sign = strtoupper(md5($md5str . "key=" . $Md5key));
$native["pay_md5sign"] = $sign;
$native['pay_attach'] = "1234|456";
$native['pay_productname'] ='团购商品';

$submitData = Http::post($api, $native);

//$resp = build_request($api, $data);
//echo '返回值:'.$resp;


$arr = json_decode($submitData,true);

if($arr['success']){
     echo "<p>正在为您跳转到支付页面，请稍候...</p>";
    echo "<script>";
    echo "window.location.href='" .  $arr['data']['qr_code'] . "'";
    echo ";</script>";
    exit; 
}

// var_dump($arr);
// exit();


$DB->exec("INSERT INTO `pre_orderinfo` (`content`,`order_sn`, `createtime`,`status`) VALUES (:content,:order_sn, :createtime,:status)", [':content'=>json_encode($arr),':order_sn'=>TRADE_NO,':createtime'=>time(),':status'=>'9']);

if($arr['code'] == "1"){
    $agent = strtolower($_SERVER['HTTP_USER_AGENT']);
    if(strpos($agent, 'iphone')){
            $terminals = 'IOS';
               echo "<div style='margin-top:50%;text-align:center'><p style='font-size:45px'>正在为您跳转到支付页面，请稍候...</p></div>";?>
                  <script language="javascript">
                   //var num = 1; //倒计时的秒数
                    var URL = "<?php echo$arr['data']['payurl']?>";
                    window.location.href=URL;
                    //var id = window.setInterval('doUpdate()', 1000);
                    // function doUpdate() {
                       
                    //     if(num == 0) {
                    //         window.clearInterval(id);*/
                    //         window.location.href=URL;
                    //   }
                    //     num --;
                    // }
                    </script> 
               
               <?php
              
                exit; 
    }elseif ( strpos($agent, 'android')){
            $terminals = 'Android';
               echo "<div style='margin-top:50%;text-align:center'><p style='font-size:45px'>正在为您跳转到支付页面，请稍候...</p></div>";?>
                   <script language="javascript">
                   //var num = 1; //倒计时的秒数
                    var URL = "<?php echo$arr['data']['payurl']?>";
                    window.location.href=URL;
                    //var id = window.setInterval('doUpdate()', 1000);
                    // function doUpdate() {
                       
                    //     if(num == 0) {
                    //         window.clearInterval(id);*/
                    //         window.location.href=URL;
                    //   }
                    //     num --;
                    // }
                    </script> 
               
               <?php
              
                exit; 
    }else{
            $terminals = 'PC';
    }


}else{
    
       //跳转回
    //echo "<div style='margin-top:50%;text-align:center'><p style='font-size:45px'>失败！原因: 单号重复，请返回原网页刷新后重新提交</p></div>";
    ?>
    <script language="javascript">
    var num = 4; //倒计时的秒数
    var URL = "http://<?php echo $order['domain']?>";
    var id = window.setInterval('doUpdate()', 1000);
    function doUpdate() {
       
        if(num == 0) {
            window.clearInterval(id);
            window.location.href=URL;
        }
        num --;
    }
    </script>       
    <?php
    
    // echo "<script language='javascript'>";
    // echo "var num = 3;"; //倒计时的秒数
    // echo "var URL = '".$order['domain']."'";
    // echo "var id = window.setInterval('doUpdate()', 1000)";
    // echo "function doUpdate() {";

    // echo  "if(num == 0) {";
    // echo  "window.clearInterval(id)";
    // echo  "window.location = URL";
    // echo  "}"    ;
    // echo  "num --";
    // echo "}";
    // echo "</script>";
    
    // // echo "拉取支付失败";
   exit();
}

if($arr['data']['payurl']!='')
{
	$payurl = $arr['data']['payurl'];
}

//var_dump($payurl);


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

