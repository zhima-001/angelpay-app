<?php
// SSE 响应头设置
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('X-Accel-Buffering: no'); // 对某些Nginx版本需要禁用缓冲
ob_implicit_flush(1);
ob_end_flush();

require 'vendor/autoload.php';
require 'MyEventHandler.php';

use danog\MadelineProto\API;
use danog\MadelineProto\Settings;
use danog\MadelineProto\Logger;
use Revolt\EventLoop;
use function Amp\async;
use function Amp\await;

// 在 MyEventHandler 中我们将通过 echo 输出SSE数据
MyEventHandler::$outputSSE = true; // 自定义的静态属性，用于控制输出

EventLoop::run(function () {
    $api_id = TELEGRAM_API_ID;    // 配置你的API ID
    $api_hash = TELEGRAM_API_HASH; // 配置你的API HASH

    // 指定事件处理器
    $settings = new Settings([
        'logger' => [
            'logger' => 4 // info级别日志
        ],
        'app_info' => [
            'api_id' => $api_id,
            'api_hash' => $api_hash
        ],
        'updates' => [
            'handler' => MyEventHandler::class
        ]
    ]);

    $MadelineProto = new API('session.madeline', $settings);
    $MadelineProto->async(true);

    // 异步启动
    await($MadelineProto->start());

    // 启动后事件处理器将自动监听消息
    // 没有显式的loop或sleep，因为EventLoop::run()会阻塞直到手动中断
    echo "data: SSE Listener started\n\n";
    flush();
});
