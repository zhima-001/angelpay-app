<?php
/**
 * 订单列表
**/
include("../includes/common.php");
if($islogin2==1){}else exit("<script language='javascript'>window.location.href='./login.php';</script>");


function display_status($status){
	if($status==1)
		$msg = '<font color=green>已回调</font>';
	elseif($status==2)
		$msg = '<font color=red>未查到</font>';
	elseif($status==3)
		$msg = '<font color=red>出补单时间</font>';
	elseif($status==4)
		$msg = '<font color=red>超时支付无法补</font>';
	elseif($status==5)
		$msg = '<font color=red>其他</font>';
	else
		$msg = '<font color=blue>等待处理</font>';
	
	return $msg;
}

$paytype = [];
$paytypes = [];


$sql=" uid=$uid";
$links='';

if(isset($_GET['dstatus']) && $_GET['dstatus']==1) {
	$sql.=" AND status=1";
	$links.='&status=1';
}
if(isset($_GET['kw']) && !empty($_GET['kw'])) {
	$kw=daddslashes($_GET['kw']);
	if($_GET['type']==1){
		$sql.=" AND `apiorder`='{$kw}'";
	}
	$numrows=$DB->getColumn("SELECT count(*) from pre_budandetail   WHERE{$sql}  group by tianshibudan_id ");
	$con='包含 '.$_GET['value'].' 的共有 <b>'.$numrows.'</b> 条订单';
	$link='&type='.$_GET['type'].'&kw='.$_GET['kw'].$links;
}else{
	$numrows=$DB->getColumn("SELECT count(*) from pre_budandetail  WHERE{$sql}  group by tianshibudan_id ");
	$con='共有 <b>'.$numrows.'</b> 条订单';
	$link=$links;
}
?> 
	  <div class="table-responsive">
        <table class="table table-striped table-bordered table-vcenter">
          <thead><tr><th>补单订单号</th><th>商户订单信息</th><th>凭证</th><th>提交时间</th><th>处理时间</th><th>状态</th></tr></thead>
          <tbody>
<?php
$pagesize=30;
$pages=ceil($numrows/$pagesize);
$page=isset($_GET['page'])?intval($_GET['page']):1;
$offset=$pagesize*($page - 1);
// echo "SELECT * FROM pre_budandetail  WHERE{$sql} group by tianshibudan_id order by id desc limit $offset,$pagesize";
$rs=$DB->query("SELECT * FROM pre_budandetail  WHERE{$sql} group by tianshibudan_id order by id desc limit $offset,$pagesize");
while($res = $rs->fetch())
{
    //查原来的信息：
    $tianshibudan_id = $res['tianshibudan_id'];
    $r2s=$DB->getRow("SELECT * FROM pre_tianshibudan where id={$tianshibudan_id}");
    //在查明细：
    $r2ss=$DB->getAll("SELECT * FROM pre_budandetail where tianshibudan_id={$tianshibudan_id}");
    $dingdan_imfo = "";
    foreach ($r2ss as $sk=>$vsa){
       $dingdan_imfo .= $vsa['order_sn']."=>".$vsa['money']."<br>";
    }
    
    $dangqian_ip = $res['ip'];
    $notime = date('Y-m-d H:i:s',$r2s['createtime']);
    if($r2s['updatetime']>0){
        $updatetime = date('Y-m-d H:i:s',$r2s['updatetime']);
    }else{
        $updatetime = "等待处理";
    }
  
   $have = true; 
   echo '<tr><td>'.$r2s['apiorder'].'</td><td>'.$dingdan_imfo.'</td><td> <b><img src="../'.$r2s['image'].'" style="width:40px"></b></td><td>'.$notime.'</td><td>'.$updatetime.'</td><td>'.display_status($res['status']).'</td></tr>';
   

}
?>
          </tbody>
        </table>
      </div>
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
