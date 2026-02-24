<?php
/**
 * 订单列表
**/
include("../includes/common.php");
$title='订单列表';
include './head.php';
if($islogin==1){}else exit("<script language='javascript'>window.location.href='./login.php';</script>");

$type_select = '<option value="0">所有支付方式</option>';
$type_select2 = '<option value="0">上游自定义编号</option>';
$rs = $DB->getAll("SELECT * FROM pre_type");
foreach($rs as $row){
	$type_select .= '<option value="'.$row['id'].'">'.$row['showname'].'</option>';
}
$rs2 = $DB->getAll("SELECT topzidingyi FROM pre_channel  group by topzidingyi");
foreach($rs2 as $row2){
    if(!empty($row2['topzidingyi'])){
        	$type_select2 .= '<option value="'.$row2['topzidingyi'].'">'.$row2['topzidingyi'].'</option>';
    }

}
unset($rs);
unset($rs2);
?>
<style>
#orderItem .orderTitle{word-break:keep-all;}
#orderItem .orderContent{word-break:break-all;}
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
  <div class="container-fluid" style="padding-top:70px;">
    <div class="col-md-12 center-block" style="float: none;">

<form onsubmit="return searchOrder()" method="GET" class="form-inline">
  <div class="form-group">
    <label>搜索</label>
	<select name="column" id="changeti" class="form-control" default="<?php echo $_GET['column']?$_GET['column']:'trade_no'?>"><option value="trade_no">订单号</option><option value="out_trade_no">商户订单号</option><option value="api_trade_no">接口订单号</option><option value="terminals">终端渠道</option><option value="name">商品名称</option><option value="money">金额范围</option><option value="domain">网站域名</option><option value="buyer">支付账号</option><option value="ip">支付IP</option><option value="addtime">创建时间</option><option value="endtime">完成时间</option></select>
  </div>
  <div class="form-group">
    <input type="text" class="form-control" name="value" id="sousuoneirong" placeholder="搜索内容" value="<?php echo @$_GET['value']?>">
  </div>
  
  <div class="form-group">
    <label>上游自定义编号</label>
    <select name="topzidingyi" class="form-control" default="<?php echo $_GET['topzidingyi']?$_GET['topzidingyi']:'0'?>">
        <?php echo $type_select2?>
	</select>
  </div>
  <div class="form-group">
    <input type="text" class="form-control" name="uid" style="width: 100px;" placeholder="商户号" value="<?php echo @$_GET['uid']?>">
  </div>
  <div class="form-group">
    <select name="type" class="form-control" default="<?php echo $_GET['type']?$_GET['type']:0?>"><?php echo $type_select?></select>
  </div>
  <div class="form-group">
    <input type="text" class="form-control" name="channel" style="width: 80px;" placeholder="通道ID" value="<?php echo @$_GET['channel']?>">
  </div>
  <div class="form-group">
    <label>支付状态</label>
    <!--手动完成，自动完成，自动通知，手动通知-->
	<select name="order_type" class="form-control" default="<?php echo $_GET['order_type']?$_GET['order_type']:'0'?>">
	    <option value="0">显示全部</option><option value="5">自动完成</option><option value="4">手动完成</option>
	</select>
  </div>
  <div class="form-group">
    <label>通知状态</label>
    <!--手动完成，自动完成，自动通知，手动通知-->
	<select name="orderpp_type" class="form-control" default="<?php echo $_GET['orderpp_type']?$_GET['orderpp_type']:'0'?>">
	     <option value="0">显示全部</option><option value="2">自动回调</option><option value="-1">手动回调</option>
	</select>
  </div>
  <button type="submit" class="btn btn-primary">搜索</button>
  <a href="javascript:searchClear()" class="btn btn-default" title="刷新订单列表"><i class="fa fa-refresh"></i></a>
  <div class="form-group">
	<select id="dstatus" class="form-control"><option value="0">显示全部</option><option value="1">只显示已完成</option><option value="3">只显示已冻结</option><option value="2">只显示已退款</option></select>
  </div>
  <button type="push" class="btn btn-success"  onclick="daochu()">导出</button>
    <button type="push" class="btn btn-success"  onclick="daochuj()">导出商户成率</button>
  <div style="width:300px;height:100px">
      <h4>订单数：<span id="dingdanzongshu" style="color:red"></span></h4>
      <h4>成功数：<span id="chenggongshu" style="color:green"></span></h4>
      <h4>成功总额：<span id="chenggongzonger" style="color:blue"></span></h4>
     
  </div>
   <div id="zheshiya">
        
      </div>
<?php if($DB->getRow("SHOW TABLES LIKE 'pay_order_old'")){echo '<a href="./order_old.php" class="btn btn-default btn-xs">历史订单查询</a>';}?>
</form>

<div id="listTable"></div>
    </div>
  </div>
<a style="display: none;" href="" id="vurl" rel="noreferrer" target="_blank"></a>
<script src="../assets/js/new/layer.js"></script>
<script src="../assets/js/layui/layui.js"></script> 
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

var dstatus = 0;
function listTable(query){
	var url = window.document.location.href.toString();
	var queryString = url.split("?")[1];
	query = query || queryString;
	if(query == 'start' || query == undefined){
		query = '';
		history.replaceState({}, null, './order.php');
	}else if(query != undefined){
		history.replaceState({}, null, './order.php?'+query);
	}
	layer.closeAll();
	var ii = layer.load(2, {shade:[0.1,'#fff']});
	$.ajax({
		type : 'GET',
		url : 'order-table.php?dstatus='+dstatus+'&'+query,
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
	$.ajax({
		type : 'GET',
		url : 'order-table2.php?dstatus='+dstatus+'&'+query,
		dataType : 'json',
		cache : false,
		success : function(data) {
		    console.log(data);
		    $("#dingdanzongshu").text(data.number);
		    $("#chenggongshu").text(data.all_chenggong);
		    $("#chenggongzonger").text(data.all_chenggongjiner);
		    $("#zheshiya").html(data.tongji);
		},
		error:function(data){
			layer.msg('服务器错误');
			return false;
		}
	});
}
function openlink(full_link){ 
	window.open('javascript:window.name;', '<script>location.replace("'+full_link+'")<\/script>');
}
function searchOrder(){
	var column=$("select[name='column']").val();
	var value=$("input[name='value']").val();
	var uid=$("input[name='uid']").val();
	var topzidingyi=$("select[name='topzidingyi']").val();
	var type=$("select[name='type']").val();
	var channel=$("input[name='channel']").val();
	var order_type=$("select[name='order_type']").val();
	var orderpp_type=$("select[name='orderpp_type']").val();
	
	if(value==''){
		listTable('uid='+uid+'&type='+type+'&channel='+channel+'&order_type='+order_type+'&orderpp_type='+orderpp_type+'&topzidingyi='+topzidingyi);
	}else{
		listTable('column='+column+'&value='+value+'&uid='+uid+'&topzidingyi='+topzidingyi+'&type='+type+'&channel='+channel+'&order_type='+order_type+'&orderpp_type='+orderpp_type);
	}
	return false;
}
function searchClear(){
	$("input[name='value']").val('');
	$("input[name='uid']").val('');
	$("select[name='type']").val(0);
	$("input[name='channel']").val('');
	listTable('start');
}

function daochu(){
    var sql = $("#sql_l").val();
    
    var column=$("select[name='column']").val();
	var value=$("input[name='value']").val();
	var uid=$("input[name='uid']").val();
	var type=$("select[name='type']").val();
	var channel=$("input[name='channel']").val();
	//window.location.href = "ajax.php?act=daochu&column=" + column+"&value="+value+"&uid="+uid+"&type="+type+"&channel="+channel;
    window.location.href = "ajax.php?act=daochu&sql=" + sql; 
}
$("#changeti").change(function(obj){
    if(this.value=="addtime"){
        layui.use(function(){
          var laydate = layui.laydate;
             laydate.render({
                elem: '#sousuoneirong',
                type: 'datetime' ,
                range: true
             });

        });
        $("#sousuoneirong").attr('placeholder',"2023-10-30 10:51:00 - 2023-10-30 10:51:00");
        
    }
    if(this.value=="endtime"){
        $("#sousuoneirong").attr('placeholder',"2023-10-29 10:51:00 - 2023-11-29 10:51:00");
        layui.use(function(){
          var laydate = layui.laydate;
             laydate.render({
                elem: '#sousuoneirong',
                type: 'datetime' ,
                range: true
             });

        });
    }
    
})

function operation(){
	var ii = layer.load(2, {shade:[0.1,'#fff']});
	$.ajax({
		type : 'POST',
		url : 'ajax.php?act=operation',
		data : $('#form1').serialize(),
		dataType : 'json',
		success : function(data) {
			layer.close(ii);
			if(data.code == 0){
				listTable();
				layer.alert(data.msg);
			}else{
				layer.alert(data.msg);
			}
		},
		error:function(data){
			layer.msg('请求超时');
			listTable();
		}
	});
	return false;
}
function showOrder(trade_no) {
	var ii = layer.load(2, {shade:[0.1,'#fff']});
	var status = ['<span class="label label-primary">未支付</span>','<span class="label label-success">已支付</span>','<span class="label label-red">已退款</span>'];
	$.ajax({
		type : 'GET',
		url : 'ajax.php?act=order&trade_no='+trade_no,
		dataType : 'json',
		success : function(data) {
			layer.close(ii);
			if(data.code == 0){
				var data = data.data;
				var item = '<table class="table table-condensed table-hover" id="orderItem">';
				item += '<tr><td colspan="6" style="text-align:center" class="orderTitle"><b>订单信息</b></td></tr>';
				item += '<tr class="orderTitle"><td class="info" class="orderTitle">系统订单号</td><td colspan="5" class="orderContent">'+data.trade_no+'</td></tr>';
					item += '<tr class="orderTitle"><td class="info" class="orderTitle">终端信息</td><td colspan="5" class="orderContent">'+data.terminals+'</td></tr>';
				item += '<tr><td class="info" class="orderTitle">商户订单号</td><td colspan="5" class="orderContent">'+data.out_trade_no+'</td></tr>';
				item += '<tr><td class="info" class="orderTitle">接口订单号</td><td colspan="5" class="orderContent">'+data.api_trade_no+'</td></tr>';
				item += '<tr><td class="info">商户ID</td class="orderTitle"><td colspan="5" class="orderContent"><a href="./ulist.php?my=search&column=uid&value='+data.uid+'" target="_blank">'+data.uid+'</a></td>';
				item += '</tr><tr><td class="info" class="orderTitle">支付方式</td><td colspan="5" class="orderContent">'+data.typename+'</td></tr>';
				item += '</tr><tr><td class="info" class="orderTitle">支付通道</td><td colspan="5" class="orderContent">'+data.channelname+'</td></tr>';
				item += '</tr><tr><td class="info" class="orderTitle">商品名称</td><td colspan="5" class="orderContent">'+data.name+'</td></tr>'; 
				item += '</tr><tr><td class="info" class="orderTitle">订单金额</td><td colspan="5" class="orderContent">'+data.money+'</td></tr>';
				item += '</tr><tr><td class="info" class="orderTitle">实际支付金额</td><td colspan="5" class="orderContent">'+data.realmoney+'('+data.randmoney+')</td></tr>';
				item += '</tr><tr><td class="info" class="orderTitle">商户分成金额</td><td colspan="5" class="orderContent">'+data.getmoney+'</td></tr>';
				item += '</tr><tr><td class="info" class="orderTitle">创建时间</td><td colspan="5" class="orderContent">'+data.addtime+'</td></tr>';
				item += '</tr><tr><td class="info" class="orderTitle">完成时间</td><td colspan="5" class="orderContent">'+data.endtime+'</td></tr>';
				item += '</tr><tr><td class="info" class="orderTitle" title="只有在官方通道支付完成后才能显示">支付账号</td><td colspan="5" class="orderContent">'+data.buyer+'</td></tr>';
				// item += '</tr><tr><td class="info" class="orderTitle">网站域名</td><td colspan="5" class="orderContent"><a href="http://'+data.domain+'" target="_blank" rel="noreferrer">'+data.domain+'</a></td></tr>';
				item += '</tr><tr><td class="info" class="orderTitle">支付IP</td><td colspan="5" class="orderContent"><a href="https://m.ip138.com/iplookup.asp?ip='+data.ip+'" target="_blank" rel="noreferrer">'+data.ip+'</a></td></tr>';
				item += '<tr><td class="info" class="orderTitle">订单状态</td><td colspan="5" class="orderContent">'+status[data.status]+'</td></tr>';
				if(data.status>0){
					item += '<tr><td class="info" class="orderTitle">通知状态</td><td colspan="5" class="orderContent">'+(data.notify==0?'<span class="label label-success">通知成功</span>':'<span class="label label-red">通知失败</span>')+(data.sdbd==1?"<span style=\'color:red;\'>[手动]</span>":"")+'</td></tr>';
				}
				item += '<tr><td colspan="6" style="text-align:center" class="orderTitle"><b>订单操作</b></td></tr>';
				item += '<tr><td colspan="6"><a href="javascript:callnotify(\''+data.trade_no+'\')" class="btn btn-xs btn-default">重新通知(异步)</a>&nbsp;<a href="javascript:callreturn(\''+data.trade_no+'\')" class="btn btn-xs btn-default">重新通知(同步)</a></td></tr>';
				item += '</table>';
				var area = [$(window).width() > 480 ? '480px' : '100%'];
				layer.open({
				  type: 1,
				  area: area,
				  title: '订单详细信息',
				  skin: 'layui-layer-rim',
				  content: item
				});
			}else{
				layer.alert(data.msg);
			}
		},
		error:function(data){
			layer.msg('服务器错误');
			return false;
		}
	});
}
<?php 
function isMobile(){    
    $useragent=isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';    
    $useragent_commentsblock=preg_match('|\(.*?\)|',$useragent,$matches)>0?$matches[0]:'';      
    function CheckSubstrs($substrs,$text){    
        foreach($substrs as $substr)    
            if(false!==strpos($text,$substr)){    
                return true;    
            }    
            return false;    
    }  
    $mobile_os_list=array('Google Wireless Transcoder','Windows CE','WindowsCE','Symbian','Android','armv6l','armv5','Mobile','CentOS','mowser','AvantGo','Opera Mobi','J2ME/MIDP','Smartphone','Go.Web','Palm','iPAQ');  
    $mobile_token_list=array('Profile/MIDP','Configuration/CLDC-','160×160','176×220','240×240','240×320','320×240','UP.Browser','UP.Link','SymbianOS','PalmOS','PocketPC','SonyEricsson','Nokia','BlackBerry','Vodafone','BenQ','Novarra-Vision','Iris','NetFront','HTC_','Xda_','SAMSUNG-SGH','Wapaka','DoCoMo','iPhone','iPod');    
                
    $found_mobile=CheckSubstrs($mobile_os_list,$useragent_commentsblock) ||    
              CheckSubstrs($mobile_token_list,$useragent);    
                
    if ($found_mobile){    
        return true;    
    }else{    
        return false;    
    }    
}
?>
function callnotify(trade_no){
	var ii = layer.load(2, {shade:[0.1,'#fff']});
	$.ajax({
		type : 'POST',
		url : 'ajax.php?act=notify',
		data : {trade_no:trade_no},
		dataType : 'json',
		success : function(data) {
			layer.close(ii);
			if(data.code == 0){
				$("#vurl").attr("href",data.url);
				<?php 
					if (isMobile()||$is_ipad)  
	{
		
		?>
		//alert(data.url);
		document.location = data.url;
		return false;
		<?php
	}
else
{
				?>
				document.getElementById("vurl").click();
				<?php 
}
				?>
				listTable();
			}else{
				layer.alert(data.msg);
			}
		},
		error:function(data){
			layer.msg('服务器错误');
		}
	});
	return false;
}
function callreturn(trade_no){
	var ii = layer.load(2, {shade:[0.1,'#fff']});
	$.ajax({
		type : 'POST',
		url : 'ajax.php?act=notify',
		data : {trade_no:trade_no},
		dataType : 'json',
		success : function(data) {
			layer.close(ii);
			if(data.code == 0){
				$("#vurl").attr("href",data.url);
				document.getElementById("vurl").click();
				listTable();
			}else{
				layer.alert(data.msg);
			}
		},
		error:function(data){
			layer.msg('服务器错误');
		}
	});
	return false;
}
function refund(trade_no) {
	var ii = layer.load(2, {shade:[0.1,'#fff']});
	$.ajax({
		type : 'POST',
		url : 'ajax.php?act=getmoney',
		data : {trade_no:trade_no},
		dataType : 'json',
		success : function(data) {
			layer.close(ii);
			if(data.code == 0){
	var confirmobj = layer.confirm('此操作将从该商户扣除订单分成金额，你需要手动退款给购买者，是否确定？', {
		btn: ['确定','取消']
	}, function(){
		var ii = layer.load(2, {shade:[0.1,'#fff']});
		$.ajax({
			type : 'POST',
			url : 'ajax.php?act=refund',
			data : {trade_no:trade_no},
			dataType : 'json',
			success : function(data) {
				layer.close(ii);
				if(data.code == 0){
					layer.alert(data.msg, {icon:1}, function(){ listTable() });
				}else{
					layer.alert(data.msg);
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
			}else{
				layer.alert(data.msg);
			}
		},
		error:function(data){
			layer.msg('服务器错误');
			return false;
		}
	});
}
function apirefund(trade_no) {
	var ii = layer.load(2, {shade:[0.1,'#fff']});
	$.ajax({
		type : 'POST',
		url : 'ajax.php?act=getmoney',
		data : {trade_no:trade_no,api:"1"},
		dataType : 'json',
		success : function(data) {
			layer.close(ii);
			if(data.code == 0){
	layer.prompt({title: 'API退款'+data.money+'元 请输入支付密码', value: '', formType: 1}, function(text, index){
		var ii = layer.load(2, {shade:[0.1,'#fff']});
		$.ajax({
			type : 'POST',
			url : 'ajax.php?act=apirefund',
			data : {trade_no:trade_no,paypwd:text},
			dataType : 'json',
			success : function(data) {
				layer.close(ii);
				if(data.code == 0){
					layer.alert(data.msg, {icon:1}, function(){ listTable() });
				}else{
					layer.alert(data.msg);
				}
			},
			error:function(data){
				layer.msg('服务器错误');
				return false;
			}
		});
	});
			}else{
				layer.alert(data.msg);
			}
		},
		error:function(data){
			layer.msg('服务器错误');
			return false;
		}
	});
}
function freeze(trade_no) {
	var ii = layer.load(2, {shade:[0.1,'#fff']});
	$.ajax({
		type : 'POST',
		url : 'ajax.php?act=freeze',
		data : {trade_no:trade_no},
		dataType : 'json',
		success : function(data) {
			layer.close(ii);
			if(data.code == 0){
				layer.alert(data.msg, {icon:1}, function(){ listTable() });
			}else{
				layer.alert(data.msg);
			}
		},
		error:function(data){
			layer.msg('服务器错误');
			return false;
		}
	});
}
function unfreeze(trade_no) {
	var ii = layer.load(2, {shade:[0.1,'#fff']});
	$.ajax({
		type : 'POST',
		url : 'ajax.php?act=unfreeze',
		data : {trade_no:trade_no},
		dataType : 'json',
		success : function(data) {
			layer.close(ii);
			if(data.code == 0){
				layer.alert(data.msg, {icon:1}, function(){ listTable() });
			}else{
				layer.alert(data.msg);
			}
		},
		error:function(data){
			layer.msg('服务器错误');
			return false;
		}
	});
}
function setStatus(trade_no, status) {
	if(status==5){
		var confirmobj = layer.confirm('你确实要删除此订单吗？', {
			btn: ['确定','取消']
		}, function(){
			setStatusDo(trade_no, status);
		});
	}else{
		setStatusDo(trade_no, status);
	}
}
function setStatusDo(trade_no, status) {
	var ii = layer.load(2, {shade:[0.1,'#fff']});
	$.ajax({
		type : 'get',
		url : 'ajax.php',
		data : 'act=setStatus&trade_no=' + trade_no + '&status=' + status,
		dataType : 'json',
		success : function(ret) {
			layer.close(ii);
			if (ret['code'] != 200) {
				alert(ret['msg'] ? ret['msg'] : '操作失败');
			}
			
			listTable();
		},
		error:function(data){
			layer.msg('服务器错误');
			return false;
		}
	});
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