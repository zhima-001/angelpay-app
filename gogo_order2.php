<?php


class Http
{

    /**
     * 发送一个POST请求
     * @param string $url 请求URL
     * @param array $params 请求参数
     * @param array $options 扩展参数
     * @return mixed|string
     */
    public static function post($url, $params = [], $options = [])
    {
        $req = self::sendRequest($url, $params, 'POST', $options);
        return $req['msg'];
    }

    public static function http_post_data_two($url, $data_string)
    {

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


    /**
     * 发送一个GET请求
     * @param string $url 请求URL
     * @param array $params 请求参数
     * @param array $options 扩展参数
     * @return mixed|string
     */
    public static function get($url, $params = [], $options = [])
    {
        $req = self::sendRequest($url, $params, 'GET', $options);
        return $req['ret'] ? $req['msg'] : '';
    }

    /**
     * CURL发送Request请求,含POST和REQUEST
     * @param string $url 请求的链接
     * @param mixed $params 传递的参数
     * @param string $method 请求的方法
     * @param mixed $options CURL的参数
     * @return array
     */
    public static function sendRequest($url, $params = [], $method = 'POST', $options = [])
    {
        $method = strtoupper($method);
        $protocol = substr($url, 0, 5);
        $query_string = is_array($params) ? http_build_query($params) : $params;

        $ch = curl_init();
        $defaults = [];
        if ('GET' == $method) {
            $geturl = $query_string ? $url . (stripos($url, "?") !== false ? "&" : "?") . $query_string : $url;
            $defaults[CURLOPT_URL] = $geturl;
        } else {
            $defaults[CURLOPT_URL] = $url;
            if ($method == 'POST') {
                $defaults[CURLOPT_POST] = 1;
            } else {
                $defaults[CURLOPT_CUSTOMREQUEST] = $method;
            }
            $defaults[CURLOPT_POSTFIELDS] = $query_string;
        }

        $defaults[CURLOPT_HEADER] = false;
        $defaults[CURLOPT_USERAGENT] = "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/45.0.2454.98 Safari/537.36";
        $defaults[CURLOPT_FOLLOWLOCATION] = true;
        $defaults[CURLOPT_RETURNTRANSFER] = true;
        $defaults[CURLOPT_CONNECTTIMEOUT] = 3;
        $defaults[CURLOPT_TIMEOUT] = 30;

        // disable 100-continue
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
                'ret' => false,
                'errno' => $errno,
                'msg' => $err,
                'info' => $info,
            ];
        }
        curl_close($ch);
        return [
            'ret' => true,
            'msg' => $ret,
        ];
    }

    /**
     * 异步发送一个请求
     * @param string $url 请求的链接
     * @param mixed $params 请求的参数
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
     * @param bool $delaftersend
     * @param bool $exitaftersend
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

class five
{
    private $token = '5352979058:AAHhTzSZ2WqUAt0agWErxhxgERIYBq178xc';  //token
    private $ownerAddress = "TVTEdbTeBaTQjXccezvsqtNDbRb3zjJhb9";
    private $link = "";

    private $telegram;
    private $pdo;

    public function __construct()
    {
        $token = $this->token;
        $this->link = 'https://api.telegram.org/bot' . $token . '';


        /*$dbHost = "127.0.0.1";
        $dbName = "tianshi_com";
        $dbUser = "tianshi_com";
        $dbPassword = "aCdSCd7BAhDhmiWT";*/
        $dbHost = "127.0.0.1";
        $dbName = "tianshifacaiyyds";
        $dbUser = "TianshiFacaiyyds";
        $dbPassword = "aCdSCd7BAhDhmiWT";

        $this->pdo = new PDO("mysql:host=" . $dbHost . ";dbname=" . $dbName, $dbUser, $dbPassword, array(PDO::ATTR_PERSISTENT => true));


    }


    public function index()
    {

        ///rate_30  30分钟在跑通道商户成率如下:
        ///rate_60  60分钟在跑通道商户成率如下:
        ///rate_06-25 20:22#06-25 21:22 就是查询这个时间段的通道成率

        //$chatid = "-768730678";
        $message = "/alluserrate60";
        $chatid_arr = array("-1001741459913");
        //$chatid_arr = array("-1001406020780");  //如果有多个  array("-1001406020780","xxx","yyy"),;
        for ($i = 0; $i < count($chatid_arr); $i++) {
            $chatid = $chatid_arr[$i];
            /*if ($chatid != "-768730678") {
               //已經綁定群了：
                   $parameter = array(
                       'chat_id' => $chatid,
                       'parse_mode' => 'HTML',
                       'text' => "该群暂未绑定查询通道成功命令"
                   );
               $this->http_post_data('sendMessage', json_encode($parameter));
               exit();
           }*/
            $rate = explode("/alluserrate", $message);
            if (count($rate) <= 1) {
                $parameter = array(
                    'chat_id' => $chatid,
                    'parse_mode' => 'HTML',
                    'text' => "输入格式错误：/userrate时间"
                );
                $this->http_post_data('sendMessage', json_encode($parameter));
                exit();
            }
            if (strpos($rate[1], '#') !== false) {
                $pp = "🎈" . $rate[1] . "在跑通道成率如下:";
                $new_rate = explode("#", $rate[1]);
                $one_time = trim($new_rate[0]);
                $two_time = trim($new_rate[1]);
                //06-25 20:22#06-25 21:22
                $now_time = date('Y') . "-" . $one_time . ":00";
                $end_time = date('Y') . "-" . $two_time . ":00";
                $find_sql = "SELECT type,channel,money,status from pay_order where  addtime between '" . $now_time . "' and '" . $end_time . "'";
            } elseif (strpos($rate[1], '-') !== false) {


                $pp = "🎈" . date('Y-m-d') . " " . $rate[1] . "在跑通道成率如下:";
                $new_rate = explode("#", $rate[1]);
                $one_time = trim($new_rate[0]);
                $two_time = trim($new_rate[1]);

                $now_time = date('Y-m-d') . " " . $one_time . ":00:00";
                $end_time = date('Y-m-d') . " " . $two_time . ":00";
                $find_sql = "SELECT type,channel,money,status,uid from pay_order where  addtime between '" . $now_time . "' and '" . $end_time . "'";
            } else {
                $pp = "🎈" . $rate[1] . "分钟在跑通道成率如下:";
                $now_time = date("Y-m-d H:i:s", time() - $rate[1] * 60);
                $end_time = date("Y-m-d H:i:s", time());
                $find_sql = "SELECT type,channel,money,status,uid from pay_order where  addtime between '" . $now_time . "' and '" . $end_time . "'";
            }


            $channel = [];
            $sql1 = "SELECT id,name FROM pay_channel WHERE status=1";

            $q = $this->pdo->query($sql1);
            $rs = $q->fetchAll();

            foreach ($rs as $row) {
                $channel[$row['id']] = $row['name'];
            }


            $user_g = [];
            $sql2 = "SELECT uid,username FROM pay_user ";

            $q2 = $this->pdo->query($sql2);
            $rs2 = $q2->fetchAll();

            foreach ($rs2 as $row2) {
                $user_g[$row2['uid']] = $row2['username'];
            }


            unset($rs);
            $order_channel_fukuan = array(); //付款
            $order_channel_all = array();//所有

            $order_channel_all_user = array();
            $order_channel_all_user_fukuan = array();

            foreach ($channel as $id => $type) {
                $order_channel_fukuan[$id] = 0;
                $order_channel_all[$id] = 0;
            }


            $rs = $this->pdo->query($find_sql);
            $row = $rs->fetchAll();

            foreach ($row as $ks => $cvs) {

                $order_channel_all[$cvs['channel']] += 1;

                //初始化用户：
                $order_channel_all_user[$cvs['channel']][$cvs['uid']] = 0;
                $order_channel_all_user_fukuan[$cvs['channel']][$cvs['uid']] = 0;

                if ($cvs['status'] == "1") {
                    $order_channel_fukuan[$cvs['channel']] += 1;
                }
            }
            foreach ($row as $ks => $cvs) {
                //用户：
                $order_channel_all_user[$cvs['channel']][$cvs['uid']] += 1;

                if ($cvs['status'] == "1") {
                    $order_channel_all_user_fukuan[$cvs['channel']][$cvs['uid']] += 1;
                }
            }


            $order_channel = array();
            foreach ($order_channel_all as $k => $sv) {

                $order_channel[$k] = round(($order_channel_fukuan[$k] / $sv) * 100, 2);
            }


            $message = "";
            $message .= $pp . "\n\r\n\r";
            foreach ($order_channel as $key => $value3) {
                if ($value3 > 0) {
                    $message .= "✅" . $channel[$key] . " : \n\r" . "💰成率：" . $value3 . "%\n\r\n\r";
                    $sqw = 1;
                    /*foreach ($order_channel_all_user[$key] as $ke => $sq) {
                        $ssqsa = round(($order_channel_all_user_fukuan[$key][$ke] / $sq) * 100, 2);


                        $message .= $sqw . ".🧑‍💻" . $user_g[$ke] . "-" . $ke . "-成率：" . $ssqsa . "\n\r\n\r";
                        $sqw++;
                    }*/
                }

            }


            $parameter = array(
                'chat_id' => $chatid,
                'parse_mode' => 'HTML',
                'text' => $message

            );
            $this->http_post_data('sendMessage', json_encode($parameter));

        }


        return true;


    }


    //post的array数据请求
    public function send_post($url, $post_data)
    {

        $postdata = http_build_query($post_data);
        $options = array(
            'http' => array(
                'method' => 'POST',
                'header' => 'Content-type:application/x-www-form-urlencoded',
                'content' => $postdata,
                'timeout' => 15 * 60 // 超时时间（单位:s）
            )
        );
        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);

        return $result;
    }

    //post的json数据请求
    public function http_post_data($action, $data_string)
    {
        //这里，
        /*$sql= "insert into wolive_tests (content) values ('".json_encode($data)."')";
        $this->pdo->exec($sql);*/

        $url = $this->link . "/" . $action . "?";
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


}

$oen = new five();
$oen->index();

?>
