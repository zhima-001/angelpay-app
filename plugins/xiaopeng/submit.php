
<?php

if(!defined('IN_PLUGIN'))exit();
include 'function.php';
$now_time = date("Y-m-d H:i:s");
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
$nishiwaiwang = 0;
//$user_ip = getClientIPs(0,true);
//$ip_url = "http://ip-api.com/json/".$user_ip;
//$ip_info =     file_get_contents($ip_url);;
//$ip_info = json_decode($ip_info,true);
//if($ip_info['status']=="success"){
//
//   if($ip_info['countryCode'] != "CN"){
//            $nishiwaiwang = "1";
//        }
//
//}else{
//     $nishiwaiwang = "1";
//}


$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$domain = $_SERVER['HTTP_HOST'];

//$full_domain = $protocol . $domain;
$full_domain =  $conf['localurl'];
if(empty($trade_no)){
    $trade_no = $_GET['trade_no'];
}
//require_once(PAY_ROOT."inc/epay_submit.class.php");
$order= $DB->getRow("SELECT * FROM pre_order WHERE trade_no = '".$trade_no."'");
$zhifu_url = $full_domain."/pay/xiaopeng/qrcode/{$trade_no}/";

// 获取请求的 URI
$request_uri = $_SERVER['REQUEST_URI'];

// 组合完整的URL
$current_url = $full_domain. $request_uri;


$agent = strtolower($_SERVER['HTTP_USER_AGENT']);

    //支付宝：
    if($order['type']=="1"){
        if($channel['tianshiyidong']=="1"){?>
        <!DOCTYPE html><html lang="zh"><head>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta charset="UTF-8">
        <title>在线支付</title>
    <style>
        .payBtn {
            width: 50vw;
            border-radius: 3em;
            background: #09bb07;
            padding: 0.5em;
            color: #ffffff;
            text-decoration: none;
            font-size: 1.25em;
        }

        .payBtnAliPay {
            width: 50vw;
            border-radius: 3em;
            background: #1678ff;
            padding: 0.5em;
            color: #ffffff;
            text-decoration: none;
            font-size: 1.25em;
        }
    </style>
    <style id="savepage-cssvariables">
      :root {
      }
    </style>
    <script id="savepage-shadowloader" type="text/javascript">
      "use strict";
      window.addEventListener("DOMContentLoaded",
      function(event) {
        savepage_ShadowLoader(5);
      },false);
      function savepage_ShadowLoader(c){createShadowDOMs(0,document.documentElement);function createShadowDOMs(a,b){var i;if(b.localName=="iframe"||b.localName=="frame"){if(a<c){try{if(b.contentDocument.documentElement!=null){createShadowDOMs(a+1,b.contentDocument.documentElement)}}catch(e){}}}else{if(b.children.length>=1&&b.children[0].localName=="template"&&b.children[0].hasAttribute("data-savepage-shadowroot")){b.attachShadow({mode:"open"}).appendChild(b.children[0].content);b.removeChild(b.children[0]);for(i=0;i<b.shadowRoot.children.length;i++)if(b.shadowRoot.children[i]!=null)createShadowDOMs(a,b.shadowRoot.children[i])}for(i=0;i<b.children.length;i++)if(b.children[i]!=null)createShadowDOMs(a,b.children[i])}}}
    </script>
     <script type="text/javascript" src="/assets/111/js/jquery.min.js"></script>
    <script src="/assets/pay/js/qrcode.min.js"></script>
    <meta name="savepage-title" content="在线支付">
    <meta name="savepage-pubdate" content="Unknown">
    <meta name="savepage-state" content="Standard Items; Retain cross-origin frames; Merge CSS images; Remove unsaved URLs; Load lazy images in existing content; Max frame depth = 5; Max resource size = 50MB; Max resource time = 10s;">
    <meta name="savepage-version" content="33.9">
    <meta name="savepage-comments" content="">
    <style>
        .announcement {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            display: none;
            justify-content: center;
            align-items: center;
            height: 100vh;
            z-index: 1000;
        }

        .announcement-content {
            width: 310px;
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .announcement-content p {
            margin: 0 0 20px;
        }

        #acknowledgeButton {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }

        #acknowledgeButton:hover {
            background-color: #45a049;
        }
    </style>
      </head>

    <body>
    <div style="margin: 0 auto;padding-top: 2vh;text-align: center;">
        <div>
              <img data-savepage-currentsrc="https://anan.ancaizf.com/ico/alipay.png" data-savepage-src="/ico/alipay.png" src="data:image/png;base64,/9j/4QAYRXhpZgAASUkqAAgAAAAAAAAAAAAAAP/sABFEdWNreQABAAQAAAA2AAD/4QMdaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wLwA8P3hwYWNrZXQgYmVnaW49Iu+7vyIgaWQ9Ilc1TTBNcENlaGlIenJlU3pOVGN6a2M5ZCI/PiA8eDp4bXBtZXRhIHhtbG5zOng9ImFkb2JlOm5zOm1ldGEvIiB4OnhtcHRrPSJBZG9iZSBYTVAgQ29yZSA2LjAtYzAwNiA3OS5kYWJhY2JiLCAyMDIxLzA0LzE0LTAwOjM5OjQ0ICAgICAgICAiPiA8cmRmOlJERiB4bWxuczpyZGY9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkvMDIvMjItcmRmLXN5bnRheC1ucyMiPiA8cmRmOkRlc2NyaXB0aW9uIHJkZjphYm91dD0iIiB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIgeG1sbnM6c3RSZWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZVJlZiMiIHhtbG5zOnhtcD0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wLyIgeG1wTU06RG9jdW1lbnRJRD0ieG1wLmRpZDo5QjU1RDBGMDVCODMxMUVFQkZEQUVDODhDRTY3ODg3OCIgeG1wTU06SW5zdGFuY2VJRD0ieG1wLmlpZDo5QjU1RDBFRjVCODMxMUVFQkZEQUVDODhDRTY3ODg3OCIgeG1wOkNyZWF0b3JUb29sPSJBZG9iZSBQaG90b3Nob3AgMjAyMSBNYWNpbnRvc2giPiA8eG1wTU06RGVyaXZlZEZyb20gc3RSZWY6aW5zdGFuY2VJRD0iRDQ1MTA4MjgxMDE1NDhBOEY5MDBGNUUwODc3NjhCMzciIHN0UmVmOmRvY3VtZW50SUQ9IkQ0NTEwODI4MTAxNTQ4QThGOTAwRjVFMDg3NzY4QjM3Ii8+IDwvcmRmOkRlc2NyaXB0aW9uPiA8L3JkZjpSREY+IDwveDp4bXBtZXRhPiA8P3hwYWNrZXQgZW5kPSJyIj8+/+4ADkFkb2JlAGTAAAAAAf/bAIQABwUFBQYFBwYGBwsHBgcLDAkHBwkMDgwMDAwMDhEMDAwMDAwRDhEREhERDhYWFxcWFiAfHx8gIyMjIyMjIyMjIwEICAgPDQ8cEhIcHhgUGB4jIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMj/8AAEQgAPACvAwERAAIRAQMRAf/EALwAAAICAwEBAAAAAAAAAAAAAAYHAAUDBAgBAgEBAQADAQEBAAAAAAAAAAAAAAECBQYEAwcQAAEDAgQDAwYFDQwLAQAAAAECAwQRBQAhEgYxEwdBIhRRYYEyIxVxQnLSFpFSM0OTsyQ0VGR0pCWxYoKismNzlFU2Fwih0ZJTg6PThLQ1N1cRAAIBAQQFCQcEAwEAAAAAAAABAgMRIQQFMVGBEhNBcZGhscEiMiNh0VJyMxQG4WKiY/CCksL/2gAMAwEAAhEDEQA/ADvqVv8AuMa4OWKzvGNyAPHS0fZCtY1BltXxaJIKlDOpoKUOOpybKYThxaitt8q5Od9xosyx8oy4cLtbFkq4XFaitc2QpSs1KL7pJ/jY6ZUKau3Y9CNI6s3yvpPPGz/yyR92c+di8GHwx6ETiS1s8M+cK/hkjLj7Zz52HBh8MehDiy1s98bO/LJH3Zz52HBh8MehDiS1snjZ/wCWSPuznzsODD4Y9CHElrfSbEK+3yA8H4dxksupNQQ6pQPykLKkqHmIx86uEo1FZKMWub3GcMRUg7YyY9NhbtO5bSp19KW7hEUGpiEeqSRVDiBU0SsdnYa44bNcB9tUsV8JXr3bDqcDi+PC1+ZaQoxrD2kwAs959abdte/ybM7anpTsVDalPIcQhJLiA5QAgnIKGeBbBjRJAkxGJISUB9tDgSeI1pCqH6uBDNgAF371Ws2zJ0aDKjOy5D7SpC0sqSOU2FaUleo/HIVT4MCpBdaZztwtcSc7GXDclNIeMV0grb1jVoXTtFc8CGWdLRDhSJjiSpuM0t5aU01ENpKiBWmeWABDY3VCz7ymyocCJIjritJeWqQG6EKVpoNClZ4FaCyRc7bGc5UiYyy7QK5bjqEKoeBoog4EKXdG+LPYbFLuyXW55ihB8Iw+3zF61pb7tT2aq4ApNi9WrTux6c2qMbSISWlBUt9r2vNKwQih+Loz+HArQZN3m0OLS23PjrcWQlCEvIJUTwAAOeBDdwBzXuok7nvBJqTMkVPwOED/AEY/Scv+hD5V2HF4v6svmfaaUS23KaFmFDflBugcLDS3AmvDVoBpXH2q14U/NJR53YfOFKc/Km+Y2Po5uL+yJn9Wd+bj5fe0Pjh/0jP7ar8Muhjo6XQZETaTLUqMuM+Xn1LbdbLa83DpKgoA8OHmxxmd1YzxDcXvKxaL+Q6TLKbjRSasdrFvvux3uRu+6Px7bKeZccQUOtMOKQoBpAqlSU0PDPz46LKcVSjhoKU4p36WtbNPj6FSVaTUW1zewH/o5uL+yJv9Wd+bjY/e0Pjh/wBI8f2tX4ZdDK7MEg5EZEecY9Kdp8BndFCfG3kdnKjmn8JzHM/ky8MOeXcbzJNMtneM2+W126WqTAamv25x9ISmbEVoebooKqhXZWlMckdALj6PWr/9RneT/wBmz87AoluozDUXdk5lq6u3tttDOm4vupfW4C0lVOYjIhJOnEKhxQNv2tUGKf8AEucwSy2Sz7yZGiqAdNNWVMUgXbQs7FtEu5p3XK3BDWjlqVJkpkMslolS1JKSUhWfewIc+OX217q6nN3a9uqbs0qagJToU4THaOiKxoSCaOkJ15ZalYhlYdB3yD1IcujzlmvNtiWxQT4ePJiLcdTRIC9S0rSDVWKYlLeLZ1YTZ56pN+tTkYRni8hEFwKUjlq1BJ5mRI4HAosugzN7c3PINskMx2G2mlXND7ZcU7H5n2NopI0Lr8Y4hWEvVvYpvm7TNF+s9u/BmkeGuErkvd0q72jQrumuRxSJhFb9tdGGoMVqUuzOy22m0SHUym6LcSkBah7QcTngDYO3+h+WoWfzVlN/9TAC7vHTeySd2G4WTclhh2zxUd2JC8WOYkIU2VJATq7ylA6c8BadF4EOat0/3mvH6ZI++qx+k4D6EPlXYcXi/qy+Z9pm2xc9zMTBbdvyFNSJ6x7IaAFqQkmpU4DSiQcfPH0aDjv1lao85lhalVPcpu+Qa+A61f77/nRv9WNLxsr1dUjZ8PHa+tB9s9rcLVkbRuFRVcw46VkqQruFZ5ebfd9XGhzCVF1W6PkuNthFUVNcTzApuOH1UXfZq7M6U2sqT4Uc1hPd5adWS+961eONpgqmAVKKqrx8tz1ngxMMW6j3H4eTQDt4m9V7JD8dcphYjhaWwoLjrJUv1QEpBrjY4enl9eW5CNr2njrSxdKO9J3bBfrUpa1OLOpa1Fa1HtUo1J9Jx0MY2KxGobtdozOin49eP6KP/KcxzP5N5Yc8u43eSeaWwbuOSOgEXv13bOy74xHmbDt8myyylcee0gBakinPb0FOnmozUE6qKFPPQVDDtWzOmd0t0e422zQJEGUgOMPNspoUnzUyI4EHMHI4EEV1Lb2/dd3x9vbLs8YKjrMUriNJSZUtZGtOtP2tnTQq4A6jwGIZIfFr2BDhbARs4SXGGnY5Zmy4ulLi1unVJUgrSsDWVKHDJOKYiA3ptO37T3/b7Rb3XnowXAfC5BSpepx+ihVCUCndyyxDK0bXX6wC4bObuiEan7O+l0qAz5D3snR8FSlR+TikR89Mr7716RzI7itUi0x5cFypqdKGitnj/NrSPRgGB/8Aly/vDeP0Nr77gVjD3pe+lUK9cndMJh+6cpCuY7BXIVyyToHMS2vz5VwIAm8r/wBG5W17lHsUCO1dnGwIriLctlQVrSTR0tJCe7XtwCBfpVc+n1vN1+l8VmQXeR4DmxFSdITzOdTShemtU/DiFYx2Nz9BVSGEs22KH1ONpZItbgIcKgEGvJy71MUg3sCHNW6iBuW8E5ATJFT/AMVWP0nAfQh8q7Di8X9WXzPtLixdON3XNDctptNuaNFMvyVqacI7FIQ2FLHppjxYvOcPTti/G9SvXXcemhltad68POXb/S3e6BVm8of8xkyUH9xWPDHPMK9NOz/WLPTLKq60T62D9x2v1AtoUqQzNW2ni7HfW+mnl9msqHpGNhRx2DqXJw2pLtR5KmFxMNO9sdoTdIYNwl3KbdpT7zkeGkxWkOuLUC8uinO6snNCQB/CxrPyCpCEI04pWyvuS0cnT3HuyinKUnOTdiu2nz1lvQemwrK0qojAypIH16wUNJ9CdR9Ixl+OYWyMqr5bl39xjnNe1qC5L33Czx05pBm9FPx68f0Uf+U5jmPybyw55dxvMk80tg3cckdAUO9Ie1pu35MfdC2WrUrNTz6w3y1j1FtLNCHB8WmfZgDmGPe75ZkX62bQucuXt1yviZTbC0UbUAkvEUJjqV6pX3dQzyypDKwanQq3bDaY8XFnNzN0OIo808kNuR0Ed5uM2r1k/XOJJr5uGKRjowIc39ZP/rED5Nt/8hWIVHQt1tsS62yXbJiSuLNaXHfSDQ6HElKqHsOeWKQDrb08suzdv7h91PynEzoa+ciS4ladTTTmladKEUUdefowLaLb/Ll/eG8fobX33ArOhaDyYGIHdWAP8O77l9pT99RgVC7/AMt3r7l/7L9x/EDDNPWHb43d9FZECZGneL8CX3Us8kOE0bVVLhVpXVOnu9oxRYMLAglbPb4L+/dw3O4p5kGxrlz3G6VClodVy6jt06VK+EDHZYmrOODpU4eaoox6jm6FOLxE5y8sLX1lHeOoO6rq+twznIUdZJbhxVctKE9iVLTRaz5ST6Bj3YbKMPSSW6pS1u/9Dy18xq1Hp3VqRqwt47qhLCo92kAD4ji+aj/Zd1jH2q5bh6mmEdl3YfOGNrQ0SYwtm9ULndLlHtM+3+IffNEyYmWkD1lutLNAlPFSgr0Y57MskhRg6kJWJckuxP8ATabjBZnOpJQlG160M0ACtBSuZp5cc0boXW5ekzdznSblDujrcuUtTrqJSQ6gqPBKVJ0KQlIoBxoMdDgs+dKKhKCcVquNPicpVSTkpO167wBu3Tzd1r1KXBMtlP26GecKeXl0S5/Fxv8AD5zh6uiW6/3XdejrNTWy2tT5LV7An6LApuN6QoFLiW44WhQIUk6nMik5jGs/JHbCm1rfce7JU1KVvsGPuaPf5NlkMbelNQbq5oDEt9OtDY1jmK06V1OitMuOOSOgASL0TizpaZ+8b3M3HLGYbWsssj96lKVKWB8lSR5sC2jFtlntVqhphW2GzDiJ4MMoShOfEkAZk9pOBAN3J0a2XenTKYYVaLhXWmVAIaGsZhRa9Ste0AHz4FtN3Zm3d62KY/Gu+4BfLLygIZeb0ym3Qr46+8VJ0/XLJwDBzfXSe7bk3nG3BGuEePHYEQFh1Kys+HdLi805Zg5YC0amBAY3lt7cl7baYtF+9zRi281Oa8Mh/npdCQO8sgo0gK4eXAqKTpr0rTsqXOlruRnvS20MoSGuUlCEKKj8dZUSafBgGy53DtfclzuJlW/dUqzxtCUeDYYZcTqTWq9TgJ71cCFFceme5rnBegT97zZMSQAl5hcWPpUAQqh0hKuI7DgW00bD0buu3uf7l3dIheK0+IDcRo69FdFda1cNRwDZrT+hcm4XQ3eXuh526FbbvizEbCtbOnlKolxI7ugYC0bWl3kadY52mnM05aqetpr5eyuBBK2u9W+27+v0e6UFrursqFLUrJKNTqtClnsTmUk9la9mO0xGGnVwdN0/PBRkug5qjXjTxE1PyzbT6TFd+k+5Ij6vdYRc4RNWHA4hDoQeHMSvSknzpOfGg4Yzw2f0ZL1LYS5brthjWympF+DxRMVt6U7ulvBMlpq3s1Gp15xLhp26W2iqp+EjGVbP8PBeG2b5rO0xpZTWk77IoYsK37T6e2lb773t3RR2SsBUiQofa2kJ7K8EjIcSeJxztWriMwqWJXLk5I+1+83MKdLBwtb97FbuTfd6vV2ROYect7MUnwDLKylTYORWtSclLUOPZTLy16jBZTSo091pSb8zf+aDRYnHzqT3k91LQX9h6wXSLpZvccT2eBksBLb4HnRk2v0aceDF/jsJX0nuvU9HTpXWevD5xJXTW97Vp9wy7Fu7b19SBbpiFvUqqMv2byfL7NdCaeUVGObxOArUPPG7XydJuaGLp1fK9nKXAaaDhdCEh1QCVLoNRA4AnjTPHktdlh6LFbafWIUBeq96usOyRLPYnlM3/cElMG3LbVoWgAF15xK/i0QimrsrgVFxsLcf0j2lbbqs/hLrfLmp4aZLJLT6adnfScCFT1Xulxttht71vkuRXXLnDZccaUUlTairUgkdhpngVBxgQFep8+bb+n9/mwX1xpbERa2ZDSilaFZd5KhmDgEX1ndcdtMF1xRW45HZWtZ4lSkAknAC9vRud16pP2L39NtNuatLUxKYbyW9TpeU2fsiVjMHyVywKbfTPcF4l3bc1gnT/fUaxSW2od5IQFOJcQVFl0thKVLbIzI9PZgGWvUy/S7PtdxNuUU3i6PM2y1aTRXiJSw2lSfkJqr0YBHvTS/TLxtdtNyUVXi1vPWu66jVXiIqy2pSj+/TpX6cCGv1Jk3u0QYW57W88WbG+HrvbmydEmCuiZFUcCtpPfSfMcCow7bu03c+9bldoU5Z2raGkW+ChlfsJkpxKXpD6qZKDQUlCfPXz4Bh3gQ5339bXrfu65Nug6JLhlsKPxm3jqJHwL1J9GP0LKa6qYeNnJ4Xs/Q5DMKThWlby39Jhs289zWVtLMCesRk+rGeAdbA8iQsEpHmSRjPE5ZQrO2UfFrVzMaONq0rou7U7yyk9UN5vt8tMxDAPFbTKAr6qgqn1MeaGRYaLtsb52faWaV2rLeoFpUuVMkKkzH3JMhWSnnlqWunk1KJy82NrTpRgt2KSXsPDOcpO2TbZixmYEwB5TMHgpJqlQyIPlBHDBgdXSWbuGdbJki5y3JMFK0swC/3lVQDzlBwjUpNSlOZOYOOKz+lRp1FGmkpWWys6rjpspnUlBuTbXJaMHGgNsLO8WDdu4eo7lxhve5om3IyY9qmyogktvvSxqlOMtlxv1UhKddfN5cCmz07st/21f8AcFlnpVKtsxabtDujTPJjqff7splLYUsNnUArTXynAGx1ct1zn7chJt0J6e8xcYshceOkKXy29RUQCRgEXFj3bIus/wAI5t262tGhTglT2Wm2u6QNGpt1w6jqyFMCGDqfBm3Dp/f4UFhcqY/EWhiO0NS1qNO6lI4nAF9Z2nGrRBadSUONx2ULQeIUEAEH4MAAN02VGv3VSS/e7QZlkTaGksSHUnlCSl891Kkkd7Qo4FDy0WW0WWEmDaYbUGGglQYYQEJ1Hio04k+U4EAXeFi3NuTftragqNtt+3mDPZukiP4hhyc8eWlCWytsLU22Ca17tcCk2fYtzbb35dWZyjc7fuFhNweujEfw7Dc1k8pbamwtwIU42Qa171MAH1zbU7bZjSEcxTjLiUooDqJQQE0PlwIDHSa1S7VsC0QZsRcGU0l4uxnE6VpKnlqGoeUgg4BhjgAP6i/Q73W39I666r8ByPxrVTv8nzcNWrucK9mNtlH3PE9Dbb5dv6X6jX5hwdz1dmvYJR36O8xXK94cuvd1eGrTz47SPHsv4f8AI5p8Hk3uo+P2F+f/AKti+t/X/Inpfu6ifsL8/wD1bD1v6/5D0v3dRP2F+f8A6th639f8h6X7uon7C/P/ANWw9b+v+Q9L93UWNm+g/jEe9/ePhajVTl6afznJ9pp+RnjzYn7vd9Ph723/ANXdJ9qP2+9496zZ12XnQVr92+7o3uzl+7+WnwvIpy+XTu6NOVMcFW39979u9bfbpOtp7u6t3ym3j5GZMATAEwBMATAEwBMATAEwBMATAEwBMAf/2Q==" height="45" alt="支付宝">

        </div>
        <div style="margin-top: 0.5em;margin-bottom: 0.3em;">
            订单号:
            <span id="orderNumber"><?php echo $trade_no?></span>
        </div>
        <div>
            请支付
            <span style="font-size: 2em;color: red;">
                <?php echo $order['money']?>
            </span>
            元
        </div>

         <?php
           if(strpos($agent, 'iphone') || strpos($agent, 'android')){
                ?>
                <br>
                <a href="<?php echo $zhifu_url ?>" target="_blank" id="payBtn" class="payBtnAliPay">    点击去付款    </a>
            <?php
             }
            ?>

        <br>
        <br>
        <div style="margin-top: 7px;">也可用其他手机扫码付款</div>
        <div align="center">
             <span id="qrcode">
               </span>
        </div>
        <div>
            温馨提示：如充值付款时显示其它商品名
        </div>
        <div>
            或收款名称，请按提示操作放心付款即可
        </div>
        <div>
            系统会自动到账并开通您购买的产品服务
        </div>

        <div class="announcement" id="announcement">
            <div class="announcement-content">
                <span id="cityMsgssss">支付提示：系统检测到您的网络为境外IP，请关闭VPN或代理软件后再点击去付款。</span>
                <br>
                   <br>

                <button id="acknowledgeButton"> 已关闭，去付款</button>
            </div>
        </div>

    </div>

    <script data-savepage-type="" type="text/plain"></script>
    <script>
        $("#acknowledgeButton").click(function(){
            $("#acknowledgeButton").attr("disabled",true);
             $("#announcement").attr("style","display:none");
            var  trade_no ="<?php echo $trade_no?>";
          //  window.location.href='/pay/shensangda/qrcode/'+trade_no+'/';
        });
         //当前通道是否开启支持外网：
    var shifouzhichiwaiwang = "<?php echo $channel['waiwangip']?>";
    //当前用户是不是外网：
    var nishiwaiwang ="<?php echo $nishiwaiwang?>";
    if(shifouzhichiwaiwang =="1" && nishiwaiwang=="1"){
         $("#announcement").attr("style","display:flex");
    }

    if($('#qrcode').length > 0) {
                    var qrcode = new QRCode(document.getElementById("qrcode"), {
                        text: '<?=$zhifu_url?>',
                        width: 198,
                        height: 198,
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
    </script>





    </body>
    </html>
        <?php
        exit();
        }
    }else{
        if($channel['tianshiyidong']=="1"){

           if(strpos($agent, 'iphone') || strpos($agent, 'android')){?>

               <!DOCTYPE html><html lang="zh"><head>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta charset="UTF-8">
        <title>在线支付</title>
    <style>
        .payBtn {
            width: 50vw;
            border-radius: 3em;
            background: #09bb07;
            padding: 0.5em;
            color: #ffffff;
            text-decoration: none;
            font-size: 1.25em;
        }

        .payBtnAliPay {
            width: 50vw;
            border-radius: 3em;
            background: #1678ff;
            padding: 0.5em;
            color: #ffffff;
            text-decoration: none;
            font-size: 1.25em;
        }
    </style>
    <style id="savepage-cssvariables">
      :root {
      }
    </style>
    <script id="savepage-shadowloader" type="text/javascript">
      "use strict";
      window.addEventListener("DOMContentLoaded",
      function(event) {
        savepage_ShadowLoader(5);
      },false);
      function savepage_ShadowLoader(c){createShadowDOMs(0,document.documentElement);function createShadowDOMs(a,b){var i;if(b.localName=="iframe"||b.localName=="frame"){if(a<c){try{if(b.contentDocument.documentElement!=null){createShadowDOMs(a+1,b.contentDocument.documentElement)}}catch(e){}}}else{if(b.children.length>=1&&b.children[0].localName=="template"&&b.children[0].hasAttribute("data-savepage-shadowroot")){b.attachShadow({mode:"open"}).appendChild(b.children[0].content);b.removeChild(b.children[0]);for(i=0;i<b.shadowRoot.children.length;i++)if(b.shadowRoot.children[i]!=null)createShadowDOMs(a,b.shadowRoot.children[i])}for(i=0;i<b.children.length;i++)if(b.children[i]!=null)createShadowDOMs(a,b.children[i])}}}
    </script>
     <script type="text/javascript" src="/assets/111/js/jquery.min.js"></script>
    <script src="/assets/pay/js/qrcode.min.js"></script>
    <meta name="savepage-title" content="在线支付">
    <meta name="savepage-pubdate" content="Unknown">
    <meta name="savepage-state" content="Standard Items; Retain cross-origin frames; Merge CSS images; Remove unsaved URLs; Load lazy images in existing content; Max frame depth = 5; Max resource size = 50MB; Max resource time = 10s;">
    <meta name="savepage-version" content="33.9">
    <meta name="savepage-comments" content="">
    <style>
        .announcement {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            display: none;
            justify-content: center;
            align-items: center;
            height: 100vh;
            z-index: 1000;
        }

        .announcement-content {
            width: 310px;
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .announcement-content p {
            margin: 0 0 20px;
        }

        #acknowledgeButton {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }

        #acknowledgeButton:hover {
            background-color: #45a049;
        }
    </style>
      </head>

    <body>
    <div style="margin: 0 auto;padding-top: 2vh;text-align: center;">
       <img data-savepage-currentsrc="https://anan.ancaizf.com/ico/wechatpay.png" data-savepage-src="/ico/wechatpay.png" src="data:image/png;base64,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" height="45" alt="微信支付">
        <div style="margin-top: 0.5em;margin-bottom: 0.3em;">
            订单号:
            <span id="orderNumber"><?php echo $trade_no?></span>
        </div>
        <div>
            请支付
            <span style="font-size: 2em;color: red;">
                <?php echo $order['money']?>
            </span>
            元
        </div>

         <?php
           if(strpos($agent, 'iphone') || strpos($agent, 'android')){
                ?>
                <br>
                <a href="<?php echo  $zhifu_url ?>" target="_blank" id="payBtn" class="payBtnAliPay">    点击去付款    </a>
            <?php
             }
            ?>

        <br>
        <br>
        <!--<div style="margin-top: 7px;">也可用其他手机扫码付款</div>-->
        <!--<div align="center">-->
        <!--     <span id="qrcode">-->
        <!--       </span>-->
        <!--</div>-->
        <div>
            温馨提示：如充值付款时显示其它商品名
        </div>
        <div>
            或收款名称，请按提示操作放心付款即可
        </div>
        <div>
            系统会自动到账并开通您购买的产品服务
        </div>

        <div class="announcement" id="announcement" >
            <div class="announcement-content">
                <span id="cityMsgssss">支付提示：系统检测到您的网络为境外IP，请关闭VPN或代理软件后再点击去付款。</span>
                <br>
                   <br>

                <button id="acknowledgeButton"> 已关闭，去付款</button>
            </div>
        </div>

    </div>

    <script data-savepage-type="" type="text/plain"></script>
    <script>
        $("#acknowledgeButton").click(function(){
            $("#acknowledgeButton").attr("disabled",true);
             $("#announcement").attr("style","display:none");
            var  trade_no ="<?php echo $trade_no?>";
        });
         //当前通道是否开启支持外网：
            var shifouzhichiwaiwang = "<?php echo $channel['waiwangip']?>";
            //当前用户是不是外网：
            var nishiwaiwang ="<?php echo $nishiwaiwang?>";
            if(shifouzhichiwaiwang =="1" && nishiwaiwang=="1"){
                 $("#announcement").attr("style","display:flex");
            }

    </script>





    </body>
    </html>
                <?php
                exit();

            }else{?>

        <!DOCTYPE html><html><head>
            <meta name="viewport" content="initial-scale=1, maximum-scale=1, user-scalable=no, width=device-width">
            <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
            <meta http-equiv="Content-Language" content="zh-cn">
            <meta name="renderer" content="webkit">
            <title>微信支付</title>
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
            <style data-savepage-href="/assets/js/new/layer.css?v=3.1.1">.layui-layer-imgbar,.layui-layer-imgtit a,.layui-layer-tab .layui-layer-title span,.layui-layer-title{text-overflow:ellipsis;white-space:nowrap}html #layuicss-layer{display:none;position:absolute;width:1989px}.layui-layer,.layui-layer-shade{position:fixed;_position:absolute;pointer-events:auto}.layui-layer-shade{top:0;left:0;width:100%;height:100%;_height:expression(document.body.offsetHeight+"px")}.layui-layer{-webkit-overflow-scrolling:touch;top:150px;left:0;margin:0;padding:0;background-color:#fff;-webkit-background-clip:content;border-radius:2px;box-shadow:1px 1px 50px rgba(0,0,0,.3)}.layui-layer-close{position:absolute}.layui-layer-content{position:relative}.layui-layer-border{border:1px solid #B2B2B2;border:1px solid rgba(0,0,0,.1);box-shadow:1px 1px 5px rgba(0,0,0,.2)}.layui-layer-load{background:/*savepage-url=loading-1.gif*/url() center center no-repeat #eee}.layui-layer-ico{background:/*savepage-url=icon.png*/url() no-repeat}.layui-layer-btn a,.layui-layer-dialog .layui-layer-ico,.layui-layer-setwin a{display:inline-block;*display:inline;*zoom:1;vertical-align:top}.layui-layer-move{display:none;position:fixed;*position:absolute;left:0;top:0;width:100%;height:100%;cursor:move;opacity:0;filter:alpha(opacity=0);background-color:#fff;z-index:2147483647}.layui-layer-resize{position:absolute;width:15px;height:15px;right:0;bottom:0;cursor:se-resize}.layer-anim{-webkit-animation-fill-mode:both;animation-fill-mode:both;-webkit-animation-duration:.3s;animation-duration:.3s}@-webkit-keyframes layer-bounceIn{0%{opacity:0;-webkit-transform:scale(.5);transform:scale(.5)}100%{opacity:1;-webkit-transform:scale(1);transform:scale(1)}}@keyframes layer-bounceIn{0%{opacity:0;-webkit-transform:scale(.5);-ms-transform:scale(.5);transform:scale(.5)}100%{opacity:1;-webkit-transform:scale(1);-ms-transform:scale(1);transform:scale(1)}}.layer-anim-00{-webkit-animation-name:layer-bounceIn;animation-name:layer-bounceIn}@-webkit-keyframes layer-zoomInDown{0%{opacity:0;-webkit-transform:scale(.1) translateY(-2000px);transform:scale(.1) translateY(-2000px);-webkit-animation-timing-function:ease-in-out;animation-timing-function:ease-in-out}60%{opacity:1;-webkit-transform:scale(.475) translateY(60px);transform:scale(.475) translateY(60px);-webkit-animation-timing-function:ease-out;animation-timing-function:ease-out}}@keyframes layer-zoomInDown{0%{opacity:0;-webkit-transform:scale(.1) translateY(-2000px);-ms-transform:scale(.1) translateY(-2000px);transform:scale(.1) translateY(-2000px);-webkit-animation-timing-function:ease-in-out;animation-timing-function:ease-in-out}60%{opacity:1;-webkit-transform:scale(.475) translateY(60px);-ms-transform:scale(.475) translateY(60px);transform:scale(.475) translateY(60px);-webkit-animation-timing-function:ease-out;animation-timing-function:ease-out}}.layer-anim-01{-webkit-animation-name:layer-zoomInDown;animation-name:layer-zoomInDown}@-webkit-keyframes layer-fadeInUpBig{0%{opacity:0;-webkit-transform:translateY(2000px);transform:translateY(2000px)}100%{opacity:1;-webkit-transform:translateY(0);transform:translateY(0)}}@keyframes layer-fadeInUpBig{0%{opacity:0;-webkit-transform:translateY(2000px);-ms-transform:translateY(2000px);transform:translateY(2000px)}100%{opacity:1;-webkit-transform:translateY(0);-ms-transform:translateY(0);transform:translateY(0)}}.layer-anim-02{-webkit-animation-name:layer-fadeInUpBig;animation-name:layer-fadeInUpBig}@-webkit-keyframes layer-zoomInLeft{0%{opacity:0;-webkit-transform:scale(.1) translateX(-2000px);transform:scale(.1) translateX(-2000px);-webkit-animation-timing-function:ease-in-out;animation-timing-function:ease-in-out}60%{opacity:1;-webkit-transform:scale(.475) translateX(48px);transform:scale(.475) translateX(48px);-webkit-animation-timing-function:ease-out;animation-timing-function:ease-out}}@keyframes layer-zoomInLeft{0%{opacity:0;-webkit-transform:scale(.1) translateX(-2000px);-ms-transform:scale(.1) translateX(-2000px);transform:scale(.1) translateX(-2000px);-webkit-animation-timing-function:ease-in-out;animation-timing-function:ease-in-out}60%{opacity:1;-webkit-transform:scale(.475) translateX(48px);-ms-transform:scale(.475) translateX(48px);transform:scale(.475) translateX(48px);-webkit-animation-timing-function:ease-out;animation-timing-function:ease-out}}.layer-anim-03{-webkit-animation-name:layer-zoomInLeft;animation-name:layer-zoomInLeft}@-webkit-keyframes layer-rollIn{0%{opacity:0;-webkit-transform:translateX(-100%) rotate(-120deg);transform:translateX(-100%) rotate(-120deg)}100%{opacity:1;-webkit-transform:translateX(0) rotate(0);transform:translateX(0) rotate(0)}}@keyframes layer-rollIn{0%{opacity:0;-webkit-transform:translateX(-100%) rotate(-120deg);-ms-transform:translateX(-100%) rotate(-120deg);transform:translateX(-100%) rotate(-120deg)}100%{opacity:1;-webkit-transform:translateX(0) rotate(0);-ms-transform:translateX(0) rotate(0);transform:translateX(0) rotate(0)}}.layer-anim-04{-webkit-animation-name:layer-rollIn;animation-name:layer-rollIn}@keyframes layer-fadeIn{0%{opacity:0}100%{opacity:1}}.layer-anim-05{-webkit-animation-name:layer-fadeIn;animation-name:layer-fadeIn}@-webkit-keyframes layer-shake{0%,100%{-webkit-transform:translateX(0);transform:translateX(0)}10%,30%,50%,70%,90%{-webkit-transform:translateX(-10px);transform:translateX(-10px)}20%,40%,60%,80%{-webkit-transform:translateX(10px);transform:translateX(10px)}}@keyframes layer-shake{0%,100%{-webkit-transform:translateX(0);-ms-transform:translateX(0);transform:translateX(0)}10%,30%,50%,70%,90%{-webkit-transform:translateX(-10px);-ms-transform:translateX(-10px);transform:translateX(-10px)}20%,40%,60%,80%{-webkit-transform:translateX(10px);-ms-transform:translateX(10px);transform:translateX(10px)}}.layer-anim-06{-webkit-animation-name:layer-shake;animation-name:layer-shake}@-webkit-keyframes fadeIn{0%{opacity:0}100%{opacity:1}}.layui-layer-title{padding:0 80px 0 20px;height:42px;line-height:42px;border-bottom:1px solid #eee;font-size:14px;color:#333;overflow:hidden;background-color:#F8F8F8;border-radius:2px 2px 0 0}.layui-layer-setwin{position:absolute;right:15px;*right:0;top:15px;font-size:0;line-height:initial}.layui-layer-setwin a{position:relative;width:16px;height:16px;margin-left:10px;font-size:12px;_overflow:hidden}.layui-layer-setwin .layui-layer-min cite{position:absolute;width:14px;height:2px;left:0;top:50%;margin-top:-1px;background-color:#2E2D3C;cursor:pointer;_overflow:hidden}.layui-layer-setwin .layui-layer-min:hover cite{background-color:#2D93CA}.layui-layer-setwin .layui-layer-max{background-position:-32px -40px}.layui-layer-setwin .layui-layer-max:hover{background-position:-16px -40px}.layui-layer-setwin .layui-layer-maxmin{background-position:-65px -40px}.layui-layer-setwin .layui-layer-maxmin:hover{background-position:-49px -40px}.layui-layer-setwin .layui-layer-close1{background-position:1px -40px;cursor:pointer}.layui-layer-setwin .layui-layer-close1:hover{opacity:.7}.layui-layer-setwin .layui-layer-close2{position:absolute;right:-28px;top:-28px;width:30px;height:30px;margin-left:0;background-position:-149px -31px;*right:-18px;_display:none}.layui-layer-setwin .layui-layer-close2:hover{background-position:-180px -31px}.layui-layer-btn{text-align:right;padding:0 15px 12px;pointer-events:auto;user-select:none;-webkit-user-select:none}.layui-layer-btn a{height:28px;line-height:28px;margin:5px 5px 0;padding:0 15px;border:1px solid #dedede;background-color:#fff;color:#333;border-radius:2px;font-weight:400;cursor:pointer;text-decoration:none}.layui-layer-btn a:hover{opacity:.9;text-decoration:none}.layui-layer-btn a:active{opacity:.8}.layui-layer-btn .layui-layer-btn0{border-color:#1E9FFF;background-color:#1E9FFF;color:#fff}.layui-layer-btn-l{text-align:left}.layui-layer-btn-c{text-align:center}.layui-layer-dialog{min-width:260px}.layui-layer-dialog .layui-layer-content{position:relative;padding:20px;line-height:24px;word-break:break-all;overflow:hidden;font-size:14px;overflow-x:hidden;overflow-y:auto}.layui-layer-dialog .layui-layer-content .layui-layer-ico{position:absolute;top:16px;left:15px;_left:-40px;width:30px;height:30px}.layui-layer-ico1{background-position:-30px 0}.layui-layer-ico2{background-position:-60px 0}.layui-layer-ico3{background-position:-90px 0}.layui-layer-ico4{background-position:-120px 0}.layui-layer-ico5{background-position:-150px 0}.layui-layer-ico6{background-position:-180px 0}.layui-layer-rim{border:6px solid #8D8D8D;border:6px solid rgba(0,0,0,.3);border-radius:5px;box-shadow:none}.layui-layer-msg{min-width:180px;border:1px solid #D3D4D3;box-shadow:none}.layui-layer-hui{min-width:100px;background-color:#000;filter:alpha(opacity=60);background-color:rgba(0,0,0,.6);color:#fff;border:none}.layui-layer-hui .layui-layer-content{padding:12px 25px;text-align:center}.layui-layer-dialog .layui-layer-padding{padding:20px 20px 20px 55px;text-align:left}.layui-layer-page .layui-layer-content{position:relative;overflow:auto}.layui-layer-iframe .layui-layer-btn,.layui-layer-page .layui-layer-btn{padding-top:10px}.layui-layer-nobg{background:0 0}.layui-layer-iframe iframe{display:block;width:100%}.layui-layer-loading{border-radius:100%;background:0 0;box-shadow:none;border:none}.layui-layer-loading .layui-layer-content{width:60px;height:24px;background:/*savepage-url=loading-0.gif*/url() no-repeat}.layui-layer-loading .layui-layer-loading1{width:37px;height:37px;background:/*savepage-url=loading-1.gif*/url() no-repeat}.layui-layer-ico16,.layui-layer-loading .layui-layer-loading2{width:32px;height:32px;background:/*savepage-url=loading-2.gif*/url() no-repeat}.layui-layer-tips{background:0 0;box-shadow:none;border:none}.layui-layer-tips .layui-layer-content{position:relative;line-height:22px;min-width:12px;padding:8px 15px;font-size:12px;_float:left;border-radius:2px;box-shadow:1px 1px 3px rgba(0,0,0,.2);background-color:#000;color:#fff}.layui-layer-tips .layui-layer-close{right:-2px;top:-1px}.layui-layer-tips i.layui-layer-TipsG{position:absolute;width:0;height:0;border-width:8px;border-color:transparent;border-style:dashed;*overflow:hidden}.layui-layer-tips i.layui-layer-TipsB,.layui-layer-tips i.layui-layer-TipsT{left:5px;border-right-style:solid;border-right-color:#000}.layui-layer-tips i.layui-layer-TipsT{bottom:-8px}.layui-layer-tips i.layui-layer-TipsB{top:-8px}.layui-layer-tips i.layui-layer-TipsL,.layui-layer-tips i.layui-layer-TipsR{top:5px;border-bottom-style:solid;border-bottom-color:#000}.layui-layer-tips i.layui-layer-TipsR{left:-8px}.layui-layer-tips i.layui-layer-TipsL{right:-8px}.layui-layer-lan[type=dialog]{min-width:280px}.layui-layer-lan .layui-layer-title{background:#4476A7;color:#fff;border:none}.layui-layer-lan .layui-layer-btn{padding:5px 10px 10px;text-align:right;border-top:1px solid #E9E7E7}.layui-layer-lan .layui-layer-btn a{background:#fff;border-color:#E9E7E7;color:#333}.layui-layer-lan .layui-layer-btn .layui-layer-btn1{background:#C9C5C5}.layui-layer-molv .layui-layer-title{background:#009f95;color:#fff;border:none}.layui-layer-molv .layui-layer-btn a{background:#009f95;border-color:#009f95}.layui-layer-molv .layui-layer-btn .layui-layer-btn1{background:#92B8B1}.layui-layer-iconext{background:/*savepage-url=icon-ext.png*/url() no-repeat}.layui-layer-prompt .layui-layer-input{display:block;width:230px;height:36px;margin:0 auto;line-height:30px;padding-left:10px;border:1px solid #e6e6e6;color:#333}.layui-layer-prompt textarea.layui-layer-input{width:300px;height:100px;line-height:20px;padding:6px 10px}.layui-layer-prompt .layui-layer-content{padding:20px}.layui-layer-prompt .layui-layer-btn{padding-top:0}.layui-layer-tab{box-shadow:1px 1px 50px rgba(0,0,0,.4)}.layui-layer-tab .layui-layer-title{padding-left:0;overflow:visible}.layui-layer-tab .layui-layer-title span{position:relative;float:left;min-width:80px;max-width:260px;padding:0 20px;text-align:center;overflow:hidden;cursor:pointer}.layui-layer-tab .layui-layer-title span.layui-this{height:43px;border-left:1px solid #eee;border-right:1px solid #eee;background-color:#fff;z-index:10}.layui-layer-tab .layui-layer-title span:first-child{border-left:none}.layui-layer-tabmain{line-height:24px;clear:both}.layui-layer-tabmain .layui-layer-tabli{display:none}.layui-layer-tabmain .layui-layer-tabli.layui-this{display:block}.layui-layer-photos{-webkit-animation-duration:.8s;animation-duration:.8s}.layui-layer-photos .layui-layer-content{overflow:hidden;text-align:center}.layui-layer-photos .layui-layer-phimg img{position:relative;width:100%;display:inline-block;*display:inline;*zoom:1;vertical-align:top}.layui-layer-imgbar,.layui-layer-imguide{display:none}.layui-layer-imgnext,.layui-layer-imgprev{position:absolute;top:50%;width:27px;_width:44px;height:44px;margin-top:-22px;outline:0;blr:expression(this.onFocus=this.blur())}.layui-layer-imgprev{left:10px;background-position:-5px -5px;_background-position:-70px -5px}.layui-layer-imgprev:hover{background-position:-33px -5px;_background-position:-120px -5px}.layui-layer-imgnext{right:10px;_right:8px;background-position:-5px -50px;_background-position:-70px -50px}.layui-layer-imgnext:hover{background-position:-33px -50px;_background-position:-120px -50px}.layui-layer-imgbar{position:absolute;left:0;bottom:0;width:100%;height:32px;line-height:32px;background-color:rgba(0,0,0,.8);background-color:#000\9;filter:Alpha(opacity=80);color:#fff;overflow:hidden;font-size:0}.layui-layer-imgtit *{display:inline-block;*display:inline;*zoom:1;vertical-align:top;font-size:12px}.layui-layer-imgtit a{max-width:65%;overflow:hidden;color:#fff}.layui-layer-imgtit a:hover{color:#fff;text-decoration:underline}.layui-layer-imgtit em{padding-left:10px;font-style:normal}@-webkit-keyframes layer-bounceOut{100%{opacity:0;-webkit-transform:scale(.7);transform:scale(.7)}30%{-webkit-transform:scale(1.05);transform:scale(1.05)}0%{-webkit-transform:scale(1);transform:scale(1)}}@keyframes layer-bounceOut{100%{opacity:0;-webkit-transform:scale(.7);-ms-transform:scale(.7);transform:scale(.7)}30%{-webkit-transform:scale(1.05);-ms-transform:scale(1.05);transform:scale(1.05)}0%{-webkit-transform:scale(1);-ms-transform:scale(1);transform:scale(1)}}.layer-anim-close{-webkit-animation-name:layer-bounceOut;animation-name:layer-bounceOut;-webkit-animation-fill-mode:both;animation-fill-mode:both;-webkit-animation-duration:.2s;animation-duration:.2s}@media screen and (max-width:1100px){.layui-layer-iframe{overflow-y:auto;-webkit-overflow-scrolling:touch}}</style>
            <style id="savepage-cssvariables">
              :root {
                --savepage-url-3: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADgAAACXCAMAAAB3EmgXAAAABGdBTUEAALGPC/xhBQAAAAFzUkdCAK7OHOkAAANvaVRYdFhNTDpjb20uYWRvYmUueG1wAAAAAAA8P3hwYWNrZXQgYmVnaW49Iu+7vyIgaWQ9Ilc1TTBNcENlaGlIenJlU3pOVGN6a2M5ZCI/PiA8eDp4bXBtZXRhIHhtbG5zOng9ImFkb2JlOm5zOm1ldGEvIiB4OnhtcHRrPSJBZG9iZSBYTVAgQ29yZSA1LjUtYzAxNCA3OS4xNTE0ODEsIDIwMTMvMDMvMTMtMTI6MDk6MTUgICAgICAgICI+IDxyZGY6UkRGIHhtbG5zOnJkZj0iaHR0cDovL3d3dy53My5vcmcvMTk5OS8wMi8yMi1yZGYtc3ludGF4LW5zIyI+IDxyZGY6RGVzY3JpcHRpb24gcmRmOmFib3V0PSIiIHhtbG5zOnhtcE1NPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvbW0vIiB4bWxuczpzdFJlZj0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL3NUeXBlL1Jlc291cmNlUmVmIyIgeG1sbnM6eG1wPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvIiB4bXBNTTpPcmlnaW5hbERvY3VtZW50SUQ9InhtcC5kaWQ6ZWYyODkyMTMtZWMxOS00YjhlLTk1YTAtZDg4MjI4MmIyNmVkIiB4bXBNTTpEb2N1bWVudElEPSJ4bXAuZGlkOkJDNDBGQTRBRTM1RjExRTQ5RTgwRTdCMjNEOThDMjA2IiB4bXBNTTpJbnN0YW5jZUlEPSJ4bXAuaWlkOkJDNDBGQTQ5RTM1RjExRTQ5RTgwRTdCMjNEOThDMjA2IiB4bXA6Q3JlYXRvclRvb2w9IkFkb2JlIFBob3Rvc2hvcCBDQyAoTWFjaW50b3NoKSI+IDx4bXBNTTpEZXJpdmVkRnJvbSBzdFJlZjppbnN0YW5jZUlEPSJ4bXAuaWlkOjM2ZTUwMDMzLTI2ZDctYTc0Ny1iOGM3LWE5ZDljZDk2OGNmMyIgc3RSZWY6ZG9jdW1lbnRJRD0ieG1wLmRpZDpCNzNEN0M4Q0UzNUMxMUU0QjY3MEQwQjU2NkE4Q0UxMyIvPiA8L3JkZjpEZXNjcmlwdGlvbj4gPC9yZGY6UkRGPiA8L3g6eG1wbWV0YT4gPD94cGFja2V0IGVuZD0iciI/PhLgzx4AAAAZdEVYdFNvZnR3YXJlAEFkb2JlIEltYWdlUmVhZHlxyWU8AAAAQlBMVEVMaXHv7+8l0CUQzBDp6en8/Pz09PT////39/cAyACA5IDp+ulV21U71TvH88eT6JOg66B24na177XY99j0/fRm3mbKNClsAAAAAXRSTlMAQObYZgAAAzlJREFUaN7t2dlyrCAQAFBFWRRBcfn/X72NzigKDUpVbh5iP6QqwTOAbD2k4HzuVdXswZE4nqhUP3NecE0aN9IQgmheyKp5DptKFgZ+DpI/CDlAXaaAhvb8YfTQ2ALqlU+hBFREuoXHC19Y8Mx44V+HTWb8AnzH8YXZk/w9O16YB/Nyuezscc1X5yds3vLV7Aw5OyfP/hZQ13XJhLdMC2+FC1bCs8UeQlDBANMopIAYPClcaIOCFSgUoOj24BXacGt1IdR2PBSC0GIRgALamIBQKfMgq09PIJDW5QWWNb0D9zq/8FJfBNJNfiC71BeBIOkOqeciECbIDksmHkBhB2WFohaPoCg/sBQPIfTSQlo/hdBLCwM9TEBar9B/pSkIL8UWhFqagIxBAWPPoSihoBTPIbU7RLCLPwXFCsV/hL/Qx9zhyJ4AuVMue5JnL6vshfzdOp7C3M0qd3vM3ZCzj4DMQ8c/5ti9Yy54sNIkzD3K0eRBlBSFUFji6QplpQikK9TmTCyRIMEHXxKkNSWjN1IyeknJYKCoDQ+mYn+2bc/M2Exx/1trfzMhODTNcIK8c+TqOh6Aoy0ZT5AecnM0APWa6lbahXxWH7k6NXMfTp8UuZpcyOWadberI5L7UO5p+VrM4wUHDH8w2pQdIl1BO/+F2MtDX/cXYsOFDvAn0AmCTqk/FJfvonpoF6U6048Tjy9Rp2jq1em7ntG3oO78WzsyJKHswhd+ZIzDAb8rXGJwid0yKonCLn4/ua2NAFxSN5tLGA6J+pYp3FQZZaS3PdRjAMY6qNbBmGHpzR6ccNZtM6e3u0fvQezNVGbr2rhNQ+JBsr/zzusaTN/9j/oK9xrW2xa3a3CymeOjegw2Bj6fOF2DcXKvTwwKG3up0yrzXYdanScBDk8XSd5q6XBYHafybPxph8Nj3+2JPzztFTp3QIS7I3eJ8Qrdp8bTyJ1DXqG7NqBKg00/f5K7t2SEYPNW+3C4808KFVrI6gaUISjTbgjvOTrlWmyX0zdceF+dSKKd6E5OUamm+NmBHR194tChVXC6DMljbs1munE0inyvNJd+unGwdsdGM+lx1BPl/NaJTKrkRfLF/QOCidL5094dOAAAAABJRU5ErkJggg==);
                --savepage-url-4: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAB4AAAAHCAMAAAAoNw3DAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAA3NpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuNS1jMDE0IDc5LjE1MTQ4MSwgMjAxMy8wMy8xMy0xMjowOToxNSAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wTU09Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9tbS8iIHhtbG5zOnN0UmVmPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvc1R5cGUvUmVzb3VyY2VSZWYjIiB4bWxuczp4bXA9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC8iIHhtcE1NOk9yaWdpbmFsRG9jdW1lbnRJRD0ieG1wLmRpZDplZjI4OTIxMy1lYzE5LTRiOGUtOTVhMC1kODgyMjgyYjI2ZWQiIHhtcE1NOkRvY3VtZW50SUQ9InhtcC5kaWQ6QjY1RUZEQjFFMzVDMTFFNEI2NzBEMEI1NjZBOENFMTMiIHhtcE1NOkluc3RhbmNlSUQ9InhtcC5paWQ6QjY1RUZEQjBFMzVDMTFFNEI2NzBEMEI1NjZBOENFMTMiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENDIChNYWNpbnRvc2gpIj4gPHhtcE1NOkRlcml2ZWRGcm9tIHN0UmVmOmluc3RhbmNlSUQ9InhtcC5paWQ6ZjM0NDNlZmQtMDkwNy00NDc1LWJlOTYtNzRmOWRhZTg5MWVlIiBzdFJlZjpkb2N1bWVudElEPSJ4bXAuZGlkOmVmMjg5MjEzLWVjMTktNGI4ZS05NWEwLWQ4ODIyODJiMjZlZCIvPiA8L3JkZjpEZXNjcmlwdGlvbj4gPC9yZGY6UkRGPiA8L3g6eG1wbWV0YT4gPD94cGFja2V0IGVuZD0iciI/PqOuhZMAAAAtUExURff39////+7u7vX19fb29vr6+vHx8fT09PPz8+/v7+3t7erq6vDw8Pj4+Ozs7EoyMs4AAABBSURBVHjadMdLDsAgCAVAHqD4v/9xTWtMG6KzG6KXlEQ/rswU67VNAYR6qRkegY+VhkXjoTljS0N9i+DT2XUKMAClGgHHUVvOJwAAAABJRU5ErkJggg==);
              }
            </style>
            <script id="savepage-shadowloader" type="text/javascript">
              "use strict";
              window.addEventListener("DOMContentLoaded",
              function(event) {
                savepage_ShadowLoader(5);
              },false);
              function savepage_ShadowLoader(c){createShadowDOMs(0,document.documentElement);function createShadowDOMs(a,b){var i;if(b.localName=="iframe"||b.localName=="frame"){if(a<c){try{if(b.contentDocument.documentElement!=null){createShadowDOMs(a+1,b.contentDocument.documentElement)}}catch(e){}}}else{if(b.children.length>=1&&b.children[0].localName=="template"&&b.children[0].hasAttribute("data-savepage-shadowroot")){b.attachShadow({mode:"open"}).appendChild(b.children[0].content);b.removeChild(b.children[0]);for(i=0;i<b.shadowRoot.children.length;i++)if(b.shadowRoot.children[i]!=null)createShadowDOMs(a,b.shadowRoot.children[i])}for(i=0;i<b.children.length;i++)if(b.children[i]!=null)createShadowDOMs(a,b.children[i])}}}
            </script>
            <meta name="savepage-title" content="微信支付">
            <meta name="savepage-state" content="Standard Items; Retain cross-origin frames; Merge CSS images; Remove unsaved URLs; Load lazy images in existing content; Max frame depth = 5; Max resource size = 50MB; Max resource time = 10s;">
            <meta name="savepage-version" content="33.9">
            <meta name="savepage-comments" content="">
              </head>
            <body>
            <div class="body">
            <h1 class="mod-title">
            <span class="ico-wechat"></span><span class="text">微信支付</span>
            </h1>
            <div class="mod-ct">

            <div class="mobile-tip" style="display: none;">提示：二维码会风控，请复制下方链接支付</div>
            <div class="amount">￥<?php echo $order['money']?></div>

            <div class="mobile-btn" style="">
                <span style="font-weight:bold"><?php echo $current_url ?></span>
                <br>
                <br>
                <br>
                <a class="btn-copy-link" id="copy-btn" data-clipboard-text="<?php echo $current_url ?>">复制</a>

               <div class="mobile-tip" style="margin-top:20px;font-weight:bold">支付提示:请复制以上链接，用"手机浏览器打开" 完成支付</div>

            </div>

            <div class="detail detail-open" id="orderDetail">
            <dl class="detail-ct" style="display: block;">
            <dt>商家</dt>
            <dd id="storeName">在线充值</dd>

            <dt>商户订单号</dt>
            <dd id="billId"><?php echo $trade_no?></dd>
            <dt>创建时间</dt>
            <dd id="createTime"><?php echo $now_time?></dd>
            </dl>
            <a href="javascript:void(0)" class="arrow"><i class="ico-arrow"></i></a>
            </div>
            <div class="tip">
            <span class="dec dec-left"></span>
            <span class="dec dec-right"></span>


            <div class="tip-text">
            </div>
            </div>

            </div>
            <script data-savepage-type="" type="text/plain" data-savepage-src="/assets/js/new/jquery/1.12.4/jquery.min.js"></script>
            <script src="/assets/js/new/clipboard.min.js"></script>

            <script data-savepage-type="" type="text/plain"></script>
            <script>
                // 初始化 clipboard.js
                var clipboard = new ClipboardJS('#copy-btn');

                // 监听复制事件
                clipboard.on('success', function(e) {
                    console.log('内容已复制:', e.text);
                    alert('复制成功!');
                    e.clearSelection();
                });

                clipboard.on('error', function(e) {
                    console.log('复制失败');
                    alert('复制失败，请手动复制。');
                });
            </script>
            </body></html>
                <?php
            }
         exit();

         }
    }


echo "<script>window.location.href='/pay/xiaopeng/qrcode/{$trade_no}/?sitename={$sitename}';</script>";

?>
