<?php
//付款码获取
function erweima($channel)
{
	$myconfig=explode("\r\n",$channel["appsecret"]);
	$return = array();
	foreach($myconfig as $key => $val)
	{
		$arr = explode("|",$val);
		$return['a'.(string)$arr[0]]  = $arr[1];
	}
	return $return;
	
}


//融合200个赋值二维码
function ronghe($make_money,$erweima)
{
	$huoma = $erweima["a0"];//动态码链接

	$guma = array();
	
	$dongma = array();
	
	foreach($make_money as $val)
	{
		if(isset($erweima['a'.$val]))
		{
			$guma['a'.$val]['val'] = $erweima['a'.$val];
			$guma['a'.$val]['url']  = 1;
		}
		else
		{
			$dongma['a'.$val]['val'] = $huoma;
			$dongma['a'.$val]['url'] = 0;
		}
		
	}
	return array_merge($guma,$dongma);
}

//有效期等处理
function cord($key,$val,$order,$time,$channel)
{
		global $DB;
		$money = str_replace("a",'',$key);
		//echo $money."<br>";
		$trade_no = $order['trade_no'];
		$info= $DB->getRow("SELECT * FROM pre_rand WHERE type = 'haoyunpay' and zfb ='".$channel["appid"]."'  and price='".$order["money"]."' and ip = '".$order["ip"]."' and status = 0 and time >'".$time."' LIMIT 1");

		if($info!=""){
			$reorder=$info["reorder"]."[".$info["orderno"]."]";
			$DB->exec("update `pre_rand` set `orderno` = '".$trade_no."', `reorder` = '".$reorder."', `erweima` = '".$val['val']."'  where id = ".$info["id"]);
			$DB->exec("update `pre_order` set  `randmoney` = '".$info["money"]."'  where trade_no = '".$trade_no."'");
			echo "<script>window.location.href='/pay/haoyunpay/qrcode/".$trade_no."/?sitename={$sitename}';</script>";
			exit;
		}
		
		$row= $DB->getRow("SELECT * FROM pre_rand WHERE type = 'haoyunpay' and zfb ='".$channel["appid"]."'  and money='".$money."' LIMIT 1");
		
		if($row)
		{
			if(($row["status"]==0 and $row["time"]+360<time()) or $row["status"] == 1){
				$DB->exec("update `pre_rand` set `orderno` = '".$trade_no."', `url`= '".$val['url']."', `ip` = '".$order["ip"]."',  `status` = '0', `time` ='".time()."'  , `erweima` = '".$val['val']."' where id = ".$row["id"]);
                $DB->exec("update `pre_order` set  `randmoney` = '".$money."'  where trade_no = '".$trade_no."'");
			}
		}
		else
		{
			   $DB->exec("INSERT INTO `pre_rand` (`type`,`orderno`, `zfb`,  `money`, `price`, `status`, `url`, `ip`, `time`,`erweima`) VALUES (:type,:orderno, :zfb,  :money, :price, :status, :url , :ip , :time,:erweima)", [':type'=>'haoyunpay',':orderno'=>$trade_no,':zfb'=>$channel["appid"],':money'=>$money,':price'=>$order["money"],':status'=>0,':url'=>$val['url'],':ip'=>$order["ip"], ':time'=>time(), ':erweima'=>$val['val']]);
            $DB->exec("update `pre_order` set  `randmoney` = '".$money."'  where trade_no = '".$trade_no."'");
			return true;
		}
		
	return false;
}
?>