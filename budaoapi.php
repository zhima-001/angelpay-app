<?php
if(preg_match('/Baiduspider/', $_SERVER['HTTP_USER_AGENT']))exit;
$nosession = true;
require './includes/common.php';

if (function_exists("set_time_limit"))
{
	@set_time_limit(0);
}
if (function_exists("ignore_user_abort"))
{
	@ignore_user_abort(true);
}




//同一个ip，120分钟内不能提交超过10次补单
$iptijiao = $conf['iptijiao'];
$ipchadan = $conf['ipchadan'];
$date = time();

$localurl = $conf['localurl'];

$end_time = time();
$start_time = time()-120*60;
//获取iP 一个IP 一天最多可以提交 
$ip = getClientIPs(0,true);
$ip_all =$DB->getRow("select count(id) as all_ipcha from pre_budanxianzhi where type='1' and ip='".$ip."' and createtime between ".$start_time." and ".$end_time."  limit 1");
if($ip_all){
   $have_tijiao = $ip_all['all_ipcha'];
}else{
    $have_tijiao = 0;
}
if($have_tijiao>=$iptijiao){
    echo "提交补单次数太多了,稍后再试试吧";
    exit();
}

//新增补单限制数据：

$DB->exec("INSERT INTO `pre_budanxianzhi` (`ip`,  `createtime`, `type`) VALUES ('{$ip}',   '{$date}',  '1')");


//随机字符串：
$pp = md5(date("Y-m-d H:i:s"));

$targetDirectory = "budanuploads/"; // 上传文件的目标目录
$targetFile = $targetDirectory .$pp. basename($_FILES['file']["name"]);
$uploadOk = 1;
$imageFileType = strtolower(pathinfo($targetFile,PATHINFO_EXTENSION));

//处理订单号：
$ordersn = $_POST['ordersn'];
$money = $_POST['money'];
if(empty($ordersn)){
    echo "订单号不能为空"; 
    exit();
}

$pp = "";
$pp_arr= array();
$order_str = "";
$order_arr = explode("\n",$ordersn);
$order_str_arr = array();
foreach ($order_arr as $k=>$v){
    $v = trim($v);
    //  if(!checkPrefixIsDate($v)){
    //       $pp .= $v."订单号不正确，请核实！"."\r\n"; 
        
    //  }
    //限制订单号长度不能超过36个字符
    if (strlen(trim($v)) > 36) {
            echo "订单号不能超过36个字符: " . htmlspecialchars(trim($order));
            exit;
        }
     $order_str .=$v.",";
     $order_str_arr[] = $v;
}
if(!empty($pp)){
     echo $pp;
     exit();
}
$order_str = substr($order_str,0,-1);    
if(count($order_str_arr)>6){
    echo "每次提交不能多于6个订单号";
    exit();
}



// 确保文件是真实的图片
if(isset($_POST["submit"])) {
    $check = getimagesize($_FILES['file']["tmp_name"]);
    // if($check !== false) {
    //     echo "文件是一个有效的图片 - " . $check["mime"] . ".";
    //     $uploadOk = 1;
    // } else {
    //     echo "文件不是一个有效的图片.";
    //     $uploadOk = 0;
    // }
}

// 检查文件是否已存在
if (file_exists($targetFile)) {
    echo "抱歉，文件已经存在.";
    $uploadOk = 0;
}

// 允许上传的文件格式
if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
&& $imageFileType != "gif" ) {
    echo "抱歉，只允许 JPG, JPEG, PNG & GIF 文件.";
    $uploadOk = 0;
}

// 检查 $uploadOk 是否为 0
if ($uploadOk == 0) {
    echo "抱歉，文件未上传.";
    exit();
// 如果一切正常，尝试上传文件
} else {
    if (move_uploaded_file($_FILES['file']["tmp_name"], $targetFile)) {
        
    } else {
        echo "抱歉，上传文件时出现了错误.";
        exit();
    }
}
//生成订单号：
$apiorder = date("YmdHis").rand(1000,9999);
$token = $conf['tianshijiqiren']; 
$token_td = $conf['budantoken'];  //支付商通道token

//查询用户订单记录：

$ip_o =$DB->getRow("select ip from pre_tianshibudan where order_sn='".$order_str."' limit 1");

if($ip_o){
  echo "以下订单号已为你提交补单，请5分钟后看看是否到账:<br>".$order_str;
  exit();  
}
//遍历查询订单数据金额：
$sss = "";
$sss_arr = array(); 

$order_str_arr = array_unique($order_str_arr);
if(count($order_str_arr)<=0){
     echo "你还没有提供订单号！";
     exit(); 
}
//记录到提交订单的
$jilv_dengdai_order = array();
//呈现给商户的：
$chengxian_order = array();
$order_str_arr_cha = $order_str_arr;
foreach ($order_str_arr as $ks=>$vs){
    //先查查是不是系统订单号：
    $ip_o =$DB->getAll("select money,out_trade_no,trade_no from pre_order where trade_no='".$vs."' limit 1");
    if(!$ip_o){
     
        $ip_o =$DB->getAll("select money,out_trade_no,trade_no from pre_order where out_trade_no='".$vs."'");
        if(!$ip_o){
            $pp_arr[] = $vs;
            continue;
        }
    }
   
    if(floor($money) != floor($ip_o[0]['money'])){
            $sss .= $vs.",";
            $sss_arr[] = $vs;
            continue;
    }
    if(count($ip_o)>20){
        echo $vs."该订单无法进行补单";
        exit();
    }
    foreach ($ip_o as $ks=>$vsp){
       $jilv_dengdai_order[$vsp['trade_no']] = $vsp['trade_no'];
    }
    $chengxian_order[] = $vs;
}

if(count($pp_arr)>0){
    echo "以下订单号并非我们支付平台，已剔除：<br>";
    $pp_arr_str = implode("\r\n",$pp_arr);
    echo $pp_arr_str;
    echo "<br>";
}
if(count($sss_arr)>0){
    echo "以下订单号和你填写的金额不符，已剔除：<br>";
    $sss_arr_str = implode("\r\n",$sss_arr);
    echo $sss_arr_str;
    echo "<br>";
}

$valueCount = array_count_values($jilv_dengdai_order);

$repeatedValues = array();
foreach ($valueCount as $value => $count) {
    if ($count > 1) {
        // 如果出现次数大于1，说明这个值是重复的
        $repeatedValues[] = $value;
    }
}

$chongfutijiaode_arr = array();

if(count($repeatedValues)>0){
    foreach($repeatedValues as $sckey=>$vchfvalue){
        $jilvkeys = array_keys($jilv_dengdai_order, $vchfvalue);
        //这里移除掉一个：
        unset($jilv_dengdai_order[$jilvkeys[0]]);
        //记录起来：
        $chongfutijiaode_arr[] = $jilvkeys[0];
    }
}
if(count($chongfutijiaode_arr)>0){
    echo "以下订单号是本次提交的重复订单，已剔除:<br>";
    $sss_qaarr_str = implode(",",$chongfutijiaode_arr);
    echo $sss_qaarr_str;
    echo "<br>";
}

$yijingtijiao = array();
foreach ($jilv_dengdai_order as $ks=>$saq){
     $budan_detail =$DB->getRow("select id from pre_budandetail where order_sn='".$saq."' or trade_no='".$saq."' limit 1");
     if($budan_detail){
         $yijingtijiao[] = $saq;
         unset($jilv_dengdai_order[$ks]);
     }
}

if(count($yijingtijiao)>0){
    echo "以下订单之前已提交过:<br>";
    $sss_arr_str = implode("\r\n",$yijingtijiao);
    echo $sss_arr_str."\r\n\r\n";
    echo "请复制订单号，请往首页右上角点击“补单查询 ”进行查看";
    echo "<br>";
}

$result_arr = $jilv_dengdai_order;




if(count($result_arr)>0){
    $stpp = $DB->exec("INSERT INTO `pre_tianshibudan` (`ip`, `image`, `order_sn`, `status`, `createtime`, `apiorder`) VALUES ('{$ip}', '{$targetFile}',  '{$order_str}', '0',  '{$date}',  '{$apiorder}')");
}else{
    // if(count($pp_arr)>0){
    //         echo "以下订单号并非我们支付平台，已剔除：<br>";
    //         $pp_arr_str = implode(",",$pp_arr);
    //         echo $pp_arr_str;
    //         echo "<br>";
    //     }
    

    exit();
}

$arry_ke = array_keys($result_arr);


if($stpp){
    $tianshibudan_id = $DB->lastinsertid();
    //处理订单信息：
    // $order_arr = explode("\n",$ordersn);
    $msg_shanghu = "您提交了一个新补单，正处理中：\r\n 订单号如下：\r\n\r\n";
    $shanghu_chatid = "";
    $channel_arr = array();
    foreach ($result_arr as $keys=>$values){
        //查询订单信息是否存在：
      	$rs_order=$DB->getRow("SELECT channel,money,addtime,ip,uid,trade_no,out_trade_no from pre_order where trade_no>={$values} limit 1");
      	if($rs_order){
      	    $channel = $rs_order['channel'];
      	    $t_order_sn = $rs_order['out_trade_no'];
      	    $money = $rs_order['money'];
      	    $addtime = $rs_order['addtime'];
      	    $ip = $rs_order['ip'];
      	    $uid = $rs_order['uid'];
      	    $trade_no = $rs_order['trade_no'];
      	    $stpp_detail = $DB->exec("INSERT INTO `pre_budandetail` (`tianshibudan_id`, `order_sn`, `channel`, `ip`, `money`,`addtime`,`uid`,`trade_no`) VALUES ('{$tianshibudan_id}', '{$t_order_sn}',  '{$channel}', '{$ip}','{$money}','{$addtime}','{$uid}','{$trade_no}')");
      	   	$old_chs = $DB->lastinsertid();
      	    if($stpp_detail){
      	        $uid_info = $DB->getRow("select chatid from pre_botsettle where merchant ='".$uid."' limit 1");
      	        if($uid_info){
      	            //发消息给商户：
      	           $msg_shanghu .="<b>".$t_order_sn."</b>\r\n";
      	           $shanghu_chatid = $uid_info['chatid'];
      	        }
      	        //这里先把同渠道的加入一个集合：
      	        $channel_arr[$channel][]=array($trade_no,$old_chs);
      	    }
      	}else{
      	    echo "订单号：".$keys."查询异常！";
      	    echo "没有发现需要提交的订单信息！请核实后再提交";
      	    //删除照片+原始订单信息：
      	    $DB->exec("DELETE FROM pre_tianshibudan WHERE id='$tianshibudan_id'");
      	    exit();
      	}
    }
    if(!empty($shanghu_chatid)){
          $msg_shanghu .= "\r\n补单编号：<b>".$apiorder."</b>\r\n".'特此告知！';
                $parameter = array(
                'chat_id' => $shanghu_chatid,
                'parse_mode' => 'HTML',
 
                 'photo'=>$localurl.$targetFile,
                'caption'=>$msg_shanghu,
            );
         
            $response = http_post_datas($token,'sendPhoto', json_encode($parameter));
            // 解析响应
            $response_data = json_decode($response, true);
            
            if ($response_data['ok']) {
                $message_id = $response_data['result']['message_id'];
                $DB->exec("update pre_tianshibudan set message_id='$message_id'  WHERE id='$tianshibudan_id'");
                
            }
            //  $parameter2 = array(
            //     'chat_id' => $shanghu_chatid,
            //     'parse_mode' => 'HTML',
            //      'reply_to_message_id' => $message_id,
            //      'text' => "嗯嗯嗯嗯~",
 
            // );
            //http_post_datas($token,'sendMessage', json_encode($parameter2));
    }


    if(count($channel_arr)>0){
        //给上游发送信息：
        
        foreach($channel_arr as $ley=>$dsa){
            //查新上游的渠道：
             $pre_channel =  $DB->getRow("select chatid from pre_channel where id ='".$ley."' limit 1");
            $shangyou_chatid = $pre_channel['chatid'];
            
            
            if($shangyou_chatid){
                
      
                $inline_keyboard_arr[0] = array('text' => "确定已支付,处理回调!", "callback_data" => "chulihuidiao_" . $apiorder."_".$ley);
                $inline_keyboard_arr2[0] = array('text' => "没有支付,无需处理!", "callback_data" => "meiyouzhifu_" . $apiorder."_".$ley);

                $keyboard = [
                    'inline_keyboard' => [
                        $inline_keyboard_arr,
                        $inline_keyboard_arr2
                       
                    ]
                ];
                $dingdan = array();
                $dingdan_id = array();
                foreach ($dsa as $sk=>$cvs){
                    $dingdan[] = $cvs[0];
                    $dingdan_id[] = $cvs[1];
                    
                }
               
                $shangyou_msg = "商户反馈订单已支付,但未处理,订单号如下：\r\n\r\n";
                $shangyou_msg .= "<b>".implode("\r\n",$dingdan)."</b>\r\n\r\n 请尽快处理！";
                $parameter = array(
                    'chat_id' => $shangyou_chatid,
                    'photo'=>$localurl.$targetFile,
                    'caption'=>$shangyou_msg,
                    'reply_markup' => $keyboard,
                     'parse_mode' => 'HTML',
                );
            $response2 = http_post_datas($token_td,'sendPhoto', json_encode($parameter));
            $response_data2 = json_decode($response2, true);
            $xiangxidingdanid = implode(",",$dingdan_id);
         
             if ($response_data2['ok']) {
                    $message_id = $response_data2['result']['message_id'];
                   $DB->exec("update pre_budandetail set message_id='$message_id'  WHERE id in (".$xiangxidingdanid.")");
             }
         
        }
            
        }

    }
    
  
    echo "以下订单号已成功提交补单，正在处理中:<br>";
    echo "请务必复制补单编号:".$apiorder."\r\n5-10分钟后回到首页右上角点击“补单查询” 进入查看";
    $result_arr_str = implode(",",$chengxian_order);
    echo $result_arr_str;
    exit();
}else{
     echo "提交补单失败，请直接联系天使客服";
}

exit();


function http_post_datas($token,$action,$data_string){
        //这里，
        /*$sql= "insert into wolive_tests (content) values ('".json_encode($data)."')";
        $this->pdo->exec($sql);*/
        $link = 'https://api.telegram.org/bot' . $token . '';
         $url =$link. "/" . $action . "?";
      
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_POST, 1);

        curl_setopt($ch, CURLOPT_URL, $url);

        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);

        curl_setopt($ch, CURLOPT_HTTPHEADER, array(

                'Content-Type: application/json; charset=utf-8',

                'Content-Length: ' . strlen($data_string))

        );

        ob_start();

        curl_exec($ch);

        $return_content = ob_get_contents();

        //echo $return_content."


        ob_end_clean();

        $return_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        // return array($return_code, $return_content);

        return $return_content;

    }
function checkPrefixIsDate($string) {
    // 正则表达式匹配 YYYYMMDD 格式
    $pattern = '/^(19|20)\d{2}(0[1-9]|1[0-2])(0[1-9]|[12][0-9]|3[01])/';
    // 执行正则表达式匹配
    if (preg_match($pattern, $string)) {
        return true;
    } else {
        return false;
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