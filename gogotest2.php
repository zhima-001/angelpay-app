<?php
// 第三方API的URL
$url = 'https://m.xianjindaijiedaixitong.top/Repay/loans'; 
//$url = 'https://m.xianjindaijiedaixitong.top/Index/ajaxSignIn';
// 要发送的数据（以数组形式）
$data = [
    'username' => '9176131111',

];

// 初始化 cURL 会话
$ch = curl_init();

// 设置cURL选项
curl_setopt($ch, CURLOPT_URL, $url);              // 请求的URL
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);    // 返回响应结果，而不是直接输出
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);    // 允许cURL跟随重定向
curl_setopt($ch, CURLOPT_HEADER, false);           // 不返回头信息
curl_setopt($ch, CURLOPT_TIMEOUT, 30);             // 设置超时（单位：秒）

// 设置为POST请求并添加数据
curl_setopt($ch, CURLOPT_POST, true);              // 设置请求方法为POST
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data)); // 发送的数据，必须使用url编码

// 可选设置，添加请求头（例如认证、用户代理等）
$headers = [
    'Authorization: Bearer your_access_token',  // 如果API需要认证令牌
    'Content-Type: application/x-www-form-urlencoded',  // 发送表单数据
    'Accept: application/json'                   // 设置返回数据格式
];
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

// 执行cURL请求并获取响应
$response = curl_exec($ch);

// 检查请求是否成功
if(curl_errno($ch)) {
    // 如果请求失败，输出错误信息
    echo 'Curl error: ' . curl_error($ch);
} else {
    // 如果请求成功，输出响应结果
    echo 'Response: ' . $response;
}

// 关闭cURL会话
curl_close($ch);
?>
