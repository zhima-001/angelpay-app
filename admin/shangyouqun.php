<?php
/**
 * 结算列表
**/
include("../includes/common.php");
$title='上游群自动回复管理';
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
.containerss {
    padding-top: 70px;
}
.center-block {
    float: none;
    margin: 0 auto;
}
.modal-content {
    padding: 20px;
    border-radius: 10px;
}
.modal-header, .modal-footer {
    border-bottom: none;
    border-top: none;
}
.table {
    width: 100%;
    margin-top: 20px;
}
.table th, .table td {
    padding: 8px;
    text-align: left;
    border: 1px solid #ddd;
}
.table th {
    background-color: #f8f8f8;
}
.btn {
    margin: 5px 0;
}
.or-section, .and-section {
    margin-top: 10px;
    display: flex;
    align-items: center;
}
.or-section input, .and-section input {
    flex-grow: 1;
    margin-right: 10px;
}
#conditions-container {
    max-width: 100%;
    overflow-x: auto;
}
</style>
<div class="container">
    <div class="col-md-12 center-block">

        <div class="modal" id="modal-store" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content animated flipInX">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal"><span
                                aria-hidden="true">&times;</span><span
                                class="sr-only">Close</span></button>
                        <h4 class="modal-title" id="modal-title">上游群自动回复管理修改/添加</h4>
                    </div>
                    <div class="modal-body">
                        <form class="form-horizontal" id="form-store">
                            <span>上游群自动回复管理</span>
                            <br>
                            <button id="add-or" class="btn btn-primary">添加或</button>
                            <div id="conditions-container" class="containerss"></div>
                            <label for="reply-content">回复内容:</label>
                            <!--<input type="text" id="reply-content" class="form-control">-->
                            <textarea id="reply-content" class="form-control" style="height: 200px; "></textarea>

                            <input type="hidden" id="rule-id" name="id" value="">
                            	<button type="button" class="btn btn-white" data-dismiss="modal">关闭</button>
    				            <button type="button" class="btn btn-primary" id="store" onclick="save123()">保存</button>
                        </form>
                    </div>
                </div>
                
            </div>
            
        </div>

        <form onsubmit="return searchSettle()" method="GET" class="form-inline">
            <div class="form-group">
                <label>搜索</label>
                <select name="column" class="form-control">
                    <option value="order">系统订单号</option>
                    <option value="out_trade_no">商户订单号</option>
                </select>
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
                    <option value="0">显示全部</option>
                    <option value="1" <?php echo $_GET['paytype']=="1"?"selected":"" ?>>支付宝</option>
                    <option value="2" <?php echo $_GET['paytype']=="2"?"selected":"" ?>>微信</option>
                </select>
            </div>
            <div class="form-group">
                <label>处理状态</label>
                <select name="status" class="form-control" default="<?php echo $_GET['status']?$_GET['status']:''?>">
                    <option value="">显示全部</option>
                    <option value="0" <?php echo $_GET['status']=="0"?"selected":"" ?>>待扣除</option>
                    <option value="1" <?php echo $_GET['status']=="1"?"selected":"" ?>>已扣除</option>
                </select>
            </div>
            <div class="form-group">
                <label>操作人</label>
                <input type="text" class="form-control" name="admin" style="width: 100px;" placeholder="操作人" value="<?php echo @$_GET['admin']?>">
            </div>
            <button type="submit" class="btn btn-primary">搜索</button>
            <a href="javascript:addframe()" class="btn btn-success">添加关键字监控</a>
            <a href="javascript:listTable('start')" class="btn btn-default" title="刷新投诉列表"><i class="fa fa-refresh"></i></a>
        </form>

        <div id="listTable"></div>
    </div>
</div>
<script src="../assets/js/new/layer.js"></script>
<script>
let ruleCount = 0;

document.getElementById('add-or').addEventListener('click', function(event) {
    event.preventDefault(); // 阻止按钮默认行为

    const container = document.getElementById('conditions-container');
    const orSection = document.createElement('div');
    orSection.className = 'or-section';
    
    const orText = document.createElement('span');
    orText.textContent = '或';
    orSection.appendChild(orText);
    
    const input = document.createElement('input');
    input.className = 'form-control';
    orSection.appendChild(input);

    const addAndButton = document.createElement('button');
    addAndButton.textContent = '+';
    addAndButton.className = 'btn btn-secondary';
    orSection.appendChild(addAndButton);
    
    const andSection = document.createElement('div');
    andSection.className = 'and-section';
    orSection.appendChild(andSection);
    
    addAndButton.addEventListener('click', function(event) {
        event.preventDefault(); // 阻止按钮默认行为
        const andText = document.createElement('span');
        andText.textContent = '且';
        andSection.appendChild(andText);

        const andInput = document.createElement('input');
        andInput.className = 'form-control';
        andSection.appendChild(andInput);
    });
    
    container.appendChild(orSection);
});


function deleteRule(button) {
    const row = button.parentNode.parentNode;
    row.parentNode.removeChild(row);
}
</script>

<script>
var checkflag1 = "false";
function check1(field) {
    if (checkflag1 == "false") {
        for (i = 0; i < field.length; i++) {
            field[i].checked = true;
        }
        checkflag1 = "true";
        return "false";
    } else {
        for (i = 0; i < field.length; i++) {
            field[i].checked = false;
        }
        checkflag1 = "false";
        return "true";
    }
}




function addframe(){
    $("#modal-store").modal('show');
    $("#modal-title").html("新增条件关键字监控");
    $("#action").val("add");
    $("#id").val('');
    $("#name").val('');
    $("#rate").val('');
    $("#type").val(0);
    $("#plugin").empty();
}

function delItem(id,status) {
   
    var confirmobj = layer.confirm('你确实要删除此条件监听吗？', {
      btn: ['确定','取消']
    }, function(){
      $.ajax({
        type : 'GET',
        url : 'ajax.php?act=delshangyou&id='+id,
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
function editframe(id) {
    var ii = layer.load(2, {shade:[0.1,'#fff']});
    $.ajax({
        type : 'GET',
        url : 'ajax.php?act=getshangyou&id='+id,
        dataType : 'json',
        success : function(data) {
            layer.close(ii);
            if(data.code == 0){
                var rule = data.info;
                $("#modal-store").modal('show');
                $("#modal-title").html("修改规则");
                $("#rule-id").val(rule.id);
                $("#reply-content").val(rule.reply_content);

                // 清空现有的条件输入框
                $("#conditions-container").empty();
                const conditions = rule.conditions.split(' 或 ');
                conditions.forEach(condition => {
                    const orSection = document.createElement('div');
                    orSection.className = 'or-section';

                    const orText = document.createElement('span');
                    orText.textContent = '或';
                    orSection.appendChild(orText);

                    const inputs = condition.split(' 且 ');
                    inputs.forEach((inputText, index) => {
                        if (index > 0) {
                            const andText = document.createElement('span');
                            andText.textContent = '且';
                            orSection.appendChild(andText);
                        }
                        const input = document.createElement('input');
                        input.className = 'form-control';
                        input.value = inputText;
                        orSection.appendChild(input);
                    });

                    const addAndButton = document.createElement('button');
                    addAndButton.textContent = '+';
                    addAndButton.className = 'btn btn-secondary';
                    orSection.appendChild(addAndButton);
                    
                    const andSection = document.createElement('div');
                    andSection.className = 'and-section';
                    orSection.appendChild(andSection);
                    
                    addAndButton.addEventListener('click', function(event) {
                        event.preventDefault(); // 阻止按钮默认行为
                        const andText = document.createElement('span');
                        andText.textContent = '且';
                        andSection.appendChild(andText);

                        const andInput = document.createElement('input');
                        andInput.className = 'form-control';
                        andSection.appendChild(andInput);
                    });

                    $("#conditions-container").append(orSection);
                });
            } else {
                layer.alert(data.msg, {icon: 2});
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
        history.replaceState({}, null, './shangyouqun.php');
    }else if(query != undefined){
        history.replaceState({}, null, './shangyouqun.php?'+query);
    }
    layer.closeAll();
    var ii = layer.load(2, {shade:[0.1,'#fff']});
    $.ajax({
        type : 'GET',
        url : 'shangyouqun-table.php?'+query,
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



function save123() {
    var ii22 = layer.load(2, {shade:[0.1,'#fff']}); 
    var id=$("#rule-id").val();
    
            const conditionsContainer = document.getElementById('conditions-container');
            const replyContent = document.getElementById('reply-content').value;
            
            let conditionsText = '';
            const orSections = conditionsContainer.getElementsByClassName('or-section');
            for (let i = 0; i < orSections.length; i++) {
                if (i > 0) conditionsText += ' 或 ';
                const orInputs = orSections[i].getElementsByTagName('input');
                for (let j = 0; j < orInputs.length; j++) {
                    if (j > 0) conditionsText += ' 且 ';
                    conditionsText += orInputs[j].value;
                }
            }
    console.log(conditionsText);
    
    

    var reply_content = $("#reply-content").val();
    if(reply_content ==''){
        layer.alert('返回内容不能为空！');return false;
    }

    $('#save').val('Loading');

    $.ajax({
        type : "POST",
        url : "ajax.php?act=shangyouqunkan_save",
        data : {id:id,conditionsText:conditionsText,reply_content:reply_content},
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
