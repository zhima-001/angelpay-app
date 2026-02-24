<?php
session_start();
header('Content-Type: application/json'); // 设置响应头为 JSON

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
$ipchadan = $conf['ipchadan'];
$search_number =$_POST['search_number'];
$end_time = time();
$start_time = time()-120*60;

$user_captcha = $_POST['captcha'];

if ($user_captcha != $_SESSION['captcha']) {

    $return_data = array('code'=>201,'msg'=>"验证码错误,重新输入");
    echo json_encode($return_data);
    exit();
}

//获取iP 一个IP 一天最多可以提交 
$ip = getClientIPs(0,true);
$ip_all =$DB->getRow("select count(id) as all_ipcha from pre_budanxianzhi where type='2' and ip='".$ip."' and createtime between ".$start_time." and ".$end_time."  limit 1");


if($ip_all){
   $have_tijiao = $ip_all['all_ipcha'];
}else{
    $have_tijiao = 0;
}
if($have_tijiao>=$ipchadan){
   
    $return_data = array('code'=>201,'msg'=>"补单查询次数太多了,稍后再试试吧");
    echo json_encode($return_data);
    exit();
 
}

//新增补单限制数据：

$DB->exec("INSERT INTO `pre_budanxianzhi` (`ip`,  `createtime`, `type`) VALUES ('{$ip}',   '{$end_time}',  '2')");

//查询数据：
//先看看是不是补单订单号：
$ip_o =$DB->getRow("select id,order_sn,status from pre_tianshibudan where apiorder='".$search_number."' limit 1");
//如果已经成功：

$find_arr = array();
if($ip_o){
    
    if($ip_o['status']=="1"){
          //如果存在信息：那就查询对应的其他明细：
            $ip_2o =$DB->getAll("select * from pre_budandetail where tianshibudan_id='".$ip_o['id']."' and status='1'");
    }else{
          //如果存在信息：那就查询对应的其他明细：
         $ip_2o =$DB->getAll("select * from pre_budandetail where tianshibudan_id='".$ip_o['id']."' ");
    }
    
 
   
   foreach ($ip_2o as $key=>$value){
       $find_arr[] = array(
           'budan_id'=>$ip_o['id'],
           'xiangqing_id'=>$value['id'],
           'budan_order'=> $search_number,
           'shanghu_order'=>$value['order_sn'],
           'xitong_order'=>$value['trade_no'],
           'budan_status'=>$ip_o['status'],
           'xiangqing_status'=>$value['status'],
           
        );
   }
    $return_data = array('code'=>200,'msg'=>"查询成功",'data'=>$find_arr,'budan_status'=>$ip_o['status'],'type'=>"1","budan_order"=>$search_number);
    echo json_encode($return_data);
    exit();

}


//再看提交的补单订单号所属详细订单号：
$budan_detail =$DB->getRow("select * from pre_budandetail where order_sn='".$search_number."' or trade_no='".$search_number."' limit 1");
if($budan_detail){
    //如果存在信息：那就查询对应的其他明细：
   $ip_zhu =$DB->getRow("select id,order_sn,status from pre_tianshibudan where id='".$budan_detail['tianshibudan_id']."' ");
   
//   foreach ($ip_2o as $key=>$value){
       $find_arr[] = array(
           'budan_id'=>$ip_zhu['id'],
           'xiangqing_id'=>$budan_detail['id'],
           'budan_order'=> $search_number,
           'shanghu_order'=>$budan_detail['order_sn'],
           'xitong_order'=>$budan_detail['trade_no'],
           'budan_status'=>$ip_zhu['status'],
           'xiangqing_status'=>$budan_detail['status'],
          
           
        );
//   }
    $return_data = array('code'=>200,'msg'=>"查询成功",'data'=>$find_arr,'budan_status'=>$ip_zhu['status'], 'type'=>"2","budan_order"=>$search_number);
    echo json_encode($return_data);
    exit();
}
$return_data = array('code'=>201,'msg'=>"没有找到相关的补单记录");
echo json_encode($return_data);
exit();

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