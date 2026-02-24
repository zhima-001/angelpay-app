<?php
include("../includes/common.php");
if($islogin2==1){}else exit("<script language='javascript'>window.location.href='./login.php';</script>");
$title='补单记录';
include './head.php';
?>

 <div id="content" class="app-content" role="main">
    <div class="app-content-body ">

<div class="bg-light lter b-b wrapper-md hidden-print">
  <h1 class="m-n font-thin h3">补单记录</h1>
</div>
<div class="wrapper-md control">
<?php if(isset($msg)){?>
<div class="alert alert-info">
	<?php echo $msg?>
</div>
<?php }?>
	<div class="panel panel-default">
		<div class="panel-heading font-补单记录">
			<h3 class="panel-title">订单记录<a href="javascript:searchClear()" class="btn btn-default btn-xs pull-right" title="刷新订单列表"><i class="fa fa-refresh"></i></a></h3>
		</div>
		<center><h5><font color="#FF0000">温馨提示：补单！</font></h5></center>
<center><h5><font color="blue">温馨提示：补单！</font></h5></center>
	  <div class="row wrapper">
	    <form onsubmit="return searchOrder()" method="GET" class="form">
		  <div class="col-md-2">
	        <div class="form-group">
			<select class="form-control" name="type">
			  <option value="1">补单订单号</option>
			</select>
		    </div>
		  </div>
		  <div class="col-md-6">
			<div class="form-group" id="searchword">
			  <input type="text" class="form-control" name="kw" placeholder="搜索内容">
			</div>
		  </div> 
		 
		  <div class="col-md-2">
			 <div class="form-group">
				<button class="btn btn-default" type="submit">搜索</button>
			 </div>
		  </div>
		</form>
      </div>
<div id="listTable"></div>
	</div>
<?php if($DB->getRow("SHOW TABLES LIKE 'pay_order_old'")){echo '<a href="./order_old.php" class="btn btn-default btn-xs">历史订单查询</a>';}?>
</div>
    </div>
  </div>

<?php include 'foot.php';?>
<a style="display: none;" href="" id="vurl" rel="noreferrer" target="_blank"></a>
<script src="../assets/js/new/layer.js"></script>
<script>
var dstatus = 0;
function listTable(query){
	var url = window.document.location.href.toString();
	var queryString = url.split("?")[1];
	query = query || queryString;
	if(query == 'start' || query == undefined){
		query = '';
		history.replaceState({}, null, './budan.php');
	}else if(query != undefined){
		history.replaceState({}, null, './budan.php?'+query);
	}
	layer.closeAll();
	var ii = layer.load(2, {shade:[0.1,'#fff']});
	$.ajax({
		type : 'GET',
		url : 'budan-table.php?dstatus='+dstatus+'&'+query,
		dataType : 'html',
		cache : false,
		success : function(data) {
			layer.close(ii);
			$("#listTable").html(data)
		},
		error:function(data){
			layer.msg('服务器错误');
			return false;
		}
	});
}
function searchOrder(){
	var type=$("select[name='type']").val();
	var kw=$("input[name='kw']").val();
	var paytype=$("select[name='paytype']").val();
	if(kw==''){
		listTable('paytype='+paytype);
	}else{
		listTable('type='+type+'&kw='+kw+'&paytype='+paytype);
	}
	return false;
}
function searchClear(){
	$("select[name='type']").val(1);
	$("input[name='kw']").val('');
	$("select[name='paytype']").val(0);
	listTable('start');
}

$(document).ready(function(){
	var items = $("select[default]");
	for (i = 0; i < items.length; i++) {
		$(items[i]).val($(items[i]).attr("default")||0);
	}
	listTable();
	$("#dstatus").change(function () {
		var val = $(this).val();
		dstatus = val;
		listTable();
	});
})
</script>