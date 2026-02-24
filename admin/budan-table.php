<?php
/**
 * 结算列表
**/
include("../includes/common.php");
if($islogin==1){}else exit("<script language='javascript'>window.location.href='./login.php';</script>");

function display_type($type){
	if($type==1)
		return '支付宝';
	elseif($type==2)
		return '微信';
	elseif($type==3)
		return 'QQ钱包';
	elseif($type==4)
		return '银行卡';
	else
		return 1;
}

function display_status($status, $id){
	if($status==0)
		return '<font color=green>待处理</font>';
	elseif($status==1)
		return '<font color=orange>已回调处理</font>';
	elseif($status==2)
			return '<font color=orange>并未支付</font>';
	else
		return '<font color=blue>未知状态</font>';
}

if(isset($_GET['batch']) && !empty($_GET['batch'])) {
	$sql=" `batch`='{$_GET['batch']}'";
	$numrows=$DB->getColumn("SELECT count(*) from pre_settle WHERE{$sql}");
	$con='批次号 '.$_GET['batch'].' 共有 <b>'.$numrows.'</b> 条结算记录';
	$link='&my=search&column='.$_GET['column'].'&value='.$_GET['value'];
}elseif(isset($_GET['value']) && !empty($_GET['value'])) {
	$sql=" `{$_GET['column']}`='{$_GET['value']}'";
	$numrows=$DB->getColumn("SELECT count(*) from pre_settle WHERE{$sql}");
	$con='包含 '.$_GET['value'].' 的共有 <b>'.$numrows.'</b> 条结算记录';
	$link='&my=search&column='.$_GET['column'].'&value='.$_GET['value'];
}else{
	$numrows=$DB->getColumn("SELECT count(*) from pre_tianshibudan WHERE 1");
	$sql=" 1";
	$con='共有 <b>'.$numrows.'</b> 条补单记录';
}
?>
	<form name="form1" id="form1">
	  <div class="table-responsive">
<?php echo $con?>
        <table class="table table-striped table-bordered table-vcenter">
            <!--<th>商户号</th>-->
          <thead><tr><th>ID</th><th>补单单号</th><th>订单号列表</th><th>支付凭证</th><th>添加时间</th><th>审核时间</th><th>状态</th><th>操作人</th></tr></thead>
          <tbody>
<?php
$pagesize=30;
$pages=ceil($numrows/$pagesize);
$page=isset($_GET['page'])?intval($_GET['page']):1;
$offset=$pagesize*($page - 1);

$rs=$DB->query("SELECT * FROM pre_tianshibudan WHERE{$sql} order by id desc limit $offset,$pagesize");
while($res = $rs->fetch())
{
    //根据单号需要
    // $ordersn = explode("\n",$res['ordersn']);
    $res['createtime'] = date("Y-m-d H:i:s",$res['createtime']);
    if($res['updatetime']>0){
        $res['updatetime'] = date("Y-m-d H:i:s",$res['updatetime']);
    }else{
        $res['updatetime'] ="待处理";
    }
    $new_msg = "";
    if(!empty($res['order_sn'])){
        //处理：
        //$ordersn_arr = explode(",",$res['order_sn']);
        //foreach ($ordersn_arr as $ks=>$sv){
            $rse=$DB->getAll("SELECT * FROM pre_budandetail WHERE tianshibudan_id={$res['id']}");
            foreach ($rse as $ks=>$vs){
                //查询支付渠道：
                $channem_info = $DB->getRow("SELECT * FROM pre_channel WHERE id={$vs['channel']} limit 1");
                if($vs['status']=="1"){
                    $pppx= "已支付";
                }elseif($vs['status']=="2"){
                    $pppx= "未支付";
                }else{
                    $pppx= "未处理";
                }
                //这里还要查询下这个订单目前真实的状态：
                $order_info = $DB->getRow("SELECT status FROM pre_order WHERE trade_no={$vs['trade_no']} limit 1");
                if($order_info['status']=="1"){
                    $pppx11= "已支付";
                }else{
                    $pppx11= "未支付";
                }
                $new_msg .="订单号：".$vs['order_sn']."(".$pppx.")<br>"."商户号：".$vs['uid']."<br>渠道：".$channem_info['name']."<br>金额：".$vs['money']."元<br>订单系统状态：".$pppx11."<br><br>";

            }
        //}
    }
    
echo '<tr><td>'.$res['id'].'</td><td><b>'.$res['apiorder'].'</b></td><td><b>'.$new_msg.'</b></td><td><img style="width:60px" src="../'.$res['image'].'"></td><td>'.$res['createtime'].'</td><td>'.$res['updatetime'].'</td><td>'.display_status($res['status'],$res['id']).'</td><td><b>'.$res['admin_id'].'</b></td></tr>';
}
?>
          </tbody>
        </table>
		<input name="chkAll1" type="checkbox" id="chkAll1" onClick="this.value=check1(this.form.list1)" value="checkbox">&nbsp;全选&nbsp;
<select name="status"><option selected>推送商户</option><option value="0">推送支付商</option><option value="1">回调处理</option><option value="4">删除记录</option></select>
<button type="button" onclick="operation()">确定</button>
      </div>
	 </form>
<?php
echo'<div class="text-center"><ul class="pagination">';
$first=1;
$prev=$page-1;
$next=$page+1;
$last=$pages;
if ($page>1)
{
echo '<li><a href="javascript:void(0)" onclick="listTable(\'page='.$first.$link.'\')">首页</a></li>';
echo '<li><a href="javascript:void(0)" onclick="listTable(\'page='.$prev.$link.'\')">&laquo;</a></li>';
} else {
echo '<li class="disabled"><a>首页</a></li>';
echo '<li class="disabled"><a>&laquo;</a></li>';
}
$start=$page-10>1?$page-10:1;
$end=$page+10<$pages?$page+10:$pages;
for ($i=$start;$i<$page;$i++)
echo '<li><a href="javascript:void(0)" onclick="listTable(\'page='.$i.$link.'\')">'.$i .'</a></li>';
echo '<li class="disabled"><a>'.$page.'</a></li>';
for ($i=$page+1;$i<=$end;$i++)
echo '<li><a href="javascript:void(0)" onclick="listTable(\'page='.$i.$link.'\')">'.$i .'</a></li>';
if ($page<$pages)
{
echo '<li><a href="javascript:void(0)" onclick="listTable(\'page='.$next.$link.'\')">&raquo;</a></li>';
echo '<li><a href="javascript:void(0)" onclick="listTable(\'page='.$last.$link.'\')">尾页</a></li>';
} else {
echo '<li class="disabled"><a>&raquo;</a></li>';
echo '<li class="disabled"><a>尾页</a></li>';
}
echo'</ul></div>';
