<?php
/**
 * 机器人消息发送示例
 * 展示如何使用 bot_message.php 发送消息到 Telegram 机器人
 */

// 示例1: 基本使用 - 只更新订单时间
$basic_params = array(
    'trade_no' => 'ORDER123456789',
    'start' => 0, // 从数据库获取开始时间
    'end' => time() * 1000 // 当前时间（毫秒）
);

// 示例2: 带自定义消息
$custom_message_params = array(
    'trade_no' => 'ORDER123456789',
    'start' => 0,
    'end' => time() * 1000,
    'message' => '订单已成功处理，用户已付款',
    'chat_id' => '982124360' // 指定接收消息的聊天ID
);

// 示例3: 指定开始和结束时间
$time_specific_params = array(
    'trade_no' => 'ORDER123456789',
    'start' => 1640995200000, // 2022-01-01 00:00:00 的毫秒时间戳
    'end' => 1640998800000,   // 2022-01-01 01:00:00 的毫秒时间戳
    'message' => '订单处理完成，耗时1小时',
    'chat_id' => '-1001556731305' // 群组ID
);

/**
 * 发送请求到 bot_message.php
 */
function sendBotMessage($params) {
    $url = 'http://your-domain.com/bot_message.php';
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return array(
        'http_code' => $http_code,
        'response' => json_decode($response, true)
    );
}

// 使用示例
echo "<h2>机器人消息发送示例</h2>";

echo "<h3>示例1: 基本使用</h3>";
$result1 = sendBotMessage($basic_params);
echo "<pre>" . json_encode($result1, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "</pre>";

echo "<h3>示例2: 带自定义消息</h3>";
$result2 = sendBotMessage($custom_message_params);
echo "<pre>" . json_encode($result2, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "</pre>";

echo "<h3>示例3: 指定时间范围</h3>";
$result3 = sendBotMessage($time_specific_params);
echo "<pre>" . json_encode($result3, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "</pre>";

/**
 * 直接调用示例（在同一服务器上）
 */
echo "<h3>直接调用示例</h3>";
echo "<p>如果您在同一服务器上，可以直接包含文件：</p>";
echo "<pre>";
echo '<?php' . "\n";
echo '$_REQUEST["trade_no"] = "ORDER123456789";' . "\n";
echo '$_REQUEST["message"] = "测试消息";' . "\n";
echo '$_REQUEST["chat_id"] = "982124360";' . "\n";
echo '$_REQUEST["start"] = 0;' . "\n";
echo '$_REQUEST["end"] = time() * 1000;' . "\n";
echo 'include "bot_message.php";' . "\n";
echo '?>';
echo "</pre>";

/**
 * JavaScript/AJAX 调用示例
 */
echo "<h3>JavaScript/AJAX 调用示例</h3>";
echo "<pre>";
echo 'function sendBotMessage(tradeNo, message, chatId) {' . "\n";
echo '    const params = {' . "\n";
echo '        trade_no: tradeNo,' . "\n";
echo '        message: message || "订单状态更新",' . "\n";
echo '        chat_id: chatId || "982124360",' . "\n";
echo '        start: 0,' . "\n";
echo '        end: Date.now()' . "\n";
echo '    };' . "\n";
echo '    ' . "\n";
echo '    fetch("bot_message.php", {' . "\n";
echo '        method: "POST",' . "\n";
echo '        headers: {' . "\n";
echo '            "Content-Type": "application/x-www-form-urlencoded",' . "\n";
echo '        },' . "\n";
echo '        body: new URLSearchParams(params)' . "\n";
echo '    })' . "\n";
echo '    .then(response => response.json())' . "\n";
echo '    .then(data => {' . "\n";
echo '        console.log("机器人消息发送结果:", data);' . "\n";
echo '    })' . "\n";
echo '    .catch(error => {' . "\n";
echo '        console.error("错误:", error);' . "\n";
echo '    });' . "\n";
echo '}' . "\n";
echo '</pre>';

/**
 * 参数说明
 */
echo "<h3>参数说明</h3>";
echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
echo "<tr><th>参数</th><th>类型</th><th>必填</th><th>说明</th></tr>";
echo "<tr><td>trade_no</td><td>string</td><td>是</td><td>订单号</td></tr>";
echo "<tr><td>start</td><td>int</td><td>否</td><td>开始时间（毫秒时间戳），0表示从数据库获取</td></tr>";
echo "<tr><td>end</td><td>int</td><td>是</td><td>结束时间（毫秒时间戳）</td></tr>";
echo "<tr><td>message</td><td>string</td><td>否</td><td>自定义消息内容，默认为'订单状态更新通知'</td></tr>";
echo "<tr><td>chat_id</td><td>string</td><td>否</td><td>接收消息的聊天ID，默认为老板的chat_id</td></tr>";
echo "</table>";

/**
 * 返回结果说明
 */
echo "<h3>返回结果说明</h3>";
echo "<p><strong>成功时：</strong></p>";
echo "<pre>";
echo '{"code":1,"msg":"付款成功","bot_message":"机器人消息发送成功","processing_time":"10.5秒"}';
echo "</pre>";
echo "<p><strong>失败时：</strong></p>";
echo "<pre>";
echo '{"code":-1,"msg":"未付款","bot_message":"机器人错误消息已发送"}';
echo "</pre>";
?>

