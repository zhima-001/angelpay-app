<?php 
/**
 * 商户信息
**/
include("../includes/common.php");
$title='商户信息';
include './head.php';
if($islogin==1){}else exit("<script language='javascript'>window.location.href='./login.php';</script>");
?>
  <div class="container" style="padding-top:70px;">
    <div class="col-xs-12 col-sm-10 col-lg-8 center-block" style="float: none;">
	<?php
$jiage = $_POST["jiage"];
$erweima = $_POST['erweima'];
$beizhu = $_POST['beizhu'];

$sds=$DB->exec("INSERT INTO `pre_erweima` (`jiage`, `erweima`, `beizhu`) VALUES ('{$jiage}', '{$erweima}', '{$beizhu}')");

showmsg('添加二维码成功！<br/><a href="./erweima.php">>>返回二维码列表</a>',1);
?>