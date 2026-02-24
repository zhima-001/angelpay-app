<?php
    require './includes/common.php';
    $order_sn = $_GET['order_sn'];
    $userrow=$DB->query("SELECT `urlstr` FROM `pre_orderzhong` WHERE `order_sn`='{$order_sn}' LIMIT 1")->fetch();
    if($userrow){
        $real_url = $userrow['urlstr'];
        echo "<script>window.location.href='".$real_url."';</script>";
    }

?>