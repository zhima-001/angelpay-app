<?php
/**
 * 支付通道
**/
include("../includes/common.php");
$title='记账管理';
include './head.php';
if($islogin==1){}else exit("<script language='javascript'>window.location.href='./login.php';</script>");
?>
  <div class="container" style="padding-top:70px;">
    <div class="col-md-12 center-block" style="float: none;">
<?php

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
$sql = "SELECT * FROM pre_jizhang  where 1=1";

if(!empty($_GET['addtime3']) && $_GET['addtime3']!="undefined"){
    
    $time_arr = explode(" - ",$_GET['addtime3']);
    
    $time1= strtotime($time_arr[0]);
    $time2= strtotime($time_arr[1]);
    
    $sql .= " and addtime >='".$time1."' and addtime<='".$time2."'";
    
}
if(!empty($_GET['typelist']) || $_GET['typelist']=="0"){
    $typelist= $_GET['typelist']; 
  
    $sql .= " and typelist=".$typelist;
    
}
if(!empty($_GET['channel_id2'])){

    $channel_id2= $_GET['channel_id2']; 
    $r2s = $DB->getAll("SELECT id FROM pre_channel where topzidingyi ='$channel_id2'"); 
    $arr_change = array();
    foreach ($r2s as $ks=>$sq){
        $arr_change[] = $sq['id'];
    }
    
    $arr_change_str = implode(",",$arr_change);
    $sql .= " and channel_id in (".$arr_change_str.")";
    
}
$sql .= " order by id asc";

$list = $DB->getAll($sql); 

?>
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
							 
								<option value="0">预付Usdt(U)</option>
								<option value="1">投诉扣除(元)</option>
								<option value="2">余额扣除(元)</option>
								<option value="3">预退付(U)</option>
							</select>
						</div>
					</div>
				    
				 
                  
				        
                    <div class="form-group">
				         <label class="col-sm-2 control-label no-padding-right" style="color:red">操作时间</label>
				         <div class="col-sm-8">
                          <div class="layui-inline" id="">
                            <!--<div class="layui-input-inline">
                              <input type="text" autocomplete="off" id="ID-laydate-start-date" name="addtime" class="layui-input" placeholder="创建时间">
                            </div>
                          
                            <div class="layui-input-inline">
                              <input type="text" autocomplete="off" id="ID-laydate-end-date" name="endtime" class="layui-input" placeholder="结束时间">
                            </div>-->
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
				
				
					<div class="form-group">
						<label class="col-sm-2 control-label" style="">上游编号</label>
						<div class="col-sm-10">
							<select name="channel_id" id="channel_id" class="form-control" >
								<option value="0">请选择上游编号</option><?php echo $type_select; ?>
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
<form onsubmit="return searchSettle()" method="GET" class="form-inline">
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
								<option value="1">投诉扣除(元)</option>
								<option value="2">余额扣除(元)</option>
								<option value="3">预退付(U)</option>
						</select>
						
                      </div>
                    <div class="col-sm-4">
    						<label class="col-sm-2 control-label" style="">上游编号</label>
    						<div class="col-sm-10">
    							<select name="channel_id2" id="channel_id2" class="form-control" >
    								<option value="0">请选择上游编号</option><?php echo $type_select2; ?>
    							</select>
    						</div>
    					</div>
  </div>

  <button type="submit" class="btn btn-primary">搜索</button>

</form>
<div class="panel panel-info">
   <div class="panel-heading"><h3 class="panel-title">系统共有 <b><?php echo count($list);?></b> 个账单记录&nbsp;<span class="pull-right"><a href="javascript:addframe()" class="btn btn-default btn-xs"><i class="fa fa-plus"></i>新增记账</a></span></h3></div>
      <div class="table-responsive">
        <table class="table table-striped">
          <thead><tr><th>ID</th><th>操作时间</th><th>操作行为</th><th>操作金额</th><th>上游编号</th><th>预付结余</th><th>统计方式</th><th>统计时间</th><th>创建时间</th><th>详情</th><th>操作</th></tr></thead>
          <tbody>
<?php
$typelist = array("预付Usdt","投诉扣除(元)","余额扣除(元)");
     $typebian = array("+","-");
     $bianrates = array("元","Usdt");
     $tongjis = array("完成时间","创建时间");
foreach($list as $res)
{
     $addtime = date("Y-m-d H:i:s",$res['addtime']);

     $createtime = date("Y-m-d H:i:s",$res['createtime']);
     $data ="";
     if($res['typelist']=="2"){
            $start_time = date('Y-m-d H:i:s',$res['tongjistarttime']);
	        $end_time = date('Y-m-d H:i:s',$res['tongjiendtime']);
	        $apptype = $res['channel_id'];
            $tongjilist = $res['tongjilist'];
            $caozuolist = $res['typelist'];
            $moneys = $res['money'];
            $plugin=$DB->getRow("select `topzidingyi` from `pre_channel` where `id`='{$apptype}' limit 1");

	    $topzidingyi = $plugin['topzidingyi'];
	
	    $channel_info=$DB->getAll("select `id`,`name` from `pre_channel` where `topzidingyi`='{$topzidingyi}'");
	
	    $channel_arr = array();
	    $channel_arr_id = array();
    	foreach($channel_info as $key=>$veal){
	        $channel_arr_id[]=$veal['id'];
		    $channel_arr[$veal['id']]['name']=$veal['name'];
        }
	        $all_channel = implode(",",$channel_arr_id);
	
	//查询所有的订单：
	    if($tongjilist=="0"){
	    //完成时间
	        $sqal ="select money,channel from pre_order where endtime >='".$start_time."' and endtime<='".$end_time."' and channel in (".$all_channel.") and status='1'";
	    }else{
	    //开始时间
	   	    $sqal ="select money,channel from pre_order where addtime >='".$start_time."' and addtime<='".$end_time."' and channel in (".$all_channel.") and status='1'";
	    }
        
    	$order_info=$DB->getAll($sqal);
      
    	$all_money = 0;
    	foreach ($order_info as $keyo=>$ordero){
    	    $channel_arr[$ordero['channel']]['money'] +=$ordero['money'];
    	    $all_money+=$ordero['money'];
    	}

	//差额=合计跑量-余额扣除数
		$select = "";
    	if(count($order_info)>0){
    	    foreach($channel_arr as $key=>$veal){
    		     $select .= '<label><span>'.$veal['name'].':</span><span>'.$veal['money'].'</span></label>'."\r\n";
    		}
    	}else{
    	    $select="";
    	}
  

    

	    $data .=$select;
	    $data .="<br>".'<label><span>合计:</span><span>'.$all_money.'</span></label>&nbsp;'."\r\n"; 

	    $chaer = $all_money-$moneys;
	    $data .= "<br>".'<label><span>差额:</span><span>'.$chaer.'</span></label>&nbsp;'; 
        $detial = $data;
        
        
        $addtime2 = date("Y-m-d H:i:s",$res['tongjistarttime']);
        $endtime2 = date("Y-m-d H:i:s",$res['tongjiendtime']);
        $tongjifangshi = $tongjis[$res['tongjilist']];
        $tongjitime = $addtime2."至".$endtime2;
     }else{
          $detial = "";
           $tongjitime = "";
            $tongjifangshi = "";
     }
    
echo '<tr>
    <td><b>'.$res['id'].'</b></td>
    <td>'.$addtime.'</td>
    <td>'.$typelist[$res['typelist']].'</td>
  
    <td><span style="color:red">'.$typebian[$res['typebian']]."</span>".$res['money']."(".$bianrates[$res['bianrates']].')</td>


    <td>'.$paytype[$res['channel_id']].'</td>
    <td>'.$res['residuemoney'].'(元)</td>
    
    
       <td>'.$tongjifangshi.'</td>
      <td>'.$tongjitime.'</td>
      <td>'.$createtime.'</td>
      <td>'.$detial.'</td>
       
     <td>
    <a class="btn btn-xs btn-info" onclick="editframe('.$res['id'].')">编辑</a>&nbsp;
    <a class="btn btn-xs btn-danger" onclick="delItem('.$res['id'].')">删除</a>&nbsp;
   
    
    </td></tr>';
}
?>
          </tbody>
        </table>
      </div>
	</div>
    </div>
  </div>
<script src="../assets/js/new/layer.js"></script>
<script src="../assets/js/layui/layui.js"></script> 

<script>


var saq ="<?php echo $_GET['typelist']?>";
if(saq>0){
    $("#typelist2").val(saq);
}
var addtime3s ="<?php echo $_GET['addtime3']?>";
if(addtime3s!=''){
    console.log(addtime3s);
   layui.use(function(){
    var laydate = layui.laydate;

     laydate.render({
        elem: '#test12',
        type: 'datetime'
        ,range: true,
        value:addtime3s
    });
   });
}

function searchSettle(){
	var addtime3=$("input[name='addtime3']").val();
    var typelist2=$("select[name='typelist2']").val();
    var channel_id2=$("select[name='channel_id2']").val();
	if(addtime3!='' || typelist2!='' || channel_id2!=''){

			window.location.href='./pay_jizhang.php?addtime3='+addtime3+"&typelist="+typelist2+"&channel_id2="+channel_id2;
	}else{
			window.location.href='./pay_jizhang.php'
	}
	return false;
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
    if(actions=="add"){
        if(ss=="0"){
            //预付款
            $("#typebian").val(0);
            $("#bianrates").val(1);
             $("#tongjifangshiya").hide();
               $("#tongjishijian").hide();
               $("#yufujieyu").show();
        }else if(ss=="1"){
            //投诉
             $("#typebian").val(1);
            $("#bianrates").val(0);
              $("#tongjifangshiya").hide();
                $("#tongjishijian").hide();
                $("#yufujieyu").show();
        }else if(ss=="2"){
            //余额扣除
              $("#typebian").val(1);
            $("#bianrates").val(0);
            $("#tongjifangshiya").show();
             $("#tongjishijian").show();
              $("#yufujieyu").show();
        }else{
            //预退付：
            $("#typebian").val(1);
            $("#bianrates").val(1);
              $("#tongjifangshiya").hide();
                $("#tongjishijian").hide();
                $("#yufujieyu").hide();
        }
    }else{
       if(ss=="0"){
            //预付款
            $("#typebian").val(0);
            $("#bianrates").val(1);
             $("#tongjifangshiya").hide();
               $("#tongjishijian").hide();
               $("#yufujieyu").show();
        }else if(ss=="1"){
            //投诉
             $("#typebian").val(1);
            $("#bianrates").val(0);
              $("#tongjifangshiya").hide();
                $("#tongjishijian").hide();
                $("#yufujieyu").show();
        }else if(ss=="2"){
            //余额扣除
              $("#typebian").val(1);
            $("#bianrates").val(0);
            $("#tongjifangshiya").show();
             $("#tongjishijian").show();
              $("#yufujieyu").show();
        }else{
            //预退付：
            $("#typebian").val(1);
            $("#bianrates").val(1);
              $("#tongjifangshiya").hide();
                $("#tongjishijian").hide();
                $("#yufujieyu").hide();
        }  
    }
   
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

   

</script>
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
	$("#name").val('');
	$("#rate").val('');
	$("#type").val(0);
	$("#plugin").empty();
}
function editframe(id){
	var ii = layer.load(2, {shade:[0.1,'#fff']});

	$.ajax({
		type : 'GET',
		url : 'ajax.php?act=getjizhang&id='+id,
		dataType : 'json',
		success : function(data) {
			layer.close(ii);
			if(data.code == 0){
			    alert(123);
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
	if($("#ID-laydate-start-date").val()==''||$("#ID-laydate-start-date").val()==''){
		layer.alert('账单日期不能为空！');return false;
	}
	if($("#money").val()==0){
		layer.alert('变动金额不能为空！');return false;
	}
// 	if($("#residuemoney").val()==0){
// 		layer.alert('剩余金额不能为空！');return false;
// 	}

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
				  window.location.reload()
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