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

$token_td = $conf['budantoken'];  //支付商通道token


$id = $_POST['id'];



$ip_o =$DB->getRow("select * from pre_budandetail where id='".$id."' limit 1");
if(!$ip_o){
  $return_data = array('code'=>201,'msg'=>"查询失败,补单单号异常");
  echo json_encode($return_data);
  exit();  
}
//这里存在一个问题，可能是其他订单需要催促处理：
if($ip_o['status'] !="0"){
    //查询其他的订单信息：
    $budan_all =  $DB->getRow("select * from pre_budandetail where status='0' and tianshibudan_id ='".$ip_o['tianshibudan_id']."'");
    if(!$budan_all){
        $return_data = array('code'=>201,'msg'=>"查询失败,补单单号异常");
        echo json_encode($return_data);
        exit();  
    }
    $all_channel_arr = array();
    foreach ($budan_all as $s=>$v){
        $all_channel_arr[$v['channel']] = $v['channel'];
    }
    foreach ($all_channel_arr as $ss=>$vv){
        $pre_channel =  $DB->getRow("select chatid from pre_channel where id ='".$vv."' limit 1");
        $shangyou_chatid = $pre_channel['chatid']; 
        
        $parameter = [
                'chat_id' => $shangyou_chatid,
                'text' => "这单麻烦尽快处理一下，客户在催，谢谢！",
                'reply_to_message_id' =>$ip_o['message_id'] 
            ];
        $response2 = http_post_datas($token_td,'sendMessage', json_encode($parameter));
    }
    exit();
    
}

$pre_channel =  $DB->getRow("select chatid from pre_channel where id ='".$ip_o['channel']."' limit 1");
$shangyou_chatid = $pre_channel['chatid']; 

$parameter = [
        'chat_id' => $shangyou_chatid,
        'text' => "这单麻烦尽快处理一下，客户在催，谢谢！",
        'reply_to_message_id' =>$ip_o['message_id'] 
    ];
$response2 = http_post_datas($token_td,'sendMessage', json_encode($parameter));



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

