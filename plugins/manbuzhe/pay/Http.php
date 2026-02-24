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
     * 异步发送一个请求
     * @param string $url    请求的链接
     * @param mixed  $params 请求的参数
     * @param string $method 请求的方法
     * @return boolean TRUE
     */
public static function sendRequest($url, $params = [], $method = 'POST', $options = [])
{
    $method = strtoupper($method);
    $protocol = substr($url, 0, 5);

    $ch = curl_init();
    $defaults = [];
    if ('GET' == $method) {
        $query_string = is_array($params) ? http_build_query($params) : $params;
        $geturl = $query_string ? $url . (stripos($url, "?") !== false ? "&" : "?") . $query_string : $url;
        $defaults[CURLOPT_URL] = $geturl;
    } else {
        $defaults[CURLOPT_URL] = $url;
        if ($method == 'POST') {
            $defaults[CURLOPT_POST] = 1;
        } else {
            $defaults[CURLOPT_CUSTOMREQUEST] = $method;
        }

        // 强制设置为 form-data 格式
        if (is_array($params)) {
            $postfields = [];
            foreach ($params as $key => $value) {
                // 如果是文件字段，必须使用 CURLFile
                if (file_exists($value)) {
                    $postfields[$key] = new CURLFile($value);
                } else {
                    $postfields[$key] = $value;
                }
            }
            $defaults[CURLOPT_POSTFIELDS] = $postfields;
        } else {
            $defaults[CURLOPT_POSTFIELDS] = $params;
        }
    }

    $defaults[CURLOPT_HEADER] = false;
    $defaults[CURLOPT_USERAGENT] = "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/45.0.2454.98 Safari/537.36";
    $defaults[CURLOPT_FOLLOWLOCATION] = true;
    $defaults[CURLOPT_RETURNTRANSFER] = true;
    $defaults[CURLOPT_CONNECTTIMEOUT] = 3;
    $defaults[CURLOPT_TIMEOUT] = 30;

    // 禁用 100-continue
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:'));

    if ('https' == $protocol) {
        $defaults[CURLOPT_SSL_VERIFYPEER] = false;
        $defaults[CURLOPT_SSL_VERIFYHOST] = false;
    }

    curl_setopt_array($ch, (array)$options + $defaults);

    $ret = curl_exec($ch);
    $err = curl_error($ch);

    if (false === $ret || !empty($err)) {
        $errno = curl_errno($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);
        return [
            'ret'   => false,
            'errno' => $errno,
            'msg'   => $err,
            'info'  => $info,
        ];
    }
    curl_close($ch);
    return [
        'ret' => true,
        'msg' => $ret,
    ];
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
