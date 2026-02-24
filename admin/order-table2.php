<?php
/**
 * 订单列表
**/
include("../includes/common.php");
if($islogin==1){}else exit("<script language='javascript'>window.location.href='./login.php';</script>");



$paytype = [];
$paytypes = [];
$rs = $DB->getAll("SELECT * FROM pre_type");
foreach($rs as $row){
	$paytype[$row['id']] = $row['showname'];
	$paytypes[$row['id']] = $row['name'];
}
unset($rs);

$sqls="";
$links='';
if(isset($_GET['uid']) && !empty($_GET['uid'])) {
	$uid = intval($_GET['uid']);
	$sqls.=" AND A.`uid`='$uid'";
	$links.='&uid='.$uid;
}
if(isset($_GET['topzidingyi']) && !empty($_GET['topzidingyi'])) {
	$topzidingyi = trim($_GET['topzidingyi']);
	$sqls.=" AND B.`topzidingyi`='$topzidingyi'";
	$links.='&topzidingyi='.$topzidingyi;
}

if(isset($_GET['type']) && $_GET['type']>0) {
	$type = intval($_GET['type']);
	$sqls.=" AND A.`type`='$type'";
	$links.='&type='.$type;
}elseif(isset($_GET['channel']) && $_GET['channel']>0) {
	$channel = intval($_GET['channel']);
	$sqls.=" AND A.`channel`='$channel'";
	$links.='&channel='.$channel;
}
if(isset($_GET['dstatus']) && $_GET['dstatus']>0) {
	$dstatus = intval($_GET['dstatus']);
	$sqls.=" AND A.status={$dstatus}";
	$links.='&dstatus='.$dstatus;
}
if($_GET['orderpp_type']=="-1") {
    $orderpp_type = "1";
	$sqls.=" AND A.is_shoudongnotify={$orderpp_type}";
	$links.='&is_shoudongnotify='.$orderpp_type;
}
if($_GET['orderpp_type']=="2") {
    //自动回调
    $orderpp_type = "-1";
	$sqls.=" AND A.api_trade_no !={$orderpp_type}";
	$links.='&api_trade_no !='.$orderpp_type;
}

if($_GET['order_type']=="5") {
    //自动完成订单支付
    $order_type = "0";
	$sqls.=" AND A.is_shoudongstatus ={$order_type} AND A.status=1";
	$links.='&is_shoudongstatus ='.$order_type;
}
if($_GET['order_type']=="4") {
    //手动完成订单支付
    $order_type = "1";
	$sqls.=" AND A.is_shoudongstatus ={$order_type}";
	$links.='&is_shoudongstatus ='.$order_type;
}
if(isset($_GET['value']) && !empty($_GET['value'])) {
	if($_GET['column']=='name'){
		$sql=" A.`{$_GET['column']}` like '%{$_GET['value']}%'";
	}elseif($_GET['column']=='addtime'){
	 
		$kws = explode(' - ',$_GET['value']);
		$sql=" A.`addtime`>='{$kws[0]}' AND A.`addtime`<='{$kws[1]}'";
	}elseif($_GET['column']=='endtime'){
	 
		$kws = explode(' - ',$_GET['value']);
		$sql=" A.`endtime`>='{$kws[0]}' AND A.`endtime`<='{$kws[1]}'";
	}elseif($_GET['column']=='money'){
	 
		$kws = explode('>',$_GET['value']);
		$sql=" A.`money`>='{$kws[0]}' AND A.`money`<='{$kws[1]}'";
	}else{
		$sql=" A.`{$_GET['column']}`='{$_GET['value']}'";
	}
	$sql.=$sqls;
	$numrows=$DB->getColumn("SELECT count(*) from pre_order A LEFT JOIN pre_channel B ON A.channel=B.id WHERE{$sql}");
	$con='包含 '.$_GET['value'].' 的共有 <b>'.$numrows.'</b> 条订单';
	$link='&column='.$_GET['column'].'&value='.$_GET['value'].$links;
}else{
	$sql=" 1";
	$sql.=$sqls;
	$numrows=$DB->getColumn("SELECT count(*) from pre_order A LEFT JOIN pre_channel B ON A.channel=B.id WHERE{$sql}");
	$con='共有 <b>'.$numrows.'</b> 条订单';
	$link=$links;
}

$rs=$DB->query("SELECT A.*,B.plugin,B.name,B.feilv,B.topzidingyi FROM pre_order A LEFT JOIN pre_channel B ON A.channel=B.id WHERE{$sql}");


$all_chenggong=0;
$all_chenggongjiner=0;
$channel_arr = array();
$channel_arr2 = array();
while($res = $rs->fetch())
{
    $channel_arr2[$res['topzidingyi']]['all_order'] +=1;
    $channel_arr2[$res['topzidingyi']]['channel_id'][] = $res['channel'];
    
    
    $channel_arr[$res['channel']]['name'] = $res['name'];
    $channel_arr[$res['channel']]['feilv'] = $res['feilv'];
    $channel_arr[$res['channel']]['all_order'] +=1;
    if($res['status']=="1"){
        if($res['feilv']>0){
            $res['feilv']=$res['feilv'];
        }else{
            $res['feilv'] = 0;
        }
        $can_money = $res['money']*number_format(((100-$res['feilv'])/100),2);
        
        $channel_arr2[$res['topzidingyi']]['all_pay'] +=1;
        $channel_arr2[$res['topzidingyi']]['all_paymoney'] +=$res['money'];

        $channel_arr2[$res['topzidingyi']]['all_paymoney_fei'] +=$can_money;
        
        $all_chenggong+=1;
        $all_chenggongjiner+=$res['money'];
        
        $channel_arr[$res['channel']]['all_pay'] +=1;
        $channel_arr[$res['channel']]['all_paymoney'] += $res['money'];

        $channel_arr[$res['channel']]['all_paymoney_fei'] +=$can_money;
    }
 
}
$ret_data = array(
    'number'=>$numrows,
    'all_chenggong'=>$all_chenggong,
    'all_chenggongjiner'=>$all_chenggongjiner
);
if(isset($_GET['topzidingyi']) && !empty($_GET['topzidingyi'])) {
    if(count($channel_arr2)>0){
        $tongjihtml = "<ul>";
        foreach ($channel_arr2 as $sq=>$sv){
             $tongjihtml .="<li>自定义编号：".$sq."(".$sv['all_pay']."/".$sv['all_order'].")</li><li>合计跑量：".$sv['all_paymoney']."元</li><li>合计扣费率后余额：".$sv['all_paymoney_fei']."元</li>";
            $tongjihtml .="<br/>";
            foreach ($channel_arr as $sq2=>$sv2){
                 $tongjihtml .="<li>".$sv2['name'].":".$sv2['all_paymoney']."元</li><li>费率：".$sv2['feilv']."</li><li>扣费率后余额：".$sv2['all_paymoney_fei']."元</li>";
                  $tongjihtml .="<br/>";
            }
           
        }
        $ret_data['tongji']=$tongjihtml."</ul>";
       
    }else{
        $ret_data['tongji']="";
    }
       
}

	exit(json_encode($ret_data));
?>

