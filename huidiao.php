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
        
        //$req = self::sendRequest3($url, $params);
        //var_dump($req);
        $req = self::sendRequest2($url, $params, 'POST', $options);
        //var_dump($req);
      //return $req['ret'] ? $req['msg'] : '';
        return $req;
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
 public static  function sendRequest3($remote_server, $post_string) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $remote_server);
    curl_setopt($ch, CURLOPT_POSTFIELDS, 'mypost=' . $post_string);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, "snsgou.com's CURL Example beta");
    $data = curl_exec($ch);
    curl_close($ch);
 
    return $data;
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
        $protocol = substr("https://".$url, 0, 5);
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
   public static function sendRequest2($url,$post_data){
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
    
    // 检查 URL 是否已经包含协议
    if(strpos($url, 'http://') === 0 || strpos($url, 'https://') === 0){
        // URL 已经包含协议，直接使用
        $url2 = $url;
    } else {
        // URL 不包含协议，根据当前请求判断添加
        $http_type = self::is_https();
        if($http_type){
            $url2 = "https://".$url;
        } else {
            $url2 = "http://".$url;
        }
    }
    
    $result = file_get_contents($url2, false, $context);
 
    return $result;  
  }
  /**
     * PHP判断当前协议是否为HTTPS
     */
   public static function is_https() {
      if ( !empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off') {
          return true;
      } elseif ( isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https' ) {
          return true;
      } elseif ( !empty($_SERVER['HTTP_FRONT_END_HTTPS']) && strtolower($_SERVER['HTTP_FRONT_END_HTTPS']) !== 'off') {
          return true;
      }
      return false;
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
     public function __construct()
    { 
    //  $dbHost = "127.0.0.1";  //不用改
    //  $dbName = "tsgegekgkh_f";  //数据库名
    //  $dbUser = "tsgegekgkH_F"; //数据库登陆名
    //  $dbPassword = "k2AxY8ZRpYkBNYLy"; //数据库登陆名密码
     include_once('config.php');
     $dbHost = $dbconfig['host'];  //不用改
     $dbName = $dbconfig['dbname'];  //数据库名
     $dbUser =  $dbconfig['user']; //数据库登陆名
     $dbPassword = $dbconfig['pwd']; //数据库登陆名密码
     $this->pdo = new PDO("mysql:host=" . $dbHost . ";dbname=" . $dbName, $dbUser, $dbPassword, array(PDO::ATTR_PERSISTENT => true)); 
    }
    function is_json($string) {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }
    public function index(){
    
        $qingqiu  = $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
        
        // 先读取一次 php://input 并保存（只能读取一次）
        $raw_input = file_get_contents('php://input');
        file_put_contents('./demo2.txt', $raw_input . "\n---\n" . print_r($_REQUEST, true));
        //var_dump($raw_input);
        // 初始化参数数组，先合并 GET 参数
        $param_data = $_GET;
        
        // 处理 POST 数据
        if(!empty($raw_input)){
            // 检查是否是 JSON 格式
            if($this->is_json($raw_input)){
                // 解析 JSON 数据并合并到参数数组
                $json_data = json_decode($raw_input, true);
                if($json_data !== null && is_array($json_data)){
                    $param_data = array_merge($param_data, $json_data);
                }
            } else {
                // 如果不是 JSON，可能是 form-data，使用 $_POST
                if(!empty($_POST)){
                    $param_data = array_merge($param_data, $_POST);
                }
            }
        } else {
            // 如果 php://input 为空，使用 $_POST
            if(!empty($_POST)){
                $param_data = array_merge($param_data, $_POST);
            }
        }

        //判断是否是get请求回调：
        if(strpos($qingqiu, '?')){
            $qingqius =  explode("?",$qingqiu);
            $qingqiu = $qingqius['0'];
        }
    
        $trade_no_ul = explode("/",$qingqiu);
        $trade_no = $trade_no_ul['2'];
        $sql2 = "select channel from pay_order where trade_no='" . $trade_no . "'";
        $order_query2 = $this->pdo->query($sql2);
        $order_info2 = $order_query2->fetchAll();
    
        if(empty($order_info2) || empty($order_info2[0]['channel'])){
            exit("error:订单不存在");
        }
    
        $channel = $order_info2[0]['channel'];
        $sql3 = "select plugin from pay_channel where id='" . $channel . "'";
        $order_query23 = $this->pdo->query($sql3);
        $order_info23 = $order_query23->fetchAll();
        
        if(empty($order_info23) || empty($order_info23[0]['plugin'])){
            exit("error:支付通道不存在");
        }
        
        $lujing = $order_info23[0]['plugin'];
        
        $new_url = "https://".$_SERVER['HTTP_HOST']."/pay/".$lujing."/notify/".$trade_no.'/';
       
        $result = Http::post($new_url, $param_data); 

        if(trim($result) == "success"){
            echo "success";
        }elseif(trim($result) == "OK"){
             echo "OK";
        }elseif(trim($result) == "SUCCESS"){
             echo "SUCCESS";
        }elseif(trim($result) == "ok"){
             echo "ok";
        }else{
            exit("error:".trim($result));
        }
        exit();
    }
}
$oen = new five();
$oen->index();

?>