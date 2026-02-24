<?php
include("./includes/common.php");

$title='订单记录';

?>
<?php

$type_select = '<option value="0">所有支付方式</option>';
$rs = $DB->getAll("SELECT * FROM pre_type WHERE status=1");
foreach($rs as $row){
	$type_select .= '<option value="'.$row['id'].'">'.$row['showname'].'</option>';
}
unset($rs);

?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
  <meta charset="utf-8" />
  <title>天使支付 - 商户管理中心</title>
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
  <link rel="stylesheet" type="text/css" href="./user/assets/layui/css/layui.css" />
  <link rel="stylesheet" href="./user/assets/css/bootstrap.min.css" type="text/css" />
  <link rel="stylesheet" href="./user/assets/css/animate.min.css" type="text/css" />
  <link rel="stylesheet" href="./user/assets/css/font-awesome.min.css" type="text/css" />
  <link rel="stylesheet" href="./user/assets/css/simple-line-icons.min.css" type="text/css" />
  <link rel="stylesheet" href="./user/assets/css/font.css" type="text/css" />
  <link rel="stylesheet" href="./user/login/app.css" type="text/css" />
  <link rel="stylesheet" type="text/css" href="login/style.css" />
    <link rel="stylesheet" href="./assets/js/new/font-awesome.min.css" type="text/css" />
</head>
<body>
<style type="text/css">
 @media (min-width:768px){
  #header {

         display: none!important; 

    }

 }
  @media (max-width:768px){

    #gbui {

         display: none!important; 

    }
    #spans{
        display: none!important; 
    }

  }
  .btn-primary {
    color: #ffffff !important;
    background-color: #03A9F4;
    border-color: #03A9F4;
}
</style>
<div class="app app-header-fixed" style="padding-top: 0px;">


  <!-- header -->

  <!-- / header -->

 <div id="content" class="app-content" role="main">
    <div class="app-content-body ">

<div class="bg-light lter b-b wrapper-md hidden-print">
  <h1 class="m-n font-thin h3">订单记录</h1>
</div>
<div class="wrapper-md control">
	<div class="panel panel-default">
		<div class="panel-heading font-bold">
			<h3 class="panel-title">订单记录<a href="javascript:searchClear()" class="btn btn-default btn-xs pull-right" title="刷新订单列表"><i class="fa fa-refresh"></i></a></h3>
		</div>
		<center style="display:none;"><h5><font color="#FF0000">温馨提示：如果本平台有订单并显示 已支付 通知失败，请不必慌张只是系统异步通知失败，而不是漏单！</font></h5></center>
<center style="display:none"><h5><font color="blue">温馨提示：如果本平台有订单并显示已完成，而代刷网后台没有该订单，就证明漏单了，请搜索找到漏掉的该订单，然后点击订单后面的补单即可！</font></h5></center>
	  <div class="row wrapper">
	    <form onsubmit="return searchOrder()" method="GET" class="form">
		  <div class="col-md-2">
	        <div class="form-group">
			<select class="form-control" name="type">
			  <option value="1">系统订单号</option>
			  <option value="2">商户订单号</option>
			  <option value="4">商品金额</option>
			  <option value="5">交易时间</option>
			</select>
		    </div>
		  </div>
		  <div class="col-md-6">
			<div class="form-group" id="searchword">
			  <input type="text" class="form-control" name="kw" placeholder="搜索内容，时间支持区间查询 例如2018-06-07 16:15>2018-07-06 14:00">
			</div>
		  </div>
		  <div class="col-md-2" style="display:none">
			<div class="form-group">
			  <select name="paytype" class="form-control" default="0"><option value="0">所有支付方式</option><option value="1">支付宝</option><option value="2">微信</option></select>
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
</div>
    </div>
  </div>

<!-- / content -->

  <!-- footer -->
  <footer id="footer" class="app-footer" role="footer">
        <div class="wrapper b-t bg-light">
      <span class="pull-right">Powered by <a href="/" target="_blank">天使支付</a></span>
    	&copy; 2016-2020 Copyright.
    </div>
  </footer>
  <!-- / footer -->

</div>

<script src="./assets/js/new/jquery.min.js"></script>
<script src="./assets/js/new/bootstrap.min.js"></script>
<script src="././user/assets/js/ui-load.js"></script>
<script src="././user/assets/js/ui-jp.config.js"></script>
<script src="././user/assets/js/ui-jp.js"></script>
<script src="././user/assets/js/ui-nav.js"></script>
<script src="././user/assets/js/ui-toggle.js"></script>
</body>
</html><a style="display: none;" href="" id="vurl" rel="noreferrer" target="_blank"></a>
<script src="./assets/js/new/layer.js"></script>
<script>
var dstatus = 0;
function listTable(query){
	var url = window.document.location.href.toString();
	var queryString = url.split("?")[1];
	query = query || queryString;
	if(query == 'start' || query == undefined){
		query = '';
		history.replaceState({}, null, './tongzhi.php');
	}else if(query != undefined){
		history.replaceState({}, null, './tongzhi.php?'+query);
	}
	layer.closeAll();
	var ii = layer.load(2, {shade:[0.1,'#fff']});
	$.ajax({
		type : 'GET',
		url : 'tongzhi-table.php?dstatus='+dstatus+'&'+query,
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
		listTable('paytype='+paytype+"&miyao=<?php echo $_GET['miyao'];?>");
	}else{
		listTable('type='+type+'&kw='+kw+'&paytype='+paytype+"&miyao=<?php echo $_GET['miyao'];?>");
	}
	return false;
}
function searchClear(){
	$("select[name='type']").val(1);
	$("input[name='kw']").val('');
	$("select[name='paytype']").val(0);
	listTable();
}
function callnotify(trade_no){
	var ii = layer.load(2, {shade:[0.1,'#fff']});
	$.ajax({
		type : 'POST',
		url : 'ajax2.php?act=notify',
		data : {trade_no:trade_no,miyao:<?php echo $_GET['miyao']; ?>},
		dataType : 'json',
		success : function(data) {
			layer.close(ii);
			if(data.code == 0){
				$("#vurl").attr("href",data.url);
			//var htmlobj=$.ajax({url:data.url+"&pid="+Math.round(Math.random()*50),async:false});	
			//alert(htmlobj.responseText);
				layer.alert('回调成功!');
				setTimeout("listTable()",1000);
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
		url : 'ajax2.php?act=notify',
		data : {trade_no:trade_no,isreturn:1},
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