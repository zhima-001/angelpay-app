<?php
/**
 * 数据库配置 - 从环境变量读取
 * K8s 通过 ConfigMap/Secret 注入环境变量
 */
$dbconfig = array(
    'host'   => getenv('DB_HOST') ?: 'localhost',
    'port'   => intval(getenv('DB_PORT') ?: 3306),
    'user'   => getenv('DB_USER') ?: 'root',
    'pwd'    => getenv('DB_PASS') ?: '',
    'dbname' => getenv('DB_NAME') ?: 'tianshi_ceshi_zhandian_kkwl',
    'dbqz'   => 'pre_'
);
