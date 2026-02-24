<?php
/**
 * 订单列表
**/
include("../includes/common.php");
if($islogin==1){}else exit("<script language='javascript'>window.location.href='./login.php';</script>");


function display_status($status,$notify){
	if($status==1)
		$msg = '<font color=green>已支付</font>';
	elseif($status==2)
		$msg = '<font color=red>已退款</font>';
	elseif($status==3)
		$msg = '<font color=red>已冻结</font>';
	else
		$msg = '<font color=blue>未支付</font>';
	if($notify==0 && $status>0)
		$msg .= '<br/><font color=green>通知成功</font>';
	elseif($status>0)
		$msg .= '<br/><font color=red>通知失败</font>';
	return $msg;
}
function display_operation($status, $trade_no){
	if($status==1)
		$msg = '<li><a href="javascript:setStatus(\''.$trade_no.'\', 0)">改未完成</a></li><li><a href="javascript:apirefund(\''.$trade_no.'\')">API退款</a></li><li><a href="javascript:refund(\''.$trade_no.'\')">手动退款</a></li><li><a href="javascript:freeze(\''.$trade_no.'\')">冻结订单</a></li><li role="separator" class="divider"></li><li><a href="javascript:callnotify(\''.$trade_no.'\')">重新通知</a></li><li><a href="javascript:setStatus(\''.$trade_no.'\', 5)">删除订单</a></li>';
	elseif($status==2)
		$msg = '<li><a href="javascript:setStatus(\''.$trade_no.'\', 0)">改未完成</a></li><li><a href="javascript:setStatus(\''.$trade_no.'\', 1)">改已完成</a></li><li role="separator" class="divider"></li><li><a href="javascript:callnotify(\''.$trade_no.'\')">重新通知</a></li><li><a href="javascript:setStatus(\''.$trade_no.'\', 5)">删除订单</a></li>';
	elseif($status==3)
		$msg = '<li><a href="javascript:unfreeze(\''.$trade_no.'\')">解冻订单</a></li><li role="separator" class="divider"></li><li><a href="javascript:callnotify(\''.$trade_no.'\')">重新通知</a></li><li><a href="javascript:setStatus(\''.$trade_no.'\', 5)">删除订单</a></li>';
	else
		$msg = '<li><a href="javascript:setStatus(\''.$trade_no.'\', 1)">改已完成</a></li><li role="separator" class="divider"></li><li><a href="javascript:callnotify(\''.$trade_no.'\')">重新通知</a></li><li><a href="javascript:setStatus(\''.$trade_no.'\', 5)">删除订单</a></li>';
	return $msg;
}

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

if(isset($_GET['value']) && !empty($_GET['value'])) {
$sql=" `{$_GET['column']}`='{$_GET['value']}'";
if(stristr($_GET['value'],">")!=false)
{
	$arr = explode(">",$_GET['value']);
	$kai = $arr[0];
	$jie = $arr[1];
	$sql=" `{$_GET['column']}`>=".$kai." and   `{$_GET['column']}`<=".$jie."";
	
}
	$sql.=$sqls;
	$numrows=$DB->getColumn("SELECT count(*) from pre_erweima A WHERE{$sql}");
	$con='包含 '.$_GET['value'].' 的共有 <b>'.$numrows.'</b> 条订单';
	$link='&column='.$_GET['column'].'&value='.$_GET['value'].$links;
}else{
	$sql=" 1";
	$sql.=$sqls;
	$numrows=$DB->getColumn("SELECT count(*) from pre_erweima A WHERE{$sql}");
	$con='共有 <b>'.$numrows.'</b> 条订单';
	$link=$links;
}
//echo $sql;
?><?php echo $con?>
<form name="formx" id ="formx" action="erweima_save.php"  method="POST" style="margin-top:6px;">
	<label>价格：</label><input type="text" name="jiage" style="width:50px"></label>&nbsp;&nbsp;
	<label><span style="float:left;">二维码：</span><input type="text"   style="float:left; width:80px;"   id="logo"  class="form-control" name="erweima" value=""><input type="file" name="photo" id="photo" value="" placeholder="" style="float:left">
<input type="button" onclick="postData();" value="上传二维码" name="" style="width:100px;height:30px;"></label>
		<label>备注：<input type="text" name="beizhu"></label>
		<label>
			<input type="submit" name="" value="添加">
		</label>
</form>
	  <form name="form1" id="form1">
	  <div class="table-responsive">


        <table class="table table-striped table-bordered table-vcenter">
          <thead><tr
		  ><th>id</th>
		   <th>价格</th>
		  <th>二维码</th>
		
		  <th>操作</th>
		  </thead>
          <tbody>
<?php
$pagesize=30;
$pages=ceil($numrows/$pagesize);
$page=isset($_GET['page'])?intval($_GET['page']):1;
$offset=$pagesize*($page - 1);

$rs=$DB->query("SELECT * from  pre_erweima WHERE{$sql} order by id  desc limit $offset,$pagesize");
while($res = $rs->fetch())
{
	
    $sdbd=$res['api_trade_no']==-1?"<span style='color:red;'>[手动]</span>":"";
echo '<tr><td><input type="checkbox" name="checkbox[]" id="list1" value="'.$res['id'].'" onClick="unselectall1()"><br/>'.$res['id'].'</td>

<td>'.$res['jiage'].'</td>



<td><input type="text" value="'.$res['jiage']."|".$res['erweima'].'|'.$res['beizhu'].'" style="width:550px"><br><a href="'.$res['erweima'].'" target="_blank"><img style="width:70px; height:70px;" src="'.$res['erweima'].'"></a></td>




<td><div class="btn-group" role="group"><a href="javascript:setStatus(\''.$res['id'].'\', 5)">删除二维码</a></div></td>
</tr>';
}
?>
          </tbody>
        </table>
		<!--
<input name="chkAll1" type="checkbox" id="chkAll1" onClick="this.value=check1(this.form.list1)" value="checkbox">&nbsp;全选&nbsp;
<select name="status"><option selected>操作订单</option><option value="0">改未完成</option><option value="1">改已完成</option><option value="2">冻结订单</option><option value="3">解冻订单</option><option value="4">删除订单</option></select>
<button type="button" onclick="operation()">确定</button>-->
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
?>
<script language="javascript">

function postData(){
    var formData = new FormData();
    formData.append("photo",$("#photo")[0].files[0]);
   // formData.append("service",'App.Passion.UploadFile');
  //  formData.append("token",token);
    $.ajax({
        url:'/upload.php', /*接口域名地址*/
        type:'post',
        data: formData,
        contentType: false,
        processData: false,
		dataType: 'json', 
        success:function(res){
            ;
            if(res.code=="succ"){
				$("#logo").val('/upload/'+res.wenjian);
				$("#logo_img").attr("src",'/upload/'+res.wenjian);
                //alert('成功');
            }else if(res.code=="err"){
                alert('失败');
            }else{
                alert(res.code);
            }
        }
    })
}
</script>
