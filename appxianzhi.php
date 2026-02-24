<?php
function is_wechat_browser() {
    if (isset($_SERVER['HTTP_USER_AGENT'])) {
        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        if (strpos($user_agent, 'MicroMessenger') !== false || strpos($user_agent, 'WindowsWechat') !== false) {
            return true;
        }
    }
    return false;
}
function isAlipay() {
    if (isset($_SERVER['HTTP_USER_AGENT'])) {
        $userAgent = $_SERVER['HTTP_USER_AGENT'];
        // 检查用户代理字符串中是否包含“AlipayClient”的标识符
        if (strpos($userAgent, 'AlipayClient') !== false) {
            return true;
        }
    }
    return false;
}
function isQQ() {
    if (isset($_SERVER['HTTP_USER_AGENT'])) {
        $userAgent = $_SERVER['HTTP_USER_AGENT'];
        // 检查用户代理字符串中是否包含“QQBrowser”或“QQ”的标识符
        if (strpos($userAgent, 'QQBrowser') !== false || strpos($userAgent, 'QQ/') !== false) {
            return true;
        }
    }
    return false;
}

$wechat_browser = is_wechat_browser();
$alipay_browser = isAlipay();
$qq_browser = isQQ();
if ($wechat_browser || $alipay_browser || $qq_browser) {
     ?>
     <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>请使用浏览器打开</title>
    <style>
        #wechat-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            z-index: 1000;
            color: white;
            text-align: center;
            font-size: 18px;
        }
        #wechat-overlay img {
            margin-top: 20%;
        }
        #wechat-overlay p {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div id="wechat-overlay">
        <img src="./photo_2024-05-28_08-55-11.jpg" alt="Open in Browser">
        <p>请点击右上角选择在浏览器中打开~~</p>
    </div>

    <!-- Your actual page content here -->

    <script>
        document.addEventListener("DOMContentLoaded", function() {
             document.getElementById('wechat-overlay').style.display = 'block';
          
        });
    </script>
</body>
</html>
   
 <?php 

} else {
    require './includes/common.php';
    /**
     * 递归解码函数
     *
     * @param mixed $value 要解码的值
     * @return mixed 解码后的值
     */
   function recursiveUrldecode($value) {
    if (is_array($value)) {
        return array_map('recursiveUrldecode', $value);
    } else {
        return urldecode($value);
    }
}
/**
 * 将数组构建为查询字符串
 *
 * @param array $query_params 查询参数数组
 * @return string 查询字符串
 */
function buildQueryString($query_params) {
    $query_strings = [];
    foreach ($query_params as $key => $value) {
        if (is_array($value)) {
            foreach ($value as $sub_value) {
                $query_strings[] = $key . '=' . urlencode($sub_value);
            }
        } else {
            $query_strings[] = $key . '=' . urlencode($value);
        }
    }
    return implode('&', $query_strings);
}
    /**
     * 完整的 URL 递归解码函数
     *
     * @param string $encoded_url 要解码的URL
     * @return string 完全解码后的URL
     */
    function fullyDecodeUrl($encoded_url) {
    // 初步解码
    $decoded_url = urldecode($encoded_url);
    
    // 解析 URL 中的查询字符串
    $query_string = parse_url($decoded_url, PHP_URL_QUERY);
    parse_str($query_string, $query_params);
    
    // 对所有参数进行递归解码
    $decoded_params = recursiveUrldecode($query_params);
    
    // 构建查询字符串
    $query_string = buildQueryString($decoded_params);
    
    // 构建最终解码后的 URL
    $scheme = parse_url($decoded_url, PHP_URL_SCHEME);
    $host = parse_url($decoded_url, PHP_URL_HOST);
    $path = parse_url($decoded_url, PHP_URL_PATH);
    $final_url = $scheme . '://' . $host . $path . '?' . $query_string;
    
    return $final_url;
}
    
    $order_sn = $_GET['order_sn'];
    if(empty($order_sn)){
        echo "请返回原网站重新下单！";
        exit();
    }
    $userrow=$DB->query("SELECT `shangyouzhifu` FROM `pre_order` WHERE `trade_no`='{$order_sn}' LIMIT 1")->fetch();
    if($userrow){
        // 调用函数并输出结果
        $real_url = fullyDecodeUrl($userrow['shangyouzhifu']);
     
        echo "<script>window.location.href='".$real_url."';</script>";
        exit();
    }
}

    

?>