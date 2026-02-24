<?php



/**
 * 字符串类
 */
class Http
{



    /**
     * 发送一个POST请求
     * @param string $url     请求URL
     * @param array  $params  请求参数
     * @param array  $options 扩展参数
     * @return mixed|string
     */
    public static function post($url, $params = [], $options = [])
    {
   
        $req = self::sendRequest($url, $params, 'POST', $options);
//        return $req['ret'] ? $req['msg'] : '';
        return $req['msg'];
    }

    /**
     * 发送一个GET请求
     * @param string $url     请求URL
     * @param array  $params  请求参数
     * @param array  $options 扩展参数
     * @return mixed|string
     */
    public static function get($url, $params = [], $options = [])
    {
        $req = self::sendRequest($url, $params, 'GET', $options);
        return $req['ret'] ? $req['msg'] : '';
    }

    /**
     * CURL发送Request请求,含POST和REQUEST
     * @param string $url     请求的链接
     * @param mixed  $params  传递的参数
     * @param string $method  请求的方法
     * @param mixed  $options CURL的参数
     * @return array
     */
   public static function sendRequest($url, $params = [], $method = 'POST', $options = [])
{
    $method = strtoupper($method);
    $isJson = isset($options['json']) && $options['json'] === true;
    $headers = isset($options['headers']) ? $options['headers'] : [];

    // 处理协议
    $protocol = strtolower(parse_url($url, PHP_URL_SCHEME));

    // 处理参数格式
    $queryData = $isJson ? json_encode($params) : (is_array($params) ? http_build_query($params) : $params);

    $ch = curl_init();

    // 默认配置
    $defaults = [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HEADER => false,
        CURLOPT_CONNECTTIMEOUT => $options['connect_timeout'] ?? 5,
        CURLOPT_TIMEOUT => $options['timeout'] ?? 30,
        CURLOPT_USERAGENT => $options['user_agent'] ?? 'Mozilla/5.0 PHP Curl Client',
    ];

    // 请求方法处理
    if ($method === 'GET') {
        $getUrl = $queryData ? $url . (stripos($url, '?') !== false ? '&' : '?') . $queryData : $url;
        $defaults[CURLOPT_URL] = $getUrl;
    } else {
        if ($method === 'POST') {
            $defaults[CURLOPT_POST] = true;
        } else {
            $defaults[CURLOPT_CUSTOMREQUEST] = $method;
        }
        $defaults[CURLOPT_POSTFIELDS] = $queryData;
    }

    // 默认 Headers
    $defaultHeaders = [];
    if ($isJson) {
        $defaultHeaders[] = 'Content-Type: application/json';
    } else {
        $defaultHeaders[] = 'Content-Type: application/x-www-form-urlencoded';
    }
    $defaultHeaders[] = 'Expect:'; // 防止 100-continue
    $mergedHeaders = array_merge($defaultHeaders, $headers);
    $defaults[CURLOPT_HTTPHEADER] = $mergedHeaders;

    // SSL 设置
    if ($protocol === 'https') {
        $defaults[CURLOPT_SSL_VERIFYPEER] = $options['ssl_verify_peer'] ?? false;
        $defaults[CURLOPT_SSL_VERIFYHOST] = $options['ssl_verify_host'] ?? false;
    }

    // 调试模式
    $debug = $options['debug'] ?? false;
    if ($debug) {
        $defaults[CURLOPT_VERBOSE] = true;
    }

    // 合并并执行请求
    curl_setopt_array($ch, $defaults);
    $response = curl_exec($ch);
    $error = curl_error($ch);
    $errno = curl_errno($ch);
    $info = curl_getinfo($ch);
    curl_close($ch);

    if ($response === false || !empty($error)) {
        return [
            'ret' => false,
            'errno' => $errno,
            'msg' => $error,
            'info' => $info,
        ];
    }

    return [
        'ret' => true,
        'msg' => $response,
        'info' => $info,
    ];
}


    /**
     * 异步发送一个请求
     * @param string $url    请求的链接
     * @param mixed  $params 请求的参数
     * @param string $method 请求的方法
     * @return boolean TRUE
     */
    public static function sendAsyncRequest($url, $params = [], $method = 'POST')
    {
        $method = strtoupper($method);
        $method = $method == 'POST' ? 'POST' : 'GET';
        //构造传递的参数
        if (is_array($params)) {
            $post_params = [];
            foreach ($params as $k => &$v) {
                if (is_array($v)) {
                    $v = implode(',', $v);
                }
                $post_params[] = $k . '=' . urlencode($v);
            }
            $post_string = implode('&', $post_params);
        } else {
            $post_string = $params;
        }
        $parts = parse_url($url);
        //构造查询的参数
        if ($method == 'GET' && $post_string) {
            $parts['query'] = isset($parts['query']) ? $parts['query'] . '&' . $post_string : $post_string;
            $post_string = '';
        }
        $parts['query'] = isset($parts['query']) && $parts['query'] ? '?' . $parts['query'] : '';
        //发送socket请求,获得连接句柄
        $fp = fsockopen($parts['host'], isset($parts['port']) ? $parts['port'] : 80, $errno, $errstr, 3);
        if (!$fp) {
            return false;
        }
        //设置超时时间
        stream_set_timeout($fp, 3);
        $out = "{$method} {$parts['path']}{$parts['query']} HTTP/1.1\r\n";
        $out .= "Host: {$parts['host']}\r\n";
        $out .= "Content-Type: application/x-www-form-urlencoded\r\n";
        $out .= "Content-Length: " . strlen($post_string) . "\r\n";
        $out .= "Connection: Close\r\n\r\n";
        if ($post_string !== '') {
            $out .= $post_string;
        }
        fwrite($fp, $out);
        //不用关心服务器返回结果
        //echo fread($fp, 1024);
        fclose($fp);
        return true;
    }

    /**
     * 发送文件到客户端
     * @param string $file
     * @param bool   $delaftersend
     * @param bool   $exitaftersend
     */
    public static function sendToBrowser($file, $delaftersend = true, $exitaftersend = true)
    {
        if (file_exists($file) && is_readable($file)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment;filename = ' . basename($file));
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate, post-check = 0, pre-check = 0');
            header('Pragma: public');
            header('Content-Length: ' . filesize($file));
            ob_clean();
            flush();
            readfile($file);
            if ($delaftersend) {
                unlink($file);
            }
            if ($exitaftersend) {
                exit;
            }
        }
    }
}
