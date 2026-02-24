<?php
/**
 * 订单列表
**/
include("../includes/common.php");
$title='记账管理';
include './head.php';
if($islogin==1){}else exit("<script language='javascript'>window.location.href='./login.php';</script>");

$paytype = [];
$type_select = '';
$type_select2 = '';
$rs = $DB->getAll("SELECT * FROM pre_channel where topzidingyi !='' group by topzidingyi"); 
foreach($rs as $row){
    if(!empty($row['topzidingyi'])){
        $chang = $row['topzidingyi'];
    }else{
        $chang = $row['name'];
    }
	$paytype[$row['id']] = $chang;
	$type_select .= '<option value="'.$row['id'].'">'.$chang.'</option>';
		$type_select2 .= '<option value="'.$chang.'">'.$chang.'</option>';
}
unset($rs);
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
<div class="modal" id="modal-store2" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content animated flipInX">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal"><span
							aria-hidden="true">&times;</span><span
							class="sr-only">Close</span></button>
				<h4 class="modal-title" id="modal-title">调整汇率</h4>
			</div>
			<div class="modal-body">
				<form class="form-horizontal" id="form-store2">
					<input type="hidden" name="rate_id" id="rate_id"/>
				   <input type="hidden" name="jizhangid_id" id="jizhangid_id"/>
				   <input type="hidden" name="zhangdan_id" id="zhangdan_id"/>
				   


				
				<div class="form-group">
						<label class="col-sm-2 control-label no-padding-right">旧的汇率</label>
						<div class="col-sm-10">
							<input type="text" class="form-control" name="old_rate" id="old_rate" placeholder="" readonly="readonly"> 
						</div>
					</div>
			
				
					<div class="form-group">
						<label class="col-sm-2 control-label no-padding-right">新的汇率</label>
						<div class="col-sm-10">
							<input type="text" class="form-control" name="now_rate" id="now_rate" placeholder="新的汇率"> 
						</div>
					</div>
			
				
				</form>
			</div>
			<div class="modal-footer">
			 
				<button type="button" class="btn btn-white" data-dismiss="modal">关闭</button>
				<button type="button" class="btn btn-primary" id="store" onclick="save2()">保存</button>

			</div>
		</div>
	</div>
</div>


<div class="modal" id="modal-store" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content animated flipInX">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal"><span
							aria-hidden="true">&times;</span><span
							class="sr-only">Close</span></button>
				<h4 class="modal-title" id="modal-title">账单修改/添加</h4>
			</div>
			<div class="modal-body">
				<form class="form-horizontal" id="form-store">
					<input type="hidden" name="action" id="action"/>
					<input type="hidden" name="id" id="id"/>
				    
				    <div class="form-group">
						<label class="col-sm-2 control-label" style="color:red">操作【先选择此项】</label>
						<div class="col-sm-10">
							<select name="typelist" id="typelist" class="form-control" onchange="gaigbianya(this)">
							 
								<option value="0" selected="selected">预付Usdt(U)</option>
								<option value="1">投诉扣除(元)</option>
								<option value="2">余额扣除(元)</option>
								<option value="3">预退付(U)</option>
								<option value="4">上游补钱</option>
								<option value="5">实时下发Usdt</option>
							</select>
						</div>
					</div>
				    
				 
                  
				        
                    <div class="form-group">
				         <label class="col-sm-2 control-label no-padding-right" style="color:red">操作时间</label>
				         <div class="col-sm-8">
                          <div class="layui-inline" id="">
                   
                               <input type="text" class="layui-input" name="addtime" id="test10" placeholder="yyyy-MM-dd HH:mm:ss">
                          </div>
                        </div>
                    </div>
                    
				         
                     
                 
					<div class="form-group">
						<label class="col-sm-2 control-label no-padding-right" style="color:red">变动金额</label>
						<div class="col-sm-8">
    							<div class="input-group">
    							    
    							    <div class="input-group">
            							<select name="typebian" id="typebian" class="form-control" disabled="disabled">
            								<option value="0" selected="selected">增加</option>
            								<option value="1">减少</option>
            							</select>
    						        </div>
    						         
    							    <input type="text" class="form-control" name="money" id="money" placeholder="填写变动金额:0.01"/>
    							   <div class="input-group">
            							<select name="bianrates" id="bianrates" class="form-control" disabled="disabled">
            								<option value="0">元</option>
            								<option value="1" selected="selected">U</option>
            							</select>
    						        </div>
						        </div>
						</div>
					</div>
					
					<div class="form-group" style="display:block" id="feilyid" >
						<label class="col-sm-2 control-label no-padding-right"  style="color:red">汇率</label>
						<div class="col-sm-10">
							<input type="text" class="form-control" name="feilv" id="feilv" placeholder="U价一般上浮0.12，如果太高则上报"> 
						</div>
					</div>
				
				
					<div class="form-group">
						<label class="col-sm-2 control-label"  style="color:red">上游编号</label>
						<div class="col-sm-10">
							<select name="channel_id" id="channel_id" class="form-control" >
								<option value="0">请选择上游编号</option>
								<?php echo $type_select; ?>
							</select>
						</div>
					</div>
					
					
					
					 <div class="form-group" style="display:none" id="tongjifangshiya">
						<label class="col-sm-2 control-label" style="color:red">统计方式</label>
						<div class="col-sm-10">
							<select name="tongjilist" id="tongjilist" class="form-control" >
							    
								<option value="0" selected="selected">完成时间</option>
								<option value="1">创建时间</option>
							</select>
						</div>
					</div>
					 <div class="form-group" id="tongjishijian" style="display:none">
				         <label class="col-sm-2 control-label no-padding-right" style="color:red">统计时间</label>
				         <div class="col-sm-8">
                          <div class="layui-inline" id="ID-laydate-rangeLinked1">
                            <!--<div class="layui-input-inline">
                              <input type="text" autocomplete="off" id="ID-laydate-start-date1" name="addtime1" class="layui-input" placeholder="开始时间">
                            </div>
                          
                            <div class="layui-input-inline">
                              <input type="text" autocomplete="off" id="ID-laydate-end-date1" name="endtime1" class="layui-input" placeholder="结束时间">
                            </div>-->
                             <input type="text" class="layui-input" id="test11" name="tongjitime" placeholder="统计时间" lay-key="12">
                          </div>
                        </div>
                    </div>
					<div class="form-group" style="display:block" id="yufujieyu">
						<label class="col-sm-2 control-label no-padding-right" style="color:red">预付结余</label>
						<div class="col-sm-9">
    							<div class="input-group col-sm-10">
   
    							    <input type="text" class="form-control" name="residuemoney" id="residuemoney" placeholder="填写预付结余:0.01默认单位：元"/>
    							  
						        </div>
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-2 control-label no-padding-right">备注</label>
						<div class="col-sm-10">
							<input type="text" class="form-control" name="remakes" id="remakes" placeholder="备注"> 
						</div>
					</div>
			
				
				</form>
			</div>
			<div class="modal-footer">
			 
				<button type="button" class="btn btn-white" data-dismiss="modal">关闭</button>
				<button type="button" class="btn btn-primary" id="store" onclick="save()">保存</button>

			</div>
		</div>
	</div>
</div>
  <div class="container-fluid" style="padding-top:70px;">
    <div class="col-md-12 center-block" style="float: none;">

<form onsubmit="return searchOrder()" method="GET" class="form-inline">
  <div class="form-group">
    <label>账单日期:</label>
	
  </div>
  <div class="form-group">
    <!--<input type="text" class="form-control" name="value" placeholder="搜索内容">-->
    <!--<div class="form-group">-->
				         <!--<label class="col-sm-2 control-label no-padding-right" style="color:red">账单日期</label>-->
				        <div class="col-sm-4">
                          <div class="layui-inline" id="ID-laydate-rangeLinked2">
                            <!--<div class="layui-input-inline">
                              <input type="text" autocomplete="off" id="ID-laydate-start-date2" name="addtime2" class="layui-input" placeholder="创建时间">
                            </div>
                          
                            <div class="layui-input-inline">
                              <input type="text" autocomplete="off" id="ID-laydate-end-date2" name="endtime2" class="layui-input" placeholder="结束时间">
                            </div>-->
                            <input type="text" class="layui-input" name="addtime3" id="test12" placeholder="yyyy-MM-dd HH:mm:ss">
                          </div>
                        </div>
                    <!--</div>-->
                      <div class="col-sm-4">
                        <label>操作行为:</label>
                    	<select name="typelist2" id="typelist2" class="form-control">	
                    	        <option value="">全部</option>
                    	        <option value="0">预付Usdt(U)</option>
                    	        <option value="5">实时下发usdt(U)</option>
                    	        
								<option value="1">投诉扣除(元)</option>
								<option value="2">余额扣除(元)</option>
								<option value="3">预退付(U)</option>
								<option value="4">上游补钱</option>
								
						</select>
						
                      </div>
                    <div class="col-sm-4">
    						<label class="col-sm-2 control-label" style="">上游编号</label>
    						<div class="col-sm-10">
    							<select name="channel_id2" id="channel_id2" class="form-control" >
    								<option value="0">请选择上游编号</option>
    								<option value="10001">空上游通道编号</option>
    								<?php echo $type_select2; ?>
    							</select>
    						</div>
    					</div>
    					<!-- <div class="col-sm-3">-->
    					<!--	<label class="col-sm-2 control-label" style="">统计方式</label>-->
    					<!--	<div class="col-sm-10">-->
    					<!--		<select name="tongjifangshis" id="tongjifangshis" class="form-control" >-->
    					<!--			<option value="">请选择统计方式</option>-->
    					<!--			<option value="0">完成时间</option>-->
    					<!--			<option value="1">创建时间</option>-->
    					<!--		</select>-->
    					<!--	</div>-->
    					<!--</div>-->
  </div>

  <button type="submit" class="btn btn-primary">搜索</button>
  <button type="button" class="btn btn-success" onclick="chongxinhe()">重新核算</button>
</form>
<div class="panel-heading" style=" text-align: right;"><h3 class="panel-title">
    <a href="javascript:addframe()" class="btn btn-default btn-xs"><i class="fa fa-plus"></i>新增记账</a></span></h3></div>

<div id="listTable"></div>
    </div>
  </div>
<a style="display: none;" href="" id="vurl" rel="noreferrer" target="_blank"></a>

<script src="../assets/js/new/layer.js"></script>
<script src="../assets/js/layui/layui.js"></script> 
<script>

function chongxinhe(){
   var channel_id =$("select[name='channel_id2']").val();
   if(channel_id =="0"){
       layer.msg('请选择一个需要重新核算的上游编号！');
       return false;
   }
   $.ajax({
		type : 'POST',
		url : 'ajax.php?act=chongxinhesuan',
		data : {"channel_id":channel_id},
		dataType : 'json',
		success : function(data) {
		
			if(data.code == 0){
			
				layer.alert(data.msg);
			}else{
				layer.alert(data.msg);
			}
			listTable();
		},
		error:function(data){
			layer.msg('请求超时');
		
		}
	});
}

function operation(){
	var ii = layer.load(2, {shade:[0.1,'#fff']});
	$.ajax({
		type : 'POST',
		url : 'ajax.php?act=operationshanchu',
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

function xiugairate (old_rate,rate_id,jizhangid_id,zhangdan_id){
    
     $.ajax({
		type : 'POST',
		url : 'ajax.php?act=chajizhangshijian',
		data : {"zhangdan_id":zhangdan_id},
		dataType : 'json',
		success : function(data) {
		
			if(data.code != 0){
			
				layer.alert(data.msg);
				return false;
			}else{
			        $("#modal-store2").modal('show');
                	$("#modal-title").html("修改单调汇率");
                	$("#rate_id").val(rate_id);
                	$("#jizhangid_id").val(jizhangid_id);
                	$("#zhangdan_id").val(zhangdan_id);
                	$("#old_rate").val(old_rate);
			}
		
		},
		error:function(data){
			layer.msg('请求超时');
		
		}
	});
    
  

    
}


function gaigbianya(that){
    var ss = $(that).val();
    /*
预付：+  U
投诉：- 元
余款扣除：- 元
预退付： + U
    */
    console.log(ss);
    var actions = $("#action").val();
    //if(actions=="add"){
         if(ss=="0"){
            //预付款
            $("#typebian").val(0);
            $("#bianrates").val(1);
             $("#tongjifangshiya").hide();
               $("#tongjishijian").hide();
               $("#yufujieyu").show();
               $("#feilyid").show();
        }else if(ss=="0"){
            //预付款
            $("#typebian").val(0);
            $("#bianrates").val(1);
             $("#tongjifangshiya").hide();
               $("#tongjishijian").hide();
               $("#yufujieyu").show();
               $("#feilyid").show();
        }else if(ss=="1"){
            //投诉
             $("#typebian").val(0);
            $("#bianrates").val(0);
              $("#tongjifangshiya").hide();
                $("#tongjishijian").hide();
                $("#yufujieyu").show();
                 $("#feilyid").hide();
        }else if(ss=="2"){
            //余额扣除
              $("#typebian").val(1);
            $("#bianrates").val(0);
            $("#tongjifangshiya").show();
             $("#tongjishijian").show();
              $("#yufujieyu").show();
              $("#feilyid").hide();
        }else if(ss=="3"){
            //预退付：
            $("#typebian").val(1);
            $("#bianrates").val(1);
              $("#tongjifangshiya").hide();
                $("#tongjishijian").hide();
                $("#yufujieyu").show();
                $("#feilyid").show();
        }else if(ss=="4"){
            //上游补钱：
             $("#typebian").val(1);
            $("#bianrates").val(0);
              $("#tongjifangshiya").hide();
                $("#tongjishijian").hide();
                $("#yufujieyu").show();
                 $("#feilyid").hide();
        }else if(ss=="5"){
            //实时下发usdt
            $("#typebian").val(0);
            $("#bianrates").val(1);
             $("#tongjifangshiya").hide();
               $("#tongjishijian").hide();
               $("#yufujieyu").show();
               $("#feilyid").show();
        }
    //}
   
}

layui.use(function(){
  var laydate = layui.laydate;
  
   laydate.render({
    elem: '#test10',
    type: 'datetime'
  });
  laydate.render({
      elem: '#test11'
      ,type: 'datetime'
      ,range: true
    }); 
     laydate.render({
    elem: '#test12',
    type: 'datetime'
     ,range: true
  });
    

    
    
});

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
		history.replaceState({}, null, './jizhang.php');
	}else if(query != undefined){
		history.replaceState({}, null, './jizhang.php?'+query);
	}
	layer.closeAll();
	var ii = layer.load(2, {shade:[0.1,'#fff']});
	$.ajax({
		type : 'GET',
		url : 'jizhang-table.php?dstatus='+dstatus+'&'+query,
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
function openlink(full_link){ 
	window.open('javascript:window.name;', '<script>location.replace("'+full_link+'")<\/script>');
}
function searchOrder(){
	var addtime3=$("input[name='addtime3']").val();
    var typelist2=$("select[name='typelist2']").val();
    var channel_id2=$("select[name='channel_id2']").val();
    // var tongjifangshis = $("select[name='tongjifangshis']").val();
	
    if(addtime3!='' || typelist2!='' || channel_id2!='' ){
		listTable('addtime3='+addtime3+'&typelist='+typelist2+'&channel_id2='+channel_id2);
	}else{
			listTable('addtime3='+addtime3+'&typelist='+typelist2+'&channel_id2='+channel_id2);
	}
	return false;
}
function searchClear(){
	$("input[name='addtime3']").val('');
	$("select[name='channel_id2']").val('');
	$("select[name='typelist2']").val(0);

	listTable('start');
}





$(document).ready(function(){

	listTable();

})
</script>
 <script src="../assets/js/new/layer.js"></script>
     <script src="../assets/js/layui/layui.js"></script> 
	 <script>
function editInfo(id){
	var ii = layer.load(2, {shade:[0.1,'#fff']});
	$.ajax({
		type : 'GET',
		url : 'ajax.php?act=jizhangInfo&id='+id,
		dataType : 'json',
		success : function(data) {
			layer.close(ii);
			if(data.code == 0){
				var area = [$(window).width() > 520 ? '520px' : '100%', ';max-height:100%'];
				layer.open({
				  type: 1,
				  area: area,
				  title: '账单详情',
				  skin: 'layui-layer-rim',
				  content: data.data
				});
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
function addframe(){
	$("#modal-store").modal('show');
	$("#modal-title").html("新增账单记录");
	$("#action").val("add");
	$("#id").val('');
	$("#typelist").val(0);
	$("#test10").val('');
    $("#test11").val('');
	$("#typebian").val('0');
	$("#money").val(0);
	$("#bianrates").val('1');
	$("#channel_id").val('0');
	$("#tongjilist").val('0');
    $("#tongjishijian").val('');
	$("#residuemoney").val(0);
	$("#remakes").val('');	
	$("#feilyid").show();

				 
}
function editframe(id){
	var ii2 = layer.load(2, {shade:[0.1,'#fff']});
	$.ajax({
		type : 'GET',
		url : 'ajax.php?act=getjizhang&id='+id,
		dataType : 'json',
		success : function(data) {
			layer.close(ii2);
			if(data.code == 0){
				$("#modal-store").modal('show');
				$("#modal-title").html("修改账单信息");
				$("#action").val("edit");
				$("#id").val(data.data.id);
			
				var addtime =data.data.addtime;
			    var tongjistarttime =data.data.tongjistarttime;
				layui.use(function(){
                  var laydate = layui.laydate;
                         laydate.render({
                                elem: '#test10',
                                type: 'datetime',
                                value:addtime
                              });
                              laydate.render({
                                  elem: '#test11'
                                  ,type: 'datetime'
                                  ,range: true
                                  ,value:tongjistarttime
                                }); 
                                                    
                        });
    			$("#typebian").val(data.data.typebian);
				$("#money").val(data.data.money);
				$("#bianrates").val(data.data.bianrates);
				$("#typelist").val(data.data.typelist);
			
				$("#residuemoney").val(data.data.residuemoney);
	
				$("#shengrates").val(data.data.shengrates);
				$("#remakes").val(data.data.remakes);
				$("#channel_id").val(data.data.channel_id);
	            $("#tongjilist").val(data.data.tongjilist);
	            $("#feilv").val(data.data.feilv);
	            
	            var sss = $("#typelist").val();
	
				 if(sss=="0"){
                        //预付款
                        $("#typebian").val(0);
                        $("#bianrates").val(1);
                         $("#tongjifangshiya").hide();
                           $("#tongjishijian").hide();
                           $("#yufujieyu").show();
                           $("#feilyid").show();
                    }else if(sss=="1"){
                        //投诉
                         $("#typebian").val(0);
                        $("#bianrates").val(0);
                          $("#tongjifangshiya").hide();
                            $("#tongjishijian").hide();
                            $("#yufujieyu").show();
                             $("#feilyid").hide();
                    }else if(sss=="2"){
                        //余额扣除
                          $("#typebian").val(1);
                        $("#bianrates").val(0);
                        $("#tongjifangshiya").show();
                         $("#tongjishijian").show();
                          $("#yufujieyu").show();
                              $("#feilyid").hide();
                    }else if(sss=="3"){
                        //预退付：
                        $("#typebian").val(1);
                        $("#bianrates").val(1);
                          $("#tongjifangshiya").hide();
                            $("#tongjishijian").hide();
                            $("#yufujieyu").show();
                                // $("#feilyid").hide();
                    }else if(sss=="4"){
                        //投诉
                         $("#typebian").val(0);
                        $("#bianrates").val(0);
                          $("#tongjifangshiya").hide();
                            $("#tongjishijian").hide();
                            $("#yufujieyu").show();
                             $("#feilyid").hide();
                    }else if(sss=="5"){
                        //预付款
                        $("#typebian").val(0);
                        $("#bianrates").val(1);
                         $("#tongjifangshiya").hide();
                           $("#tongjishijian").hide();
                           $("#yufujieyu").show();
                           $("#feilyid").show();
                    }
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
function save(){
	if($("#test10").val()==''){
		layer.alert('账单日期不能为空！');return false;
	}
	if($("#money").val()==0){
		layer.alert('变动金额不能为空！');return false;
	}
	if($("#typelist").val()==0 || $("#typelist").val()==3){
	    if($("#feilv").val() <=0){
	        layer.alert('请填写汇率！');return false;
	    }
	
	}
	if (!$.isNumeric($("#residuemoney").val())) {

		layer.alert('预付结余不能为空！');return false;
	}
	
	if($("#channel_id").val()==0){
	    layer.alert('请选择上游编号！');return false;
	   
	}
	
	

	var ii = layer.load(2, {shade:[0.1,'#fff']});
	$.ajax({
		type : 'POST',
		url : 'ajax.php?act=savejizhang',
		data : $("#form-store").serialize(),
		dataType : 'json',
		success : function(data) {
			layer.close(ii);
			if(data.code == 0){
			    
				layer.alert(data.msg,{
					icon: 1,
					closeBtn: false
				}, function(){
				//   window.location.reload()
				  layer.close(ii);
				  $("#modal-store").modal('hide');
        		    listTable();
				});
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
function save2(){

	if($("#now_rate").val()<=0){
		layer.alert('新的汇率不能为空！');return false;
	}

	var ii = layer.load(2, {shade:[0.1,'#fff']});
	$.ajax({
		type : 'POST',
		url : 'ajax.php?act=savejizhang2',
		data : $("#form-store2").serialize(),
		dataType : 'json',
		success : function(data) {
			layer.close(ii);
			if(data.code == 0){
				layer.alert(data.msg,{
					icon: 1,
					closeBtn: false
				}, function(){
				     layer.close(ii);
				     
				    $("#modal-store2").modal('hide');
        		    listTable();
				});
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
function delItem(id) {
	var confirmobj = layer.confirm('你确实要删除此账单吗？', {
	  btn: ['确定','取消']
	}, function(){
	  $.ajax({
		type : 'GET',
		url : 'ajax.php?act=deljizhang&id='+id,
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

</script>