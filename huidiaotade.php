<?php

function md5Sign($prestr, $key) {
		$prestr = $prestr . $key;
		return md5($prestr);
	}
function createLinkstringUrlencode($para) {
		$arg  = "";
		foreach ($para as $key=>$val) {
			$arg.=$key."=".urlencode($val)."&";
		}
		//去掉最后一个&字符
		$arg = substr($arg,0,-1);

		return $arg;
	}
function createLinkstring($para) {
		$arg  = "";
		foreach ($para as $key=>$val) {
			$arg.=$key."=".$val."&";
		}
		//去掉最后一个&字符
		$arg = substr($arg,0,-1);

		return $arg;
	}
function argSort($para) {
		ksort($para);
		reset($para);
		return $para;
	}
function paraFilter($para) {
		$para_filter = array();
		foreach ($para as $key=>$val) {
			if($key == "sign" || $key == "sign_type" || $val == "" || $key == "stype" || $key == "request_method" || $key == "u_channel" )continue;
			else $para_filter[$key] = $para[$key];
		}
		return $para_filter;
	}

function send_post($url, $post_data)
{

        $postdata = http_build_query($post_data);
        $options = array(
            'http' => array(
                'method' => 'POST',
                'header' => 'Content-type:application/x-www-form-urlencoded',
                'content' => $postdata,
                'timeout' => 15 * 60 // 超时时间（单位:s）
            )
        );
        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);

        return $result;
    }
            $data =array(
                'uid'=>"1021",
                "trade_no"=>"250706022049799424",
                "out_trade_no"=>"250706022049799424",
                "type"=>"alipay",
                "name"=>"秘密花园订单[xzcasdfawsdas]",
                "money"=>"299.00", 
                
            );
    	    $array=array('pid'=>$data['uid'],'trade_no'=>$data['trade_no'],'out_trade_no'=>$data['out_trade_no'],'type'=>$data['type'],'name'=>$data['name'],'money'=>(float)$data['money'],'trade_status'=>'TRADE_SUCCESS');
            $appkey="e473X0400V4E44oE779477V5YE744Xq7";
            $huidiaourl = "https://tt.momocat.me/wp-content/plugins/erphpdown/payment/easepay/notify_url.php"; 
            
            
            
            $arg=argSort(paraFilter($array));
	        $prestr=createLinkstring($arg);
	        $urlstr=createLinkstringUrlencode($arg); 
	        $sign=md5Sign($prestr, $appkey);
	
	        $url=$huidiaourl.'?'.$urlstr.'&sign='.$sign.'&sign_type=MD5';
		
            echo $url;
            exit();
            
            // echo $url;
            // exit();
            $money = "30";
            $type =  "alipay";
            $stype = $_GET['stype'];
            $mysgin = "";
            $domain = "";
            $arr = array(
                'pid' => "1207",
                'trade_no' => "12023080616574557848",
                'out_trade_no' => "12023080616574557848",
                'type' => $type,
                'trade_status' => "TRADE_SUCCESS",
                'name' => "12023080616574557848",
                'notify_url'=>$huidiaourl,
                'return_url'=>$huidiaourl,
                'money' => $money,
                'request_method'=>"JSON",

            );
           
            $post_url = "https://ccc.tianshi353.top:6789/submit.php";
            ksort($arr);
            reset($arr);
            $sign = '';
            $sign2 = '';
            foreach ($arr as $k => $v) {
                //$sign.=$k.'='.$v.'&';
                $sign2.=$k.'='.$v.'&';
            }
            $arg  = "";
            foreach ($arr as $key=>$val) {
                $arg.=$key."=".$val."&";
            }
            //去掉最后一个&字符
            $arg = substr($arg,0,-1);
            
            //$sign = trim($sign,'&');
            $sign = md5($arg.$appkey);
          
            $arr['sign']=$sign;
            $arr['request_method']="JSON";
            $arr['sign_type']="MD5";
             var_dump($arr);
            exit();
            $get_data = trim(send_post($post_url, $arr));
            
            var_dump($get_data);
            exit();
            
            $pp = explode("__", $get_data);

            $pay_url = $huidiaourl . "pay/" . $pp[0] . "/qrcode/" . $pp[1] . "/?sitename=VIP" . "`";

            $parameter = array(
                'code' => 200,
                'pay_url' =>  $pay_url
            );
            echo json_encode($parameter);
            exit();
 


?>
