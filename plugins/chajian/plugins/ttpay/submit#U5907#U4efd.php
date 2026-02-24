<?php
if(!defined('IN_PLUGIN'))exit();
//require_once(PAY_ROOT."inc/epay_submit.class.php");
if($order["money"]%2!=0){
    echo "金额错误";exit;
}
$myconfig=explode("\r\n",$channel["appsecret"]);
$arr=explode("#",$myconfig[1]);
foreach($myconfig as $k=>$v){
    if($k>1){
        $s_arr=explode("|",$v);
        $short[$s_arr[0]]=$s_arr[1];
    }
}
//print_r($short["0.99"]);
for($x=0; $x<200; $x++){
    $money=$order["money"]-$x/100;
    $row= $DB->getRow("SELECT * FROM pay_rand WHERE type = 'ttpay' and zfb ='".$channel["appurl"]."' and acc = '".$channel["appmchid"]."' and money='".$money."' LIMIT 1");
    if($row==""){
        $res=money($money,$myconfig,$arr,$short);
        if($res>0){
            $DB->exec("INSERT INTO `pay_rand` (`type`,`orderno`, `zfb`, `acc`, `money`, `status`, `url`, `time`) VALUES (:type,:orderno, :zfb, :acc, :money, :status, :url , :time)", [':type'=>'ttpay',':orderno'=>TRADE_NO,':zfb'=>$channel["appurl"],':acc'=>$channel["appmchid"],':money'=>$money,':status'=>0,':url'=>$res, ':time'=>time()]);
            break;
        }else{
            $arr["type"]=1;
            $arr["money"]=$money;
            $other[]=$arr;
            continue;
        }
    }else{
        if(($row["status"]==0 and $row["time"]+300<time()) or $row["status"] == 1){
            $res=money($money,$myconfig,$arr,$short);
            if($res>0){
                $time=time();
                $DB->exec("update `pay_rand` set `orderno` = '".TRADE_NO."', `url`= '".$res."', `status` = '0', `time` ='".$time."'  where id = ".$row["id"]);
                break;
            }else{
                $arr["rowid"]=$row["id"];
                $arr["type"]=2;
                $arr["money"]=$money;
                $other[]=$arr;
                continue;
            }            
        }
    }
}
//print_R($short);
//echo "通道：".$channel["name"]."<br>";
if($res==0){
    if(isset($other[0])){
        if($other[0]["type"]==2){
            $time=time();
            $DB->exec("update `pay_rand` set `orderno` = '".TRADE_NO."', `url` = '0', `status` = '0', `time` ='".$time."' where id = ".$other[0]["rowid"]);
        }
        if($other[0]["type"]==1){
            $DB->exec("INSERT INTO `pay_rand` (`type`,`orderno`, `zfb`, `acc`, `money`, `status`, `url`, `time`) VALUES (:type,:orderno, :zfb, :acc, :money, :status, :url , :time)", [':type'=>'ttpay',':orderno'=>TRADE_NO,':zfb'=>$channel["appurl"],':acc'=>$channel["appmchid"],':money'=>$other[0]["money"],':status'=>0,':url'=>$res, ':time'=>time()]);
        }
        /*echo "金额：".$other2[0]."<br>";
        echo "任意码-";
        echo $short[0]."<br>";exit;*/
    }
    //echo "无码可用";exit;
}
/*if($res==1){
    echo "金额：".$money."<br>";
    echo "长连接-";
    echo 'alipays://platformapi/startapp?appId=20000123&actionType=scan&biz_data={"s": "money","u": "'.$channel["appmchid"].'","a": "'.$money.'","m":"'.$money.'"}'."<br>";;
}
if($res==2){
    echo "金额：".$money."<br>";
    echo "短连接-";
    echo $short["$money"]."<br>";
}*/
function money($money,$myconfig,$arr,$short){
    if($myconfig[0]=="放行"){
        if(in_array($money,$arr)){
            return 1;
            
        }else{
            if(isset($short["$money"])){
                return 2;
            }else{
                return 0;
            }
        }
    }
    if($myconfig[0]=="拦截"){
        if(!in_array($money,$arr)){
            return 1;
        }else{
            if(isset($short["$money"])){
                return 2;
            }else{
                return 0;
            }
        }
    }    
}
echo "<script>window.location.href='/pay/ttpay/qrcode/{$trade_no}/?sitename={$sitename}';</script>";
/*for($x=0; $x<10; $x++){
    $money=$order["money"]+$rand/10;
    $row= $DB->getColumn("SELECT * FROM pay_rand WHERE money='".$money."' LIMIT 1");
    if($row==""){
        
        
    }else{
        
    }

}
/*if(!is_int($num)){
    echo "金额错误";exit;
}else{
    $order["money"]=round($order["money"]);
}
for($x=0; $x<10; $x++){
    $path=PAY_ROOT."lock/".$order["money"]."/".$rand."/".$money.".txt";
    if(file_exists($path)){
        $fp = fopen($path, "r");
        if(flock($fp,LOCK_EX | LOCK_NB)){
            
        }
    }else{
        break;
    }
    $rand=$x/10;
    $money=$order["money"]+$rand;
    
    
    $time=strtotime($order["addtime"]);
}*/

//echo "<script>window.location.href='/pay/zfbjk/qrcode/{$trade_no}/';</script>";
/*if(strpos($_SERVER['HTTP_USER_AGENT'], 'QQ/')!==false && in_array('2',$channel['apptype'])){
	echo "<script>window.location.href='/pay/qqpay/jspay/{$trade_no}/';</script>";
}elseif(checkmobile()==true){
	echo "<script>window.location.href='/pay/qqpay/wap/{$trade_no}/?sitename={$sitename}';</script>";
}else{
	echo "<script>window.location.href='/pay/qqpay/qrcode/{$trade_no}/?sitename={$sitename}';</script>";
}*/


?>