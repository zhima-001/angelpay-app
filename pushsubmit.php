<?php
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
            $pid = "1000";
            $appkey="OLX80wBVaF80xElzuuWARoEXzrvpjAbr";
            $huidiaourl = "http://139.180.188.227:4562";
            $money = "100";
            $type =  "wxpay";
            $stype = $_GET['stype'];
            $mysgin = "";
            $domain = "";
            $arr = array(
                'pid' => $pid,
                'type' => $type,
                'out_trade_no' => date("YmdHis") . rand(11111, 99999),
                'notify_url' => $huidiaourl,
                'return_url' => $huidiaourl,
                'name' => "telegram测试下单",
                 'sitename' => "telegram测试下单",
                'money' => $money,

            );
            $post_url = "https://ccc.tianshi337.top/submit.php";
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
