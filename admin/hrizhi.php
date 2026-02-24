<?php
/**
 * 登录日志
**/
include("../includes/common.php");
$title='回调日志';
include './head.php';
if($islogin==1){}else exit("<script language='javascript'>window.location.href='./login.php';</script>");
?>
<style>
.heijide{
	 width:600px!important; overflow:hidden;word-wrap:break-word;word-wrap:break-word; word-break:normal;
}
</style>
  <div class="container" style="padding-top:70px; width:100%; max-width:100%!important;">
    <div class="col-md-12 center-block" style="float: none; width:100%;max-width:100%!important;">
<form onsubmit="return searchRecord()" method="GET" class="form-inline">
  <div class="form-group">
    <label>搜索</label>
	<select name="column" class="form-control"><option value="addtime">时间</option>
	<option value="jine">金额</option>

	</select>
  </div>
  <div class="form-group" style="width:300px;">
    <input type="text"  style="width:300px;" class="form-control" name="value" placeholder="搜索内容">
  </div>
  <button type="submit" class="btn btn-primary">搜索</button>
  <a href="javascript:listTable('start')" class="btn btn-default" title="刷新登录日志"><i class="fa fa-refresh"></i></a><br>
  搜索内容，时间支持区间查询 例如2018-06-07 16:15>2018-07-06 14:00<br>
  搜索内容，金额支持区间查询 例如50>60
</form>

<div id="listTable"></div>
    </div>
  </div>
<script src="../assets/js/new/layer.js"></script>
<script>
function listTable(query){
	var url = window.document.location.href.toString();
	var queryString = url.split("?")[1];
	query = query || queryString;
	if(query == 'start' || query == undefined){
		query = '';
		history.replaceState({}, null, './hrizhi.php');
	}else if(query != undefined){
		history.replaceState({}, null, './hrizhi.php?'+query);
	}
	layer.closeAll();
	var ii = layer.load(2, {shade:[0.1,'#fff']});
	$.ajax({
		type : 'GET',
		url : 'hrizhi-table.php?'+query,
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
function searchRecord(){
	var column=$("select[name='column']").val();
	var value=$("input[name='value']").val();
	if(value==''){
		listTable();
	}else{
		listTable('column='+column+'&value='+value);
	}
	return false;
}
$(document).ready(function(){
	listTable();
})
</script>