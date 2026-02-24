<?php
/**
 * 健康检查端点 /health
 * 返回200表示应用存活（liveness）
 */
header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');

$version = 'unknown';
$buildTime = 'unknown';
if (file_exists(__DIR__ . '/version.json')) {
    $info = json_decode(file_get_contents(__DIR__ . '/version.json'), true);
    $version = $info['version'] ?? 'unknown';
    $buildTime = $info['build_time'] ?? 'unknown';
}

http_response_code(200);
echo json_encode([
    'status'     => 'ok',
    'version'    => $version,
    'build_time' => $buildTime,
    'timestamp'  => date('Y-m-d H:i:s'),
    'php'        => PHP_VERSION,
]);
