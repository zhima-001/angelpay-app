

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>加载中。。。</title>
</head>
<body>
    <div style="text-align: center;position: absolute;
    left: 50%;
    top: 50%;
    transform: translate(-50%, -50%);letter-spacing: 5px;" id="allp">
        <div>
            <span style="font-size:35px" class="ppp"></span>
        </div>
        <div>
            <span style="font-size:35px">跳转等待<span class="ptime" style="color: red">10</span></span>
        </div>
        <div>
            <span style="font-size:35px">请勿<span style="color: red">刷新</span></span>
        </div>
    </div>
    <script>
        var timer = 10;
        var ptimer = setInterval(function () {
            timer--;
            if(timer<=0){
                document.querySelector("#allp").innerHTML= "下单异常,请重新回到用户端下单";
            }else{
                document.querySelector(".ptime").innerHTML = timer;
            }
        },1000)
    </script>
</body>
</html>

<?php
if(!defined('IN_PLUGIN'))exit();
include 'function.php';
//require_once(PAY_ROOT."inc/epay_submit.class.php");
$order= $DB->getRow("SELECT * FROM pre_order WHERE trade_no = '".$trade_no."'");


//var_dump($channel);exit;
echo "<script>window.location.href='/pay/yiqiaodi/qrcode/{$trade_no}/?sitename={$sitename}';</script>";

?>
