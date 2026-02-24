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
//        return $req['ret'] ? $req['msg'] : '';
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
    private $token = '';  //token
    private $link = "";
    private $chuge_userid;
    private $chaojiyonghu;
    private $jiqirenming;
    private $shuaxinchaurl;
    private $all_ming_list = array(
        /* 'tjqml', //群里面添加命令
         'tianjia_mingling', //添加命令
         'ckqml',//查看群内部命令+指令人信息+删除         【所有人】
         'delqml',//删除群内部命令+指令人信息          【所有人】
         'szml',//设置命令，内部使用，且只有晴朗有权限     【晴朗】
         'ckml',//查看命令，内部使用，且只有晴朗有权限       【晴朗】
         'dq',//查看抗投信息通道，内部使用                  【公司人】
         'dz',//对账   内部使用                              【公司人】
         '开启', //设置当前通道是否开启                  【所有人】
         '关闭', //设置当前通道是否关闭                  【所有人】
         '添加误差',  //添加通道金额的误差                     【公司人+内部群】
         '修改误差',  //修改通道金额的误差                     【公司人+内部群】
         'start',   //机器人单聊权限                      【公司人】
         'tongdao_detail', //通道详细信息                【公司人】
         'tjzfs', //添加支付商                           【公司人】
         'sczfs',//删除支付商                             【公司人】
         'yf', //修改+查看 预付金额                      【公司人+客户群】
         'bzj',//修改+查看 保证金                        【公司人+客户群】
         'tjtd', //添加通道                          【所有人】
         'cktd',//查看通道,                              【所有人】
         'sctd',//删除通道                               【所有人】*/


        // 'tjcjuserml',        //添加超级用户    仅：飞龙+晴朗账号可以操作
        // 'ckcjuserml',     // 查看超级用户    仅：飞龙+晴朗账号可以操作
        // 'delcjuser',        // 删除超级用户    仅：飞龙+晴朗账号可以操作
        // 'ckml',     //当前那些命令外放                仅：飞龙+晴朗账号可以操作
        // 'szml',     // 添加群指定人使用命令      超级用户【如何设置给别人可以拥有】
        // 'delqml',       //      删除群用户权限        超级用户【如何设置给别人可以拥有】
        'dq',           //抗投/不抗投通道信息     超级用户【如何设置给别人可以拥有】
        'dz',           //对账信息                              超级用户【如何设置给别人可以拥有】
        '添加误差',    //修改/添加误差                           超级用户【如何设置给别人可以拥有】
        '修改误差',    //修改/添加误差                           超级用户【如何设置给别人可以拥有】
        'tongdao_detail',   //通道的详细信息                          超级用户【如何设置给别人可以拥有】
        'yf',               //预付修改                                 超级用户【如何设置给别人可以拥有】
        'bzj',            //保证金修改                            超级用户【如何设置给别人可以拥有】


        'tjzfs',        // 添加支付商               超级用户【如何设置给别人可以拥有】
        'sczfs',        //删除支付商               超级用户【如何设置给别人可以拥有】
        'sctd',         //删除支付商通道      支付商群指定用户+超级用户
        'tjtd',         // 添加支付商通道      支付商群指定用户+超级用户
        'cktd',        //查看支付商通道      支付商群指定用户+超级用户
        '开启',     //       开启/关闭通道                            支付商群指定用户+超级用户
        '关闭',     //       开启/关闭通道                            支付商群指定用户+超级用户
        'jqxiugai',   //机器人私聊修改
        'jqdelete',   //机器人私聊删除
        // 'tjqml',      //添加群指定人使用命令                         支付商群指定用户+超级用户
        // 'ckqml',    //查看指定群有命令列表                            支付商群指定用户+超级用户
    );

    private $pdo;

    public function __construct()
    {

        include "cron_jiqi.php";

        $this->link = 'https://api.telegram.org/bot' . $token_td . '';
        $this->chaojiyonghu = $chaojiyonghu;
        $this->shuaxinchaurl = $shuaxinchaurl;
        /*$dbHost = "127.0.0.1";  //不用改
        $dbName = "chpay";  //数据库名
        $dbUser = "chpay"; //数据库登陆名
        $dbPassword = "RpyZXiK4DLSscRTk"; //数据库登陆名密码*/
        $this->jiqirenming = $jiqirenming_zhifushang;
        $this->pdo = new PDO("mysql:host=" . $dbHost . ";dbname=" . $dbName, $dbUser, $dbPassword, array(PDO::ATTR_PERSISTENT => true));

        /* $set_sql1 = "select * FROM pay_chaojiuser";
         $order_query2 = $this->pdo->query($set_sql1);
         $order_info2 = $order_query2->fetchAll();
         //$chuge_userid_arr = array('5177985370','982124360');
         $chuge_userid_arr = array();
         if ($order_info2) {
             foreach ($order_info2 as $ke => $ve) {
                 $chuge_userid_arr[] = $ve['user_id'];
             }
         }

         $this->chuge_userid = $chuge_userid_arr;*/


    }
    //下载远程图片 到指定目录
    public static function downloadfile($file_url, $path, $save_file_name = '')
    {
        $basepath = '/uploaded/';
        if ($path) {
            $basepath = $basepath . $path . '/';
        }
        $basepath = $basepath . date('Ymd');
        $dir_path = __DIR__  . $basepath;
        if (!is_dir($dir_path)) {
            mkdir($dir_path, 0777, true);
        }

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $file_url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);

        $file = curl_exec($ch);

        curl_close($ch);

        //传入保存文件的名称
        $filename = $save_file_name ?: pathinfo($file_url, PATHINFO_BASENAME);

        $resource = fopen($dir_path. '/'. $filename, 'a');

        fwrite($resource, $file);

        fclose($resource);

        return $basepath . '/' . $filename;
    }





    public function index()
    {


        $data = json_decode(file_get_contents('php://input'), TRUE); //读取json并对其格式化

        $sql = "insert into pay_jiqi (content) values ('" . json_encode($data) . "')";
        $this->pdo->exec($sql);

        if ($data['callback_query']) {
            $this->callback($data);
        } else {
            $chatid = $data['message']['chat']['id'];//获取chatid
            $message = $data['message']['text'];//获取message
            $userid = $data['message']['from']['id'];//获取message
            $username =$data['message']['from']['username'];//用户名称

            $file_name =  $data['message']['document']['file_name'];
            //https://api.telegram.org/file/bot<token>/<file_path>
            $file_path = "https://api.telegram.org/file/bot".$token_td."/".$file_name;

            // $this->downloadfile($file_path, 'qipa250_pic',$file_name);

            //       $parameter = array(
            //             'chat_id' => $chatid,
            //             'parse_mode' => 'HTML',
            //             'text' => $file_path
            //         );
            //         $this->http_post_data('sendMessage', json_encode($parameter));
            //         exit();
            $this->message($message, $chatid, $userid, $data,$username);

        }


    }

    public function message($message, $chatid, $userid, $data,$username)
    {


        //导入用户组下的用户： daoruyonghu_
        if (strpos($message, 'daoruyonghu') !== false) {
            $chuge_userid_arr = $this->chaojiyonghu;
            if (!in_array($userid, $chuge_userid_arr)) {
                $ids_str = implode(",",$chuge_userid_arr);
                $parameter = array(
                    'chat_id' => $chatid,
                    'parse_mode' => 'HTML',
                    'text' => "仅Tg_ID:".$ids_str."有此权限！"
                );
                $this->http_post_data('sendMessage', json_encode($parameter));
                exit();
            }


            $info = explode("###", $message);
            $info2 =  explode("_#", $info[1]);
            $yonghuzuid =$info2[0];

            $info_two = explode("\n", $info2[1]);
            unset($info_two[0]);
            unset($info_two[1]);
            $now_arr = array();
            foreach ($info_two as $k => $v) {
                $now_arr[] = $v;
            }

            foreach ($now_arr as $kpeople => $vpeople) {

                //检验权限是否存在：
                $set_sql1 = "select * FROM pay_zuren where username='" . $vpeople . "' and yonghuzu_id='".$yonghuzuid."'";
                $order_query2 = $this->pdo->query($set_sql1);
                $order_info2 = $order_query2->fetchAll();
                if (!$order_info2) {
                    $set_sql_add = "insert into pay_zuren (yonghuzu_id,username) values ('" . $yonghuzuid . "','" . $vpeople . "')";
                    $order_info_add = $this->pdo->exec($set_sql_add);
                    if ($order_info_add) {
                        $parameter = array(
                            'chat_id' => $chatid,
                            'parse_mode' => 'HTML',
                            'text' => "添加用户：" . $vpeople . " 成功！"
                        );

                        $this->http_post_data('sendMessage', json_encode($parameter));
                    } else {
                        $parameter = array(
                            'chat_id' => $chatid,
                            'parse_mode' => 'HTML',
                            'text' => "添加用户：" . $vpeople . " 失败！"
                        );

                        $this->http_post_data('sendMessage', json_encode($parameter));
                    }


                } else {
                    $parameter = array(
                        'chat_id' => $chatid,
                        'parse_mode' => 'HTML',
                        'text' => "用户：" . $vpeople . " 已经在用户组下！请勿重复添加"
                    );

                    $this->http_post_data('sendMessage', json_encode($parameter));

                }

            }
            exit();
        }
        //导入用户组下的命令列表 daorumingling
        if (strpos($message, 'daorumingling') !== false) {
            $chuge_userid_arr = $this->chaojiyonghu;
            if (!in_array($userid, $chuge_userid_arr)) {
                $ids_str = implode(",",$chuge_userid_arr);
                $parameter = array(
                    'chat_id' => $chatid,
                    'parse_mode' => 'HTML',
                    'text' => "仅Tg_ID:".$ids_str."有此权限！"
                );
                $this->http_post_data('sendMessage', json_encode($parameter));
                exit();
            }


            $info = explode("###", $message);
            $info2 =  explode("_#", $info[1]);
            $yonghuzuid =$info2[0];

            $info_two = explode("\n", $info2[1]);
            unset($info_two[0]);
            unset($info_two[1]);
            $now_arr = array();
            foreach ($info_two as $k => $v) {
                $now_arr[] = $v;
            }
            $set_sql2 = "select * FROM pay_yonghuzu where id='" . $yonghuzuid . "'";
            $order_query3 = $this->pdo->query($set_sql2);
            $order_info2 = $order_query3->fetchAll();
            if(!empty($order_info2[0]['mingling'])){
                $qllsq =explode(",",$order_info2[0]['mingling']);
            }else{
                $qllsq = array();
            }


            $gengxin_mingling = array();
            $hava_chuxian = array();
            foreach ($now_arr as $kpeople => $vpeople) {
                $gengxin_mingling[] = $vpeople;
                //检验命令是否存在：

                if (in_array($vpeople,$qllsq)) {
                    $hava_chuxian[] =  $vpeople;


                }


            }
            if(count($hava_chuxian)>0){
                $parameter = array(
                    'chat_id' => $chatid,
                    'parse_mode' => 'HTML',
                    'text' => "命令：" . implode(",",$hava_chuxian) . " 已经在用户组下！请勿重复添加"
                );

                $this->http_post_data('sendMessage', json_encode($parameter));
                exit();
            }

            $new_mingling = array_merge($qllsq,$gengxin_mingling);
            $all_mingling_arr_str = implode(",",$new_mingling);



            $set_sql = "update pay_yonghuzu set mingling='" . $all_mingling_arr_str . "' where id='" . $yonghuzuid . "'";
            $is_gengxin =  $this->pdo->exec($set_sql);
            if ($is_gengxin) {
                $msg = "成功导入所有命令!";
            } else {
                $msg = "导入所有命令!失败！";
            }
            $parameter = array(
                'chat_id' => $chatid,
                'text' => $msg,
                'parse_mode' => 'HTML',
            );
            $this->http_post_data('sendMessage', json_encode($parameter));
            exit();



            exit();
        }
        //add_usertype  添加用户组的实际操作：
        if (strpos($message, 'tianjia_yonghuzu_') !== false) {
            $chuge_userid_arr = $this->chaojiyonghu;
            if (!in_array($userid, $chuge_userid_arr)) {
                $ids_str = implode(",",$chuge_userid_arr);
                $parameter = array(
                    'chat_id' => $chatid,
                    'parse_mode' => 'HTML',
                    'text' => "仅Tg_ID:".$ids_str."有此权限！"
                );
                $this->http_post_data('sendMessage', json_encode($parameter));
                exit();
            }


            $info = explode("tianjia_yonghuzu_#", $message);
            $info_two = explode("\n", $info[1]);
            unset($info_two[0]);
            unset($info_two[1]);
            $now_arr = array();
            foreach ($info_two as $k => $v) {
                $now_arr[] = $v;
            }
            foreach ($now_arr as $kpeople => $vpeople) {

                //检验权限是否存在：
                $set_sql1 = "select * FROM pay_yonghuzu where name='" . $vpeople . "'";
                $order_query2 = $this->pdo->query($set_sql1);
                $order_info2 = $order_query2->fetchAll();
                if (!$order_info2) {
                    $set_sql_add = "insert into pay_yonghuzu (name) values ('" . $vpeople . "')";
                    $order_info_add = $this->pdo->exec($set_sql_add);
                    if ($order_info_add) {
                        $parameter = array(
                            'chat_id' => $chatid,
                            'parse_mode' => 'HTML',
                            'text' => "添加用户组：" . $vpeople . "成功！"
                        );

                        $this->http_post_data('sendMessage', json_encode($parameter));
                    } else {
                        $parameter = array(
                            'chat_id' => $chatid,
                            'parse_mode' => 'HTML',
                            'text' => "添加用户组：" . $vpeople . "失败！"
                        );

                        $this->http_post_data('sendMessage', json_encode($parameter));
                    }


                } else {
                    $parameter = array(
                        'chat_id' => $chatid,
                        'parse_mode' => 'HTML',
                        'text' => "用户组：" . $vpeople . "已经存在！请勿重复添加"
                    );

                    $this->http_post_data('sendMessage', json_encode($parameter));

                }

            }
            exit();
        }
        //添加用户组：
        if (strpos($message, '/add_usertype') !== false) {

            $chuge_userid_arr = $this->chaojiyonghu;
            if (!in_array($userid, $chuge_userid_arr)) {
                $ids_str = implode(",",$chuge_userid_arr);
                $parameter = array(
                    'chat_id' => $chatid,
                    'parse_mode' => 'HTML',
                    'text' => "仅Tg_ID:".$ids_str."有此权限！"
                );
                $this->http_post_data('sendMessage', json_encode($parameter));
                exit();
            }

            $set_sql1 = "select * FROM pay_yonghuzu";

            $order_query2 = $this->pdo->query($set_sql1);
            $order_info2 = $order_query2->fetchAll();
            if (!$order_info2) {


                $messages = "未查询到用户组信息\r\n";
                $switch_inline_query_current_msg = "#tianjia_yonghuzu_#\r\n用户组列表\r\n超级用户组\r\n客户用户组\r\n商户用户组";
                $inline_keyboard_arr3[0] = array('text' => "马上添加一个试试 ", "switch_inline_query_current_chat" => $switch_inline_query_current_msg);
                $keyboard = [
                    'inline_keyboard' => [
                        $inline_keyboard_arr3,
                    ]
                ];

                $parameter = array(
                    'chat_id' => $chatid,
                    'parse_mode' => 'HTML',
                    'text' => $messages,
                    'reply_markup' => $keyboard,
                    'disable_web_page_preview' => true,

                );

                $this->http_post_data('sendMessage', json_encode($parameter));
                exit();

            }
            $msg = "<b>用户组列表：</b>\r\n\r\n";
            $inline_keyboard_arr = array();
            foreach ($order_info2 as $key => $value) {

                // $inline_keyboard_arr[$key] = array('text' => ($key + 1), "callback_data" => "chakanyonghuzu###" . $value['id']);
                $msg .= "<b><a href='https://t.me/".$this->jiqirenming."?start=yonghu_detail" . $value['id'] . "'>" . $value['name'] . "</a></b>  <b><a href='https://t.me/".$this->jiqirenming."?start=yonghushanchu_detail" . $value['id'] . "'>删除</a></b>\r\n";

            }
            $switch_inline_query_current_msg = "#tianjia_yonghuzu_#\r\n用户组列表\r\n超级用户组\r\n客户用户组\r\n商户用户组";
            $inline_keyboard_arr3[0] = array('text' => "继续添加 ", "switch_inline_query_current_chat" => $switch_inline_query_current_msg);
            $keyboard = [
                'inline_keyboard' => [
                    $inline_keyboard_arr3,
                ]
            ];

            $parameter = array(
                "chat_id" => $chatid,
                "text" => $msg,
                "parse_mode" => "HTML",
                'reply_markup' => $keyboard,
                'disable_web_page_preview' => true,

            );
            $this->http_post_data('sendMessage', json_encode($parameter));
            exit();
        }

        //ckcjuserml  查看超级用户
        if (strpos($message, 'ckcjuserml') !== false) {
            $chuge_userid_arr = $this->chaojiyonghu;
            if (!in_array($userid, $chuge_userid_arr)) {
                $ids_str = implode(",",$chuge_userid_arr);
                $parameter = array(
                    'chat_id' => $chatid,
                    'parse_mode' => 'HTML',
                    'text' => "仅Tg_ID:".$ids_str."有此权限！"
                );
                $this->http_post_data('sendMessage', json_encode($parameter));
                exit();
            }

            $set_sql1 = "select * FROM pay_chaojiuser";

            $order_query2 = $this->pdo->query($set_sql1);
            $order_info2 = $order_query2->fetchAll();
            if (!$order_info2) {
                $parameter = array(
                    'chat_id' => $chatid,
                    'parse_mode' => 'HTML',
                    'text' => "未查询到超级用户ID信息"
                );
                $this->http_post_data('sendMessage', json_encode($parameter));
                exit();
            }
            $msg = "<b>超级用户ID(点击数字为删除操作[谨慎])：</b>\r\n\r\n";
            $inline_keyboard_arr = array();
            foreach ($order_info2 as $key => $value) {
                $msg .= ($key + 1) . " : 用户ID：   <b>" . $value['user_id'] . "</b>\r\n";
                $inline_keyboard_arr[$key] = array('text' => ($key + 1), "callback_data" => "delcjuser###" . $value['id']);

            }
            $keyboard = [
                'inline_keyboard' => [
                    $inline_keyboard_arr
                ]
            ];
            $parameter = array(
                "chat_id" => $chatid,
                "text" => $msg,
                "parse_mode" => "HTML",
                "disable_web_page_preview" => true,
                'reply_markup' => $keyboard
            );
            $this->http_post_data('sendMessage', json_encode($parameter));
            exit();
        }
        //tjcjuserml  添加超级用户 用于触发所有命令设置：
        if (strpos($message, 'tjcjuserml') !== false) {

            $chuge_userid_arr = $this->chaojiyonghu;
            if (!in_array($userid, $chuge_userid_arr)) {
                $ids_str = implode(",",$chuge_userid_arr);
                $parameter = array(
                    'chat_id' => $chatid,
                    'parse_mode' => 'HTML',
                    'text' => "仅Tg_ID:".$ids_str."有此权限！"
                );
                $this->http_post_data('sendMessage', json_encode($parameter));
                exit();
            }


            $messages = "新增超级用户格式如下：\r\n用户ID:xxxxxx\r\n";
            $switch_inline_query_current_msg = "#tianjia_chaojiuser_#\r\n超级用户ID:66645677\r\n超级用户ID:66645677\r\n超级用户ID:66645677";
            $inline_keyboard_arr3[0] = array('text' => "马上添加一个试试 ", "switch_inline_query_current_chat" => $switch_inline_query_current_msg);
            $keyboard = [
                'inline_keyboard' => [
                    $inline_keyboard_arr3,
                ]
            ];
            $parameter = array(
                'chat_id' => $chatid,
                'parse_mode' => 'HTML',
                'text' => $messages,
                'reply_markup' => $keyboard,
                'disable_web_page_preview' => true,

            );

            $this->http_post_data('sendMessage', json_encode($parameter));
            exit();


        }

        //tjcjuserml  添加超级用户的实际操作：
        if (strpos($message, 'tianjia_chaojiuser_') !== false) {
            $chuge_userid_arr = $this->chaojiyonghu;
            if (!in_array($userid, $chuge_userid_arr)) {
                $ids_str = implode(",",$chuge_userid_arr);
                $parameter = array(
                    'chat_id' => $chatid,
                    'parse_mode' => 'HTML',
                    'text' => "仅Tg_ID:".$ids_str."有此权限！"
                );
                $this->http_post_data('sendMessage', json_encode($parameter));
                exit();
            }


            $info = explode("chaojiuser_#", $message);
            $info_two = explode("\n", $info[1]);
            unset($info_two[0]);
            $now_arr = array();
            foreach ($info_two as $k => $v) {
                $user_arr = explode(":", $v);
                $now_arr[] = $user_arr[1];
            }


            foreach ($now_arr as $kpeople => $vpeople) {

                //检验权限是否存在：
                $set_sql1 = "select * FROM pay_chaojiuser where user_id='" . $vpeople . "'";
                $order_query2 = $this->pdo->query($set_sql1);
                $order_info2 = $order_query2->fetchAll();
                if (!$order_info2) {
                    $set_sql_add = "insert into pay_chaojiuser (user_id) values ('" . $vpeople . "')";
                    $order_info_add = $this->pdo->exec($set_sql_add);
                    if ($order_info_add) {
                        $parameter = array(
                            'chat_id' => $chatid,
                            'parse_mode' => 'HTML',
                            'text' => "添加超级用户ID：" . $vpeople . "成功！"
                        );

                        $this->http_post_data('sendMessage', json_encode($parameter));
                    } else {
                        $parameter = array(
                            'chat_id' => $chatid,
                            'parse_mode' => 'HTML',
                            'text' => "添加超级用户ID：" . $vpeople . "失败！"
                        );

                        $this->http_post_data('sendMessage', json_encode($parameter));
                    }


                } else {
                    $parameter = array(
                        'chat_id' => $chatid,
                        'parse_mode' => 'HTML',
                        'text' => "超级用户ID：" . $vpeople . "已经存在！请勿重复添加"
                    );

                    $this->http_post_data('sendMessage', json_encode($parameter));

                }

            }
            exit();
        }

        //tjqml  添加群指定人使用命令：
        /* if (strpos($message, 'tjqml') !== false) {
             $quanxian = "tjqml";
             $this->quanxian($chatid, $userid, $quanxian,$username);


             if (!in_array($userid, $this->chaojiyonghu)) {

                 $parameter = array(
                     'chat_id' => $chatid,
                     'parse_mode' => 'HTML',
                     'text' => "当前命令只可以私聊机器人使用！且授权于专属人：晴朗@QingLang1688"
                 );
                 $this->http_post_data('sendMessage', json_encode($parameter));
                 exit();
             }

             $set_sql1 = "select * FROM pay_instruction group by instruction";
             $order_query2 = $this->pdo->query($set_sql1);
             $order_info2 = $order_query2->fetchAll();

             //(/tjzfs)-(46663566,565444566)-(664567777,66645677)

             $messages = "新增命令格式如下：\r\n(命令)-(可执行人电报id)\r\n";
             $switch_inline_query_current_msg = "#tianjia_qunyonghumingling_#\r\n(命令)-(可执行人电报id)\r\n(tjzfs)-(664567777,66645677)\r\n(开启)-(664567777,66645677)\r\n(yf)-(664567777,66645677)";
             $inline_keyboard_arr3[0] = array('text' => "马上添加一个试试 ", "switch_inline_query_current_chat" => $switch_inline_query_current_msg);
             $keyboard = [
                 'inline_keyboard' => [
                     $inline_keyboard_arr3,
                 ]
             ];
             $parameter = array(
                 'chat_id' => $chatid,
                 'parse_mode' => 'HTML',
                 'text' => $messages,
                 'reply_markup' => $keyboard,
                 'disable_web_page_preview' => true,

             );

             $this->http_post_data('sendMessage', json_encode($parameter));
             exit();


         }*/

        //tjqml  添加用户命令的实际操作：
        /* if (strpos($message, 'tianjia_qunyonghumingling_') !== false) {
             $quanxian = "tjqml";
             $this->quanxian($chatid, $userid, $quanxian,$username);
             if (!in_array($userid, $this->chuge_userid)) {
                 $parameter = array(
                     'chat_id' => $chatid,
                     'parse_mode' => 'HTML',
                     'text' => "禁止使用此命令！"
                 );
                 $this->http_post_data('sendMessage', json_encode($parameter));
                 exit();
             }


             $info = explode("qunyonghumingling_#", $message);
             $info_two = explode("\n", $info[1]);
             unset($info_two[0]);
             unset($info_two[1]);
             $mingling_list = $info_two;

             $all_ming_list = $this->all_ming_list;
             foreach ($mingling_list as $ke => $ve) {
                 $list = explode(")-(", $ve);
                 $mingling = substr($list[0], 1);
                 $people = substr($list[1], 0, -1);

                 //校验命令：
                 if (!in_array($mingling, $all_ming_list)) {
                     $parameter = array(
                         'chat_id' => $chatid,
                         'parse_mode' => 'HTML',
                         'text' => "请核对！当前系统未收录  <b>" . $mingling . "</b>  命令！联系晴朗@QingLang1688得到最新的命令大全"
                     );
                     $this->http_post_data('sendMessage', json_encode($parameter));
                     exit();
                 }


                 $dangqian_yonghuqun = $chatid;
                 foreach ($all_peoplelist as $kpeople => $vpeople) {
                     $dangqian_people = $vpeople;
                     //检验权限是否存在：
                     $set_sql1 = "select * FROM pay_instruction where chat_id='" . $dangqian_yonghuqun . "' and user_str='" . $dangqian_people . "' and instruction='" . $mingling . "'";
                     $order_query2 = $this->pdo->query($set_sql1);
                     $order_info2 = $order_query2->fetchAll();
                     if ($order_info2) {
                         $parameter = array(
                             'chat_id' => $chatid,
                             'parse_mode' => 'HTML',
                             'text' => "<>当前用户ID： <b>" . $dangqian_people . "</b>   已经在群ID： <b>" . $dangqian_yonghuqun . "</b> 中有 <b>" . $mingling . "</b>  的命令,请核对！"
                         );
                         exit();
                         $this->http_post_data('sendMessage', json_encode($parameter));
                         exit();
                     }
                 }


             }
             $all_msg = "";
             foreach ($mingling_list as $ke => $ve) {
                 $list = explode(")-(", $ve);

                 $mingling = substr($list[0], 1);    //命令

                 $people = substr($list[1], 0, -1);     //用户列表


                 $all_peoplelist = explode(",", $people);


                 $dangqian_yonghuqun = $chatid;
                 foreach ($all_peoplelist as $kpeople => $vpeople) {
                     $dangqian_people = $vpeople;

                     $today = date('Y-m-d H:i:s');
                     $set_sql_add = "insert into pay_instruction (chat_id,user_str,instruction,createtime) values ('" . $dangqian_yonghuqun . "','" . $dangqian_people . "','" . $mingling . "', '" . $today . "')";
                     $order_info_add = $this->pdo->exec($set_sql_add);

                     if (!$order_info_add) {
                         $parameter = array(
                             'chat_id' => $chatid,
                             'parse_mode' => 'HTML',
                             'text' => "当前用户ID： <b>" . $dangqian_people . "</b> 设置在群ID：  <b>" . $dangqian_yonghuqun . "</b>  的 <b>" . $mingling . "</b>  的命令失败！请核对！"
                         );
                         $this->http_post_data('sendMessage', json_encode($parameter));

                     } else {
                         $all_msg .= "当前用户ID： <b>" . $dangqian_people . "</b> 设置在群ID：  <b>" . $dangqian_yonghuqun . "</b>  的 <b>" . $mingling . "</b>  的命令成功！\r\n";
                     }
                 }


             }


             $parameter = array(
                 'chat_id' => $chatid,
                 'text' => $all_msg,
                 'parse_mode' => 'HTML',
             );
             $this->http_post_data('sendMessage', json_encode($parameter));
             exit();


         }*/
        
        if(strpos($message,'start_xiugai_') != false){
            $this->quanxian($chatid, $userid, "jqxiugai",$username);
            
            $tongdao_arr = explode("start_xiugai_",$message);
            $tongdao_arr2  = explode("_#",$tongdao_arr[1]);
            
            $tongdao_id  = $tongdao_arr2[0];//通道ID 
            
            $set_sql68 = "select * FROM pay_tongdao where id='" . $tongdao_id . "'";
            $order_query68= $this->pdo->query($set_sql68);
            $order_info68 = $order_query68->fetchAll();
            
            
            $changes = explode("\n", trim($tongdao_arr2[1]));
            
            $name_arr =  explode("==", trim($changes[0]));
            $name = $name_arr[1];
            
            $yufu_arr =  explode("==", trim($changes[1]));
            $yufu = $yufu_arr[1];
            
            $danbao_arr =  explode("==", trim($changes[2]));
            $danbao= $danbao_arr[1];
            
            $tongdaoname_arr =  explode("==", trim($changes[3]));
            $tongdaoname= $tongdaoname_arr[1];
            
            $chajian_arr =  explode("==", trim($changes[4]));
            $chajian= $chajian_arr[1];
            $set_sql4 = "select * FROM pay_plugin where name='" . $chajian . "'";
            $order_query4= $this->pdo->query($set_sql4);
            $order_info5 = $order_query4->fetchAll();
            
            if (!$order_info5) {
                $parameter = array(
                    'chat_id' => $chatid,
                    'text' => "系统并没有该插件,请核对！".$chajian,
                );
                $this->http_post_data('sendMessage', json_encode($parameter));
                exit();
            }
            
            $zidingyi_arr =  explode("==", trim($changes[5]));
            $zidingyi= $zidingyi_arr[1];
            
            $zhifufangshi_arr =  explode("==", trim($changes[6]));
            $zhifufangshi= $zhifufangshi_arr[1];
            
            //查看支付方式是否存在
            $set_sql5 = "select * FROM pay_type where showname='" . $zhifufangshi . "'";
            $order_query5= $this->pdo->query($set_sql5);
            $order_info6 = $order_query5->fetchAll();
            if (!$order_info6) {
                $parameter = array(
                    'chat_id' => $chatid,
                    'text' => "支付填写方式错误!请核对！",
                );
                $this->http_post_data('sendMessage', json_encode($parameter));
                exit();
            }
            $zhifufangshi_id = $order_info6[0]['id'];
            
            
            $houtaidizhi_arr =  explode("==", trim($changes[7]));
            $houtaidizhi= $houtaidizhi_arr[1];
            
            $houtaizhanghao_arr =  explode("==", trim($changes[8]));
            $houtaizhanghao= $houtaizhanghao_arr[1];
            
            $houtaimima_arr =  explode("==", trim($changes[9]));
            $houtaimima= $houtaimima_arr[1];
            
            $houtaipid_arr =  explode("==", trim($changes[10]));
            $houtaipid= $houtaipid_arr[1];
            
            $houtaiappkey_arr =  explode("==", trim($changes[11]));
            $houtaiappkey= $houtaiappkey_arr[1];
            
            $houtaixiadan_arr =  explode("==", trim($changes[12]));
            $houtaixiadan= $houtaixiadan_arr[1];
            
            $baiming_arr =  explode("==", trim($changes[13]));
            $baiming= $baiming_arr[1];
            
            $bianhao_arr =  explode("==", trim($changes[14]));
            $bianhao= $bianhao_arr[1];
            
            $feilv_arr =  explode("==", trim($changes[15]));
            $feilv= $feilv_arr[1];
            
            $chenglv_arr =  explode("==", trim($changes[16]));
            $chenglv= $chenglv_arr[1];
            
            $kangtou_arr =  explode("==", trim($changes[17]));
            $kangtou= $kangtou_arr[1]; 
            
            $bingfa_arr =  explode("==", trim($changes[18]));
            $bingfa= $bingfa_arr[1];
            
            $money_arr =  explode("==", trim($changes[19]));
            $money= $money_arr[1];
            
            $time_arr =  explode("==", trim($changes[20]));
            $time= $time_arr[1];
            
            $beizhu_arr =  explode("==", trim($changes[21]));
            $beizhu= $beizhu_arr[1];
            
            $zhuangtai_arr =  explode("==", trim($changes[22]));
            $zhuangtai= $zhuangtai_arr[1];
            
            if($zhuangtai == "开启"){
                $status = 1;
            }else{
                 $status = 0;
            }
            
            
            $set_sql ="update pay_tongdao set name='" .$tongdaoname . "',chajian='" . $chajian . "',zidingyi='" . $zidingyi . "',type='" . $zhifufangshi . "',number='" . $bianhao . "',rate='" . $feilv . "',success_rate='" . $chenglv . "',is_kangtou='" . $kangtou . "',is_bingfa='" . $bingfa . "',money='" . $money . "',time='" . $time . "',remarks='" . $beizhu . "',status='" . $status . "',pid='" . $houtaipid . "',miyao='" . $houtaiappkey . "',payulr='" . $houtaixiadan . "',baimingdan='" . $baiming . "',houtaiurl='" . $houtaidizhi . "',houtaizhanghao='" . $houtaizhanghao . "',houtaimima='" . $houtaimima . "' where  id='" . $tongdao_id . "'";
            $chang_tongdao_status = $this->pdo->exec($set_sql);
            
            $rate = "100.00";
       
            $set_sql2 = "update pay_channel set type ='" .$zhifufangshi_id . "',plugin='" . $chajian . "',name='" . $tongdaoname . "',rate='" . $rate . "',status='" . $status . "',appid='" . $houtaipid . "',appkey='" . $houtaiappkey . "',appurl='" . $bianhao . "',apiurl='" . $houtaixiadan . "',beizhu='" . $beizhu . "',bianhao='" . $bianhao . "',feilv='" . $feilv . "',chenglv='" . $chenglv . "',shifoukangtou='" . $kangtou . "',nengfoubingfa='" . $bingfa . "',jinefanwei='" . $money . "',yunxingtime='" . $time . "' where  zidingyi='" . $zidingyi . "'";
            $chang_channel_status = $this->pdo->exec($set_sql2);
            
            
            $chatid_update = $order_info68[0]['chat_id'];
            
            $set_sql = "update pay_zhifushang set money='" . $yufu . "',security='" . $danbao . "',name='" . $name . "' where chat_id='" . $chatid_update . "'";
            $chang_danbao_status = $this->pdo->exec($set_sql);
         
            
            
            if($chang_tongdao_status){
                $parameter = array(
                    'chat_id' => $chatid,
                    'text' => "机器人通道修改成功！",
                );
                $this->http_post_data('sendMessage', json_encode($parameter));
            }
            if($chang_channel_status){
                $parameter = array(
                    'chat_id' => $chatid,
                    'text' => "后台通道修改成功！",
                );
                $this->http_post_data('sendMessage', json_encode($parameter));
            }
            
            if($chang_danbao_status){
                $parameter = array(
                    'chat_id' => $chatid,
                    'text' => "用户担保金/预付/名称修改成功！",
                );
                $this->http_post_data('sendMessage', json_encode($parameter));
            }
            
            exit();
        }
    

        //szml  单聊机器人的设置命令
        if (strpos($message, 'szml') !== false) {


            $type = $data['message']['chat']['type'];

            if ($type != "private") {
                $parameter = array(
                    'chat_id' => $chatid,
                    'parse_mode' => 'HTML',
                    'text' => "当前命令只可以私聊机器人使用！且授权于专属人：晴朗@QingLang1688"
                );
                $this->http_post_data('sendMessage', json_encode($parameter));
                exit();
            }
            if (!in_array($userid, $this->chaojiyonghu)) {

                $parameter = array(
                    'chat_id' => $chatid,
                    'parse_mode' => 'HTML',
                    'text' => "当前命令只可以私聊机器人使用！"
                );
                $this->http_post_data('sendMessage', json_encode($parameter));
                exit();
            }

            $set_sql1 = "select * FROM pay_instruction group by instruction";
            $order_query2 = $this->pdo->query($set_sql1);
            $order_info2 = $order_query2->fetchAll();


            $messages = "新增命令格式如下：\r\n(命令)-(可执行群列表)-(可执行人电报id)\r\n";
            $switch_inline_query_current_msg = "#tianjia_yonghumingling_#\r\n(命令)-(可执行群列表)-(可执行人电报id)\r\n(tjzfs)-(-46663566,-565444566)-(664567777,66645677)\r\n(开启)-(-46663566,-565444566)-(664567777,66645677)\r\n(yf)-(-46663566,-565444566)-(664567777,66645677)";
            $inline_keyboard_arr3[0] = array('text' => "马上添加一个试试 ", "switch_inline_query_current_chat" => $switch_inline_query_current_msg);
            $keyboard = [
                'inline_keyboard' => [
                    $inline_keyboard_arr3,
                ]
            ];
            $parameter = array(
                'chat_id' => $chatid,
                'parse_mode' => 'HTML',
                'text' => $messages,
                'reply_markup' => $keyboard,
                'disable_web_page_preview' => true,

            );

            $this->http_post_data('sendMessage', json_encode($parameter));
            exit();


        }
        /*//szml  添加用户命令的实际操作：
        if (strpos($message, 'tianjia_yonghumingling_') !== false) {
            $type = $data['message']['chat']['type'];

            if ($type != "private") {
                $parameter = array(
                    'chat_id' => $chatid,
                    'parse_mode' => 'HTML',
                    'text' => "当前命令只可以私聊机器人使用！且授权于专属人：晴朗@QingLang1688"
                );
                $this->http_post_data('sendMessage', json_encode($parameter));
                exit();
            }
            if (!in_array($userid, $this->chaojiyonghu)) {
                $parameter = array(
                    'chat_id' => $chatid,
                    'parse_mode' => 'HTML',
                    'text' => "当前命令只可以私聊机器人使用！"
                );
                $this->http_post_data('sendMessage', json_encode($parameter));
                exit();
            }


            $info = explode("mingling_#", $message);
            $info_two = explode("\n", $info[1]);
            unset($info_two[0]);
            unset($info_two[1]);
            $mingling_list = $info_two;

            $all_ming_list = $this->all_ming_list;
            foreach ($mingling_list as $ke => $ve) {
                $list = explode(")-(", $ve);
                $mingling = substr($list[0], 1);
                $yonghuqunlist = $list[1];
                $people = substr($list[2], 0, -1);
                //校验命令：
                if (!in_array($mingling, $all_ming_list)) {
                    $parameter = array(
                        'chat_id' => $chatid,
                        'parse_mode' => 'HTML',
                        'text' => "请核对！当前系统未收录  <b>" . $mingling . "</b>  命令！联系晴朗@QingLang1688得到最新的命令大全"
                    );
                    $this->http_post_data('sendMessage', json_encode($parameter));
                    exit();
                }
                //校验群ID
                if (strpos($yonghuqunlist, '-') === false) {
                    $parameter = array(
                        'chat_id' => $chatid,
                        'parse_mode' => 'HTML',
                        'text' => "请核对！当前群ID：  <b>" . $yonghuqunlist . "</b>   不合法，请核对后输入！"
                    );
                    $this->http_post_data('sendMessage', json_encode($parameter));
                    exit();
                }
                $all_yonghuqunlist = explode(",", $yonghuqunlist);
                $all_peoplelist = explode(",", $people);
                foreach ($all_yonghuqunlist as $kyonghu => $vyonghu) {
                    $dangqian_yonghuqun = $vyonghu;
                    foreach ($all_peoplelist as $kpeople => $vpeople) {
                        $dangqian_people = $vpeople;
                        //检验权限是否存在：
                        $set_sql1 = "select * FROM pay_instruction where chat_id='" . $dangqian_yonghuqun . "' and user_str='" . $dangqian_people . "' and instruction='" . $mingling . "'";
                        $order_query2 = $this->pdo->query($set_sql1);
                        $order_info2 = $order_query2->fetchAll();
                        if ($order_info2) {
                            $parameter = array(
                                'chat_id' => $chatid,
                                'parse_mode' => 'HTML',
                                'text' => "<>当前用户ID： <b>" . $dangqian_people . "</b>   已经在群ID： <b>" . $dangqian_yonghuqun . "</b> 中有 <b>" . $mingling . "</b>  的命令,请核对！"
                            );
                            $this->http_post_data('sendMessage', json_encode($parameter));
                            exit();
                        }
                    }

                }
            }
            foreach ($mingling_list as $ke => $ve) {
                $list = explode(")-(", $ve);

                $mingling = substr($list[0], 1);    //命令
                $yonghuqunlist = $list[1];        //群列表
                $people = substr($list[2], 0, -1);     //用户列表

                $all_yonghuqunlist = explode(",", $yonghuqunlist);
                $all_peoplelist = explode(",", $people);
                $all_msg = "";
                foreach ($all_yonghuqunlist as $kyonghu => $vyonghu) {
                    $dangqian_yonghuqun = $vyonghu;
                    foreach ($all_peoplelist as $kpeople => $vpeople) {
                        $dangqian_people = $vpeople;

                        $dangqian_people = $vpeople;
                        $today = date('Y-m-d H:i:s');
                        $set_sql_add = "insert into pay_instruction (chat_id,user_str,instruction,createtime) values ('" . $dangqian_yonghuqun . "','" . $dangqian_people . "','" . $mingling . "', '" . $today . "')";
                        $order_info_add = $this->pdo->exec($set_sql_add);

                        if (!$order_info_add) {
                            $parameter = array(
                                'chat_id' => $chatid,
                                'parse_mode' => 'HTML',
                                'text' => "当前用户ID： <b>" . $dangqian_people . "</b> 设置在群ID：  <b>" . $dangqian_yonghuqun . "</b>  的 <b>" . $mingling . "</b>  的命令失败！请核对！"
                            );
                            $this->http_post_data('sendMessage', json_encode($parameter));
                            exit();
                        } else {
                            $all_msg .= "当前用户ID： <b>" . $dangqian_people . "</b> 设置在群ID：  <b>" . $dangqian_yonghuqun . "</b>  的 <b>" . $mingling . "</b>  的命令成功！\r\n";
                        }
                    }

                }

            }


            $parameter = array(
                'chat_id' => $chatid,
                'text' => $all_msg,
                'parse_mode' => 'HTML',
            );
            $this->http_post_data('sendMessage', json_encode($parameter));
            exit();


        }

        //ckqml  查看当前群有什么命令，且受制于谁：
        if (strpos($message, 'ckqml') !== false) {
            $quanxian = "ckqml";
            $this->quanxian($chatid, $userid, $quanxian,$username);

            $set_sql1 = "select * FROM pay_instruction where chat_id='" . $chatid . "'";

            $order_query2 = $this->pdo->query($set_sql1);
            $order_info2 = $order_query2->fetchAll();
            if (!$order_info2) {
                $parameter = array(
                    'chat_id' => $chatid,
                    'parse_mode' => 'HTML',
                    'text' => "当前群暂未设置命令，以及指定人信息！"
                );
                $this->http_post_data('sendMessage', json_encode($parameter));
                exit();
            }
            $msg = "<b>当前群命令信息(点击数字为删除操作[谨慎])：</b>\r\n\r\n";
            $inline_keyboard_arr = array();
            foreach ($order_info2 as $key => $value) {
                $msg .= ($key + 1) . " : 命令：<b>" . $value['instruction'] . "  </b>---  指定人ID:  <b>" . $value['user_str'] . "</b>  \r\n";
                $inline_keyboard_arr[$key] = array('text' => ($key + 1), "callback_data" => "delqml###" . $value['id']);

            }
            $keyboard = [
                'inline_keyboard' => [
                    $inline_keyboard_arr
                ]
            ];
            $parameter = array(
                "chat_id" => $chatid,
                "text" => $msg,
                "parse_mode" => "HTML",
                "disable_web_page_preview" => true,
                'reply_markup' => $keyboard
            );
            $this->http_post_data('sendMessage', json_encode($parameter));
            exit();
        }*/


        //查看命令：/ckml(查看命令，仅我能执行，写死)
        /* if (strpos($message, '/ckml') !== false) {
             $chuge_userid_arr = $this->chaojiyonghu;
             if (!in_array($userid, $chuge_userid_arr)) {
                 $ids_str = implode(",",$chuge_userid_arr);
                 $parameter = array(
                     'chat_id' => $chatid,
                     'parse_mode' => 'HTML',
                     'text' => "仅Tg_ID:".$ids_str."有此权限！"
                 );
                 $this->http_post_data('sendMessage', json_encode($parameter));
                 exit();
             }

             $type = $data['message']['chat']['type'];

             if ($type != "private") {
                 $parameter = array(
                     'chat_id' => $chatid,
                     'parse_mode' => 'HTML',
                     'text' => "当前命令只可以私聊机器人使用！且授权于专属人：晴朗@QingLang1688"
                 );
                 $this->http_post_data('sendMessage', json_encode($parameter));
                 exit();
             }

             $set_sql1 = "select * FROM pay_instruction group by instruction";
             $order_query2 = $this->pdo->query($set_sql1);
             $order_info2 = $order_query2->fetchAll();
             if (!$order_info2) {
                 $parameter = array(
                     'chat_id' => $chatid,
                     'parse_mode' => 'HTML',
                     'text' => "权限列表空，快去添加吧：/szml"
                 );
                 $this->http_post_data('sendMessage', json_encode($parameter));
                 exit();
             }
             $msg = "";
             foreach ($order_info2 as $key => $value) {
                 $msg .= "<b>" . $value['instruction'] . "</b>\r\n";
             }

             $parameter = array(
                 'chat_id' => $chatid,
                 'parse_mode' => 'HTML',
                 'text' => $msg
             );
             $this->http_post_data('sendMessage', json_encode($parameter));
             exit();
         }*/


        //dz   抗投信息
        if (strpos($message, '/dq') !== false) {
            $this->quanxian($chatid, $userid, "dq",$username);

            $set_sql1 = "select * FROM pay_tongdao order by status asc,id desc";
            $order_query2 = $this->pdo->query($set_sql1);
            $order_info2 = $order_query2->fetchAll();

            $msg = "<b>是否抗投通道信息：</b>\r\n\r\n";

            $kangtou_msg = "——————抗投如下—————\r\n";
            $no_kangtou_msg = "—————不抗投如下—————\r\n";
            $all_yufu_arr = array();
            $all_danbao_arr = array();
            foreach ($order_info2 as $key => $vel) {
                $tongdao_id = $vel['id'];
                //状态：
                if ($vel['status'] == "0") {
                    $status = "开启";
                } else {
                    $status = "切停";
                }

                //查询用户的预付+担保金：
                $user_chatid = $vel['chat_id'];
                $set_sql1_zhifu = "select * FROM pay_zhifushang where chat_id='" . $user_chatid . "'";
                $order_query2_zhifu = $this->pdo->query($set_sql1_zhifu);
                $order_info2_zhifu = $order_query2_zhifu->fetchAll();
                if (!$order_info2_zhifu) {
                    $msg_alert = "未查询到通道：" . $vel['name'] . "的支付商信息，请核对异常情况！";
                    $parameter = array(
                        'chat_id' => $chatid,
                        'text' => $msg_alert
                    );
                    $this->http_post_data('sendMessage', json_encode($parameter));
                    exit();
                }

                $zhifushang_id = $order_info2_zhifu['0']['id'];
                if (!array_key_exists($zhifushang_id, $all_yufu_arr)) {

                    $all_yufu_arr[$zhifushang_id] = $order_info2_zhifu['0']['money'];
                }
                if (!array_key_exists($zhifushang_id, $all_danbao_arr)) {

                    $all_danbao_arr[$zhifushang_id] = $order_info2_zhifu['0']['security'];
                }


                $shouxin = $order_info2_zhifu['0']['money'] + $order_info2_zhifu['0']['security'];

                if ($vel['is_kangtou'] == "是") {
                    $kangtou_msg .= "【" . $order_info2_zhifu['0']['id'] . "】<b><a href='https://t.me/".$this->jiqirenming."?start=tongdao_detail" . $tongdao_id . "'>" . $vel['name'] . "</a></b>-" . $vel['rate'] . "-授信<b>" . $shouxin . "</b>-" . $status . "\r\n";
                } else {
                    $no_kangtou_msg .= "【" . $order_info2_zhifu['0']['id'] . "】<b><a href='https://t.me/".$this->jiqirenming."?start=tongdao_detail" . $tongdao_id . "'>" . $vel['name'] . "</a></b>-" . $vel['rate'] . "-授信<b>" . $shouxin . "</b>-" . $status . "\r\n";
                }
            }
            $all_yufu_money = array_sum($all_yufu_arr);
            $all_danbao_money = array_sum($all_danbao_arr);

            $end_msg = "\r\n\r\n上游总预付(约):" . $all_yufu_money . "元\r\n上游总担保金(约):" . $all_danbao_money . "元";

            $msg_all = $msg . $kangtou_msg . "\r\n" . $no_kangtou_msg . $end_msg;

            $parameter = array(
                'chat_id' => $chatid,
                'parse_mode' => 'HTML',
                'text' => $msg_all,
            );
            $this->http_post_data('sendMessage', json_encode($parameter));
            exit();
        }

        //dz   对账信息
        if (strpos($message, '/dz') !== false) {
            $this->quanxian($chatid, $userid, "dz",$username);
            $set_sql1 = "select * FROM pay_tongdao order by status asc,id desc";
            $order_query2 = $this->pdo->query($set_sql1);
            $order_info2 = $order_query2->fetchAll();
            $all_money = 0;
            foreach ($order_info2 as $key => $vel) {

                $tongdao_id = $vel['id'];
                $today_zuori = date("Y-m-d", strtotime("-1 day"));
                $today_jinri = date("Y-m-d");
                //查看误差
                $set_sql1_wucha = "select * from pay_tongdaowucha where tongdao_id='" . $tongdao_id . "' and date='" . $today_jinri . "'";
                $order_query2_wucha = $this->pdo->query($set_sql1_wucha);
                $order_info2_wucha = $order_query2_wucha->fetchAll();
                if (!$order_info2_wucha) {
                    $order_info2[$key]['wucha'] = "未核";
                    $order_info2[$key]['wucha_num'] = "0";
                } else {
                    $order_info2[$key]['wucha'] = $order_info2_wucha[0]['money'];
                    $order_info2[$key]['wucha_num'] = (int)$order_info2_wucha[0]['money'];
                }

                //查看跑量：
                $chajian = $vel['chajian'];
                $zidingyi = $vel['zidingyi'];
                $sql_info_money = "select sum(a.getmoney) as getmoney from pay_order as a left join pay_channel as b on b.id=a.channel where a.status = '1'  and b.plugin='" . $chajian . "' and a.date='" . $today_zuori . "' and b.zidingyi='".$zidingyi."'";

                $order_query2_money = $this->pdo->query($sql_info_money);
                $chatinfo_money = $order_query2_money->fetchAll();
                if (!$chatinfo_money) {
                    $order_info2[$key]['paoliang'] = "0";
                } else {
                    $order_info2[$key]['paoliang'] = $chatinfo_money[0]['getmoney'];
                }
                $all_money += ($order_info2[$key]['paoliang'] * $vel['rate']) / 100;

            }
            $keys = array_column($order_info2, 'wucha_num');
            array_multisort($keys, SORT_ASC, $order_info2);
            $msg = "<b>昨天对账信息如下:</b>\r\n\r\n";
            foreach ($order_info2 as $key2 => $vel2) {
                
                
                if ($vel2['wucha'] == "未核") {
                    $change_wucha = "未核";
                } else {
                    $change_wucha = "误差" . $vel2['wucha'];
                }
                if($vel2['wucha'] =="未核" ){
                    continue;
                }
                
                $mo = $vel2['paoliang'] > 0 ? $vel2['paoliang'] : 0;
                $msg .= $vel2['name'] . "-" . $vel2['rate'] . "-<b>" . $mo . "元</b>-" . $change_wucha . "\r\n";
            }
            $msg .= "\r\n上游总收费用:" . $all_money . "元(跑量x费率/100)";
            $parameter = array(
                'chat_id' => $chatid,
                'parse_mode' => 'HTML',
                'text' => $msg,
            );
            $this->http_post_data('sendMessage', json_encode($parameter));
            exit();
        }


        //开启/关闭   开启或者关闭 通道：
        if (strpos($message, '开启') !== false || strpos($message, '关闭') !== false) {


            if (strpos($message, '开启') !== false) {
                $quanxian = "开启";
            } else {
                $quanxian = "关闭";
            }
            $this->quanxian($chatid, $userid, $quanxian,$username);
            $set_sql1 = "select * FROM pay_tongdao where chat_id='" . $chatid . "' order by status asc,id desc";
            $order_query2 = $this->pdo->query($set_sql1);
            $order_info2 = $order_query2->fetchAll();
            if (!$order_info2) {
                $parameter = array(
                    'chat_id' => $chatid,
                    'text' => "未查询更多的支付通道！请核对是否有添加通道",
                );
                $this->http_post_data('sendMessage', json_encode($parameter));
                exit();
            }

            $message = "<b>关闭或开启对应通道信息：</b>\r\n\r\n";
            $inline_keyboard_arr = array();
            foreach ($order_info2 as $key => $ve) {
                //1，支付宝原生 (开启中)  2，现金红包  (已切停)
                if ($ve['status'] == "0") {
                    $status = "开启中";
                } else {
                    $status = "已关闭";
                }
                $message .= ($key + 1) . ":" . $ve['name'] . "(" . $ve['chajian'] . ")【" . $status . "】\r\n";
                $inline_keyboard_arr[$key] = array('text' => ($key + 1), "callback_data" => "guanbitongdao###" . $ve['id']);
            }

            $keyboard = [
                'inline_keyboard' => [
                    $inline_keyboard_arr
                ]
            ];

            $parameter = array(
                "chat_id" => $chatid,
                "text" => $message,
                "parse_mode" => "HTML",
                "disable_web_page_preview" => true,
                'reply_markup' => $keyboard
            );
            $this->http_post_data('sendMessage', json_encode($parameter));
            exit();
        }

        //修改误差  修改误差
        if (strpos($message, 'wuchan_xiugai_') !== false) {
            $this->quanxian($chatid, $userid, "修改误差",$username);
            $today = date("Y-m-d");
            $info = explode("xiugai_", $message);
            $info_two = explode("_", $info[1]);
            $info_three = explode("=", $info_two[1]);
            $money = $info_three['1'];
            $tongdao_id = $info_two['0'];

            $set_sql1 = "select * from pay_tongdaowucha where tongdao_id='" . $tongdao_id . "' and date='" . $today . "'";
            $order_query2 = $this->pdo->query($set_sql1);
            $order_info2 = $order_query2->fetchAll();
            if (!$order_info2) {
                $parameter = array(
                    'chat_id' => $chatid,
                    'text' => "修改异常！请核对！",
                );
                $this->http_post_data('sendMessage', json_encode($parameter));
                exit();
            }
            $trade_no = $order_info2[0]['id'];
            $change_status = $this->pdo->exec("update pay_tongdaowucha set money='$money' where id='$trade_no'");
            if ($change_status) {
                $msg = "成功修改误差：" . $money . "!";
            } else {
                $msg = "修改误差失败！";
            }
            $parameter = array(
                'chat_id' => $chatid,
                'text' => $msg
            );
            $this->http_post_data('sendMessage', json_encode($parameter));
            exit();
        }
        //新增误差   新增误差
        if (strpos($message, 'wuchan_tianjia_') !== false) {
            $this->quanxian($chatid, $userid, "添加误差",$username);
            $today = date("Y-m-d");
            $create_time = date("Y-m-d H:i:s");
            $info = explode("tianjia_", $message);
            $info_two = explode("_", $info[1]);
            $info_three = explode("=", $info_two[1]);
            $money = $info_three['1'];
            $tongdao_id = $info_two['0'];


            $set_sql1 = "select * from pay_tongdaowucha where tongdao_id='" . $tongdao_id . "' and date='" . $today . "'";
            $order_query2 = $this->pdo->query($set_sql1);
            $order_info2 = $order_query2->fetchAll();
            if ($order_info2) {
                $parameter = array(
                    'chat_id' => $chatid,
                    'text' => "添加异常！请核对！",
                );
                $this->http_post_data('sendMessage', json_encode($parameter));
                exit();
            }
            $set_sql = "insert into pay_tongdaowucha (tongdao_id,date,money,createtime) values ('" . $tongdao_id . "','" . $today . "','" . $money . "', '" . $create_time . "')";
            $change_status = $this->pdo->exec($set_sql);
            if ($change_status) {
                $msg = "添加修改误差：" . $money . "!";
            } else {
                $msg = "添加误差失败！";
            }
            $parameter = array(
                'chat_id' => $chatid,
                'text' => $msg
            );
            $this->http_post_data('sendMessage', json_encode($parameter));
            exit();
        }

        


        //添加支付商命令：
        if (strpos($message, 'tjzfs') !== false) {
            $quanxian = "tjzfs";

            $this->quanxian($chatid, $userid, $quanxian,$username);
            $this->addzhifushang($message, $chatid, $userid, $data);
        }
        //删除支付商：
        if (strpos($message, 'sczfs') !== false) {

            $quanxian = "sczfs";

            $this->quanxian($chatid, $userid, $quanxian,$username);
            $this->addzhifushang($message, $chatid, $userid, $data);
        }
        /*/yf1026
        /bzj10000*/
        if (strpos($message, 'yf') !== false) {

            $this->quanxian($chatid, $userid, "yf",$username);
            $changes = explode("yf", trim($message));
            $money = $changes[1];
            if (!is_numeric($money) || strpos($money, ".") !== false || $money <= 0) {
                $parameter = array(
                    'chat_id' => $chatid,
                    'text' => "预付金额格式不正确！",
                );
                $this->http_post_data('sendMessage', json_encode($parameter));
                exit();
            }
            $set_sql = "update pay_zhifushang set money='" . $money . "' where chat_id='" . $chatid . "'";
            $this->pdo->exec($set_sql);
            //获取录入信息：
            $parameter = array(
                'chat_id' => $chatid,
                'parse_mode' => 'HTML',
                'text' => '已设置预付为' . $money . '元',
            );
            $this->http_post_data('sendMessage', json_encode($parameter));
            exit();

        }
        if (strpos($message, 'bzj') !== false) {
            $this->quanxian($chatid, $userid, "bzj",$username);

            $changes = explode("bzj", trim($message));
            $money = $changes[1];
            if (!is_numeric($money) || strpos($money, ".") !== false || $money <= 0) {
                $parameter = array(
                    'chat_id' => $chatid,
                    'text' => "保证金金额格式不正确！",
                );
                $this->http_post_data('sendMessage', json_encode($parameter));
                exit();
            }
            $set_sql = "update pay_zhifushang set security='" . $money . "' where chat_id='" . $chatid . "'";
            $this->pdo->exec($set_sql);
            //获取录入信息：
            $parameter = array(
                'chat_id' => $chatid,
                'parse_mode' => 'HTML',
                'text' => '已设置保证金为' . $money . "元"
            );
            $this->http_post_data('sendMessage', json_encode($parameter));
            exit();

        }

        //添加通道：/tjtd
        if (strpos($message, 'tjtd') !== false || strpos($message, 'cktd') !== false || strpos($message, 'sctd') !== false) {
            //查询当前群的支付商信息是否存在：
            if (strpos($message, 'tjtd') !== false) {

                $set_sql1 = "select * FROM pay_zhifushang where chat_id='" . $chatid . "'";
                $order_query2 = $this->pdo->query($set_sql1);
                $order_info2 = $order_query2->fetchAll();
                if (!$order_info2) {
                    $parameter = array(
                        'chat_id' => $chatid,
                        'text' => "当前群未绑定支付商,请先添加支付商！命令：/tjzfs",
                    );
                    $this->http_post_data('sendMessage', json_encode($parameter));
                    exit();
                }

                $quanxian = "tjtd";
            }
            if (strpos($message, 'cktd') !== false) {
                $quanxian = "cktd";
            }
            if (strpos($message, 'sctd') !== false) {
                $quanxian = "sctd";
            }

            $this->quanxian($chatid, $userid, $quanxian,$username);
            $this->addtongdao($message, $chatid, $userid, $data,$username);
        }
        
        //机器人单聊：
        if (strpos($message, '/start') !== false) {
            //删除用户组下某个命令：
            if (strpos($message, 'minglingshanchu_') !== false) {

                $chuge_userid_arr = $this->chaojiyonghu;
                if (!in_array($userid, $chuge_userid_arr)) {
                    $ids_str = implode(",",$chuge_userid_arr);
                    $parameter = array(
                        'chat_id' => $chatid,
                        'parse_mode' => 'HTML',
                        'text' => "仅Tg_ID:".$ids_str."有此权限！"
                    );
                    $this->http_post_data('sendMessage', json_encode($parameter));
                    exit();
                }
                $instruction_arr = explode("minglingshanchu_", $message);
                $instruction_id = $instruction_arr[1];
                $instruction_arr2 = explode("__", $instruction_id);
                $yonghzuid = $instruction_arr2[0];
                $yonghzumingling = $instruction_arr2[1];

                $set_sql1 = "select * FROM pay_yonghuzu where id='" . $yonghzuid . "'";
                $order_query2 = $this->pdo->query($set_sql1);
                $order_info2 = $order_query2->fetchAll();

                if (!$order_info2) {
                    $parameter = array(
                        'chat_id' => $chatid,
                        'parse_mode' => 'HTML',
                        'text' => "当前用户组查询异常！".$yonghzuid."_".$yonghzumingling
                    );
                    $this->http_post_data('sendMessage', json_encode($parameter));
                    exit();
                }
                if(empty($order_info2[0]['mingling'])){
                    $parameter = array(
                        'chat_id' => $chatid,
                        'parse_mode' => 'HTML',
                        'text' => "当前用户组下的命令查询异常！"
                    );
                    $this->http_post_data('sendMessage', json_encode($parameter));
                    exit();
                }
                $all_mingling = $order_info2[0]['mingling'];
                $all_mingling_arr = explode(",",$all_mingling);
                $yonghzumingling_arr = array($yonghzumingling);
                $all_mingling_arr = array_diff($all_mingling_arr, $yonghzumingling_arr);
                $all_mingling_arr_str = implode(",",$all_mingling_arr);
                $set_sql = "update pay_yonghuzu set mingling='" . $all_mingling_arr_str . "' where id='" . $yonghzuid . "'";
                $is_gengxin =  $this->pdo->exec($set_sql);
                if ($is_gengxin) {
                    $msg = "<b>成功!</b>:  删除用户组: <b>" . $order_info2[0]['name'] . "</b> 中的：<b>" . $yonghzumingling . "</b> 命令!";
                } else {
                    $msg = "<b>失败!</b>:  删除用户组: <b>" . $order_info2[0]['name'] . "</b> 中的：<b>" . $yonghzumingling . "</b> 命令!";
                }
                $parameter = array(
                    'chat_id' => $chatid,
                    'text' => $msg,
                    'parse_mode' => 'HTML',
                );
                $this->http_post_data('sendMessage', json_encode($parameter));
                exit();
            }
            //tongdao_detail   通道误差详细信息
            if (strpos($message, 'tongdao_detail') !== false) {
                $this->quanxian($chatid, $userid, "tongdao_detail",$username);

                $tongdao_arrs = explode("detail", $message);

                $tongdao_id = $tongdao_arrs['1'];

                $set_sql1 = "select * FROM pay_tongdao where id='" . $tongdao_id . "'";
                $order_query2 = $this->pdo->query($set_sql1);
                $order_info2 = $order_query2->fetchAll();
                if ($order_info2) {
                    $set_sql2 = "select * FROM pay_zhifushang where chat_id='" . $order_info2[0]['chat_id'] . "'";
                    $order_query3 = $this->pdo->query($set_sql2);
                    $order_info4 = $order_query3->fetchAll();
                    if (!$order_info4) {
                        $parameter = array(
                            'chat_id' => $chatid,
                            'text' => "信息查询异常！",
                        );
                        $this->http_post_data('sendMessage', json_encode($parameter));
                        exit();
                    }
                    $info_one = $order_info4[0];

                    /*支付商名称:
                    支付商预付:
                    支付商担保金:
                    名称:
                    插件：:
                    支付方式:
                    编号:
                    费率:
                    成率:
                    是否抗投:
                    能否并发:
                    金额范围:
                    运行时间:
                    备注:
                    开启状态*/


                    if ($order_info2[0]['status'] == "0") {
                        $status = "开启";
                    } else {
                        $status = "关闭";
                    }
                    $messages = "支付商名称==" . $info_one['name'] . "\r\n支付商预付==" . $info_one['money'] . "\r\n支付商担保金==" . $info_one['security'] . "\r\n通道名称==" . $order_info2[0]['name'] . "\r\n插件名==" . $order_info2[0]['chajian']. "\r\n自定义编号==" . $order_info2[0]['zidingyi'] . "\r\n支付方式==" . $order_info2[0]['type']. "\r\n支付商后台地址==" . $order_info2[0]['houtaiurl'] . "\r\n支付商账号==" . $order_info2[0]['houtaizhanghao'] . "\r\n支付商登陆密码==" . $order_info2[0]['houtaimima'] . "\r\n商户ID==" . $order_info2[0]['pid'] . "\r\n商户密钥==" . $order_info2[0]['miyao'] . "\r\n下单地址==" . $order_info2[0]['payulr'] . "\r\n白名单(英文逗号隔开)==" . $order_info2[0]['baimingdan']  . "\r\n编号==" . $order_info2[0]['number'] . "\r\n费率==" . $order_info2[0]['rate'] . "\r\n成率==" . $order_info2[0]['success_rate'] . "\r\n是否抗投==" . $order_info2[0]['is_kangtou'] . "\r\n能否并发==" . $order_info2[0]['is_bingfa'] . "\r\n金额范围==" . $order_info2[0]['money'] . "\r\n运行时间==" . $order_info2[0]['time'] . "\r\n备注==" . $order_info2[0]['remarks'] . "\r\n开启状态==" . $status;
                    /*$parameter = array(
                        'chat_id' => $chatid,
                        'text' => $messages
                    );
                    $this->http_post_data('sendMessage', json_encode($parameter));
                    exit();*/
                    
                   
                    $switch_inline_query_current_msg = "#start_xiugai_".$tongdao_id."_#\r\n". "支付商名称==" . $info_one['name'] . "\r\n 支付商预付==" . $info_one['money'] . "\r\n支付商担保金==" . $info_one['security'] . "\r\n通道名称==" . $order_info2[0]['name'] . "\r\n插件名==" . $order_info2[0]['chajian']. "\r\n自定义编号==" . $order_info2[0]['zidingyi'] . "\r\n支付方式==" . $order_info2[0]['type']. "\r\n支付商后台地址==" . $order_info2[0]['houtaiurl'] . "\r\n支付商账号==" . $order_info2[0]['houtaizhanghao'] . "\r\n支付商登陆密码==" . $order_info2[0]['houtaimima'] . "\r\n商户ID==" . $order_info2[0]['pid'] . "\r\n商户密钥==" . $order_info2[0]['miyao'] . "\r\n下单地址==" . $order_info2[0]['payulr'] . "\r\n白名单(英文逗号隔开)==" . $order_info2[0]['baimingdan']  . "\r\n编号==" . $order_info2[0]['number'] . "\r\n费率==" . $order_info2[0]['rate'] . "\r\n成率==" . $order_info2[0]['success_rate'] . "\r\n是否抗投==" . $order_info2[0]['is_kangtou'] . "\r\n能否并发==" . $order_info2[0]['is_bingfa'] . "\r\n金额范围==" . $order_info2[0]['money'] . "\r\n运行时间==" . $order_info2[0]['time'] . "\r\n备注==" . $order_info2[0]['remarks'] . "\r\n开启状态==" . $status;
                    
                    $inline_keyboard_arr3[0] = array('text' => "立即修改", "switch_inline_query_current_chat" => $switch_inline_query_current_msg);
                    $inline_keyboard_arr3[1] = array('text' => "删除", "callback_data" => "jqdelete###".$tongdao_id);
                    $keyboard = [
                        'inline_keyboard' => [
                            $inline_keyboard_arr3, 
                        ]
                    ];
                    $parameter = array(
                        'chat_id' => $chatid,
                        'parse_mode' => 'HTML',
                        'text' => $messages,
                        'reply_markup' => $keyboard,
                        'disable_web_page_preview' => true,
        
                    );
        
                    $this->http_post_data('sendMessage', json_encode($parameter));
                    exit();
                    
                    
                } else {
                    $parameter = array(
                        'chat_id' => $chatid,
                        'text' => "信息查询异常！",
                    );
                    $this->http_post_data('sendMessage', json_encode($parameter));
                    exit();
                }
            }
            //修改误差  修改误差
            if (strpos($message, 'tongdao_wucha') !== false) {
                $this->quanxian($chatid, $userid, "修改误差",$username);
                $today = date("Y-m-d");
                $tongdao_arrs = explode("_wucha", $message);

                $tongdao_id = $tongdao_arrs['1'];

                $set_sql1 = "select * from pay_tongdaowucha where tongdao_id='" . $tongdao_id . "' and date='" . $today . "'";
                $order_query2 = $this->pdo->query($set_sql1);
                $order_info2 = $order_query2->fetchAll();
                if ($order_info2) {
                    $messages = "当前通道且当天已经存在误差金额:" . $order_info2[0]['money'] . "\r\n修改格式如下：\r\n新的误差金额=+/-100\r\n";
                    $switch_inline_query_current_msg = "#wuchan_xiugai_" . $tongdao_id . "_#\r\n新的误差金额=+100";
                    $inline_keyboard_arr3[0] = array('text' => "马上添加一个试试 ", "switch_inline_query_current_chat" => $switch_inline_query_current_msg);
                    $keyboard = [
                        'inline_keyboard' => [
                            $inline_keyboard_arr3,
                        ]
                    ];
                    $parameter = array(
                        'chat_id' => $chatid,
                        'parse_mode' => 'HTML',
                        'text' => $messages,
                        'reply_markup' => $keyboard,
                        'disable_web_page_preview' => true,

                    );

                    $this->http_post_data('sendMessage', json_encode($parameter));
                    exit();
                } else {
                    $messages = "新增误差金额格式如下：\r\n新的误差金额=+/-100\r\n";
                    $switch_inline_query_current_msg = "#wuchan_tianjia_" . $tongdao_id . "_#\r\n误差金额=+100";
                    $inline_keyboard_arr3[0] = array('text' => "马上添加一个试试 ", "switch_inline_query_current_chat" => $switch_inline_query_current_msg);
                    $keyboard = [
                        'inline_keyboard' => [
                            $inline_keyboard_arr3,
                        ]
                    ];
                    $parameter = array(
                        'chat_id' => $chatid,
                        'parse_mode' => 'HTML',
                        'text' => $messages,
                        'reply_markup' => $keyboard,
                        'disable_web_page_preview' => true,

                    );

                    $this->http_post_data('sendMessage', json_encode($parameter));
                    exit();
                }

            }

            //删除用户组下的某个用户：
            if (strpos($message, 'zdyhshanchu_detail') !== false) {

                $chuge_userid_arr = $this->chaojiyonghu;
                if (!in_array($userid, $chuge_userid_arr)) {
                    $ids_str = implode(",",$chuge_userid_arr);
                    $parameter = array(
                        'chat_id' => $chatid,
                        'parse_mode' => 'HTML',
                        'text' => "仅Tg_ID:".$ids_str."有此权限！"
                    );
                    $this->http_post_data('sendMessage', json_encode($parameter));
                    exit();
                }
                $instruction_arr = explode("zdyhshanchu_detail", $message);
                $zuren_id =$instruction_arr[1];

                $set_sql1 = "select * FROM pay_zuren where id='" . $zuren_id . "'";
                $order_query2 = $this->pdo->query($set_sql1);
                $order_info2 = $order_query2->fetchAll();

                if (!$order_info2) {
                    $parameter = array(
                        'chat_id' => $chatid,
                        'parse_mode' => 'HTML',
                        'text' => "当前用户查询异常！"
                    );
                    $this->http_post_data('sendMessage', json_encode($parameter));
                    exit();
                }
                $set_sql = "DELETE FROM pay_zuren where id='" . $zuren_id . "'";
                $is_shanchu = $this->pdo->exec($set_sql);
                if ($is_shanchu) {
                    $msg = "删除".$order_info2[0]['username']."成功!";
                } else {
                    $msg = "删除".$order_info2[0]['username']."失败!";
                }
                $parameter = array(
                    'chat_id' => $chatid,
                    'text' => $msg
                );
                $this->http_post_data('sendMessage', json_encode($parameter));
                exit();


            }
            //删除用户组：
            if (strpos($message, 'yonghushanchu_detail') !== false) {
                $chuge_userid_arr = $this->chaojiyonghu;
                if (!in_array($userid, $chuge_userid_arr)) {

                    $parameter = array(
                        'chat_id' => $chatid,
                        'parse_mode' => 'HTML',
                        'text' => "仅晴朗/飞龙有此权限！"
                    );
                    $this->http_post_data('sendMessage', json_encode($parameter));
                    exit();
                }
                $info = explode("yonghushanchu_detail", $message);
                $info_two = $info[1];
                $set_sql1 = "select * FROM pay_yonghuzu where id='".$info_two."'";

                $order_query2 = $this->pdo->query($set_sql1);
                $order_info2 = $order_query2->fetchAll();
                if(!$order_info2){
                    $parameter = array(
                        'chat_id' => $chatid,
                        'parse_mode' => 'HTML',
                        'text' => "未查询到你要删除的用户组信息！请核对！"
                    );
                    $this->http_post_data('sendMessage', json_encode($parameter));
                    exit();
                }else{
                    $set_sql = "DELETE FROM pay_yonghuzu where id='" . $info_two . "'";
                    $is_shanchu = $this->pdo->exec($set_sql);
                    if($is_shanchu){
                        //删除这个用户组下面的所有人信息：
                        $set_sql2 = "DELETE FROM pay_zuren where yonghuzu_id='" . $info_two . "'";
                        $is_shanchu2 = $this->pdo->exec($set_sql2);
                        $parameter = array(
                            'chat_id' => $chatid,
                            'parse_mode' => 'HTML',
                            'text' => "删除用户组:".$order_info2[0]['name']."成功！"
                        );
                        $this->http_post_data('sendMessage', json_encode($parameter));
                        exit();
                    }else{
                        $parameter = array(
                            'chat_id' => $chatid,
                            'parse_mode' => 'HTML',
                            'text' => "删除失败！请联系管理员！"
                        );
                        $this->http_post_data('sendMessage', json_encode($parameter));
                        exit();
                    }
                }

            }
            //查看用户下的所有用户列表+命令：
            if (strpos($message, 'yonghu_detail') !== false) {
                $chuge_userid_arr = $this->chaojiyonghu;
                if (!in_array($userid, $chuge_userid_arr)) {
                    $ids_str = implode(",",$chuge_userid_arr);
                    $parameter = array(
                        'chat_id' => $chatid,
                        'parse_mode' => 'HTML',
                        'text' => "仅Tg_ID:".$ids_str."有此权限！"
                    );
                    $this->http_post_data('sendMessage', json_encode($parameter));
                    exit();
                }

                $info = explode("yonghu_detail", $message);
                $info_two =  $info[1];
                $set_sql1 = "select * FROM pay_zuren where yonghuzu_id='".$info_two."'";

                $order_query2 = $this->pdo->query($set_sql1);
                $order_info2 = $order_query2->fetchAll();
                $msg = "<b>用户如下：</b>\r\n";
                if($order_info2){
                    foreach ($order_info2 as $kq=>$ve){
                        $msg .= "<b>".$ve['username']."</b><b><a href='https://t.me/".$this->jiqirenming."?start=zdyhshanchu_detail" . $ve['id'] . "'>删除</a></b>\r\n";
                    }
                }else{
                    $msg .= "当前用户组下未添加用户\r\n";
                }


                $msg .= "\r\n<b>命令如下：</b>\r\n";
                $set_sql2 = "select * FROM pay_yonghuzu where id='".$info_two."'";

                $order_query3 = $this->pdo->query($set_sql2);
                $order_info3 = $order_query3->fetchAll();
                if($order_info3){

                    $mingling_arr = explode(",",$order_info3[0]['mingling']);

                    if(!empty($order_info3[0]['mingling'])){
                        //$msg .= count($mingling_arr)."---当前用户组暂未设置命令";
                        foreach ($mingling_arr as $kq2=>$ve2){
                            $msg .= "<b>".$ve2."</b>   <b><a href='https://t.me/".$this->jiqirenming."?start=minglingshanchu_".$info_two."__".$ve2. "'>删除</a></b>\r\n";
                        }
                    }else{
                        $msg .= "当前用户组暂未设置命令";
                    }

                }else{
                    $msg .= "当前用户组暂未设置命令";
                }

                $switch_inline_query_current_msg1 = "#daoruyonghu###".$info_two."_#\r\n用户列表\r\n@xiaozhang\r\n@xiaohong\r\n@xiaowu";
                $inline_keyboard_arr3[0] = array('text' => "导入用户 ", "switch_inline_query_current_chat" => $switch_inline_query_current_msg1);


                $inline_keyboard_arr3[1] = array('text' =>"清空用户", "callback_data" => "deleteallyonghu###" . $info_two);

                $all_ming_list = $this->all_ming_list;
                $all_msq_str = "";
                foreach ($all_ming_list as $sq=>$sqe){
                    $all_msq_str .="\r\n".$sqe;
                }
                $switch_inline_query_current_msg3 = "#daorumingling###".$info_two."_#\r\n命令列表".$all_msq_str;

                //$switch_inline_query_current_msg3 = "#daorumingling###".$info_two."_#\r\n命令列表\r\nadd_user\r\ntongdao_detail\r\n添加误差\r\n修改误差";
                $inline_keyboard_arr3[2] = array('text' => "导入命令 ", "switch_inline_query_current_chat" => $switch_inline_query_current_msg3);


                $inline_keyboard_arr3[3] = array('text' =>"清空命令", "callback_data" => "deleteallmingling###" . $info_two);

                $keyboard = [
                    'inline_keyboard' => [
                        $inline_keyboard_arr3,
                    ]
                ];

                $parameter = array(
                    'chat_id' => $chatid,
                    'parse_mode' => 'HTML',
                    'text' => $msg,
                    'reply_markup' => $keyboard,
                    'disable_web_page_preview' => true,

                );

                $this->http_post_data('sendMessage', json_encode($parameter));
                exit();

                exit();
            }


            $this->start($chatid);
        }
        
    }

    public function quanxian($chatid, $userid, $quanxian,$username)
    {
        $username = "@".$username;
        if (!in_array($userid, $this->chaojiyonghu)) {

            $set_sql1 = "select * FROM pay_zuren where username='" . $username . "'";
            $order_query2 = $this->pdo->query($set_sql1);
            $order_info2 = $order_query2->fetchAll();
            if(!$order_info2){
                $parameter = array(
                    'chat_id' => $chatid,
                    'parse_mode' => 'HTML',
                    //'text' => "你没有当前   <b>" . $quanxian . "</b>   操作此命令,请联系晴朗@QingLang1688添加权限",
                    'text' => "你没有当前在权限用户组内,请联系晴朗@QingLang1688添加权限",
                );
                $this->http_post_data('sendMessage', json_encode($parameter));
                exit();
            }

            $set_sql2 = "select * FROM pay_yonghuzu where id='" . $order_info2[0]['yonghuzu_id'] . "'";
            $order_query3 = $this->pdo->query($set_sql2);
            $order_info3 = $order_query3->fetchAll();

            if(empty($order_info3[0]['mingling'])){
                $parameter = array(
                    'chat_id' => $chatid,
                    'parse_mode' => 'HTML',
                    'text' => "当前用户组没有此项权限,请联系晴朗@QingLang1688添加",
                );
                $this->http_post_data('sendMessage', json_encode($parameter));
                exit();
            }
            $all_mingling_arr = explode(",",$order_info3[0]['mingling']);
            if(!in_array($quanxian,$all_mingling_arr)){
                $parameter = array(
                    'chat_id' => $chatid,
                    'parse_mode' => 'HTML',
                    'text' => "你没有当前   <b>" . $quanxian . "</b>   操作此命令,请联系晴朗@QingLang1688添加",
                );
                $this->http_post_data('sendMessage', json_encode($parameter));
                exit();
            }

            /*$set_sql1 = "select * FROM pay_instruction where chat_id='" . $chatid . "' and user_str='" . $userid . "' and instruction='" . $quanxian . "'";
            $order_query2 = $this->pdo->query($set_sql1);
            $order_info2 = $order_query2->fetchAll()
            if (!$order_info2) {
                $parameter = array(
                    'chat_id' => $chatid,
                    'parse_mode' => 'HTML',
                    'text' => "你没有当前   <b>" . $quanxian . "</b>   操作此命令,请联系晴朗@QingLang1688添加",
                );
                $this->http_post_data('sendMessage', json_encode($parameter));
                exit();
            }*/
        }


    }

    //添加支付商
    public function addzhifushang($message, $chatid, $userid, $data)
    {

        //删除支付商
        if (strpos($message, 'sczfs') !== false) {
            $set_sql = "DELETE FROM pay_zhifushang where chat_id='" . $chatid . "'";
            $is_shanchu = $this->pdo->exec($set_sql);
            if ($is_shanchu) {
                $msg = "删除成功!";
            } else {
                $msg = "删除失败!";
            }
            $parameter = array(
                'chat_id' => $chatid,
                'text' => $msg
            );
            $this->http_post_data('sendMessage', json_encode($parameter));
            exit();
        }
        //添加支付的==》实际操作
        if (strpos($message, '#tjzfs_addinfo_#') !== false) {
            $roll_arr = explode("#tjzfs_addinfo_#", $message);
            //查看支付商是否已经存在：

            $set_sql1 = "select * FROM pay_zhifushang where chat_id='" . $chatid . "'";
            $order_query2 = $this->pdo->query($set_sql1);
            $order_info2 = $order_query2->fetchAll();
            if ($order_info2) {
                $parameter = array(
                    'chat_id' => $chatid,
                    'text' => "当前群已经添加过支付商，请勿重复添加！解除支付商命令：/sczfs",
                );
                $this->http_post_data('sendMessage', json_encode($parameter));
                exit();
            }
            $changes = explode("\n", trim($roll_arr[1]));
            if (count($changes) != 3) {
                $parameter = array(
                    'chat_id' => $chatid,
                    'text' => "参数不全,请核对后再添加！" . json_encode($changes),
                );
                $this->http_post_data('sendMessage', json_encode($parameter));
                exit();
            }
            $add_name_arr = explode("=", trim($changes[0]));
            $add_name = $add_name_arr[1];


            $add_yufu_arr = explode("=", trim($changes[1]));
            $add_yufu = $add_yufu_arr[1];

            $add_danbao_arr = explode("=", trim($changes[2]));
            $add_danbao = $add_danbao_arr[1];

            $set_sql = "insert into pay_zhifushang (chat_id,name,money,security) values ('" . $chatid . "','" . $add_name . "','" . $add_yufu . "','" . $add_danbao . "')";
            $chang_status = $this->pdo->exec($set_sql);
            if ($chang_status) {
                $msg = "添加支付商成功!";
            } else {
                $msg = "添加支付商失败!";
            }
            $parameter = array(
                'chat_id' => $chatid,
                'parse_mode' => 'HTML',
                'text' => $msg
            );

            $this->http_post_data('sendMessage', json_encode($parameter));
            exit();


        }

        $set_sql1 = "select * FROM pay_zhifushang where chat_id='" . $chatid . "'";
        $order_query2 = $this->pdo->query($set_sql1);
        $order_info2 = $order_query2->fetchAll();
        if ($order_info2) {
            $parameter = array(
                'chat_id' => $chatid,
                'text' => "当前群已经添加过支付商，请勿重复添加！解除支付商命令：/sczfs",
            );
            $this->http_post_data('sendMessage', json_encode($parameter));

            $messages = "存在信息如下：\r\n支付商名称=" . $order_info2[0]['name'] . "\r\n支付商预付=" . $order_info2[0]['money'] . "\r\n支付商担保金=" . $order_info2[0]['security'] . "\r\n";
            $parameter = array(
                'chat_id' => $chatid,
                'text' => $messages
            );
            $this->http_post_data('sendMessage', json_encode($parameter));
            exit();

        }


        $messages = "格式如下：\r\n支付商名称=xxx\r\n支付商预付=1008.00\r\n支付商担保金=100.00\r\n";
        $switch_inline_query_current_msg = "#tjzfs_addinfo_#\r\n支付商名称=xxx\r\n支付商预付=1008.00\r\n支付商担保金=100.00";
        $inline_keyboard_arr3[0] = array('text' => "马上添加一个试试 ", "switch_inline_query_current_chat" => $switch_inline_query_current_msg);
        $keyboard = [
            'inline_keyboard' => [
                $inline_keyboard_arr3,
            ]
        ];
        $parameter = array(
            'chat_id' => $chatid,
            'parse_mode' => 'HTML',
            'text' => $messages,
            'reply_markup' => $keyboard,
            'disable_web_page_preview' => true,

        );

        $this->http_post_data('sendMessage', json_encode($parameter));
        exit();
    }

    //支付商通道
    public function addtongdao($message, $chatid, $userid, $data,$username)
    {

        //sctd 删除支付商通道
        if (strpos($message, 'sctd') !== false) {
            $this->quanxian($chatid, $userid, "sctd",$username);

            $set_sql1 = "select * FROM pay_tongdao where chat_id='" . $chatid . "'";
            $order_query2 = $this->pdo->query($set_sql1);
            $order_info2 = $order_query2->fetchAll();
            if (!$order_info2) {
                $parameter = array(
                    'chat_id' => $chatid,
                    'parse_mode' => 'HTML',
                    'text' => "未查询到当前群有支付商通道信息！请核对！"
                );

                $this->http_post_data('sendMessage', json_encode($parameter));
                exit();
            }
            $message = "<b>请选择你要删除的通道编号[谨慎操作]</b>：\r\n";
            $inline_keyboard_arr = array();
            foreach ($order_info2 as $key => $ve) {
                $message .= ($key + 1) . ":" . $ve['name'] . "\r\n";
                $inline_keyboard_arr[$key] = array('text' => ($key + 1), "callback_data" => "shanchutong###" . $ve['id']);
            }

            $keyboard = [
                'inline_keyboard' => [
                    $inline_keyboard_arr
                ]
            ];

            $parameter = array(
                "chat_id" => $chatid,
                "text" => $message,
                "parse_mode" => "HTML",
                "disable_web_page_preview" => true,
                'reply_markup' => $keyboard
            );
            $this->http_post_data('sendMessage', json_encode($parameter));
            exit();


        }
        //tjtd 添加支付商通道
        if (strpos($message, '#tjtd_addinfo_#') !== false) {
            
             //http://ceshi.freewing1688.xyz/admin/pay_plugin.php?my=refresh
            $shuaxinchaurl = $this->shuaxinchaurl;
            $sasas = Http::get($shuaxinchaurl);
            
            $this->quanxian($chatid, $userid, "tjtd",$username);

            $roll_arr = explode("#tjtd_addinfo_#", $message);

            $changes = explode("\n", trim($roll_arr[1]));
            if (count($changes) != 19) {
                $parameter = array(
                    'chat_id' => $chatid,
                    'text' => "参数不全,请核对后再添加！" . json_encode($changes),
                );
                $this->http_post_data('sendMessage', json_encode($parameter));
                exit();
            }
            $add_name_arr = explode("=", trim($changes[0]));
            $add_name = $add_name_arr[1];

            //查看支付商是否已经存在：
            $set_sql1 = "select * FROM pay_tongdao where name='" . $add_name . "'";
            $order_query2 = $this->pdo->query($set_sql1);
            $order_info2 = $order_query2->fetchAll();
            if ($order_info2) {
                $parameter = array(
                    'chat_id' => $chatid,
                    'text' => "当前" . $add_name . "通道已存在，请勿重复添加!",
                );
                $this->http_post_data('sendMessage', json_encode($parameter));
                exit();
            }

            $add_chajian_arr = explode("=", trim($changes[1]));
            $add_chajian = $add_chajian_arr[1];

            $add_zidingyi_arr = explode("=", trim($changes[2]));
            $add_zidingyi = $add_zidingyi_arr[1];

            $set_sql3 = "select * FROM pay_tongdao where zidingyi='" . $add_zidingyi . "'";
            $order_query3 = $this->pdo->query($set_sql3);
            $order_info4 = $order_query3->fetchAll();
            if ($order_info4) {
                $parameter = array(
                    'chat_id' => $chatid,
                    'text' => "自定义通道编号不允许重复添加，无法添加，自定义编号重复，请更换自定义编号",
                );
                $this->http_post_data('sendMessage', json_encode($parameter));
                exit();
            }
            
            $set_sql4 = "select * FROM pay_plugin where name='" . $add_chajian . "'";
            $order_query4= $this->pdo->query($set_sql4);
            $order_info5 = $order_query4->fetchAll();
            if (!$order_info5) {
                $parameter = array(
                    'chat_id' => $chatid,
                    'text' => "系统并没有该插件,请核对！",
                );
                $this->http_post_data('sendMessage', json_encode($parameter));
                exit();
            }



            $add_type_arr = explode("=", trim($changes[3]));
            $add_type = $add_type_arr[1];

            $set_sql5 = "select * FROM pay_type where showname='" . $add_type . "'";
            $order_query5= $this->pdo->query($set_sql5);
            $order_info6 = $order_query5->fetchAll();
            if (!$order_info6) {
                $parameter = array(
                    'chat_id' => $chatid,
                    'text' => "支付填写方式错误!请核对！",
                );
                $this->http_post_data('sendMessage', json_encode($parameter));
                exit();
            }


            $add_houtai_arr = explode("=", trim($changes[4]));
            $add_houtai = $add_houtai_arr[1];
            $add_zhanghao_arr = explode("=", trim($changes[5]));
            $add_zhanghao = $add_zhanghao_arr[1];
            $add_mima_arr = explode("=", trim($changes[6]));
            $add_mima = $add_mima_arr[1];


            $add_pid_arr = explode("=", trim($changes[7]));
            $add_pid = $add_pid_arr[1];
            $add_miyao_arr = explode("=", trim($changes[8]));
            $add_miyao = $add_miyao_arr[1];
            $add_xiadan_arr = explode("=", trim($changes[9]));
            $add_xiadan = $add_xiadan_arr[1];
            $add_baimingdan_arr = explode("=", trim($changes[10]));
            $add_baimingdan = $add_baimingdan_arr[1];




            $add_number_arr = explode("=", trim($changes[11]));
            $add_number = $add_number_arr[1];

            $add_rate_arr = explode("=", trim($changes[12]));
            $add_rate = $add_rate_arr[1];

            $add_success_rate_arr = explode("=", trim($changes[13]));
            $add_success_rate = $add_success_rate_arr[1];

            $add_is_kangtou_arr = explode("=", trim($changes[14]));
            $add_is_kangtou = $add_is_kangtou_arr[1];

            $add_is_bingfa_arr = explode("=", trim($changes[15]));
            $add_is_bingfa = $add_is_bingfa_arr[1];

            $add_money_arr = explode("=", trim($changes[16]));
            $add_money = $add_money_arr[1];

            $add_time_arr = explode("=", trim($changes[17]));
            $add_time = $add_time_arr[1];

            $add_remarks_arr = explode("=", trim($changes[18]));
            $add_remarks = $add_remarks_arr[1];

            $set_sql = "insert into pay_tongdao (chat_id,chajian,zidingyi,name,type,houtaiurl,houtaizhanghao,houtaimima,pid,miyao,payulr,baimingdan,number,rate,success_rate,is_kangtou,is_bingfa,money,time,remarks) values ('" . $chatid . "','" . $add_chajian . "','". $add_zidingyi . "','" . $add_name . "','" . $add_type ."','"  . $add_houtai ."','"  . $add_zhanghao ."','"  . $add_mima . "','"  . $add_pid . "','" . $add_miyao . "','" . $add_xiadan . "','" . $add_baimingdan . "','". $add_number . "','" . $add_rate . "','" . $add_success_rate . "','" . $add_is_kangtou . "','" . $add_is_bingfa . "','" . $add_money . "','" . $add_time . "','" . $add_remarks . "')";
            $chang_status = $this->pdo->exec($set_sql);
            if ($chang_status) {
                //自动同步几个参数：
                $this->genxintongdao($chatid,$add_zidingyi);
             
                
                $msg = "添加通道" . $add_name . "成功!";
                
                
                
            } else {
                $msg = "添加通道" . $add_name . "失败!";
            }
            $parameter = array(
                'chat_id' => $chatid,
                'parse_mode' => 'HTML',
                'text' => $msg
            );

            $this->http_post_data('sendMessage', json_encode($parameter));
            exit();

        }

        //cktd 查看支付商通道
        if (strpos($message, 'cktd') !== false) {
            $quanxian = "cktd";
            $this->quanxian($chatid, $userid, $quanxian,$username);
            $set_sql1 = "select * FROM pay_tongdao where chat_id='" . $chatid . "'";
            $order_query2 = $this->pdo->query($set_sql1);
            $order_info2 = $order_query2->fetchAll();
            if (!$order_info2) {
                $parameter = array(
                    'chat_id' => $chatid,
                    'text' => "未查询更多的支付通道！请核对是否有添加通道",
                );
                $this->http_post_data('sendMessage', json_encode($parameter));
                exit();
            }

            $message = "查看对应通道信息：\r\n";
            $inline_keyboard_arr = array();
            $zongjinri_moeny = 0;
            $zongzuori_moeny = 0;
            foreach ($order_info2 as $key => $ve) {
                $jinrimoney = 0;
                $zuorimoney = 0;
                
                $chajian = $ve['chajian'];
                $zidingyi = $ve['zidingyi'];
                
                $now_time_1= date("Y-m-d");
                $now_time_2= date("Y-m-d", strtotime("-1 day"));
   
                
                
                /*今日收益*/
                $sql_info_2 = "select sum(a.getmoney) as getmoney from pay_order as a left join pay_channel as b on b.id=a.channel where a.status = '1'  and b.plugin='" . $chajian . "' and a.date='" . $now_time_1 . "' and b.zidingyi='".$zidingyi."'";

                $order_query_jinri = $this->pdo->query($sql_info_2);
                $chatinfo_jinri = $order_query_jinri->fetchAll();
                $jinrimoney = $chatinfo_jinri[0]['getmoney']?$chatinfo_jinri[0]['getmoney']:0;


                /*昨日收益*/
                $sql_info_1 = "select sum(a.getmoney) as getmoney from pay_order as a left join pay_channel as b on b.id=a.channel where a.status = '1'  and b.plugin='" . $chajian . "' and a.date='" . $now_time_2 . "' and b.zidingyi='".$zidingyi."'";

                $order_query_zuori = $this->pdo->query($sql_info_1);
                $chatinfo_zuori = $order_query_zuori->fetchAll();
                $zuorimoney = $chatinfo_zuori[0]['getmoney']?$chatinfo_zuori[0]['getmoney']:0;

                $message .= "<a href='https://t.me/".$this->jiqirenming."?start=tongdao_detail" . $ve['id'] . "'>".($key + 1) ."." . $ve['name'] . "</a>" .  "\r\n";
                $message .="<b>今日：".$jinrimoney;
                $message .="\r\n昨日：".$zuorimoney."</b>\r\n\r\n";
                
                $zongjinri_moeny += $jinrimoney;
                $zongzuori_moeny += $zuorimoney;
                
                $inline_keyboard_arr[$key] = array('text' => ($key + 1), "callback_data" => "tongdao###" . $ve['id']);
            }
             $message .="<b>合计：
今天跑量:".$zongjinri_moeny."
昨天跑量:".$zongzuori_moeny."</b>";

            $keyboard = [
                'inline_keyboard' => [
                    $inline_keyboard_arr
                ]
            ];

            $parameter = array(
                "chat_id" => $chatid,
                "text" => $message,
                "parse_mode" => "HTML",
                "disable_web_page_preview" => true,
                'reply_markup' => $keyboard
            );
            $this->http_post_data('sendMessage', json_encode($parameter));
            exit();
        }


        $messages = "格式如下：\r\n名称=xxx\r\n插件名(英文)=menglong\r\n自定义编号(支付后台)=xiao001\r\n支付方式=支付宝/微信\r\n支付商后台地址=支付商地址\r\n支付商账号=支付商账号\r\n支付商登陆密码=支付商登陆密码\r\n商户ID=商户ID\r\n商户密钥=商户密钥\r\n下单地址=下单地址\r\n白名单(英文逗号隔开)=白名单(英文逗号隔开)\r\n编号=004\r\n费率=16\r\n成率=80\r\n是否抗投=是\r\n能否并发=能\r\n金额范围=10-500\r\n运行时间=早上6点到晚上9点\r\n备注=使劲上单\r\n";
        $switch_inline_query_current_msg = "#tjtd_addinfo_#\r\n名称=xxx\r\n插件名(英文)=menglong\r\n自定义编号(支付后台)=xiao001\r\n支付方式=支付宝/微信\r\n支付商后台地址=支付商地址\r\n支付商账号=支付商账号\r\n支付商登陆密码=支付商登陆密码\r\n商户ID=商户ID\r\n商户密钥=商户密钥\r\n下单地址=下单地址\r\n白名单(英文逗号隔开)=白名单(英文逗号隔开)\r\n编号=004\r\n费率=16\r\n成率=85\r\n是否抗头=是\r\n能否并发=能\r\n金额范围=10-500\r\n运行时间=早上6点到晚上9点\r\n备注=使劲上单";

        $inline_keyboard_arr3[0] = array('text' => "马上添加一个试试 ", "switch_inline_query_current_chat" => $switch_inline_query_current_msg);
        $keyboard = [
            'inline_keyboard' => [
                $inline_keyboard_arr3,
            ]
        ];
        $parameter = array(
            'chat_id' => $chatid,
            'parse_mode' => 'HTML',
            'text' => $messages,
            'reply_markup' => $keyboard,
            'disable_web_page_preview' => true,

        );

        $this->http_post_data('sendMessage', json_encode($parameter));
        exit();
    }

    //自定义更新：
    public function genxintongdao($chatid,$add_zidingyi){
        $set_sql8 = "select dijige FROM pay_channel order by dijige desc";
     
        $order_query8= $this->pdo->query($set_sql8);
        $order_info8 = $order_query8->fetchAll();
        $dijige = $order_info8[0]['dijige']+1; 
        
        
        $set_sql3 = "select * FROM pay_tongdao where zidingyi='" . $add_zidingyi . "'";
       
        
        $order_query3= $this->pdo->query($set_sql3);
        $order_info3 = $order_query3->fetchAll();
        $tongdao_info = $order_info3[0];
        
        
        $set_sql1 = "select * FROM pay_type where showname='" . $tongdao_info['type'] . "'";
        $order_query1= $this->pdo->query($set_sql1);
        $order_info1 = $order_query1->fetchAll();
        
        //查询支付方式的ID
        $type = $order_info1['0']['id'];
      
            $plugin = $tongdao_info['chajian'];
            $name = $tongdao_info['name'];
            $rate = "100.00";
            $status = "0";
            $appid = $tongdao_info['pid'];
            $appkey =$tongdao_info['miyao'];
            $appurl = $tongdao_info['number'];
            $bianhao = $tongdao_info['number'];
            $apiurl =  $tongdao_info['payulr'];
            $pccode = "1";
            $shortlink = "0";
            $beizhu =  $tongdao_info['remarks'];
            $feilv =  $tongdao_info['rate'];
            $chenglv =  $tongdao_info['success_rate'];
            $shifoukangtou =  $tongdao_info['is_kangtou'];
            $nengfoubingfa =  $tongdao_info['is_bingfa'];
            $jinefanwei =  $tongdao_info['money'];
            $yunxingtime =  $tongdao_info['time']; 
         
        
            //添加通道：
            $set_sql = "insert into pay_channel (dijige,type,plugin,name,rate,status,appid,appkey,appurl,apiurl,pccode,shortlink,beizhu,zidingyi,bianhao,feilv,chenglv,shifoukangtou,nengfoubingfa,jinefanwei,yunxingtime) values ('" .$dijige . "','" . $type. "','" . $plugin . "','" .$name . "','" . $rate . "','" . $status . "','" . $appid . "','" . $appkey . "','" . $appurl. "','" . $apiurl . "','" . $pccode . "','" . $shortlink . "','" . $beizhu . "','" . $add_zidingyi . "','" . $bianhao . "','" . $feilv . "','" . $chenglv . "','" . $shifoukangtou . "','" . $nengfoubingfa . "','" . $jinefanwei . "','" . $yunxingtime . "')";
             $is_insert = $this->pdo->exec($set_sql);
             if($is_insert){
                $parameter = array(
                    'chat_id' => $chatid,
                    'text' => "添加插件至后台成功！"
                );
                $this->http_post_data('sendMessage', json_encode($parameter));
             }else{
                  $parameter = array(
                    'chat_id' => $chatid,
                    'text' => "添加插件至后台失败！"
                );
                $this->http_post_data('sendMessage', json_encode($parameter));
             }
           
       
    }


    public function callback($data)
    {


        $text = $data['callback_query']['data'];
        $chat_id = $data['callback_query']['message']['chat']['id'];
        $from_id = $data['callback_query']['from']['id'];
        $username = $data['callback_query']['from']['username'];
        $message_id = $data['callback_query']['message']['message_id'];


        //私聊机器人删除通道：
         if (strpos($text, 'jqdelete') !== false) {
            $this->quanxian($chat_id, $from_id, "jqdelete",$username);
            $instruction_arr2 = explode("###", $text);
            $tongdao_id = $instruction_arr2[1];
            $set_sql1 = "select * FROM pay_tongdao where id='" . $tongdao_id . "'";
            $order_query2 = $this->pdo->query($set_sql1);
            $order_info2 = $order_query2->fetchAll();
            
            $zidingyi_bianhao = $order_info2[0]['zidingyi'];
            
            if(!$order_info2){
                 $parameter = array(
                    'chat_id' => $chat_id,
                    'parse_mode' => 'HTML',
                    'text' => "通道信息查询异常！请核对！"
                );

                $this->http_post_data('sendMessage', json_encode($parameter));
                exit();
            }
            $set_sql = "DELETE FROM pay_tongdao where id='" . $tongdao_id . "'";
            $is_shanchu = $this->pdo->exec($set_sql);
            if ($is_shanchu) {
                $msg = "<b>删除通道成功!</b>";
            } else {
                $msg = "<b>删除通道失败!</b>";
            }
            $this->http_post_data('sendMessage', json_encode($parameter));

            $set_sql2 = "DELETE FROM pay_channel where zidingyi='" . $zidingyi_bianhao . "'";
            $is_shanchu2 = $this->pdo->exec($set_sql2);
            if ($is_shanchu2) {
                $msg2 = "<b>同步通道删除通道成功!</b>";
            } else {
                $msg2 = "<b>同步通道删除通道失败!</b>";
            }
            
            $parameter = array(
                'chat_id' => $chat_id,
                'text' => $msg2,
                'parse_mode' => 'HTML',
            );
            $this->http_post_data('sendMessage', json_encode($parameter));
            exit();
         }


        //删除用户组所有的用户
        if (strpos($text, 'deleteallyonghu') !== false) {

            $chuge_userid_arr = $this->chaojiyonghu;
            if (!in_array($from_id, $chuge_userid_arr)) {
                $ids_str = implode(",",$chuge_userid_arr);
                $parameter = array(
                    'chat_id' => $chat_id,
                    'parse_mode' => 'HTML',
                    'text' => "仅Tg_ID:".$ids_str."有此权限！"
                );
                $this->http_post_data('sendMessage', json_encode($parameter));
                exit();
            }
            $instruction_arr = explode("deleteallyonghu###", $text);

            $yonghzuid = $instruction_arr[1];


            $set_sql1 = "select * FROM pay_zuren where yonghuzu_id='" . $yonghzuid . "'";
            $order_query2 = $this->pdo->query($set_sql1);
            $order_info2 = $order_query2->fetchAll();

            if (!$order_info2) {
                $parameter = array(
                    'chat_id' => $chat_id,
                    'parse_mode' => 'HTML',
                    'text' => "当前用户下没有用户！请核对！"
                );
                $this->http_post_data('sendMessage', json_encode($parameter));
                exit();
            }

            $set_sql = "DELETE FROM pay_zuren where yonghuzu_id='" . $yonghzuid . "'";
            $is_gengxin =  $this->pdo->exec($set_sql);
            if ($is_gengxin) {
                $msg = "<b>成功!</b>:  清空用户组下的所有用户";
            } else {
                $msg = "<b>失败!</b>:  清空用户组下的所有用户";
            }
            $parameter = array(
                'chat_id' => $chat_id,
                'text' => $msg,
                'parse_mode' => 'HTML',
            );
            $this->http_post_data('sendMessage', json_encode($parameter));
            exit();
        }
        // 删除用户组所有的命令
        if (strpos($text, 'deleteallmingling') !== false) {

            $chuge_userid_arr = $this->chaojiyonghu;
            if (!in_array($from_id, $chuge_userid_arr)) {
                $ids_str = implode(",",$chuge_userid_arr);
                $parameter = array(
                    'chat_id' => $chat_id,
                    'parse_mode' => 'HTML',
                    'text' => "仅Tg_ID:".$ids_str."有此权限！"
                );
                $this->http_post_data('sendMessage', json_encode($parameter));
                exit();
            }
            $instruction_arr = explode("deleteallmingling###", $text);
            $instruction_id = $instruction_arr[1];
            $instruction_arr2 = explode("###", $instruction_id);
            $yonghzuid = $instruction_arr2[0];
            $yonghzumingling = $instruction_arr2[1];

            $set_sql1 = "select * FROM pay_yonghuzu where id='" . $yonghzuid . "'";
            $order_query2 = $this->pdo->query($set_sql1);
            $order_info2 = $order_query2->fetchAll();

            if (empty($order_info2[0]['mingling'])) {
                $parameter = array(
                    'chat_id' => $chat_id,
                    'parse_mode' => 'HTML',
                    'text' => "当前用户组下的命令是空的！请核对！"
                );
                $this->http_post_data('sendMessage', json_encode($parameter));
                exit();
            }

            $all_mingling_arr_str ="";
            $set_sql = "update pay_yonghuzu set mingling='" . $all_mingling_arr_str . "' where id='" . $yonghzuid . "'";
            $is_gengxin =  $this->pdo->exec($set_sql);
            if ($is_gengxin) {
                $msg = "<b>成功清空用户组下的命令</b>";
            } else {
                $msg = "<b>失败清空用户组下的命令</b>";
            }
            $parameter = array(
                'chat_id' => $chat_id,
                'text' => $msg,
                'parse_mode' => 'HTML',
            );
            $this->http_post_data('sendMessage', json_encode($parameter));
            exit();
        }


        //delcjuser  删除超级用户ID：
        if (strpos($text, 'delcjuser') !== false) {

            $instruction_arr = explode("###", $text);
            $instruction_id = $instruction_arr[1];

            $chuge_userid_arr = $this->chaojiyonghu;
            if (!in_array($from_id, $chuge_userid_arr)) {
                $ids_str = implode(",",$chuge_userid_arr);
                $parameter = array(
                    'chat_id' => $chat_id,
                    'parse_mode' => 'HTML',
                    'text' => "仅Tg_ID:".$ids_str."有此权限！"
                );
                $this->http_post_data('sendMessage', json_encode($parameter));
                exit();
            }
            $set_sql1 = "select * FROM pay_chaojiuser where id='" . $instruction_id . "'";
            $order_query2 = $this->pdo->query($set_sql1);
            $order_info2 = $order_query2->fetchAll();

            if (!$order_info2) {
                $parameter = array(
                    'chat_id' => $chat_id,
                    'parse_mode' => 'HTML',
                    'text' => "未查询到此用户！异常！"
                );
                $this->http_post_data('sendMessage', json_encode($parameter));
                exit();
            }


            $set_sql = "DELETE FROM pay_chaojiuser where id='" . $instruction_id . "'";
            $is_shanchu = $this->pdo->exec($set_sql);
            if ($is_shanchu) {
                $msg = "<b>删除成功!</b>:  超级用户ID: <b>" . $order_info2[0]['user_id'] . "</b> ";
            } else {
                $msg = "<b>删除失败!</b>:  超级用户ID: <b>" . $order_info2[0]['user_id'] . "</b>";
            }
            $parameter = array(
                'chat_id' => $chat_id,
                'text' => $msg,
                'parse_mode' => 'HTML',
            );
            $this->http_post_data('sendMessage', json_encode($parameter));
            exit();
        }

        //delqml  删除指定用户命令：
        if (strpos($text, 'delqml') !== false) {

            $instruction_arr = explode("###", $text);
            $instruction_id = $instruction_arr[1];
            $chuge_userid_arr = $this->chaojiyonghu;
            if (!in_array($from_id, $chuge_userid_arr)) {
                $ids_str = implode(",",$chuge_userid_arr);
                $parameter = array(
                    'chat_id' => $chat_id,
                    'parse_mode' => 'HTML',
                    'text' => "仅Tg_ID:".$ids_str."有此权限！"
                );
                $this->http_post_data('sendMessage', json_encode($parameter));
                exit();
            }
            $set_sql1 = "select * FROM pay_instruction where id='" . $instruction_id . "'";
            $order_query2 = $this->pdo->query($set_sql1);
            $order_info2 = $order_query2->fetchAll();

            if (!$order_info2) {
                $parameter = array(
                    'chat_id' => $chat_id,
                    'parse_mode' => 'HTML',
                    'text' => "未查询到用户有此权限,请核对！"
                );
                $this->http_post_data('sendMessage', json_encode($parameter));
                exit();
            }


            $set_sql = "DELETE FROM pay_instruction where id='" . $instruction_id . "'";
            $is_shanchu = $this->pdo->exec($set_sql);
            if ($is_shanchu) {
                $msg = "<b>成功!</b>:  删除指定人ID: <b>" . $order_info2[0]['user_str'] . "</b> 在群ID：<b>" . $order_info2[0]['chat_id'] . "</b> 使用 <b>" . $order_info2[0]['instruction'] . "</b> 的命令!";
            } else {
                $msg = "<b>失败!</b>:   删除指定人ID: <b>" . $order_info2[0]['user_str'] . "</b> 在群ID：<b>" . $order_info2[0]['chat_id'] . "</b> 使用 <b>" . $order_info2[0]['instruction'] . "</b> 的命令!";
            }
            $parameter = array(
                'chat_id' => $chat_id,
                'text' => $msg,
                'parse_mode' => 'HTML',
            );
            $this->http_post_data('sendMessage', json_encode($parameter));
            exit();
        }


        //关闭/开启   关闭/开启指定通道
        if (strpos($text, 'guanbitongdao') !== false) {

            $tongdao_arr = explode("###", $text);
            $tongdao_id = $tongdao_arr[1];
            $set_sql1 = "select * FROM pay_tongdao where id='" . $tongdao_id . "'";
            $order_query2 = $this->pdo->query($set_sql1);
            $order_info2 = $order_query2->fetchAll();

            if (!$order_info2) {
                $parameter = array(
                    'chat_id' => $chat_id,
                    'parse_mode' => 'HTML',
                    'text' => "查询异常！"
                );

                $this->http_post_data('sendMessage', json_encode($parameter));
                exit();
            }

            if ($order_info2[0]['status'] == "0") {
                $change_st = "1";
                $quanxian = "关闭";
            } else {
                $change_st = "0";
                $quanxian = "开启";
            }

            $this->quanxian($chat_id, $from_id, $quanxian,$username);

            $set_sql = "update pay_tongdao set status='" . $change_st . "' where id='" . $tongdao_id . "'";
            $change_status = $this->pdo->exec($set_sql);
            if ($change_status) {
                if ($order_info2[0]['status'] == "0") {
                    $msg = "关闭通道" . $order_info2[0]['name'] . "成功";
                } else {
                    $msg = "开启通道" . $order_info2[0]['name'] . "成功";
                }

            } else {
                $msg = "通道" . $order_info2[0]['name'] . "操作失败!";
            }

            $zidingyi_str = $order_info2[0]['zidingyi'];
            $set_sql2 = "select * FROM pay_channel where zidingyi='" . $zidingyi_str . "'";
            $order_query3 = $this->pdo->query($set_sql2);
            $order_info3 = $order_query3->fetchAll();
            if($order_info3){
                //存在
                if($change_st == 0){
                    $change_st2 = 1;
                }else{
                    $change_st2 = 0;
                }
                $set_sql2 = "update pay_channel set status='" . $change_st2 . "' where zidingyi='" . $zidingyi_str . "'";
                $change_status2 = $this->pdo->exec($set_sql2);
                if($change_status2){
                    $msg .= "\r\n后台同步状态成功！";
                }else{
                    $msg .= "\r\n后台同步状态失败！请核对";
                }
            }else{
                //不存在
                $msg .= "\r\n但是后台并没有此通道，所以无法同步状态！请核对";
            }

            $parameter = array(
                'chat_id' => $chat_id,
                'text' => $msg
            );
            $this->http_post_data('sendMessage', json_encode($parameter));
            exit();
        }


        //cktd  查看指定通道信息：
        if (strpos($text, 'tongdao') !== false) {

            $this->quanxian($chat_id, $from_id, "cktd",$username);
            $tongdao_arr = explode("###", $text);
            $tongdao_id = $tongdao_arr[1];
            $set_sql1 = "select * FROM pay_tongdao where id='" . $tongdao_id . "'";
            $order_query2 = $this->pdo->query($set_sql1);
            $order_info2 = $order_query2->fetchAll();

            if (!$order_info2) {
                $parameter = array(
                    'chat_id' => $chat_id,
                    'parse_mode' => 'HTML',
                    'text' => "查询异常！"
                );

                $this->http_post_data('sendMessage', json_encode($parameter));
                exit();
            }
            $info = $order_info2[0];

            /*名称:支付宝原生 (https://g.com/)(点蓝字查详细信息)
            状态:切停
            今天成率:
            今天跑量:11028
            昨天成率:
            昨天跑量:12545(误差0/去核对)*/
            if ($info['status'] == "0") {
                $status = "开启";
            } elseif ($info['status'] == "1") {
                $status = "切停";
            }


            //<a href='https://t.me/tianshidierg_bot?start=zhifu_detail" . $v['id'] . "'>" . $v['id'] . "</a>

            $chajian = $info['chajian'];
            $zidingyi = $info['zidingyi'];
            $today = date("Y-m-d");

            $now_time = date("Y-m-d 00:00:00", strtotime("-1 day"));
            $end_time = date("Y-m-d 00:00:00");

            $sql_info = "select sum(a.getmoney) as getmoney from pay_order as a left join pay_channel as b on b.id=a.channel where a.status = '1'  and b.plugin='" . $chajian . "' and a.date='" . $today . "' and b.zidingyi='".$zidingyi."'";

            $order_query2 = $this->pdo->query($sql_info);
            $chatinfo = $order_query2->fetchAll();


            $detai_info_today = $chatinfo[0]['getmoney'];
            if ($detai_info_today > 0) {
                $sql_info2 = "select sum(a.getmoney) as getmoney from pay_order as a left join pay_channel as b on b.id=a.channel where b.plugin='" . $chajian . "' and a.date='" . $today . "' and b.zidingyi='".$zidingyi."'";

                $order_query3 = $this->pdo->query($sql_info2);
                $chatinfo2 = $order_query3->fetchAll();


                $detai_info_today_cheng = (($chatinfo2[0]['getmoney'] / $chatinfo2[0]['getmoney']) * 100) . "%";

            } else {
                $detai_info_today_cheng = "0%";
                $detai_info_today = "0";
            }

            //  ---------昨日：----------

            $sql_info_zuori = "select sum(a.getmoney) as getmoney from pay_order as a left join pay_channel as b on b.id=a.channel where a.status = '1'  and b.plugin='" . $chajian . "' and a.addtime between '" . $now_time . "' and '" . $end_time . "' and b.zidingyi='".$zidingyi."'";

            $order_query2_zuori = $this->pdo->query($sql_info_zuori);
            $chatinfo_zuori = $order_query2_zuori->fetchAll();

            $detai_info_paoliang = $chatinfo_zuori[0]['getmoney'];
            if ($detai_info_paoliang > 0) {


                $sql_info2_zuori = "select sum(a.getmoney) as getmoney from pay_order as a left join pay_channel as b on b.id=a.channel where b.plugin='" . $chajian . "' and a.addtime between '" . $now_time . "' and '" . $end_time . "' and b.zidingyi='".$zidingyi."'";
                $order_query3_zuori = $this->pdo->query($sql_info2_zuori);
                $chatinfo2_zuori = $order_query3_zuori->fetchAll();
                $detai_info_money = (($chatinfo_zuori[0]['getmoney'] / $chatinfo_zuori[0]['getmoney']) * 100) . "%";
            } else {
                $detai_info_paoliang = 0;
                $detai_info_money = "0%";
            }


            //查询是否有通道误差的金额：
            $sql_info_wucha = "select * from pay_tongdaowucha where tongdao_id='" . $tongdao_id . "' and date='" . $today . "'";

            $order_query2_wucha = $this->pdo->query($sql_info_wucha);
            $chatinfo_wucha = $order_query2_wucha->fetchAll();
            if ($chatinfo_wucha) {
                $wucha_money = $chatinfo_wucha[0]['money'];
            } else {
                $wucha_money = "0";
            }


            $messages = "
            名称:<b><a href='https://t.me/".$this->jiqirenming."?start=tongdao_detail" . $info['id'] . "'>" . $info['name'] . "</a></b>
<b>状态:" . $status . "</b>
今天成率:" . $detai_info_today_cheng . "
今天跑量:" . $detai_info_today . "元
昨天成率:" . $detai_info_money . "
昨天跑量:" . $detai_info_paoliang . "元(误差:" . $wucha_money . "元/<b><a href='https://t.me/".$this->jiqirenming."?start=tongdao_wucha" . $tongdao_id . "'>去核对</a></b>)";


            //  $messages = "通道信息如下：\r\n名称=".$info['name']."\r\n支付方式=".$info['type']."\r\n编号=".$info['number']."\r\n费率=".$info['rate']."\r\n成率=".$info['success_rate']."\r\n是否抗投=".$info['is_kangtou']."\r\n能否并发=".$info['is_bingfa']."\r\n金额范围=".$info['money']."\r\n运行时间=".$info['time']."\r\n备注=".$info['remarks']."\r\n";
            $parameter = array(
                'chat_id' => $chat_id,
                'parse_mode' => 'HTML',
                'text' => $messages
            );

            $this->http_post_data('sendMessage', json_encode($parameter));
            exit();

        }
        //删除指定通道信息
        if (strpos($text, 'shanchutong') !== false) {

// $this->quanxian($chatid, $userid, "sctd",$username);
            $this->quanxian($chat_id, $from_id, "sctd",$username);

            $tongdao_arr = explode("###", $text);
            $tongdao_id = $tongdao_arr[1];
            $set_sql1 = "select * FROM pay_tongdao where id='" . $tongdao_id . "'";
            $order_query2 = $this->pdo->query($set_sql1);
            $order_info2 = $order_query2->fetchAll();
            
            $zidingyi = $order_info2[0]['zidingyi'];
            
            if (!$order_info2) {
                $parameter = array(
                    'chat_id' => $chat_id,
                    'parse_mode' => 'HTML',
                    'text' => "查询异常！可能已经被删除！"
                );

                $this->http_post_data('sendMessage', json_encode($parameter));
                exit();
            }
            $info = $order_info2[0];
            $set_sql = "DELETE FROM pay_tongdao where id='" . $tongdao_id . "'";
            $is_shanchu = $this->pdo->exec($set_sql);

            if ($is_shanchu) {
                $msg = "删除成功!";
                
                //删除后台的通道：
                $set_sql2 = "select * FROM pay_channel where zidingyi='" . $zidingyi . "'";
                $order_query3 = $this->pdo->query($set_sql2);
                $order_info3 = $order_query3->fetchAll();
                if(!$order_info3){
                    $parameter = array(
                        'chat_id' => $chat_id,
                        'text' => "同步删除后台失败,后台未查询到对应的自定义通道"
                    );
                    $this->http_post_data('sendMessage', json_encode($parameter));
                }else{
                
                    $set_sql2 = "DELETE FROM pay_channel where zidingyi='" . $zidingyi . "'";
                    $is_shanchu2 = $this->pdo->exec($set_sql2);
                    if($is_shanchu2){
                        $parameter = array(
                            'chat_id' => $chat_id,
                            'text' => "同步通道删除后台成功！"
                        );
                        $this->http_post_data('sendMessage', json_encode($parameter));
                    }else{
                        $parameter = array(
                            'chat_id' => $chat_id,
                            'text' => "同步删除后台失败,后台未查询到对应的自定义通道"
                        );
                        $this->http_post_data('sendMessage', json_encode($parameter));
                    }
                }
                
                $messages = "通道：" . $info['name'] . "已删除";
            } else {
                $msg = "删除失败!";
                $messages = "通道：" . $info['name'] . "删除失败";
            }
            $parameter = array(
                'chat_id' => $chat_id,
                'text' => $messages
            );
            $this->http_post_data('sendMessage', json_encode($parameter));
            exit();


        }
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


    //系统后台：
    public function start($chatid)
    {
        /*$keyboard2 = [
            'keyboard' => [
                [

                    ['text' => '查看商户列表'],
                    ['text' => '新增商户汇率'],
                ],
            ],
            'resize_keyboard' => true,
            'one_time_keyboard' => false,

        ];
        $encodedKeyboard2 = json_encode($keyboard2);*/


        $parameter = array(
            'chat_id' => $chatid,
            'text' => "禁止复用！请重新到用户群重新点击操作！",
            // 'reply_markup' => $encodedKeyboard2
        );
        //设置当前用户进入后台：


        //发送消息

        $this->http_post_data('sendMessage', json_encode($parameter));
        exit();

    }


}

$oen = new five();
$oen->index();

?>
