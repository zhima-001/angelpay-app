<?php
/**
 * 订单列表
**/
include("../includes/common.php");
if($islogin2==1){}else exit("<script language='javascript'>window.location.href='./login.php';</script>");


function display_status($status){
	if($status==0)
		$msg = '<font color=green>等待处理</font>';
	else
		$msg = '<font color=blue>已投诉</font>';
	
	return $msg;
}

$paytype = [];
$paytypes = [];


$sql=" uid=$uid and status='1'";
$links='';




if(isset($_GET['kw']) && !empty($_GET['kw'])) {
	$kw=daddslashes($_GET['kw']);
	if($_GET['type']==1){
		$sql.=" AND `order`='{$kw}'";
	}else{
	    $sql.=" AND `out_trade_no`='{$kw}'";
	}
	$numrows=$DB->getColumn("SELECT count(*) from pre_tousu  WHERE{$sql}   ");
	$con='包含 '.$_GET['kw'].' 的共有 <b>'.$numrows.'</b> 条订单';
	$link='&type='.$_GET['type'].'&kw='.$_GET['kw'].$links;
}else{
	$numrows=$DB->getColumn("SELECT count(*) from pre_tousu  WHERE{$sql} ");
	$con='共有 <b>'.$numrows.'</b> 条订单';
	$link=$links;
}
?> 
	  <div class="table-responsive">
        <table class="table table-striped table-bordered table-vcenter">
          <thead><tr><th>投诉单号</th><th>系统单号</th><th>商户订单号</th><th>投诉凭证</th><th>投诉金额</th><th>创建时间</th><th>状态</th></tr></thead>
          <tbody>
<?php
$pagesize=30;
$pages=ceil($numrows/$pagesize);
$page=isset($_GET['page'])?intval($_GET['page']):1;
$offset=$pagesize*($page - 1);
// echo "SELECT * FROM pre_budandetail  WHERE{$sql} group by tianshibudan_id order by id desc limit $offset,$pagesize";
$rs=$DB->query("SELECT * FROM pre_tousu  WHERE{$sql} order by id desc limit $offset,$pagesize");
while($res = $rs->fetch())
{
    //查原来的信息：

    //在查明细：
     $dingdan_imfo = "";

    
    $dangqian_ip = $res['ip'];
    $notime = date('Y-m-d H:i:s',$r2s['createtime']);
   
     $updatetime = date('Y-m-d H:i:s',$r2s['createtime']);
    
  
   $have = true; 
   echo '<tr><td>'.$res['tousuorder'].'</td><td>'.$res['order'].'</td><td>'.$res['out_trade_no'].'</td><td> <b><img src="../'.$res['tousuimage'].'" style="width:40px"></b></td><td>'.$res['money'].'</td><td>'.$updatetime.'</td><td>'.display_status($res['status']).'</td></tr>';
   

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
