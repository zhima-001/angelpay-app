<?php
/**
 * config.php
 * Easypay聚合支付系统
 * =========================================================
 * Copy right 2015-2025 Easypay, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: http://www.0533hf.com
 *
 * 请尊重开发人员劳动成果，严禁使用本系统转卖、销售或二次开发后转卖、销售等商业行为。
 * 任何企业和个人不允许对程序代码以任何形式任何目的再发布。
 * =========================================================
 * @author : 366131726@qq.com
 * @date : 2019-05-14
 */

//error_reporting(E_ALL & ~E_NOTICE);
date_default_timezone_set('Asia/Shanghai');
header("Content-type: text/html; charset=utf-8");

$gateway = 'https://tcpp.fadddd.com/index/pay';      //网关地址
$merId = $channel['appid']; //商户号
$md5key = $channel['appkey'];//商户密钥

require 'Rsa.php';
require 'Http.php';
require 'Random.php';

/**
 * 签名算法
 * @param $data         请求数据
 * @param $md5Key       md5秘钥
 * @param $privateKey   商户私钥
 */
 function sign($data,$md5Key,$privateKey){
    ksort($data);
    reset($data);
    $arg = '';
    foreach ($data as $key => $val) {
        //空值不参与签名
        if ($val == '' || $key == 'sign') {
            continue;
        }
        $arg .= ($key . '=' . $val . '&');
    }
    $arg = $arg . 'key=' . $md5Key;

    //签名数据转换为大写
    $sig_data = strtoupper(md5($arg));
    //使用RSA签名
    $rsa = new Rsa('', $privateKey);
    //私钥签名
    return $rsa->sign($sig_data);
}
function sign2($data,$md5Key){
	
    ksort($data);
    reset($data);
	var_dump($data);exit;
    $arg = '';
    foreach ($data as $key => $val) {
        //空值不参与签名
        if ($val == '' || $key == 'sign') {
            continue;
        }
        $arg .= ($key . '=' . $val . '&');
    }
    $arg = $arg . 'key=' . $md5Key;

    //签名数据转换为大写
    $sig_data = strtoupper(md5($arg));
    //使用RSA签名
    $rsa = new Rsa('', $privateKey);
    //私钥签名
    return $rsa->sign($sig_data);
}

/**
 * 验签
 * @param $data         返回数据
 * @param $md5Key       md5秘钥
 * @param $pubKey       平台公钥
 */
function verify($data,$md5Key,$pubKey){
    //验签
    ksort($data);
    reset($data);
    $arg = '';
    foreach ($data as $key => $val) {
        //空值不参与签名
        if ($val == '' || $key == 'sign') {
            continue;
        }
        $arg .= ($key . '=' . $val . '&');
    }
    $arg = $arg . 'key=' . $md5Key;
    $signData = strtoupper(md5($arg));
    $rsa = new Rsa($pubKey, '');
    if ($rsa->verify($signData, $data['sign']) == 1) {
        return true;
    }
    return false;
}


