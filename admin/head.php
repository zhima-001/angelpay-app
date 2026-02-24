<?php
@header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html lang="zh-cn">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title><?php echo $title ?></title>
  <link href="../assets/js/new/bootstrap.min.css" rel="stylesheet"/>
  <link href="../assets/css/bootstrap.min.css" rel="stylesheet"/>
  <!--<link rel="stylesheet" type="text/css" href="../assets/js/datetimepicker-master/jquery.datetimepicker.css"/ >-->
  <link href="../assets/js/new/font-awesome.min.css" rel="stylesheet"/>
   <link href="../assets/js/layui/css/layui.css" rel="stylesheet">
  <script src="../assets/js/new/modernizr.min.js"></script>
  <script src="../assets/js/new/jquery.min.js"></script>

  <script src="../assets/js/new/bootstrap.min.js"></script>
  <!--[if lt IE 9]>
    <script src="../assets/js/new/html5shiv.min.js"></script>
    <script src="../assets/js/new/respond.min.js"></script>
  <![endif]-->
</head>
<body>
<?php if($islogin==1){?>
  <nav class="navbar navbar-fixed-top navbar-default">
    <div class="container">
      <div class="navbar-header">
        <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
          <span class="sr-only">导航按钮</span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
        </button>
        <a class="navbar-brand" href="./">天使支付管理中心</a>
      </div><!-- /.navbar-header -->
      <div id="navbar" class="collapse navbar-collapse">
        <ul class="nav navbar-nav navbar-right">
          <li class="<?php echo checkIfActive('index,')?>">
            <a href="./"><i class="fa fa-home"></i> 平台首页</a>
          </li>
		  <li class="<?php echo checkIfActive('order')?>">
            <a href="./order.php"><i class="fa fa-list"></i> 订单管理</a>
          </li>
           <li class="<?php echo checkIfActive('pay_jizhang')?>">
            <a href="./jizhang.php"><i class="fa fa-cny"></i> 记账管理</a>
          </li>
		  <li class="<?php echo checkIfActive('settle,slist')?>">
            <a href="./slist.php"><i class="fa fa-cloud"></i> 结算管理</a>
          </li>
          
		  <li class="<?php echo checkIfActive('ulist,glist,group,record')?>">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="fa fa-user"></i> 商户管理<b class="caret"></b></a>
            <ul class="dropdown-menu">
              <li><a href="./shanghu.php">商户实时成率</a></li>
              <li><a href="./shangyouqun.php">上游群自动回复</a></li>
              <li><a href="./tousu.php">投诉管理</a></li>
              <li><a href="./budan.php">补单管理</a></li>
              <li><a href="./ulist.php">用户列表</a></li>
			  <li><a href="./glist.php">用户组设置</a></li>
			  <li><a href="./group.php">用户组购买</a></li>
			  <li><a href="./record.php">资金明细</a></li>
			  
            </ul>
          </li>
		  <li class="<?php echo checkIfActive('pay_channel,pay_roll,pay_type,pay_plugin')?>">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="fa fa-credit-card"></i> 支付接口<b class="caret"></b></a>
            <ul class="dropdown-menu">
              <li><a href="./pay_channel.php">支付通道</a></li>
			  <li><a href="./pay_roll.php">通道轮询</a></li>
			  <li><a href="./pay_type.php">支付方式</a></li>
			  <li><a href="./pay_plugin.php">支付插件</a></li>
			   <li><a href="./erweima.php">二维码管理</a></li>
            </ul>
          </li>
		  <li class="<?php echo checkIfActive('set,gonggao')?>">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="fa fa-cog"></i> 系统设置<b class="caret"></b></a>
            <ul class="dropdown-menu">
              <li><a href="./set.php?mod=site">网站信息配置</a></li>
              <?php if($_COOKIE['admin_user'] =="admin" || $_COOKIE['admin_user'] =="tianshi888" || $_COOKIE['admin_user'] =="yyy"){?>
			  <li><a href="./set.php?mod=pay">支付与结算配置</a><li>
			  <li><a href="./set.php?mod=transfer">企业付款配置</a><li>
			  <li><a href="./set.php?mod=oauth">快捷登录配置</a><li>
			  <li><a href="./set.php?mod=certificate">实名认证配置</a><li>
			  <li><a href="./gonggao.php">网站公告配置</a></li>
			  <li><a href="./set.php?mod=template">首页模板配置</a><li>
			  <li><a href="./set.php?mod=mail">邮箱与短信配置</a><li>
			  <li><a href="./set.php?mod=upimg">网站Logo上传</a><li>
			  <li><a href="./set.php?mod=cron">计划任务配置</a><li>
			      <?php } ?> 
            </ul>
          </li>
              <?php if($_COOKIE['admin_user'] =="admin" || $_COOKIE['admin_user'] =="tianshi888" || $_COOKIE['admin_user'] =="yyy"){?>
		  <li class="<?php echo checkIfActive('clean,log,transfer')?>">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="fa fa-cube"></i> 其他功能<b class="caret"></b></a>
            <ul class="dropdown-menu">
			  <li><a href="./transfer.php">企业付款</a><li>
			  <li><a href="./risk.php">风控记录</a><li>
			  <li><a href="./log.php">登录日志</a><li>
			   <!--<li><a href="./drizhi.php">订单流日志</a><li>-->
			   <li><a href="./hrizhi.php">回调日志</a><li>
			  <li><a href="./clean.php">数据清理</a><li>
            </ul>
          </li>
           <?php } ?>
          <li><a href="./login.php?logout"><i class="fa fa-power-off"></i> 退出登录</a></li>
        </ul>
      </div><!-- /.navbar-collapse -->
    </div><!-- /.container -->
  </nav><!-- /.navbar -->
<?php }?>