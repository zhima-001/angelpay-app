<?php

namespace lib;

class Zhifu
{

     public static function httpPost($url, $data = [], $timeout = 30)
    {
        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($data),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT => $timeout,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/x-www-form-urlencoded',
                'Expect:'
            ],
        ]);

        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            return [
                'success' => false,
                'error' => $error,
            ];
        }

        return [
            'success' => true,
            'response' => $response,
        ];
    }
    static public function getqing($url){
          // 设置超时时间为3秒
        $timeout = 3;
        // 创建一个cURL资源
        $ch = curl_init();
        // 设置请求的URL
        curl_setopt($ch, CURLOPT_URL, $url);
        // 设置超时时间
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        // 执行HTTP GET请求
        $response = curl_exec($ch);
        // 检查是否超时或请求被中断
        if ($response === false) {
            // 获取错误信息
            $error = curl_error($ch);
            // 检查是否是超时错误
            if (strpos($error, "Operation timed out") !== false) {
                // 输出"1"
                return  "1";
            }
            } else {
                // 输出响应内容
                return  $response;
            }
            // 关闭cURL资源
            curl_close($ch);
 }

    static public function csasahangss($type = 0, $submitData = "", $zhifushang = "", $tusq = "") {
        include "./cron_jiqi.php"; // 这里假设定义了 $xiadan_chat, $yichang_chat, $huidiao_chat, $token

        // 根据类型选择 chat_id 和消息内容
        if ($type == "0") {
            $chatid = $xiadan_chat;
            $text = $zhifushang . $tusq . "用户下单数据：\r\n" . $submitData; 
        } elseif ($type == "2") {
            $chatid = $yichang_chat;
            $text = $zhifushang . $tusq . "异常信息：\r\n" . $submitData;
        } else {
            $chatid = $huidiao_chat;
            $text = $zhifushang . $tusq . "下单后商户返回：\r\n" . $submitData;
        }

        // Telegram API 请求参数
        $parameter = array(
            'chat_id' => $chatid,
            'parse_mode' => 'HTML',
            'text' => $text
        );

        $data_string = json_encode($parameter);
        $action = "sendMessage";
        $url = 'https://api.telegram.org/bot' . $token . '/' . $action;

        // 初始化 cURL
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json; charset=utf-8',
            'Content-Length: ' . strlen($data_string)
        ));

        // 超时设置：连接最多 1 秒，总请求最多 3 秒
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 3);

        // 直接返回结果，不输出到浏览器
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // HTTP 状态码 >= 400 时直接返回 false
        curl_setopt($ch, CURLOPT_FAILONERROR, true);

        // 执行请求
        $result = curl_exec($ch);
        $error = curl_error($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // 返回结果数组，方便判断
        return array(
            'success' => ($result !== false && $http_code == 200),
            'http_code' => $http_code,
            'response' => $result,
            'error' => $error
        );
    }

    static public function http_post_datasqwa($action, $data_string)
    {

        $token = "5313902856:AAEIQRhZIH6DOc2itLEig_D9ojdtOCkiAgY";
        $link =  'https://api.telegram.org/bot' . $token . '';
        $url = $link . "/" . $action . "?";
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_POST, 1);

        curl_setopt($ch, CURLOPT_URL, $url);

        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);

        curl_setopt($ch, CURLOPT_HTTPHEADER, array(

                'Content-Type: application/json; charset=utf-8',

                'Content-Length: ' . strlen($data_string))

        );

        ob_start();

        curl_exec($ch);

        $return_content = ob_get_contents();

        //echo $return_content."


        ob_end_clean();

        $return_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        // return array($return_code, $return_content);

        return $return_content;

    }

    static public function sign($params,$channel_info)
    {
       /* var_dump($order);
        exit();*/

        ksort($params);        //将参数数组按照参数名ASCII码从小到大排序
        foreach ($params as $key => $item) {
            if (!empty($item)) {         //剔除参数值为空的参数
                $newArr[] = $key . '=' . $item;     // 整合新的参数数组
            }
        }
        $stringA = implode("&", $newArr);         //使用 & 符号连接参数
        $stringSignTemp = $stringA . $channel_info['appkey'];
        $stringSignTemp = MD5($stringSignTemp);       //将字符串进行MD5加密
        $sign = strtolower($stringSignTemp);      //将所有字符转换为大写

        return $sign;
    }
    static public function sign2($params,$channel_info)
    {
        ksort($params);        //将参数数组按照参数名ASCII码从小到大排序
        foreach ($params as $key => $item) {
            if (!empty($item)) {         //剔除参数值为空的参数
                $newArr[] = $key . '=' . $item;     // 整合新的参数数组
            }
        }
        $stringA = implode("&", $newArr);         //使用 & 符号连接参数
        $stringSignTemp = $stringA ."&key=". $channel_info['appkey'];

        $stringSignTemp = MD5($stringSignTemp);       //将字符串进行MD5加密
        $sign = strtoupper($stringSignTemp);      //将所有字符转换为大写

        return $sign;

    }


    static public function send_post($post_data) {
      $url = "http://www.tx-pay.com/pay/index";

      $postdata = http_build_query($post_data);

      $options = array(

        'http' => array(

          'method' => 'POST',

          'header' => 'Content-type:application/x-www-form-urlencoded,Content-Length: .strlen($query)',

          'content' => $postdata,

          'timeout' => 5 // 超时时间（单位:s）

        )

     );

      $context = stream_context_create($options);

      $result  = file_get_contents($url, false, $context);

      return $result;

    }
    static public function send_post2($postdata) {
      $url = "http://www.sftsvip.com/api/mch/unifiedorder";

      //$postdata = http_build_query($post_data);

      $options = array(

        'http' => array(

          'method' => 'POST',

          'header' => 'Content-type:application/x-www-form-urlencoded,Content-Length: .strlen($query)',

          'content' => $postdata,

          'timeout' => 5 // 超时时间（单位:s）

        )

     );

      $context = stream_context_create($options);

      $result  = file_get_contents($url, false, $context);

      return $result;

    }


    static public function http_post_data($data_string) {
        $url = "http://www.sftsvip.com/api/mch/unifiedorder";
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_POST, 1);

        curl_setopt($ch, CURLOPT_URL, $url);

        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);

        curl_setopt($ch, CURLOPT_HTTPHEADER, array(

            'Content-Type: application/json; charset=utf-8',

            'Content-Length: ' . strlen($data_string))

        );

        ob_start();

        curl_exec($ch);

        $return_content = ob_get_contents();

        //echo $return_content."


        ob_end_clean();

        $return_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        // return array($return_code, $return_content);

        return $return_content;

    }
    static public function http_post_data_two($url,$data_string) {

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_POST, 1);

        curl_setopt($ch, CURLOPT_URL, $url);

        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);

        curl_setopt($ch, CURLOPT_HTTPHEADER, array(

            'Content-Type: application/json; charset=utf-8',

            'Content-Length: ' . strlen($data_string))

        );

        ob_start();

        curl_exec($ch);

        $return_content = ob_get_contents();

        //echo $return_content."


        ob_end_clean();

        $return_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        // return array($return_code, $return_content);

        return $return_content;

    }
     static public function http_posts_json($url,$data_string) {

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_POST, 1);

        curl_setopt($ch, CURLOPT_URL, $url);

        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);

        curl_setopt($ch, CURLOPT_HTTPHEADER, array(

            'Content-Type: application/json; charset=utf-8',

            'Content-Length: ' . strlen($data_string))

        );

        ob_start();

        curl_exec($ch);

        $return_content = ob_get_contents();

        //echo $return_content."


        ob_end_clean();

        $return_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        // return array($return_code, $return_content);

        return $return_content;

    }
    static public function getClientIP($type = 0, $adv = false) {
        global $ip;
        $type = $type ? 1 : 0;
        if ($ip !== NULL)
            return $ip[$type];
        if ($adv) {
            if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
                $pos = array_search('unknown', $arr);
                if (false !== $pos)
                    unset($arr[$pos]);
                $ip = trim($arr[0]);
            }elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
                $ip = $_SERVER['HTTP_CLIENT_IP'];
            } elseif (isset($_SERVER['REMOTE_ADDR'])) {
                $ip = $_SERVER['REMOTE_ADDR'];
            }
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        // IP地址合法验证
        $long = sprintf("%u", ip2long($ip));
        $ip = $long ? array(
            $ip,
            $long) : array(
            '0.0.0.0',
            0);
        return $ip[$type];
    }
}
