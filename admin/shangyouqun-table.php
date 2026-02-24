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
		return '<font color=green>未扣除</font>';
	elseif($status==1)
		return '<font color=orange>已扣除</font>';
	else
		return '<font color=blue>未知状态</font>';
}



if(isset($_GET['order']) && !empty($_GET['order'])) {
	$sql=" `order`='{$_GET['order']}'";
	$numrows=$DB->getColumn("SELECT count(*) from pre_shangyouhuifu WHERE{$sql}");
	$con='系统订单号 '.$_GET['order'].' 共有 <b>'.$numrows.'</b> 条监听条件记录';
	$link='&my=search&order='.$_GET['order'].'&out_trade_no='.$_GET['out_trade_no'];
}elseif(isset($_GET['out_trade_no']) && !empty($_GET['out_trade_no'])) {
	$sql=" `out_trade_no`='{$_GET['out_trade_no']}'";
	$numrows=$DB->getColumn("SELECT count(*) from pre_shangyouhuifu WHERE{$sql}");
	$con='包含 '.$_GET['out_trade_no'].' 的共有 <b>'.$numrows.'</b> 条监听条件记录';
	$link='&my=order&order='.$_GET['order'].'&out_trade_no='.$_GET['out_trade_no'];
}else{
	$numrows=$DB->getColumn("SELECT count(*) from pre_shangyouhuifu WHERE 1");
	$sql=" 1";
	$con='共有 <b>'.$numrows.'</b> 条监听条件记录';
}

if(isset($_GET['channel']) && !empty($_GET['channel'])) {
	$sql .=" and `channel`='{$_GET['channel']}'";
    $link .="&channel=".$_GET['channel'];
}
if(isset($_GET['paytype']) && !empty($_GET['paytype'])) {
	$sql .=" and `paytype`='{$_GET['paytype']}'";
    $link .="&paytype=".$_GET['paytype'];
}
if(isset($_GET['status']) && !empty($_GET['status'])) {
	$sql .=" and `status`='{$_GET['status']}'";
    $link .="&status=".$_GET['status'];
}
if(isset($_GET['admin']) && !empty($_GET['admin'])) {
	$sql .=" and `admin`='{$_GET['admin']}'";
    $link .="&admin=".$_GET['admin'];
}

?>
	<form name="form1" id="form1">
	  <div class="table-responsive">
<?php echo $con?>
        <table class="table table-striped table-bordered table-vcenter">
            <!--<th>商户号</th>-->
          <thead><tr><th>ID</th><th>条件</th><th>回复内容</th><th>添加时间</th><th>操作</th></tr></thead>
          <tbody>
<?php
$pagesize=30;
$pages=ceil($numrows/$pagesize);
$page=isset($_GET['page'])?intval($_GET['page']):1;
$offset=$pagesize*($page - 1);

$rs=$DB->query("SELECT * FROM pre_shangyouhuifu WHERE{$sql} order by id desc limit $offset,$pagesize");
// var_dump("SELECT * FROM pre_tousu WHERE{$sql} order by id desc limit $offset,$pagesize");
while($res = $rs->fetch())
{
    //根据单号需要
    // $ordersn = explode("\n",$res['ordersn']);
    $res['createtime'] = date("Y-m-d H:i:s",$res['createtime']);
 
    $new_msg = $res['order'];
    if($res['status'] =="0"){ 
        $ss = '<a class='.'"btn btn-xs btn-danger" onclick="tuisong('.$res['id'].')">扣除商户余额</a>';
    }else{
       $ss = '<a class='.'"btn btn-xs btn-info" >完成推送</a>';

    }
    
echo '<tr><td>'.$res['id'].'</td><td>'.$res['conditions'].'</td><td>'.$res['reply_content'].'</td><td>'.$res['createtime'].'</td><td>&nbsp;<a class="btn btn-xs btn-info" onclick="editframe('.$res['id'].','.$res['status'].')">编辑</a>&nbsp;<a class="btn btn-xs btn-danger" onclick="delItem('.$res['id'].','.$res['status'].')">删除</a>&nbsp;</td></tr>';
} 
?>
          </tbody>
        </table>
<!--		<input name="chkAll1" type="checkbox" id="chkAll1" onClick="this.value=check1(this.form.list1)" value="checkbox">&nbsp;全选&nbsp;-->
<!--<select name="status"><option selected>推送商户</option></select>-->
<!--<button type="button" onclick="operation()">确定</button>-->
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
