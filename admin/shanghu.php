<?php
/**
 * 结算列表
**/
include("../includes/common.php");
$title='商户实时成率';
include './head.php';
if($islogin==1){}else exit("<script language='javascript'>window.location.href='./login.php';</script>");
?>
<style>
.form-inline .form-control {
    display: inline-block;
    width: auto;
    vertical-align: middle;
}
.form-inline .form-group {
    display: inline-block;
    margin-bottom: 0;
    vertical-align: middle;
}
</style>
  <div class="container" style="padding-top:70px;">
    <div class="col-md-12 center-block" style="float: none;">
<form onsubmit="return searchSettle()" method="GET" class="form-inline">
  <div class="form-group">
    <label>排序方式</label>
	<select name="column" class="form-control"><option value="1">今日跑量</option><option value="2">昨日跑量</option></select>
  </div>
 
  <button type="submit" class="btn btn-primary">搜索</button>
  <!--<a href="settle.php" class="btn btn-success">批量结算</a>-->
  <a href="javascript:listTable('start')" class="btn btn-default" title="刷新商户成率列表"><i class="fa fa-refresh"></i></a>
</form>



<div id="listTable"></div>
    </div>
  </div>
<script src="../assets/js/new/layer.js"></script>
<script>
var checkflag1 = "false";
function check1(field) {
if (checkflag1 == "false") {
for (i = 0; i < field.length; i++) {
field[i].checked = true;}
checkflag1 = "true";
return "false"; }
else {
for (i = 0; i < field.length; i++) {
field[i].checked = false; }
checkflag1 = "false";
return "true"; }
}

function unselectall1()
{
    if(document.form1.chkAll1.checked){
	document.form1.chkAll1.checked = document.form1.chkAll1.checked&0;
	checkflag1 = "false";
    }
}

function listTable(query){
	var url = window.document.location.href.toString();
	var queryString = url.split("?")[1];
	query = query || queryString;
	if(query == 'start' || query == undefined){
		query = '';
		history.replaceState({}, null, './shanghu.php');
	}else if(query != undefined){
		history.replaceState({}, null, './shanghu.php?'+query);
	}
	layer.closeAll();
	var ii = layer.load(2, {shade:[0.1,'#fff']});
	$.ajax({
		type : 'GET',
		url : 'shanghu-table.php?'+query,
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
function searchSettle(){
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