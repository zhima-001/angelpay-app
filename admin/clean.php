<?php
/**
 * 系统数据清理
**/
include("../includes/common.php");
$title='系统数据清理';
include './head.php';
if($islogin==1){}else exit("<script language='javascript'>window.location.href='./login.php';</script>");
?>
  <div class="container" style="padding-top:70px;">
    <div class="col-xs-12 col-sm-10 col-lg-8 center-block" style="float: none;">
<?php
$mod=isset($_GET['mod'])?$_GET['mod']:null;
if($mod=='cleancache'){
$CACHE->clear();
if(function_exists("opcache_reset"))@opcache_reset();
showmsg('清理系统设置缓存成功！',1);
}elseif($mod=='cleanorder'){
$DB->exec("DELETE FROM `pre_order` WHERE addtime<'".date("Y-m-d H:i:s",strtotime("-30 days"))."'");
$DB->exec("OPTIMIZE TABLE `pre_order`");
showmsg('删除30天前订单记录成功！',1);
}elseif($mod=='clearzhifu'){
	//echo "aaaaaaa";exit;
$DB->exec("DELETE FROM `pre_rizhi` WHERE addtime<'".strtotime("-30 days")."'");
$DB->exec("OPTIMIZE TABLE `pre_rizhi`");
showmsg('删除30天前支付流程记录成功！',1);
}elseif($mod=='clearhd'){
	//echo "aaaaaaa";exit;
$DB->exec("DELETE FROM `pre_notify_rizhi` WHERE addtime<'".strtotime("-30 days")."'");
$DB->exec("OPTIMIZE TABLE `pre_notify_rizhi`");
showmsg('删除30天前回调流程记录成功！',1);
}elseif($mod=='jizhang'){
	//echo "aaaaaaa";exit;
$DB->exec("DELETE FROM `pre_jizhang` WHERE addtime<'".strtotime("-30 days")."'");
$DB->exec("OPTIMIZE TABLE `pre_jizhang`");
showmsg('删除30天前记账管理记录成功！',1);
}elseif($mod=='cleansettle'){
$DB->exec("DELETE FROM `pre_settle` WHERE addtime<'".date("Y-m-d H:i:s",strtotime("-30 days"))."'");
$DB->exec("OPTIMIZE TABLE `pre_settle`");
showmsg('删除30天前结算记录成功！',1);
}elseif($mod=='cleanrecord'){
$DB->exec("DELETE FROM `pre_record` WHERE date<'".date("Y-m-d H:i:s",strtotime("-30 days"))."'");
$DB->exec("OPTIMIZE TABLE `pre_record`");
showmsg('删除30天前资金明细成功！',1);
}elseif($mod=='cleanorderi' && $_POST['do']=='submit'){
$days = intval($_POST['days']);
if($days<=0)showmsg('请确保每项不能为空',3);
$DB->exec("DELETE FROM `pre_order` WHERE addtime<'".date("Y-m-d H:i:s",strtotime("-{$days} days"))."'");
$DB->exec("OPTIMIZE TABLE `pre_order`");
showmsg('删除订单记录成功！',1);
}elseif($mod=='clearzhifui' && $_POST['do']=='submit'){
//	echo "bbbbbbbbbb";exit;
$days = intval($_POST['days']);
if($days<=0)showmsg('请确保每项不能为空',3);
//$DB->exec("DELETE FROM `pre_order` WHERE addtime<'".date("Y-m-d H:i:s",strtotime("-{$days} days"))."'");
$DB->exec("DELETE FROM `pre_rizhi` WHERE addtime<'".strtotime("-{$days} days")."'");
$DB->exec("OPTIMIZE TABLE `pre_rizhi`");
showmsg('删除支付流程成功！',1);
}elseif($mod=='clearhuidiao' && $_POST['do']=='submit'){
//	echo "bbbbbbbbbb";exit;
$days = intval($_POST['days']);
if($days<=0)showmsg('请确保每项不能为空',3);
//$DB->exec("DELETE FROM `pre_order` WHERE addtime<'".date("Y-m-d H:i:s",strtotime("-{$days} days"))."'");
$DB->exec("DELETE FROM `pre_notify_rizhi` WHERE addtime<'".strtotime("-{$days} days")."'");
$DB->exec("OPTIMIZE TABLE `pre_notify_rizhi`");
showmsg('删除回调流程成功！',1);
}elseif($mod=='timejizhang' && $_POST['do']=='submit'){
//	echo "bbbbbbbbbb";exit;
$days = intval($_POST['days']);
if($days<=0)showmsg('请确保每项不能为空',3);
//$DB->exec("DELETE FROM `pre_order` WHERE addtime<'".date("Y-m-d H:i:s",strtotime("-{$days} days"))."'");
$DB->exec("DELETE FROM `pre_jizhang` WHERE addtime<'".strtotime("-{$days} days")."'");
$DB->exec("OPTIMIZE TABLE `pre_jizhang`");
showmsg('删除记账管理记录成功！',1);
}elseif($mod=='cleansettlei' && $_POST['do']=='submit'){
$days = intval($_POST['days']);
if($days<=0)showmsg('请确保每项不能为空',3);
$DB->exec("DELETE FROM `pre_settle` WHERE addtime<'".date("Y-m-d H:i:s",strtotime("-{$days} days"))."'");
$DB->exec("OPTIMIZE TABLE `pre_settle`");
showmsg('删除结算记录成功！',1);
}elseif($mod=='cleanrecordi' && $_POST['do']=='submit'){
$days = intval($_POST['days']);
if($days<=0)showmsg('请确保每项不能为空',3);
$DB->exec("DELETE FROM `pre_record` WHERE date<'".date("Y-m-d H:i:s",strtotime("-{$days} days"))."'");
$DB->exec("OPTIMIZE TABLE `pre_record`");
showmsg('删除资金明细成功！',1);
}else{
?>
<div class="panel panel-primary">
<div class="panel-heading"><h3 class="panel-title">系统数据清理</h3></div>
<div class="panel-body">
<a href="./clean.php?mod=cleancache" class="btn btn-block btn-default">清理设置缓存</a><br/>
<a href="./clean.php?mod=cleanorder" onclick="return confirm('你确实要删除30天前的订单记录吗？');" class="btn btn-block btn-default">删除30天前订单记录</a><br/>
<a href="./clean.php?mod=cleansettle" onclick="return confirm('你确实要删除30天前的结算记录吗？');" class="btn btn-block btn-default">删除30天前结算记录</a><br/>
<a href="./clean.php?mod=cleanrecord" onclick="return confirm('你确实要删除30天前的资金明细吗？');" class="btn btn-block btn-default">删除30天前资金明细</a><br/>
<a href="./clean.php?mod=clearzhifu" onclick="return confirm('你确实要删除30天前的支付流程明细吗？');" class="btn btn-block btn-default">删除30天前支付流程明细</a><br/>
<a href="./clean.php?mod=clearhd" onclick="return confirm('你确实要删除30天前的回调流程明细吗？');" class="btn btn-block btn-default">删除30天前回调流程明细</a><br/>
<a href="./clean.php?mod=jizhang" onclick="return confirm('你确实要删除30天前记账管理记录吗？');" class="btn btn-block btn-default">删除30天前记账管理记录</a><br/>
<h4>自定义清理：</h4>
<form action="./clean.php?mod=cleanorderi" method="post" role="form"><input type="hidden" name="do" value="submit"/>
<b>订单记录</b>：<input type="text" name="days" value="" placeholder="天数"/>天前的订单记录&nbsp;<input type="submit" name="submit" value="立即删除" class="btn btn-sm btn-danger" onclick="return confirm('删除后无法恢复，确定继续吗？');"/>
</form><br/>
<form action="./clean.php?mod=cleansettlei" method="post" role="form"><input type="hidden" name="do" value="submit"/>
<b>结算记录</b>：<input type="text" name="days" value="" placeholder="天数"/>天前的结算记录&nbsp;<input type="submit" name="submit" value="立即删除" class="btn btn-sm btn-danger" onclick="return confirm('删除后无法恢复，确定继续吗？');"/>
</form><br/>
<form action="./clean.php?mod=cleanrecordi" method="post" role="form"><input type="hidden" name="do" value="submit"/>
<b>资金明细</b>：<input type="text" name="days" value="" placeholder="天数"/>天前的订单记录&nbsp;<input type="submit" name="submit" value="立即删除" class="btn btn-sm btn-danger" onclick="return confirm('删除后无法恢复，确定继续吗？');"/>
</form><br/>


<form action="./clean.php?mod=clearzhifui" method="post" role="form"><input type="hidden" name="do" value="submit"/>
<b>支付流程</b>：<input type="text" name="days" value="" placeholder="天数"/>天前的支付流程&nbsp;<input type="submit" name="submit" value="立即删除" class="btn btn-sm btn-danger" onclick="return confirm('删除后无法恢复，确定继续吗？');"/>
</form><br/>


<form action="./clean.php?mod=clearhuidiao" method="post" role="form"><input type="hidden" name="do" value="submit"/>
<b>回调流程</b>：<input type="text" name="days" value="" placeholder="天数"/>天前的回调流程&nbsp;<input type="submit" name="submit" value="立即删除" class="btn btn-sm btn-danger" onclick="return confirm('删除后无法恢复，确定继续吗？');"/>
</form><br/>

<form action="./clean.php?mod=timejizhang" method="post" role="form"><input type="hidden" name="do" value="submit"/>
<b>记账管理</b>：<input type="text" name="days" value="" placeholder="天数"/>天前的记账记录&nbsp;<input type="submit" name="submit" value="立即删除" class="btn btn-sm btn-danger" onclick="return confirm('删除后无法恢复，确定继续吗？');"/>
</form><br/>
</div>
<div class="panel-footer">
<span class="glyphicon glyphicon-info-sign"></span>
定期清理数据有助于提升网站访问速度
</div>
</div>
<?php }?>
 </div>
</div>