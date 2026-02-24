<?php
/**
 * 就绪检查端点 /ready
 * 返回200表示应用已准备好接收流量（readiness）
 * 检查数据库连接等关键依赖
 */
header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');

$checks = [];
$allOk = true;

// 检查PHP运行环境
$checks['php'] = ['status' => 'ok', 'version' => PHP_VERSION];

// 检查必要目录可写
$writableDirs = ['/app/runtime', '/app/uploads'];
foreach ($writableDirs as $dir) {
    if (is_dir($dir) && is_writable($dir)) {
        $checks['dir_' . basename($dir)] = ['status' => 'ok'];
    } else {
        $checks['dir_' . basename($dir)] = ['status' => 'fail', 'message' => 'not writable'];
        $allOk = false;
    }
}

// 检查数据库连接
try {
    $dbHost = getenv('DB_HOST') ?: 'localhost';
    $dbPort = getenv('DB_PORT') ?: '3306';
    $dbName = getenv('DB_NAME') ?: 'tianshi_ceshi_zhandian_kkwl';
    $dbUser = getenv('DB_USER') ?: 'root';
    $dbPass = getenv('DB_PASS') ?: '';

    $dsn = "mysql:host={$dbHost};port={$dbPort};dbname={$dbName};charset=utf8mb4";
    $pdo = new PDO($dsn, $dbUser, $dbPass, [
        PDO::ATTR_TIMEOUT => 3,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    $pdo->query('SELECT 1');
    $checks['database'] = ['status' => 'ok'];
} catch (Exception $e) {
    $checks['database'] = ['status' => 'fail', 'message' => 'connection failed'];
    $allOk = false;
}

http_response_code($allOk ? 200 : 503);
echo json_encode([
    'status'    => $allOk ? 'ready' : 'not_ready',
    'checks'    => $checks,
    'timestamp' => date('Y-m-d H:i:s'),
]);
