<?php
/**
 * Prometheus metrics endpoint /metrics
 * 输出 Prometheus 格式的应用指标
 */
header('Content-Type: text/plain; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate');

$metrics = [];

// PHP 进程信息
$metrics[] = '# HELP php_info PHP runtime information';
$metrics[] = '# TYPE php_info gauge';
$metrics[] = 'php_info{version="' . PHP_VERSION . '"} 1';

// 内存使用
$metrics[] = '# HELP php_memory_usage_bytes Current PHP memory usage in bytes';
$metrics[] = '# TYPE php_memory_usage_bytes gauge';
$metrics[] = 'php_memory_usage_bytes ' . memory_get_usage(true);

$metrics[] = '# HELP php_memory_peak_bytes Peak PHP memory usage in bytes';
$metrics[] = '# TYPE php_memory_peak_bytes gauge';
$metrics[] = 'php_memory_peak_bytes ' . memory_get_peak_usage(true);

// OPcache 状态
if (function_exists('opcache_get_status')) {
    $opcache = @opcache_get_status(false);
    if ($opcache) {
        $metrics[] = '# HELP php_opcache_enabled Whether OPcache is enabled';
        $metrics[] = '# TYPE php_opcache_enabled gauge';
        $metrics[] = 'php_opcache_enabled ' . ($opcache['opcache_enabled'] ? 1 : 0);

        $metrics[] = '# HELP php_opcache_memory_used_bytes OPcache used memory';
        $metrics[] = '# TYPE php_opcache_memory_used_bytes gauge';
        $metrics[] = 'php_opcache_memory_used_bytes ' . ($opcache['memory_usage']['used_memory'] ?? 0);

        $metrics[] = '# HELP php_opcache_hit_rate OPcache hit rate percentage';
        $metrics[] = '# TYPE php_opcache_hit_rate gauge';
        $metrics[] = 'php_opcache_hit_rate ' . ($opcache['opcache_statistics']['opcache_hit_rate'] ?? 0);
    }
}

// 数据库连接检查（计时）
$dbUp = 0;
$dbLatency = 0;
try {
    $dbHost = getenv('DB_HOST') ?: 'localhost';
    $dbPort = getenv('DB_PORT') ?: '3306';
    $dbName = getenv('DB_NAME') ?: 'tianshi_ceshi_zhandian_kkwl';
    $dbUser = getenv('DB_USER') ?: 'root';
    $dbPass = getenv('DB_PASS') ?: '';

    $start = microtime(true);
    $dsn = "mysql:host={$dbHost};port={$dbPort};dbname={$dbName};charset=utf8mb4";
    $pdo = new PDO($dsn, $dbUser, $dbPass, [
        PDO::ATTR_TIMEOUT => 3,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    $pdo->query('SELECT 1');
    $dbLatency = (microtime(true) - $start);
    $dbUp = 1;
} catch (Exception $e) {
    $dbUp = 0;
}

$metrics[] = '# HELP app_database_up Whether database connection is successful';
$metrics[] = '# TYPE app_database_up gauge';
$metrics[] = 'app_database_up ' . $dbUp;

$metrics[] = '# HELP app_database_latency_seconds Database query latency in seconds';
$metrics[] = '# TYPE app_database_latency_seconds gauge';
$metrics[] = 'app_database_latency_seconds ' . round($dbLatency, 6);

// 应用版本
$version = 'unknown';
if (file_exists(__DIR__ . '/version.json')) {
    $info = json_decode(file_get_contents(__DIR__ . '/version.json'), true);
    $version = $info['version'] ?? 'unknown';
}
$metrics[] = '# HELP app_build_info Application build information';
$metrics[] = '# TYPE app_build_info gauge';
$metrics[] = 'app_build_info{version="' . $version . '"} 1';

// 磁盘使用
$metrics[] = '# HELP app_disk_free_bytes Free disk space on /app';
$metrics[] = '# TYPE app_disk_free_bytes gauge';
$metrics[] = 'app_disk_free_bytes ' . disk_free_space('/app');

// 输出
echo implode("\n", $metrics) . "\n";
