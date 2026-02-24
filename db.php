<?php
// db.php

require_once 'config.php';

if (!defined('DB_HOST')) {
    define('DB_HOST', $dbconfig['host']);
}
if (!defined('DB_USER')) {
    define('DB_USER', $dbconfig['user']);
}
if (!defined('DB_PASS')) {
    define('DB_PASS', $dbconfig['pwd']);
}
if (!defined('DB_NAME')) {
    define('DB_NAME', $dbconfig['dbname']);
}
/**
 * 获取数据库连接
 *
 * @return PDO
 */
function getDBConnection() {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $pdo = new PDO($dsn, DB_USER, DB_PASS);
        // 设置 PDO 错误模式为异常
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (PDOException $e) {
        die('数据库连接错误: ' . $e->getMessage());
    }
}
?>
