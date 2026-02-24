<?php
@header('Content-Type: text/html; charset=UTF-8');
if($userrow['status']==0){
	sysmsg('你的商户由于违反相关法律法规与《<a href="/?mod=agreement">"'.$conf['sitename'].'用户协议</a>》，已被禁用！');
}
switch($conf['user_style']){
	case 1: $style=['bg-black','bg-black','bg-white']; break;
	case 2: $style=['bg-dark','bg-white','bg-dark']; break;
	case 3: $style=['bg-dark','bg-dark','bg-light']; break;
	case 4: $style=['bg-info','bg-info','bg-black']; break;
	case 5: $style=['bg-info','bg-info','bg-white']; break;
	case 6: $style=['bg-primary','bg-primary','bg-dark']; break;
	case 7: $style=['bg-primary','bg-primary','bg-white']; break;
	default: $style=['bg-black','bg-white','bg-black']; break;
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
  <meta charset="utf-8" />
  <title><?php echo $conf['sitename']?> - 商户管理中心</title>
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
<!--  <link rel="stylesheet" type="text/css" href="../assets/layui/css/layui.css" />-->
  <link rel="stylesheet" href="assets/css/bootstrap.min.css" type="text/css" />
  <link rel="stylesheet" href="assets/css/animate.min.css" type="text/css" />
  <link rel="stylesheet" href="assets/css/font-awesome.min.css" type="text/css" />
  <link rel="stylesheet" href="assets/css/simple-line-icons.min.css" type="text/css" />
  <link rel="stylesheet" href="assets/css/font.css" type="text/css" />
  <link rel="stylesheet" href="login/app.css" type="text/css" />
  <link rel="stylesheet" type="text/css" href="login/style.css" />
    <link rel="stylesheet" href="../assets/js/new/font-awesome.min.css" type="text/css" />
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
  <header id="header" class="navbar" role="menu">
          <!-- navbar header -->
      <div class="navbar-header bg-dark" style="border-bottom: 1px solid #e6e6e6;">
       
        <!-- brand -->
        <a href="./" class="navbar-brand text-lt text-center" style="padding-left: 0px;padding-right: 0px;">
          <i class="fa fa-jsfiddle"></i>
          <span class="hidden-folded m-l-xs"><?php echo $conf['sitename']?></span>

       <button class="pull-right visible-xs dk" ui-toggle="show" target=".navbar-collapse" style="float: right;">
          <i class="glyphicon glyphicon-user"></i>
        </button>
        <button class="visible-xs" ui-toggle="off-screen" target=".app-aside" ui-scroll="app" style="float: left;">
          <i class="glyphicon glyphicon-align-left"></i>
        </button>


        </a>
        <!-- / brand -->
      </div>
      <!-- / navbar header -->

      <!-- navbar collapse -->
      <div class="collapse pos-rlt navbar-collapse box-shadow bg-white-only">
        <!-- buttons -->
        <div class="nav navbar-nav hidden-xs">
          <a href="#" class="btn no-shadow navbar-btn" ui-toggle="app-aside-folded" target=".app">
            <i class="fa fa-dedent fa-fw text"></i>
            <i class="fa fa-indent fa-fw text-active"></i>
          </a>
        </div>
        <!-- / buttons -->

        <!-- nabar right -->
        <ul class="nav navbar-nav navbar-right dropdown" style="margin-top: 20px;height: 120px;">

        <div class="col-md-12">
          <div class="row-sm text-center">
            <div class="col-xs-6">
              <div class="r bg-light dker item hbox no-border" style="box-shadow: 0px 0px 0px 0px rgba(0, 0, 0, 0) !important;">
                <div id="uidd" class="r bg-light dker item hbox no-border" style="border-radius: 0px;">
                <div id="uidd" class="dk padder-v r-r">
                  <a href="?userinfo">
                            <!-- dropdown -->
            <ul class="dropdown-menu animated fadeInRight w">
              <li>
                <a href="index.php">
                  <span>用户中心</span>
                </a>
              </li>
              <li>
                <a href="userinfo.php?mod=info">
                  <span>修改资料</span>
                </a>
              </li>
			  <li>
                <a href="userinfo.php?mod=account">
                  <span>修改密码</span>
                </a>
              </li>
              <li class="divider"></li>
              <li>
                <a ui-sref="access.signin" href="login.php?logout">退出登录</a>
              </li>
            </ul>
            
            <!-- / dropdown -->
         
        <!-- / navbar right -->
      </div>
      <!-- / navbar collapse -->

  </header>
  <!-- / header -->

  <!-- aside -->
  <aside id="aside" class="app-aside hidden-xs bg-dark">
     <div id="gbui" class="navbar-header bg-dark" style="border-bottom: 1px solid #e6e6e6;">
        <a href="./" class="navbar-brand text-lt text-center" style="padding-left: 0px;padding-right: 0px;">
          <i class="fa fa-jsfiddle"></i>
          <span class="hidden-folded m-l-xs"><?php echo $conf['sitename']?></span>
        </a>
      </div>
      <div class="aside-wrap">
        <div class="navi-wrap">

              <!-- aside -->

          <!-- nav -->
          <nav ui-nav class="navi clearfix">
            <ul class="nav">
              <li class="hidden-folded padder m-t m-b-sm text-muted text-xs">
                <div id="spans" style="border-bottom: 50px solid transparent;"></div>
                <span>导航</span>
              </li>
              <li>
                <a href="./">
                  <i class="glyphicon glyphicon-home icon text-primary-dker"></i>
				  <b class="label bg-info pull-right">N</b>
                  <span class="font-bold">用户中心</span>
                </a>
              </li>
        <li>
                <a href="recharge.php">
                  <i class="icon-wallet icon text-info-lter"></i>
                  <span>余额充值</span>
                </a>
              </li>
               <?php if($conf['smrz']==1){ ?>
            <?php }if($userrow['uid']!=''){ ?>
              <li>
                <a href="groupbuy.php">
                  <i class="glyphicon glyphicon-tower" style="color: #333;"></i>
                  <span>购买会员</span>
                </a>
              </li>
                            <li>
                <a href="editinfo.php">
                  <i class="glyphicon glyphicon-edit"></i>
                  <span>修改资料</span>
                </a> 
              </li>
			  <li>
               <a href="userinfo.php?mod=api">
               	<i class="glyphicon glyphicon-wrench"></i>
                  <span>我的密钥</span>
                </a> 
              </li>
            <?php } ?>
              <li class="hidden-folded padder m-t m-b-sm text-muted text-xs">
                <span>查询</span>
              </li>
			  <li>
                <a href="order.php">
                  <i class="glyphicon glyphicon-list-alt"></i>
                  <span>订单记录</span>
                </a>
              </li>
               <li>
                <a href="tousu.php">
                  <i class="glyphicon glyphicon-list-alt"></i>
                  <span>投诉管理</span>
                </a>
              </li>
               <li>
                <a href="budan.php">
                  <i class="glyphicon glyphicon-list-alt"></i>
                  <span>补单记录</span>
                </a>
              </li>
			  <li>
                <a href="settle.php">
                  <i class="glyphicon glyphicon-check"></i>
                  <span>结算记录</span>
                </a>
              </li>
              <li>
                <a href="record.php">
                  <i class="glyphicon glyphicon-calendar"></i>
                  <span>资金明细</span>
                </a>
              </li>
                <li class="hidden-folded padder m-t m-b-sm text-muted text-xs">
                <span>其他</span>
              </li>
<?php if($conf['yq_open']==1){?>      <?php }?>
              <li>
                <a href="onecode.php">
                  <i class="fa fa-paper-plane-o"></i>
                  <span>一码支付</span>
                </a>
			  <li>
                <a href="help.php">
                  <i class="fa fa-handshake-o"></i>
                  <span>结算说明</span>
                </a>
              </li>
              <li>
                <a href="<?php echo $conf['qqqun']?>" target="blank">
                  <i class="fa fa-qq"></i>
                  <span>产品QQ群</span>
                </a>
              </li>
              <li>
                <a href="https://wpa.qq.com/msgrd?v=3&uin=<?php echo $conf['kfqq']?>&site=pay&menu=yes" target="blank">
                  <i class="fa fa-qq"></i>
                  <span>在线客服</span>
                </a>
              </li>
              <li class="hidden-folded padder m-t m-b-sm text-muted text-xs">
                <span>接入</span>
              </li>
               <li>
                <a href="login.php?logout" onclick="logout()">
                  <i class="glyphicon glyphicon-log-in"></i>
                  <span>退出登录</span>
                </a>
              </li>
            </ul>
          </nav>
          <!-- nav -->

          <!-- aside footer -->
          <div class="wrapper m-t">
<div class="text-center"><a href="./" target="_blank"><?php echo $conf['sitename']?></a></div><br>
<div class="text-center">&copy; 2018-2020Copyright.</div>
    </div>

          <!-- / aside footer -->
        </div>
      </div>
  </aside>