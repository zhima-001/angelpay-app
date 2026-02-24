<?php
if (version_compare(PHP_VERSION, '5.4.0', '<')) {
    die('require PHP > 5.4 !');
}
include("./includes/common.php");
$shanghuhou = false;
//这里查下是不是开启了：
         $kefus_sql = "select * FROM pay_config";
         $kefus_query =$DB->query($kefus_sql); 
         $xiafa_info = $kefus_query->fetchAll();
         foreach ($xiafa_info as $ksa =>$saq){
             if($saq['k']=="shangqiantai"){
                 if($saq['v']=="1"){
                      $shanghuhou=true;
                 }else{ 
                      $shanghuhou=false;
                 }
             }
           
         }
if(!$shanghuhou){
    echo "网站前台已关闭！";
    exit();
}         

$mod = isset($_GET['mod'])?$_GET['mod']:'index';
$loadfile = \lib\Template::load($mod);
include $loadfile;