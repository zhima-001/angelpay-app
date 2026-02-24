<?php
$pay_titlte = "深桑达支付";
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
  $user_ip = getClientIPs(0,true);
   $ip_url = "http://ip-api.com/json/".$user_ip;
   $ip_info =     file_get_contents($ip_url);;
   $ip_info = json_decode($ip_info,true);
   if($ip_info['status']=="success"){
        if($ip_info['countryCode']!="CN"){
               $DB->exec("UPDATE pre_order SET waiwangip='1' WHERE trade_no='".TRADE_NO."'");
        }
   }

//novpn.png
if($channel['waiwangip']=="1"){
   if($ip_info['status']=="success"){
        if($ip_info['countryCode']!="CN"){
          //订单修改信息：
    //       echo '<div style="text-align: center;position: absolute; left: 50%;top: 50%;transform: translate(-50%, -50%);letter-spacing: 5px;" id="allp">
    //     <div >
    //         <img src="/assets/img/novpn.png">
    //     </div>
    //     <div style="margin-top:100px">
    //         <span style="font-size:55px">请<span style="color:red">关闭vpn</span>，使用<span style="color:red">国内IP</span>重新提交支付</span></span>
    //     </div>
    // </div>';
    //       // echo "请关闭vpn，使用国内IP重新提交支付";
    //       exit(); 
        }
    }
}


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

$weixinxianzhi = 99;// $channel['weixinxianzhi'];
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
        "pay_memberid"=>$merId,
        "pay_orderid" => TRADE_NO, 
          "pay_amount" => $order['money'],
         "pay_notifyurl" => $huidiaourl."huidiao.php/".TRADE_NO,
       // "pay_notifyurl" => $huidiaourl."pay/shensangda/notify/".TRADE_NO.'/',
         "pay_callbackurl"=>$huidiaourl."pay/shensangda/return/".TRADE_NO.'/',
         "pay_bankcode"=>$channel['appurl'],
         "pay_applydate"=>date("Y-m-d H:i:s"),
); 


ksort($native);
$md5str = "";
foreach ($native as $key => $val) {
    $md5str = $md5str . $key . "=" . $val . "&";
}

$sign =strtoupper(md5($md5str ."key=". $md5key));
$native["pay_md5sign"] = $sign;

//var_dump($native);

//$submitData = \lib\Zhifu::http_post_data_two($api,json_encode($native));

$submitData = Http::post($api,$native);



//$resp = build_request($api, $data);
//echo '返回值:'.$resp;
$submitData = json_decode($submitData,true);


/*
1，下单后上游给我们的响应和返回
2，支付成功后，上游给我们的回调和响应
*/
 

\lib\Zhifu::csasahangss(1,json_encode($submitData),$pay_titlte,"下单");

//type : 0=使用商户链接  1=PC页面  2=使用PC页面的短码
$now_time2= time();

$end_time = $now_time2-$now_time1;
$userip = getClientIP(0,true);
$DB->exec("UPDATE pre_order SET shijian=shijian+'{$end_time}',ip='{$userip}' WHERE trade_no='".TRADE_NO."'");
if($tg_beizhu == "Tg"){
    
    if($submitData['status'] == "1"){
        
        
        
        $arr = array(
           'status' => "success",
           'paytype' => $leixing,
           'trade_no' => TRADE_NO,
           'out_trade_no' => $out_trade_no,
           'pay_url' => $submitData['h5_url'],
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




if($submitData['status'] == "1"){
    $payurl = $submitData['h5_url'];

    $gaibian = $DB->exec("UPDATE pre_order SET shangyouzhifu='{$payurl}' WHERE trade_no='".TRADE_NO."'");
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
    \lib\Zhifu::csasahangss(2,"单号:".TRADE_NO.":".json_encode($submitData),$pay_titlte,"返回异常");
    var_dump($submitData);
    echo "拉取支付失败";
    exit();
}

//	$payurl = $submitData['url'];
        /*if($shortlink == "1"){
             $payurl="https://".$_SERVER['HTTP_HOST']."/gongxifacai.php?order_sn=".TRADE_NO;
        }else{
              $payurl=$payurl; 
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
    
            <!DOCTYPE html><html>
                 <head>
<meta name="viewport" content="initial-scale=1, maximum-scale=1, user-scalable=no, width=device-width">
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="Content-Language" content="zh-cn">
<meta name="renderer" content="webkit">
<title>支付宝扫码支付</title>
<style data-savepage-href="/assets/css/alipay_pay.css?v=3" media="screen">@charset "UTF-8";html{font-size:62.5%;font-family:'helvetica neue',tahoma,arial,'hiragino sans gb','microsoft yahei','Simsun',sans-serif}body,div,dl,dt,dd,ul,ol,li,h1,h2,h3,h4,h5,h6,pre,code,form,fieldset,legend,input,button,textarea,p,blockquote,th,td,hr{margin:0;padding:0}body{line-height:1.333;font-size:12px}h1,h2,h3,h4,h5,h6{font-size:100%;font-family:arial,'hiragino sans gb','microsoft yahei','Simsun',sans-serif}input,textarea,select,button{font-size:12px;font-weight:normal}input[type="button"],input[type="submit"],select,button{cursor:pointer}table{border-collapse:collapse;border-spacing:0}address,caption,cite,code,dfn,em,th,var{font-style:normal;font-weight:normal}li{list-style:none}caption,th{text-align:left}q:before,q:after{content:''}abbr,acronym{border:0;font-variant:normal}sup{vertical-align:text-top}sub{vertical-align:text-bottom}fieldset,img,a img,iframe{border-width:0;border-style:none}img{-ms-interpolation-mode:bicubic}textarea{overflow-y:auto}legend{color:#000}a:link,a:visited{text-decoration:none}hr{height:0}label{cursor:pointer}.clearfix:after{content:"\200B";display:block;height:0;clear:both}.clearfix{*zoom:1}a{color:#328CE5}a:hover{color:#2b8ae8;text-decoration:none}a.hit{color:#C06C6C}a:focus{outline:none}.hit{color:#8DC27E}.txt_auxiliary{color:#A2A2A2}.clear{*zoom:1}.clear:before,.clear:after{content:"";display:table}.clear:after{clear:both}body,.body{background:#f7f7f7;height:100%}.mod-title{height:60px;line-height:60px;text-align:center;border-bottom:1px solid #ddd;background:#fff}.mod-title .ico-wechat{display:inline-block;width:41px;height:38px;background:/*savepage-url=./alipay-pay.png*/var(--savepage-url-5) 0 -113px no-repeat;vertical-align:middle;margin-right:7px}.mod-title .text{font-size:20px;color:#333;font-weight:normal;vertical-align:middle}.mod-ct{width:610px;padding:0 135px;margin:0 auto;margin-top:15px;background:#fff /*savepage-url=./wave.png*/ var(--savepage-url-6) top center repeat-x;text-align:center;color:#333;border:1px solid #e5e5e5;border-top:none}.mod-ct .order{font-size:20px;padding-top:30px}.mod-ct .amount{font-size:48px;margin-top:20px}.mod-ct .qr-image{margin-top:30px}.mod-ct .qr-image img{width:230px;height:230px}.mod-ct .detail{margin-top:60px;padding-top:25px}.mod-ct .detail .arrow .ico-arrow{display:inline-block;width:20px;height:11px;background:/*savepage-url=./alipay-pay.png*/var(--savepage-url-5) -25px -100px no-repeat}.mod-ct .detail .detail-ct{display:none;font-size:14px;text-align:right;line-height:28px}.mod-ct .detail .detail-ct dt{float:left}.mod-ct .detail-open{border-top:1px solid #e5e5e5}.mod-ct .detail .arrow{padding:6px 34px;border:1px solid #e5e5e5}.mod-ct .detail .arrow .ico-arrow{display:inline-block;width:20px;height:11px;background:/*savepage-url=./alipay-pay.png*/var(--savepage-url-5) -25px -100px no-repeat}.mod-ct .detail-open .arrow .ico-arrow{display:inline-block;width:20px;height:11px;background:/*savepage-url=./alipay-pay.png*/var(--savepage-url-5) 0 -100px no-repeat}.mod-ct .detail-open .detail-ct{display:block}.mod-ct .tip{margin-top:40px;border-top:1px dashed #e5e5e5;padding:30px 0;position:relative}.mod-ct .tip .ico-scan{display:inline-block;width:56px;height:55px;background:/*savepage-url=./alipay-pay.png*/var(--savepage-url-5) 0 0 no-repeat;vertical-align:middle;*display:inline;*zoom:1}.mod-ct .tip .tip-text{display:inline-block;vertical-align:middle;text-align:left;margin-left:23px;font-size:16px;line-height:28px;*display:inline;*zoom:1}.mod-ct .tip .dec{display:inline-block;width:22px;height:45px;background:/*savepage-url=./alipay-pay.png*/var(--savepage-url-5) 0 -55px no-repeat;position:absolute;top:-23px}.mod-ct .tip .dec-left{background-position:0 -55px;left:-136px}.mod-ct .tip .dec-right{background-position:-25px -55px;right:-136px}.foot{text-align:center;margin:30px auto;color:#888888;font-size:12px;line-height:20px;font-family:"simsun"}.foot .link{color:#0071ce}
.open_app{margin-top:30px}
a.btn-open-app{border:1px solid #328ce5;padding:10px 20px;color:#fff;background:#328ce5;font-size:14px;cursor:pointer;}
a.btn-check{padding:10px 20px;color:#328ce5;font-size:14px;cursor:pointer;}
@media (max-width:768px){.mod-ct{width:100%;padding:0;border:0}
.mod-ct .tip .dec-right{display:none}
.mod-ct .detail{margin-top:30px}
.mod-ct .detail .detail-ct{padding:0 19px;margin-bottom:19px}
}
.top-guide[data-v-cd35abb6]{padding-top:.16rem;animation:myfirst 1.6s infinite}
@keyframes myfirst{0%{transform:translateY(0)}
50%{transform:translateY(.21333rem)}
100%{transform:translateY(0)}
}
.guide{text-align:center;position:fixed;top:0;left:0;z-index:10;width:100vw;height:100vh;background:rgba(51,51,51,.92)}
</style>
<style data-savepage-href="/assets/js/new/layer.css?v=3.1.1">.layui-layer-imgbar,.layui-layer-imgtit a,.layui-layer-tab .layui-layer-title span,.layui-layer-title{text-overflow:ellipsis;white-space:nowrap}html #layuicss-layer{display:none;position:absolute;width:1989px}.layui-layer,.layui-layer-shade{position:fixed;_position:absolute;pointer-events:auto}.layui-layer-shade{top:0;left:0;width:100%;height:100%;_height:expression(document.body.offsetHeight+"px")}.layui-layer{-webkit-overflow-scrolling:touch;top:150px;left:0;margin:0;padding:0;background-color:#fff;-webkit-background-clip:content;border-radius:2px;box-shadow:1px 1px 50px rgba(0,0,0,.3)}.layui-layer-close{position:absolute}.layui-layer-content{position:relative}.layui-layer-border{border:1px solid #B2B2B2;border:1px solid rgba(0,0,0,.1);box-shadow:1px 1px 5px rgba(0,0,0,.2)}.layui-layer-load{background:/*savepage-url=loading-1.gif*/url() center center no-repeat #eee}.layui-layer-ico{background:/*savepage-url=icon.png*/url() no-repeat}.layui-layer-btn a,.layui-layer-dialog .layui-layer-ico,.layui-layer-setwin a{display:inline-block;*display:inline;*zoom:1;vertical-align:top}.layui-layer-move{display:none;position:fixed;*position:absolute;left:0;top:0;width:100%;height:100%;cursor:move;opacity:0;filter:alpha(opacity=0);background-color:#fff;z-index:2147483647}.layui-layer-resize{position:absolute;width:15px;height:15px;right:0;bottom:0;cursor:se-resize}.layer-anim{-webkit-animation-fill-mode:both;animation-fill-mode:both;-webkit-animation-duration:.3s;animation-duration:.3s}@-webkit-keyframes layer-bounceIn{0%{opacity:0;-webkit-transform:scale(.5);transform:scale(.5)}100%{opacity:1;-webkit-transform:scale(1);transform:scale(1)}}@keyframes layer-bounceIn{0%{opacity:0;-webkit-transform:scale(.5);-ms-transform:scale(.5);transform:scale(.5)}100%{opacity:1;-webkit-transform:scale(1);-ms-transform:scale(1);transform:scale(1)}}.layer-anim-00{-webkit-animation-name:layer-bounceIn;animation-name:layer-bounceIn}@-webkit-keyframes layer-zoomInDown{0%{opacity:0;-webkit-transform:scale(.1) translateY(-2000px);transform:scale(.1) translateY(-2000px);-webkit-animation-timing-function:ease-in-out;animation-timing-function:ease-in-out}60%{opacity:1;-webkit-transform:scale(.475) translateY(60px);transform:scale(.475) translateY(60px);-webkit-animation-timing-function:ease-out;animation-timing-function:ease-out}}@keyframes layer-zoomInDown{0%{opacity:0;-webkit-transform:scale(.1) translateY(-2000px);-ms-transform:scale(.1) translateY(-2000px);transform:scale(.1) translateY(-2000px);-webkit-animation-timing-function:ease-in-out;animation-timing-function:ease-in-out}60%{opacity:1;-webkit-transform:scale(.475) translateY(60px);-ms-transform:scale(.475) translateY(60px);transform:scale(.475) translateY(60px);-webkit-animation-timing-function:ease-out;animation-timing-function:ease-out}}.layer-anim-01{-webkit-animation-name:layer-zoomInDown;animation-name:layer-zoomInDown}@-webkit-keyframes layer-fadeInUpBig{0%{opacity:0;-webkit-transform:translateY(2000px);transform:translateY(2000px)}100%{opacity:1;-webkit-transform:translateY(0);transform:translateY(0)}}@keyframes layer-fadeInUpBig{0%{opacity:0;-webkit-transform:translateY(2000px);-ms-transform:translateY(2000px);transform:translateY(2000px)}100%{opacity:1;-webkit-transform:translateY(0);-ms-transform:translateY(0);transform:translateY(0)}}.layer-anim-02{-webkit-animation-name:layer-fadeInUpBig;animation-name:layer-fadeInUpBig}@-webkit-keyframes layer-zoomInLeft{0%{opacity:0;-webkit-transform:scale(.1) translateX(-2000px);transform:scale(.1) translateX(-2000px);-webkit-animation-timing-function:ease-in-out;animation-timing-function:ease-in-out}60%{opacity:1;-webkit-transform:scale(.475) translateX(48px);transform:scale(.475) translateX(48px);-webkit-animation-timing-function:ease-out;animation-timing-function:ease-out}}@keyframes layer-zoomInLeft{0%{opacity:0;-webkit-transform:scale(.1) translateX(-2000px);-ms-transform:scale(.1) translateX(-2000px);transform:scale(.1) translateX(-2000px);-webkit-animation-timing-function:ease-in-out;animation-timing-function:ease-in-out}60%{opacity:1;-webkit-transform:scale(.475) translateX(48px);-ms-transform:scale(.475) translateX(48px);transform:scale(.475) translateX(48px);-webkit-animation-timing-function:ease-out;animation-timing-function:ease-out}}.layer-anim-03{-webkit-animation-name:layer-zoomInLeft;animation-name:layer-zoomInLeft}@-webkit-keyframes layer-rollIn{0%{opacity:0;-webkit-transform:translateX(-100%) rotate(-120deg);transform:translateX(-100%) rotate(-120deg)}100%{opacity:1;-webkit-transform:translateX(0) rotate(0);transform:translateX(0) rotate(0)}}@keyframes layer-rollIn{0%{opacity:0;-webkit-transform:translateX(-100%) rotate(-120deg);-ms-transform:translateX(-100%) rotate(-120deg);transform:translateX(-100%) rotate(-120deg)}100%{opacity:1;-webkit-transform:translateX(0) rotate(0);-ms-transform:translateX(0) rotate(0);transform:translateX(0) rotate(0)}}.layer-anim-04{-webkit-animation-name:layer-rollIn;animation-name:layer-rollIn}@keyframes layer-fadeIn{0%{opacity:0}100%{opacity:1}}.layer-anim-05{-webkit-animation-name:layer-fadeIn;animation-name:layer-fadeIn}@-webkit-keyframes layer-shake{0%,100%{-webkit-transform:translateX(0);transform:translateX(0)}10%,30%,50%,70%,90%{-webkit-transform:translateX(-10px);transform:translateX(-10px)}20%,40%,60%,80%{-webkit-transform:translateX(10px);transform:translateX(10px)}}@keyframes layer-shake{0%,100%{-webkit-transform:translateX(0);-ms-transform:translateX(0);transform:translateX(0)}10%,30%,50%,70%,90%{-webkit-transform:translateX(-10px);-ms-transform:translateX(-10px);transform:translateX(-10px)}20%,40%,60%,80%{-webkit-transform:translateX(10px);-ms-transform:translateX(10px);transform:translateX(10px)}}.layer-anim-06{-webkit-animation-name:layer-shake;animation-name:layer-shake}@-webkit-keyframes fadeIn{0%{opacity:0}100%{opacity:1}}.layui-layer-title{padding:0 80px 0 20px;height:42px;line-height:42px;border-bottom:1px solid #eee;font-size:14px;color:#333;overflow:hidden;background-color:#F8F8F8;border-radius:2px 2px 0 0}.layui-layer-setwin{position:absolute;right:15px;*right:0;top:15px;font-size:0;line-height:initial}.layui-layer-setwin a{position:relative;width:16px;height:16px;margin-left:10px;font-size:12px;_overflow:hidden}.layui-layer-setwin .layui-layer-min cite{position:absolute;width:14px;height:2px;left:0;top:50%;margin-top:-1px;background-color:#2E2D3C;cursor:pointer;_overflow:hidden}.layui-layer-setwin .layui-layer-min:hover cite{background-color:#2D93CA}.layui-layer-setwin .layui-layer-max{background-position:-32px -40px}.layui-layer-setwin .layui-layer-max:hover{background-position:-16px -40px}.layui-layer-setwin .layui-layer-maxmin{background-position:-65px -40px}.layui-layer-setwin .layui-layer-maxmin:hover{background-position:-49px -40px}.layui-layer-setwin .layui-layer-close1{background-position:1px -40px;cursor:pointer}.layui-layer-setwin .layui-layer-close1:hover{opacity:.7}.layui-layer-setwin .layui-layer-close2{position:absolute;right:-28px;top:-28px;width:30px;height:30px;margin-left:0;background-position:-149px -31px;*right:-18px;_display:none}.layui-layer-setwin .layui-layer-close2:hover{background-position:-180px -31px}.layui-layer-btn{text-align:right;padding:0 15px 12px;pointer-events:auto;user-select:none;-webkit-user-select:none}.layui-layer-btn a{height:28px;line-height:28px;margin:5px 5px 0;padding:0 15px;border:1px solid #dedede;background-color:#fff;color:#333;border-radius:2px;font-weight:400;cursor:pointer;text-decoration:none}.layui-layer-btn a:hover{opacity:.9;text-decoration:none}.layui-layer-btn a:active{opacity:.8}.layui-layer-btn .layui-layer-btn0{border-color:#1E9FFF;background-color:#1E9FFF;color:#fff}.layui-layer-btn-l{text-align:left}.layui-layer-btn-c{text-align:center}.layui-layer-dialog{min-width:260px}.layui-layer-dialog .layui-layer-content{position:relative;padding:20px;line-height:24px;word-break:break-all;overflow:hidden;font-size:14px;overflow-x:hidden;overflow-y:auto}.layui-layer-dialog .layui-layer-content .layui-layer-ico{position:absolute;top:16px;left:15px;_left:-40px;width:30px;height:30px}.layui-layer-ico1{background-position:-30px 0}.layui-layer-ico2{background-position:-60px 0}.layui-layer-ico3{background-position:-90px 0}.layui-layer-ico4{background-position:-120px 0}.layui-layer-ico5{background-position:-150px 0}.layui-layer-ico6{background-position:-180px 0}.layui-layer-rim{border:6px solid #8D8D8D;border:6px solid rgba(0,0,0,.3);border-radius:5px;box-shadow:none}.layui-layer-msg{min-width:180px;border:1px solid #D3D4D3;box-shadow:none}.layui-layer-hui{min-width:100px;background-color:#000;filter:alpha(opacity=60);background-color:rgba(0,0,0,.6);color:#fff;border:none}.layui-layer-hui .layui-layer-content{padding:12px 25px;text-align:center}.layui-layer-dialog .layui-layer-padding{padding:20px 20px 20px 55px;text-align:left}.layui-layer-page .layui-layer-content{position:relative;overflow:auto}.layui-layer-iframe .layui-layer-btn,.layui-layer-page .layui-layer-btn{padding-top:10px}.layui-layer-nobg{background:0 0}.layui-layer-iframe iframe{display:block;width:100%}.layui-layer-loading{border-radius:100%;background:0 0;box-shadow:none;border:none}.layui-layer-loading .layui-layer-content{width:60px;height:24px;background:/*savepage-url=loading-0.gif*/url() no-repeat}.layui-layer-loading .layui-layer-loading1{width:37px;height:37px;background:/*savepage-url=loading-1.gif*/url() no-repeat}.layui-layer-ico16,.layui-layer-loading .layui-layer-loading2{width:32px;height:32px;background:/*savepage-url=loading-2.gif*/url() no-repeat}.layui-layer-tips{background:0 0;box-shadow:none;border:none}.layui-layer-tips .layui-layer-content{position:relative;line-height:22px;min-width:12px;padding:8px 15px;font-size:12px;_float:left;border-radius:2px;box-shadow:1px 1px 3px rgba(0,0,0,.2);background-color:#000;color:#fff}.layui-layer-tips .layui-layer-close{right:-2px;top:-1px}.layui-layer-tips i.layui-layer-TipsG{position:absolute;width:0;height:0;border-width:8px;border-color:transparent;border-style:dashed;*overflow:hidden}.layui-layer-tips i.layui-layer-TipsB,.layui-layer-tips i.layui-layer-TipsT{left:5px;border-right-style:solid;border-right-color:#000}.layui-layer-tips i.layui-layer-TipsT{bottom:-8px}.layui-layer-tips i.layui-layer-TipsB{top:-8px}.layui-layer-tips i.layui-layer-TipsL,.layui-layer-tips i.layui-layer-TipsR{top:5px;border-bottom-style:solid;border-bottom-color:#000}.layui-layer-tips i.layui-layer-TipsR{left:-8px}.layui-layer-tips i.layui-layer-TipsL{right:-8px}.layui-layer-lan[type=dialog]{min-width:280px}.layui-layer-lan .layui-layer-title{background:#4476A7;color:#fff;border:none}.layui-layer-lan .layui-layer-btn{padding:5px 10px 10px;text-align:right;border-top:1px solid #E9E7E7}.layui-layer-lan .layui-layer-btn a{background:#fff;border-color:#E9E7E7;color:#333}.layui-layer-lan .layui-layer-btn .layui-layer-btn1{background:#C9C5C5}.layui-layer-molv .layui-layer-title{background:#009f95;color:#fff;border:none}.layui-layer-molv .layui-layer-btn a{background:#009f95;border-color:#009f95}.layui-layer-molv .layui-layer-btn .layui-layer-btn1{background:#92B8B1}.layui-layer-iconext{background:/*savepage-url=icon-ext.png*/url() no-repeat}.layui-layer-prompt .layui-layer-input{display:block;width:230px;height:36px;margin:0 auto;line-height:30px;padding-left:10px;border:1px solid #e6e6e6;color:#333}.layui-layer-prompt textarea.layui-layer-input{width:300px;height:100px;line-height:20px;padding:6px 10px}.layui-layer-prompt .layui-layer-content{padding:20px}.layui-layer-prompt .layui-layer-btn{padding-top:0}.layui-layer-tab{box-shadow:1px 1px 50px rgba(0,0,0,.4)}.layui-layer-tab .layui-layer-title{padding-left:0;overflow:visible}.layui-layer-tab .layui-layer-title span{position:relative;float:left;min-width:80px;max-width:260px;padding:0 20px;text-align:center;overflow:hidden;cursor:pointer}.layui-layer-tab .layui-layer-title span.layui-this{height:43px;border-left:1px solid #eee;border-right:1px solid #eee;background-color:#fff;z-index:10}.layui-layer-tab .layui-layer-title span:first-child{border-left:none}.layui-layer-tabmain{line-height:24px;clear:both}.layui-layer-tabmain .layui-layer-tabli{display:none}.layui-layer-tabmain .layui-layer-tabli.layui-this{display:block}.layui-layer-photos{-webkit-animation-duration:.8s;animation-duration:.8s}.layui-layer-photos .layui-layer-content{overflow:hidden;text-align:center}.layui-layer-photos .layui-layer-phimg img{position:relative;width:100%;display:inline-block;*display:inline;*zoom:1;vertical-align:top}.layui-layer-imgbar,.layui-layer-imguide{display:none}.layui-layer-imgnext,.layui-layer-imgprev{position:absolute;top:50%;width:27px;_width:44px;height:44px;margin-top:-22px;outline:0;blr:expression(this.onFocus=this.blur())}.layui-layer-imgprev{left:10px;background-position:-5px -5px;_background-position:-70px -5px}.layui-layer-imgprev:hover{background-position:-33px -5px;_background-position:-120px -5px}.layui-layer-imgnext{right:10px;_right:8px;background-position:-5px -50px;_background-position:-70px -50px}.layui-layer-imgnext:hover{background-position:-33px -50px;_background-position:-120px -50px}.layui-layer-imgbar{position:absolute;left:0;bottom:0;width:100%;height:32px;line-height:32px;background-color:rgba(0,0,0,.8);background-color:#000\9;filter:Alpha(opacity=80);color:#fff;overflow:hidden;font-size:0}.layui-layer-imgtit *{display:inline-block;*display:inline;*zoom:1;vertical-align:top;font-size:12px}.layui-layer-imgtit a{max-width:65%;overflow:hidden;color:#fff}.layui-layer-imgtit a:hover{color:#fff;text-decoration:underline}.layui-layer-imgtit em{padding-left:10px;font-style:normal}@-webkit-keyframes layer-bounceOut{100%{opacity:0;-webkit-transform:scale(.7);transform:scale(.7)}30%{-webkit-transform:scale(1.05);transform:scale(1.05)}0%{-webkit-transform:scale(1);transform:scale(1)}}@keyframes layer-bounceOut{100%{opacity:0;-webkit-transform:scale(.7);-ms-transform:scale(.7);transform:scale(.7)}30%{-webkit-transform:scale(1.05);-ms-transform:scale(1.05);transform:scale(1.05)}0%{-webkit-transform:scale(1);-ms-transform:scale(1);transform:scale(1)}}.layer-anim-close{-webkit-animation-name:layer-bounceOut;animation-name:layer-bounceOut;-webkit-animation-fill-mode:both;animation-fill-mode:both;-webkit-animation-duration:.2s;animation-duration:.2s}@media screen and (max-width:1100px){.layui-layer-iframe{overflow-y:auto;-webkit-overflow-scrolling:touch}}</style>
<style id="savepage-cssvariables">
  :root {
    --savepage-url-5: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADgAAACXCAYAAABAzJglAAAABGdBTUEAALGPC/xhBQAAAAFzUkdCAK7OHOkAAAAJcEhZcwAAHYYAAB2GAV2iE4EAAANvaVRYdFhNTDpjb20uYWRvYmUueG1wAAAAAAA8P3hwYWNrZXQgYmVnaW49Iu+7vyIgaWQ9Ilc1TTBNcENlaGlIenJlU3pOVGN6a2M5ZCI/PiA8eDp4bXBtZXRhIHhtbG5zOng9ImFkb2JlOm5zOm1ldGEvIiB4OnhtcHRrPSJBZG9iZSBYTVAgQ29yZSA1LjUtYzAxNCA3OS4xNTE0ODEsIDIwMTMvMDMvMTMtMTI6MDk6MTUgICAgICAgICI+IDxyZGY6UkRGIHhtbG5zOnJkZj0iaHR0cDovL3d3dy53My5vcmcvMTk5OS8wMi8yMi1yZGYtc3ludGF4LW5zIyI+IDxyZGY6RGVzY3JpcHRpb24gcmRmOmFib3V0PSIiIHhtbG5zOnhtcE1NPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvbW0vIiB4bWxuczpzdFJlZj0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL3NUeXBlL1Jlc291cmNlUmVmIyIgeG1sbnM6eG1wPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvIiB4bXBNTTpPcmlnaW5hbERvY3VtZW50SUQ9InhtcC5kaWQ6ZWYyODkyMTMtZWMxOS00YjhlLTk1YTAtZDg4MjI4MmIyNmVkIiB4bXBNTTpEb2N1bWVudElEPSJ4bXAuZGlkOkJDNDBGQTRBRTM1RjExRTQ5RTgwRTdCMjNEOThDMjA2IiB4bXBNTTpJbnN0YW5jZUlEPSJ4bXAuaWlkOkJDNDBGQTQ5RTM1RjExRTQ5RTgwRTdCMjNEOThDMjA2IiB4bXA6Q3JlYXRvclRvb2w9IkFkb2JlIFBob3Rvc2hvcCBDQyAoTWFjaW50b3NoKSI+IDx4bXBNTTpEZXJpdmVkRnJvbSBzdFJlZjppbnN0YW5jZUlEPSJ4bXAuaWlkOjM2ZTUwMDMzLTI2ZDctYTc0Ny1iOGM3LWE5ZDljZDk2OGNmMyIgc3RSZWY6ZG9jdW1lbnRJRD0ieG1wLmRpZDpCNzNEN0M4Q0UzNUMxMUU0QjY3MEQwQjU2NkE4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);
    --savepage-url-6: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAB4AAAAHCAMAAAAoNw3DAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAA3NpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuNS1jMDE0IDc5LjE1MTQ4MSwgMjAxMy8wMy8xMy0xMjowOToxNSAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wTU09Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9tbS8iIHhtbG5zOnN0UmVmPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvc1R5cGUvUmVzb3VyY2VSZWYjIiB4bWxuczp4bXA9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC8iIHhtcE1NOk9yaWdpbmFsRG9jdW1lbnRJRD0ieG1wLmRpZDplZjI4OTIxMy1lYzE5LTRiOGUtOTVhMC1kODgyMjgyYjI2ZWQiIHhtcE1NOkRvY3VtZW50SUQ9InhtcC5kaWQ6QjY1RUZEQjFFMzVDMTFFNEI2NzBEMEI1NjZBOENFMTMiIHhtcE1NOkluc3RhbmNlSUQ9InhtcC5paWQ6QjY1RUZEQjBFMzVDMTFFNEI2NzBEMEI1NjZBOENFMTMiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENDIChNYWNpbnRvc2gpIj4gPHhtcE1NOkRlcml2ZWRGcm9tIHN0UmVmOmluc3RhbmNlSUQ9InhtcC5paWQ6ZjM0NDNlZmQtMDkwNy00NDc1LWJlOTYtNzRmOWRhZTg5MWVlIiBzdFJlZjpkb2N1bWVudElEPSJ4bXAuZGlkOmVmMjg5MjEzLWVjMTktNGI4ZS05NWEwLWQ4ODIyODJiMjZlZCIvPiA8L3JkZjpEZXNjcmlwdGlvbj4gPC9yZGY6UkRGPiA8L3g6eG1wbWV0YT4gPD94cGFja2V0IGVuZD0iciI/PqOuhZMAAAAtUExURff39////+7u7vX19fb29vr6+vHx8fT09PPz8+/v7+3t7erq6vDw8Pj4+Ozs7EoyMs4AAABBSURBVHjadMdLDsAgCAVAHqD4v/9xTWtMG6KzG6KXlEQ/rswU67VNAYR6qRkegY+VhkXjoTljS0N9i+DT2XUKMAClGgHHUVvOJwAAAABJRU5ErkJggg==);
  }
</style>
<script src="/assets/weixinpay/jquery.js"></script>
<script id="savepage-shadowloader" type="text/javascript">
  "use strict";
  window.addEventListener("DOMContentLoaded",
  function(event) {
    savepage_ShadowLoader(5);
  },false);
  function savepage_ShadowLoader(c){createShadowDOMs(0,document.documentElement);function createShadowDOMs(a,b){var i;if(b.localName=="iframe"||b.localName=="frame"){if(a<c){try{if(b.contentDocument.documentElement!=null){createShadowDOMs(a+1,b.contentDocument.documentElement)}}catch(e){}}}else{if(b.children.length>=1&&b.children[0].localName=="template"&&b.children[0].hasAttribute("data-savepage-shadowroot")){b.attachShadow({mode:"open"}).appendChild(b.children[0].content);b.removeChild(b.children[0]);for(i=0;i<b.shadowRoot.children.length;i++)if(b.shadowRoot.children[i]!=null)createShadowDOMs(a,b.shadowRoot.children[i])}for(i=0;i<b.children.length;i++)if(b.children[i]!=null)createShadowDOMs(a,b.children[i])}}}
</script>
<meta name="savepage-url" content="">
<meta name="savepage-title" content="支付宝扫码支付">
<meta name="savepage-pubdate" content="Unknown">
<meta name="savepage-from" content="">
<meta name="savepage-state" content="Standard Items; Retain cross-origin frames; Merge CSS images; Remove unsaved URLs; Load lazy images in existing content; Max frame depth = 5; Max resource size = 50MB; Max resource time = 10s;">
<meta name="savepage-version" content="33.9">
<meta name="savepage-comments" content="">
  </head>
<body>

<div class="body">
<h1 class="mod-title">
<span class="ico-wechat"></span><span class="text">支付宝扫码支付</span>
</h1>
<div class="mod-ct">
<div class="order">
</div>
<div class="amount">￥<?=$order["money"]?></div>
<div align="center">
                    <span id="qrcode">
                    </span>
                </div>
<div class="open_app" style="display: none;">
    <a class="btn-open-app">打开支付宝APP继续付款</a><br><br><br>
	<a onclick="checkresult()" class="btn-check">我已付款，返回查看订单</a>
</div>
<div class="detail detail-open" id="orderDetail">
<dl class="detail-ct" style="display: block;">
<dt>购买物品</dt>
<dd id="productName">积分充值 <?php echo $order['trade_no'] ; ?>，</dd>
<dt>商户订单号</dt>
<dd id="billId"><?php echo $order['trade_no'] ; ?></dd>
<dt>创建时间</dt>
<dd id="createTime"><?php echo$now_time2?></dd>
</dl>
<a href="javascript:void(0)" class="arrow"><i class="ico-arrow"></i></a>
</div>
<div class="tip">
<span class="dec dec-left"></span>
<span class="dec dec-right"></span>
<div class="ico-scan"></div>
<div class="tip-text">
<p>请使用支付宝扫一扫</p>
<p>扫描二维码完成支付</p>
</div>
</div>
<div class="tip-text">
</div>
</div>
<script data-savepage-type="" type="text/plain" data-savepage-src="/assets/js/new/jquery/1.12.4/jquery.min.js"></script>
<script data-savepage-type="" type="text/plain" data-savepage-src="/assets/js/new/layer.min.js"></script>
<script data-savepage-type="" type="text/plain" data-savepage-src="/assets/js/new/jquery.qrcode.min.js"></script>
<script data-savepage-type="" type="text/plain"></script>

</div>
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

</body></html>
       
    
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


