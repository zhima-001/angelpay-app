<?php
/**
 * 结算列表
**/
include("../includes/common.php");
$title='投诉列表';
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

<div class="modal" id="modal-store" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content animated flipInX">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal"><span
							aria-hidden="true">&times;</span><span
							class="sr-only">Close</span></button>
				<h4 class="modal-title" id="modal-title">投诉订单修改/添加</h4>
			</div>
			<div class="modal-body">
				<form class="form-horizontal" id="form-store">
					<input type="hidden" name="action" id="action"/>
	                <input type="hidden" name="id" id="id"/>
	                <input type="hidden" class="form-control" name="out_trade_no" id="out_trade_no" placeholder="商户订单号"> 
					<div class="form-group">
						<label class="col-sm-2 control-label no-padding-right" style="color:red">投诉凭证</label>
						<input type="hidden" class="form-control" name="tousuimage" id="tousuimage" placeholder="投诉凭证" required="required"> 
						

						<div class="col-sm-10">
				<input type="file" name="photo" id="photo" value="" placeholder="" style="float:left" onchange="previewImage();">
							
						</div>
					</div>
					
				
					<div class="form-group">
						<label class="col-sm-2 control-label no-padding-right">系统订单号</label>
						<div class="col-sm-10">
							<input type="text" class="form-control" name="order" id="order" placeholder="系统订单号" onchange="huoqudingdan();"> 
						</div>
					</div>
				<!--<div class="form-group">-->
				<!--		<label class="col-sm-2 control-label no-padding-right">商户订单号</label>-->
				<!--		<div class="col-sm-10">-->
							
					<!--	</div>-->
					<!--</div>-->
						<div class="form-group">
						<label class="col-sm-2 control-label no-padding-right">凭证上的金额</label>
						<div class="col-sm-10">
							<input type="text" class="form-control" name="money" id="money" placeholder="凭证上的金额"> 
						</div>
					</div>
					
					</div>
				</form>
			</div>
			<div class="modal-footer">
			 
				<button type="button" class="btn btn-white" data-dismiss="modal">关闭</button>
				<button type="button" class="btn btn-primary" id="store" onclick="save123()">保存</button>

			</div>
		</div>
	</div>
</div>


<form onsubmit="return searchSettle()" method="GET" class="form-inline">
  <div class="form-group">
    <label>搜索</label>
	<select name="column" class="form-control"><option value="order">系统订单号</option><option value="out_trade_no">商户订单号</option></select>
  </div>
  <div class="form-group">
    <input type="text" class="form-control" name="value" placeholder="搜索内容">
  </div>
  <div class="form-group">
    <label>通道名称</label>
    <input type="text" class="form-control" name="channel" style="width: 100px;" placeholder="通道名称" value="<?php echo @$_GET['channel']?>">
  </div>
  <div class="form-group">
       <label>支付方式</label>
	<select name="paytype" class="form-control" default="<?php echo $_GET['paytype']?$_GET['paytype']:'0'?>">
	    <option value="0">显示全部</option><option value="1" <?php echo $_GET['paytype']=="1"?"selected":"" ?> >支付宝</option><option value="2" <?php echo $_GET['paytype']=="2"?"selected":"" ?>>微信</option>
	</select>  
	</div>

  <div class="form-group">
    <label>处理状态</label>
    <!--手动完成，自动完成，自动通知，手动通知-->
	<select name="status" class="form-control" default="<?php echo $_GET['status']?$_GET['status']:''?>">
	    <option value="">显示全部</option><option value="0" <?php echo $_GET['status']=="0"?"selected":"" ?>>待扣除</option><option value="1" <?php echo $_GET['status']=="1"?"selected":"" ?>>已扣除</option>
	</select>
  </div>
  <div class="form-group">
    <label>操作人</label>
    <input type="text" class="form-control" name="admin" style="width: 100px;" placeholder="操作人" value="<?php echo @$_GET['admin']?>">
  </div>
  
  <button type="submit" class="btn btn-primary">搜索</button>&nbsp;<a href="javascript:addframe()" class="btn btn-success">添加投诉</a>
  <!--<a href="settle.php" class="btn btn-success">批量结算</a>-->
  <a href="javascript:listTable('start')" class="btn btn-default" title="刷新投诉列表"><i class="fa fa-refresh"></i></a>
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

function huoqudingdan(){
    var order_info=$("#order").val();
    $.ajax({
		type : 'POST',
		url : 'ajax.php?act=huoqudingdan&order_info='+order_info,
		data:{'order_info':order_info},
		dataType : 'json',
		success : function(data) {
			if(data.code != 0){
			
			}else{
			    var out_trade_no = data.info.out_trade_no;
			    $("#out_trade_no").val(out_trade_no);
			    var money = data.info.money;
			    $("#money").val(money);
			}
		},
		error:function(data){
			layer.msg('服务器错误');
			return false;
		}
	  });
}

 function previewImage() {
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
				$("#tousuimage").val('/upload/'+res.wenjian);
			
                //alert('成功');
            }else if(res.code=="err"){
                alert('失败');
            }else{
                alert(res.code);
            }
        }
    })
}
function addframe(){
	$("#modal-store").modal('show');
	$("#modal-title").html("新增投诉");
	$("#action").val("add");
	$("#id").val('');
	$("#name").val('');
	$("#rate").val('');
	$("#type").val(0);
	$("#plugin").empty();
}

function delItem(id,status) {
	if(status=="1"){
		layer.msg('已经通知商户的投诉订单不可删除');
		return false;
	}
	var confirmobj = layer.confirm('你确实要删除此投诉订单吗？', {
	  btn: ['确定','取消']
	}, function(){
	  $.ajax({
		type : 'GET',
		url : 'ajax.php?act=deltousu&id='+id,
		dataType : 'json',
		success : function(data) {
			if(data.code == 0){
				window.location.reload()
			}else{
				layer.alert(data.msg, {icon: 2});
			}
		},
		error:function(data){
			layer.msg('服务器错误');
			return false;
		}
	  });
	}, function(){
	  layer.close(confirmobj);
	});
}

function unselectall1()
{
    if(document.form1.chkAll1.checked){
	document.form1.chkAll1.checked = document.form1.chkAll1.checked&0;
	checkflag1 = "false";
    }
}
function editframe(id,status){
    if(status=="1"){
		layer.msg('已经通知商户的投诉订单不可修改！');
		return false;
	}
	var ii = layer.load(2, {shade:[0.1,'#fff']});
	$.ajax({
		type : 'GET',
		url : 'ajax.php?act=gettousu&id='+id,
		dataType : 'json',
		success : function(data) {
			layer.close(ii);
			if(data.code == 0){
			    var data = data.info;
				$("#modal-store").modal('show');
				$("#modal-title").html("修改投诉订单");
				$("#action").val("edit");
				$("#id").val(data.id);
				$("#tousuimage").val(data.tousuimage);
				$("#order").val(data.order);
				$("#out_trade_no").val(data.out_trade_no);
				$("#money").val(data.money);
			
			}else{
				layer.alert(data.msg, {icon: 2})
			}
		},
		error:function(data){
			layer.msg('服务器错误');
			return false;
		}
	});
}
function listTable(query){
	var url = window.document.location.href.toString();
	var queryString = url.split("?")[1];
	query = query || queryString;
	if(query == 'start' || query == undefined){
		query = '';
		history.replaceState({}, null, './tousu.php');
	}else if(query != undefined){
		history.replaceState({}, null, './tousu.php?'+query);
	}
	layer.closeAll();
	var ii = layer.load(2, {shade:[0.1,'#fff']});
	$.ajax({
		type : 'GET',
		url : 'tousu-table.php?'+query,
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
	var order=$("select[name='order']").val();
	var out_trade_no=$("input[name='out_trade_no']").val();
	if(value==''){
		listTable();
	}else{
		listTable('order='+order+'&out_trade_no='+out_trade_no);
	}
	return false;
}
function tuisong(id){
    $.ajax({
		type : "POST",
		url : "ajax.php?act=tuisong_save",
		data : {id:id},
		dataType : 'json',
		success : function(data) {
// 			layer.close(ii);
			if(data.code == 0){
				layer.msg('保存成功！');
				listTable();
			}else{
				layer.alert(data.msg);
			}
		
		} 
	});
}

function save123() {
    	var ii22 = layer.load(2, {shade:[0.1,'#fff']}); 
    var id=$("#id").val();
	var order=$("#order").val();
	var out_trade_no=$("#out_trade_no").val();
	var money=$("#money").val();
	var tousuimage = $("#tousuimage").val();
	if(tousuimage ==''){
	    layer.alert('投诉凭证不能为空！');return false;
	}

	if(order=='' || out_trade_no=='' || money==''){layer.alert('请确保每项不能为空！');return false;} 
	$('#save').val('Loading');

	$.ajax({
		type : "POST",
		url : "ajax.php?act=tousu_save",
		data : {id:id,order:order,out_trade_no:out_trade_no,money:money,tousuimage:tousuimage},
		dataType : 'json',
		success : function(data) {
			layer.close(ii22);
			if(data.code == 0){
			    
				layer.msg(data.msg);
				listTable();
				 window.location.reload()
			}else{
				layer.alert(data.msg);
			}
		
		} 
	});
}
$(document).ready(function(){
	listTable();
})
</script>