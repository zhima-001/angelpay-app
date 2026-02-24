<?php

/*require 'vendor/autoload.php';

use Telegram\Bot\Api;

include "two.php";*/


class Http
{

    public function sendPostRequest($url, $data = [], $headers = [])
    {
        // 初始化cURL会话
        $ch = curl_init($url);

        // 将传递的数据格式化为URL编码字符串
        $postData = http_build_query($data);

        // 设置cURL选项
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);  // 指定请求方式为POST
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);  // 传递POST数据
        if (!empty($headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);  // 设置HTTP请求头
        }

        // 执行请求并获取响应
        $response = curl_exec($ch);

        // 检查是否发生错误
        if (curl_errno($ch)) {
            $error_msg = curl_error($ch);
            curl_close($ch);
            return "cURL Error: $error_msg";
        }

        // 关闭cURL会话
        curl_close($ch);

        // 返回响应
        return $response;
    }

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
    // private $token = '5313902856:AAEIQRhZIH6DOc2itLEig_D9ojdtOCkiAgY';  //token
    //  private $ownerAddress = "TD7Gv9m4BdMXqbsRgxPfYJSPQ81gEqJZT2";

    private $kaiqi_teshu_xiafa = false; //是否开启特殊日期下发  默认：false  开启：true
    private $teshu_riqi = "2024-4-26"; //需要下发那一天的

    private $tianshi_bot_url = "";
    private $link = "";
    private $chat_url = "";
    private $pay_pay_url = "";
    private $private_key = "";
    private $rocket_url = "";
    private $token = "";
    private $ownerAddress = "";
    private $huilv_api = "";
    private $laoban_chatid = "";
    private $telegram;
    private $guding_fudian;
    private $istuisong;
    private $pdo;
    private $jiqirenming;
    private $chaojiyonghu;
    private $all_ming_list = array(

        '/userxq',           ///user10000  【实际操作命令】
        '订单管理',           //订单管理
        '商户管理',    //修改/添加误差                           超级用户【如何设置给别人可以拥有】
        '其他功能',    //修改/添加误差                           超级用户【如何设置给别人可以拥有】
        '结算管理',   //通道的详细信息                          超级用户【如何设置给别人可以拥有】
        'shrate',               //预付修改                                 超级用户【如何设置给别人可以拥有】
        'cdrate',            //保证金修改                            超级用户【如何设置给别人可以拥有】
        '呼叫24h客服',
        'tongzhidel',
        'tousu_kouchu_',
        '投诉扣除'

    );

    public function __construct()
    {

        include "cron_jiqi.php";
        $this->istuisong = false;

        $this->tojiesuan = 10;

        $this->token = $token;
        $this->rocket_url = $rocket_url;
        $this->chat_url = $chat_url;
        $this->pay_pay_url = $pay_pay_url;
        $this->private_key = $private_key;
        $this->chaojiyonghu = $chaojiyonghu;
        $this->ownerAddress = $ownerAddress;
        $this->jiqirenming = $jiqirenming_tianshizhifu;
        $this->tianshi_bot_url = "https://t.me/" . $jiqirenming_tianshizhifu;
        $token = $this->token;
        $this->link = 'https://api.telegram.org/bot' . $token . '';
        $this->huilv_api = $huilv_api;
        $this->laoban_chatid = $laoban_chatid;
        $this->guding_fudian = $guding_fudian;
        /*$dbHost = "127.0.0.1";
        $dbName = "chpay";
        $dbUser = "chpay";
        $dbPassword = "RpyZXiK4DLSscRTk";*/

        //$this->pdo = new PDO("mysql:host=" . $dbHost . ";dbname=" . $dbName, $dbUser, $dbPassword, array(PDO::ATTR_PERSISTENT => true));
        $this->pdo = new PDO(
            "mysql:host=" . $dbHost . ";dbname=" . $dbName . ";charset=utf8mb4",
            $dbUser,
            $dbPassword,
            [
                PDO::ATTR_PERSISTENT => true,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
            ]
        );
        //这里查下是不是开启了：
        $kefus_sql = "select * FROM pay_config";
        $kefus_query = $this->pdo->query($kefus_sql);
        $xiafa_info = $kefus_query->fetchAll();
        foreach ($xiafa_info as $ksa => $saq) {
            if ($saq['k'] == "zidingyixiafa") {
                if ($saq['v'] == "1") {
                    $this->kaiqi_teshu_xiafa = true;
                } else {
                    $this->kaiqi_teshu_xiafa = false;
                }
            }
            if ($saq['k'] == "xiafariqi") {
                $this->teshu_riqi = $saq['v'];
            }
            if ($saq['k'] == "tojiesuan") {
                if ($saq['v'] > 0) {
                    $this->tojiesuan = $saq['v'];
                }

            }
        }


    }


    public function index()
    {


        $data = json_decode(file_get_contents('php://input'), TRUE); //读取json并对其格式化
        $datatype = $data['message']['chat']['type'];//获取message


        $sql = "insert into pay_jiqi (content) values ('" . json_encode($data) . "')";
        $this->pdo->exec($sql);


        if ($data['callback_query']) {
            $this->callback($data);
        } else {


            $chatid = $data['message']['chat']['id'];//获取chatid


            $message = $data['message']['text'];//获取message
            $userid = $data['message']['from']['id'];//获取message


            //这里需要判断一下是不是正在进行客服聊天，如果是的话，需要直接走客服逻辑:
            $kefu_sql = "select * FROM pay_userchat where chat_id ='" . $chatid . "' and channel='" . $this->token . "' and status='0'";
            $kefu_query = $this->pdo->query($kefu_sql);
            $kefu_info = $kefu_query->fetchAll();
            if ($kefu_info) {
                $token = $kefu_info[0]['visitorToken'];
                $room_id = $kefu_info[0]['room_id'];
                $agentId = $kefu_info[0]['agent_id'];
                $kefu_username = $kefu_info[0]['kefu_name'];
                $message_kefu = $data['message'];
                $chatId = $message_kefu['chat']['id'];
                if (isset($message_kefu['text'])) {
                    // 处理文字消息
                    $text = $message_kefu['text'];


                    $this->sendMessageToRocketChat($text, $room_id, $token, $agentId, $kefu_username, $chatid);
                } elseif (isset($message_kefu['photo'])) {
                    // 处理图片消息
                    $photo = $message_kefu['photo'];
                    $fileId = end($photo)['file_id'];

                    $filePath = $this->getTelegramFilePath($fileId);

                    $localFilePath = $this->downloadTelegramFile($filePath);
                    if ($localFilePath == "error") {
                        $this->xiaoxi("发送照片失败,请稍后再试试！", $chatid);
                    }
                    $description = "";
                    if (!empty($message_kefu['caption'])) {
                        $description = $message_kefu['caption'];
                    }
                    $fileUrl = $this->uploadFileToLiveChatRoom($localFilePath, $room_id, $token, $description, $chatid);
                    $this->xiaoxi($fileUrl, $chatid);
                    if ($fileUrl && isset($fileUrl['file'])) {
                        $fileUrl = $this->chat_url . $fileUrl['file']['path'];
                        $this->sendImageMessage($room_id, $token, "![image]($fileUrl)");
                    }

                }


                $kefu_xiaoxi_url = $this->chat_url . "api/v1/livechat/message";
                $qingqiuti = array(
                    'token' => $kefu_info['0']['visitorToken'],
                    'rid' => $kefu_info['0']['room_id'],
                    'msg' => "",
                    "agent" => array(
                        "agentId" => $kefu_info['0']['agent_id'],
                        'username' => $kefu_info['0']['kefu_name'],
                    )
                );
            }

            // $this->xiaoxinoend($datatype,$chatid);
            if ($datatype == "private") {
                $set_sql1 = "select id FROM pay_jiqichat where chat_id ='" . $chatid . "' and typelist='1'";
                $order_query2 = $this->pdo->query($set_sql1);
                $order_info2 = $order_query2->fetchAll();
                //  $this->xiaoxinoend(json_encode($order_info2),$chatid);
                if (!$order_info2) {
                    $sql = "insert into pay_jiqichat (from_id,chat_id,typelist) values ('" . $userid . "','" . $chatid . "','1')";
                    // $this->xiaoxinoend($sql,$chatid);
                    $this->pdo->exec($sql);
                }
            } elseif ($datatype == "group" || $datatype == "supergroup") {
                $set_sql1 = "select id FROM pay_jiqichat where chat_id ='" . $chatid . "' and typelist='2'";
                $order_query2 = $this->pdo->query($set_sql1);
                $order_info2 = $order_query2->fetchAll();
                // $this->xiaoxinoend(json_encode($order_info2),$chatid);
                if (!$order_info2) {
                    $sql = "insert into pay_jiqichat (from_id,chat_id,typelist) values ('" . $userid . "','" . $chatid . "','2')";
                    //  $this->xiaoxinoend($sql,$chatid);
                    $this->pdo->exec($sql);
                }
            }


            $this->message($message, $chatid, $data, $userid);
        }


    }

    public function chaojiyonghuquanxian($userid, $chatid)
    {
        $chuge_userid_arr = $this->chaojiyonghu;
        if (!in_array($userid, $chuge_userid_arr)) {
            $ids_str = implode(",", $chuge_userid_arr);
            $parameter = array(
                'chat_id' => $chatid,
                'parse_mode' => 'HTML',
                'text' => "仅Tg_ID:" . $ids_str . "有此权限！"
            );
            $this->http_post_data('sendMessage', json_encode($parameter));
            exit();
        }
    }

    public function chadanya($order_sn, $chatid)
    {

        /*🅿️订单号:2022062114155153521
            🆔商户订单号:62b16213ad4e5e50bb31
            📱终端:pc
            🧑‍💻商户号:1003
            💰订单金额:50元
            ♻️支付方式:🦋支付宝
            🔧支付插件:bujingyun
            🔎IP地址:34.75.87.355
            ⏱创建时间:2022-06-21 15:46:07
            ⏰完成时间:2022-06-21 15:46:07
            ⭕️支付状态:已完成✅
            📣通知状态:已通知✅

            ⚙️操作:改已完成 (https://goo.com/)~重新通知 (https://g.com/)~删除订单 (https://chh.com/)*/
        //A.trade_no,A.out_trade_no,A.terminals,A.uid,A.money,A.ip,A.addtime,A.endtime,A.status
        $sql = "select A.*,B.name as channel_name from pay_order as A left join pay_channel as B on A.channel = B.id where A.trade_no='" . $order_sn . "' or A.out_trade_no='" . $order_sn . "'";
        $order_query = $this->pdo->query($sql);
        $order_info = $order_query->fetchAll();
        if (!$order_info) {
            $this->xiaoxi("未查到!", $chatid);
        }
        $messages = "";
        foreach ($order_info as $key => $detai_info) {
            if ($detai_info['type'] == "1") {
                $change_type = "🦋支付宝";
            } else {
                $change_type = "🍀微信";
            }
            if ($detai_info['status'] == "1") {
                $change_type2 = "已完成✅";
            } else {
                $change_type2 = "未完成✖";
            }
            if (!empty($detai_info['date'])) {
                $change_type3 = "已通知✅";
            } else {
                $change_type3 = "未通知✖";
            }
            $messages .= "
🅿️订单号: `" . $detai_info['trade_no'] . "`
🆔商户订单号: `" . $detai_info['out_trade_no'] . "`
📱终端: " . $detai_info['terminals'] . "
🧑‍💻商户号: " . $detai_info['uid'] . "
💰订单金额: " . $detai_info['money'] . "元
♻️支付方式: " . $change_type . "
🔧支付插件: " . $detai_info['channel_name'] . "
🔎IP地址: " . $detai_info['ip'] . "
⏱创建时间: " . $detai_info['addtime'] . "
⏰完成时间: " . $detai_info['endtime'] . "
⭕️支付状态: " . $change_type2 . "
📣通知状态: " . $change_type3;
            $messages .= "\r\n\r\n";
        }

        $parameter = [
            'chat_id' => $chatid,
            'parse_mode' => 'Markdown', // 或 'HTML'
            'text' => $messages,
            'disable_web_page_preview' => true
        ];


        $this->http_post_data('sendMessage', json_encode($parameter));
        exit();

    }
    public function jiesuanday($jiesuan_day, $chatid){
        // 构建当月的日期字符串，例如：24 -> 2024-01-24
        $current_year = date("Y");
        $current_month = date("m");
        $today = $current_year . "-" . $current_month . "-" . str_pad($jiesuan_day, 2, "0", STR_PAD_LEFT);

        // 验证日期是否有效
        if (!checkdate($current_month, $jiesuan_day, $current_year)) {
            $this->xiaoxi("日期无效，请输入有效的日期，例如：/js24", $chatid);
            return;
        }

        // 查询该日期有收益的所有用户
        $sql_info = "select uid from pay_order where status = '1' and date='" . $today . "' group by uid";
        $order_query2 = $this->pdo->query($sql_info);
        $chatinfo = $order_query2->fetchAll();

        if (empty($chatinfo)) {
            $this->xiaoxi($jiesuan_day . "号暂无收益数据", $chatid);
            return;
        }

        // 获取汇率信息
        $huilvinfo = $this->huilvinfo("99999", "99999");

        // 获取TRX手续费
        $trx_info = "select * from pay_usertrx";
        $trx_jinri = $this->pdo->query($trx_info);
        $trx_arr = $trx_jinri->fetchAll();
        $trx_shouxufei = $trx_arr ? $trx_arr[0]['trx'] : 0.00;

        // 获取支付类型信息
        $sql_zhifu = "select id,showname from pay_type";
        $zhifu_fetch = $this->shujuku($sql_zhifu);
        $zhifu_info_arr = array();
        foreach ($zhifu_fetch as $kp => $vp) {
            $zhifu_info_arr[$vp['id']] = $vp['showname'];
        }

        $all_user_settle = array(); // 存储所有用户的结算信息
        $total_settle = 0; // 总结算金额

        foreach ($chatinfo as $k => $v) {
            $uid = trim($v['uid']);

            // 获取商户信息
            $sql_info3 = "select username,usdt_str from pay_user where uid ='" . $uid . "'";
            $order_query7 = $this->pdo->query($sql_info3);
            $chatinfo3 = $order_query7->fetchAll();

            if (empty($chatinfo3)) {
                continue;
            }

            $uidinfo2 = $chatinfo3[0];
            $uid_arr = explode("|", $uid);

            // 只处理单个商户号的情况
            if (count($uid_arr) > 1) {
                continue;
            }

            // 获取商户的费率信息
            $fufonginfo = $this->fudonginfo($uid, $chatid);
            $fenchenginfo = $this->fenchenginfo($uid, $chatid);
            $tongdaoxinxi = $this->tongdaoxinxi($uid, $chatid);
            $zhifuxinxi = $this->zhifuxinxi($uid, $chatid);

            if (count($zhifuxinxi) <= 0) {
                continue;
            }

            // 计算实际汇率
            $type = substr($fufonginfo, 0, 1);
            if ($type == "-") {
                $changs = explode("-", $fufonginfo);
                $shiji_huilv = $huilvinfo - $changs[1];
            } else {
                $changs = explode("+", $fufonginfo);
                $shiji_huilv = $huilvinfo + $changs[1];
            }

            // 查询该商户该日期的所有订单
            $sql_info = "select * from pay_order where status = '1' and uid ='" . $uid . "' and date='" . $today . "'";
            $order_query3 = $this->pdo->query($sql_info);
            $zuoorderinfo = $order_query3->fetchAll();

            if (empty($zuoorderinfo)) {
                continue;
            }

            // 计算各支付方式下的各个通道数据
            $all_tongdao_zhifu = array();
            $feilihoujiner = 0;

            foreach ($zuoorderinfo as $key => $value) {
                $kv = $value['type'];
                $kp = $value['channel'];
                $vp = $value['money'];

                if (!isset($all_tongdao_zhifu[$kv])) {
                    $all_tongdao_zhifu[$kv] = array();
                }
                if (!isset($all_tongdao_zhifu[$kv][$kp])) {
                    $all_tongdao_zhifu[$kv][$kp] = 0;
                }
                $all_tongdao_zhifu[$kv][$kp] += $vp;
            }

            // 计算收益
            $all_usdt_m = 0;
            foreach ($all_tongdao_zhifu as $kv => $vv) {
                foreach ($vv as $kp => $vp) {
                    if (array_key_exists($kp, $tongdaoxinxi)) {
                        $zhifu_lixi = $tongdaoxinxi[$kp];
                    } else {
                        $zhifu_lixi = $zhifuxinxi[$kv];
                    }

                    $jisuan = round(($vp * $zhifu_lixi * $fenchenginfo) / ($shiji_huilv), 2);
                    $feilihoujiner += round(($vp * $zhifu_lixi * $fenchenginfo), 2);
                    $all_usdt_m += $jisuan;
                }
            }

            // 查询投诉退款
            $tousu_info2 = "select * from pay_usertousu where pid ='" . $uid . "'";
            $order_tousu2 = $this->pdo->query($tousu_info2);
            $tousu_m2 = $order_tousu2->fetchAll();

            $tousu_U = 0;
            $jinritimne = date("Y-m-d", time());
            foreach ($tousu_m2 as $k => $v) {
                if ($v['status'] == "1") {
                    if ($jinritimne == $v['koushijian']) {
                        $tousu_U += $v['money'];
                    }
                } else {
                    $tousu_U += $v['money'];
                }
            }

            // 查询昨日下发记录
            $jinri_info = "select money,jutishijian,feiu_money,feilv from pay_jinrixiafa where status='1' and pid ='" . $uid . "' and xiafatime='" . $today . "'";
            $order_jinri = $this->pdo->query($jinri_info);
            $tjinri_arr = $order_jinri->fetchAll();

            $all_jinri_xiafa = 0.00;
            if ($tjinri_arr) {
                foreach ($tjinri_arr as $kj => $vj) {
                    $all_jinri_xiafa += $vj['feiu_money'];
                }
            }

            // 计算可下发金额
            $jie_all_jin_u = $all_jinri_xiafa > 0 ? $all_jinri_xiafa : 0;
            $jie_all_tou_u = $tousu_U > 0 ? round($tousu_U, 2) : 0;
            $jie_all_usdt_m = round($all_usdt_m, 2);

            $keyixiafa = $jie_all_usdt_m - $jie_all_jin_u - $jie_all_tou_u - $trx_shouxufei;
            $shijixiafa_value = (floor((($feilihoujiner - $all_jinri_xiafa - $jie_all_tou_u) / $shiji_huilv) * 100)) / 100 - $trx_shouxufei;

            if ($shijixiafa_value > 0) {
                $all_user_settle[] = array(
                    'uid' => $uid,
                    'username' => $uidinfo2['username'],
                    'usdt_str' => $uidinfo2['usdt_str'],
                    'settle_amount' => round($shijixiafa_value, 2)
                );
                $total_settle += round($shijixiafa_value, 2);
            }
        }

        // 格式化输出
        $msg = $jiesuan_day . "号\r\n";
        $msg .= "结算总和：" . round($total_settle, 2) . "u\r\n\r\n";

        foreach ($all_user_settle as $user_settle) {
            $msg .= $user_settle['uid'] . "-" . $user_settle['usdt_str'] . "-" . $user_settle['settle_amount'] . "u\r\n";
        }

        if (empty($all_user_settle)) {
            $msg = $jiesuan_day . "号暂无可结算数据";
        }

        $this->xiaoxi($msg, $chatid);
    }
    public function message($message, $chatid, $data, $userid)
    {

        $sql_info = "select * from pay_botsettle where chatid ='" . $chatid . "'";
        $order_query2 = $this->pdo->query($sql_info);
        $userbotsettle_info2 = $order_query2->fetchAll();

        $dapid = $userbotsettle_info2[0]['merchant'];

        $username = $data['message']['from']['username'];//用户名称
        if (strpos($message, '/delete_tousu') !== false) {
            $quanxian = "删除投诉";
            $this->quanxian($chatid, $userid, $quanxian, $username);
            //查询当前商户是否存在
            $info_arr = explode("tousu_", $message);

            $info_arr_2 = explode("@", $info_arr['1']);
            $tous_id = $info_arr_2[0];

            //群关联记录删除
            $sql_info = "delete from pay_usertousu where id ='" . $tous_id . "'";
            $this->pdo->exec($sql_info);

            $parameter = array(
                'chat_id' => $chatid,
                'parse_mode' => 'HTML',
                'text' => "投诉信息删除成功"

            );
            $this->http_post_data('sendMessage', json_encode($parameter));
            exit();

        }
        //结算统计：
        if (strpos($message, '/js') === 0 || strpos($message, 'js') === 0) {
            if($chatid !="-1003371180227"){
                $this->xiaoxi("仅内部群可查！", $chatid);
            }
            $orderarr = explode("js", $message);
            $jiesuan_day = $orderarr[1];
            if (empty($jiesuan_day)) {
                $this->xiaoxi("请输入你要查询的结算日,例如：/js24", $chatid);
            }
            //查询对应的结算日：
            $this->jiesuanday(trim($jiesuan_day), $chatid);
        }
        //查单
        if (strpos($message, '/查单') === 0 || strpos($message, '查单') === 0) {
            $orderarr = explode("单", $message);
            $order_sn = $orderarr[1];
            if (empty($order_sn)) {
                $this->xiaoxi("请输入你要查询的订单号,例如：/查单 xxxxxxxxxxxxxxxxxxxx", $chatid);
            }
            //查询对应的订单：
            $this->chadanya(trim($order_sn), $chatid);
        }
        $from_id = "";
        //订单查询
        if (strpos($message, '订单查询') === 0) {
            $this->dingdanguanli($chatid, $from_id, 1, $dapid);
        }
        if (strpos($message, '订单特殊查询') === 0) {
            $ty = explode("查询", $message);
            $dapids = $ty[1];
            $this->dingdanguanli($chatid, $from_id, 1, $dapids);
        }


//        if (strpos($message, 'delete_tousu_') === 0) {
//            ///$quanxian = "delete_tousu_";
//            //$this->quanxian($chatid, $userid, $quanxian, $username);
//            // 提取投诉 ID
//            $ty = explode("delete_tousu_", $message);
//            $tousu_id = $ty[1];
//
//            // 删除数据库中的投诉记录
//            $delete_sql = "DELETE FROM pay_usertousu WHERE id='" . $tousu_id . "'";
//            $this->pdo->exec($delete_sql);
//        }


        if (strpos($message, '/gaibiandingdan_') !== false) {
            $ty = explode("_", $message);
            $uid = $ty[1];  //1=实时  2=昨日
            $sql_info = "select * from pay_order where uid ='" . $uid . "' and status='1' limit 20";

            $order_info2 = $this->shujuku($sql_info);

            $sql_info3 = "select * from pay_uidcao where uid ='" . $uid . "' order by id desc";

            $order_info3 = $this->shujuku($sql_info3);
            $zuixin = "无记录";
            if ($order_info3) {
                $zuixin = $order_info3[0]['date'];
            }

            $msg = "商户：" . $order_info2[0]['uid'] . "，最新查看时间：" . $zuixin . "\r\n\r\n";
            foreach ($order_info2 as $k => $v) {

                $msg .= $v['addtime'] . "--><b>" . $v['trade_no'] . "--" . $v['money'] . "<a href='https://t.me/" . $this->jiqirenming . "?start=gaibiandinaqagdanya" . $v['trade_no'] . "'>修改</a></b>" . "\r\n";
            }
            $parameter = array(
                'chat_id' => $chatid,
                'parse_mode' => 'HTML',
                'text' => $msg
            );

            $this->http_post_data('sendMessage', json_encode($parameter));


        }

        if ($message == "Trx手续费") {
            $sql_info = "select * from pay_usertrx";
            $order_info3 = $this->shujuku($sql_info);
            if ($order_info3) {

                $pay_str2 = $order_info3[0]['trx'];

                $msg = "<b>你当前的Trx手续费如下:</b>\r\n\r\n" . $pay_str2;
                $switch_inline_query_current_msg = "#usertrxshouxu_tianjia_#\r\n" . "Trx手续费=" . $pay_str2;
                $inline_keyboard_arr3[0] = array('text' => "修改Trx手续费 ", "switch_inline_query_current_chat" => $switch_inline_query_current_msg);
                $keyboard = [
                    'inline_keyboard' => [
                        $inline_keyboard_arr3,
                    ]
                ];
            } else {
                $msg = "<b>你尚未设置Trx手续费，请设置</b>";
                $switch_inline_query_current_msg = "#usertrxshouxu_tianjia_#\r\n" . "Trx手续费=1.00";
                $inline_keyboard_arr3[0] = array('text' => "设置Trx手续费 ", "switch_inline_query_current_chat" => $switch_inline_query_current_msg);
                $keyboard = [
                    'inline_keyboard' => [
                        $inline_keyboard_arr3,
                    ]
                ];
            }
            $parameter = array(
                'chat_id' => $chatid,
                'parse_mode' => 'HTML',
                'text' => $msg,
                'reply_markup' => $keyboard,
                'disable_web_page_preview' => true,
            );

            $this->http_post_data('sendMessage', json_encode($parameter));
            exit();
        }

        if (strpos($message, 'usertrxshouxu_tianjia_') !== false) {
            $oneinfo = explode("usertrxshouxu_tianjia_#", $message);
            $info_two = explode("\n", $oneinfo[1]);
            unset($info_two[0]);
            $arr = explode("=", $info_two[1]);

            $trx_val = $arr[1];

            $sql_info1 = "select id from pay_usertrx";
            $order_info1 = $this->shujuku($sql_info1);
            if ($order_info1) {
                $set_sql = "DELETE FROM pay_usertrx";
                $this->pdo->exec($set_sql);
            }
            $set_sql2 = "insert into pay_usertrx (trx) values ('" . $trx_val . "')";
            $chang_status = $this->pdo->exec($set_sql2);
            if ($chang_status) {
                $this->xiaoxi("设置Trx手续费成功！", $chatid);
            } else {
                $this->xiaoxi("注意,设置Trx手续费失败！", $chatid);
            }

        }

        //实时，昨日信息修改：
        if (strpos($message, 'jishixianzai_xiugai_') !== false) {
            $this->chaojiyonghuquanxian($userid, $chatid);
            $ty = explode("#", $message);
            $ty2 = explode("_", $ty[1]);
            $type = $ty2[2];  //1=实时  2=昨日

            //$this->xiaoxi(json_encode($ty),$chatid);


            if ($type == "1") {

                $info_two = explode("\n", $ty[3]);
                $info_three = explode("\n", $ty[4]);
                unset($info_two[0]);
                unset($info_three[0]);

                $shifoukaiqi_arr = explode(":", $info_two[1]);
                $shifoukaiqi = $shifoukaiqi_arr[1];
                $jiner_arr = explode(":", $info_two[2]);
                $jiner = $jiner_arr[1];
                $cishu_arr = explode(":", $info_two[3]);
                $cishu = $cishu_arr[1];

                $shifoukaiqi_arr2 = explode(":", $info_three[1]);
                $shifoukaiqi2 = $shifoukaiqi_arr2[1];
                $jiner_arr2 = explode(":", $info_three[2]);
                $jiner2 = $jiner_arr2[1];
                $cishu_arr2 = explode(":", $info_three[3]);
                $cishu2 = $cishu_arr2[1];


                //手动
                $sql_1 = "update pay_xiafashezhi set svalue='" . $shifoukaiqi . "' where pid='" . $dapid . "' and type='" . $type . "' and typelist='1' and leixing='1'";
                $sql_2 = "update pay_xiafashezhi set svalue='" . $jiner . "' where pid='" . $dapid . "' and type='" . $type . "' and typelist='2' and leixing='1'";
                $sql_3 = "update pay_xiafashezhi set svalue='" . $cishu . "' where pid='" . $dapid . "' and type='" . $type . "' and typelist='3' and leixing='1'";

                //自动
                $sql_4 = "update pay_xiafashezhi set svalue='" . $shifoukaiqi2 . "' where pid='" . $dapid . "' and type='" . $type . "' and typelist='1' and leixing='2'";
                $sql_5 = "update pay_xiafashezhi set svalue='" . $jiner2 . "' where pid='" . $dapid . "' and type='" . $type . "' and typelist='2' and leixing='2'";
                $sql_6 = "update pay_xiafashezhi set svalue='" . $cishu2 . "' where pid='" . $dapid . "' and type='" . $type . "' and typelist='3' and leixing='2'";


                $sql_list1 = $this->shujuku("select id from pay_xiafashezhi where typelist='1' and leixing='1' and pid='" . $dapid . "' and type='" . $type . "'");
                if ($sql_list1) {
                    $this->pdo->exec($sql_1);
                } else {
                    $typelists = "1";
                    $set_sql = "insert into pay_xiafashezhi (pid,type,typelist,svalue,leixing) values ('" . $dapid . "','" . $type . "','" . $typelists . "','" . $shifoukaiqi . "','1')";
                    $this->pdo->exec($set_sql);
                }
                $sql_list2 = $this->shujuku("select id from pay_xiafashezhi where typelist='2' and leixing='1' and pid='" . $dapid . "' and type='" . $type . "'");
                if ($sql_list2) {
                    $this->pdo->exec($sql_2);
                } else {
                    $typelists = "2";
                    $set_sql = "insert into pay_xiafashezhi (pid,type,typelist,svalue,leixing) values ('" . $dapid . "','" . $type . "','" . $typelists . "','" . $jiner . "','1')";
                    $this->pdo->exec($set_sql);
                }

                $sql_list3 = $this->shujuku("select id from pay_xiafashezhi where typelist='3' and leixing='1' and pid='" . $dapid . "' and type='" . $type . "'");
                if ($sql_list3) {
                    $this->pdo->exec($sql_3);
                } else {
                    $typelists = "3";
                    $set_sql = "insert into pay_xiafashezhi (pid,type,typelist,svalue,leixing) values ('" . $dapid . "','" . $type . "','" . $typelists . "','" . $cishu . "','1')";
                    $this->pdo->exec($set_sql);
                }
                //---------------------------------------------------------

                $sql_list4 = $this->shujuku("select id from pay_xiafashezhi where typelist='1' and leixing='2' and pid='" . $dapid . "' and type='" . $type . "'");
                if ($sql_list4) {
                    $this->pdo->exec($sql_4);
                } else {
                    $typelists = "1";
                    $set_sql = "insert into pay_xiafashezhi (pid,type,typelist,svalue,leixing) values ('" . $dapid . "','" . $type . "','" . $typelists . "','" . $shifoukaiqi2 . "','2')";
                    $this->pdo->exec($set_sql);
                }
                $sql_list5 = $this->shujuku("select id from pay_xiafashezhi where typelist='2' and leixing='2' and pid='" . $dapid . "' and type='" . $type . "'");
                if ($sql_list5) {
                    $this->pdo->exec($sql_5);
                } else {
                    $typelists = "2";
                    $set_sql = "insert into pay_xiafashezhi (pid,type,typelist,svalue,leixing) values ('" . $dapid . "','" . $type . "','" . $typelists . "','" . $jiner2 . "','2')";
                    $this->pdo->exec($set_sql);
                }

                $sql_list6 = $this->shujuku("select id from pay_xiafashezhi where typelist='3' and leixing='2' and pid='" . $dapid . "' and type='" . $type . "'");
                if ($sql_list6) {
                    $this->pdo->exec($sql_6);
                } else {
                    $typelists = "3";
                    $set_sql = "insert into pay_xiafashezhi (pid,type,typelist,svalue,leixing) values ('" . $dapid . "','" . $type . "','" . $typelists . "','" . $cishu2 . "','2')";
                    $this->pdo->exec($set_sql);
                }

            } else {

                $info_two = explode("\n", $ty[3]);
                $info_three = explode("\n", $ty[4]);
                unset($info_two[0]);
                unset($info_three[0]);

                $shifoukaiqi_arr = explode("=", $info_two[1]);
                $shifoukaiqi = $shifoukaiqi_arr[1];

                $shifoukaiqi_arr2 = explode("=", $info_three[1]);
                $shifoukaiqi2 = $shifoukaiqi_arr2[1];

                $shijian_arr = explode("=", $info_three[2]);
                $shijian = $shijian_arr[1];


                //手动
                $sql_1 = "update pay_xiafashezhi set svalue='" . $shifoukaiqi . "' where pid='" . $dapid . "' and type='" . $type . "' and typelist='1' and leixing='1'";

                //自动
                $sql_2 = "update pay_xiafashezhi set svalue='" . $shifoukaiqi2 . "' where pid='" . $dapid . "' and type='" . $type . "' and typelist='1' and leixing='2'";
                $sql_3 = "update pay_xiafashezhi set svalue='" . $shijian . "' where pid='" . $dapid . "' and type='" . $type . "' and typelist='2' and leixing='2'";


                $sql_list1 = $this->shujuku("select id from pay_xiafashezhi where typelist='1' and leixing='1' and pid='" . $dapid . "' and type='" . $type . "'");
                if ($sql_list1) {
                    $this->pdo->exec($sql_1);
                } else {
                    $typelists = "1";
                    $set_sql = "insert into pay_xiafashezhi (pid,type,typelist,svalue,leixing) values ('" . $dapid . "','" . $type . "','" . $typelists . "','" . $shifoukaiqi . "','1')";
                    $this->pdo->exec($set_sql);
                }


                $sql_list2 = $this->shujuku("select id from pay_xiafashezhi where typelist='1' and leixing='2' and pid='" . $dapid . "' and type='" . $type . "'");
                if ($sql_list2) {
                    $this->pdo->exec($sql_2);
                } else {
                    $typelists = "1";
                    $set_sql = "insert into pay_xiafashezhi (pid,type,typelist,svalue,leixing) values ('" . $dapid . "','" . $type . "','" . $typelists . "','" . $shifoukaiqi2 . "','2')";
                    $this->pdo->exec($set_sql);
                }

                $sql_list3 = $this->shujuku("select id from pay_xiafashezhi where typelist='2' and leixing='2' and pid='" . $dapid . "' and type='" . $type . "'");
                if ($sql_list3) {
                    $this->pdo->exec($sql_3);
                } else {
                    $typelists = "2";
                    $set_sql = "insert into pay_xiafashezhi (pid,type,typelist,svalue,leixing) values ('" . $dapid . "','" . $type . "','" . $typelists . "','" . $shijian . "','2')";
                    $this->pdo->exec($set_sql);
                }


            }


            $this->xiaoxi("调整成功！", $chatid);
        }

        //修改分成比例信息
        if (strpos($message, 'usertongfencheng_tianjia_#') !== false) {
            $this->chaojiyonghuquanxian($userid, $chatid);

            $sql_info = "select * from pay_botsettle where chatid ='" . $chatid . "'";

            $order_info2 = $this->shujuku($sql_info);
            $pid = $order_info2[0]['merchant'];

            $oneinfo = explode("usertongfencheng_tianjia_#", $message);
            $info_two = explode("\n", $oneinfo[1]);

            unset($info_two[0]);
            unset($info_two[1]);
            $times = date("Y-m-d H:i:s");
            $typelist = "5";

            $ve = $info_two[2];
            $arr = explode("=", $ve);
            $typename = $arr[0];
            $typevalue = $arr[1];

            //查询ID：
            $typeid = "fencheng";

            $sql_info2 = "select id from pay_userfeilv where typelist='" . $typelist . "' and pid ='" . $pid . "' and chatid='" . $chatid . "' and type='" . $typeid . "'";
            $order_info2 = $this->shujuku($sql_info2);
            if ($order_info2) {
                $ids = $order_info2[0]['id'];
                //存在
                $set_sql2 = "update pay_userfeilv set feilv ='" . $typevalue . "' where  id='" . $ids . "'";
                $chang_status = $this->pdo->exec($set_sql2);
            } else {
                //不存在
                $set_sql2 = "insert into pay_userfeilv (pid,chatid,type,createtime,typelist,feilv) values ('" . $pid . "','" . $chatid . "','" . $typeid . "','" . $times . "','" . $typelist . "','" . $typevalue . "')";
                $chang_status = $this->pdo->exec($set_sql2);
            }


            $this->xiaoxi("设置分成比例成功！", $chatid);

        }
        //调整：
        if (strpos($message, 'duolkasy') !== false) {
            $this->changes($message, $chatid);
        }
        //修改U币浮动信息
        if (strpos($message, 'usertongfudong_tianjia_#') !== false) {
            $this->chaojiyonghuquanxian($userid, $chatid);
            $sql_info = "select * from pay_botsettle where chatid ='" . $chatid . "'";

            $order_info2 = $this->shujuku($sql_info);
            $pid = $order_info2[0]['merchant'];

            $oneinfo = explode("usertongfudong_tianjia_#", $message);
            $info_two = explode("\n", $oneinfo[1]);

            unset($info_two[0]);
            unset($info_two[1]);
            $times = date("Y-m-d H:i:s");
            $typelist = "3";

            $ve = $info_two[2];
            $arr = explode("=", $ve);
            $typename = $arr[0];
            $typevalue = $arr[1];

            //查询ID：
            $typeid = "fudong";

            $sql_info2 = "select id from pay_userfeilv where typelist='" . $typelist . "' and pid ='" . $pid . "' and chatid='" . $chatid . "' and type='" . $typeid . "'";
            $order_info2 = $this->shujuku($sql_info2);
            if ($order_info2) {
                $ids = $order_info2[0]['id'];
                //存在
                $set_sql2 = "update pay_userfeilv set feilv ='" . $typevalue . "' where  id='" . $ids . "'";
                $chang_status = $this->pdo->exec($set_sql2);
            } else {
                //不存在
                $set_sql2 = "insert into pay_userfeilv (pid,chatid,type,createtime,typelist,feilv) values ('" . $pid . "','" . $chatid . "','" . $typeid . "','" . $times . "','" . $typelist . "','" . $typevalue . "')";
                $chang_status = $this->pdo->exec($set_sql2);
            }


            $this->xiaoxi("设置成功！", $chatid);

        }
        //修改U币汇率信息
        if (strpos($message, 'usertonghuilv_tianjia_#') !== false) {
            $this->chaojiyonghuquanxian($userid, $chatid);

            $pid = "99999";

            $oneinfo = explode("usertonghuilv_tianjia_#", $message);
            $info_two = explode("\n", $oneinfo[1]);

            unset($info_two[0]);
            unset($info_two[1]);
            $times = date("Y-m-d H:i:s");
            $typelist = "4";

            $ve = $info_two[2];
            $arr = explode("=", $ve);
            $typename = $arr[0];
            $typevalue = $arr[1];

            $typeid = "huilv";
            $chatid_all = "99999";
            $sql_info2 = "select id from pay_userfeilv where typelist='" . $typelist . "' and pid ='" . $pid . "' and chatid='" . $chatid_all . "' and type='" . $typeid . "'";
            $order_info2 = $this->shujuku($sql_info2);
            if ($order_info2) {
                $ids = $order_info2[0]['id'];
                //存在
                $set_sql2 = "update pay_userfeilv set feilv ='" . $typevalue . "' where  id='" . $ids . "'";
                $chang_status = $this->pdo->exec($set_sql2);
            } else {
                //不存在
                $set_sql2 = "insert into pay_userfeilv (pid,chatid,type,createtime,typelist,feilv) values ('" . $pid . "','" . $chatid_all . "','" . $typeid . "','" . $times . "','" . $typelist . "','" . $typevalue . "')";
                $chang_status = $this->pdo->exec($set_sql2);
            }

            $this->xiaoxi("设置统一费率成功！", $chatid);

        }
        //修改商户汇率信息
        if (strpos($message, 'usertongdaofeilv_tianjia_#') !== false) {
            $this->chaojiyonghuquanxian($userid, $chatid);
            $sql_info = "select * from pay_botsettle where chatid ='" . $chatid . "'";

            $order_info2 = $this->shujuku($sql_info);
            $pid = $order_info2[0]['merchant'];

            $oneinfo = explode("usertongdaofeilv_tianjia_#", $message);
            $info_two = explode("\n", $oneinfo[1]);

            unset($info_two[0]);
            unset($info_two[1]);
            $times = date("Y-m-d H:i:s");
            $typelist = "2";

            foreach ($info_two as $ke => $ve) {
                $arr = explode("=", $ve);
                $typename = $arr[0];
                $typevalue = $arr[1];

                //查询ID：
                $typeid = $typename;

                $sql_info2 = "select id from pay_userfeilv where typelist='" . $typelist . "' and pid ='" . $pid . "' and chatid='" . $chatid . "' and type='" . $typeid . "'";
                $order_info2 = $this->shujuku($sql_info2);
                if ($order_info2) {
                    $ids = $order_info2[0]['id'];
                    //存在
                    $set_sql2 = "update pay_userfeilv set feilv ='" . $typevalue . "' where  id='" . $ids . "'";
                    $chang_status = $this->pdo->exec($set_sql2);
                } else {
                    //不存在
                    $set_sql2 = "insert into pay_userfeilv (pid,chatid,type,createtime,typelist,feilv) values ('" . $pid . "','" . $chatid . "','" . $typeid . "','" . $times . "','" . $typelist . "','" . $typevalue . "')";
                    $chang_status = $this->pdo->exec($set_sql2);
                }


            }
            $this->xiaoxi("设置通达费率成功！", $chatid);

        }
        //添加 商户汇率信息
        if (strpos($message, 'userzhifufeilv_tianjia_#') !== false) {

            //$this->chaojiyonghuquanxian($userid, $chatid);
            $quanxian = "支付费率设置";
            $this->quanxian($chatid, $userid, $quanxian, $username);

            $sql_info = "select * from pay_botsettle where chatid ='" . $chatid . "'";

            $order_info2 = $this->shujuku($sql_info);
            $pid = $order_info2[0]['merchant'];

            $oneinfo = explode("userzhifufeilv_tianjia_#", $message);


            $info_two = explode("\n", $oneinfo[1]);
            unset($info_two[0]);
            unset($info_two[1]);
            $times = date("Y-m-d H:i:s");
            $typelist = "1";

            foreach ($info_two as $ke => $ve) {
                $arr = explode("=", $ve);
                $typename = $arr[0];
                $typevalue = $arr[1];

                //查询ID：
                $sql_info1 = "select id from pay_type where showname='" . $typename . "'";
                $order_info1 = $this->shujuku($sql_info1);
                if (!$order_info1) {
                    $this->xiaoxi("添加信息中" . $typename . "这个信息不存在系统中，请核对！", $chatid);
                }

            }



            foreach ($info_two as $ke => $ve) {
                $arr = explode("=", $ve);
                $typename = $arr[0];
                $typevalue = $arr[1];

                //查询ID：
                $sql_info1 = "select id from pay_type where showname='" . $typename . "'";
                $order_info1 = $this->shujuku($sql_info1);
                if ($order_info1) {

                }

                $typeid = $order_info1[0]['id'];

                $sql_info2 = "select id from pay_userfeilv where typelist='" . $typelist . "' and pid ='" . $pid . "' and chatid='" . $chatid . "' and type='" . $typeid . "'";
                $order_info2 = $this->shujuku($sql_info2);
                if ($order_info2) {
                    $ids = $order_info2[0]['id'];
                    //存在
                    $set_sql2 = "update pay_userfeilv set feilv ='" . $typevalue . "' where  id='" . $ids . "'";
                    $chang_status = $this->pdo->exec($set_sql2);
                } else {
                    //不存在
                    $set_sql2 = "insert into pay_userfeilv (pid,chatid,type,createtime,typelist,feilv) values ('" . $pid . "','" . $chatid . "','" . $typeid . "','" . $times . "','" . $typelist . "','" . $typevalue . "')";
                    $chang_status = $this->pdo->exec($set_sql2);
                }


            }
            $this->xiaoxi("设置支付费率成功！", $chatid);

        }
        if (strpos($message, 'qyaozhi_roll_') !== false) {
            $quanxian = "拉取订单";
            $this->quanxian($chatid, $userid, $quanxian, $username);
            // $this->chaojiyonghuquanxian($userid, $chatid);

            $roll_arr = explode("*", $message);
            $moeny_arr = explode("支付金额:", $message);

            //  $this->xiaoxi(json_encode($roll_arr),$chatid);

            $roll_ids = explode("###", $roll_arr[1]);

            $roll_id = $roll_ids[0];
            $pid = $roll_ids[1];

            $money = $moeny_arr[1];
            if (!is_numeric($money)) {
                $parameter = array(
                    'chat_id' => $chatid,
                    'text' => "你输入的金额：" . $money . "！此格式错误，请直接输入数字，例如：50",
                    'show_alert' => true
                );
                $this->http_post_data('sendMessage', json_encode($parameter));

                exit();
            }


            //huidiaourl
            $set_sql1 = "select * FROM pay_config where k ='huidiaourl'";
            $order_query2 = $this->pdo->query($set_sql1);
            $order_info2 = $order_query2->fetchAll();
            $huidiaourl = $order_info2[0]['v'];


            $sql_info = "select * from pay_botsettle where chatid ='" . $chatid . "'";
            $order_query3 = $this->pdo->query($sql_info);
            $order_info3 = $order_query3->fetchAll();
            if (!$order_info3) {
                //已經綁定群了：
                $parameter = array(
                    'chat_id' => $chatid,
                    'parse_mode' => 'HTML',
                    'text' => "当前群尚未绑定商户号"

                );
                $this->http_post_data('sendMessage', json_encode($parameter));
                exit();
            }
            //$pid = $order_info3[0]['merchant'];


            $sql_info9 = "select * from pay_type where id ='" . $roll_id . "'";
            $order_query9 = $this->pdo->query($sql_info9);
            $order_info9 = $order_query9->fetchAll();
            $type = $order_info9[0]['name'];


            $mysgin = "";
            $domain = "";
            $arr = array(
                'pid' => $pid,
                'out_trade_no' => date("YmdHis") . rand(11111, 99999),
                'notify_url' => $huidiaourl,
                'return_url' => $huidiaourl,
                'name' => "telegram测试下单",
                'money' => $money,
                'sitename' => "telegram测试下单",
                'sign' => $mysgin,
                'sign_type' => "MD5",
                'stype' => "0",
                'terminals' => "PC",
                'domain' => $domain,
                'clientip' => "127.0.0.1",
                'type' => $type,
            );
            $post_url = $huidiaourl . "pushsubmit2.php";
            $get_data = trim($this->send_post($post_url, $arr));

            $pp = explode("__", $get_data);

            $set_sql5 = "select * FROM pay_config where k ='appurl'";
            $order_query5 = $this->pdo->query($set_sql5);
            $order_info5 = $order_query5->fetchAll();
            $appurl = trim($order_info2[0]['v']);


            $pay_url = "`$appurl" . "pay/" . $pp[0] . "/qrcode/" . $pp[1] . "/?sitename=VIP" . "`";

            $parameter = array(
                'chat_id' => $chatid,
                'parse_mode' => 'MarkDown',
                'text' => "将此链接复制到浏览器访问：\r\n\r\n" . $pay_url
            );

            $this->http_post_data('sendMessage', json_encode($parameter));
        }


        //导入用户组下的用户： daoruyonghu_
        if (strpos($message, 'daoruyonghu') !== false) {
            $this->chaojiyonghuquanxian($userid, $chatid);


            $info = explode("###", $message);
            $info2 = explode("_#", $info[1]);
            $yonghuzuid = $info2[0];

            $info_two = explode("\n", $info2[1]);
            unset($info_two[0]);
            unset($info_two[1]);
            $now_arr = array();
            foreach ($info_two as $k => $v) {
                $now_arr[] = $v;
            }

            foreach ($now_arr as $kpeople => $vpeople) {

                //检验权限是否存在：
                $set_sql1 = "select * FROM pay_zuren where typelist='2' and username='" . $vpeople . "' and yonghuzu_id='" . $yonghuzuid . "'";
                $order_query2 = $this->pdo->query($set_sql1);
                $order_info2 = $order_query2->fetchAll();
                if (!$order_info2) {
                    $set_sql_add = "insert into pay_zuren (yonghuzu_id,username,typelist) values ('" . $yonghuzuid . "','" . $vpeople . "','2')";
                    $order_info_add = $this->pdo->exec($set_sql_add);
                    if ($order_info_add) {
                        $parameter = array(
                            'chat_id' => $chatid,
                            'parse_mode' => 'HTML',
                            'text' => "添加用户ID：" . $vpeople . " 成功！"
                        );

                        $this->http_post_data('sendMessage', json_encode($parameter));
                    } else {
                        $parameter = array(
                            'chat_id' => $chatid,
                            'parse_mode' => 'HTML',
                            'text' => "添加用户ID：" . $vpeople . " 失败！"
                        );

                        $this->http_post_data('sendMessage', json_encode($parameter));
                    }


                } else {
                    $parameter = array(
                        'chat_id' => $chatid,
                        'parse_mode' => 'HTML',
                        'text' => "用户ID：" . $vpeople . " 已经在用户组下！请勿重复添加"
                    );

                    $this->http_post_data('sendMessage', json_encode($parameter));

                }

            }
            exit();
        }
        //导入用户组下的命令列表 daorumingling
        if (strpos($message, 'daorumingling') !== false) {
            $this->chaojiyonghuquanxian($userid, $chatid);


            $info = explode("###", $message);
            $info2 = explode("_#", $info[1]);
            $yonghuzuid = $info2[0];

            $info_two = explode("\n", $info2[1]);
            unset($info_two[0]);
            unset($info_two[1]);
            $now_arr = array();
            foreach ($info_two as $k => $v) {
                $now_arr[] = $v;
            }
            $set_sql2 = "select * FROM pay_yonghuzu where typelist='2' and id='" . $yonghuzuid . "'";
            $order_query3 = $this->pdo->query($set_sql2);
            $order_info2 = $order_query3->fetchAll();
            if (!empty($order_info2[0]['mingling'])) {
                $qllsq = explode(",", $order_info2[0]['mingling']);
            } else {
                $qllsq = array();
            }


            $gengxin_mingling = array();
            $hava_chuxian = array();
            foreach ($now_arr as $kpeople => $vpeople) {
                $gengxin_mingling[] = $vpeople;
                //检验命令是否存在：

                if (in_array($vpeople, $qllsq)) {
                    $hava_chuxian[] = $vpeople;


                }


            }
            if (count($hava_chuxian) > 0) {
                $parameter = array(
                    'chat_id' => $chatid,
                    'parse_mode' => 'HTML',
                    'text' => "命令：" . implode(",", $hava_chuxian) . " 已经在用户组下！请勿重复添加"
                );

                $this->http_post_data('sendMessage', json_encode($parameter));
                exit();
            }

            $new_mingling = array_merge($qllsq, $gengxin_mingling);
            $all_mingling_arr_str = implode(",", $new_mingling);


            $set_sql = "update pay_yonghuzu set mingling='" . $all_mingling_arr_str . "' where id='" . $yonghuzuid . "' and typelist='2'";
            $is_gengxin = $this->pdo->exec($set_sql);
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
        if (strpos($message, 'tianjia_yonghuzu_') !== false) {
            $this->chaojiyonghuquanxian($userid, $chatid);


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
                $set_sql1 = "select * FROM pay_yonghuzu where typelist = '2' and name='" . $vpeople . "'";
                $order_query2 = $this->pdo->query($set_sql1);
                $order_info2 = $order_query2->fetchAll();
                if (!$order_info2) {
                    $set_sql_add = "insert into pay_yonghuzu (name,typelist) values ('" . $vpeople . "','2')";
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
        //推送消息：
        if (strpos($message, 'guanpgbopqz_type_') !== false) {
            $guangboinfo_quer = $this->pdo->query("SELECT types FROM pay_guangbozhuang LIMIT 1");
            $guangboinfo = $guangboinfo_quer->fetchAll();
            if ($guangboinfo[0]['types'] == "1") {
                $this->xiaoxi("已经推送过，不在重复执行", $chatid);
            }

            $this->xiaoxinoend("执行开始=》" . json_encode($guangboinfo), $chatid);
            $typearr = explode("guanpgbopqz_type_", $message);

            $type2 = explode("_", $typearr[1]);
            $type = $type2[0];
            $content_arr = explode("推送内容:", $message);
            $content = trim($content_arr[1]);
            $all_user_pp = array();


            if ($type == "1") {
                //关注机器人：
                $set_sql1 = "select * FROM pay_jiqichat where typelist = '1'";
                $order_query2 = $this->pdo->query($set_sql1);
                $order_info2 = $order_query2->fetchAll();
                foreach ($order_info2 as $k => $v) {
                    // $this->xiaoxinoend($content, $v['chat_id']);
                    $all_user_pp[] = $v['chat_id'];
                }

            } elseif ($type == "2") {
                //关注机器人：
                $set_sql1 = "select * FROM pay_jiqichat where typelist = '2' group by chat_id";
                $order_query2 = $this->pdo->query($set_sql1);
                $order_info2 = $order_query2->fetchAll();

                foreach ($order_info2 as $k => $v) {
                    $all_user_pp[] = $v['chat_id'];

                    //$this->xiaoxinoend($content, $v['chat_id']);
                }

                //$this->xiaoxi(json_encode($all_user_pp), $chatid);
            } elseif ($type == "3") {
                //关注机器人：

                $today = date("Y-m-d", time());
                $sql_info = "select uid from pay_order where status = '1' and date='" . $today . "' group by uid";

                $order_query2 = $this->pdo->query($sql_info);
                $chatinfo = $order_query2->fetchAll();


                foreach ($chatinfo as $k => $v) {
                    $pid = $v['uid'];
                    $set_sql1 = "select * FROM pay_botsettle where merchant = '" . $pid . "'";
                    $order_query2 = $this->pdo->query($set_sql1);
                    $order_info2 = $order_query2->fetchAll();
                    $new_chat_id = $order_info2[0]['chatid'];
                    //$this->xiaoxinoend($content, $new_chat_id);
                    $all_user_pp[] = $new_chat_id;
                }

                //  $this->xiaoxi(json_encode($all_user_pp), $chatid);

            } elseif ($type == "4") {
                //关注机器人：

                if($this->kaiqi_teshu_xiafa){
                    $nayitian = $this->teshu_riqi;
                    $today = date("Y-m-d", strtotime(date($nayitian)));
                }else{
                    $today = date("Y-m-d", strtotime("-1 day"));
                }




                $sql_info = "select uid from pay_order where status = '1' and date='" . $today . "' group by uid";

                $order_query2 = $this->pdo->query($sql_info);
                $chatinfo = $order_query2->fetchAll();


                foreach ($chatinfo as $k => $v) {
                    $pid = $v['uid'];
                    $set_sql1 = "select * FROM pay_botsettle where merchant = '" . $pid . "'";
                    $order_query2 = $this->pdo->query($set_sql1);
                    $order_info2 = $order_query2->fetchAll();
                    $new_chat_id = $order_info2[0]['chatid'];
                    // $this->xiaoxinoend($content, $new_chat_id);
                    $all_user_pp[] = $new_chat_id;
                }

            } elseif ($type == "5") {
                //下发昨日收益文字：
                //$today = date("Y-m-d", strtotime("-1 day"));
                if($this->kaiqi_teshu_xiafa){
                    $nayitian = $this->teshu_riqi;
                    $today = date("Y-m-d", strtotime(date($nayitian)));
                }else{
                    $today = date("Y-m-d", strtotime("-1 day"));
                }
                $sql_info = "select uid from pay_order where status = '1' and date='" . $today . "' group by uid";

                $order_query2 = $this->pdo->query($sql_info);
                $chatinfo = $order_query2->fetchAll();


                foreach ($chatinfo as $k => $v) {

                    $pid = $v['uid'];
                    $set_sql1 = "select * FROM pay_botsettle where merchant = '" . $pid . "'";
                    $order_query2 = $this->pdo->query($set_sql1);
                    $order_info2 = $order_query2->fetchAll();
                    $new_chat_id = $order_info2[0]['chatid'];
                    // $this->xiaoxinoend($content, $new_chat_id);
                    $all_user_pp[] = $new_chat_id;
                }
            } elseif ($type == "6") {
                //下发昨日收益文字：

                //$today = date("Y-m-d", strtotime("-1 day"));
                if($this->kaiqi_teshu_xiafa){
                    $nayitian = $this->teshu_riqi;
                    $today = date("Y-m-d", strtotime(date($nayitian)));
                }else{
                    $today = date("Y-m-d", strtotime("-1 day"));
                }
                $sql_info = "select uid from pay_order where status = '1' and date='" . $today . "' group by uid";

                $order_query2 = $this->pdo->query($sql_info);
                $chatinfo = $order_query2->fetchAll();


                foreach ($chatinfo as $k => $v) {

                    $pid = $v['uid'];
                    $set_sql1 = "select * FROM pay_botsettle where merchant = '" . $pid . "'";
                    $order_query2 = $this->pdo->query($set_sql1);
                    $order_info2 = $order_query2->fetchAll();
                    $new_chat_id = $order_info2[0]['chatid'];
                    // $this->xiaoxinoend($content, $new_chat_id);
                    $all_user_pp[] = $new_chat_id;
                }
            }
            $all_user_pp = array_unique($all_user_pp);
            $this->gotoya($all_user_pp, $chatid, $content, $type);
        }


        //添加用户组：
        if (strpos($message, '权限用户组') !== false) {

            $this->chaojiyonghuquanxian($userid, $chatid);

            $set_sql1 = "select * FROM pay_yonghuzu where typelist='2'";

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
                $msg .= "<b><a href='https://t.me/" . $this->jiqirenming . "?start=yonghu_detail" . $value['id'] . "'>" . $value['name'] . "</a></b>  <b><a href='https://t.me/" . $this->jiqirenming . "?start=yonghushanchu_detail" . $value['id'] . "'>删除</a></b>\r\n";

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

        //商户的：/shrate221_30_1000

        if (strpos($message, '/shrate') !== false) {
            $quanxian = "shrate";
            $this->quanxian($chatid, $userid, $quanxian, $username);


            $info = explode("shrate", $message);
            $info1 = explode("_", $info[1]);

            $channel_id = $info1[0];
            $channel_time = intval($info1[1]);
            $pid = $info1[2];
            $sql_info = "select * from pay_channel where id ='" . $channel_id . "'";
            $order_query2 = $this->pdo->query($sql_info);
            $order_info2 = $order_query2->fetchAll();
            if (!$order_info2) {
                $parameter = array(
                    'chat_id' => $chatid,
                    'text' => "查询通道信息异常！请核对当前通道编号:" . $channel_id,
                    'show_alert' => true
                );
                $this->http_post_data('sendMessage', json_encode($parameter));
                exit();
            }
            /*
            🔺10-29成率：50%【1/2】
            🔺30-100成率：50%【1/2】
            🔺100-200成率：50%【1/2】
            🔺200-400成率：50%【1/2】
            🔺400-500成率：50%【1/2】
            🔺500-1000成率：50%【1/2】
            🔺1000-2000成率：50%【1/2】
            */
            // if (strpos($channel_time, '#') !== false) {


            //     $pp = "🎈" . $channel_time . "在跑通道成率如下:";
            //     $new_rate = explode("#", $channel_time);
            //     $one_time = trim($new_rate[0]);
            //     $two_time = trim($new_rate[1]);
            //     //06-25 20:22#06-25 21:22
            //     $now_time = date('Y') . "-" . $one_time . ":00";
            //     $end_time = date('Y') . "-" . $two_time . ":00";
            //     $find_sql = "SELECT type,channel,money,status from pay_order where channel ='".$channel_id."' and addtime between '" . $now_time . "' and '" . $end_time . "'";
            // } elseif (strpos($channel_time, '-') !== false) {


            //     $pp = "🎈" . date('Y-m-d') . " " . $channel_time. "在跑通道成率如下:";
            //     $new_rate = explode("-", $channel_time);
            //     $one_time = trim($new_rate[0]);
            //     $two_time = trim($new_rate[1]);
            //     //06-25 20:22#06-25 21:22
            //     $now_time = date('Y-m-d') . " " . $one_time . ":00:00";
            //     $end_time = date('Y-m-d') . " " . $two_time . ":00:00";
            //     $find_sql = "SELECT type,channel,money,status from pay_order where channel ='".$channel_id."' and addtime between '" . $now_time . "' and '" . $end_time . "'";
            // } else {

            $pp = "🆔商户" . $pid . "
🅿" . $order_info2[0]['name'] . "\r\n";
            $pp .= "💹" . $channel_time . "分钟在跑通道成率如下:";
            $now_time = date("Y-m-d H:i:s", time() - $channel_time * 60);
            $end_time = date("Y-m-d H:i:s", time());
            $find_sql = "SELECT type,channel,money,status from pay_order where uid= '" . $pid . "' and channel ='" . $channel_id . "' and addtime between '" . $now_time . "' and '" . $end_time . "'";
            // }
            $rs = $this->pdo->query($find_sql);
            $row = $rs->fetchAll();
            $money_new_arr = array(
                '1-29' => array(0, 0),
                '30-100' => array(0, 0),
                '101-200' => array(0, 0),
                '201-400' => array(0, 0),
                '401-500' => array(0, 0),
                '501-1000' => array(0, 0),
                '1001-2000' => array(0, 0),
                '2001-30000' => array(0, 0),
            );


            foreach ($row as $ks => $cvs) {

                if ($cvs['money'] < 30) {
                    //10-29成率
                    $money_new_arr['1-29'][0] += 1;

                    if ($cvs['status'] == "1") {
                        $money_new_arr['1-29'][1] += 1;
                    }
                } elseif ($cvs['money'] <= 100) {
                    //30-100成率
                    $money_new_arr['30-100'][0] += 1;

                    if ($cvs['status'] == "1") {
                        $money_new_arr['30-100'][1] += 1;
                    }
                } elseif ($cvs['money'] <= 200) {
                    //100-200成率
                    $money_new_arr['101-200'][0] += 1;

                    if ($cvs['status'] == "1") {
                        $money_new_arr['101-200'][1] += 1;
                    }
                } elseif ($cvs['money'] <= 400) {
                    //200-400成率
                    $money_new_arr['201-400'][0] += 1;

                    if ($cvs['status'] == "1") {
                        $money_new_arr['201-400'][1] += 1;
                    }
                } elseif ($cvs['money'] <= 500) {
                    //400-500成率
                    $money_new_arr['401-500'][0] += 1;

                    if ($cvs['status'] == "1") {
                        $money_new_arr['401-500'][1] += 1;
                    }
                } elseif ($cvs['money'] <= 1000) {
                    //500-1000成率
                    $money_new_arr['501-1000'][0] += 1;

                    if ($cvs['status'] == "1") {
                        $money_new_arr['501-1000'][1] += 1;
                    }
                } elseif ($cvs['money'] <= 2000) {
                    //1000-2000成率
                    $money_new_arr['1001-2000'][0] += 1;

                    if ($cvs['status'] == "1") {
                        $money_new_arr['1001-2000'][1] += 1;
                    }
                } else {

                    $money_new_arr['2001-30000'][0] += 1;

                    if ($cvs['status'] == "1") {
                        $money_new_arr['2001-30000'][1] += 1;
                    }
                }
            }
            $msg = "\r\n" . $pp . "\r\n\r\n";
            foreach ($money_new_arr as $key => $value) {
                $chenglv = round(($value[1] / $value[0]) * 100, 2);
                if ($chenglv >= 0) {
                    $msg .= "🔺" . $key . "成率：" . $chenglv . "%【" . $value[1] . "/" . $value[0] . "】\r\n";
                }


            }


            $parameter = array(
                'chat_id' => $chatid,
                'text' => $msg,
                'show_alert' => true
            );
            $this->http_post_data('sendMessage', json_encode($parameter));

            exit();

        }
        if (strpos($message, 'getid') !== false) {
            $quanxian = "getid";
            $this->quanxian($chatid, $userid, $quanxian, $username);
            if (count($data['message']['reply_to_message']) <= 0) {
                $this->xiaoxi("请引用你需要查询的用户唯一ID的信息", $chatid);
            } else {
                $dianbao_id = $data['message']['reply_to_message']['from']['id'];
                $tep = $data['message']['reply_to_message']['from']['first_name'] . "的电报id为:" . $dianbao_id;
                $this->xiaoxi($tep, $chatid);
            }
        }
        if (strpos($message, "统一费率") !== false) {
            $quanxian = "统一费率";
            $this->quanxian($chatid, $userid, $quanxian, $username);

            $pid = "99999";


            $typelist = "4";
            //查看是否有通道费率信息：
            $sql_info4 = "select * from pay_userfeilv where  pid ='" . $pid . "' and type='huilv' and typelist ='" . $typelist . "'";
            $order_info4 = $this->shujuku($sql_info4);

            $tongdao_str = "";
            if ($order_info4) {
                $tongdao_str .= "\r\nU币汇率" . "=" . $order_info4[0]['feilv'];

            } else {
                $tongdao_str = "U币汇率，格式:U币汇率[固定]=U币汇率值\r\nU币汇率=6.92";
            }

            if ($order_info4) {

                $msg = "<b>你当前U币汇率信息(优先):</b>\r\n" . $tongdao_str;
                $switch_inline_query_current_msg2 = "#usertonghuilv_tianjia_#\r\n" . $tongdao_str;
                $inline_keyboard_arr3[0] = array('text' => "修改U币汇率 ", "switch_inline_query_current_chat" => $switch_inline_query_current_msg2);
                $keyboard = [
                    'inline_keyboard' => [
                        $inline_keyboard_arr3,
                    ]
                ];
            } else {


                $msg = "<b>你尚未设置U币汇率，请设置</b>";
                $switch_inline_query_current_msg2 = "#usertonghuilv_tianjia_#\r\n" . $tongdao_str;
                $inline_keyboard_arr3[0] = array('text' => "修改U币汇率 ", "switch_inline_query_current_chat" => $switch_inline_query_current_msg2);
                $keyboard = [
                    'inline_keyboard' => [
                        $inline_keyboard_arr3,
                    ]
                ];


            }
            $parameter = array(
                'chat_id' => $chatid,
                'parse_mode' => 'HTML',
                'text' => $msg,
                'reply_markup' => $keyboard,
                'disable_web_page_preview' => true,
            );

            $this->http_post_data('sendMessage', json_encode($parameter));
            exit();
        }


        if (strpos($message, 'cdrate') !== false) {
            $quanxian = "cdrate";
            $this->quanxian($chatid, $userid, $quanxian, $username);

            $info = explode("cdrate", $message);
            $info1 = explode("_", $info[1]);

            $channel_id = $info1[0];
            $channel_time = intval($info1[1]);

            $sql_info = "select * from pay_channel where id ='" . $channel_id . "'";
            $order_query2 = $this->pdo->query($sql_info);
            $order_info2 = $order_query2->fetchAll();
            if (!$order_info2) {
                $parameter = array(
                    'chat_id' => $chatid,
                    'text' => "查询通道信息异常！请核对当前通道编号:" . $channel_id,
                    'show_alert' => true
                );
                $this->http_post_data('sendMessage', json_encode($parameter));
                exit();
            }
            /*
            🔺10-29成率：50%【1/2】
            🔺30-100成率：50%【1/2】
            🔺100-200成率：50%【1/2】
            🔺200-400成率：50%【1/2】
            🔺400-500成率：50%【1/2】
            🔺500-1000成率：50%【1/2】
            🔺1000-2000成率：50%【1/2】
            */
            if (strpos($channel_time, '#') !== false) {


                $pp = "🎈" . $channel_time . "在跑通道成率如下:";
                $new_rate = explode("#", $channel_time);
                $one_time = trim($new_rate[0]);
                $two_time = trim($new_rate[1]);
                //06-25 20:22#06-25 21:22
                $now_time = date('Y') . "-" . $one_time . ":00";
                $end_time = date('Y') . "-" . $two_time . ":00";
                $find_sql = "SELECT type,channel,money,status from pay_order where channel ='" . $channel_id . "' and addtime between '" . $now_time . "' and '" . $end_time . "'";
            } elseif (strpos($channel_time, '-') !== false) {


                $pp = "🎈" . date('Y-m-d') . " " . $channel_time . "在跑通道成率如下:";
                $new_rate = explode("-", $channel_time);
                $one_time = trim($new_rate[0]);
                $two_time = trim($new_rate[1]);
                //06-25 20:22#06-25 21:22
                $now_time = date('Y-m-d') . " " . $one_time . ":00:00";
                $end_time = date('Y-m-d') . " " . $two_time . ":00:00";
                $find_sql = "SELECT type,channel,money,status from pay_order where channel ='" . $channel_id . "' and addtime between '" . $now_time . "' and '" . $end_time . "'";
            } else {


                $pp = "🎈" . $channel_time . "分钟在跑通道成率如下:";
                $now_time = date("Y-m-d H:i:s", time() - $channel_time * 60);
                $end_time = date("Y-m-d H:i:s", time());
                $find_sql = "SELECT type,channel,money,status from pay_order where channel ='" . $channel_id . "' and addtime between '" . $now_time . "' and '" . $end_time . "'";
            }
            $rs = $this->pdo->query($find_sql);
            $row = $rs->fetchAll();
            $money_new_arr = array(
                '1-29' => array(0, 0),
                '30-100' => array(0, 0),
                '101-200' => array(0, 0),
                '201-400' => array(0, 0),
                '401-500' => array(0, 0),
                '501-1000' => array(0, 0),
                '1001-2000' => array(0, 0),
                '2001-30000' => array(0, 0),
            );

            // $this->xiaoxi(json_encode($row),$chatid);

            foreach ($row as $ks => $cvs) {

                if ($cvs['money'] < 30) {
                    //10-29成率
                    $money_new_arr['1-29'][0] += 1;

                    if ($cvs['status'] == "1") {
                        $money_new_arr['1-29'][1] += 1;
                    }
                } elseif ($cvs['money'] <= 100) {
                    //30-100成率
                    $money_new_arr['30-100'][0] += 1;

                    if ($cvs['status'] == "1") {
                        $money_new_arr['30-100'][1] += 1;
                    }
                } elseif ($cvs['money'] <= 200) {
                    //100-200成率
                    $money_new_arr['101-200'][0] += 1;

                    if ($cvs['status'] == "1") {
                        $money_new_arr['101-200'][1] += 1;
                    }
                } elseif ($cvs['money'] <= 400) {
                    //200-400成率
                    $money_new_arr['201-400'][0] += 1;

                    if ($cvs['status'] == "1") {
                        $money_new_arr['201-400'][1] += 1;
                    }
                } elseif ($cvs['money'] <= 500) {
                    //400-500成率
                    $money_new_arr['401-500'][0] += 1;

                    if ($cvs['status'] == "1") {
                        $money_new_arr['401-500'][1] += 1;
                    }
                } elseif ($cvs['money'] <= 1000) {
                    //500-1000成率
                    $money_new_arr['501-1000'][0] += 1;

                    if ($cvs['status'] == "1") {
                        $money_new_arr['501-1000'][1] += 1;
                    }
                } elseif ($cvs['money'] <= 2000) {
                    //1000-2000成率
                    $money_new_arr['1001-2000'][0] += 1;

                    if ($cvs['status'] == "1") {
                        $money_new_arr['1001-2000'][1] += 1;
                    }
                } else {
                    //1000-2000成率
                    $money_new_arr['2001-30000'][0] += 1;

                    if ($cvs['status'] == "1") {
                        $money_new_arr['2001-30000'][1] += 1;
                    }
                }
            }
            $msg = "🅿️" . $order_info2['0']['name'] .
                "\r\n" . $pp . "\r\n\r\n";
            foreach ($money_new_arr as $key => $value) {
                $chenglv = round(($value[1] / $value[0]) * 100, 2);
                if ($chenglv >= 0) {
                    $msg .= "🔺" . $key . "成率：" . $chenglv . "%【" . $value[1] . "/" . $value[0] . "】\r\n";
                }


            }


            $parameter = array(
                'chat_id' => $chatid,
                'text' => $msg,
                'show_alert' => true
            );
            $this->http_post_data('sendMessage', json_encode($parameter));

            exit();

        }
        if (strpos($message, '拉取订单') !== false) {
            $quanxian = "拉取订单";
            $this->quanxian($chatid, $userid, $quanxian, $username);
            $sql_info = "select * from pay_botsettle where chatid ='" . $chatid . "'";
            $order_query2 = $this->pdo->query($sql_info);
            $order_info2 = $order_query2->fetchAll();
            $pid = $order_info2['0']['merchant'];

            /*$uid_arr = explode("|", $pid);
            if (count($uid_arr) > 1) {

                    foreach ($uid_arr as $k => $v) {
                        $inline_keyboard_arr[$k] = array('text' => "拉取商户:" . $v, "callback_data" => "订单拉取商户_" . $v);
                    }

                    $keyboard = [
                        'inline_keyboard' => [
                            $inline_keyboard_arr
                        ]
                    ];
                    $parameter = array(
                        'chat_id' => $chatid,
                        'parse_mode' => 'HTML',
                        'text' => "请选择要拉取订单的商户",
                        'reply_markup' => $keyboard,

                    );

                    $this->http_post_data('sendMessage', json_encode($parameter));
                    exit();

                }*/


            $sql_info2 = "select * from pay_user where uid ='" . $pid . "'";
            $order_query3 = $this->pdo->query($sql_info2);
            $order_info_gr = $order_query3->fetchAll();
            $gid = $order_info_gr['0']['gid'];

            $sql_info3 = "select * from pay_group where gid ='" . $gid . "'";
            $order_query4 = $this->pdo->query($sql_info3);
            $order_info_gp = $order_query4->fetchAll();

            $type_arr_json = $order_info_gp[0]['info'];
            $type_arr = json_decode($type_arr_json, true);
            $info = "<b>请选择支付方式:</b>\r\n\r\n";
            $ps = 1;

            $pss = 0;
            $inline_keyboard_arr3 = array();
            foreach ($type_arr as $k => $v) {

                $sql_info4 = "select * from pay_type where id ='" . $k . "'";
                $order_query5 = $this->pdo->query($sql_info4);
                $order_info_gp2 = $order_query5->fetchAll();
                //$ids = $v['channel']
                if ($v['channel'] > 0) {
                    $showname = $order_info_gp2[0]['showname'];

                    if ($v['type'] == "channel") {
                        //方式

                        //$info .=$ps.":".$showname."\r\n";

                        $inline_keyboard_arr3[$pss] = array('text' => $showname, "callback_data" => "zhifu_channel_" . $k . "###" . $pid);
                    } else {
                        //轮询
                        //$info .=$ps.":".$showname."\r\n";

                        $inline_keyboard_arr3[$pss] = array('text' => $showname, "callback_data" => "zhifu_roll_" . $k . "###" . $pid);
                    }
                    $ps += 1;
                    $pss += 1;
                }


            }


            $keyboard = [
                'inline_keyboard' => [
                    $inline_keyboard_arr3,
                ]
            ];
            $parameter = array(
                'chat_id' => $chatid,
                'parse_mode' => 'HTML',
                'text' => $info,
                'reply_markup' => $keyboard,
                'disable_web_page_preview' => true,

            );

            $this->http_post_data('sendMessage', json_encode($parameter));
            exit();

        }
         if (strpos($message, '最近跑量') !== false) {

            $sql_info = "select * from pay_botsettle where chatid ='" . $chatid . "'";
            $order_query2 = $this->pdo->query($sql_info);
            $order_info2 = $order_query2->fetchAll();
            $pid = $order_info2['0']['merchant'];

            if (!$pid) {
                //已經綁定群了：
                $parameter = array(
                    'chat_id' => $chatid,
                    'parse_mode' => 'HTML',
                    'text' => "该群暂未绑定商户号，请输入快捷命令：/bd"

                );
                $this->http_post_data('sendMessage', json_encode($parameter));
                exit();

            }

            $now_time = date("Y-m-d H:i:s");
            $ten_days_ago = date("Y-m-d H:i:s", strtotime("-10 days"));

            // 查询近10日的已支付订单（支付宝/微信）
            $sql_info2 = "SELECT 
                             DATE(addtime) AS d, 
                             type as paytype, 
                             SUM(money) AS total 
                          FROM pay_order 
                          WHERE uid = '" . $pid . "' 
                            AND addtime BETWEEN '" . $ten_days_ago . "' AND '" . $now_time . "' 
                            AND status = 1
                            AND type IN (1, 2)
                          GROUP BY d, paytype 
                          ORDER BY d DESC";
            $order_query3 = $this->pdo->query($sql_info2);
            $order_info_gr = $order_query3->fetchAll(PDO::FETCH_ASSOC);

            // 初始化
            $daily_data = [];
            $paytypes = [1 => 'alipay', 2 => 'wechat'];

            for ($i = 0; $i < 10; $i++) {
                $date = date("Y-m-d", strtotime("-$i days"));
                $daily_data[$date] = [
                    'total' => 0,
                    'alipay' => 0,
                    'wechat' => 0,
                ];
            }

            // 聚合数据
            foreach ($order_info_gr as $row) {
                $date = $row['d'];
                $type = intval($row['paytype']);
                $amount = floatval($row['total']);

                if (isset($daily_data[$date])) {
                    $key = $paytypes[$type];
                    $daily_data[$date]['total'] += $amount;
                    $daily_data[$date][$key] += $amount;
                }
            }

            // 检查是否总量都为 0（即无跑量）
            $has_data = false;
            foreach ($daily_data as $d) {
                if ($d['total'] > 0) {
                    $has_data = true;
                    break;
                }
            }

            // 输出
            if (!$has_data) {
                $info = "<b>近十日无任何跑量记录。</b>";
            } else {
                $info = "<b>近十日跑量明细:</b>\r\n\r\n";
                foreach ($daily_data as $date => $amounts) {
                    $cn_date = date("m月d日", strtotime($date));
                    $info .= "📅 <b>{$cn_date}</b>\n";
                    $info .= "　总量：<b>" . $amounts['total'] . "</b>\n";
                    $info .= "　支付宝：<b>" . $amounts['alipay'] . "</b>\n";
                    $info .= "　微信：<b>" . $amounts['wechat'] . "</b>\n\n";
                }
            }

            // 推送 Telegram
            $parameter = array(
                'chat_id' => $chatid,
                'parse_mode' => 'HTML',
                'text' => $info,
            );

            $this->http_post_data('sendMessage', json_encode($parameter));
            exit();



        }

        if (strpos($message, 'dandushanghu_gengxagai_') !== false) {
            $sqa = explode("dandushanghu_gengxagai_#", $message);

            $info_two = explode("\n", $sqa[1]);
            $trade_no = $info_two[0];

            $uid_arr = explode("=", $info_two[1]);
            $uid = $uid_arr[1];

            $trade_no_arr = explode("=", $info_two[2]);
            $trade_no2 = $trade_no_arr[1];

            $name_arr = explode("=", $info_two[3]);
            $name = $name_arr[1];

            $notify_url_arr = explode("=", $info_two[4]);
            $notify_url = $notify_url_arr[1];

            $return_url_arr = explode("=", $info_two[5]);
            $return_url = $return_url_arr[1];

            $domain_arr = explode("=", $info_two[6]);
            $domain = $domain_arr[1];

            $out_trade_no_arr = explode("=", $info_two[7]);
            $out_trade_no = $out_trade_no_arr[1];

            $changeuid_arr = explode("=", $info_two[8]);
            $changeuid = $changeuid_arr[1];

            $change_status = $this->pdo->exec("update `pay_order` set `uid` ='$changeuid',`notify_url` ='$notify_url',`return_url` ='$return_url',`domain`='$domain',`name`='VIP' where `trade_no`='$trade_no'");

            $order_info = $this->shujuku("select * from pay_order where trade_no='$trade_no'");
            $money = $order_info[0]['money'];
            $cyang1 = $this->pdo->exec("update `pay_user` set money=money+'{$money}' where `uid`='$changeuid'");
            $cyang2 = $this->pdo->exec("update `pay_user` set money=money-'{$money}' where `uid`='$uid'");
            $this->xiaoxi($change_status . "=>" . $cyang1 . "=>" . $cyang2, $chatid);
            //$this->xiaoxi("update `pay_user` set money=money+'{$money}' where `uid`='$changeuid'",$chatid);
        }


        if (strpos($message, '呼叫24h客服') !== false) {

            $kefu_sql = "select * FROM pay_userchat where chat_id ='" . $chatid . "'  and channel='" . $this->token . "' and status='0'";
            $kefu_query = $this->pdo->query($kefu_sql);
            $kefu_info = $kefu_query->fetchAll();
            if ($kefu_info) {
                $inline_keyboard_arr9[0] = array('text' => "关闭当前会话 ", "callback_data" => "关闭当前客服会话");
                $keyboard = [
                    'inline_keyboard' => [
                        $inline_keyboard_arr9,

                    ]
                ];

                $parameter = array(
                    'chat_id' => $chatid,
                    'parse_mode' => 'HTML',
                    'text' => "你已经有一个正在通讯的会话！",
                    'reply_markup' => $keyboard,
                    'disable_web_page_preview' => true,
                );

                $this->http_post_data('sendMessage', json_encode($parameter));
                exit();

            }

            //先去选择对应的客服部门：
            $bottoken = $this->token;
            $info = Http::sendPostRequest($this->rocket_url . "/api/Index/index", ['bottoken' => $bottoken]);
            $info_arr = json_decode($info, true);
            if ($info_arr['code'] == "0") {
                $this->xiaoxi($info_arr['msg'], $chatid);
            }

            if (count($info_arr['data']) > 1) {
                $inline_keyboard_arr = array();
                for ($i = 0; $i < count($info_arr['data']); $i++) {
                    $inline_keyboard_arr[] = array("text" => $info_arr['data'][$i], "callback_data" => "xunzhaokefu_" . $info_arr['data'][$i]);
                }

                $msg = "请选择你要的客服类型:";
                $keyboard = [
                    'inline_keyboard' => [
                        $inline_keyboard_arr,

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
            $depart_info = $info_arr['data'][0];


            //$quanxian = "呼叫24h客服";
            //$this->quanxian($chatid, $userid, $quanxian, $username);
            // $parameter = array(
            //     'chat_id' => $chatid,
            //     'text' => "正在唤起客服系统~请稍后~",
            //     'show_alert' => true
            // );
            $sql_info = "select * from pay_botsettle where chatid ='" . $chatid . "'";
            $order_query2 = $this->pdo->query($sql_info);
            $chatinfo = $order_query2->fetchAll();
            $uid = $chatinfo['0']['merchant'];

            $visitorToken = $this->generateVisitorToken();
            // Rocket.Chat 服务器地址
            $serverUrl = $this->chat_url;

            // 创建访客
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $serverUrl . '/api/v1/livechat/visitor');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
                'visitor' => [
                    'token' => $visitorToken,
                    'name' => '商户号：' . $uid . "随机数:" . rand(100, 999),
                    'email' => 'visitor@example.com',
                    "department" => $depart_info,  //天使技术部A，上游客服部B
                ]
            ]));

            $response = curl_exec($ch);
            if (!$response) {
                $this->xiaoxi("客服人工座席忙,请稍后再请求123！", $chatid);
            }

            $visitorData = json_decode($response, true);

            //第二步：通过token拿到数据先：
            //https://ccc.zmchat.xyz/api/v1/livechat/room?token=bc2a23307a699c2909a54c5948c2b05c036c92dc200568646cb5b10cabb4a0d5
            $url2 = $serverUrl . "api/v1/livechat/room?token=" . $visitorToken;
            $headers2 = [];
            $response2 = $this->httpGet($url2, $headers2);
            if (!$response2) {
                $this->xiaoxi("客服人工座席忙,请稍后再请求456！", $chatid);
            }
            $room_id = $response2['room']['_id'];


            //再去请求获取用户信息：
            //https://ccc.zmchat.xyz/api/v1/livechat/agent.info/MPSvGLEJgvGzNgg7x/5a65a366b07dcabef66ce3b624b83dcd7c01cc4599881fffdff79eff8fc6f6a2
            // 使用示例
            $url3 = $serverUrl . 'api/v1/livechat/agent.info/' . $room_id . "/" . $visitorToken;
            $headers3 = [
                //'Authorization: Bearer your_token_here',
                //'Content-Type: application/json'
            ];

            $response3 = $this->httpGet($url3, $headers3);
            if (!$response3) {
                $this->xiaoxi("客服人工座席忙,请稍后再请求789！", $chatid);
            }
            $agent_id = $response3['agent']['_id'];
            $kefu_username = $response3['agent']['username'];

            //这里来一个记录，表示当前商户正在对话，进行中：
            $status = "0";
            $channel = $this->token;
            $createtime = time();
            $set_sql = "insert into pay_userchat (channel,status,visitorToken,room_id,agent_id,user_id,createtime,chat_id,kefu_name) values ('" . $channel . "','" . $status . "','" . $visitorToken . "', '" . $room_id . "','" . $agent_id . "','" . $uid . "','" . $createtime . "','" . $chatid . "','" . $kefu_username . "')";
            $this->pdo->exec($set_sql);
            $this->xiaoxi("客服:" . $kefu_username . ",为你开启服务，请简要说出你的需求", $chatid);
        }
        if (strpos($message, 'xiafa_genggai_#') !== false) {
            $this->chaojiyonghuquanxian($userid, $chatid);
            $arra = explode("商户ID:", $message);
            $arrb = explode("USDT:", $arra[1]);

            $pid = trim($arrb[0]);

            //查看商户是否存在：
            $pid_info = $this->shujuku("select * from pay_user where uid='" . $pid . "'");
            if (!$pid_info) {
                $this->xiaoxi("商户ID异常,请核对", $chatid);
            }

            $dq_pid = $userbotsettle_info2[0]['merchant'];


            if ($dq_pid != $pid) {
                $this->xiaoxi("当前修改的商户ID信息不存在当前绑定的群中！请核对!", $chatid);
            }


            $usdt = trim($arrb[1]);
            $messages = "商户:" . $pid . "申请更改下发USDT地址：\r\n\r\n商户ID:" . $pid . "\r\nUSDT:" . $usdt;
            $switch_inline_query_current_msg = "#xiafa_genggai_#\r\n商户ID:" . $pid . "\r\nUSDT:xxxxxxxxxxxxxxxxxxxx";
            $inline_keyboard_arr3[0] = array('text' => "确定更改", "callback_data" => "quedingusdt_" . $usdt . "###" . $pid);
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

        if (strpos($message, '更换usdt地址') !== false) {
            //关闭当前的会话：

            $this->chaojiyonghuquanxian($userid, $chatid);


            if (!$userbotsettle_info2) {
                //已經綁定群了：
                $parameter = array(
                    'chat_id' => $chatid,
                    'parse_mode' => 'HTML',
                    'text' => "当前群尚未绑定商户号"

                );
                $this->http_post_data('sendMessage', json_encode($parameter));
                exit();
            }
            $merchant = $userbotsettle_info2[0]['merchant'];
            if (strpos($merchant, '|') !== false) {
                $chs_m = "xxxx";
            } else {
                $chs_m = $merchant;
            }

            $userinfo = $this->shujuku("select * from pay_user where uid='" . $chs_m . "'");
            $usdt_m_arr = $userinfo[0]['usdt_str'];

            $messages = "申请更改下发USDT地址,格式如下：\r\n商户ID:" . $chs_m . "\r\nUSDT:" . $usdt_m_arr;
            $switch_inline_query_current_msg = "#xiafa_genggai_#\r\n商户ID:" . $chs_m . "\r\nUSDT:" . $usdt_m_arr;
            $inline_keyboard_arr3[0] = array('text' => "申请更改", "switch_inline_query_current_chat" => $switch_inline_query_current_msg);
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

        if (strpos($message, 'tongzhidel') !== false) {
            $quanxian = "tongzhidel";
            $this->quanxian($chatid, $userid, $quanxian, $username);

            $changesq = "";
            $res = $this->pdo->exec("UPDATE pay_botsettle SET atyonghu='" . $changesq . "' WHERE chatid='" . $chatid . "'");


            //获取录入信息：
            $parameter = array(
                'chat_id' => $chatid,
                'parse_mode' => 'HTML',
                'text' => '删除回U通知at人成功！'
            );
            $this->http_post_data('sendMessage', json_encode($parameter));
            exit();
        }

        if (strpos($message, 'tousu_kouchu_#') !== false) {
            $quanxian = "投诉扣除";
            $this->quanxian($chatid, $userid, $quanxian, $username);

            /*
            @tianshidierg_bot #tousu_kouchu_#
            商户ID:1205
            扣除投诉金额:50

            */
            $info = explode("tousu_kouchu_#", $message);
            $info_two = explode("\n", $info[1]);
            unset($info_two[0]);


            $pid_arr = explode(":", trim($info_two[1]));
            $pid = $pid_arr[1];

            $money_arr = explode(":", trim($info_two[2]));
            $money = $money_arr[1];


            $sql_info = "select * from pay_botsettle where chatid ='" . $chatid . "'";
            $order_query2 = $this->pdo->query($sql_info);
            $order_info2 = $order_query2->fetchAll();
            $find_pid = $order_info2[0]['merchant'];
            $today = date("Y-m-d");

            if (!is_numeric($money)) {
                $parameter = array(
                    'chat_id' => $chatid,
                    'text' => "你输入的金额：" . $money . "！此格式错误，请直接输入数字，例如：50",
                    'show_alert' => true
                );
                $this->http_post_data('sendMessage', json_encode($parameter));

                exit();
            }

            if (strpos($find_pid, "|")) {
                $all_pid = explode("|", $find_pid);
                if (!in_array($pid, $all_pid)) {
                    $parameter = array(
                        'chat_id' => $chatid,
                        'text' => "你输入的商户号：" . $pid . "！不存在此商户群中的商户：" . $find_pid . "中",
                        'show_alert' => true
                    );
                    $this->http_post_data('sendMessage', json_encode($parameter));

                    exit();
                }
            } else {
                if ($find_pid != $pid) {
                    $parameter = array(
                        'chat_id' => $chatid,
                        'text' => "你输入的商户号：" . $pid . "！不存在此商户群中的商户：" . $find_pid . "中",
                        'show_alert' => true
                    );
                    $this->http_post_data('sendMessage', json_encode($parameter));

                    exit();
                }
            }


            $set_sql = "insert into pay_usertousu (pid,money,date,chatid) values ('" . $pid . "','" . $money . "', '" . $today . "','" . $chatid . "')";

            $this->pdo->exec($set_sql);

            $sql_info = "select sum(money) as tousumoney from pay_usertousu where pid ='" . $pid . "'";

            $order_query3 = $this->pdo->query($sql_info);
            $chatinfo = $order_query3->fetchAll();
            $order_today = round($chatinfo[0]['tousumoney'], 2);

            $parameter = array(
                'chat_id' => $chatid,
                'text' => "商户ID:" . $pid . "\r\n计入投诉金额：" . $money . "元成功！\r\n将会从昨日结算中扣除！\r\n\r\n当前合计总投诉金额：" . $order_today . "元",
                'show_alert' => true
            );
            $this->http_post_data('sendMessage', json_encode($parameter));

            exit();
        }

        if (strpos($message, '商户管理设置') !== false) {
            //关闭当前的会话：
            $quanxian = "商户管理设置";
            $this->quanxian($chatid, $userid, $quanxian, $username);

            $sql_info = "select * from pay_botsettle where chatid ='" . $chatid . "'";
            $order_query2 = $this->pdo->query($sql_info);
            $order_info2 = $order_query2->fetchAll();
            if (!$order_info2) {
                $parameter = array(
                    'chat_id' => $chatid,
                    'text' => "当前群尚未绑定商户号！",
                    'show_alert' => true
                );
                $this->http_post_data('sendMessage', json_encode($parameter));

                exit();
            }

            $now_pids = $order_info2[0]['merchant'];
            if (strpos($now_pids, '|') !== false) {
                $now_pids_arr = explode("|", $now_pids);
                $now_pid = $now_pids_arr[0];
            } else {
                $now_pid = $now_pids;
            }

            $messages = "<b>请选择你要设置的选项</b>";

            $inline_keyboard_arr3[0] = array('text' => "支付费率设置 ", "callback_data" => "支付费率设置");
            $inline_keyboard_arr3[1] = array('text' => "通道费率设置 ", "callback_data" => "通道费率设置");
            $inline_keyboard_arr5[0] = array('text' => "U币汇率浮点设置 ", "callback_data" => "U币汇率浮点设置");
            $inline_keyboard_arr5[1] = array('text' => "分成比例 ", "callback_data" => "分成比例");

            $inline_keyboard_arr4[0] = array('text' => "下发设置 ", "callback_data" => "下发设置");
            $inline_keyboard_arr4[1] = array('text' => "回u通知设置 ", "callback_data" => "回u通知设置");
            $inline_keyboard_arr4[2] = array('text' => "订单推送设置 ", "callback_data" => "订单推送设置");
            $keyboard = [
                'inline_keyboard' => [
                    $inline_keyboard_arr3,
                    $inline_keyboard_arr5,
                    $inline_keyboard_arr4
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

        if (strpos($message, '投诉扣除') !== false) {
            //关闭当前的会话：
            $quanxian = "投诉扣除";
            $this->quanxian($chatid, $userid, $quanxian, $username);

            $sql_info = "select * from pay_botsettle where chatid ='" . $chatid . "'";
            $order_query2 = $this->pdo->query($sql_info);
            $order_info2 = $order_query2->fetchAll();
            if (!$order_info2) {
                $parameter = array(
                    'chat_id' => $chatid,
                    'text' => "当前群尚未绑定商户号！",
                    'show_alert' => true
                );
                $this->http_post_data('sendMessage', json_encode($parameter));

                exit();
            }

            $now_pids = $order_info2[0]['merchant'];
            if (strpos($now_pids, '|') !== false) {
                $now_pids_arr = explode("|", $now_pids);
                $now_pid = $now_pids_arr[0];
            } else {
                $now_pid = $now_pids;
            }

            $messages = "用户投诉扣除金额格式如下：\r\n商户ID:商户ID\r\n扣除投诉金额:数字金额\r\n";
            $switch_inline_query_current_msg = "#tousu_kouchu_#\r\n商户ID:" . $now_pid . "\r\n扣除投诉金额:50";
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

        if (strpos($message, 'hl8976UUU') !== false) {
            $this->chaojiyonghuquanxian($userid, $chatid);

            $usets = explode("UUU", $message);
            $set_sql = "update pay_uset set three='" . $usets['1'] . "'";
            $this->pdo->exec($set_sql);
            //获取录入信息：
            $parameter = array(
                'chat_id' => $chatid,
                'parse_mode' => 'HTML',
                'text' => '修改所有商户号汇率成功'
            );
            $this->http_post_data('sendMessage', json_encode($parameter));
            exit();
        }
        //进入后台：
        if (strpos($message, 'htglxx8976') !== false) {
            $this->chaojiyonghuquanxian($userid, $chatid);
            //关闭当前的会话：
            //$this->start($chatid);
        } elseif ($message == "htgl" || $message == "/htgl") {
            $this->chaojiyonghuquanxian($userid, $chatid);

            $this->start_hou($chatid);
        } elseif (strpos($message, '/tuisong') !== false) {
            $this->chaojiyonghuquanxian($userid, $chatid);

            $uid_info = explode("tuisong", $message);
            $from_id = $data['message']['from']['id'];
            $this->tuisong($chatid, $uid_info[1], $from_id);
        } elseif (strpos($message, '解除推送') !== false) {
            $this->chaojiyonghuquanxian($userid, $chatid);

            $uid_info = explode("tuisong", $message);
            $from_id = $data['message']['from']['id'];
            $this->tuisongs($chatid, $uid_info[1], $from_id);
        } else {

            //开始：
            if (strpos($message, '/start') !== false) {


                //删除用户组下的某个用户：
                if (strpos($message, 'zdyhshanchu_detail') !== false) {

                    $this->chaojiyonghuquanxian($userid, $chatid);

                    $instruction_arr = explode("zdyhshanchu_detail", $message);
                    $zuren_id = $instruction_arr[1];

                    $set_sql1 = "select * FROM pay_zuren where typelist= '2' and id='" . $zuren_id . "'";
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
                    $set_sql = "DELETE FROM pay_zuren where typelist= '2' and id='" . $zuren_id . "'";
                    $is_shanchu = $this->pdo->exec($set_sql);
                    if ($is_shanchu) {
                        $msg = "删除" . $order_info2[0]['username'] . "成功!";
                    } else {
                        $msg = "删除" . $order_info2[0]['username'] . "失败!";
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
                    $this->chaojiyonghuquanxian($userid, $chatid);

                    $info = explode("yonghushanchu_detail", $message);
                    $info_two = $info[1];
                    $set_sql1 = "select * FROM pay_yonghuzu where typelist= '2' and  id='" . $info_two . "'";

                    $order_query2 = $this->pdo->query($set_sql1);
                    $order_info2 = $order_query2->fetchAll();
                    if (!$order_info2) {
                        $parameter = array(
                            'chat_id' => $chatid,
                            'parse_mode' => 'HTML',
                            'text' => "未查询到你要删除的用户组信息！请核对！"
                        );
                        $this->http_post_data('sendMessage', json_encode($parameter));
                        exit();
                    } else {
                        $set_sql = "DELETE FROM pay_yonghuzu where typelist= '2' and  id='" . $info_two . "'";
                        $is_shanchu = $this->pdo->exec($set_sql);
                        if ($is_shanchu) {
                            //删除这个用户组下面的所有人信息：
                            $set_sql2 = "DELETE FROM pay_zuren where typelist= '2' and  yonghuzu_id='" . $info_two . "'";
                            $is_shanchu2 = $this->pdo->exec($set_sql2);
                            $parameter = array(
                                'chat_id' => $chatid,
                                'parse_mode' => 'HTML',
                                'text' => "删除用户组:" . $order_info2[0]['name'] . "成功！"
                            );
                            $this->http_post_data('sendMessage', json_encode($parameter));
                            exit();
                        } else {
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

                    $this->chaojiyonghuquanxian($userid, $chatid);

                    $info = explode("yonghu_detail", $message);
                    $info_two = $info[1];
                    $set_sql1 = "select * FROM pay_zuren where typelist= '2' and yonghuzu_id='" . $info_two . "'";

                    $order_query2 = $this->pdo->query($set_sql1);
                    $order_info2 = $order_query2->fetchAll();
                    $msg = "<b>用户如下：</b>\r\n";
                    if ($order_info2) {
                        foreach ($order_info2 as $kq => $ve) {
                            $msg .= "<b>" . $ve['username'] . "</b><b><a href='https://t.me/" . $this->jiqirenming . "?start=zdyhshanchu_detail" . $ve['id'] . "'>删除</a></b>\r\n";
                        }
                    } else {
                        $msg .= "当前用户组下未添加用户\r\n";
                    }


                    $msg .= "\r\n<b>命令如下：</b>\r\n";
                    $set_sql2 = "select * FROM pay_yonghuzu where typelist= '2' and  id='" . $info_two . "'";


                    $order_query3 = $this->pdo->query($set_sql2);
                    $order_info3 = $order_query3->fetchAll();


                    if ($order_info3) {

                        $mingling_arr = explode(",", $order_info3[0]['mingling']);

                        if (!empty($order_info3[0]['mingling'])) {
                            //$msg .= count($mingling_arr)."---当前用户组暂未设置命令";
                            foreach ($mingling_arr as $kq2 => $ve2) {
                                $msg .= "<b>" . $ve2 . "</b>   <b><a href='https://t.me/" . $this->jiqirenming . "?start=minglingshanchu_" . $info_two . "__" . $ve2 . "'>删除</a></b>\r\n";
                            }
                        } else {
                            $msg .= "当前用户组暂未设置命令";
                        }

                    } else {
                        $msg .= "当前用户组暂未设置命令";
                    }


                    $switch_inline_query_current_msg1 = "#daoruyonghu###" . $info_two . "_#\r\n用户列表\r\n用户唯一ID1\r\n用户唯一ID2\r\n用户唯一ID2";
                    $inline_keyboard_arr3[0] = array('text' => "导入用户 ", "switch_inline_query_current_chat" => $switch_inline_query_current_msg1);


                    $inline_keyboard_arr3[1] = array('text' => "清空用户", "callback_data" => "deleteallyonghu###" . $info_two);

                    $all_ming_list = $this->all_ming_list;
                    $all_msq_str = "";
                    foreach ($all_ming_list as $sq => $sqe) {
                        $all_msq_str .= "\r\n" . $sqe;
                    }
                    $switch_inline_query_current_msg3 = "#daorumingling###" . $info_two . "_#\r\n命令列表" . $all_msq_str;

                    //$switch_inline_query_current_msg3 = "#daorumingling###".$info_two."_#\r\n命令列表\r\nadd_user\r\ntongdao_detail\r\n添加误差\r\n修改误差";
                    $inline_keyboard_arr3[2] = array('text' => "导入命令 ", "switch_inline_query_current_chat" => $switch_inline_query_current_msg3);


                    $inline_keyboard_arr3[3] = array('text' => "清空命令", "callback_data" => "deleteallmingling###" . $info_two);

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

                }

                if (strpos($message, 'gaibiandinaqagdanya') !== false) {


                    $info = explode("gaibiandinaqagdanya", $message);
                    $trade_no = $info[1];

                    $set_sql1 = "select * FROM pay_order where  trade_no='" . $trade_no . "'";

                    $order_query2 = $this->pdo->query($set_sql1);
                    $order_info2 = $order_query2->fetchAll();

                    $info = $order_info2[0];

                    $messages = "调整信息";
                    $switch_inline_query_current_msg = "#dandushanghu_gengxagai_#" . $info['trade_no'] . "\r\nUID=" . $info['uid'] . "\r\ntrade_no=" . $info['trade_no'] . "\r\nname=" . $info['name'] . "\r\nnotify_url=" . $info['notify_url'] . "\r\nreturn_url=" . $info['return_url'] . "\r\ndomain=" . $info['domain'] . "\r\nout_trade_no=" . $info['out_trade_no'] . "\r\nchangeuod=1133";
                    $inline_keyboard_arr3[0] = array('text' => "立即更改", "switch_inline_query_current_chat" => $switch_inline_query_current_msg);
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

                if (strpos($message, 'order_detail') !== false || strpos($message, 'shang_detail') !== false) {
                    //根据群信息去查询商户的信息：
                    $from_id = $data['message']['from']['id'];
                    $this->findgroup($chatid, $message, $from_id, $data);
                }


                $keyboard2 = [
                    'keyboard' => [

                        [
                            ['text' => '今日收益'],
                            ['text' => '昨日收益'],
                            ['text' => '订单查询']
                        ],

                        [
                            ['text' => '实时下发'],
                            ['text' => '商户管理设置'],
                            ['text' => '下发昨日收益'],
                            ['text' => '投诉扣除'],

                        ],
                        [
                            ["text" => '拉取订单'],
                            ["text" => '最近跑量'],
                            ['text' => '呼叫24h客服'],
                            ['text' => '更换usdt地址']

                        ]


                    ],
                    //可选。请求客户端垂直调整键盘大小以获得最佳适配（例如，如果只有两行按钮，则使键盘更小）。默认为false，在这种情况下，自定义键盘始终与应用程序的标准键盘高度相同。
                    'resize_keyboard' => true,
                    //可选。要求客户在使用后立即隐藏键盘。键盘仍然可用，但客户端会在聊天中自动显示常用的字母键盘——用户可以在输入字段中按下一个特殊的按钮来再次看到自定义键盘。默认为false。
                    'one_time_keyboard' => false,
                    //string 可选。键盘处于活动状态时要在输入字段中显示的占位符；1-64 个字符
                    //'input_field_placeholder'=>'',
                    //可选。如果您只想向特定用户显示键盘，请使用此参数。目标：1），其在用户@mentioned文本的的消息对象; 2）如果机器人的消息是回复（有reply_to_message_id），原始消息的发件人。

                    //'selective'=>''
                ];
                $encodedKeyboard2 = json_encode($keyboard2);

                /* $botId = $this->response->getId();
                 $firstName = $this->response->getFirstName();
                 $lastName = $this->response->getLastName();
                 $userName = $this->response->getUsername();*/
                $parameter = array(
                    'chat_id' => $chatid,
                    'text' => "你好:" . "欢迎使用本系统！",
                    'reply_markup' => $encodedKeyboard2
                );
                //发送消息

                $this->http_post_data('sendMessage', json_encode($parameter));
                exit();
            } else {
                if ($data['message']['new_chat_member']) {
                    $parameter = array(
                        'chat_id' => $chatid,
                        'text' => "欢迎：" . $data['message']['new_chat_member']['first_name'] . $data['message']['new_chat_member']['last_name'] . "入群！",
                    );
                    $this->http_post_data('sendMessage', json_encode($parameter));

                } else {

                    //关闭录入群消息：
                    $from_id = $data['message']['from']['id'];
                    if ($message == "0000") {
                        // $this->chaojiyonghuquanxian($userid, $chatid);
                        $set_sql1 = "select * FROM pay_chatgroupset where  from_id='" . $from_id . "'";

                        $order_query2 = $this->pdo->query($set_sql1);
                        $order_info2 = $order_query2->fetchAll();
                        if ($order_info2) {
                            $set_sql = "DELETE FROM pay_chatgroupset where from_id='" . $from_id . "'";
                            $this->pdo->exec($set_sql);
                            $parameter = array(
                                'chat_id' => $chatid,
                                'parse_mode' => 'HTML',
                                'text' => "关闭商户号汇率设置成功！"
                            );
                            $this->http_post_data('sendMessage', json_encode($parameter));
                            exit();
                        }

                    }

                    $sql = "select * from pay_chatgroupset where from_id ='" . $from_id . "'";

                    $order_query = $this->pdo->query($sql);
                    $order_info = $order_query->fetchAll();

                    if ($order_info[0]['id'] > 0) {

                        $uid_info = explode(",", $message);
                        if (count($uid_info) != 7) {
                            //获取录入信息：
                            $parameter = array(
                                'chat_id' => $chatid,
                                'parse_mode' => 'HTML',
                                'text' => '你输入的格式不正确,请输入格式：支付类型,商户号,xx,xx,U汇率,+/-浮动指数,U币地址'
                            );
                            $this->http_post_data('sendMessage', json_encode($parameter));
                            exit();
                        } else {
                            $type = substr($uid_info['5'], 0, 1);
                            $changes = explode($type, $uid_info['5']);
                            if (count($changes) != 2) {
                                $parameter = array(
                                    'chat_id' => $chatid,
                                    'parse_mode' => 'HTML',
                                    'text' => '你输入的浮动指数格式不正确,例如：[1:] -0.09   [2:]+0.18'
                                );
                                $this->http_post_data('sendMessage', json_encode($parameter));
                                exit();
                            }
                        }


                        //$set_sql1= "DELETE FROM pay_uset";
                        //$this->pdo->exec($set_sql1);
                        //先查看当前商户是否已经存在，如果存在就是更新：
                        $set_sql1 = "select * FROM pay_uset where uid='" . $uid_info['1'] . "' and typelist='" . $uid_info['0'] . "'";
                        $order_query2 = $this->pdo->query($set_sql1);
                        $order_info2 = $order_query2->fetchAll();
                        $uid_info['6'] = trim($uid_info['6']);
                        if ($order_info2) {

                            $set_sql = "update pay_uset set one='" . $uid_info['2'] . "',two='" . $uid_info['3'] . "',three='" . $uid_info['4'] . "',four='" . $uid_info['5'] . "',five='" . $uid_info['6'] . "' where  uid='" . $uid_info['1'] . "' and typelist='" . $uid_info['0'] . "'";
                            $this->pdo->exec($set_sql);
                            //获取录入信息：
                            $parameter = array(
                                'chat_id' => $chatid,
                                'parse_mode' => 'HTML',
                                'text' => '修改商户号：' . $uid_info['1'] . '的' . $uid_info['0'] . '汇率成功,如需结束请输入：0000'
                            );
                            $this->http_post_data('sendMessage', json_encode($parameter));
                            exit();
                        } else {
                            $set_sql = "insert into pay_uset (uid,one,two,three,four,five,createtime,typelist) values ('" . $uid_info['1'] . "','" . $uid_info['2'] . "','" . $uid_info['3'] . "','" . $uid_info['4'] . "','" . $uid_info['5'] . "','" . $uid_info['6'] . "','" . time() . "','" . $uid_info['0'] . "')";
                            $this->pdo->exec($set_sql);
                            //获取录入信息：
                            $parameter = array(
                                'chat_id' => $chatid,
                                'parse_mode' => 'HTML',
                                'text' => '设置商户汇率信息成功,如需结束请输入：0000'
                            );
                            $this->http_post_data('sendMessage', json_encode($parameter));
                            exit();
                        }

                    }
                    $sql2 = "select * from pay_ordercha where type ='1' and from_id ='" . $from_id . "' and chat_id='" . $chatid . "'";

                    $order_query2 = $this->pdo->query($sql2);
                    $chaorder_info2 = $order_query2->fetchAll();

                    if ($chaorder_info2[0]['id'] > 0) {


                        if (!empty($chaorder_info2[0]['cha'])) {

                            $chang_where = explode(" ", $message);
                            $find_where = "";
                            //查询数据出来：
                            for ($i = 0; $i < strlen($chaorder_info2[0]['cha']); $i++) {

                                $re[] = substr($chaorder_info2[0]['cha'], $i, 1);
                            }

                            foreach ($re as $ke => $vs) {
                                $can_arr = array(0, 1, 2, 3, 4);
                                if (!in_array($vs, $can_arr)) {
                                    $vs = 0;
                                }

                                if ($ke == "0") {
                                    if ($vs == "1") {
                                        $find_where .= "trade_no='" . $chang_where[0] . "' ";
                                    } elseif ($vs == "2") {
                                        $find_where .= "out_trade_no='" . $chang_where[0] . "' ";
                                    } elseif ($vs == "3") {
                                        $find_where .= "terminals='" . $chang_where[0] . "' ";
                                    }
                                } elseif ($ke == "1") {


                                    if ($vs == "1") {
                                        if (empty($find_where)) {
                                            $find_where .= "uid='" . $chang_where[1] . "' ";
                                        } else {
                                            $find_where .= "and uid='" . $chang_where[1] . "' ";
                                        }

                                    }
                                } elseif ($ke == "2") {
                                    if ($vs == "1") {
                                        $find_where .= "and type='1' ";//支付宝
                                    } elseif ($vs == "2") {
                                        $find_where .= "and type='2' ";//微信
                                    } elseif ($vs == "3") {
                                        $find_where .= "and type='3' ";//QQ钱包
                                    } elseif ($vs == "3") {
                                        $find_where .= "and type='13' ";//云闪付
                                    }
                                } elseif ($ke == "3") {
                                    if ($vs == "1") {
                                        $find_where .= "and status='1' ";
                                    }
                                }
                            }


                            $sql_count = "select count(*) from pay_order where " . $find_where;

                            $q = $this->pdo->query($sql_count);
                            $rows = $q->fetch();
                            $count_info = $rows[0];

                            $sql = "select trade_no,money,type,status from pay_order where " . $find_where . " order by trade_no desc limit 0,20 ";
                            $order_query = $this->pdo->query($sql);
                            $order_info = $order_query->fetchAll();

                            $messgae = "";
                            foreach ($order_info as $key => $value) {
                                //2022062114155153521 (https://g.com/)~50元~🦋~✅
                                //2022062114155153521 (https://g.com/)~50元~🍀~✖️
                                if ($value['type'] == "1") {
                                    $change_type = "🦋";
                                } else {
                                    $change_type = "🍀";
                                }
                                if ($value['status'] == "1") {
                                    $change_type2 = "✅";
                                } else {
                                    $change_type2 = "✖";
                                }
                                $messgae .= "/order_detail" . $value['trade_no'] . "~" . $value['money'] . "元~" . $change_type . "~" . $change_type2 . "\n\r";

                            }

                            if ($count_info > 20) {

                                $inline_keyboard_arr2[0] = array('text' => "下一页", "callback_data" => "nextgroup###2&&&order");
                                // $inline_keyboard_arr2[1] = array('text' => "搜索", "callback_data" => "findorderonly");
                                $keyboard = [
                                    'inline_keyboard' => [
                                        $inline_keyboard_arr2
                                    ]
                                ];
                                $parameter = array(
                                    'chat_id' => $chatid,
                                    'parse_mode' => 'HTML',
                                    'text' => $messgae,
                                    'reply_markup' => $keyboard,
                                    'disable_web_page_preview' => true
                                );
                            } else {
                                // $inline_keyboard_arr2[0] = array('text' => "搜索", "callback_data" => "findorderonly");
                                // $keyboard = [
                                //     'inline_keyboard' => [
                                //         $inline_keyboard_arr2
                                //     ]
                                // ];
                                $parameter = array(
                                    'chat_id' => $chatid,
                                    'parse_mode' => 'HTML',
                                    'text' => $messgae,
                                    // 'reply_markup' => $keyboard,
                                    // 'disable_web_page_preview' => true
                                );
                            }


                            $this->http_post_data('sendMessage', json_encode($parameter));

                            $sql_info = "delete from pay_ordercha where type ='1' and from_id ='" . $from_id . "' and chat_id='" . $chatid . "'";

                            $this->pdo->exec($sql_info);

                            $this->http_post_data('sendMessage', json_encode($parameter));
                            exit();
                        }


                        /* 搜索内容选择:
                          0: 全部
                          1，订单号，
                          2，商户订单号
                          3，终端渠道

                          商户号选择2:
                          0，全部
                          1，商户号

                          支付方式选择:
                          0，全部
                          1，支付宝
                          2，微信
                          3，QQ红包
                          4，云闪付

                          状态选择:
                          0，全部
                          1，已完成*/
                        $set_sql = "update pay_ordercha set cha='" . $message . "' where  type ='1' and from_id ='" . $from_id . "' and chat_id='" . $chatid . "'";
                        $this->pdo->exec($set_sql);
                        $parameter = array(
                            'chat_id' => $chatid,
                            'parse_mode' => 'HTML',
                            'text' => 'OK！请输入查询数据'
                        );
                        $this->http_post_data('sendMessage', json_encode($parameter));
                        exit();
                    }

                    $set_sqlq = "select * FROM pay_usercaozuo where chat_id='" . $chatid . "'";
                    $order_query_q = $this->pdo->query($set_sqlq);
                    $user_caozuo = $order_query_q->fetchAll();
                    if ($user_caozuo) {
                        $uid = $user_caozuo[0]['uid'];
                        if ($user_caozuo[0]['types'] == "1") {
                            //$messages = "你正在添加通知人的输入，你直接输入例如：@111 @222 @333";

                            if (strpos($message, "@") == true) {
                                $parameter = array(
                                    'chat_id' => $chatid,
                                    'parse_mode' => 'HTML',
                                    'text' => "你输入的格式错误！需要 @xxx某人的格式",
                                );
                                $this->http_post_data('sendMessage', json_encode($parameter));
                                exit();
                            }
                            $message = " " . $message;

                            $this->pdo->exec("UPDATE pay_userpayorder SET tuisong='" . $message . "' WHERE uid='" . $uid . "'");

                        } elseif ($user_caozuo[0]['types'] == "2") {
                            //$messages = "你正在添加当达到多少单未支付进行通知，当达到多少单未支付进行通知，例如：60,50,40,30,10  必须英文逗号隔开！";

                            $this->pdo->exec("UPDATE pay_userpayorder SET dingdanshu='" . $message . "' WHERE uid='" . $uid . "'");
                        } elseif ($user_caozuo[0]['types'] == "3") {
                            //$messages = "你正在添加通道检索时间范围，例如输入：60   就是只检索最近60分钟用过的所有通道的未支付情况";
                            if (!is_numeric($message)) {
                                $parameter = array(
                                    'chat_id' => $chatid,
                                    'parse_mode' => 'HTML',
                                    'text' => "你输入的格式错误！请输入整数！你输入的是：" . $message,
                                );
                                $this->http_post_data('sendMessage', json_encode($parameter));
                                exit();
                            }
                            $this->pdo->exec("UPDATE pay_userpayorder SET jiansuotime='" . $message . "' WHERE uid='" . $uid . "'");
                        } else {
                            //$messages = "你正在添加设置同一个通道相同的两条消息最少间隔通知时间，例如输入：60  就是如果60分钟内同样的消息如果通知过一次，就不会再次通知";

                            if (!is_numeric($message)) {
                                $parameter = array(
                                    'chat_id' => $chatid,
                                    'parse_mode' => 'HTML',
                                    'text' => "你输入的格式错误！请输入整数！你输入的是：" . $message,
                                );
                                $this->http_post_data('sendMessage', json_encode($parameter));
                                exit();
                            }
                            $this->pdo->exec("UPDATE pay_userpayorder SET jiangetime='" . $message . "' WHERE uid='" . $uid . "'");
                        }
                        $parameter = array(
                            'chat_id' => $chatid,
                            'parse_mode' => 'HTML',
                            'text' => "修改成功",
                        );
                        $set_sql = "DELETE FROM pay_usercaozuo where chat_id='" . $chatid . "'";
                        $this->pdo->exec($set_sql);
                        $this->http_post_data('sendMessage', json_encode($parameter));
                        exit();
                    }


                    //设置汇率：
                    if ($message == "old查看ppp商户列表") {
                        $this->allgroup($chatid);
                    } elseif ($message == "查看商户列表") {
                        $this->chaojiyonghuquanxian($userid, $chatid);
                        $this->allgroup($chatid);
                    } else {
                        $zhifu_hou = array("订单管理", "结算管理", "商户管理", "支付接口", "其他功能", '广播推送');
                        if (in_array($message, $zhifu_hou)) {
                            //根据群信息去查询商户的信息：


                            $this->findhoutai($chatid, $message, $from_id, $data);
                        } else {
                            //根据群信息去查询商户的信息：

                            $this->findgroup($chatid, $message, $from_id, $data);
                        }
                    }
                }

            }
        }
    }

    public function gotoya($all_user_pp, $chatid, $content, $type)
    {
        $guangboinfo_query = $this->pdo->query("SELECT types FROM pay_guangbozhuang LIMIT 1");
        $guangboinfo = $guangboinfo_query->fetchAll();
        if ($guangboinfo) {
            $res = $this->pdo->exec("UPDATE pay_guangbozhuang SET types='1' ");
        } else {
            $this->pdo->exec("INSERT INTO `pay_guangbozhuang` (`types`) VALUES ('1')");
        }
        $sqpa = count($all_user_pp);
        if ($sqpa > 0) {
            foreach ($all_user_pp as $key => $values) {
                if ($type == 5) {
                    $this->xiafazuoriuid($content, $values);
                } elseif ($type == 6) {
                    $this->quedingxiafazuoriuid($content, $values);
                } else {
                    $this->xiaoxinoend($content, $values);
                }

                unset($all_user_pp[$key]);
            }

            $this->xiaoxi("终于推送成功！类型为：" . $type, $chatid);
        } else {
            $this->xiaoxi("没有可推送的对象", $chatid);
            exit();
        }
        exit();
    }

    public function shujuku($sql)
    {
        $order_query = $this->pdo->query($sql);
        $info = $order_query->fetchAll();
        return $info;
    }

    public function xiaoxi($msg, $chatid, $type = "0", $answer = "")
    {
        $parameter = array(
            'chat_id' => $chatid,
            'parse_mode' => 'HTML',
            'text' => $msg
        );
        $this->http_post_data('sendMessage', json_encode($parameter));
        if ($type == "1") {
            $parameter = array(
                'callback_query_id' => $answer,
                'text' => "",
            );
            $this->http_post_data('answerCallbackQuery', json_encode($parameter));
        }

        exit();
    }

    public function xiaoxinoend($msg, $chatid)
    {
        $parameter = array(
            'chat_id' => $chatid,
            'parse_mode' => 'HTML',
            'text' => $msg
        );
        $this->http_post_data('sendMessage', json_encode($parameter));

    }

    public function findhoutai($chatid, $message, $from_id, $data)
    {
        $userid = $data['message']['from']['id'];//获取message
        $username = $data['message']['from']['username'];//用户名称

        if (strpos($message, '订单管理') !== false) {
            $quanxian = "订单管理";
            $this->quanxian($chatid, $userid, $quanxian, $username);

            $this->dingdanguanli($chatid, $from_id);
        } elseif (strpos($message, '结算管理') !== false) {
            $quanxian = "结算管理";
            $this->quanxian($chatid, $userid, $quanxian, $username);
            $this->jiesuanguanli($chatid, $message, $from_id, $data);
        } elseif (strpos($message, '商户管理') !== false) {
            $quanxian = "商户管理";
            $this->quanxian($chatid, $userid, $quanxian, $username);
            $this->shanghuguanli($chatid, $from_id);
        } elseif (strpos($message, '支付接口') !== false) {
            $quanxian = "支付接口";
            $this->quanxian($chatid, $userid, $quanxian, $username);
            //$this->zhifuguanli($chatid, $from_id);
        } elseif (strpos($message, '其他功能') !== false) {
            $quanxian = "其他功能";
            $this->quanxian($chatid, $userid, $quanxian, $username);
            $this->qitaguanli($chatid, $from_id);
        } elseif (strpos($message, '广播推送') !== false) {
            $quanxian = "广播推送";
            $this->quanxian($chatid, $userid, $quanxian, $username);
            $this->guangbo($chatid, $message, $from_id, $data);
        }
    }

    public function jiesuanguanli($chatid, $message, $from_id, $data)
    {

        $inline_keyboard_arr = array(
            array('text' => "今日实时", "callback_data" => "chajintianshishi###1"),
            array('text' => "昨日结算", "callback_data" => "chajintianshishi###2"),

        );
        $msg = "请选择查看类型:";
        $keyboard = [
            'inline_keyboard' => [
                $inline_keyboard_arr,

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


    public function guangbo($chatid, $message, $from_id, $data)
    {
        $inline_keyboard_arr = array(
            array('text' => "关注机器人的全部用户", "callback_data" => "paliangshang###1"),
            array('text' => "机器人全部所在群", "callback_data" => "paliangshang###2"),

        );
        $inline_keyboard_arr2 = array(
            array('text' => "今天跑量的所有商户群", "callback_data" => "paliangshang###3"),
            array('text' => "昨天跑量的所有商户群", "callback_data" => "paliangshang###4"),
        );
        $inline_keyboard_arr3 = array(
            array('text' => "下发信息推送", "callback_data" => "paliangshang###5"),
            array('text' => "自动昨日下发推送", "callback_data" => "paliangshang###6"),

        );

        $msg = "请选择推送到哪里:";
        $keyboard = [
            'inline_keyboard' => [
                $inline_keyboard_arr,
                $inline_keyboard_arr2,
                $inline_keyboard_arr3
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

    public function orderzhuan($order_sn)
    {


        // 截取前14位，表示年、月、日、时、分、秒
        $timeString = substr($order_sn, 0, 14);

        // 使用 DateTime 类解析字符串
        $date = DateTime::createFromFormat('YmdHis', $timeString);

        // 将日期格式化为 'm-d H:i:s'
        $formattedDate = $date->format('m-d H:i:s');

        return $formattedDate;
    }


    public function dingdanguanli($chatid, $from_id, $type = 0, $uid = 0)
    {

        if ($type == "0") {
            $sql_count = "select count(*) from pay_order";
            $sql = "select waiwangip,trade_no,money,type,status from pay_order order by trade_no desc limit 0,20";
        } else {
            $sql_count = "select count(*) from pay_order where uid='" . $uid . "'";
            $sql = "select waiwangip,trade_no,money,type,status from pay_order where uid='" . $uid . "' order by trade_no desc limit 0,20";
        }

        $q = $this->pdo->query($sql_count);
        $rows = $q->fetch();
        $count_info = $rows[0];


        $order_query = $this->pdo->query($sql);
        $order_info = $order_query->fetchAll();
        if (!$order_info) {
            $this->xiaoxi("请去商户群发送查询订单请求", $chatid);
        }
        $messgae = "";
        foreach ($order_info as $key => $value) {
            //2022062114155153521 (https://g.com/)~50元~🦋~✅
            //2022062114155153521 (https://g.com/)~50元~🍀~✖️
            // if ($value['type'] == "1") {
            //     $change_type = "😂";
            // } else {
            //     $change_type = "🙈";
            // }
            if ($value['status'] == "1") {
                $change_type2 = "✅";
            } else {
                $change_type2 = "✖";
            }
            $tianshi_bot_url = $this->tianshi_bot_url;
            if ($type == 0) {
                $typelist = "order_detail";
            } else {
                $typelist = "shang_detail";
            }

            if ($value['type'] == 1) {
                $waiwang = "支";
            } else {
                $waiwang = "微";
            }

            // if($value['waiwangip']==0){
            //     $waiwang = "内";
            // }else{
            //     $waiwang = "外";
            // }
            $new_order_sn = $this->orderzhuan($value['trade_no']);

            $messgae .= "<b><a href='" . $tianshi_bot_url . "?start=" . $typelist . $value['trade_no'] . "'>" . $new_order_sn . "</a></b>~" . $waiwang . "~<b>" . $value['money'] . "元</b>~" . $change_type2 . "\n\r";

        }

        if ($count_info > 20) {

            $inline_keyboard_arr2[0] = array('text' => "下一页", "callback_data" => "nextgroup###2&&&order***" . $uid);
            // $inline_keyboard_arr2[1] = array('text' => "搜索", "callback_data" => "findorderonly");
            $keyboard = [
                'inline_keyboard' => [
                    $inline_keyboard_arr2
                ]
            ];
            $parameter = array(
                'chat_id' => $chatid,
                'parse_mode' => 'HTML',
                'text' => $messgae,
                'reply_markup' => $keyboard,
                'disable_web_page_preview' => true
            );
        } else {
            // $inline_keyboard_arr2[0] = array('text' => "搜索", "callback_data" => "findorderonly");
            // $keyboard = [
            //     'inline_keyboard' => [
            //         $inline_keyboard_arr2
            //     ]
            // ];
            $parameter = array(
                'chat_id' => $chatid,
                'parse_mode' => 'HTML',
                'text' => $messgae,
                // 'reply_markup' => $keyboard,
                // 'disable_web_page_preview' => true
            );
        }


        $this->http_post_data('sendMessage', json_encode($parameter));
        exit();
    }


    public function findgroup($chatid, $message, $from_id, $data)
    {
        $username = $data['message']['from']['username'];//用户名称

        $set_sqlq = "select * FROM pay_type where status='1'";
        $order_query_q = $this->pdo->query($set_sqlq);
        $user_type = $order_query_q->fetchAll();
        $new_type = array();
        // 设置字符集
        $this->pdo->exec("SET NAMES utf8mb4");
        foreach ($user_type as $item => $v) {
            $new_type[$v['name']] = $v['showname'];
        }
        /*$new_type = array(
            'alipay'=>"支付宝",
            'wxpay'=>"微信支付",
            'qqpay'=>"QQ钱包",
            'webbank'=>'网银支付',
            'yunshanpay'=>"云闪付",
            'kaka'=>'卡转卡',
            'shuzi'=>'数字货币'
        );*/
        //指定订单的明细
        //指定订单的明细
        //指定订单的明细
        if (strpos($message, 'shang_detail') !== false) {
            $info_arr = explode("detail", $message);
            /*🅿️订单号:2022062114155153521
                🆔商户订单号:62b16213ad4e5e50bb31
                📱终端:pc
                🧑‍💻商户号:1003
                💰订单金额:50元
                ♻️支付方式:🦋支付宝
                🔧支付插件:bujingyun
                🔎IP地址:34.75.87.355
                ⏱创建时间:2022-06-21 15:46:07
                ⏰完成时间:2022-06-21 15:46:07
                ⭕️支付状态:已完成✅
                📣通知状态:已通知✅

                ⚙️操作:改已完成 (https://goo.com/)~重新通知 (https://g.com/)~删除订单 (https://chh.com/)*/
            //A.trade_no,A.out_trade_no,A.terminals,A.uid,A.money,A.ip,A.addtime,A.endtime,A.status
            $sql = "select A.*,B.name as channel_name from pay_order as A left join pay_channel as B on A.channel = B.id where A.trade_no='" . $info_arr['1'] . "'";
            $order_query = $this->pdo->query($sql);
            $order_info = $order_query->fetchAll();
            $detai_info = $order_info['0'];
            if ($detai_info['type'] == "1") {
                $change_type = "🦋支付宝";
            } else {
                $change_type = "🍀微信";
            }
            if ($detai_info['status'] == "1") {
                $change_type2 = "已完成✅";
            } else {
                $change_type2 = "未完成✖";
            }
            if (!empty($detai_info['date'])) {
                $change_type3 = "已通知✅";
            } else {
                $change_type3 = "未通知✖";
            }
            $messages = "
            🅿️订单号:" . $detai_info['trade_no'] . "
🆔商户订单号:" . $detai_info['out_trade_no'] . "
📱终端:" . $detai_info['terminals'] . "
🧑‍💻商户号:" . $detai_info['uid'] . "
💰订单金额:" . $detai_info['money'] . "元
♻️支付方式:" . $change_type . "
🔎IP地址:" . $detai_info['ip'] . "
⏱创建时间:" . $detai_info['addtime'] . "
⏰完成时间:" . $detai_info['endtime'] . "
⭕️支付状态:" . $change_type2 . "
📣通知状态:" . $change_type3;


            $parameter = array(
                'chat_id' => $chatid,
                'parse_mode' => 'HTML',
                'text' => $messages,
                'disable_web_page_preview' => true
            );


            $this->http_post_data('sendMessage', json_encode($parameter));
            exit();

        }
        if (strpos($message, 'order_detail') !== false) {
            $info_arr = explode("detail", $message);
            /*🅿️订单号:2022062114155153521
                🆔商户订单号:62b16213ad4e5e50bb31
                📱终端:pc
                🧑‍💻商户号:1003
                💰订单金额:50元
                ♻️支付方式:🦋支付宝
                🔧支付插件:bujingyun
                🔎IP地址:34.75.87.355
                ⏱创建时间:2022-06-21 15:46:07
                ⏰完成时间:2022-06-21 15:46:07
                ⭕️支付状态:已完成✅
                📣通知状态:已通知✅

                ⚙️操作:改已完成 (https://goo.com/)~重新通知 (https://g.com/)~删除订单 (https://chh.com/)*/
            //A.trade_no,A.out_trade_no,A.terminals,A.uid,A.money,A.ip,A.addtime,A.endtime,A.status
            $sql = "select A.*,B.name as channel_name from pay_order as A left join pay_channel as B on A.channel = B.id where A.trade_no='" . $info_arr['1'] . "'";
            $order_query = $this->pdo->query($sql);
            $order_info = $order_query->fetchAll();
            $detai_info = $order_info['0'];
            if ($detai_info['type'] == "1") {
                $change_type = "🦋支付宝";
            } else {
                $change_type = "🍀微信";
            }
            if ($detai_info['status'] == "1") {
                $change_type2 = "已完成✅";
            } else {
                $change_type2 = "未完成✖";
            }
            if (!empty($detai_info['date'])) {
                $change_type3 = "已通知✅";
            } else {
                $change_type3 = "未通知✖";
            }
            $messages = "
            🅿️订单号:" . $detai_info['trade_no'] . "
🆔商户订单号:" . $detai_info['out_trade_no'] . "
📱终端:" . $detai_info['terminals'] . "
🧑‍💻商户号:" . $detai_info['uid'] . "
💰订单金额:" . $detai_info['money'] . "元
♻️支付方式:" . $change_type . "
🔧支付插件:" . $detai_info['channel_name'] . "
🔎IP地址:" . $detai_info['ip'] . "
⏱创建时间:" . $detai_info['addtime'] . "
⏰完成时间:" . $detai_info['endtime'] . "
⭕️支付状态:" . $change_type2 . "
📣通知状态:" . $change_type3;

            //操作:改已完成 (https://goo.com/)~重新通知 (https://g.com/)~删除订单 (https://chh.com/)
            $inline_keyboard_arr2[0] = array('text' => "改已完成", "callback_data" => "changorder_finish_" . $info_arr['1']);
            $inline_keyboard_arr2[1] = array('text' => "重新通知", "callback_data" => "changorder_notice_" . $info_arr['1']);
            $inline_keyboard_arr2[2] = array('text' => "删除订单", "callback_data" => "changorder_delete_" . $info_arr['1']);
            $keyboard = [
                'inline_keyboard' => [
                    $inline_keyboard_arr2
                ]
            ];
            $parameter = array(
                'chat_id' => $chatid,
                'parse_mode' => 'HTML',
                'text' => $messages,
                'reply_markup' => $keyboard,
                'disable_web_page_preview' => true
            );


            $this->http_post_data('sendMessage', json_encode($parameter));
            exit();

        }
        if (strpos($message, '新增商户汇率') !== false) {
            $this->chaojiyonghuquanxian($from_id, $chatid);
            //纪录当前用户正在录入信息：查询是不是正在设置概率
            $sql = "select * from pay_chatgroupset where from_id ='" . $from_id . "'";
            $order_query = $this->pdo->query($sql);
            $order_info = $order_query->fetchAll();
            if ($order_info) {
                if ($order_info['uid'] > 0) {
                    $text = '你正在调整商户号：' . $order_info['uid'] . '的设置,结束请回复：0000';
                } else {
                    $text = '你正在添加某商户号的设值：,结束请回复：0000';
                }
                $parameter = array(
                    'chat_id' => $chatid,
                    'parse_mode' => 'HTML',
                    'text' => $text
                );
                $this->http_post_data('sendMessage', json_encode($parameter));
                exit();
            }
            $set_sql = "insert into pay_chatgroupset (chat_id,status,createtime,from_id) values ('" . $chatid . "','0','" . time() . "', '" . $from_id . "')";
            $this->pdo->exec($set_sql);

            $parameter = array(
                'chat_id' => $chatid,
                'parse_mode' => 'HTML',
                'text' => "good!请直接:支付类型,商户号,xx,xx,U率,+/-上浮指数,U币地址",
            );

            $this->http_post_data('sendMessage', json_encode($parameter));

        }




        if (strpos($message, '/del') !== false) {
            $this->chaojiyonghuquanxian($from_id, $chatid);

            //查询当前商户是否存在
            $info_arr = explode("l", $message);
            $info_arr_2 = explode("@", $info_arr['1']);

            if (count($info_arr_2) > 1) {
                $info_arr = $info_arr_2['0'];
                $info_arr = array("0", $info_arr_2['0']);
            }


            if (count($info_arr) > 1) {
                $uid = $info_arr['1'];
                $del_user_sql = "select * FROM pay_uset where uid ='" . $uid . "'";
                $del_user_info = $this->pdo->query($del_user_sql);
                $del_user_info_detail = $del_user_info->fetchAll();

                if ($del_user_info_detail) {
                    //群关联记录删除
                    $sql_info = "delete from pay_botsettle where merchant ='" . $uid . "'";
                    $this->pdo->exec($sql_info);
                    //用户记录删除
                    $sql_info = "delete from pay_uset where uid ='" . $uid . "'";
                    $this->pdo->exec($sql_info);
                    $parameter = array(
                        'chat_id' => $chatid,
                        'parse_mode' => 'HTML',
                        'text' => "删除成功"

                    );
                    $this->http_post_data('sendMessage', json_encode($parameter));
                    exit();

                } else {
                    $parameter = array(
                        'chat_id' => $chatid,
                        'parse_mode' => 'HTML',
                        'text' => "为查询此商户信息，请核对"

                    );
                    $this->http_post_data('sendMessage', json_encode($parameter));
                    exit();
                }
            } else {
                $parameter = array(
                    'chat_id' => $chatid,
                    'parse_mode' => 'HTML',
                    'text' => "/del商户号，才是正确格式"

                );
                $this->http_post_data('sendMessage', json_encode($parameter));
                exit();
            }


        }
        if (strpos($message, '强制解绑') !== false) {

            //$this->chaojiyonghuquanxian($from_id, $chatid);
            $quanxian = "解绑";
            $this->quanxian($chatid, $from_id, $quanxian, $username);


            $jie = explode("解绑_", $message);
            $pid = $jie[1];
            $sql_info = "select * from pay_botsettle where merchant ='" . $pid . "'";
            $order_query2 = $this->pdo->query($sql_info);
            $order_info2 = $order_query2->fetchAll();
            if (!$order_info2) {
                $parameter = array(
                    'chat_id' => $chatid,
                    'parse_mode' => 'HTML',
                    'text' => "未查询到商户号：" . $pid . "的详细信息，无法强制解绑此商户号！"

                );
                $this->http_post_data('sendMessage', json_encode($parameter));
                exit();
            }
            //群关联记录删除
            $sql_info = "delete from pay_botsettle where merchant ='" . $pid . "'";
            $this->pdo->exec($sql_info);

            $uid = $order_info2['0']['merchant'];
            $sql_info2 = "delete from pay_uset where uid ='" . $uid . "'";
            $this->pdo->exec($sql_info2);

            //已經綁定群了：
            $parameter = array(
                'chat_id' => $chatid,
                'parse_mode' => 'HTML',
                'text' => "强制解绑商户号:" . $uid . "成功！"
            );
            $this->http_post_data('sendMessage', json_encode($parameter));


        }
        if (strpos($message, '解绑') !== false) {
            //$this->chaojiyonghuquanxian($from_id, $chatid);
            $quanxian = "解绑";
            $this->quanxian($chatid, $from_id, $quanxian, $username);

            //绑定群：
            //查詢當前群是否已經綁定了：
            $sql_info = "select * from pay_botsettle where chatid ='" . $chatid . "'";
            $order_query2 = $this->pdo->query($sql_info);
            $order_info2 = $order_query2->fetchAll();
            if ($order_info2) {
                //群关联记录删除
                $sql_info = "delete from pay_botsettle where chatid ='" . $chatid . "'";
                $this->pdo->exec($sql_info);

                $uid = $order_info2['0']['merchant'];
                $sql_info2 = "delete from pay_uset where uid ='" . $uid . "'";
                $this->pdo->exec($sql_info2);

                //已經綁定群了：
                $parameter = array(
                    'chat_id' => $chatid,
                    'parse_mode' => 'HTML',
                    'text' => "解绑成功！"
                );
                $this->http_post_data('sendMessage', json_encode($parameter));
            } else {

                $parameter = array(
                    'chat_id' => $chatid,
                    'parse_mode' => 'HTML',
                    'text' => "错误！当前群尚未绑定商户号"

                );
                $this->http_post_data('sendMessage', json_encode($parameter));
            }

        }
        if (strpos($message, '/bdid') !== false) {
            $this->chaojiyonghuquanxian($from_id, $chatid);
            //绑定群用户提醒人：
            //查詢當前群是否已經綁定了：
            $sql_info = "select * from pay_botsettle where chatid ='" . $chatid . "'";
            $order_query2 = $this->pdo->query($sql_info);
            $order_info2 = $order_query2->fetchAll();
            if (!$order_info2) {
                //已經綁定群了：
                $parameter = array(
                    'chat_id' => $chatid,
                    'parse_mode' => 'HTML',
                    'text' => "当前群尚未绑定商户号"

                );
                $this->http_post_data('sendMessage', json_encode($parameter));
            } else {
                if (!empty($order_info2['0']['atyonghu'])) {
                    //已經綁定群了：
                    $parameter = array(
                        'chat_id' => $chatid,
                        'parse_mode' => 'HTML',
                        'text' => "已绑定如下通知：" . $order_info2[0]['atyonghu'] . "已经回U\r\n  命令：/tongzhidel可以删除此通知设置"

                    );
                    $this->http_post_data('sendMessage', json_encode($parameter));
                    exit();
                }


                $merchant = explode("/bdid", $message);


                if ($from_id != "982124360") {
                    $parameter = array(
                        'chat_id' => $chatid,
                        'parse_mode' => 'HTML',
                        'text' => "操作失败！群绑定商户号操作只运行楚歌操作！"

                    );
                    $this->http_post_data('sendMessage', json_encode($parameter));
                    exit();
                }

                if (empty($merchant['1'])) {
                    $parameter = array(
                        'chat_id' => $chatid,
                        'parse_mode' => 'HTML',
                        'text' => "error.设置失败：格式 /bdid@xxxqqq"

                    );
                    $this->http_post_data('sendMessage', json_encode($parameter));
                    exit();
                }
                //$set_sql= "insert into pay_botsettle (chatid,merchant,createtime,settletime,from_id) values ('".$chatid."','".$merchant[1]."','".time()."','"."0". "','".$from_id."')";
                $res = $this->pdo->exec("UPDATE pay_botsettle SET atyonghu='" . $merchant['1'] . "' WHERE chatid='" . $chatid . "'");

                $parameter = array(
                    'chat_id' => $chatid,
                    'parse_mode' => 'HTML',
                    'text' => "OK，设置群关联用户绑定成功！"

                );
                $this->http_post_data('sendMessage', json_encode($parameter));
            }

        }
        if (strpos($message, '/bd') !== false) {
            //$this->chaojiyonghuquanxian($from_id, $chatid);
            $quanxian = "/bd";
            $this->quanxian($chatid, $from_id, $quanxian, $username);
            //绑定群：
            //查詢當前群是否已經綁定了：
            $sql_info = "select * from pay_botsettle where chatid ='" . $chatid . "'";
            $order_query2 = $this->pdo->query($sql_info);
            $order_info2 = $order_query2->fetchAll();
            if ($order_info2) {
                //已經綁定群了：
                $parameter = array(
                    'chat_id' => $chatid,
                    'parse_mode' => 'HTML',
                    'text' => "该群已经绑定商户号：" . $order_info2[0]['merchant']

                );
                $this->http_post_data('sendMessage', json_encode($parameter));
            } else {
                $merchant = explode("/bd", $message);


                /* if ($from_id != "982124360") {  //5054318030
                     $parameter = array(
                         'chat_id' => $chatid,
                         'parse_mode' => 'HTML',
                         'text' => "操作失败！群绑定商户号操作只运行楚歌操作！"

                     );
                     $this->http_post_data('sendMessage', json_encode($parameter));
                     exit();
                 }*/

                if (empty($merchant['1'])) {
                    $parameter = array(
                        'chat_id' => $chatid,
                        'parse_mode' => 'HTML',
                        'text' => "error.设置失败：格式 /bd商户号"

                    );
                    $this->http_post_data('sendMessage', json_encode($parameter));
                    exit();
                }
                if (strpos($merchant['1'], '|') !== false) {
                    $all_pid = explode("|", $merchant['1']);
                } else {
                    $all_pid = array($merchant['1']);
                }
                if (count($all_pid) > 1) {
                    $this->xiaoxi("禁止绑定多商户号！", $chatid);
                }


                $can = 0;
                foreach ($all_pid as $ksq => $veq) {

                    $sql_info = "select * from pay_botsettle where merchant ='" . $veq . "'";
                    $order_query2 = $this->pdo->query($sql_info);
                    $order_info2 = $order_query2->fetchAll();
                    if ($order_info2) {
                        $can = 1;
                        //已經綁定群了：
                        $parameter = array(
                            'chat_id' => $chatid,
                            'parse_mode' => 'HTML',
                            'text' => "商户号：" . $veq . "已经存在过其他的群中，请先去解绑！"

                        );
                        $this->http_post_data('sendMessage', json_encode($parameter));
                    }

                }
                if ($can == "1") {
                    exit();
                }


                $set_sql = "insert into pay_botsettle (chatid,merchant,createtime,settletime,from_id) values ('" . $chatid . "','" . $merchant[1] . "','" . time() . "','" . "0" . "','" . $from_id . "')";
                $this->pdo->exec($set_sql);
                $parameter = array(
                    'chat_id' => $chatid,
                    'parse_mode' => 'HTML',
                    'text' => "OK，设置成功！"

                );
                $this->http_post_data('sendMessage', json_encode($parameter));
            }

        }


        if (strpos($message, '实时下发') !== false) {


            $quanxian = "实时下发";
            $this->quanxian($chatid, $from_id, $quanxian, $username);

            $sql_info = "select * from pay_botsettle where chatid ='" . $chatid . "'";

            $order_query2 = $this->pdo->query($sql_info);
            $chatinfo = $order_query2->fetchAll();

            if (!$chatinfo) {
                //已經綁定群了：
                $parameter = array(
                    'chat_id' => $chatid,
                    'parse_mode' => 'HTML',
                    'text' => "该群暂未绑定商户号，请输入快捷命令：/bd"

                );
                $this->http_post_data('sendMessage', json_encode($parameter));
            } else {
                $uid = $chatinfo['0']['merchant'];


                $uid_end = $chatinfo['0']['merchant'];

                $today = date("Y-m-d");
                $todays = date("Y年m月d日");

                $huilvinfo = $this->huilvinfo("99999", "99999");
                $fufonginfo = $this->fudonginfo($uid, $chatid);
                $fenchenginfo = $this->fenchenginfo($uid, $chatid);

                $tongdaoxinxi = $this->tongdaoxinxi($uid, $chatid);
                $zhifuxinxi = $this->zhifuxinxi($uid, $chatid);


                $sql_zhifu = "select id,showname from pay_type";

                $zhifu_fetch = $this->shujuku($sql_zhifu);
                $zhifu_info_arr = array();
                foreach ($zhifu_fetch as $kp => $vp) {
                    $zhifu_info_arr[$vp['id']] = $vp['showname'];
                }

                if (count($zhifuxinxi) <= 0) {
                    $this->xiaoxi("当前商户暂未设置支付类型费率，请先设置！", $chatid);
                }
                $all_zhifu = array();  //纯支付方式的量
                $all_tongdao = array(); //纯设置通道的量

                $all_tongdao_zhifu = array();  //支付方式下的各个通道跑的数据

                $uid_arr = explode("|", $uid);
                if (count($uid_arr) > 1) {

                    foreach ($uid_arr as $k => $v) {
                        $inline_keyboard_arr[$k] = array('text' => "下发商户:" . $v, "callback_data" => "实时下发商户_" . $v);
                    }

                    $keyboard = [
                        'inline_keyboard' => [
                            $inline_keyboard_arr
                        ]
                    ];
                    $parameter = array(
                        'chat_id' => $chatid,
                        'parse_mode' => 'HTML',
                        'text' => "请选择要下发的商户",
                        'reply_markup' => $keyboard,

                    );

                    $this->http_post_data('sendMessage', json_encode($parameter));
                    exit();

                } else {
                    //查询次商户号今日总收入信息：
                    $sql_info = "select * from pay_order where status = '1' and uid ='" . $uid . "' and date='" . $today . "'";


                    $order_query3 = $this->pdo->query($sql_info);
                    $chatinfo = $order_query3->fetchAll();
                    if (count($chatinfo) <= 0) {
                        $this->xiaoxi("未查询到今日支付订单成功数据记录！", $chatid);
                    }

                    $all_money = 0;
                    foreach ($chatinfo as $key => $value) {
                        $all_money += $value['money'];
                        //支付方式计算
                        $all_zhifu[$value['type']] += $value['money'];
                        $all_tongdao_zhifu[$value['type']][$value['channel']] += $value['money'];


                        if (array_key_exists($value['channel'], $tongdaoxinxi)) {
                            //通道费用计算
                            $all_tongdao[$value['channel']] += $value['money'];
                        }
                    }


                    $sql_info3 = "select username,usdt_str from pay_user where  uid ='" . $uid . "'";
                    $order_query7 = $this->pdo->query($sql_info3);
                    $chatinfo3 = $order_query7->fetchAll();
                    $uidinfo2 = $chatinfo3[0];


                    $msg = "✅今天跑量\r\n🆔商户号:" . $uid . "\r\n🧑🏻‍💼名字:" . $uidinfo2['username'] . "\r\n";

                    $msg_tongdao = "";


                    if (count($all_zhifu) > 0) {
                        foreach ($all_zhifu as $kt => $vt) {
                            $sql_zhifu = "select showname from pay_type where  id ='" . $kt . "'";

                            $zhifu_fetch = $this->shujuku($sql_zhifu);

                            $zhifu_info = $zhifu_fetch[0]['showname'];
                            $msg .= "🔔" . $zhifu_info . "总量:" . $vt . "\r\n";
                        }

                    }


                    $msg .= "💹总跑量:" . $all_money . "\r\n";

                    $type = substr($fufonginfo, 0, 1);
                    if ($type == "-") {
                        $changs = explode("-", $fufonginfo);
                        $shiji_huilv = $huilvinfo - $changs[1];
                    } else {
                        $changs = explode("+", $fufonginfo);
                        $shiji_huilv = $huilvinfo + $changs[1];
                    }
                    $shiji_huilv_tousu = $shiji_huilv - 0.1;
                    $all_usdt_m = 0;
                    $all_fusdt_money = 0;
                    $xiafa_str = "";

                    foreach ($all_tongdao_zhifu as $kv => $vv) {
                        //$zhifu_info_arr[$kv]
                        //$msg .= "\r\n📮" . $zhifu_info_arr[$kv] . "跑量如下：\r\n\r\n";
                        foreach ($vv as $kp => $vp) {
                            $channel_sql = "select id,name from pay_channel where id='" . $kp . "'";
                            $channel_info_query = $this->shujuku($channel_sql);
                            $channel_info = $channel_info_query[0];
                            // $msg .= "(" . $channel_info['id'] . ")" . $channel_info['name'] . ":" . $vp . "\r\n";
                            if (array_key_exists($kp, $tongdaoxinxi)) {

                                $zhifu_lixi = $tongdaoxinxi[$kp];

                            } else {
                                $zhifu_lixi = $zhifuxinxi[$kv];

                            }
                            $type = substr($fufonginfo, 0, 1);

                            $jisuan = ($vp * $zhifu_lixi * $fenchenginfo);
                            //$msg .= $vp . "*" . $zhifu_lixi . "*" . $fenchenginfo . "/(" . $shiji_huilv . ")=" . $jisuan . "U\r\n\r\n";

                            $xiafa_str .= $jisuan . "+";

                            $all_usdt_m += $jisuan;
                            $all_fusdt_money += $vp;
                        }
                    }
                    $msg .= "💹费率后总额:" . $all_usdt_m . "\r\n";

                    $msg .= "\r\n➖➖➖➖➖➖➖➖➖\r\n\r\n";
                    $msg .= "不可下发金额\r\n";


                    $trx_info = "select * from pay_usertrx";
                    $trx_jinri = $this->pdo->query($trx_info);
                    $trx_arr = $trx_jinri->fetchAll();

                    if ($trx_arr) {
                        $trx_shouxufei = $trx_arr[0]['trx'];
                    } else {
                        $trx_shouxufei = 0.00;
                    }
                    $xiafa_str .= "-" . $trx_shouxufei;
                    //查询t0的限额：

                    $jinri_tojiesuan = round($this->tojiesuan / $shiji_huilv, 2);
                    //查看今日下发数据记录：
                    $jinri_info = "select money,jutishijian,feiu_money,feilv from pay_jinrixiafa where status='1' and pid ='" . $uid . "' and xiafatime='" . $today . "' and chatid='" . $chatid . "'";
                    $order_jinri = $this->pdo->query($jinri_info);
                    $tjinri_arr = $order_jinri->fetchAll();
                    $all_jinri_xiafa = 0.00;

                    $xiafa_str = substr($xiafa_str, 0, -1);

                    if ($tjinri_arr) {

                        $msg .= "\r\n📮今天下发历史记录" . "\r\n";
                        foreach ($tjinri_arr as $kj => $vj) {
                            $ti = date('H:i:s', $vj['jutishijian']);
                            $msg .= $ti . " 成功下发：" . $vj['feiu_money'] . "/" . $vj['feilv'] . "/" . $vj['money'] . "U(含手续费)\r\n";
                            $all_jinri_xiafa += $vj['feiu_money'];

                            $xiafa_str .= "-" . $vj['feiu_money'];
                        }
                    }
                    $xiafa_str .= "-" . $tousu_U;

                    $msg .= "\r\n❌t0不可结算限额:" . $this->tojiesuan . "元\r\n\r\n";


                    /*投诉：*/
                    $tousu_info2 = "select * from pay_usertousu where pid ='" . $uid . "'";
                    $order_tousu2 = $this->pdo->query($tousu_info2);
                    $tousu_m2 = $order_tousu2->fetchAll();
                    $tousu_today = 0;
                    $tousu_today2 = 0;
                    $tousu_U2 = 0;
                    foreach ($tousu_m2 as $k => $v) {
                        $time = date('m-d', strtotime($v['date']));
                        $tousu_today += $v['money'];

                        if ($v['status'] == "1") {
                            //已扣除
                            $pp = "已扣除";
                        } else {
                            //待扣除
                            $pp = "待扣除 ---- /delete_tousu_" . $v['id'];
                            $tousu_today2 += $v['money'];

                            $tousu_U2 += $v['money'];

                        }


                        $msg .= "❌" . $time . ":投诉退款:" . $v['money'] . "元  ----" . $pp . "\r\n";
                    }


                    //查看今日的投诉金额：
                    $tousu_info = "select sum(money) as tousumoney from pay_usertousu where status='0' and  pid ='" . $uid . "'";
                    $order_tousu = $this->pdo->query($tousu_info);
                    $tousu_m = $order_tousu->fetchAll();
                    //$tousu_today = round($tousu_m[0]['tousumoney'], 2);
                    $tousu_today = (floor(($tousu_m[0]['tousumoney'] * 100)) / 100);
                    //查看投诉退款数据：
                    if ($tousu_U2 > 0) {
                        $tousu_U = $tousu_U2;
                        $msg .= "❌合计待投诉退款:" . $tousu_today . "元\r\n";
                    } else {
                        $tousu_U = 0;
                    }
                    $bukexiafaheji = $tousu_today + $all_jinri_xiafa + $this->tojiesuan;
                    $msg .= "\r\n💹不可下发金额合计：" . $bukexiafaheji . "元\r\n\r\n";
                    $msg .= "➖➖➖➖➖➖➖➖➖\r\n";
                    $msg .= "下发扣除费用\r\n\r\n";
                    $msg .= "🔄Trx手续费=" . $trx_shouxufei . "U(每次下发)\r\n";
                    $msg .= "➖➖➖➖➖➖➖➖➖\r\n";

                    //$keyixiafa = round($all_usdt_m, 2) - round($all_jinri_xiafa, 2) - $tousu_U - round($trx_shouxufei, 2)-$jinri_tojiesuan;

                    //下发了多少金额： 总金额-已经下发-投诉金额-限额+手续费
                    $shijixiafa_jiner_rnb = $all_usdt_m - $all_jinri_xiafa - $tousu_U - $this->tojiesuan;
                    $jieer_str = $all_usdt_m . " - " . $bukexiafaheji . " = " . $shijixiafa_jiner_rnb;

                    //当前可下发:   总金额-已经下发的-限额
                    $keyixiafa_value = $all_usdt_m - $all_jinri_xiafa - $this->tojiesuan;
                    $keyixiafa_str = $all_usdt_m . " - " . $all_jinri_xiafa . " - " . $this->tojiesuan . '=' . $keyixiafa_value;


                    //实际下发：当前可下发-手续费-投诉金额

                    //$shijixiafa_value = round(($shijixiafa_jiner_rnb/$shiji_huilv),2)-round($trx_shouxufei, 2);
                    $shijixiafa_value = (floor((($shijixiafa_jiner_rnb / $shiji_huilv) * 100)) / 100) - (floor(($trx_shouxufei * 100)) / 100);


                    $shijixiafa_str = $shijixiafa_jiner_rnb . "/" . $shiji_huilv . " - " . $trx_shouxufei . "=" . $shijixiafa_value;


                    $msg .= "\r\n🈴当前可下发:" . $jieer_str . "元";
                    $msg .= "\r\n🈴实际下发:" . $shijixiafa_str . "U";
                    $msg .= "\r\n✅下发地址:\r\n" . $uidinfo2['usdt_str'];

                    $today_time = date("d");
                    //查看下发地址：
                    if ($shijixiafa_value > 0) {
                        $inline_keyboard_arr[0] = array('text' => "立即下发今日:" . $shijixiafa_value . "U", "callback_data" => "jinrixiafa_user_" . $uid . "&&" . $shijixiafa_value . "###" . $shijixiafa_jiner_rnb . "!!!" . $today_time);

                    } else {
                        $inline_keyboard_arr[0] = array('text' => "当前收益不足以下发", "callback_data" => "wufaxiafa_user_" . $uid_end);

                    }
                    $inline_keyboard_arr2[0] = array('text' => "查详细账单", "callback_data" => "chakanjinrixiangxi_" . $uid_end);

                }


                $keyboard = [
                    'inline_keyboard' => [
                        $inline_keyboard_arr,
                        $inline_keyboard_arr2
                    ]
                ];
                $parameter = array(
                    'chat_id' => $chatid,
                    'parse_mode' => 'HTML',
                    'text' => $msg,
                    'reply_markup' => $keyboard,

                );

                $this->http_post_data('sendMessage', json_encode($parameter));
                exit();
            }
        }
        if (strpos($message, '下发昨日收益') !== false) {
            //$this->xiaoxinoend("点击了",$chatid);
            $quanxian = "下发昨日结算收益";
            $this->quanxian($chatid, $from_id, $quanxian, $username);

            $sql_info = "select * from pay_botsettle where chatid ='" . $chatid . "'";

            $order_query2 = $this->pdo->query($sql_info);
            $chatinfo = $order_query2->fetchAll();

            if (!$chatinfo) {
                $this->xiaoxi("该群暂未绑定商户号，请输入快捷命令：/bd商户号", $chatid);
            }
            $uid = $chatinfo['0']['merchant'];
            $uid_end = $uid;


            if ($this->kaiqi_teshu_xiafa) {
                $nayitian = $this->teshu_riqi;
                $today = date("Y-m-d", strtotime(date($nayitian)));
                $todays = date("Y年m月d日", strtotime(date($nayitian)));
                $todays2 = date("m月d日", strtotime(date($nayitian)));
            } else {
                $today = date("Y-m-d", strtotime("-1 day"));
                $todays = date("Y年m月d日", strtotime("-1 day"));
                $todays2 = date("m月d日", strtotime("-1 day"));
            }


            $uid_arr = explode("|", $uid);

            $huilvinfo = $this->huilvinfo("99999", "99999");
            $fufonginfo = $this->fudonginfo($uid, $chatid);
            $fenchenginfo = $this->fenchenginfo($uid, $chatid);

            $tongdaoxinxi = $this->tongdaoxinxi($uid, $chatid);
            $zhifuxinxi = $this->zhifuxinxi($uid, $chatid);

            $sql_zhifu = "select id,showname from pay_type";

            $zhifu_fetch = $this->shujuku($sql_zhifu);
            $zhifu_info_arr = array();
            foreach ($zhifu_fetch as $kp => $vp) {
                $zhifu_info_arr[$vp['id']] = $vp['showname'];
            }

            if (count($zhifuxinxi) <= 0) {
                $this->xiaoxi("当前商户暂未设置支付类型费率，请先设置！", $chatid);
            }

            //这里去请求设置汇率：$huilv_api
            $now_time = strtotime(date("Y-m-d"));
            //查询是不是请求过了:
            $huilv_info = $sql_info = "select * from pay_huoquhuilv where  huoqutime='" . $now_time . "' order by id desc";
            $hui_query = $this->pdo->query($huilv_info);
            $huilvinfop = $hui_query->fetchAll();
            if ($huilvinfop) {
                //如果存在，就看看时间：
                $nexttimes = $huilvinfop[0]['nexttime'];
                if (time() > $nexttimes) {
                    $this->ouyi(0, $huilvinfop[0]['id']);
                }
            } else {
                $this->ouyi(1);

            }

            $all_zhifu = array();  //纯支付方式的量
            $all_tongdao = array(); //纯设置通道的量
            $all_tongdao_zhifu = array();  //支付方式下的各个通道跑的数据

            $sql_info3 = "select username,usdt_str from pay_user where  uid ='" . $uid . "'";
            $order_query7 = $this->pdo->query($sql_info3);
            $chatinfo3 = $order_query7->fetchAll();
            $uidinfo2 = $chatinfo3[0];


            if (count($uid_arr) > 1) {

                $parameter = array(
                    'chat_id' => $chatid,
                    'parse_mode' => 'HTML',
                    'text' => "</b>当前群存在多个商户号,请先解绑，将商户分群后再操作！</b>",
                );
                $this->http_post_data('sendMessage', json_encode($parameter));
                exit();

                foreach ($uid_arr as $k => $v) {
                    $inline_keyboard_arr[$k] = array('text' => "下发商户:" . $v, "callback_data" => "结算下发商户_" . $v);
                }

                $keyboard = [
                    'inline_keyboard' => [
                        $inline_keyboard_arr
                    ]
                ];
                $parameter = array(
                    'chat_id' => $chatid,
                    'parse_mode' => 'HTML',
                    'text' => "请选择要下发昨日收益结算的商户",
                    'reply_markup' => $keyboard,

                );

                $this->http_post_data('sendMessage', json_encode($parameter));
                exit();

            } else {
                //查询次商户号昨日总收入信息：
                $sql_info = "select sum(getmoney) as getmoney from pay_order where status = '1' and uid ='" . $uid . "' and date='" . $today . "'";

                $order_query3 = $this->pdo->query($sql_info);
                $chatinfo = $order_query3->fetchAll();
                $order_today = round($chatinfo[0]['getmoney'], 2);
                if ($order_today <= 0) {

                    $message .= "<strong>💰收入结算:0u</strong>";
                    $parameter = array(
                        'chat_id' => $chatid,
                        'parse_mode' => 'HTML',
                        'text' => $message,
                    );


                    $this->http_post_data('sendMessage', json_encode($parameter));
                    exit();
                }


                //查看昨日总下发的记录 这里有一点需要注意，如果昨日存在有下发异常的 需要天使自己核对 手动下发：
                $zuori_sql = "select * from pay_jinrixiafa where status = '0' and pid ='" . $uid . "' and xiafatime='" . $today . "'";

                $zuorixiafa = $this->shujuku($zuori_sql);
                if ($zuorixiafa) {
                    $parameter = array(
                        'chat_id' => $chatid,
                        'parse_mode' => 'HTML',
                        'text' => "当前商户昨日存在实时下发" . $zuorixiafa[0]['money'] . "U异常！建议手动结算昨日收益！",
                    );
                    $this->http_post_data('sendMessage', json_encode($parameter));
                    exit();

                }

                //最日下发的数据
                $zuori_money = 0.00;
                $zuori_usdt = 0.00;

                //昨日收益数据分析：
                $sql_info = "select * from pay_order where status = '1' and uid ='" . $uid . "' and date='" . $today . "'";
                $order_query3 = $this->pdo->query($sql_info);
                $zuoorderinfo = $order_query3->fetchAll();

                $all_money = 0;
                foreach ($zuoorderinfo as $key => $value) {
                    $all_money += $value['money'];
                    //支付方式计算
                    $all_zhifu[$value['type']] += $value['money'];

                    //支付方式下的各个通道跑的数据：
                    $all_tongdao_zhifu[$value['type']][$value['channel']] += $value['money'];
                    if (array_key_exists($value['channel'], $tongdaoxinxi)) {
                        //通道费用计算
                        $all_tongdao[$value['channel']] += $value['money'];
                    }
                }
                $msg = "✅" . $todays2 . "量情况如下\r\n🆔商户号:" . $uid . "\r\n🧑🏻‍💼名字:" . $uidinfo2['username'] . "\r\n";


                if (count($all_zhifu) > 0) {
                    foreach ($all_zhifu as $kt => $vt) {
                        $sql_zhifu = "select showname from pay_type where  id ='" . $kt . "'";

                        $zhifu_fetch = $this->shujuku($sql_zhifu);

                        $zhifu_info = $zhifu_fetch[0]['showname'];
                        $msg .= "🔔" . $zhifu_info . "总量:" . $vt . "\r\n";
                    }

                }


                $type = substr($fufonginfo, 0, 1);
                if ($type == "-") {
                    $changs = explode("-", $fufonginfo);
                    $shiji_huilv = $huilvinfo - $changs[1];
                } else {
                    $changs = explode("+", $fufonginfo);
                    $shiji_huilv = $huilvinfo + $changs[1];
                }

                $shiji_huilv_tousu = $shiji_huilv - 0.1;


                $all_usdt_m = 0;
                $all_fusdt_money = 0;
                $xiafa_str = "";
                $feilihoujiner = 0;
                foreach ($all_tongdao_zhifu as $kv => $vv) {
                    //$zhifu_info_arr[$kv]
                    //$msg .= "\r\n📮" . $zhifu_info_arr[$kv] . "跑量如下：\r\n\r\n";
                    foreach ($vv as $kp => $vp) {
                        $channel_sql = "select id,name from pay_channel where id='" . $kp . "'";
                        $channel_info_query = $this->shujuku($channel_sql);
                        $channel_info = $channel_info_query[0];
                        // $msg .= "(" . $channel_info['id'] . ")" . $channel_info['name'] . ":" . $vp . "\r\n";
                        if (array_key_exists($kp, $tongdaoxinxi)) {

                            $zhifu_lixi = $tongdaoxinxi[$kp];

                        } else {
                            $zhifu_lixi = $zhifuxinxi[$kv];

                        }
                        $type = substr($fufonginfo, 0, 1);

                        $jisuan = round(($vp * $zhifu_lixi * $fenchenginfo) / ($shiji_huilv), 2);
                        //$msg .= $vp . "*" . $zhifu_lixi . "*" . $fenchenginfo . "/(" . $shiji_huilv . ")=" . $jisuan . "U\r\n\r\n";

                        $xiafa_str .= $jisuan . "+";

                        $feilihoujiner += round(($vp * $zhifu_lixi * $fenchenginfo), 2);

                        $all_usdt_m += $jisuan;
                        $all_fusdt_money += $vp;
                    }
                }
                $msg .= "💹总跑量:" . $all_money . "元\r\n";
                $msg .= "💹费率后总额:" . $feilihoujiner . "元\r\n\r\n";
                $msg .= "➖➖➖➖➖➖➖➖➖\r\n\r\n";
                $msg .= "不可下发金额\r\n\r\n";

                $tousu_info2 = "select * from pay_usertousu where pid ='" . $uid . "'";

                $order_tousu2 = $this->pdo->query($tousu_info2);
                $tousu_m2 = $order_tousu2->fetchAll();
                $tousu_today = 0;
                $tousu_today2 = 0;
                $tousu_U = 0;
                $jinritimne = date("Y-m-d", time());
                foreach ($tousu_m2 as $k => $v) {
                    $time = date('m-d', strtotime($v['date']));
                    $tousu_today += $v['money'];

                    if ($v['status'] == "1") {
                        //已扣除
                        $pp = "已扣除";
                        //如果是今天扣的，要计算体现到出来：
                        if ($jinritimne == $v['koushijian']) {
                            $tousu_today2 += $v['money'];
                            $tousu_U += $v['money'];
                        }
                    } else {
                        //待扣除
                        $pp = "待扣除 ---- /delete_tousu_" . $v['id'];
                        $tousu_today2 += $v['money'];
                        $tousu_U += $v['money'];

                    }


                    $msg .= "❌" . $time . ":投诉退款:" . $v['money'] . "元  ----" . $pp . "\r\n";
                }


                //查看今日的投诉金额：
                /*$tousu_info = "select sum(money) as tousumoney from pay_usertousu where status='0' and  pid ='" . $uid . "' and date='" . $today . "'";
                $order_tousu = $this->pdo->query($tousu_info);
                $tousu_m = $order_tousu->fetchAll();

                $tousu_today = $tousu_m[0]['tousumoney']>0?$tousu_m[0]['tousumoney']:0;*/


                //查看投诉退款数据：
                if ($tousu_U > 0) {
                    $tousu_U2 = $tousu_U;
                    $msg .= "❌合计待投诉退款:" . $tousu_today2 . "元\r\n";
                } else {
                    $tousu_U2 = 0;
                }

                $xiafa_str = substr($xiafa_str, 0, -1);

                $xiafa_str .= "-" . $tousu_U2;

                //查看今日下发数据记录：
                $jinri_info = "select money,jutishijian,feiu_money,feilv from pay_jinrixiafa where status='1' and pid ='" . $uid . "' and xiafatime='" . $today . "' and chatid='" . $chatid . "'";
                $order_jinri = $this->pdo->query($jinri_info);
                $tjinri_arr = $order_jinri->fetchAll();
                $all_jinri_xiafa = 0.00;


                if ($tjinri_arr) {

                    $msg .= "\r\n📮" . $todays2 . "下发历史记录" . "\r\n";
                    foreach ($tjinri_arr as $kj => $vj) {
                        $zuori_money += $vj['all_feiu_money'];
                        $zuori_usdt += $vj['money'];


                        $ti = date('H:i:s', $vj['jutishijian']);
                        $msg .= "🔈" . $ti . " 已下发：" . $vj['feiu_money'] . "/" . $vj['feilv'] . "/" . $vj['money'] . "\r\n";
                        $all_jinri_xiafa += $vj['feiu_money'];

                        $xiafa_str .= "-" . $vj['feiu_money'];
                    }
                }
                $trx_info = "select * from pay_usertrx";
                $trx_jinri = $this->pdo->query($trx_info);
                $trx_arr = $trx_jinri->fetchAll();

                if ($trx_arr) {
                    $trx_shouxufei = $trx_arr[0]['trx'];
                } else {
                    $trx_shouxufei = 0.00;
                }

                $bukexiafaheji_zuoro = $all_jinri_xiafa + $tousu_today2;

                $msg .= "\r\n💹不可下发金额合计：" . $bukexiafaheji_zuoro . "元\r\n\r\n";
                $msg .= "➖➖➖➖➖➖➖➖➖\r\n";
                $msg .= "下发扣除费用\r\n\r\n";
                $msg .= "🔄Trx手续费=" . $trx_shouxufei . "U\r\n\r\n";
                $xiafa_str .= "-" . $trx_shouxufei;


                $keyixiafa_value = $feilihoujiner - $bukexiafaheji_zuoro;
                $keyixiafa_str = $feilihoujiner . " - " . $bukexiafaheji_zuoro . " = " . $keyixiafa_value;

                $msg .= "🈴当前可下发:" . $keyixiafa_str . "元";


                //实际下发：
                $shijixiafa_value = (floor((($keyixiafa_value / $shiji_huilv) * 100)) / 100) - $trx_shouxufei;
                $shijixiafa_str = $keyixiafa_value . "/" . $shiji_huilv . " - " . $trx_shouxufei . " = " . $shijixiafa_value;

                $msg .= "\r\n🈴实际下发:" . $shijixiafa_str . "U";

                $jie_all_jin_u = $all_jinri_xiafa > 0 ? $all_jinri_xiafa : 0;
                $jie_all_tou_u = $tousu_U2 > 0 ? round($tousu_U2, 2) : 0;
                $jie_all_usdt_m = round($all_usdt_m, 2);
                $keyixiafa = $jie_all_usdt_m - $jie_all_jin_u - $jie_all_tou_u - $trx_shouxufei;
                //$keyixiafa = $keyixiafa>0?round($keyixiafa,2):0;

                //$msg .= "\r\n" . $xiafa_str . "=" . $keyixiafa . "U";
                //$msg .= $shijixiafa_value . "U";
                $msg .= "\r\n✅下发地址:\r\n" . $uidinfo2['usdt_str'];


                //查询结算是否已经下发：
                $sql_info_u = "select * from pay_zuorixiafau where pid ='" . $uid . "' and xiafatime='" . $today . "' and status='1'";


                $order_query_user_u = $this->pdo->query($sql_info_u);
                $xiafa_i_u = $order_query_user_u->fetchAll();

                $xiafade_day = date("d");
                if ($xiafa_i_u) {
                    $inline_keyboard_arr[0] = array('text' => "收益已清算", "callback_data" => "yijingxiafa_" . $uid);
                } else {
                    $inline_keyboard_arr[0] = array('text' => "确定下发:" . $shijixiafa_value . "U", "callback_data" => "zuotianxiafa_user_" . $uid . "&&" . $shijixiafa_value . "!!!" . $xiafade_day);
                }
                $inline_keyboard_arr2[0] = array('text' => "查详细账单", "callback_data" => "chakanzuorixiangxi_" . $uid);


            }


            $keyboard = [
                'inline_keyboard' => [
                    $inline_keyboard_arr,
                    $inline_keyboard_arr2
                ]
            ];
            $parameter = array(
                'chat_id' => $chatid,
                'parse_mode' => 'HTML',
                'text' => $msg,
                'reply_markup' => $keyboard,

            );

            $this->http_post_data('sendMessage', json_encode($parameter));
            exit();

        }


        if (strpos($message, '/jinriteshushanghu_') !== false) {

            $uid_arr = explode("_", $message);
            $uid = $uid_arr[1];
            $today = date("Y-m-d");

            $datesa = date('Y-m-d H:i:s');
            //查询次商户号今日总收入信息：
            $sql_info = "select sum(getmoney) as getmoney from pay_order where status = '1' and uid ='" . $uid . "' and date='" . $today . "'";

            $order_query2 = $this->pdo->query($sql_info);
            $chatinfo = $order_query2->fetchAll();
            $order_today = round($chatinfo[0]['getmoney'], 2);

            $set_sql1 = "select typelist FROM pay_uset where uid='" . $uid . "'";
            $order_query_user = $this->pdo->query($set_sql1);
            $chatinfo_usertype = $order_query_user->fetchAll();

            $set_sql2 = "select * FROM pay_user where uid='" . $uid . "'";
            $order_query_user2 = $this->pdo->query($set_sql2);
            $chatinfo_userinfo = $order_query_user2->fetchAll();


            $message = "商户：" . $uid . "\n\r";
            $message .= "商户key：" . $chatinfo_userinfo[0]['key'] . "\n\r";
            $message .= "今日总收入：" . $order_today . "元\n\r";
            foreach ($chatinfo_usertype as $key2 => $value2) {
                $sql_info2 = "SELECT sum(getmoney) as getmoney FROM pay_order WHERE uid='" . $uid . "' AND type=(SELECT id FROM pay_type WHERE name='" . $value2['typelist'] . "') AND status=1 AND date='" . $today . "'";
                $order_query3 = $this->pdo->query($sql_info2);
                $chatinfo2 = $order_query3->fetchAll();
                $order_today_alipay = round($chatinfo2['0']['getmoney'], 2);
                $message .= "今日" . $new_type[$value2['typelist']] . "：" . $order_today_alipay . "元\n\r";
            }


            $parameter = array(
                'chat_id' => $chatid,
                'parse_mode' => 'HTML',
                'text' => $message

            );
            $this->http_post_data('sendMessage', json_encode($parameter));
        }
        if (strpos($message, '/zuoriteshushanghu_') !== false) {

            $uid_arr = explode("_", $message);
            $uid = $uid_arr[1];
            $today = date("Y-m-d", strtotime("-1 day"));

            $datesa = date('Y-m-d H:i:s');
            //查询次商户号今日总收入信息：
            $sql_info = "select sum(getmoney) as getmoney from pay_order where status = '1' and uid ='" . $uid . "' and date='" . $today . "'";

            $order_query2 = $this->pdo->query($sql_info);
            $chatinfo = $order_query2->fetchAll();
            $order_today = round($chatinfo[0]['getmoney'], 2);

            $set_sql1 = "select typelist FROM pay_uset where uid='" . $uid . "'";
            $order_query_user = $this->pdo->query($set_sql1);
            $chatinfo_usertype = $order_query_user->fetchAll();
            $message = "商户：" . $uid . "\n\r";
            $message .= "昨日总收入：" . $order_today . "元\n\r";
            foreach ($chatinfo_usertype as $key2 => $value2) {
                $sql_info2 = "SELECT sum(getmoney) as getmoney FROM pay_order WHERE uid='" . $uid . "' AND type=(SELECT id FROM pay_type WHERE name='" . $value2['typelist'] . "') AND status=1 AND date='" . $today . "'";
                $order_query3 = $this->pdo->query($sql_info2);
                $chatinfo2 = $order_query3->fetchAll();
                $order_today_alipay = round($chatinfo2['0']['getmoney'], 2);
                $message .= "昨日" . $new_type[$value2['typelist']] . "：" . $order_today_alipay . "元\n\r";
            }

            $set_sql2 = "select * FROM pay_user where uid='" . $uid . "'";
            $order_query_user2 = $this->pdo->query($set_sql2);
            $chatinfo_userinfo = $order_query_user2->fetchAll();
            $usdt_url = $chatinfo_userinfo[0]['usdt_str'];
            $key_url = $chatinfo_userinfo[0]['key'];

            $parameter = array(
                'chat_id' => $chatid,
                'parse_mode' => 'HTML',
                'text' => $message . "\r\n" . $key_url . "\r\n" . $usdt_url

            );
            $this->http_post_data('sendMessage', json_encode($parameter));
        }
        if (strpos($message, '今日收益') !== false || strpos($message, 'd0') !== false) {


            $sql_info = "select * from pay_botsettle where chatid ='" . $chatid . "'";
            $order_query2 = $this->pdo->query($sql_info);
            $chatinfo = $order_query2->fetchAll();


            if (!$chatinfo) {
                //已經綁定群了：
                $parameter = array(
                    'chat_id' => $chatid,
                    'parse_mode' => 'HTML',
                    'text' => "该群暂未绑定商户号，请输入快捷命令：/bd"

                );
                $this->http_post_data('sendMessage', json_encode($parameter));
            } else {
                $uid = $chatinfo['0']['merchant'];
                $today = date("Y-m-d");

                $datesa = date('Y-m-d H:i:s');
                $this->pdo->exec("INSERT INTO `pay_uidcao` (`uid`,`typelist`, `date`) VALUES ('" . $uid . "', '" . $message . "', '" . $datesa . "')");

                $uid_arr = explode("|", $uid);


                if (count($uid_arr) > 1) {
                    $message = "";
                    for ($i = 0; $i < count($uid_arr); $i++) {
                        //查询次商户号今日总收入信息：
                        $uids = $uid_arr[$i];
                        $sql_info = "select sum(getmoney) as getmoney from pay_order where status = '1' and uid ='" . $uids . "' and date='" . $today . "'";

                        $order_query2 = $this->pdo->query($sql_info);
                        $chatinfo = $order_query2->fetchAll();
                        $order_today = round($chatinfo[0]['getmoney'], 2);


                        $message .= "商户：" . $uids . "\n\r";
                        $message .= "今日总收入：" . $order_today . "元\n\r";

                        $set_sql1 = "select typelist FROM pay_uset where uid='" . $uids . "'";
                        $order_query_user = $this->pdo->query($set_sql1);
                        $chatinfo_usertype = $order_query_user->fetchAll();

                        foreach ($chatinfo_usertype as $key2 => $value2) {
                            $sql_info2 = "SELECT sum(getmoney) as getmoney FROM pay_order WHERE uid='" . $uids . "' AND type=(SELECT id FROM pay_type WHERE name='" . $value2['typelist'] . "') AND status=1 AND date='" . $today . "'";
                            $order_query3 = $this->pdo->query($sql_info2);
                            $chatinfo2 = $order_query3->fetchAll();
                            $order_today_alipay = round($chatinfo2['0']['getmoney'], 2);

                            $message .= "今日" . $new_type[$value2['typelist']] . "：" . $order_today_alipay . "元\n\r";
                        }

                        /*//$order_today_wxpay= $this->pdo->exec("SELECT sum(getmoney) as getmoney FROM pre_order WHERE uid='".$uid."' AND type=(SELECT id FROM pre_type WHERE name='wxpay') AND status=1 AND date='".$today."'");

                        $sql_info3 ="SELECT sum(getmoney) as getmoney FROM pay_order WHERE uid='".$uids."' AND type=(SELECT id FROM pay_type WHERE name='wxpay') AND status=1 AND date='".$today."'";
                        $order_query4 = $this->pdo->query($sql_info3);
                        $chatinfo3 = $order_query4->fetchAll();
                        $order_today_wxpay=round($chatinfo3['0']['getmoney'],2);

                        $sql_info4 ="SELECT sum(getmoney) as getmoney FROM pay_order WHERE uid='".$uids."' AND type=(SELECT id FROM pay_type WHERE name='qqpay') AND status=1 AND date='".$today."'";
                        $order_query5 = $this->pdo->query($sql_info4);
                        $chatinfo4 = $order_query5->fetchAll();
                        $order_today_qqpay=round($chatinfo4['0']['getmoney'],2);




                        $message.="今日微信：".$order_today_wxpay."元\n\r";
                        $message.="今日QQ钱包：".$order_today_qqpay."元\n\r\n\r\n\r";*/
                    }

                } else {
                    //查询次商户号今日总收入信息：
                    $sql_info = "select sum(getmoney) as getmoney from pay_order where status = '1' and uid ='" . $uid . "' and date='" . $today . "'";

                    $order_query2 = $this->pdo->query($sql_info);
                    $chatinfo = $order_query2->fetchAll();
                    $order_today = round($chatinfo[0]['getmoney'], 2);

                    $set_sql1 = "select typelist FROM pay_uset where uid='" . $uid . "'";
                    $order_query_user = $this->pdo->query($set_sql1);
                    $chatinfo_usertype = $order_query_user->fetchAll();
                    $message = "商户：" . $uid . "\n\r";
                    $message .= "今日总收入：" . $order_today . "元\n\r";
                    if (!$chatinfo_usertype) {
                        $chatinfo_usertype = array(array('typelist' => "alipay"), array('typelist' => "wxpay"));

                    }

                    foreach ($chatinfo_usertype as $key2 => $value2) {
                        $sql_info2 = "SELECT sum(getmoney) as getmoney FROM pay_order WHERE uid='" . $uid . "' AND type=(SELECT id FROM pay_type WHERE name='" . $value2['typelist'] . "') AND status=1 AND date='" . $today . "'";
                        $order_query3 = $this->pdo->query($sql_info2);
                        $chatinfo2 = $order_query3->fetchAll();
                        $order_today_alipay = round($chatinfo2['0']['getmoney'], 2);
                        $message .= "今日" . $new_type[$value2['typelist']] . "：" . $order_today_alipay . "元\n\r";
                    }

                }
                $parameter = array(
                    'chat_id' => $chatid,
                    'parse_mode' => 'HTML',
                    'text' => $message

                );
                $this->http_post_data('sendMessage', json_encode($parameter));


            }
        }
        if (strpos($message, '昨日收益') !== false || strpos($message, 'd1') !== false) {
            $sql_info = "select * from pay_botsettle where chatid ='" . $chatid . "'";
            $order_query2 = $this->pdo->query($sql_info);
            $chatinfo = $order_query2->fetchAll();
            if (!$chatinfo) {
                //已經綁定群了：
                $parameter = array(
                    'chat_id' => $chatid,
                    'parse_mode' => 'HTML',
                    'text' => "该群暂未绑定商户号，请输入快捷命令：/bd"

                );
                $this->http_post_data('sendMessage', json_encode($parameter));
            } else {
                $uid = $chatinfo['0']['merchant'];
                $today = date("Y-m-d", strtotime("-1 day"));


                $uid_arr = explode("|", $uid);
                if (count($uid_arr) > 1) {
                    $message = "";
                    for ($i = 0; $i < count($uid_arr); $i++) {
                        //查询次商户号今日总收入信息：
                        $uids = $uid_arr[$i];
                        $sql_info = "select sum(getmoney) as getmoney from pay_order where status = '1' and uid ='" . $uids . "' and date='" . $today . "'";

                        $order_query2 = $this->pdo->query($sql_info);
                        $chatinfo = $order_query2->fetchAll();
                        $order_today = round($chatinfo[0]['getmoney'], 2);

                        $set_sql1 = "select typelist FROM pay_uset where uid='" . $uids . "'";
                        $order_query_user = $this->pdo->query($set_sql1);
                        $chatinfo_usertype = $order_query_user->fetchAll();
                        $message .= "商户：" . $uids . "\n\r";
                        $message .= "昨日总收入：" . $order_today . "元\n\r";
                        foreach ($chatinfo_usertype as $key2 => $value2) {
                            $sql_info2 = "SELECT sum(getmoney) as getmoney FROM pay_order WHERE uid='" . $uids . "' AND type=(SELECT id FROM pay_type WHERE name='" . $value2['typelist'] . "') AND status=1 AND date='" . $today . "'";
                            $order_query3 = $this->pdo->query($sql_info2);
                            $chatinfo2 = $order_query3->fetchAll();
                            $order_today_alipay = round($chatinfo2['0']['getmoney'], 2);
                            $message .= "昨日" . $new_type[$value2['typelist']] . "：" . $order_today_alipay . "元\n\r";
                        }
                        $message .= "\n\r";
                        /*// $order_today_alipay= $this->pdo->exec("SELECT sum(getmoney) as getmoney FROM pre_order WHERE uid='".$uid."' AND type=(SELECT id FROM pre_type WHERE name='alipay') AND status=1 AND date='".$today."'");
                        $sql_info2 ="SELECT sum(getmoney) as getmoney FROM pay_order WHERE uid='".$uids."' AND type=(SELECT id FROM pay_type WHERE name='alipay') AND status=1 AND date='".$today."'";
                        $order_query3 = $this->pdo->query($sql_info2);
                        $chatinfo2 = $order_query3->fetchAll();
                        $order_today_alipay=round($chatinfo2['0']['getmoney'],2);


                        //$order_today_wxpay= $this->pdo->exec("SELECT sum(getmoney) as getmoney FROM pre_order WHERE uid='".$uid."' AND type=(SELECT id FROM pre_type WHERE name='wxpay') AND status=1 AND date='".$today."'");

                        $sql_info3 ="SELECT sum(getmoney) as getmoney FROM pay_order WHERE uid='".$uids."' AND type=(SELECT id FROM pay_type WHERE name='wxpay') AND status=1 AND date='".$today."'";
                        $order_query4 = $this->pdo->query($sql_info3);
                        $chatinfo3 = $order_query4->fetchAll();
                        $order_today_wxpay=round($chatinfo3['0']['getmoney'],2);


                        $message .= "商户：".$uids."\n\r";
                        $message.="昨日总收入：".$order_today."元\n\r";
                        $message.="昨日支付宝：".$order_today_alipay."元\n\r";
                        $message.="昨日微信：".$order_today_wxpay."元\n\r\n\r\n\r";*/

                    }

                } else {
                    //查询次商户号今日总收入信息：
                    $sql_info = "select sum(getmoney) as getmoney from pay_order where status = '1' and uid ='" . $uid . "' and date='" . $today . "'";


                    $order_query2 = $this->pdo->query($sql_info);
                    $chatinfo = $order_query2->fetchAll();
                    $order_today = round($chatinfo[0]['getmoney'], 2);


                    $set_sql1 = "select typelist FROM pay_uset where uid='" . $uid . "'";
                    $order_query_user = $this->pdo->query($set_sql1);
                    $chatinfo_usertype = $order_query_user->fetchAll();
                    $message = "商户：" . $uid . "\n\r";
                    $message .= "昨日总收入：" . $order_today . "元\n\r";
                    if (!$chatinfo_usertype) {
                        $chatinfo_usertype = array(array('typelist' => "alipay"), array('typelist' => "wxpay"));

                    }
                    foreach ($chatinfo_usertype as $key2 => $value2) {
                        $sql_info2 = "SELECT sum(getmoney) as getmoney FROM pay_order WHERE uid='" . $uid . "' AND type=(SELECT id FROM pay_type WHERE name='" . $value2['typelist'] . "') AND status=1 AND date='" . $today . "'";
                        $order_query3 = $this->pdo->query($sql_info2);
                        $chatinfo2 = $order_query3->fetchAll();
                        $order_today_alipay = round($chatinfo2['0']['getmoney'], 2);
                        $message .= "昨日" . $new_type[$value2['typelist']] . "：" . $order_today_alipay . "元\n\r";
                    }


                }

                $parameter = array(
                    'chat_id' => $chatid,
                    'parse_mode' => 'HTML',
                    'text' => $message

                );
                $this->http_post_data('sendMessage', json_encode($parameter));


            }
        }


        if (strpos($message, '下发昨日收益——old') !== false) {

            $quanxian = "下发昨日结算收益";
            $this->quanxian($chatid, $from_id, $quanxian, $username);

            $sql_info = "select * from pay_botsettle where chatid ='" . $chatid . "'";

            $order_query2 = $this->pdo->query($sql_info);
            $chatinfo = $order_query2->fetchAll();

            if (!$chatinfo) {
                //已經綁定群了：
                $parameter = array(
                    'chat_id' => $chatid,
                    'parse_mode' => 'HTML',
                    'text' => "该群暂未绑定商户号，请输入快捷命令：/bd"

                );
                $this->http_post_data('sendMessage', json_encode($parameter));
            } else {
                $uid = $chatinfo['0']['merchant'];


                $uid_end = $chatinfo['0']['merchant'];

                $today = date("Y-m-d", strtotime("-1 day"));
                $todays = date("Y年m月d日", strtotime("-1 day"));

                $uid_arr = explode("|", $uid);
                if (count($uid_arr) > 1) {
                    $message = "";
                    $message .= "<strong>⏰" . $todays . "结算:</strong>\n\r\n\r";


                    $ems_all_end = "0";
                    $ems_all_str_end = "";
                    $ems_new_end = "";
                    for ($j = 0; $j < count($uid_arr); $j++) {
                        //查询次商户号今日总收入信息：
                        $uid = $uid_arr[$j];
                        $sql_info = "select sum(getmoney) as getmoney from pay_order where status = '1' and uid ='" . $uid . "' and date='" . $today . "'";

                        $order_query3 = $this->pdo->query($sql_info);
                        $chatinfo = $order_query3->fetchAll();
                        $order_today = round($chatinfo[0]['getmoney'], 2);

                        //查看单商户最日的投诉金额：
                        $tousu_info = "select sum(money) as tousumoney from pay_usertousu where  pid ='" . $uid . "'";
                        $order_tousu = $this->pdo->query($tousu_info);
                        $tousu_m = $order_tousu->fetchAll();
                        $tousu_today = round($tousu_m[0]['tousumoney'], 2);


                        $sql_info2 = "select * from pay_uset where  uid ='" . $uid . "'";
                        $order_query6 = $this->pdo->query($sql_info2);
                        $chatinfo2 = $order_query6->fetchAll();
                        $uidinfo = $chatinfo2[0];


                        $sql_info3 = "select username from pay_user where  uid ='" . $uid . "'";
                        $order_query7 = $this->pdo->query($sql_info3);
                        $chatinfo3 = $order_query7->fetchAll();
                        $uidinfo2 = $chatinfo3[0];

                        //ｕ＝2323＊0.8＊0.94／6.4=238u

                        $message .= "<strong>🆔商户号:" . $uid . "</strong>\n\r";
                        $message .= "<strong>🧑🏻‍💼名字:" . $uidinfo2['username'] . "</strong>\n\r";
                        //$message .= "昨日收入：".$order_today."元\n\r";

                        if ($order_today <= 0) {

                            $message .= "<strong>💰收入结算:" . "0" . "u</strong>\n\r\n\r\n\r";
                        } else {
                            $ems_all = "0";
                            $ems_all_str = "";
                            $set_sql1 = "select typelist FROM pay_uset where uid='" . $uid . "'";
                            $order_query_user = $this->pdo->query($set_sql1);
                            $chatinfo_usertype = $order_query_user->fetchAll();
                            foreach ($chatinfo_usertype as $key2 => $value2) {
                                $sql_info2 = "select * from pay_uset where  uid ='" . $uid . "' and typelist='" . $value2['typelist'] . "'";
                                $order_query6 = $this->pdo->query($sql_info2);
                                $chatinfo2 = $order_query6->fetchAll();
                                $uidinfo = $chatinfo2[0];

                                $type = substr($uidinfo['four'], 0, 1);


                                $sql_info2 = "SELECT sum(getmoney) as getmoney FROM pay_order WHERE uid='" . $uid . "' AND type=(SELECT id FROM pay_type WHERE name='" . $value2['typelist'] . "') AND status=1 AND date='" . $today . "'";
                                $order_query4 = $this->pdo->query($sql_info2);
                                $chatinfo2 = $order_query4->fetchAll();
                                $order_today2 = round($chatinfo2[0]['getmoney'], 2);
                                if ($type == "-") {
                                    $changs = explode("-", $uidinfo['four']);
                                    $ems = intval($order_today2 * $uidinfo['one'] * $uidinfo['two'] / ($uidinfo['three'] - $changs[1]));
                                    $sss = $uidinfo['three'] - $changs[1];
                                    $message .= "<strong>💰" . $new_type[$value2['typelist']] . "结算:" . $order_today2 . "*" . $uidinfo['one'] . "*" . $uidinfo['two'] . "/" . $sss . "=" . $ems . "u" . "</strong>" . "\n\r";

                                } else {
                                    $changs = explode("+", $uidinfo['four']);
                                    $ems = intval($order_today2 * $uidinfo['one'] * $uidinfo['two'] / ($uidinfo['three'] + $changs[1]));
                                    $sss = $uidinfo['three'] + $changs[1];
                                    $message .= "<strong>💰" . $new_type[$value2['typelist']] . "结算:" . $order_today2 . "*" . $uidinfo['one'] . "*" . $uidinfo['two'] . "/" . $sss . "=" . $ems . "u</strong>" . "\n\r";
                                }
                                $ems_all += $ems;
                                $ems_all_str .= $ems . "u+";

                            }


                            $ems_all_str = substr($ems_all_str, 0, -1);


                            if ($tousu_today > 0) {
                                $tousu_u = round($tousu_today / ($uidinfo['three'] - $changs[1]), 2);
                                $message .= "❌投诉退款:" . $tousu_today . "元/" . ($uidinfo['three'] - $changs[1]) . "=" . $tousu_u . "u\r\n";
                                $ems_all = $ems_all - $tousu_u;
                                $ems_all_str = $ems_all_str . "-" . $tousu_u . "u";

                                $tousu_str .= "-" . $tousu_u . "u";

                                //$ems_all_end -=$tousu_u;

                                $message .= "<strong>🈴单商户合计:" . $ems_all_str . "=" . $ems_all . "u</strong>\n\r";
                            } else {

                                $message .= "<strong>🈴单商户合计:" . $ems_all_str . "=" . $ems_all . "u</strong>\n\r";
                            }
                            $ems_new_end .= $ems_all . "#";

                            $new_T = $this->func_substr_replace($uidinfo['five'], '*', 3, 4);

                            $message .= "<strong>💰单下发地址:" . $new_T . "</strong>\n\r\n\r";


                            $ems_all_end += $ems_all;

                            $ems_all_str_end .= $ems_all . "u+";

                        }

                    }
                    $ems_all_str_end = substr($ems_all_str_end, 0, -1);
                    $ems_new_end = substr($ems_new_end, 0, -1);


                    $message .= "<strong>🈴总合计:" . $ems_all_str_end . "=" . $ems_all_end . "u</strong>\n\r\n\r";

                    //查询结算是否已经下发：
                    $sql_info_u = "select * from pay_xiafau where uid ='" . $uid_arr[1] . "' and date='" . $today . "'";
                    $order_query_user_u = $this->pdo->query($sql_info_u);
                    $xiafa_i_u = $order_query_user_u->fetchAll();
                    if ($xiafa_i_u) {
                        $inline_keyboard_arr[0] = array('text' => "已经下发:" . $ems_all_end . "U", "callback_data" => "yijingxiafa_" . $uid_end);
                    } else {
                        $inline_keyboard_arr[0] = array('text' => "2确定下发:" . $ems_all_end . "U", "callback_data" => "xiafa_user_" . $uid_end . "&&" . $ems_new_end);

                    }

                } else {
                    //查询次商户号今日总收入信息：
                    $sql_info = "select sum(getmoney) as getmoney from pay_order where status = '1' and uid ='" . $uid . "' and date='" . $today . "'";

                    $order_query3 = $this->pdo->query($sql_info);
                    $chatinfo = $order_query3->fetchAll();
                    $order_today = round($chatinfo[0]['getmoney'], 2);

                    //查看最日的投诉金额：
                    $tousu_info = "select sum(money) as tousumoney from pay_usertousu where  pid ='" . $uid . "'";
                    $order_tousu = $this->pdo->query($tousu_info);
                    $tousu_m = $order_tousu->fetchAll();
                    $tousu_today = round($tousu_m[0]['tousumoney'], 2);


                    $sql_info3 = "select username from pay_user where  uid ='" . $uid . "'";
                    $order_query7 = $this->pdo->query($sql_info3);
                    $chatinfo3 = $order_query7->fetchAll();
                    $uidinfo2 = $chatinfo3[0];

                    //ｕ＝2323＊0.8＊0.94／6.4=238u
                    $message = "<strong>⏰" . $todays . "结算:</strong>\n\r";
                    $message .= "<strong>🆔商户号:" . $uid . "</strong>\n\r";
                    $message .= "<strong>🧑🏻‍💼名字:" . $uidinfo2['username'] . "</strong>\n\r";
                    //$message .= "昨日收入：".$order_today."元\n\r";

                    $set_sql1 = "select typelist,five FROM pay_uset where uid='" . $uid . "'";
                    $order_query_user = $this->pdo->query($set_sql1);
                    $chatinfo_usertype = $order_query_user->fetchAll();


                    if ($order_today <= 0) {

                        $message .= "<strong>💰收入结算:0u</strong>";
                        $parameter = array(
                            'chat_id' => $chatid,
                            'parse_mode' => 'HTML',
                            'text' => $message,
                        );


                        $this->http_post_data('sendMessage', json_encode($parameter));
                        exit();
                    } else {
                        $ems_all = "0";
                        $ems_all_str = "";
                        foreach ($chatinfo_usertype as $key2 => $value2) {
                            $sql_info2 = "select * from pay_uset where  uid ='" . $uid . "' and typelist='" . $value2['typelist'] . "'";
                            $order_query6 = $this->pdo->query($sql_info2);
                            $chatinfo2 = $order_query6->fetchAll();
                            $uidinfo = $chatinfo2[0];

                            $type = substr($uidinfo['four'], 0, 1);


                            $sql_info2 = "SELECT sum(getmoney) as getmoney FROM pay_order WHERE uid='" . $uid . "' AND type=(SELECT id FROM pay_type WHERE name='" . $value2['typelist'] . "') AND status=1 AND date='" . $today . "'";
                            $order_query4 = $this->pdo->query($sql_info2);
                            $chatinfo2 = $order_query4->fetchAll();
                            $order_today2 = round($chatinfo2[0]['getmoney'], 2);
                            if ($type == "-") {
                                $changs = explode("-", $uidinfo['four']);
                                $ems = intval($order_today2 * $uidinfo['one'] * $uidinfo['two'] / ($uidinfo['three'] - $changs[1]));
                                $sss = $uidinfo['three'] - $changs[1];
                                $message .= "<strong>💰" . $new_type[$value2['typelist']] . "结算:" . $order_today2 . "*" . $uidinfo['one'] . "*" . $uidinfo['two'] . "/" . $sss . "=" . $ems . "u" . "</strong>" . "\n\r";

                            } else {
                                $changs = explode("+", $uidinfo['four']);
                                $ems = intval($order_today2 * $uidinfo['one'] * $uidinfo['two'] / ($uidinfo['three'] + $changs[1]));
                                $sss = $uidinfo['three'] + $changs[1];
                                $message .= "<strong>💰" . $new_type[$value2['typelist']] . "结算:" . $order_today2 . "*" . $uidinfo['one'] . "*" . $uidinfo['two'] . "/" . $sss . "=" . $ems . "u</strong>" . "\n\r";
                            }
                            $ems_all += $ems;
                            $ems_all_str .= $ems . "u+";
                        }
                        $ems_all_str = substr($ems_all_str, 0, -1);

                        $tousu_u = round($tousu_today / ($uidinfo['three'] - $changs[1]), 2);

                        $ems_all = $ems_all - $tousu_u;
                        $ems_all_str = $ems_all_str . "-" . $tousu_u . "u";

                        $message .= "❌投诉退款:" . $tousu_today . "元/" . ($uidinfo['three'] - $changs[1]) . "=" . $tousu_u . "u\r\n";


                        $message .= "<strong>🈴合计:" . $ems_all_str . "=" . $ems_all . "u</strong>\n\r";

                        $new_T = $this->func_substr_replace($chatinfo_usertype[0]['five'], '*', 3, 4);


                        $message .= "<strong>💰下发地址:" . $new_T . "</strong>";
                        //查询结算是否已经下发：
                        $sql_info_u = "select * from pay_xiafau where uid ='" . $uid . "' and date='" . $today . "'";
                        $order_query_user_u = $this->pdo->query($sql_info_u);
                        $xiafa_i_u = $order_query_user_u->fetchAll();


                        if ($xiafa_i_u) {
                            $inline_keyboard_arr[0] = array('text' => "已经下发:" . $ems_all . "U", "callback_data" => "yijingxiafa_" . $uid_end);
                        } else {
                            $inline_keyboard_arr[0] = array('text' => "1确定下发:" . $ems_all . "U", "callback_data" => "xiafa_user_" . $uid_end . "&&" . $ems_all);

                        }
                    }
                }


                /* $keyboard = [
                     'inline_keyboard' => [
                         $inline_keyboard_arr
                     ]
                 ];*/
                if ($order_today > 0) {
                    $keyboard = [
                        'inline_keyboard' => [
                            $inline_keyboard_arr
                        ]
                    ];
                    $parameter = array(
                        'chat_id' => $chatid,
                        'parse_mode' => 'HTML',
                        'text' => $message,
                        'reply_markup' => $keyboard,

                    );
                } else {

                    $parameter = array(
                        'chat_id' => $chatid,
                        'parse_mode' => 'HTML',
                        'text' => $message,
                    );
                }
                $parameter = array(
                    'chat_id' => $chatid,
                    'parse_mode' => 'HTML',
                    'text' => $message,
                    'reply_markup' => $keyboard,

                );
                $this->http_post_data('sendMessage', json_encode($parameter));
            }
        }

        if (strpos($message, '昨日结算') !== false) {
            $sql_info = "select * from pay_botsettle where chatid ='" . $chatid . "'";

            $order_query2 = $this->pdo->query($sql_info);
            $chatinfo = $order_query2->fetchAll();

            /*$parameter = array(
                    'chat_id' => $chatid,
                    'parse_mode' => 'HTML',
                    'text'=>"该群暂未绑定商户号，请输入快捷命令：/bd"

                );
                $this->http_post_data('sendMessage', json_encode($parameter));
                exit();*/


            if (!$chatinfo) {
                //已經綁定群了：
                $parameter = array(
                    'chat_id' => $chatid,
                    'parse_mode' => 'HTML',
                    'text' => "该群暂未绑定商户号，请输入快捷命令：/bd"

                );
                $this->http_post_data('sendMessage', json_encode($parameter));
            } else {
                $uid = $chatinfo['0']['merchant'];
                $today = date("Y-m-d", strtotime("-1 day"));
                $todays = date("Y年m月d日", strtotime("-1 day"));

                $uid_arr = explode("|", $uid);
                if (count($uid_arr) > 1) {
                    $message = "";
                    $message .= "<strong>⏰" . $todays . "结算:</strong>\n\r\n\r";

                    $ems_all_end = "0";
                    $ems_all_str_end = "";
                    $tousu_str = "";
                    for ($j = 0; $j < count($uid_arr); $j++) {
                        //查询次商户号今日总收入信息：
                        $uid = $uid_arr[$j];
                        $sql_info = "select sum(getmoney) as getmoney from pay_order where status = '1' and uid ='" . $uid . "' and date='" . $today . "'";

                        $order_query3 = $this->pdo->query($sql_info);
                        $chatinfo = $order_query3->fetchAll();
                        $order_today = round($chatinfo[0]['getmoney'], 2);

                        //查看当前商户号最日的投诉金额：
                        $tousu_info = "select sum(money) as tousumoney from pay_usertousu where  pid ='" . $uid . "'";
                        $order_tousu = $this->pdo->query($tousu_info);
                        $tousu_m = $order_tousu->fetchAll();
                        $tousu_today = round($tousu_m[0]['tousumoney'], 2);


                        $sql_info2 = "select * from pay_uset where  uid ='" . $uid . "'";
                        $order_query6 = $this->pdo->query($sql_info2);
                        $chatinfo2 = $order_query6->fetchAll();
                        $uidinfo = $chatinfo2[0];


                        $sql_info3 = "select username from pay_user where  uid ='" . $uid . "'";
                        $order_query7 = $this->pdo->query($sql_info3);
                        $chatinfo3 = $order_query7->fetchAll();
                        $uidinfo2 = $chatinfo3[0];

                        //ｕ＝2323＊0.8＊0.94／6.4=238u

                        $message .= "<strong>🆔商户号:" . $uid . "</strong>\n\r";
                        $message .= "<strong>🧑🏻‍💼名字:" . $uidinfo2['username'] . "</strong>\n\r";
                        //$message .= "昨日收入：".$order_today."元\n\r";

                        if ($order_today <= 0) {

                            $message .= "<strong>💰收入结算:" . "0" . "u</strong>\n\r\n\r\n\r";
                        } else {
                            $set_sql1 = "select typelist FROM pay_uset where uid='" . $uid . "'";
                            $order_query_user = $this->pdo->query($set_sql1);
                            $chatinfo_usertype = $order_query_user->fetchAll();
                            $ems_all = "0";
                            $ems_all_str = "";

                            foreach ($chatinfo_usertype as $key2 => $value2) {
                                $sql_info2 = "select * from pay_uset where  uid ='" . $uid . "' and typelist='" . $value2['typelist'] . "'";
                                $order_query6 = $this->pdo->query($sql_info2);
                                $chatinfo2 = $order_query6->fetchAll();
                                $uidinfo = $chatinfo2[0];

                                $type = substr($uidinfo['four'], 0, 1);


                                $sql_info2 = "SELECT sum(getmoney) as getmoney FROM pay_order WHERE uid='" . $uid . "' AND type=(SELECT id FROM pay_type WHERE name='" . $value2['typelist'] . "') AND status=1 AND date='" . $today . "'";
                                $order_query4 = $this->pdo->query($sql_info2);
                                $chatinfo2 = $order_query4->fetchAll();
                                $order_today2 = round($chatinfo2[0]['getmoney'], 2);
                                if ($type == "-") {
                                    $changs = explode("-", $uidinfo['four']);
                                    $ems = intval($order_today2 * $uidinfo['one'] * $uidinfo['two'] / ($uidinfo['three'] - $changs[1]));
                                    $sss = $uidinfo['three'] - $changs[1];
                                    $message .= "<strong>💰" . $new_type[$value2['typelist']] . "结算:" . $order_today2 . "*" . $uidinfo['one'] . "*" . $uidinfo['two'] . "/" . $sss . "=" . $ems . "u" . "</strong>" . "\n\r";

                                } else {
                                    $changs = explode("+", $uidinfo['four']);
                                    $ems = intval($order_today2 * $uidinfo['one'] * $uidinfo['two'] / ($uidinfo['three'] + $changs[1]));
                                    $sss = $uidinfo['three'] + $changs[1];
                                    $message .= "<strong>💰" . $new_type[$value2['typelist']] . "结算:" . $order_today2 . "*" . $uidinfo['one'] . "*" . $uidinfo['two'] . "/" . $sss . "=" . $ems . "u</strong>" . "\n\r";
                                }
                                $ems_all += $ems;
                                $ems_all_str .= $ems . "u+";


                            }
                            $ems_all_end += $ems_all;
                            $ems_all_str_end .= $ems_all . "u+";


                            $ems_all_str = substr($ems_all_str, 0, -1);


                            if ($tousu_today > 0) {
                                $tousu_u = round($tousu_today / ($uidinfo['three'] - $changs[1]), 2);
                                $message .= "❌投诉退款:" . $tousu_today . "元/" . ($uidinfo['three'] - $changs[1]) . "=" . $tousu_u . "u\r\n";
                                $ems_all = $ems_all - $tousu_u;
                                $ems_all_str = $ems_all_str . "-" . $tousu_u . "u";

                                $tousu_str .= "-" . $tousu_u . "u";

                                $ems_all_end -= $tousu_u;

                                $message .= "<strong>🈴单商户合计:" . $ems_all_str . "=" . $ems_all . "u</strong>\n\r\n\r";
                            } else {

                                $message .= "<strong>🈴单商户合计:" . $ems_all_str . "=" . $ems_all . "u</strong>\n\r\n\r";
                            }


                        }
                    }

                    $ems_all_str_end = substr($ems_all_str_end, 0, -1);
                    $ems_all_str_end .= $tousu_str;
                    $message .= "<strong>🈴最终总合计:" . $ems_all_str_end . "=" . $ems_all_end . "u</strong>\n\r\n\r";

                } else {
                    //查询次商户号今日总收入信息：
                    $sql_info = "select sum(getmoney) as getmoney from pay_order where status = '1' and uid ='" . $uid . "' and date='" . $today . "'";

                    //查看最日的投诉金额：
                    $tousu_info = "select sum(money) as tousumoney from pay_usertousu where  pid ='" . $uid . "'";
                    $order_tousu = $this->pdo->query($tousu_info);
                    $tousu_m = $order_tousu->fetchAll();
                    $tousu_today = round($tousu_m[0]['tousumoney'], 2);


                    $order_query3 = $this->pdo->query($sql_info);
                    $chatinfo = $order_query3->fetchAll();
                    $order_today = round($chatinfo[0]['getmoney'], 2);


                    //进行结算处理：
                    /*$find_sql = "SELECT * from pay_user where uid = '".$uid."'";
                    $userinfo = $this->pdo->query($find_sql);
                    $allmoney=0;
                    $realmoney=$userinfo[0]['money'];
                    $row = $userinfo[0];
                    $date = date("Y-m-d H:i:s");

                    if($this->pdo->exec("INSERT INTO `pay_settle` (`uid`, `type`, `username`, `account`, `money`, `realmoney`, `addtime`, `status`) VALUES ('".$row['uid']."', '".$row['settle_id']."', '".$row['username']."', '".$row['account']."', '".$row['money']."', '".$realmoney."', '".$date."', '0')")){
                        $this->changeUserMoney($userinfo[0]['uid'], $userinfo[0]['money'], false, '自动结算');
                        $allmoney+=$realmoney;

                    }*/


                    $sql_info3 = "select username from pay_user where  uid ='" . $uid . "'";
                    $order_query7 = $this->pdo->query($sql_info3);
                    $chatinfo3 = $order_query7->fetchAll();
                    $uidinfo2 = $chatinfo3[0];

                    //ｕ＝2323＊0.8＊0.94／6.4=238u
                    $message = "<strong>⏰" . $todays . "结算:</strong>\n\r";
                    $message .= "<strong>🆔商户号:" . $uid . "</strong>\n\r";
                    $message .= "<strong>🧑🏻‍💼名字:" . $uidinfo2['username'] . "</strong>\n\r";
                    //$message .= "昨日收入：".$order_today."元\n\r";

                    $set_sql1 = "select typelist FROM pay_uset where uid='" . $uid . "'";
                    $order_query_user = $this->pdo->query($set_sql1);
                    $chatinfo_usertype = $order_query_user->fetchAll();


                    if ($order_today <= 0) {

                        $message .= "<strong>💰收入结算:0u</strong>";
                    } else {
                        $ems_all = "0";
                        $ems_all_str = "";
                        foreach ($chatinfo_usertype as $key2 => $value2) {
                            $sql_info2 = "select * from pay_uset where  uid ='" . $uid . "' and typelist='" . $value2['typelist'] . "'";
                            $order_query6 = $this->pdo->query($sql_info2);
                            $chatinfo2 = $order_query6->fetchAll();
                            $uidinfo = $chatinfo2[0];

                            $type = substr($uidinfo['four'], 0, 1);


                            $sql_info2 = "SELECT sum(getmoney) as getmoney FROM pay_order WHERE uid='" . $uid . "' AND type=(SELECT id FROM pay_type WHERE name='" . $value2['typelist'] . "') AND status=1 AND date='" . $today . "'";
                            $order_query4 = $this->pdo->query($sql_info2);
                            $chatinfo2 = $order_query4->fetchAll();
                            $order_today2 = round($chatinfo2[0]['getmoney'], 2);
                            if ($type == "-") {
                                $changs = explode("-", $uidinfo['four']);
                                $ems = intval($order_today2 * $uidinfo['one'] * $uidinfo['two'] / ($uidinfo['three'] - $changs[1]));
                                $sss = $uidinfo['three'] - $changs[1];
                                $message .= "<strong>💰" . $new_type[$value2['typelist']] . "结算:" . $order_today2 . "*" . $uidinfo['one'] . "*" . $uidinfo['two'] . "/" . $sss . "=" . $ems . "u" . "</strong>" . "\n\r";

                            } else {
                                $changs = explode("+", $uidinfo['four']);
                                $ems = intval($order_today2 * $uidinfo['one'] * $uidinfo['two'] / ($uidinfo['three'] + $changs[1]));
                                $sss = $uidinfo['three'] + $changs[1];
                                $message .= "<strong>💰" . $new_type[$value2['typelist']] . "结算:" . $order_today2 . "*" . $uidinfo['one'] . "*" . $uidinfo['two'] . "/" . $sss . "=" . $ems . "u</strong>" . "\n\r";
                            }
                            $ems_all += $ems;
                            $ems_all_str .= $ems . "u+";
                        }
                        $ems_all_str = substr($ems_all_str, 0, -1);

                        if ($tousu_today > 0) {
                            $tousu_u = round($tousu_today / ($uidinfo['three'] - $changs[1]), 2);
                            $message .= "❌投诉退款:" . $tousu_today . "元/" . ($uidinfo['three'] - $changs[1]) . "=" . $tousu_u . "u\r\n";
                            $ems_all = $ems_all - $tousu_u;
                            $ems_all_str = $ems_all_str . "-" . $tousu_u . "u";
                            $message .= "<strong>🈴合计:" . $ems_all_str . "=" . $ems_all . "u</strong>";
                        } else {

                            $message .= "<strong>🈴合计:" . $ems_all_str . "=" . $ems_all . "u</strong>";
                        }


                    }
                }

                $parameter = array(
                    'chat_id' => $chatid,
                    'parse_mode' => 'HTML',
                    'text' => $message

                );
                $this->http_post_data('sendMessage', json_encode($parameter));
            }
        }
        if (strpos($message, '/userxq') !== false) {
            //查询商户的具体信息：
            $uid_arr = explode("userxq", $message);
            if (strpos($uid_arr[1], '@')) {
                $uid_arr = explode("@", $uid_arr[1]);
                $user_id = $uid_arr[0];
            } else {
                $user_id = $uid_arr[1];
            }


            /*商户号： 1004
                余额 ： 6666
                姓名： 四面
                wxpay：1120,0.82,0.95,6.63,+0
                alipay：1120,0.82,0.95,6.63,+0
                qqpay：1120,0.82,0.95,6.63,+0
                bank：1120,0.82,0.95,6.63,+0
                结算地址：Txxxx03
                按钮 ：修改汇率 (http://google.com/)*/


            $sql_info = "select a.id,a.one,a.two,a.three,a.four,a.five,a.typelist,a.uid,b.money,b.username,a.five from pay_uset as a left join pay_user as b on b.uid=a.uid where a.uid='" . $user_id . "'";
            $order_query2 = $this->pdo->query($sql_info);
            $chatinfo = $order_query2->fetchAll();
            $message = "";
            $message .= "商户号：" . $user_id . "\n\r";
            foreach ($chatinfo as $key => $value) {
                $message .= $value['typelist'] . "：" . $value['one'] . "," . $value['two'] . "," . $value['three'] . "," . $value['four'] . "\n\r";
            }

            $message .= "余额：" . $chatinfo[0]['money'] . "\n\r";
            $message .= "姓名：" . $chatinfo[0]['username'] . "\n\r";
            $message .= "结算地址：" . $chatinfo[0]['five'] . "\n\r";

            $inline_keyboard_arr2[0] = array('text' => "修改汇率信息", "callback_data" => "changeuser_" . $user_id);
            $keyboard = [
                'inline_keyboard' => [
                    $inline_keyboard_arr2
                ]
            ];
            $parameter = array(
                'chat_id' => $chatid,
                'parse_mode' => 'HTML',
                'text' => $message,
                'reply_markup' => $keyboard,

            );
            $this->http_post_data('sendMessage', json_encode($parameter));
            exit();
        }
        if (strpos($message, 'yijingxiafa_') !== false) {
            $quanxian = "下发昨日结算收益";
            $this->quanxian($chatid, $from_id, $quanxian, $username);

            $uid_arr = explode("_", $message);
            $parameter = array(
                'chat_id' => $chatid,
                'parse_mode' => 'HTML',
                'text' => "当前商户号:" . $uid_arr . "的U币已经下发过了"

            );
            $this->http_post_data('sendMessage', json_encode($parameter));
        }



        if (strpos($message, '/finduserggg') !== false) {
            $uid_arr = explode("ggg", $message);
            $uid = $uid_arr['1'];
            $today = date("Y-m-d");

            //查询次商户号今日总收入信息：
            $sql_info = "select sum(getmoney) as getmoney from pay_order where status = '1' and uid ='" . $uid . "' and date='" . $today . "'";

            $order_query2 = $this->pdo->query($sql_info);
            $chatinfo = $order_query2->fetchAll();
            $order_today = round($chatinfo[0]['getmoney'], 2);

            $set_sql1 = "select typelist FROM pay_uset where uid='" . $uid . "'";
            $order_query_user = $this->pdo->query($set_sql1);
            $chatinfo_usertype = $order_query_user->fetchAll();
            $message = "商户：" . $uid . "\n\r";
            $message .= "今日总收入：" . $order_today . "元\n\r";
            foreach ($chatinfo_usertype as $key2 => $value2) {
                $sql_info2 = "SELECT sum(getmoney) as getmoney FROM pay_order WHERE uid='" . $uid . "' AND type=(SELECT id FROM pay_type WHERE name='" . $value2['typelist'] . "') AND status=1 AND date='" . $today . "'";
                $order_query3 = $this->pdo->query($sql_info2);
                $chatinfo2 = $order_query3->fetchAll();
                $order_today_alipay = round($chatinfo2['0']['getmoney'], 2);
                $message .= "今日" . $new_type[$value2['typelist']] . "：" . $order_today_alipay . "元\n\r";
            }


            $parameter = array(
                'chat_id' => $chatid,
                'parse_mode' => 'HTML',
                'text' => $message

            );
            $this->http_post_data('sendMessage', json_encode($parameter));


        }
        if (strpos($message, '/td') !== false) {
            $quanxian = "td";
            $this->quanxian($chatid, $from_id, $quanxian, $username);
            //$this->chaojiyonghuquanxian($from_id, $chatid);

            $channel = [];
            $sql1 = "SELECT id,name,feilv FROM pay_channel WHERE status=1";

            $q = $this->pdo->query($sql1);
            $rs = $q->fetchAll();

            // $rs = $DB->getAll("SELECT id,name FROM pre_channel WHERE status=1");


            foreach ($rs as $row) {
                $channel[$row['id']] = $row['name'] . "--" . $row['feilv'];
            }

            unset($rs);
            $order_channel = array();
            foreach ($channel as $id => $type) {
                $order_channel[$id] = 0;
            }

            $today = date("Y-m-d");

            $rs = $this->pdo->query("SELECT type,channel,money from pay_order where status=1 and date>='$today'");
            $row = $rs->fetchAll();
            foreach ($row as $ks => $cvs) {
                $order_channel[$cvs['channel']] += $cvs['money'];
            }


            foreach ($order_channel as $k => $v) {
                $order_channel[$k] = round($v, 2);
            }

            $allmoney = 0;
            foreach ($order_channel as $order) {
                $allmoney += $order;
            }
            $order_today['all'] = round($allmoney, 2);
            $message = "";
            foreach ($order_channel as $key => $value3) {
                if ($value3 > 0) {
                    $message .= $channel[$key] . " : " . $value3 . "\n\r";

                }

            }
            $message .= "\n\r";
            $message .= "合计:" . $allmoney;

            $parameter = array(
                'chat_id' => $chatid,
                'parse_mode' => 'HTML',
                'text' => $message

            );
            $this->http_post_data('sendMessage', json_encode($parameter));
            exit();
        }
        if (strpos($message, '/zt') !== false) {
            $this->chaojiyonghuquanxian($from_id, $chatid);

            $channel = [];
            $sql1 = "SELECT id,name,feilv FROM pay_channel WHERE status=1";

            $q = $this->pdo->query($sql1);
            $rs = $q->fetchAll();

            // $rs = $DB->getAll("SELECT id,name FROM pre_channel WHERE status=1");


            foreach ($rs as $row) {
                $channel[$row['id']] = $row['name'] . "--" . $row['feilv'];
            }

            unset($rs);
            $order_channel = array();
            foreach ($channel as $id => $type) {
                $order_channel[$id] = 0;
            }

            $today = date("Y-m-d", strtotime("-1 day"));

            $rs = $this->pdo->query("SELECT type,channel,money from pay_order where status=1 and date='$today'");
            $row = $rs->fetchAll();
            foreach ($row as $ks => $cvs) {
                $order_channel[$cvs['channel']] += $cvs['money'];
            }


            foreach ($order_channel as $k => $v) {
                $order_channel[$k] = round($v, 2);
            }

            $allmoney = 0;
            foreach ($order_channel as $order) {
                $allmoney += $order;
            }
            $order_today['all'] = round($allmoney, 2);
            $message = "";
            foreach ($order_channel as $key => $value3) {
                if ($value3 > 0) {
                    $message .= $channel[$key] . " : " . $value3 . "\n\r";

                }

            }
            $message .= "\n\r";
            $message .= "昨日合计:" . $allmoney;

            $parameter = array(
                'chat_id' => $chatid,
                'parse_mode' => 'HTML',
                'text' => $message

            );
            $this->http_post_data('sendMessage', json_encode($parameter));
            exit();
        }
        if (strpos($message, '/wwwzppptlll') !== false) {
            $channel = [];
            $sql1 = "SELECT id,name,feilv FROM pay_channel WHERE status=1";

            $q = $this->pdo->query($sql1);
            $rs = $q->fetchAll();

            // $rs = $DB->getAll("SELECT id,name FROM pre_channel WHERE status=1");


            foreach ($rs as $row) {
                $channel[$row['id']] = $row['name'] . "--" . $row['feilv'];
            }

            unset($rs);
            $order_channel = array();
            foreach ($channel as $id => $type) {
                $order_channel[$id] = 0;
            }

            $today = date("Y-m-d", strtotime("-1 day"));

            $rs = $this->pdo->query("SELECT type,channel,money from pay_order where status=1 and date='$today'");
            $row = $rs->fetchAll();
            foreach ($row as $ks => $cvs) {
                $order_channel[$cvs['channel']] += $cvs['money'];
            }


            foreach ($order_channel as $k => $v) {
                $order_channel[$k] = round($v, 2);
            }

            $allmoney = 0;
            foreach ($order_channel as $order) {
                $allmoney += $order;
            }
            $order_today['all'] = round($allmoney, 2);
            $message = "";
            foreach ($order_channel as $key => $value3) {
                if ($value3 > 0) {
                    $message .= $channel[$key] . " : " . $value3 . "\n\r";

                }

            }
            $message .= "\n\r";
            $message .= "昨日合计:" . $allmoney;

            $parameter = array(
                'chat_id' => $chatid,
                'parse_mode' => 'HTML',
                'text' => $message

            );
            $this->http_post_data('sendMessage', json_encode($parameter));
            exit();
        }
        if (strpos($message, '/krate') !== false) {
            //单独查询某个商户的成率
            //$this->chaojiyonghuquanxian($from_id, $chatid);
            $quanxian = "krate";
            $this->quanxian($chatid, $from_id, $quanxian, $username);

            $rate = explode("te", $message);
            if (count($rate) <= 1) {
                $parameter = array(
                    'chat_id' => $chatid,
                    'parse_mode' => 'HTML',
                    'text' => "输入格式错误：/krate时间-商户号"
                );
                $this->http_post_data('sendMessage', json_encode($parameter));
                exit();
            }
            /*if (strpos($rate[1], '#') !== false) {


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
                $new_rate = explode("-", $rate[1]);
                $one_time = trim($new_rate[0]);
                $two_time = trim($new_rate[1]);
                //06-25 20:22#06-25 21:22
                $now_time = date('Y-m-d') . " " . $one_time . ":00:00";
                $end_time = date('Y-m-d') . " " . $two_time . ":00:00";
                $find_sql = "SELECT type,channel,money,status from pay_order where  addtime between '" . $now_time . "' and '" . $end_time . "'";
            } else {*/
            $new_rate = explode("-", $rate[1]);
            $one_time = trim($new_rate[0]);
            $two_time = trim($new_rate[1]);

            $rs = $this->pdo->query("SELECT * from pay_user where uid='$two_time'");
            $rosw = $rs->fetchAll();
            if (!$rosw) {
                $parameter = array(
                    'chat_id' => $chatid,
                    'parse_mode' => 'HTML',
                    'text' => "你输入的用户PID格式有错误！请核对！"

                );
                $this->http_post_data('sendMessage', json_encode($parameter));
                exit();
            }

            $pp = "🎈商户" . $two_time . "," . $one_time . "分钟在跑通道成率如下:";
            $now_time = date("Y-m-d H:i:s", time() - $one_time * 60);
            $end_time = date("Y-m-d H:i:s", time());
            $find_sql = "SELECT type,channel,money,status from pay_order where  addtime between '" . $now_time . "' and '" . $end_time . "' and uid='" . $two_time . "'";
            /*}*/


            $channel = [];
            $sql1 = "SELECT id,name FROM pay_channel WHERE status=1";

            $q = $this->pdo->query($find_sql);
            $rs = $q->fetchAll();

            foreach ($rs as $row) {
                // $channel[$row['id']] = $row['name'];
                $sql1 = "SELECT id,name FROM pay_channel WHERE status=1 and id='" . $row['channel'] . "'";
                $q2 = $this->pdo->query($sql1);
                $rs2 = $q2->fetchAll();

                $channel[$rs2[0]['id']] = $rs2[0]['name'];
            }

            unset($rs);
            $order_channel_fukuan = array(); //付款
            $order_channel_all = array();//所有
            foreach ($channel as $id => $type) {
                $order_channel_fukuan[$id] = 0;
                $order_channel_all[$id] = 0;
            }


            $rs = $this->pdo->query($find_sql);
            $row = $rs->fetchAll();
            foreach ($row as $ks => $cvs) {

                $order_channel_all[$cvs['channel']] += 1;


                if ($cvs['status'] == "1") {
                    $order_channel_fukuan[$cvs['channel']] += 1;
                }
            }


            $order_channel = array();
            foreach ($order_channel_all as $k => $sv) {


                //$order_channel[$k] = round(($order_channel_fukuan[$k] / $sv) * 100, 2);
                if ($order_channel_fukuan[$k] > 0) {
                    $order_channel[$k] = round(($order_channel_fukuan[$k] / $sv) * 100, 2);
                } else {
                    $order_channel[$k] = 0;
                }
            }

            //$this->xiaoxi(json_encode($order_channel),$chatid);
            $message = "";
            $message .= $pp . "\n\r\n\r";
            foreach ($order_channel as $key => $value3) {
                //if ($value3 > 0) {

                $sql2 = "SELECT feilv FROM pay_channel WHERE id='" . $key . "'";
                $q2 = $this->pdo->query($sql2);
                $rss = $q2->fetchAll();
                if ($value3 > 0) {
                    $chengl = $order_channel_fukuan[$key];
                } else {
                    $chengl = 0;
                }

                //$message .= "✅" . $channel[$key] . " : \n\r" . "💰成率：" . $value3 . "%\n\r\n\r";
                $message .= "✅" . $channel[$key] . "--" . $rss[0]['feilv'] . " : \n\r" . "💰成率：" . $value3 . "%【" . $chengl . "/" . $order_channel_all[$key] . "】\n\r";
                $message .= "🅿️详情：" . "/shrate" . $key . "_" . $one_time . "_" . $two_time . "\n\r\r\n";

                //}

            }


            $parameter = array(
                'chat_id' => $chatid,
                'parse_mode' => 'HTML',
                'text' => $message

            );
            $this->http_post_data('sendMessage', json_encode($parameter));
            exit();
        }
        if (strpos($message, '/rate') !== false) {

           // $this->xiaoxinoend("我进来了",$chatid);
            //$this->chaojiyonghuquanxian($from_id, $chatid);
            $quanxian = "rate";
           // $this->quanxian($chatid, $from_id, $quanxian, $username);
            // $this->quanxian($chatid, $userid, $quanxian, $username);
            //  $parameter = array(
            //          'chat_id' => $chatid,
            //         'parse_mode' => 'HTML',
            //          'text'=>"2121"
            //  );
            //      $this->http_post_data('sendMessage', json_encode($parameter));
            //      exit();
            $now_time = date("Y-m-d H:i:s", time() - 3 * 60 * 60);
            $end_time = date("Y-m-d H:i:s", time());
            //$find_sql = "SELECT type,channel,money,status from pay_order where  addtime between '" . $now_time . "' and '" . $end_time . "' group by channel";
            $find_sql = "SELECT channel from pay_order where  addtime between '" . $now_time . "' and '" . $end_time . "' group by channel";

            $qss = $this->pdo->query($find_sql);
            $rsss = $qss->fetchAll();
            if (!$rsss) {
                $parameter = array(
                    'chat_id' => $chatid,
                    'parse_mode' => 'HTML',
                    'text' => "未查询到此时间区间的订单数据信息！"
                );
                $this->http_post_data('sendMessage', json_encode($parameter));
                exit();
            }

            $find_channel = array();
            foreach ($rsss as $row) {
                $find_channel[] = $row['channel'];
            }
            $str_channel = implode(",", $find_channel);


            $channel = [];
            $sql1 = "SELECT id,name FROM pay_channel WHERE id in (" . $str_channel . ")";
            $q = $this->pdo->query($sql1);
            $rs = $q->fetchAll();

            foreach ($rs as $row) {
                $channel[$row['id']] = $row['name'];
            }


            ///rate_30  30分钟在跑通道商户成率如下:
            ///rate_60  60分钟在跑通道商户成率如下:
            ///rate_06-25 20:22#06-25 21:22 就是查询这个时间段的通道成率

            //查询可以使用这个命令的群：
            /* if($chatid != "-1001406020780"){
               //已經綁定群了：
                $parameter = array(
                     'chat_id' => $chatid,
                    'parse_mode' => 'HTML',
                     'text'=>"该群暂未绑定查询通道成功命令"
                 );
                 $this->http_post_data('sendMessage', json_encode($parameter));
                 exit();
             }*/
            $rate = explode("te", $message);
            if (count($rate) <= 1) {
                $parameter = array(
                    'chat_id' => $chatid,
                    'parse_mode' => 'HTML',
                    'text' => "输入格式错误：/rate时间"
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
                $new_rate = explode("-", $rate[1]);
                $one_time = trim($new_rate[0]);
                $two_time = trim($new_rate[1]);
                //06-25 20:22#06-25 21:22
                $now_time = date('Y-m-d') . " " . $one_time . ":00:00";
                $end_time = date('Y-m-d') . " " . $two_time . ":00:00";
                $find_sql = "SELECT type,channel,money,status from pay_order where  addtime between '" . $now_time . "' and '" . $end_time . "'";
            } else {


                $pp = "🎈" . $rate[1] . "分钟在跑通道成率如下:";
                $now_time = date("Y-m-d H:i:s", time() - $rate[1] * 60);
                $end_time = date("Y-m-d H:i:s", time());
                $find_sql = "SELECT type,channel,money,status from pay_order where  addtime between '" . $now_time . "' and '" . $end_time . "'";
            }


            $channel = [];
            //$sql1 = "SELECT id,name FROM pay_channel WHERE status=1";

            $q = $this->pdo->query($find_sql);
            $rs = $q->fetchAll();
            //{"422":"\u5f90\u5bb6\u6c47\u539f\u751f\u652f\u4ed8\u5b9d","352":"\u5c0f\u9e4f\u5fae\u4fe1\u539f\u751f","426":"\u4e9a\u6d32\u9f99UID","383":"\u8fde\u4e91\u6e2f\u5fae\u4fe1\u8bdd\u8d39","":null}
            foreach ($rs as $row) {
                $sql1 = "SELECT id,name FROM pay_channel WHERE status=1 and id='" . $row['channel'] . "'";

                $q2 = $this->pdo->query($sql1);
                $rs2 = $q2->fetchAll();
                if ($rs2) {
                    $channel[$rs2[0]['id']] = $rs2[0]['name'];
                }

            }


            unset($rs);
            $order_channel_fukuan = array(); //付款
            $order_channel_all = array();//所有
            foreach ($channel as $id => $type) {
                $order_channel_fukuan[$id] = 0;
                $order_channel_all[$id] = 0;
            }


            $rs = $this->pdo->query($find_sql);
            $row = $rs->fetchAll();
            foreach ($row as $ks => $cvs) {

                if ($cvs['channel'] > 0) {
                    $order_channel_all[$cvs['channel']] += 1;


                    if ($cvs['status'] == "1") {
                        $order_channel_fukuan[$cvs['channel']] += 1;
                    }
                }

            }


            $order_channel = array();


            foreach ($order_channel_all as $k => $sv) {
                if ($order_channel_fukuan[$k] > 0) {
                    $order_channel[$k] = round(($order_channel_fukuan[$k] / $sv) * 100, 2);
                } else {
                    $order_channel[$k] = 0;
                }


            }


            $message = "";
            $message .= $pp . "\n\r\n\r";
            foreach ($order_channel as $key => $value3) {
                //if ($value3 > 0) {

                $sql2 = "SELECT feilv FROM pay_channel WHERE id='" . $key . "'";
                $q2 = $this->pdo->query($sql2);
                $rss = $q2->fetchAll();
                //$message .= "✅" . $channel[$key] . " : \n\r" . "💰成率：" . $value3 . "%\n\r\n\r";
                if ($value3 > 0) {
                    $chengl = $order_channel_fukuan[$key];
                } else {
                    $chengl = 0;
                }

                $message .= "✅" . $channel[$key] . "--" . $rss[0]['feilv'] . " : \n\r" . "💰成率：" . $value3 . "%【" . $chengl . "/" . $order_channel_all[$key] . "】";
                $message .= "\r\n🅿️详情：" . "/cdrate" . $key . "_" . $rate[1] . "\r\n\r\n";


                //}

            }


            $parameter = array(
                'chat_id' => $chatid,
                'parse_mode' => 'HTML',
                'text' => $message

            );
            $this->http_post_data('sendMessage', json_encode($parameter));
            exit();
        }
        if (strpos($message, '/alluserrate') !== false) {

            ///rate_30  30分钟在跑通道商户成率如下:
            ///rate_60  60分钟在跑通道商户成率如下:
            ///rate_06-25 20:22#06-25 21:22 就是查询这个时间段的通道成率


            if ($chatid != "-1001406020780") {
                //已經綁定群了：
                $parameter = array(
                    'chat_id' => $chatid,
                    'parse_mode' => 'HTML',
                    'text' => "该群暂未绑定查询通道成功命令"
                );
                $this->http_post_data('sendMessage', json_encode($parameter));
                exit();
            }
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
//			$parameter = array(
//                    'chat_id' => $chatid,
//                    'parse_mode' => 'HTML',
//                    'text'=>$find_sql
//                );
//                $this->http_post_data('sendMessage', json_encode($parameter));

            /*if($rate[1] =="30"){
                $pp= "🎈30分钟在跑通道成率如下:";

                $now_time = date("Y-m-d H:i:s",time()-1800);
                $end_time = date("Y-m-d H:i:s",time());
                $find_sql = "SELECT type,channel,money,status,uid from pay_order where  addtime between '".$now_time ."' and '". $end_time."'";
            }elseif($rate[1] =="60"){
                $pp= "🎈60分钟在跑通道成率如下:";
                $now_time = date("Y-m-d H:i:s",time()-3600);
                $end_time = date("Y-m-d H:i:s",time());
                $find_sql = "SELECT type,channel,money,status,uid from pay_order where  addtime between '".$now_time ."' and '". $end_time."'";
            }else{
                $pp= "🎈".$rate[1]."在跑通道成率如下:";
                $new_rate = explode("#",$rate[1]);
                //06-25 20:22#06-25 21:22
                $now_time = date('Y')."-".$new_rate[0].":00";
                $end_time =date('Y')."-".$new_rate[1].":00";
                $find_sql = "SELECT type,channel,money,status,uid from pay_order where  addtime between '".$now_time ."' and '". $end_time."'";
            }*/


            $channel = [];
            //$sql1 = "SELECT id,name FROM pay_channel WHERE status=1";

            $q = $this->pdo->query($find_sql);
            $rs = $q->fetchAll();

            foreach ($rs as $row) {
                $sql1 = "SELECT id,name FROM pay_channel WHERE status=1 and id='" . $row['channel'] . "'";
                $q2 = $this->pdo->query($sql1);
                $rs2 = $q2->fetchAll();

                $channel[$rs2[0]['id']] = $rs2[0]['name'];
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
                //$order_channel[$k] = round(($order_channel_fukuan[$k] / $sv) * 100, 2);
                if ($order_channel_fukuan[$k] > 0) {
                    $order_channel[$k] = round(($order_channel_fukuan[$k] / $sv) * 100, 2);
                } else {
                    $order_channel[$k] = 0;
                }
            }


            $message = "";
            $message .= $pp . "\n\r\n\r";


            foreach ($order_channel as $key => $value3) {
                //if ($value3 > 0) {
                // if ($value3 > 0) {
                //     $chengl = $order_channel_fukuan[$key];
                // }else{
                //     $chengl = 0;
                // }

                $message .= "✅" . $channel[$key] . " : \n\r" . "💰成率：" . $value3 . "%\n\r\n\r";
                $sqw = 1;
                foreach ($order_channel_all_user[$key] as $ke => $sq) {
                    $ssqsa = round(($order_channel_all_user_fukuan[$key][$ke] / $sq) * 100, 2);


                    $message .= $sqw . ".🧑‍💻" . $user_g[$ke] . "-" . $ke . "-成率：" . $ssqsa . "\n\r\n\r";
                    $sqw++;
                }
                //}

            }


            $parameter = array(
                'chat_id' => $chatid,
                'parse_mode' => 'HTML',
                'text' => $message

            );
            $this->http_post_data('sendMessage', json_encode($parameter));
            exit();
        }
        if (strpos($message, '/setwarn') !== false) {
            //setwarn&192%15-30-40-50#

            if ($chatid != "-1001406020780") {
                //已經綁定群了：
                $parameter = array(
                    'chat_id' => $chatid,
                    'parse_mode' => 'HTML',
                    'text' => "该群暂未绑定查询通道成功命令"
                );
                $this->http_post_data('sendMessage', json_encode($parameter));
                exit();
            }
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
                    foreach ($order_channel_all_user[$key] as $ke => $sq) {
                        $ssqsa = round(($order_channel_all_user_fukuan[$key][$ke] / $sq) * 100, 2);


                        $message .= $sqw . ".🧑‍💻" . $user_g[$ke] . "-" . $ke . "-成率：" . $ssqsa . "\n\r\n\r";
                        $sqw++;
                    }
                }

            }


            $parameter = array(
                'chat_id' => $chatid,
                'parse_mode' => 'HTML',
                'text' => $message

            );
            $this->http_post_data('sendMessage', json_encode($parameter));
            exit();
        }


    }

    // 隐藏部分字符串
    function func_substr_replace($str, $replacement = '*', $start = 1, $length = 3)
    {
        $len = mb_strlen($str, 'utf-8');
        $length = $len - $length - $start;
        if ($len > intval($start + $length)) {
            $str1 = mb_substr($str, 0, $start, 'utf-8');
            $str2 = mb_substr($str, intval($start + $length), NULL, 'utf-8');
        } else {
            $str1 = mb_substr($str, 0, 1, 'utf-8');
            $str2 = mb_substr($str, $len - 1, 1, 'utf-8');
            $length = $len - 2;
        }
        $new_str = $str1;
        for ($i = 0; $i < $length; $i++) {
            $new_str .= $replacement;
        }
        $new_str .= $str2;

        return $new_str;
    }

    function changeUserMoney($uid, $money, $add = true, $type = null, $orderid = null)
    {


        $oldmoney = $this->pdo->query("SELECT money FROM pay_user WHERE uid='" . $uid . "' LIMIT 1");
        $oldmoney = $oldmoney[0]['money'];
        if ($add == true) {
            $action = 1;
            $newmoney = round($oldmoney + $money, 2);
        } else {
            $action = 2;
            $newmoney = round($oldmoney - $money, 2);
        }
        $res = $this->pdo->exec("UPDATE pay_user SET money='" . $newmoney . "' WHERE uid='" . $uid . "'");


        $this->pdo->exec("INSERT INTO `pay_record` (`uid`, `action`, `money`, `oldmoney`, `newmoney`, `type`, `trade_no`, `date`) VALUES ('" . $uid . "', '" . $action . "', '" . $money . "', '" . $oldmoney . "', '" . $newmoney . "', '" . $type . "', '" . $orderid . "', '0')");
        return $res;
    }

    function xiafazuoriuid($conetnt, $chatid)
    {

        $sql_info = "select * from pay_botsettle where chatid ='" . $chatid . "'";

        $order_query2 = $this->pdo->query($sql_info);
        $chatinfo = $order_query2->fetchAll();

        if (!$chatinfo) {
            $this->xiaoxi("该群暂未绑定商户号，请输入快捷命令：/bd商户号", $chatid);
        }
        $uid = $chatinfo['0']['merchant'];
        $uid_end = $uid;


        if ($this->kaiqi_teshu_xiafa) {
            $nayitian = $this->teshu_riqi;
            $today = date("Y-m-d", strtotime(date($nayitian)));
            $todays = date("Y年m月d日", strtotime(date($nayitian)));
            $todays2 = date("m月d日", strtotime(date($nayitian)));
        } else {
            $today = date("Y-m-d", strtotime("-1 day"));
            $todays = date("Y年m月d日", strtotime("-1 day"));
            $todays2 = date("m月d日", strtotime("-1 day"));
        }


        $uid_arr = explode("|", $uid);

        $huilvinfo = $this->huilvinfo("99999", "99999");
        $fufonginfo = $this->fudonginfo($uid, $chatid);
        $fenchenginfo = $this->fenchenginfo($uid, $chatid);

        $tongdaoxinxi = $this->tongdaoxinxi($uid, $chatid);
        $zhifuxinxi = $this->zhifuxinxi($uid, $chatid);

        $sql_zhifu = "select id,showname from pay_type";

        $zhifu_fetch = $this->shujuku($sql_zhifu);
        $zhifu_info_arr = array();
        foreach ($zhifu_fetch as $kp => $vp) {
            $zhifu_info_arr[$vp['id']] = $vp['showname'];
        }

        if (count($zhifuxinxi) <= 0) {
            $this->xiaoxi("当前商户暂未设置支付类型费率，请先设置！", $chatid);
        }

        //这里去请求设置汇率：$huilv_api
        $now_time = strtotime(date("Y-m-d"));
        //查询是不是请求过了:
        $huilv_info = "select * from pay_huoquhuilv where  huoqutime='" . $now_time . "' order by id desc";
        $hui_query = $this->pdo->query($huilv_info);
        $huilvinfop = $hui_query->fetchAll();
        if ($huilvinfop) {
            //如果存在，就看看时间：
            $nexttimes = $huilvinfop[0]['nexttime'];
            if (time() > $nexttimes) {
                $this->ouyi(0, $huilvinfop[0]['id']);
            }
        } else {
            $this->ouyi(1);

        }

        $all_zhifu = array();  //纯支付方式的量
        $all_tongdao = array(); //纯设置通道的量
        $all_tongdao_zhifu = array();  //支付方式下的各个通道跑的数据

        $sql_info3 = "select username,usdt_str from pay_user where  uid ='" . $uid . "'";
        $order_query7 = $this->pdo->query($sql_info3);
        $chatinfo3 = $order_query7->fetchAll();
        $uidinfo2 = $chatinfo3[0];


        if (count($uid_arr) > 1) {

            $parameter = array(
                'chat_id' => $chatid,
                'parse_mode' => 'HTML',
                'text' => "</b>当前群存在多个商户号,请先解绑，将商户分群后再操作！</b>",
            );
            $this->http_post_data('sendMessage', json_encode($parameter));
            exit();

            foreach ($uid_arr as $k => $v) {
                $inline_keyboard_arr[$k] = array('text' => "下发商户:" . $v, "callback_data" => "结算下发商户_" . $v);
            }

            $keyboard = [
                'inline_keyboard' => [
                    $inline_keyboard_arr
                ]
            ];
            $parameter = array(
                'chat_id' => $chatid,
                'parse_mode' => 'HTML',
                'text' => "请选择要下发昨日收益结算的商户",
                'reply_markup' => $keyboard,

            );

            $this->http_post_data('sendMessage', json_encode($parameter));
            exit();

        } else {
            //查询次商户号昨日总收入信息：
            $sql_info = "select sum(getmoney) as getmoney from pay_order where status = '1' and uid ='" . $uid . "' and date='" . $today . "'";

            $order_query3 = $this->pdo->query($sql_info);
            $chatinfo = $order_query3->fetchAll();
            $order_today = round($chatinfo[0]['getmoney'], 2);
            if ($order_today <= 0) {

                $message .= "<strong>💰收入结算:0u</strong>";
                $parameter = array(
                    'chat_id' => $chatid,
                    'parse_mode' => 'HTML',
                    'text' => $message,
                );


                $this->http_post_data('sendMessage', json_encode($parameter));
                exit();
            }


            //查看昨日总下发的记录 这里有一点需要注意，如果昨日存在有下发异常的 需要天使自己核对 手动下发：
            $zuori_sql = "select * from pay_jinrixiafa where status = '0' and pid ='" . $uid . "' and xiafatime='" . $today . "'";

            $zuorixiafa = $this->shujuku($zuori_sql);
            if ($zuorixiafa) {
                $parameter = array(
                    'chat_id' => $chatid,
                    'parse_mode' => 'HTML',
                    'text' => "当前商户昨日存在实时下发" . $zuorixiafa[0]['money'] . "U异常！建议手动结算昨日收益！",
                );
                $this->http_post_data('sendMessage', json_encode($parameter));
                exit();

            }

            //最日下发的数据
            $zuori_money = 0.00;
            $zuori_usdt = 0.00;

            //昨日收益数据分析：
            $sql_info = "select * from pay_order where status = '1' and uid ='" . $uid . "' and date='" . $today . "'";
            $order_query3 = $this->pdo->query($sql_info);
            $zuoorderinfo = $order_query3->fetchAll();

            $all_money = 0;
            foreach ($zuoorderinfo as $key => $value) {
                $all_money += $value['money'];
                //支付方式计算
                $all_zhifu[$value['type']] += $value['money'];

                //支付方式下的各个通道跑的数据：
                $all_tongdao_zhifu[$value['type']][$value['channel']] += $value['money'];
                if (array_key_exists($value['channel'], $tongdaoxinxi)) {
                    //通道费用计算
                    $all_tongdao[$value['channel']] += $value['money'];
                }
            }
            $msg = "✅" . $todays2 . "量情况如下\r\n🆔商户号:" . $uid . "\r\n🧑🏻‍💼名字:" . $uidinfo2['username'] . "\r\n";


            if (count($all_zhifu) > 0) {
                foreach ($all_zhifu as $kt => $vt) {
                    $sql_zhifu = "select showname from pay_type where  id ='" . $kt . "'";

                    $zhifu_fetch = $this->shujuku($sql_zhifu);

                    $zhifu_info = $zhifu_fetch[0]['showname'];
                    $msg .= "🔔" . $zhifu_info . "总量:" . $vt . "\r\n";
                }

            }


            $type = substr($fufonginfo, 0, 1);
            if ($type == "-") {
                $changs = explode("-", $fufonginfo);
                $shiji_huilv = $huilvinfo - $changs[1];
            } else {
                $changs = explode("+", $fufonginfo);
                $shiji_huilv = $huilvinfo + $changs[1];
            }

            $shiji_huilv_tousu = $shiji_huilv - 0.1;


            $all_usdt_m = 0;
            $all_fusdt_money = 0;
            $xiafa_str = "";
            $feilihoujiner = 0;
            foreach ($all_tongdao_zhifu as $kv => $vv) {
                //$zhifu_info_arr[$kv]
                //$msg .= "\r\n📮" . $zhifu_info_arr[$kv] . "跑量如下：\r\n\r\n";
                foreach ($vv as $kp => $vp) {
                    $channel_sql = "select id,name from pay_channel where id='" . $kp . "'";
                    $channel_info_query = $this->shujuku($channel_sql);
                    $channel_info = $channel_info_query[0];
                    // $msg .= "(" . $channel_info['id'] . ")" . $channel_info['name'] . ":" . $vp . "\r\n";
                    if (array_key_exists($kp, $tongdaoxinxi)) {

                        $zhifu_lixi = $tongdaoxinxi[$kp];

                    } else {
                        $zhifu_lixi = $zhifuxinxi[$kv];

                    }
                    $type = substr($fufonginfo, 0, 1);

                    $jisuan = round(($vp * $zhifu_lixi * $fenchenginfo) / ($shiji_huilv), 2);
                    //$msg .= $vp . "*" . $zhifu_lixi . "*" . $fenchenginfo . "/(" . $shiji_huilv . ")=" . $jisuan . "U\r\n\r\n";

                    $xiafa_str .= $jisuan . "+";

                    $feilihoujiner += round(($vp * $zhifu_lixi * $fenchenginfo), 2);

                    $all_usdt_m += $jisuan;
                    $all_fusdt_money += $vp;
                }
            }
            $msg .= "💹总跑量:" . $all_money . "元\r\n";
            $msg .= "💹费率后总额:" . $feilihoujiner . "元\r\n\r\n";
            $msg .= "➖➖➖➖➖➖➖➖➖\r\n\r\n";
            $msg .= "不可下发金额\r\n\r\n";

            $tousu_info2 = "select * from pay_usertousu where pid ='" . $uid . "'";

            $order_tousu2 = $this->pdo->query($tousu_info2);
            $tousu_m2 = $order_tousu2->fetchAll();
            $tousu_today = 0;
            $tousu_today2 = 0;
            $tousu_U = 0;
            $jinritimne = date("Y-m-d", time());
            foreach ($tousu_m2 as $k => $v) {
                $time = date('m-d', strtotime($v['date']));
                $tousu_today += $v['money'];

                if ($v['status'] == "1") {
                    //已扣除
                    $pp = "已扣除";
                    //如果是今天扣的，要计算体现到出来：
                    if ($jinritimne == $v['koushijian']) {
                        $tousu_today2 += $v['money'];
                        $tousu_U += $v['money'];
                    }
                } else {
                    //待扣除
                    $pp = "待扣除 ---- /delete_tousu_" . $v['id'];
                    $tousu_today2 += $v['money'];
                    $tousu_U += $v['money'];

                }


                $msg .= "❌" . $time . ":投诉退款:" . $v['money'] . "元  ----" . $pp . "\r\n";
            }


            //查看今日的投诉金额：
            /*$tousu_info = "select sum(money) as tousumoney from pay_usertousu where status='0' and  pid ='" . $uid . "' and date='" . $today . "'";
            $order_tousu = $this->pdo->query($tousu_info);
            $tousu_m = $order_tousu->fetchAll();

            $tousu_today = $tousu_m[0]['tousumoney']>0?$tousu_m[0]['tousumoney']:0;*/


            //查看投诉退款数据：
            if ($tousu_U > 0) {
                $tousu_U2 = $tousu_U;
                $msg .= "❌合计待投诉退款:" . $tousu_today2 . "元\r\n";
            } else {
                $tousu_U2 = 0;
            }

            $xiafa_str = substr($xiafa_str, 0, -1);

            $xiafa_str .= "-" . $tousu_U2;

            //查看今日下发数据记录：
            $jinri_info = "select money,jutishijian,feiu_money,feilv from pay_jinrixiafa where status='1' and pid ='" . $uid . "' and xiafatime='" . $today . "' and chatid='" . $chatid . "'";
            $order_jinri = $this->pdo->query($jinri_info);
            $tjinri_arr = $order_jinri->fetchAll();
            $all_jinri_xiafa = 0.00;


            if ($tjinri_arr) {

                $msg .= "\r\n📮" . $todays2 . "下发历史记录" . "\r\n";
                foreach ($tjinri_arr as $kj => $vj) {
                    $zuori_money += $vj['all_feiu_money'];
                    $zuori_usdt += $vj['money'];


                    $ti = date('H:i:s', $vj['jutishijian']);
                    $msg .= "🔈" . $ti . " 已下发：" . $vj['feiu_money'] . "/" . $vj['feilv'] . "/" . $vj['money'] . "\r\n";
                    $all_jinri_xiafa += $vj['feiu_money'];

                    $xiafa_str .= "-" . $vj['feiu_money'];
                }
            }
            $trx_info = "select * from pay_usertrx";
            $trx_jinri = $this->pdo->query($trx_info);
            $trx_arr = $trx_jinri->fetchAll();

            if ($trx_arr) {
                $trx_shouxufei = $trx_arr[0]['trx'];
            } else {
                $trx_shouxufei = 0.00;
            }

            $bukexiafaheji_zuoro = $all_jinri_xiafa + $tousu_today2;

            $msg .= "\r\n💹不可下发金额合计：" . $bukexiafaheji_zuoro . "元\r\n\r\n";
            $msg .= "➖➖➖➖➖➖➖➖➖\r\n";
            $msg .= "下发扣除费用\r\n\r\n";
            $msg .= "🔄Trx手续费=" . $trx_shouxufei . "U\r\n\r\n";
            $xiafa_str .= "-" . $trx_shouxufei;


            $keyixiafa_value = $feilihoujiner - $bukexiafaheji_zuoro;
            $keyixiafa_str = $feilihoujiner . " - " . $bukexiafaheji_zuoro . " = " . $keyixiafa_value;

            $msg .= "🈴当前可下发:" . $keyixiafa_str . "元";


            //实际下发：
            $shijixiafa_value = (floor((($keyixiafa_value / $shiji_huilv) * 100)) / 100) - $trx_shouxufei;
            $shijixiafa_str = $keyixiafa_value . "/" . $shiji_huilv . " - " . $trx_shouxufei . " = " . $shijixiafa_value;

            $msg .= "\r\n🈴实际下发:" . $shijixiafa_str . "U";

            $jie_all_jin_u = $all_jinri_xiafa > 0 ? $all_jinri_xiafa : 0;
            $jie_all_tou_u = $tousu_U2 > 0 ? round($tousu_U2, 2) : 0;
            $jie_all_usdt_m = round($all_usdt_m, 2);
            $keyixiafa = $jie_all_usdt_m - $jie_all_jin_u - $jie_all_tou_u - $trx_shouxufei;
            //$keyixiafa = $keyixiafa>0?round($keyixiafa,2):0;

            //$msg .= "\r\n" . $xiafa_str . "=" . $keyixiafa . "U";
            //$msg .= $shijixiafa_value . "U";
            $msg .= "\r\n✅下发地址:\r\n" . $uidinfo2['usdt_str'];


            //查询结算是否已经下发：
            $sql_info_u = "select * from pay_zuorixiafau where pid ='" . $uid . "' and xiafatime='" . $today . "' and status='1'";


            $order_query_user_u = $this->pdo->query($sql_info_u);
            $xiafa_i_u = $order_query_user_u->fetchAll();

            $xiafade_day = date("d");
            if ($xiafa_i_u) {
                $inline_keyboard_arr[0] = array('text' => "收益已清算", "callback_data" => "yijingxiafa_" . $uid);
            } else {
                $inline_keyboard_arr[0] = array('text' => "确定下发:" . $shijixiafa_value . "U", "callback_data" => "zuotianxiafa_user_" . $uid . "&&" . $shijixiafa_value . "!!!" . $xiafade_day);
            }
            $inline_keyboard_arr2[0] = array('text' => "查详细账单", "callback_data" => "chakanzuorixiangxi_" . $uid);


        }


        $keyboard = [
            'inline_keyboard' => [
                $inline_keyboard_arr,
                $inline_keyboard_arr2
            ]
        ];
        $parameter = array(
            'chat_id' => $chatid,
            'parse_mode' => 'HTML',
            'text' => $msg,
            'reply_markup' => $keyboard,

        );

        $this->http_post_data('sendMessage', json_encode($parameter));

    }

    public function tuisongxiaoxi($type, $chat_id)
    {
        //这里改变数据库表：
        $guangboinfo_quer = $this->pdo->query("SELECT types FROM pay_guangbozhuang LIMIT 1");
        $guangboinfo = $guangboinfo_quer->fetchAll();
        if ($guangboinfo) {
            $res = $this->pdo->exec("UPDATE pay_guangbozhuang SET types='0' ");
        } else {
            $this->pdo->exec("INSERT INTO `pay_guangbozhuang` (`types`) VALUES ('0')");
        }
        if ($type == "5") {
            $messages = "下发昨日收益";
            $switch_inline_query_current_msg = "#guanpgbopqz_type_" . $type . "_#\r\n推送内容:\r\n下发昨日收益";
        }else if ($type == "6") {
            $messages = "自动下发昨日收益";
            $switch_inline_query_current_msg = "#guanpgbopqz_type_" . $type . "_#\r\n推送内容:\r\n自动下发昨日收益";
        } else {
            $messages = "请输入你要推送的内容,格式如下：\r\n推送内容:今天收入怎么样,有什么好建议\r\n";
            $switch_inline_query_current_msg = "#guanpgbopqz_type_" . $type . "_#\r\n推送内容:\r\n今天收入怎么样?有什么好建议!";
        }


        $inline_keyboard_arr3[0] = array('text' => "马上添加一个试试 ", "switch_inline_query_current_chat" => $switch_inline_query_current_msg);
        $keyboard = [
            'inline_keyboard' => [
                $inline_keyboard_arr3,
            ]
        ];
        $parameter = array(
            'chat_id' => $chat_id,
            'parse_mode' => 'HTML',
            'text' => $messages,
            'reply_markup' => $keyboard,
            'disable_web_page_preview' => true,

        );

        $this->http_post_data('sendMessage', json_encode($parameter));
        exit();

    }

    public function huilvinfo($pid, $chatid)
    {
        //查看通道费率信息：
        $sql = "select * from pay_userfeilv where typelist='4' and pid='" . $pid . "' and chatid='" . $chatid . "'";

        $sql_info = $this->shujuku($sql);

        if ($sql_info) {
            $feilvfudong = $sql_info[0]['feilv'];
        } else {
            $feilvfudong = "7";
        }
        return $feilvfudong;

    }

    public function fudonginfo($pid, $chatid)
    {
        //查看通道费率信息：
        $sql = "select * from pay_userfeilv where typelist='3' and pid='" . $pid . "' and chatid='" . $chatid . "'";
        $sql_info = $this->shujuku($sql);


        if ($sql_info) {
            $fudong = $sql_info[0]['feilv'];
        } else {
            $fudong = "+0";
        }

        return $fudong;

    }

    //分成：
    public function fenchenginfo($pid, $chatid)
    {
        //查看通道费率信息：
        $sql = "select * from pay_userfeilv where typelist='5' and pid='" . $pid . "' and chatid='" . $chatid . "'";
        $sql_info = $this->shujuku($sql);
        $feilvfudong = $sql_info[0]['feilv'];
        if ($sql_info) {
            $fencheng = $sql_info[0]['feilv'];
        } else {
            $fencheng = "1";
        }
        return $fencheng;

    }

    public function tongdaoxinxi($uid, $chatid)
    {
        //查看通道费率信息：
        $sql = "select * from pay_userfeilv where typelist='2' and pid = '" . $uid . "'";
        $sql_info = $this->shujuku($sql);


        $tongdao = array();
        foreach ($sql_info as $key => $value) {
            $tongdao[$value['type']] = $value['feilv'];
        }

        return $tongdao;

    }

    public function zhifuxinxi($uid, $chatid)
    {
        //查看支付信息
        $sql = "select * from pay_userfeilv where typelist='1' and pid = ".$uid;
        $sql_info = $this->shujuku($sql);
        if (!$sql_info) {

            $this->xiaoxi("商户".$uid."暂未设置支付费率信息,请先设置！"."-->".$sql, $chatid);
        }

        $zhifu = array();
        $msg = "";
        foreach ($sql_info as $key => $value) {

            if ($value['feilv'] <= 0) {
                //查询支付信息：
                $zhifu_list = $this->shujuku("select showname from pay_type where id='" . $value['type'] . "'");
                $zhifu_name = $zhifu_list[0]['showname'];
                $msg .= "商户" . $zhifu_name . "未设置费率信息,请先设置！\r\n";

            }
            $zhifu[$value['type']] = $value['feilv'];


        }
        if (!empty($msg)) {
            $this->xiaoxi($msg, $chatid);
        }

        //$this->xiaoxi(json_encode($zhifu),$chatid);
        return $zhifu;
    }

    public function callback_jiesuan($text, $chat_id, $from_id, $type = '1')
    {
        $type_arr = explode("###", $text);
        $typelist = $type_arr[1];
        if ($typelist == "1") {
            $today = date('Y-m-d');

            //今日实时
            $jinri_info = $this->shujuku("select b.username,sum(a.feiu_money) as all_feiu_money,sum(a.money) as all_money,a.status,a.pid from pay_jinrixiafa as a left join pay_user as b on b.uid=a.pid where xiafatime='" . $today . "' group by a.pid");
            $jiesuan_arr = array();
            $msgs = "";

            $all_feilu = 0;
            $all_mon = 0;
            foreach ($jinri_info as $key => $value) {
                $all_mon += $value['all_feiu_money'];
                $all_feilu += $value['all_money'];

                $jiesuan_arr[] = $value['pid'];
                $msgs .= "🧑‍💻" . $value['pid'] . "[" . $value['username'] . "] <b>已下发(" . $value['all_money'] . "U)</b>\r\n";
            }
            $msg = "";
            $all_user = $this->shujuku("select uid,username from pay_user");
            foreach ($all_user as $k => $v) {
                if (!in_array($v['uid'], $jiesuan_arr)) {
                    //查看商户今日收益：
                    $sql_info = "select sum(getmoney) as getmoney from pay_order where status = '1' and uid ='" . $v['uid'] . "' and date='" . $today . "'";
                    $sql_res = $this->shujuku($sql_info);
                    $sql_res[0]['getmoney'] = $sql_res[0]['getmoney'] > 0 ? $sql_res[0]['getmoney'] : 0;
                    $msgs .= "🧑‍💻" . $v['uid'] . "[" . $v['username'] . "] <b>未下发(" . $sql_res[0]['getmoney'] . "RNB)</b>\r\n";
                }
            }

            $all_m = "今天实时下发情况如下:\r\n💹今日已下发金额:" . $all_mon . "RNB\r\n📮今日合计已结算:" . $all_feilu . "u\r\n\r\n";

            $parameter = array(
                'chat_id' => $chat_id,
                'parse_mode' => 'HTML',
                'text' => $all_m . $msgs . "\r\n\r\n" . $msg,

            );

            $this->http_post_data('sendMessage', json_encode($parameter));
            exit();
        } else {
            $today = date('Y-m-d', strtotime("-1 day"));

            //昨日结算
            $jinri_info = $this->shujuku("select b.username,a.money,a.status,a.pid from pay_zuorixiafau as a left join pay_user as b on b.uid=a.pid where xiafatime='" . $today . "' group by a.pid");
            if (!$jinri_info) {
                $this->xiaoxi("昨日结算无数据！", $chat_id);
            }


            $jiesuan_arr = array();
            $msgs = "";

            $all_money = 0;

            foreach ($jinri_info as $key => $value) {

                $all_money += $value['money'];
                $jiesuan_arr[] = $value['pid'];
                if ($value['status'] == "1") {
                    $msgs .= "🧑‍💻" . $value['pid'] . "[" . $value['username'] . "] <b>已下发(" . $value['money'] . "U)</b>\r\n";
                } else {
                    $msgs .= "🧑‍💻" . $value['pid'] . "[" . $value['username'] . "] <b>正在下发/异常下发(" . $value['money'] . "U)</b>\r\n";
                }
            }
            $msg = "";
            $all_user = $this->shujuku("select uid,username from pay_user");
            foreach ($all_user as $k => $v) {
                if (!in_array($v['uid'], $jiesuan_arr)) {
                    //查看商户今日收益：
                    $sql_info = "select sum(getmoney) as getmoney from pay_order where status = '1' and uid ='" . $v['uid'] . "' and date='" . $today . "'";
                    $sql_res = $this->shujuku($sql_info);
                    $sql_res[0]['getmoney'] = $sql_res[0]['getmoney'] > 0 ? $sql_res[0]['getmoney'] : 0;
                    $msgs .= "🧑‍💻" . $v['uid'] . "[" . $v['username'] . "] <b>未下发(" . $sql_res[0]['getmoney'] . "RNB)</b>\r\n";
                }
            }

            $all_m = "昨日结算情况如下:\r\n📮昨日合计结算:" . $all_money . "U\r\n\r\n";

            $parameter = array(
                'chat_id' => $chat_id,
                'parse_mode' => 'HTML',
                'text' => $all_m . $msgs . "\r\n\r\n" . $msg,

            );

            $this->http_post_data('sendMessage', json_encode($parameter));
            exit();
        }
    }

    public function callback_xiafa($text, $chat_id, $from_id, $user_pid, $type)
    {
        $sql = "select * from pay_xiafashezhi where pid='" . $user_pid . "' and type='" . $type . "'";
        $sql_list = $this->shujuku($sql);
        $shifou = "否";
        $xianzhimon = "未填写";
        $xianzhicishu = "未填写";

        $shifou2 = "否";
        $xianzhimon2 = "未填写";
        $xianzhicishu2 = "未填写";
        if ($sql_list) {
            foreach ($sql_list as $key => $value) {
                if ($value['leixing'] == "1") {
                    if ($value['typelist'] == "1") {
                        $shifou = $value['svalue'];
                    }
                    if ($value['typelist'] == "2") {
                        $xianzhimon = $value['svalue'];
                    }
                    if ($value['typelist'] == "3") {
                        $xianzhicishu = $value['svalue'];
                    }

                } else {
                    if ($value['typelist'] == "1") {
                        $shifou2 = $value['svalue'];
                    }
                    if ($value['typelist'] == "2") {
                        $xianzhimon2 = $value['svalue'];
                    }
                    if ($value['typelist'] == "3") {
                        $xianzhicishu2 = $value['svalue'];
                    }
                }
            }


        }
        //1：实时下发：
        //2：昨日结算：
        if ($type == "1") {
            $sq = "实时下发";
            $shifou_str = $shifou == "是" ? "是" : "否";
            $shifou_str2 = $shifou2 == "是" ? "是" : "否";
            $msg = "当前信息:\r\n#手动下发设置\r\n是否开启" . $sq . "手动下发:" . $shifou_str . "\r\n余额满多少(元)手动下发一次:" . $xianzhimon . "\r\n当天手动下发次数不得超过:" . $xianzhicishu . "\r\n#自动下发设置\r\n是否开启" . $sq . "自动下发:" . $shifou_str2 . "\r\n余额满多少(元)自动下发一次:" . $xianzhimon2 . "\r\n当天自动下发次数不得超过:" . $xianzhicishu2;
            $switch_inline_query_current_msg = "#jishixianzai_xiugai_" . $type . "_#\r\n#手动下发设置\r\n是否开启" . $sq . "手动下发:" . $shifou_str . "\r\n余额满多少(元)手动下发一次:" . $xianzhimon . "\r\n当天手动下发次数不得超过:" . $xianzhicishu . "\r\n#自动下发设置\r\n是否开启" . $sq . "自动下发:" . $shifou_str2 . "\r\n余额满多少(元)自动下发一次:" . $xianzhimon2 . "\r\n当天自动下发次数不得超过:" . $xianzhicishu2;

        } else {
            $sq = "昨日结算";
            $shifou_str = $shifou == "是" ? "是" : "否";
            $shifou_str2 = $shifou2 == "是" ? "是" : "否";
            $msg = "当前信息:\r\n#昨日手动下发设置\r\n是否开启" . $sq . "手动下发:" . $shifou_str . "\r\n#昨日自动下发设置\r\n是否开启昨日自动下发=" . $shifou_str2 . "\r\n下发时间(每天)=" . $xianzhimon2;
            $switch_inline_query_current_msg = "#jishixianzai_xiugai_" . $type . "_#\r\n#昨日手动下发设置\r\n是否开启" . $sq . "手动下发=" . $shifou_str . "\r\n#昨日自动下发设置\r\n是否开启昨日自动下发=" . $shifou_str2 . "\r\n下发时间(每天)=" . $xianzhimon2;
        }


        $inline_keyboard_arr3[0] = array('text' => "立即修改 ", "switch_inline_query_current_chat" => $switch_inline_query_current_msg);
        $keyboard = [
            'inline_keyboard' => [
                $inline_keyboard_arr3,
            ]
        ];
        $parameter = array(
            'chat_id' => $chat_id,
            'parse_mode' => 'HTML',
            'text' => $msg,
            'reply_markup' => $keyboard,
            'disable_web_page_preview' => true,

        );

        $this->http_post_data('sendMessage', json_encode($parameter));
        exit();

    }

    public function kefuya($chatid, $department)
    {
        $sql_info = "select * from pay_botsettle where chatid ='" . $chatid . "'";
        $order_query2 = $this->pdo->query($sql_info);
        $chatinfo = $order_query2->fetchAll();
        $uid = $chatinfo['0']['merchant'];

        $visitorToken = $this->generateVisitorToken();
        // Rocket.Chat 服务器地址
        $serverUrl = $this->chat_url;

        // 创建访客
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $serverUrl . '/api/v1/livechat/visitor');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
            'visitor' => [
                'token' => $visitorToken,
                'name' => '商户号：' . $uid . "随机数:" . rand(100, 999),
                'email' => 'visitor@example.com',
                "department" => $department,  //天使技术部A，上游客服部B
            ]
        ]));

        $response = curl_exec($ch);
        if (!$response) {
            $this->xiaoxi("客服人工座席忙,请稍后再请求123！", $chatid);
        }

        $visitorData = json_decode($response, true);

        //第二步：通过token拿到数据先：
        //https://ccc.zmchat.xyz/api/v1/livechat/room?token=bc2a23307a699c2909a54c5948c2b05c036c92dc200568646cb5b10cabb4a0d5
        $url2 = $serverUrl . "api/v1/livechat/room?token=" . $visitorToken;
        $headers2 = [];
        $response2 = $this->httpGet($url2, $headers2);
        if (!$response2) {
            $this->xiaoxi("客服人工座席忙,请稍后再请求456！", $chatid);
        }
        $room_id = $response2['room']['_id'];


        //再去请求获取用户信息：
        //https://ccc.zmchat.xyz/api/v1/livechat/agent.info/MPSvGLEJgvGzNgg7x/5a65a366b07dcabef66ce3b624b83dcd7c01cc4599881fffdff79eff8fc6f6a2
        // 使用示例
        $url3 = $serverUrl . 'api/v1/livechat/agent.info/' . $room_id . "/" . $visitorToken;
        $headers3 = [
            //'Authorization: Bearer your_token_here',
            //'Content-Type: application/json'
        ];

        $response3 = $this->httpGet($url3, $headers3);
        if (!$response3) {
            $this->xiaoxi("客服人工座席忙,请稍后再请求789！", $chatid);
        }
        $agent_id = $response3['agent']['_id'];
        $kefu_username = $response3['agent']['username'];

        //这里来一个记录，表示当前商户正在对话，进行中：
        $status = "0";
        $channel = $this->token;
        $createtime = time();
        $set_sql = "insert into pay_userchat (channel,status,visitorToken,room_id,agent_id,user_id,createtime,chat_id,kefu_name) values ('" . $channel . "','" . $status . "','" . $visitorToken . "', '" . $room_id . "','" . $agent_id . "','" . $uid . "','" . $createtime . "','" . $chatid . "','" . $kefu_username . "')";
        $this->pdo->exec($set_sql);
        $this->xiaoxi("客服:" . $kefu_username . ",为你开启服务，请简要说出你的需求", $chatid);
    }


    public function callback($data)
    {

        $text = $data['callback_query']['data'];
        $chat_id = $data['callback_query']['message']['chat']['id'];
        $from_id = $data['callback_query']['from']['id'];
        $userid = $from_id;
        $message_id = $data['callback_query']['message']['message_id'];
        $set_sqlq = "select * FROM pay_type where status='1'";
        $order_query_q = $this->pdo->query($set_sqlq);
        $user_type = $order_query_q->fetchAll();
        $new_type = array();
        $chatid = $chat_id;
        $username = $data['message']['from']['username'];//用户名称


        $sql_info = "select * from pay_botsettle where chatid ='" . $chatid . "'";
        $order_query2 = $this->pdo->query($sql_info);
        $chatinfo = $order_query2->fetchAll();


        //关闭当前客服会话
        if (strpos($text, '关闭当前客服会话') !== false) {
            $res = $this->pdo->exec("UPDATE pay_userchat SET status='1' WHERE channel='" . $this->token . "' and chat_id='" . $chatid . "'");
            $this->xiaoxi("关闭当前客服会话成功", $chatid);
        }
        $kefu_sql = "select * FROM pay_userchat where chat_id ='" . $chatid . "' and channel='" . $this->token . "' and status='0'";
        $order_query2 = $this->pdo->query($kefu_sql);
        $chatinfo3 = $order_query2->fetchAll();
        if ($chatinfo3) {
            $inline_keyboard_arr9[0] = array('text' => "关闭当前会话 ", "callback_data" => "关闭当前客服会话");
            $keyboard = [
                'inline_keyboard' => [
                    $inline_keyboard_arr9,

                ]
            ];

            $parameter = array(
                'chat_id' => $chatid,
                'parse_mode' => 'HTML',
                'text' => "你已经有一个正在通讯的会话！",
                'reply_markup' => $keyboard,
                'disable_web_page_preview' => true,
            );

            $this->http_post_data('sendMessage', json_encode($parameter));
            exit();

        }


        if (strpos($text, 'xunzhaokefu_') !== false) {
            $kefupei = explode("kefu_", $text);
            $this->kefuya($chatid, $kefupei[1]);
        }

        $user_pid = $chatinfo[0]['merchant'];
        if (strpos($text, 'shanghuliuliang') !== false) {
            $qudao = explode("###", $text);
            if ($qudao[1] == "1") {
                //今日：
                $start_time = date('Y-m-d 00:00:00');
                $end_time = date('Y-m-d 00:00:00', strtotime('+1 day'));
            } elseif ($qudao[1] == "2") {
                //昨日：
                $start_time = date('Y-m-d 00:00:00', strtotime('-1 day'));
                $end_time = date('Y-m-d 00:00:00');
            } elseif ($qudao[1] == "3") {
                //一周：
                $start_time = date('Y-m-d 00:00:00', strtotime('-7 day'));
                $end_time = date('Y-m-d 00:00:00');
            } else {
                //一月
                $start_time = date('Y-m-d 00:00:00', strtotime('-30 day'));
                $end_time = date('Y-m-d 00:00:00');
            }
            $sql = "select u_channel,count(u_channel) as channel_count from pay_order where addtime between '" . $start_time . "' and '" . $end_time . "' and u_channel !='0' group by u_channel";
            $order_channel = $this->pdo->query($sql);
            $channel_info = $order_channel->fetchAll();
            if (count($channel_info) > 0) {
                $msg = "流量渠道订单分析：\r\n";
                foreach ($channel_info as $keys => $vales) {
                    $msg .= "渠道编号:" . $vales['u_channel'] . "  --->下单数量： " . $vales['channel_count'] . "\r\n";
                }
                $inline_keyboard_arr2 = array(
                    array('text' => "查看详情渠道信息", "callback_data" => "xiangxishangpphuliuliang###" . $qudao[1]),

                );
                $keyboard = [
                    'inline_keyboard' => [

                        $inline_keyboard_arr2,
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
            } else {
                $msg = "没有查询倒你的渠道流量统计数据信息";
                $this->xiaoxi($msg, $chatid);
            }
        }
        if (strpos($text, 'xiangxishangpphuliuliang') !== false) {
            $qudao = explode("###", $text);
            if ($qudao[1] == "1") {
                //今日：
                $start_time = date('Y-m-d 00:00:00');
                $end_time = date('Y-m-d 00:00:00', strtotime('+1 day'));
            } elseif ($qudao[1] == "2") {
                //昨日：
                $start_time = date('Y-m-d 00:00:00', strtotime('-1 day'));
                $end_time = date('Y-m-d 00:00:00');
            } elseif ($qudao[1] == "3") {
                //一周：
                $start_time = date('Y-m-d 00:00:00', strtotime('-7 day'));
                $end_time = date('Y-m-d 00:00:00');
            } else {
                //一月
                $start_time = date('Y-m-d 00:00:00', strtotime('-30 day'));
                $end_time = date('Y-m-d 00:00:00');
            }
            $sql = "select u_channel,status from pay_order where addtime between '" . $start_time . "' and '" . $end_time . "' and u_channel !='0'";
            $order_channel = $this->pdo->query($sql);
            $channel_info = $order_channel->fetchAll();
            if (count($channel_info) > 0) {
                $msg = "详细流量渠道订单分析：\r\n";
                $all_channel = array();
                foreach ($channel_info as $keys => $vales) {
                    $all_channel[$vales['u_channel']]['all'] += 1;
                    if ($vales['status'] == "1") {
                        $all_channel[$vales['u_channel']]['pay'] += 1;
                    } else {
                        $all_channel[$vales['u_channel']]['nopay'] += 1;
                    }
                }

                foreach ($all_channel as $keysp => $valesp) {
                    $all = $valesp['all'] > 0 ? $valesp['all'] : "0";
                    $all_pay = $valesp['pay'] > 0 ? $valesp['pay'] : "0";
                    $msg .= "渠道编号:" . $keysp . "  --->下单数量： " . $all . "  ---> 支付数量： " . $all_pay . "\r\n";
                }
                $inline_keyboard_arr2 = array(
                    array('text' => "查看简洁渠道信息", "callback_data" => "shanghuliuliang###" . $qudao[1]),

                );
                $keyboard = [
                    'inline_keyboard' => [

                        $inline_keyboard_arr2,
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
            } else {
                $msg = "没有查询倒你的渠道流量统计数据信息";
                $this->xiaoxi($msg, $chatid);
            }
        }
        if (strpos($text, 'chakanjinrijianyue_') !== false) {


            /* $quanxian = "实时下发";
             $this->quanxian($chatid, $from_id, $quanxian, $username);*/

            $sql_info = "select * from pay_botsettle where chatid ='" . $chatid . "'";

            $order_query2 = $this->pdo->query($sql_info);
            $chatinfo = $order_query2->fetchAll();

            if (!$chatinfo) {
                //已經綁定群了：
                $parameter = array(
                    'chat_id' => $chatid,
                    'parse_mode' => 'HTML',
                    'text' => "该群暂未绑定商户号，请输入快捷命令：/bd"

                );
                $this->http_post_data('sendMessage', json_encode($parameter));
            } else {
                $uid = $chatinfo['0']['merchant'];


                $uid_end = $chatinfo['0']['merchant'];

                $today = date("Y-m-d");
                $todays = date("Y年m月d日");

                $huilvinfo = $this->huilvinfo("99999", "99999");

                $fufonginfo = $this->fudonginfo($uid, $chatid);
                $fenchenginfo = $this->fenchenginfo($uid, $chatid);

                $tongdaoxinxi = $this->tongdaoxinxi($uid, $chatid);
                $zhifuxinxi = $this->zhifuxinxi($uid, $chatid);


                $sql_zhifu = "select id,showname from pay_type";

                $zhifu_fetch = $this->shujuku($sql_zhifu);
                $zhifu_info_arr = array();
                foreach ($zhifu_fetch as $kp => $vp) {
                    $zhifu_info_arr[$vp['id']] = $vp['showname'];
                }

                if (count($zhifuxinxi) <= 0) {
                    $this->xiaoxi("当前商户暂未设置支付类型费率，请先设置！", $chatid);
                }
                $all_zhifu = array();  //纯支付方式的量
                $all_tongdao = array(); //纯设置通道的量

                $all_tongdao_zhifu = array();  //支付方式下的各个通道跑的数据

                $uid_arr = explode("|", $uid);
                if (count($uid_arr) > 1) {

                    foreach ($uid_arr as $k => $v) {
                        $inline_keyboard_arr[$k] = array('text' => "下发商户:" . $v, "callback_data" => "实时下发商户_" . $v);
                    }

                    $keyboard = [
                        'inline_keyboard' => [
                            $inline_keyboard_arr
                        ]
                    ];
                    $parameter = array(
                        'chat_id' => $chatid,
                        'parse_mode' => 'HTML',
                        'text' => "请选择要下发的商户",
                        'reply_markup' => $keyboard,

                    );

                    $this->http_post_data('sendMessage', json_encode($parameter));
                    exit();

                } else {
                    //查询次商户号今日总收入信息：
                    $sql_info = "select * from pay_order where status = '1' and uid ='" . $uid . "' and date='" . $today . "'";


                    $order_query3 = $this->pdo->query($sql_info);
                    $chatinfo = $order_query3->fetchAll();
                    if (count($chatinfo) <= 0) {
                        $this->xiaoxi("未查询到今日支付订单成功数据记录！", $chatid);
                    }

                    $all_money = 0;
                    foreach ($chatinfo as $key => $value) {
                        $all_money += $value['money'];
                        //支付方式计算
                        $all_zhifu[$value['type']] += $value['money'];
                        $all_tongdao_zhifu[$value['type']][$value['channel']] += $value['money'];


                        if (array_key_exists($value['channel'], $tongdaoxinxi)) {
                            //通道费用计算
                            $all_tongdao[$value['channel']] += $value['money'];
                        }
                    }


                    $sql_info3 = "select username,usdt_str from pay_user where  uid ='" . $uid . "'";
                    $order_query7 = $this->pdo->query($sql_info3);
                    $chatinfo3 = $order_query7->fetchAll();
                    $uidinfo2 = $chatinfo3[0];


                    $msg = "✅今天跑量情况如下\r\n🆔商户号:" . $uid . "\r\n🧑🏻‍💼名字:" . $uidinfo2['username'] . "\r\n";

                    $msg_tongdao = "";


                    if (count($all_zhifu) > 0) {
                        foreach ($all_zhifu as $kt => $vt) {
                            $sql_zhifu = "select showname from pay_type where  id ='" . $kt . "'";

                            $zhifu_fetch = $this->shujuku($sql_zhifu);

                            $zhifu_info = $zhifu_fetch[0]['showname'];
                            $msg .= "🔔" . $zhifu_info . "总量:" . $vt . "\r\n";
                        }

                    }


                    $msg .= "💹总跑量:" . $all_money . "\r\n";

                    $type = substr($fufonginfo, 0, 1);
                    if ($type == "-") {
                        $changs = explode("-", $fufonginfo);
                        $shiji_huilv = $huilvinfo - $changs[1];
                    } else {
                        $changs = explode("+", $fufonginfo);
                        $shiji_huilv = $huilvinfo + $changs[1];
                    }
                    $shiji_huilv_tousu = $shiji_huilv - 0.1;
                    $all_usdt_m = 0;
                    $all_fusdt_money = 0;
                    $xiafa_str = "";

                    foreach ($all_tongdao_zhifu as $kv => $vv) {
                        //$zhifu_info_arr[$kv]
                        //$msg .= "\r\n📮" . $zhifu_info_arr[$kv] . "跑量如下：\r\n\r\n";
                        foreach ($vv as $kp => $vp) {
                            $channel_sql = "select id,name from pay_channel where id='" . $kp . "'";
                            $channel_info_query = $this->shujuku($channel_sql);
                            $channel_info = $channel_info_query[0];
                            // $msg .= "(" . $channel_info['id'] . ")" . $channel_info['name'] . ":" . $vp . "\r\n";
                            if (array_key_exists($kp, $tongdaoxinxi)) {

                                $zhifu_lixi = $tongdaoxinxi[$kp];

                            } else {
                                $zhifu_lixi = $zhifuxinxi[$kv];

                            }
                            $type = substr($fufonginfo, 0, 1);

                            $jisuan = ($vp * $zhifu_lixi * $fenchenginfo);
                            //$msg .= $vp . "*" . $zhifu_lixi . "*" . $fenchenginfo . "/(" . $shiji_huilv . ")=" . $jisuan . "U\r\n\r\n";

                            $xiafa_str .= $jisuan . "+";

                            $all_usdt_m += $jisuan;
                            $all_fusdt_money += $vp;
                        }
                    }
                    $msg .= "💹费率后总额:" . $all_usdt_m . "\r\n";
                    $msg .= "\r\n➖➖➖➖➖➖➖➖➖\r\n\r\n";
                    $msg .= "不可下发金额\r\n\r\n";


                    //查看今日下发数据记录：
                    $jinri_info = "select money,jutishijian,feiu_money,feilv from pay_jinrixiafa where status='1' and pid ='" . $uid . "' and xiafatime='" . $today . "' and chatid='" . $chatid . "'";
                    $order_jinri = $this->pdo->query($jinri_info);
                    $tjinri_arr = $order_jinri->fetchAll();
                    $all_jinri_xiafa = 0.00;

                    $xiafa_str = substr($xiafa_str, 0, -1);

                    if ($tjinri_arr) {

                        $msg .= "\r\n📮今天下发历史记录" . "\r\n";
                        foreach ($tjinri_arr as $kj => $vj) {
                            $ti = date('H:i:s', $vj['jutishijian']);
                            $msg .= "🔈" . $ti . " 成功下发：" . $vj['feiu_money'] . "元(含手续费)\r\n";
                            $all_jinri_xiafa += $vj['feiu_money'];

                            $xiafa_str .= "-" . $vj['feiu_money'];
                        }
                    }

                    $msg .= "\r\n❌t0不可结算限额:" . $this->tojiesuan . "元\r\n\r\n";


                    /*投诉：*/
                    $tousu_info2 = "select * from pay_usertousu where pid ='" . $uid . "'";
                    $order_tousu2 = $this->pdo->query($tousu_info2);
                    $tousu_m2 = $order_tousu2->fetchAll();
                    $tousu_today = 0;
                    $tousu_today2 = 0;
                    $tousu_U2 = 0;
                    foreach ($tousu_m2 as $k => $v) {
                        $time = date('m-d', strtotime($v['date']));
                        $tousu_today += $v['money'];

                        if ($v['status'] == "1") {
                            //已扣除
                            $pp = "已扣除";
                        } else {
                            //待扣除
                            $pp = "待扣除 ---- /delete_tousu_" . $v['id'];
                            $tousu_today2 += $v['money'];

                            $tousu_U2 += $v['money'];

                        }


                        $msg .= "❌" . $time . ":投诉退款:" . $v['money'] . "元  ----" . $pp . "\r\n";
                    }

                    $trx_info = "select * from pay_usertrx";
                    $trx_jinri = $this->pdo->query($trx_info);
                    $trx_arr = $trx_jinri->fetchAll();

                    if ($trx_arr) {
                        $trx_shouxufei = $trx_arr[0]['trx'];
                    } else {
                        $trx_shouxufei = 0.00;
                    }

                    //查看今日的投诉金额：
                    $tousu_info = "select sum(money) as tousumoney from pay_usertousu where status='0' and  pid ='" . $uid . "'";
                    $order_tousu = $this->pdo->query($tousu_info);
                    $tousu_m = $order_tousu->fetchAll();
                    $tousu_today = round($tousu_m[0]['tousumoney'], 2);
                    //$tousu_U = 0;
                    //查看投诉退款数据：
                    if ($tousu_U2 > 0) {
                        $tousu_U = $tousu_U2;
                        $msg .= "❌合计待投诉退款:" . $tousu_today . "元\r\n";
                        $tousu_U = 0;
                    }

                    $bukexiafaheji = $tousu_today + $all_jinri_xiafa + $this->tojiesuan;
                    $msg .= "\r\n💹不可下发金额合计：" . $bukexiafaheji . "元\r\n\r\n";
                    $msg .= "➖➖➖➖➖➖➖➖➖\r\n";
                    $msg .= "下发扣除费用\r\n\r\n";
                    $msg .= "🔄Trx手续费=" . $trx_shouxufei . "U(每次下发)\r\n";
                    $msg .= "➖➖➖➖➖➖➖➖➖\r\n";


                    $xiafa_str .= "-" . $tousu_U;

                    $trx_info = "select * from pay_usertrx";
                    $trx_jinri = $this->pdo->query($trx_info);
                    $trx_arr = $trx_jinri->fetchAll();

                    if ($trx_arr) {
                        $trx_shouxufei = $trx_arr[0]['trx'];
                    } else {
                        $trx_shouxufei = 0.00;
                    }
                    $xiafa_str .= "-" . $trx_shouxufei;

                    //查询t0的限额：

                    $jinri_tojiesuan = round($this->tojiesuan / $shiji_huilv, 2);
                    $msg .= "\r\n❌t0不可结算限额:" . $this->tojiesuan . "元\r\n\r\n";

                    //$keyixiafa = round($all_usdt_m, 2) - round($all_jinri_xiafa, 2) - $tousu_U - round($trx_shouxufei, 2) -$jinri_tojiesuan;

                    //当前可下发:   总金额-已经下发的-限额

                    $keyixiafa_value = $all_usdt_m - $all_jinri_xiafa - $this->tojiesuan;
                    $keyixiafa_str = $all_fusdt_money . " - " . $all_jinri_xiafa . " - " . $this->tojiesuan . '=' . $keyixiafa_value;

                    //实际下发：当前可下发-手续费-投诉金额
                    $shijixiafa_value = (floor((($keyixiafa_value / $shiji_huilv) * 100)) / 100) - round($trx_shouxufei, 2) - (floor((($tousu_U2 / $shiji_huilv) * 100)) / 100);
                    $shijixiafa_str = $keyixiafa_value . "/" . $shiji_huilv . " - " . round($trx_shouxufei, 2) . " - " . $tousu_U2 . "/" . $shiji_huilv . "=" . $shijixiafa_value;

                    //$this->xiaoxinoend($shijixiafa_value,$chatid);
                    //下发了多少金额： 总金额-已经下发-投诉金额-限额
                    $shijixiafa_jiner_rnb = $all_usdt_m - $all_jinri_xiafa - $tousu_U2 - $this->tojiesuan;
                    //$this->xiaoxinoend($shijixiafa_jiner_rnb,$chatid);
                    //$msg .= "\r\n🈴当前可下发:" . $xiafa_str . "=" . $keyixiafa . "U";
                    $msg .= "\r\n🈴当前可下发:" . $shijixiafa_value . "U";
                    $msg .= "\r\n✅下发地址:\r\n" . $uidinfo2['usdt_str'];
                    // $this->xiaoxinoend($shijixiafa_jiner_rnb,$chatid);
                    //查看下发地址：
                    $today_time = date("d");
                    if ($shijixiafa_value > 0) {
                        $inline_keyboard_arr[0] = array('text' => "立即下发今日:" . $shijixiafa_value . "U", "callback_data" => "jinrixiafa_user_" . $uid . "&&" . $shijixiafa_value . "###" . $shijixiafa_jiner_rnb . "!!!" . $today_time);

                    } else {
                        $inline_keyboard_arr[0] = array('text' => "今日收益下发成功", "callback_data" => "wufaxiafa_user_" . $uid_end);

                    }
                    $inline_keyboard_arr2[0] = array('text' => "查详细账单", "callback_data" => "chakanjinrixiangxi_" . $uid_end);

                }


                $keyboard = [
                    'inline_keyboard' => [
                        $inline_keyboard_arr,
                        $inline_keyboard_arr2
                    ]
                ];
                $parameter = array(
                    'chat_id' => $chatid,
                    'parse_mode' => 'HTML',
                    'text' => $msg,
                    'reply_markup' => $keyboard,
                    'message_id' => $message_id

                );

                $this->http_post_data('editMessageText', json_encode($parameter));
                exit();
            }
        }
        if (strpos($text, 'chakanjinrixiangxi_') !== false) {

            /*$quanxian = "实时下发";
            $this->quanxian($chatid, $from_id, $quanxian, $username);*/

            $sql_info = "select * from pay_botsettle where chatid ='" . $chatid . "'";

            $order_query2 = $this->pdo->query($sql_info);
            $chatinfo = $order_query2->fetchAll();

            if (!$chatinfo) {
                //已經綁定群了：
                $parameter = array(
                    'chat_id' => $chatid,
                    'parse_mode' => 'HTML',
                    'text' => "该群暂未绑定商户号，请输入快捷命令：/bd"

                );
                $this->http_post_data('sendMessage', json_encode($parameter));
            } else {
                $uid = $chatinfo['0']['merchant'];


                $uid_end = $chatinfo['0']['merchant'];

                $today = date("Y-m-d");
                $todays = date("Y年m月d日");

                $huilvinfo = $this->huilvinfo("99999", "99999");
                $fufonginfo = $this->fudonginfo($uid, $chatid);
                $fenchenginfo = $this->fenchenginfo($uid, $chatid);

                $tongdaoxinxi = $this->tongdaoxinxi($uid, $chatid);
                $zhifuxinxi = $this->zhifuxinxi($uid, $chatid);


                $sql_zhifu = "select id,showname from pay_type";

                $zhifu_fetch = $this->shujuku($sql_zhifu);
                $zhifu_info_arr = array();
                foreach ($zhifu_fetch as $kp => $vp) {
                    $zhifu_info_arr[$vp['id']] = $vp['showname'];
                }

                if (count($zhifuxinxi) <= 0) {
                    $this->xiaoxi("当前商户暂未设置支付类型费率，请先设置！", $chatid);
                }
                $all_zhifu = array();  //纯支付方式的量
                $all_tongdao = array(); //纯设置通道的量

                $all_tongdao_zhifu = array();  //支付方式下的各个通道跑的数据

                $uid_arr = explode("|", $uid);
                if (count($uid_arr) > 1) {
                    $parameter = array(
                        'chat_id' => $chatid,
                        'parse_mode' => 'HTML',
                        'text' => "</b>当前群存在多个商户号,请先解绑，将商户分群后再操作！</b>",
                    );
                    $this->http_post_data('sendMessage', json_encode($parameter));
                    exit();

                } else {
                    //查询次商户号今日总收入信息：
                    $sql_info = "select * from pay_order where status = '1' and uid ='" . $uid . "' and date='" . $today . "'";


                    $order_query3 = $this->pdo->query($sql_info);
                    $chatinfo = $order_query3->fetchAll();
                    if (count($chatinfo) <= 0) {
                        $this->xiaoxi("未查询到今日支付订单成功数据记录！", $chatid);
                    }

                    $all_money = 0;
                    foreach ($chatinfo as $key => $value) {
                        $all_money += $value['money'];
                        //支付方式计算
                        $all_zhifu[$value['type']] += $value['money'];
                        $all_tongdao_zhifu[$value['type']][$value['channel']] += $value['money'];


                        if (array_key_exists($value['channel'], $tongdaoxinxi)) {
                            //通道费用计算
                            $all_tongdao[$value['channel']] += $value['money'];
                        }
                    }


                    $sql_info3 = "select username,usdt_str from pay_user where  uid ='" . $uid . "'";
                    $order_query7 = $this->pdo->query($sql_info3);
                    $chatinfo3 = $order_query7->fetchAll();
                    $uidinfo2 = $chatinfo3[0];


                    $msg = "✅今天跑量\r\n🆔商户号:" . $uid . "\r\n🧑🏻‍💼名字:" . $uidinfo2['username'] . "\r\n";

                    $msg_tongdao = "";


                    if (count($all_zhifu) > 0) {
                        foreach ($all_zhifu as $kt => $vt) {
                            $sql_zhifu = "select showname from pay_type where  id ='" . $kt . "'";

                            $zhifu_fetch = $this->shujuku($sql_zhifu);

                            $zhifu_info = $zhifu_fetch[0]['showname'];
                            $msg .= "🔔" . $zhifu_info . "总量:" . $vt . "\r\n";
                        }

                    }


                    $msg .= "💹总跑量:" . $all_money . "\r\n";

                    $type = substr($fufonginfo, 0, 1);
                    if ($type == "-") {
                        $changs = explode("-", $fufonginfo);
                        $shiji_huilv = $huilvinfo - $changs[1];
                    } else {
                        $changs = explode("+", $fufonginfo);
                        $shiji_huilv = $huilvinfo + $changs[1];
                    }
                    $shiji_huilv_tousu = $shiji_huilv - 0.1;
                    $all_usdt_m = 0;
                    $all_fusdt_money = 0;
                    $xiafa_str = "";

                    foreach ($all_tongdao_zhifu as $kv => $vv) {
                        //$zhifu_info_arr[$kv]
                        $msg .= "➖➖➖➖➖➖➖➖➖\r\n📮" . $zhifu_info_arr[$kv] . "跑量如下：\r\n\r\n";

                        $zhifuleixing_jisuanqian = 0;
                        $zhifuleixing_jisuanqianhou = 0;

                        foreach ($vv as $kp => $vp) {
                            $channel_sql = "select id,name from pay_channel where id='" . $kp . "'";
                            $channel_info_query = $this->shujuku($channel_sql);
                            $channel_info = $channel_info_query[0];
                            $msg .= "(" . $channel_info['id'] . ")" . $channel_info['name'] . ":" . $vp . "\r\n";
                            if (array_key_exists($kp, $tongdaoxinxi)) {

                                $zhifu_lixi = $tongdaoxinxi[$kp];

                            } else {
                                $zhifu_lixi = $zhifuxinxi[$kv];

                            }
                            $type = substr($fufonginfo, 0, 1);

                            $jisuan = ($vp * $zhifu_lixi * $fenchenginfo);
                            $msg .= "费率后：" . $vp . "*" . $zhifu_lixi . "*" . $fenchenginfo . "=" . $jisuan . "\r\n\r\n";

                            $xiafa_str .= $jisuan . "+";

                            $all_usdt_m += $jisuan;
                            $all_fusdt_money += $vp;

                            $zhifuleixing_jisuanqian += $vp;
                            $zhifuleixing_jisuanqianhou += $jisuan;


                        }
                        $msg .= "💹" . $zhifu_info_arr[$kv] . "总跑量:" . $zhifuleixing_jisuanqian . "元\r\n💹" . $zhifu_info_arr[$kv] . "费率后:" . $zhifuleixing_jisuanqianhou . "元\r\n";
                    }
                    $msg .= "➖➖➖➖➖➖➖➖➖\r\n";
                    $msg .= "\r\n💹今天费率后总额: " . $all_usdt_m . "元\r\n\r\n";
                    $msg .= "➖➖➖➖➖➖➖➖➖";
                    $msg .= "\r\n不可下发金额:\r\n";


                    //查看今日下发数据记录：
                    $jinri_info = "select money,jutishijian,feiu_money,feilv from pay_jinrixiafa where status='1' and pid ='" . $uid . "' and xiafatime='" . $today . "' and chatid='" . $chatid . "'";
                    $order_jinri = $this->pdo->query($jinri_info);
                    $tjinri_arr = $order_jinri->fetchAll();
                    $all_jinri_xiafa = 0.00;

                    $xiafa_str = substr($xiafa_str, 0, -1);

                    if ($tjinri_arr) {

                        $msg .= "\r\n📮今天下发历史记录" . "\r\n";
                        foreach ($tjinri_arr as $kj => $vj) {
                            $ti = date('H:i:s', $vj['jutishijian']);
                            $msg .= "🔈" . $ti . " 成功下发：" . $vj['feiu_money'] . "/" . $vj['feilv'] . "/" . $vj['money'] . "U(含手续费)\r\n";
                            $all_jinri_xiafa += $vj['feiu_money'];

                            $xiafa_str .= "-" . $vj['feiu_money'];
                        }
                    }


                    $trx_info = "select * from pay_usertrx";
                    $trx_jinri = $this->pdo->query($trx_info);
                    $trx_arr = $trx_jinri->fetchAll();

                    if ($trx_arr) {
                        $trx_shouxufei = $trx_arr[0]['trx'];
                    } else {
                        $trx_shouxufei = 0.00;
                    }
                    $xiafa_str .= "-" . $trx_shouxufei;

                    //查询t0的限额：

                    $jinri_tojiesuan = round($this->tojiesuan / $shiji_huilv, 2);
                    $msg .= "\r\n❌t0不可结算限额:" . $this->tojiesuan . "元\r\n\r\n";

                    $tousu_info2 = "select * from pay_usertousu where pid ='" . $uid . "'";
                    $order_tousu2 = $this->pdo->query($tousu_info2);
                    $tousu_m2 = $order_tousu2->fetchAll();
                    $tousu_today = 0;
                    $tousu_today2 = 0;
                    $tousu_U2 = 0;

                    foreach ($tousu_m2 as $k => $v) {
                        $time = date('m-d', strtotime($v['date']));
                        $tousu_today += $v['money'];

                        if ($v['status'] == "1") {
                            //已扣除
                            $pp = "已扣除";
                        } else {
                            //待扣除
                            $pp = "待扣除 ---- /delete_tousu_" . $v['id'];
                            $tousu_today2 += $v['money'];

                            $tousu_U2 += $v['money'];

                        }


                        $msg .= "❌" . $time . ":投诉退款:" . $v['money'] . "元  ----" . $pp . "\r\n";
                    }
                    $xiafa_str .= "-" . $tousu_U;

                    //查看今日的投诉金额：
                    $tousu_info = "select sum(money) as tousumoney from pay_usertousu where status='0' and  pid ='" . $uid . "'";
                    $order_tousu = $this->pdo->query($tousu_info);
                    $tousu_m = $order_tousu->fetchAll();
                    $tousu_today = (floor(($tousu_m[0]['tousumoney'] * 100)) / 100);

                    //查看投诉退款数据：
                    if ($tousu_U2 > 0) {
                        $tousu_U = $tousu_U2;
                        $msg .= "❌合计待投诉退款:" . $tousu_U . "元\r\n";

                    } else {
                        $tousu_U = 0;
                    }

                    $bukexiafaheji = $all_jinri_xiafa + $this->tojiesuan + $tousu_U;
                    $msg .= "\r\n💹不可下发金额合计：" . $bukexiafaheji . "元\r\n\r\n";

                    $msg .= "➖➖➖➖➖➖➖➖➖\r\n下发扣除费用\r\n";
                    $msg .= "\r\n🔄Trx手续费=" . $trx_shouxufei . "U(每次下发)\r\n";

                    $keyixiafa = $all_usdt_m - $all_jinri_xiafa - $tousu_U - $trx_shouxufei - $jinri_tojiesuan;

                    $jinrike = $all_usdt_m - $all_jinri_xiafa;
                    $xiafa_str2 = round($all_usdt_m, 2) . "-" . $all_jinri_xiafa . "-" . $tousu_U . "-" . round($trx_shouxufei, 2) . "-" . $jinri_tojiesuan . "=" . round($keyixiafa, 2);


                    //当前可下发:   总金额-已经下发的-限额
                    $keyixiafa_value = $all_usdt_m - $bukexiafaheji;
                    $keyixiafa_str = $all_usdt_m . "-" . $bukexiafaheji . '=' . $keyixiafa_value;

                    //实际下发：当前可下发-手续费-投诉金额
                    $shijixiafa_value = (floor((($keyixiafa_value / $shiji_huilv) * 100)) / 100) - $trx_shouxufei;
                    $shijixiafa_str = $keyixiafa_value . "/" . $shiji_huilv . " - " . round($trx_shouxufei, 2) . "=" . $shijixiafa_value;


                    //下发了多少金额： 总金额-已经下发-投诉金额-限额+手续费
                    $shijixiafa_jiner_rnb = $all_usdt_m - $all_jinri_xiafa - $tousu_U - $this->tojiesuan;
                    $msg .= "\r\n➖➖➖➖➖➖➖➖➖\r\n";

                    //当前可下发=今天费率后总额-不可下发金额合计

                    $msg .= "\r\n🈴当前可下发:" . $keyixiafa_str . "元\r\n\r\n";
                    // $msg .= "投诉冻结余额:" . $tousu_U . "\r\n";
                    //$msg .= "trx手续费:" . $trx_shouxufei . "U\r\n";
                    $msg .= "🈴实际可下发:" . $shijixiafa_str . "U\r\n";
                    $msg .= "\r\n✅下发地址\r\n" . $uidinfo2['usdt_str'];
                    //$this->xiaoxinoend($all_usdt_m."-".$all_jinri_xiafa."-".$tousu_U."-".$trx_shouxufei,$chatid);


                    $today_time = date("d");
                    //查看下发地址：
                    if ($keyixiafa > 0) {
                        $inline_keyboard_arr[0] = array('text' => "立即下发今日:" . $shijixiafa_value . "U", "callback_data" => "jinrixiafa_user_" . $uid . "&&" . $shijixiafa_value . "###" . $shijixiafa_jiner_rnb . "!!!" . $today_time);
                    } else {
                        $inline_keyboard_arr[0] = array('text' => "当前收益不足以下发", "callback_data" => "wufaxiafa_user_" . $uid_end);
                    }

                    $inline_keyboard_arr2[0] = array('text' => "查简约账单", "callback_data" => "chakanjinrijianyue_" . $uid_end);

                }


                $keyboard = [
                    'inline_keyboard' => [
                        $inline_keyboard_arr,
                        $inline_keyboard_arr2
                    ]
                ];
                $parameter = array(
                    'chat_id' => $chatid,
                    'parse_mode' => 'HTML',
                    'text' => $msg,
                    'reply_markup' => $keyboard,
                    "message_id" => $message_id,

                );

                $this->http_post_data('editMessageText', json_encode($parameter));

                exit();
            }
        }

        if (strpos($text, 'chakanzuorijianyue_') !== false) {

            /*  $quanxian = "下发昨日结算收益";
              $this->quanxian($chatid, $from_id, $quanxian, $username);*/

            $sql_info = "select * from pay_botsettle where chatid ='" . $chatid . "'";

            $order_query2 = $this->pdo->query($sql_info);
            $chatinfo = $order_query2->fetchAll();

            if (!$chatinfo) {
                $this->xiaoxi("该群暂未绑定商户号，请输入快捷命令：/bd商户号", $chatid);
            }
            $uid = $chatinfo['0']['merchant'];
            $uid_end = $uid;

            if ($this->kaiqi_teshu_xiafa) {
                $nayitian = $this->teshu_riqi;
                $today = date("Y-m-d", strtotime(date($nayitian)));
                $todays = date("Y年m月d日", strtotime(date($nayitian)));
                $todays2 = date("m月d日", strtotime(date($nayitian)));
            } else {
                $today = date("Y-m-d", strtotime("-1 day"));
                $todays = date("Y年m月d日", strtotime("-1 day"));
                $todays2 = date("m月d日", strtotime("-1 day"));
            }

            $uid_arr = explode("|", $uid);

            $huilvinfo = $this->huilvinfo("99999", "99999");
            $fufonginfo = $this->fudonginfo($uid, $chatid);
            $fenchenginfo = $this->fenchenginfo($uid, $chatid);

            $tongdaoxinxi = $this->tongdaoxinxi($uid, $chatid);
            $zhifuxinxi = $this->zhifuxinxi($uid, $chatid);

            $sql_zhifu = "select id,showname from pay_type";

            $zhifu_fetch = $this->shujuku($sql_zhifu);
            $zhifu_info_arr = array();
            foreach ($zhifu_fetch as $kp => $vp) {
                $zhifu_info_arr[$vp['id']] = $vp['showname'];
            }

            if (count($zhifuxinxi) <= 0) {
                $this->xiaoxi("当前商户暂未设置支付类型费率，请先设置！", $chatid);
            }
            $all_zhifu = array();  //纯支付方式的量
            $all_tongdao = array(); //纯设置通道的量
            $all_tongdao_zhifu = array();  //支付方式下的各个通道跑的数据

            $sql_info3 = "select username,usdt_str from pay_user where  uid ='" . $uid . "'";
            $order_query7 = $this->pdo->query($sql_info3);
            $chatinfo3 = $order_query7->fetchAll();
            $uidinfo2 = $chatinfo3[0];


            if (count($uid_arr) > 1) {

                $parameter = array(
                    'chat_id' => $chatid,
                    'parse_mode' => 'HTML',
                    'text' => "</b>当前群存在多个商户号,请先解绑，将商户分群后再操作！</b>",
                );
                $this->http_post_data('sendMessage', json_encode($parameter));
                exit();

                foreach ($uid_arr as $k => $v) {
                    $inline_keyboard_arr[$k] = array('text' => "下发商户:" . $v, "callback_data" => "结算下发商户_" . $v);
                }

                $keyboard = [
                    'inline_keyboard' => [
                        $inline_keyboard_arr
                    ]
                ];
                $parameter = array(
                    'chat_id' => $chatid,
                    'parse_mode' => 'HTML',
                    'text' => "请选择要下发昨日收益结算的商户",
                    'reply_markup' => $keyboard,

                );

                $this->http_post_data('sendMessage', json_encode($parameter));
                exit();

            } else {
                //查询次商户号昨日总收入信息：
                $sql_info = "select sum(getmoney) as getmoney from pay_order where status = '1' and uid ='" . $uid . "' and date='" . $today . "'";

                $order_query3 = $this->pdo->query($sql_info);
                $chatinfo = $order_query3->fetchAll();
                $order_today = round($chatinfo[0]['getmoney'], 2);
                if ($order_today <= 0) {

                    $message .= "<strong>💰收入结算:0u</strong>";
                    $parameter = array(
                        'chat_id' => $chatid,
                        'parse_mode' => 'HTML',
                        'text' => $message,
                    );


                    $this->http_post_data('sendMessage', json_encode($parameter));
                    exit();
                }


                //查看昨日总下发的记录 这里有一点需要注意，如果昨日存在有下发异常的 需要天使自己核对 手动下发：
                $zuori_sql = "select * from pay_jinrixiafa where status = '0' and pid ='" . $uid . "' and xiafatime='" . $today . "'";
                $zuorixiafa = $this->shujuku($zuori_sql);
                if ($zuorixiafa) {
                    $parameter = array(
                        'chat_id' => $chatid,
                        'parse_mode' => 'HTML',
                        'text' => "当前商户昨日存在实时下发" . $zuorixiafa[0]['money'] . "U异常！建议手动结算昨日收益！",
                    );
                    $this->http_post_data('sendMessage', json_encode($parameter));
                    exit();

                }

                //最日下发的数据
                $zuori_money = 0.00;
                $zuori_usdt = 0.00;

                //昨日收益数据分析：
                $sql_info = "select * from pay_order where status = '1' and uid ='" . $uid . "' and date='" . $today . "'";
                $order_query3 = $this->pdo->query($sql_info);
                $zuoorderinfo = $order_query3->fetchAll();
                $all_money = 0;
                foreach ($zuoorderinfo as $key => $value) {
                    $all_money += $value['money'];
                    //支付方式计算
                    $all_zhifu[$value['type']] += $value['money'];

                    //支付方式下的各个通道跑的数据：
                    $all_tongdao_zhifu[$value['type']][$value['channel']] += $value['money'];
                    if (array_key_exists($value['channel'], $tongdaoxinxi)) {
                        //通道费用计算
                        $all_tongdao[$value['channel']] += $value['money'];
                    }
                }
                $msg = "✅" . $todays2 . "量情况如下\r\n🆔商户号:" . $uid . "\r\n🧑🏻‍💼名字:" . $uidinfo2['username'] . "\r\n";

                if (count($all_zhifu) > 0) {
                    foreach ($all_zhifu as $kt => $vt) {
                        $sql_zhifu = "select showname from pay_type where  id ='" . $kt . "'";

                        $zhifu_fetch = $this->shujuku($sql_zhifu);

                        $zhifu_info = $zhifu_fetch[0]['showname'];
                        $msg .= "🔔" . $zhifu_info . "总量:" . $vt . "\r\n";
                    }

                }
                //$msg .= "💹总跑量:" . $all_money . "\r\n";

                $type = substr($fufonginfo, 0, 1);
                if ($type == "-") {
                    $changs = explode("-", $fufonginfo);
                    $shiji_huilv = $huilvinfo - $changs[1];
                } else {
                    $changs = explode("+", $fufonginfo);
                    $shiji_huilv = $huilvinfo + $changs[1];
                }
                $shiji_huilv_tousu = $shiji_huilv - 0.1;


                $all_usdt_m = 0;
                $all_fusdt_money = 0;
                $xiafa_str = "";
                $feilihoujiner = 0;
                foreach ($all_tongdao_zhifu as $kv => $vv) {
                    //$zhifu_info_arr[$kv]
                    //$msg .= "\r\n📮" . $zhifu_info_arr[$kv] . "跑量如下：\r\n\r\n";
                    foreach ($vv as $kp => $vp) {
                        $channel_sql = "select id,name from pay_channel where id='" . $kp . "'";
                        $channel_info_query = $this->shujuku($channel_sql);
                        $channel_info = $channel_info_query[0];
                        // $msg .= "(" . $channel_info['id'] . ")" . $channel_info['name'] . ":" . $vp . "\r\n";
                        if (array_key_exists($kp, $tongdaoxinxi)) {

                            $zhifu_lixi = $tongdaoxinxi[$kp];

                        } else {
                            $zhifu_lixi = $zhifuxinxi[$kv];

                        }
                        $type = substr($fufonginfo, 0, 1);

                        $jisuan = ($vp * $zhifu_lixi * $fenchenginfo);
                        $feilihoujiner += round(($vp * $zhifu_lixi * $fenchenginfo), 2);
                        //$msg .= $vp . "*" . $zhifu_lixi . "*" . $fenchenginfo . "/(" . $shiji_huilv . ")=" . $jisuan . "U\r\n\r\n";

                        $xiafa_str .= $jisuan . "+";

                        $all_usdt_m += $jisuan;
                        $all_fusdt_money += $vp;
                    }
                }

                $msg .= "💹总跑量:" . $all_money . "元\r\n";
                $msg .= "💹费率后总额:" . $all_usdt_m . "元\r\n\r\n";
                $msg .= "➖➖➖➖➖➖➖➖➖\r\n";
                $msg .= "不可下发金额\r\n";


                $tousu_info2 = "select * from pay_usertousu where pid ='" . $uid . "'";
                $order_tousu2 = $this->pdo->query($tousu_info2);
                $tousu_m2 = $order_tousu2->fetchAll();
                $tousu_today = 0;
                $tousu_today2 = 0;
                $tousu_U = 0;

                //查看今日下发数据记录：
                $jinri_info = "select money,jutishijian,feiu_money,feilv from pay_jinrixiafa where status='1' and pid ='" . $uid . "' and xiafatime='" . $today . "' and chatid='" . $chatid . "'";
                $order_jinri = $this->pdo->query($jinri_info);
                $tjinri_arr = $order_jinri->fetchAll();
                $all_jinri_xiafa = 0.00;


                if ($tjinri_arr) {

                    $msg .= "\r\n📮" . $todays2 . "下发历史记录" . "\r\n";
                    foreach ($tjinri_arr as $kj => $vj) {
                        $zuori_money += $vj['all_feiu_money'];
                        $zuori_usdt += $vj['money'];


                        $ti = date('H:i:s', $vj['jutishijian']);
                        $msg .= "🔈" . $ti . " 已下发：" . $vj['feiu_money'] . "/" . $vj['feilv'] . "/" . $vj['money'] . "\r\n";
                        $all_jinri_xiafa += $vj['feiu_money'];

                        $xiafa_str .= "-" . $vj['feiu_money'];
                    }
                }

                $msg .= "\r\n";
                $jinritimne = date("Y-m-d", time());

                foreach ($tousu_m2 as $k => $v) {
                    $tousu_today += $v['money'];
                    $time = date('m-d', strtotime($v['date']));
                    if ($v['status'] == "1") {
                        //已扣除
                        $pp = "已扣除";
                        //如果是今天扣的，要计算体现到出来：
                        if ($jinritimne == $v['koushijian']) {
                            $tousu_today2 += $v['money'];
                            $tousu_U += $v['money'];
                        }
                    } else {
                        //待扣除
                        $pp = "待扣除 ---- /delete_tousu_" . $v['id'];
                        $tousu_today2 += $v['money'];
                        $tousu_U += $v['money'];

                    }


                    $msg .= "❌" . $time . ":投诉退款:" . $v['money'] . "元  ----" . $pp . "\r\n";
                }


                //查看投诉退款数据：
                if ($tousu_U > 0) {
                    $tousu_U2 = $tousu_U;
                    $msg .= "❌合计待投诉退款:" . $tousu_today2 . "元/" . $shiji_huilv_tousu . "=" . $tousu_U2 . "U\r\n";
                } else {
                    $tousu_U2 = 0;
                }

                $xiafa_str = substr($xiafa_str, 0, -1);

                $xiafa_str .= "-" . $tousu_U2;


                $trx_info = "select * from pay_usertrx";
                $trx_jinri = $this->pdo->query($trx_info);
                $trx_arr = $trx_jinri->fetchAll();

                if ($trx_arr) {
                    $trx_shouxufei = $trx_arr[0]['trx'];
                } else {
                    $trx_shouxufei = 0.00;
                }

                $bukexiafaheji_zuoro = $all_jinri_xiafa + $tousu_today2;
                $msg .= "\r\n💹不可下发金额合计：" . $bukexiafaheji_zuoro . "元\r\n\r\n";
                $msg .= "➖➖➖➖➖➖➖➖➖\r\n";
                $msg .= "下发扣除费用\r\n\r\n";
                $msg .= "🔄Trx手续费=" . $trx_shouxufei . "U\r\n\r\n";
                $msg .= "➖➖➖➖➖➖➖➖➖\r\n";
                $xiafa_str .= "-" . $trx_shouxufei;

                // $msg .= "\r\n🈴合计下发:";

                // $jie_all_jin_u = $all_jinri_xiafa > 0 ? round($all_jinri_xiafa, 2) : 0;
                // $jie_all_tou_u = $tousu_U2 > 0 ? round($tousu_U2, 2) : 0;
                // $jie_all_usdt_m = round($all_usdt_m, 2);
                // $keyixiafa = $jie_all_usdt_m - $jie_all_jin_u - $jie_all_tou_u - round($trx_shouxufei, 2);
                //$keyixiafa = $keyixiafa>0?round($keyixiafa,2):0;
                //$this->xiaoxi($keyixiafa,$chatid);
                $keyixiafa_value = $feilihoujiner - $bukexiafaheji_zuoro;
                $keyixiafa_str = $feilihoujiner . " - " . $bukexiafaheji_zuoro . " = " . $keyixiafa_value;

                $msg .= "🈴当前可下发:" . $keyixiafa_str . "元";


                //实际下发：
                $shijixiafa_value = (floor((($keyixiafa_value / $shiji_huilv) * 100)) / 100) - $trx_shouxufei;
                $shijixiafa_str = $keyixiafa_value . "/" . $shiji_huilv . " - " . $trx_shouxufei . " = " . $shijixiafa_value;

                $msg .= "\r\n🈴实际下发:" . $shijixiafa_str . "U";


                //$msg .= "\r\n" . $xiafa_str . "=" . $keyixiafa . "U";
                //  $msg .= $keyixiafa . "U";
                $msg .= "\r\n✅下发地址:\r\n" . $uidinfo2['usdt_str'];


                //查询结算是否已经下发：
                $sql_info_u = "select * from pay_zuorixiafau where pid ='" . $uid . "' and xiafatime='" . $today . "' and status='1'";


                $order_query_user_u = $this->pdo->query($sql_info_u);
                $xiafa_i_u = $order_query_user_u->fetchAll();

                $xiafade_day = date("d");
                if ($xiafa_i_u) {
                    $inline_keyboard_arr[0] = array('text' => "收益已清算", "callback_data" => "yijingxiafa_" . $uid);
                } else {
                    $inline_keyboard_arr[0] = array('text' => "确定下发:" . $shijixiafa_value . "U", "callback_data" => "zuotianxiafa_user_" . $uid . "&&" . $shijixiafa_value . "!!!" . $xiafade_day);
                }
                $inline_keyboard_arr2[0] = array('text' => "查详细账单", "callback_data" => "chakanzuorixiangxi_" . $uid);


            }


            $keyboard = [
                'inline_keyboard' => [
                    $inline_keyboard_arr,
                    $inline_keyboard_arr2
                ]
            ];
            $parameter = array(
                'chat_id' => $chatid,
                'parse_mode' => 'HTML',
                'text' => $msg,
                'reply_markup' => $keyboard,
                "message_id" => $message_id,

            );

            $this->http_post_data('editMessageText', json_encode($parameter));

            exit();

        }

        if (strpos($text, 'chakanzuorixiangxi_') !== false) {

            /*$quanxian = "下发昨日结算收益";
            $this->quanxian($chatid, $from_id, $quanxian, $username);*/

            $sql_info = "select * from pay_botsettle where chatid ='" . $chatid . "'";

            $order_query2 = $this->pdo->query($sql_info);
            $chatinfo = $order_query2->fetchAll();

            if (!$chatinfo) {
                $this->xiaoxi("该群暂未绑定商户号，请输入快捷命令：/bd商户号", $chatid);
            }
            $uid = $chatinfo['0']['merchant'];
            $uid_end = $uid;

            if ($this->kaiqi_teshu_xiafa) {
                $nayitian = $this->teshu_riqi;
                $today = date("Y-m-d", strtotime(date($nayitian)));
                $todays = date("Y年m月d日", strtotime(date($nayitian)));
                $todays2 = date("m月d日", strtotime(date($nayitian)));
            } else {
                $today = date("Y-m-d", strtotime("-1 day"));
                $todays = date("Y年m月d日", strtotime("-1 day"));
                $todays2 = date("m月d日", strtotime("-1 day"));
            }


            $uid_arr = explode("|", $uid);

            $huilvinfo = $this->huilvinfo("99999", "99999");
            $fufonginfo = $this->fudonginfo($uid, $chatid);
            $fenchenginfo = $this->fenchenginfo($uid, $chatid);

            $tongdaoxinxi = $this->tongdaoxinxi($uid, $chatid);
            $zhifuxinxi = $this->zhifuxinxi($uid, $chatid);

            $sql_zhifu = "select id,showname from pay_type";

            $zhifu_fetch = $this->shujuku($sql_zhifu);
            $zhifu_info_arr = array();
            foreach ($zhifu_fetch as $kp => $vp) {
                $zhifu_info_arr[$vp['id']] = $vp['showname'];
            }

            if (count($zhifuxinxi) <= 0) {
                $this->xiaoxi("当前商户暂未设置支付类型费率，请先设置！", $chatid);
            }
            $all_zhifu = array();  //纯支付方式的量
            $all_tongdao = array(); //纯设置通道的量
            $all_tongdao_zhifu = array();  //支付方式下的各个通道跑的数据

            $sql_info3 = "select username,usdt_str from pay_user where  uid ='" . $uid . "'";
            $order_query7 = $this->pdo->query($sql_info3);
            $chatinfo3 = $order_query7->fetchAll();
            $uidinfo2 = $chatinfo3[0];


            if (count($uid_arr) > 1) {

                $parameter = array(
                    'chat_id' => $chatid,
                    'parse_mode' => 'HTML',
                    'text' => "</b>当前群存在多个商户号,请先解绑，将商户分群后再操作！</b>",
                );
                $this->http_post_data('sendMessage', json_encode($parameter));
                exit();
            } else {
                //查询次商户号昨日总收入信息：
                $sql_info = "select sum(getmoney) as getmoney from pay_order where status = '1' and uid ='" . $uid . "' and date='" . $today . "'";

                $order_query3 = $this->pdo->query($sql_info);
                $chatinfo = $order_query3->fetchAll();
                $order_today = round($chatinfo[0]['getmoney'], 2);
                if ($order_today <= 0) {

                    $message .= "<strong>💰收入结算:0u</strong>";
                    $parameter = array(
                        'chat_id' => $chatid,
                        'parse_mode' => 'HTML',
                        'text' => $message,
                    );


                    $this->http_post_data('sendMessage', json_encode($parameter));
                    exit();
                }


                //查看昨日总下发的记录 这里有一点需要注意，如果昨日存在有下发异常的 需要天使自己核对 手动下发：
                $zuori_sql = "select * from pay_jinrixiafa where status = '0' and pid ='" . $uid . "' and xiafatime='" . $today . "'";
                $zuorixiafa = $this->shujuku($zuori_sql);
                if ($zuorixiafa) {
                    $parameter = array(
                        'chat_id' => $chatid,
                        'parse_mode' => 'HTML',
                        'text' => "当前商户昨日存在实时下发" . $zuorixiafa[0]['money'] . "U异常！建议手动结算昨日收益！",
                    );
                    $this->http_post_data('sendMessage', json_encode($parameter));
                    exit();

                }

                //最日下发的数据
                $zuori_money = 0.00;
                $zuori_usdt = 0.00;

                //昨日收益数据分析：
                $sql_info = "select * from pay_order where status = '1' and uid ='" . $uid . "' and date='" . $today . "'";
                $order_query3 = $this->pdo->query($sql_info);
                $zuoorderinfo = $order_query3->fetchAll();
                $all_money = 0;
                foreach ($zuoorderinfo as $key => $value) {
                    $all_money += $value['money'];
                    //支付方式计算
                    $all_zhifu[$value['type']] += $value['money'];

                    //支付方式下的各个通道跑的数据：
                    $all_tongdao_zhifu[$value['type']][$value['channel']] += $value['money'];
                    if (array_key_exists($value['channel'], $tongdaoxinxi)) {
                        //通道费用计算
                        $all_tongdao[$value['channel']] += $value['money'];
                    }
                }
                $msg = "✅" . $todays2 . "跑量情况如下\r\n🆔商户号:" . $uid . "\r\n🧑🏻‍💼名字:" . $uidinfo2['username'] . "\r\n";

                if (count($all_zhifu) > 0) {
                    foreach ($all_zhifu as $kt => $vt) {
                        $sql_zhifu = "select showname from pay_type where  id ='" . $kt . "'";

                        $zhifu_fetch = $this->shujuku($sql_zhifu);

                        $zhifu_info = $zhifu_fetch[0]['showname'];
                        $msg .= "🔔" . $zhifu_info . "总量:" . $vt . "\r\n";
                    }

                }
                $msg .= "💹总跑量:" . $all_money . "\r\n";
                $msg .= "➖➖➖➖➖➖➖➖➖\r\n";

                $type = substr($fufonginfo, 0, 1);
                if ($type == "-") {
                    $changs = explode("-", $fufonginfo);
                    $shiji_huilv = $huilvinfo - $changs[1];
                } else {
                    $changs = explode("+", $fufonginfo);
                    $shiji_huilv = $huilvinfo + $changs[1];
                }
                $shiji_huilv_tousu = $shiji_huilv - 0.1;


                $all_usdt_m = 0;
                $all_fusdt_money = 0;
                $xiafa_str = "";

                foreach ($all_tongdao_zhifu as $kv => $vv) {
                    $zhifuleixing_jisuanqian = 0;
                    $zhifuleixing_jisuanqianhou = 0;

                    $msg .= "\r\n📮" . $zhifu_info_arr[$kv] . "跑量如下：\r\n\r\n";
                    foreach ($vv as $kp => $vp) {
                        $channel_sql = "select id,name from pay_channel where id='" . $kp . "'";
                        $channel_info_query = $this->shujuku($channel_sql);
                        $channel_info = $channel_info_query[0];
                        $msg .= "(" . $channel_info['id'] . ")" . $channel_info['name'] . ":" . $vp . "\r\n";
                        if (array_key_exists($kp, $tongdaoxinxi)) {

                            $zhifu_lixi = $tongdaoxinxi[$kp];

                        } else {
                            $zhifu_lixi = $zhifuxinxi[$kv];

                        }
                        $type = substr($fufonginfo, 0, 1);

                        //$jisuan = round(($vp * $zhifu_lixi * $fenchenginfo) / ($shiji_huilv), 2);
                        $jisuan = ($vp * $zhifu_lixi * $fenchenginfo);
                        $msg .= $vp . "*" . $zhifu_lixi . "*" . $fenchenginfo . " = " . $jisuan . "元\r\n\r\n";

                        $xiafa_str .= $jisuan . "+";

                        $all_usdt_m += $jisuan;
                        $all_fusdt_money += $vp;

                        $zhifuleixing_jisuanqian += $vp;
                        $zhifuleixing_jisuanqianhou += $jisuan;
                    }
                    $msg .= "💹" . $zhifu_info_arr[$kv] . "总跑量:" . $zhifuleixing_jisuanqian . "元\r\n💹" . $zhifu_info_arr[$kv] . "费率后:" . $zhifuleixing_jisuanqianhou . "元\r\n";
                    $msg .= "➖➖➖➖➖➖➖➖➖\r\n";


                }

                $msg .= "\r\n💹昨日费率后总额: " . $all_usdt_m . "元\r\n\r\n";
                $msg .= "➖➖➖➖➖➖➖➖➖";
                $msg .= "\r\n不可下发金额:\r\n";


                $tousu_info2 = "select * from pay_usertousu where pid ='" . $uid . "'";
                $order_tousu2 = $this->pdo->query($tousu_info2);
                $tousu_m2 = $order_tousu2->fetchAll();
                $tousu_today = 0;
                $tousu_today2 = 0;
                $tousu_U = 0;


                //查看今日的投诉金额：
                /*$tousu_info = "select sum(money) as tousumoney from pay_usertousu where status='0' and  pid ='" . $uid . "' and date='" . $today . "'";
                $order_tousu = $this->pdo->query($tousu_info);
                $tousu_m = $order_tousu->fetchAll();

                $tousu_today = $tousu_m[0]['tousumoney']>0?$tousu_m[0]['tousumoney']:0;*/


                //查看投诉退款数据：


                $xiafa_str = substr($xiafa_str, 0, -1);

                $xiafa_str .= "-" . $tousu_U2;

                //查看今日下发数据记录：
                $jinri_info = "select money,jutishijian,feiu_money,feilv from pay_jinrixiafa where status='1' and pid ='" . $uid . "' and xiafatime='" . $today . "' and chatid='" . $chatid . "'";
                $order_jinri = $this->pdo->query($jinri_info);
                $tjinri_arr = $order_jinri->fetchAll();
                $all_jinri_xiafa = 0.00;


                if ($tjinri_arr) {

                    $msg .= "\r\n📮昨日下发历史记录" . "\r\n";
                    foreach ($tjinri_arr as $kj => $vj) {
                        $zuori_money += $vj['all_feiu_money'];
                        $zuori_usdt += $vj['money'];


                        $ti = date('H:i:s', $vj['jutishijian']);
                        //$msg .= "🔈" . $ti . " 已下发：" . $vj['money'] . "U\r\n";
                        $msg .= "🔈" . $ti . " 成功下发：" . $vj['feiu_money'] . "/" . $vj['feilv'] . "/" . $vj['money'] . "U(含手续费)\r\n";

                        $all_jinri_xiafa += $vj['feiu_money'];

                        $xiafa_str .= "-" . $vj['feiu_money'];
                    }
                }
                $msg .= "\r\n";
                //$jinri_tojiesuan =round($this->tojiesuan/$shiji_huilv,2);
                // $msg .= "\r\n❌t0不可结算限额:" . $this->tojiesuan . "元\r\n\r\n";
                $jinritimne = date("Y-m-d", time());
                foreach ($tousu_m2 as $k => $v) {
                    $tousu_today += $v['money'];
                    $time = date('m-d', strtotime($v['date']));

                    if ($v['status'] == "1") {
                        //已扣除
                        $pp = "已扣除";
                        //如果是今天扣的，要计算体现到出来：
                        if ($jinritimne == $v['koushijian']) {
                            $tousu_today2 += $v['money'];
                            $tousu_U += $v['money'];
                        }
                    } else {
                        //待扣除
                        $pp = "待扣除 ---- /delete_tousu_" . $v['id'];
                        $tousu_today2 += $v['money'];
                        $tousu_U += $v['money'];

                    }


                    $msg .= "❌" . $time . ":投诉退款:" . $v['money'] . "元  ----" . $pp . "\r\n";
                }

                if ($tousu_U > 0) {
                    $tousu_U2 = $tousu_U;
                    // $msg .= "❌合计待投诉退款:" . $tousu_today2 . "元/" . $shiji_huilv_tousu . "=" . $tousu_U2 . "U\r\n";
                    $msg .= "❌合计待投诉退款:" . $tousu_U . "元\r\n";

                } else {
                    $tousu_U2 = 0;
                }


                $trx_info = "select * from pay_usertrx";
                $trx_jinri = $this->pdo->query($trx_info);
                $trx_arr = $trx_jinri->fetchAll();

                if ($trx_arr) {
                    $trx_shouxufei = $trx_arr[0]['trx'];
                } else {
                    $trx_shouxufei = 0.00;
                }

                $bukexiafaheji = $all_jinri_xiafa + $tousu_U2;
                $msg .= "\r\n💹不可下发金额合计：" . $bukexiafaheji . "元\r\n\r\n";
                $msg .= "➖➖➖➖➖➖➖➖➖\r\n下发扣除费用\r\n";
                $msg .= "\r\n🔄Trx手续费=" . $trx_shouxufei . "U(每次下发)\r\n";


                $keyixiafa = round($all_usdt_m, 2) - round($all_jinri_xiafa, 2) - $tousu_U - round($trx_shouxufei, 2);

                $jinrike = round($all_usdt_m, 2) - $all_jinri_xiafa;
                $xiafa_str2 = round($all_usdt_m, 2) . "-" . $all_jinri_xiafa . "-" . $tousu_U . "-" . round($trx_shouxufei, 2) . "=" . round($keyixiafa, 2);


                //当前可下发:   总金额-已经下发的-限额
                $keyixiafa_value = $all_usdt_m - $bukexiafaheji;
                $keyixiafa_str = $all_usdt_m . "-" . $bukexiafaheji . '=' . $keyixiafa_value;

                //实际下发：当前可下发-手续费-投诉金额
                $shijixiafa_value = (floor((($keyixiafa_value / $shiji_huilv) * 100)) / 100) - round($trx_shouxufei, 2);
                $shijixiafa_str = $keyixiafa_value . "/" . $shiji_huilv . " - " . round($trx_shouxufei, 2) . "=" . $shijixiafa_value;


                //下发了多少金额： 总金额-已经下发-投诉金额-限额+手续费
                $shijixiafa_jiner_rnb = $all_usdt_m - $all_jinri_xiafa - $tousu_U - $this->tojiesuan;
                $msg .= "\r\n➖➖➖➖➖➖➖➖➖\r\n";

                //当前可下发=今天费率后总额-不可下发金额合计

                $msg .= "\r\n🈴当前可下发:" . $keyixiafa_str . "元\r\n\r\n";
                // $msg .= "投诉冻结余额:" . $tousu_U . "\r\n";
                //$msg .= "trx手续费:" . $trx_shouxufei . "U\r\n";
                $msg .= "🈴实际可下发:" . $shijixiafa_str . "U\r\n";
                $msg .= "\r\n✅下发地址\r\n" . $uidinfo2['usdt_str'];


                // $msg .= "\r\n🈴合计下发:";

                // $jie_all_jin_u = $all_jinri_xiafa > 0 ? round($all_jinri_xiafa, 2) : 0;
                // $jie_all_tou_u = $tousu_U2 > 0 ? round($tousu_U2, 2) : 0;
                // $jie_all_usdt_m = round($all_usdt_m, 2);
                // $keyixiafa = $jie_all_usdt_m - $jie_all_jin_u - $jie_all_tou_u - round($trx_shouxufei, 2);
                // //$keyixiafa = $keyixiafa>0?round($keyixiafa,2):0;
                // //$this->xiaoxi($keyixiafa,$chatid);

                // $msg .= "\r\n" . $xiafa_str . "=" . $keyixiafa . "U";
                // $msg .= "\r\n✅下发地址:\r\n" . $uidinfo2['usdt_str'];


                //查询结算是否已经下发：
                $sql_info_u = "select * from pay_zuorixiafau where pid ='" . $uid . "' and xiafatime='" . $today . "' and status='1'";

                $xiafade_day = date("d");
                $order_query_user_u = $this->pdo->query($sql_info_u);
                $xiafa_i_u = $order_query_user_u->fetchAll();
                if ($xiafa_i_u) {
                    $inline_keyboard_arr[0] = array('text' => "收益已清算", "callback_data" => "yijingxiafa_" . $uid);
                } else {
                    $inline_keyboard_arr[0] = array('text' => "确定下发:" . $shijixiafa_value . "U", "callback_data" => "zuotianxiafa_user_" . $uid . "&&" . $shijixiafa_value . "!!!" . $xiafade_day);
                }
                $inline_keyboard_arr2[0] = array('text' => "查简约账单", "callback_data" => "chakanzuorijianyue_" . $uid);

            }


            $keyboard = [
                'inline_keyboard' => [
                    $inline_keyboard_arr,
                    $inline_keyboard_arr2
                ]
            ];
            $parameter = array(
                'chat_id' => $chatid,
                'parse_mode' => 'HTML',
                'text' => $msg,
                'reply_markup' => $keyboard,
                "message_id" => $message_id,

            );

            $this->http_post_data('editMessageText', json_encode($parameter));
            exit();

        }
        if (strpos($text, 'kaiqiordertuisong') !== false) {
            $quanxian = "订单推送设置";
            $this->quanxian($chatid, $userid, $quanxian, $username);
            $pp = explode("###", $text);
            $chang_status = $pp[1];
            $res = $this->pdo->exec("UPDATE pay_botsettle SET kaiqi='" . $chang_status . "' WHERE merchant='" . $user_pid . "'");

            $chang_str = $chang_status > 1 ? "关闭" : "开启";
            $msg = "修改状态成功，当前状态为：" . $chang_str;
            $this->xiaoxi($msg, $chatid);

        }
        if (strpos($text, 'jiechuxiafaxianzhi_') !== false) {

            $quanxian = "解除昨日下发限制";
            $this->quanxian($chatid, $userid, $quanxian, $username);
            //$today = date("Y-m-d", strtotime("-1 day"));
            if ($this->kaiqi_teshu_xiafa) {
                $teshu_riqi = $this->teshu_riqi;
                $not_time = date("Y-m-d", strtotime(date($teshu_riqi)));
            } else {
                $not_time = date("Y-m-d", strtotime("-1 day"));
            }
            $uid_arr = explode("_", $text);
            $uids = $uid_arr[1];
            $set_sql = "DELETE FROM pay_xiafau where uid='" . $uids . "' and date='" . $not_time . "'";
            $this->pdo->exec($set_sql);
            $set_sql2 = "DELETE FROM pay_zuorixiafau where pid='" . $uids . "' and xiafatime='" . $not_time . "'";
            $this->pdo->exec($set_sql2);

            $parameter = array(
                'chat_id' => $chatid,
                'parse_mode' => 'HTML',
                'text' => "已取消商户号:".$uids."的下发限制，可以重新下发!"

            );
            $this->http_post_data('sendMessage', json_encode($parameter));
        }
        if (strpos($text, 'jiechujinrixiafaxianzhi_') !== false) {

            $quanxian = "解除今日下发限制";
            $this->quanxian($chatid, $userid, $quanxian, $username);
            $uid_arr = explode("_", $text);
            $insert_id = $uid_arr[1];
            $set_sql = "DELETE FROM pay_jinrixiafa where id='" . $insert_id . "'";
            $this->pdo->exec($set_sql);

            $parameter = array(
                'chat_id' => $chatid,
                'parse_mode' => 'HTML',
                'text' => "已取消当前失败的下发限制，可以重新下发!"

            );
            $this->http_post_data('sendMessage', json_encode($parameter));
        }

        if (strpos($text, '回u通知设置') !== false) {
            $this->chaojiyonghuquanxian($from_id, $chatid);


            if (!empty($chatinfo['0']['atyonghu'])) {
                //已經綁定群了：
                $parameter = array(
                    'chat_id' => $chatid,
                    'parse_mode' => 'HTML',
                    'text' => "已绑定如下通知：" . $chatinfo[0]['atyonghu'] . "已经回U\r\n  命令：/tongzhidel 可以删除此通知设置"

                );
                $this->http_post_data('sendMessage', json_encode($parameter));
                exit();
            }


            //获取录入信息：
            $parameter = array(
                'chat_id' => $chatid,
                'parse_mode' => 'HTML',
                'text' => '请输入:/bdid@用户名' . "\r\n\r\n" . '例如：/bdid@fu_008'
            );
            $this->http_post_data('sendMessage', json_encode($parameter));
            exit();
        }
        if (strpos($text, '订单推送设置') !== false) {


            $quanxian = "订单推送设置";
            $this->quanxian($chatid, $userid, $quanxian, $username);

            $kaiqi_status = $chatinfo['0']['kaiqi'] > 1 ? "关闭" : "开启";

            $msg = "你当前订单推送设置状态为：<b>" . $kaiqi_status . "</b>\r\n\r\n请务必先关注@tianshipaybot,然后点击下方按钮开启订单推送";

            $inline_keyboard_arr3[0] = array('text' => "开启推送", "callback_data" => "kaiqiordertuisong###1");
            $inline_keyboard_arr3[1] = array('text' => "关闭推送", "callback_data" => "kaiqiordertuisong###2");

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
        }
        if (strpos($text, "chajintianshishi") !== false) {
            $saq = explode("###", $text);

            $this->callback_jiesuan($text, $chat_id, $from_id, $saq[1]);
        }
        if (strpos($text, "congshishixiafa") !== false) {
            $this->callback_xiafa($text, $chat_id, $from_id, $user_pid, 1);
        }

        if (strpos($text, "congzuorijiesuan") !== false) {
            $this->callback_xiafa($text, $chat_id, $from_id, $user_pid, 2);
        }

        //下发设置
        if (strpos($text, "下发设置") !== false) {
            $messages = "请选择下发设置类型";
            $inline_keyboard_arr3[0] = array('text' => "实时下发", "callback_data" => "congshishixiafa");
            $inline_keyboard_arr3[1] = array('text' => "昨日结算", "callback_data" => "congzuorijiesuan");

            $keyboard = [
                'inline_keyboard' => [
                    $inline_keyboard_arr3,

                ]
            ];
            $parameter = array(
                'chat_id' => $chat_id,
                'parse_mode' => 'HTML',
                'text' => $messages,
                'reply_markup' => $keyboard,
                'disable_web_page_preview' => true,

            );

            $this->http_post_data('sendMessage', json_encode($parameter));
            exit();

        }
        if (strpos($text, "wufaxiafa_user_") !== false) {
            $str_arr = explode("xiafa_user_", $text);
            $arr_new = explode("&&", $str_arr[1]);
            $pid = $arr_new[0];
            $usdt_m = $arr_new[1];
            //记录下发数据：再去调用下发数据接口：
            $this->xiaoxi("余额不足！", $chatid, '1', $data['callback_query']['id']);


        }
        //今日实时下发：
        if (strpos($text, "jinrixiafa_user_") !== false) {
            //"jinrixiafa_user_" . $uid_end . "&&" . $keyixiafa . "###" . $all_fusdt_money."#&#".$tousu_U2
            $quanxian = "实时下发";

            $this->quanxian($chatid, $userid, $quanxian, $username);
            $str_arr = explode("xiafa_user_", $text);
            $arr_new = explode("&&", $str_arr[1]);
            $pid = $arr_new[0];

            $usdt_m_arr = explode("###", $arr_new[1]);

            $usdt_m = $usdt_m_arr[0];
            $ppty_arr = explode("!!!", $usdt_m_arr[1]);
            $usdt_fm = $ppty_arr[0];
            if ($usdt_m <= 0) {
                $this->xiaoxi("余额不足！", $chatid);
            }
            $tousu_U2 = $ppty_arr[1];

            //记录下发数据：再去调用下发数据接口：

            $now_time = date("d");
            if ($now_time != $tousu_U2) {
                $this->xiaoxi("禁止跨日下发", $chatid);
            }

            $this->xiafausdt($pid, $usdt_m, $usdt_fm, $message_id, $chatid, $data, $chatinfo, '0', $tousu_U2);
        }
        //昨日下发：
        //" => "zuotianxiafa_user_" . $uid_end . "&&" . $keyixiafa."!!!".$xiafade_day
        if (strpos($text, "zuotianxiafa_user_") !== false) {
            $quanxian = "下发昨日结算收益";
            $this->quanxian($chatid, $userid, $quanxian, $username);

            $str_arr = explode("zuotianxiafa_user_", $text);
            $arr_new = explode("&&", $str_arr[1]);
            $pid = $arr_new[0];

            $arr_new_change = explode("!!!", $arr_new[1]);

            $usdt_m = $arr_new_change[0];
            $usdt_fm = 0;
            if ($usdt_m <= 0) {
                $this->xiaoxi("余额不足！", $chatid);
            }

            $usdt_m_xiafashijian = $arr_new_change[1];
            $jinris = date("d");
            // $this->xiaoxinoend($jinris,$chatid);
            // $this->xiaoxinoend($usdt_m, $chatid);
            // $this->xiaoxi($usdt_m_xiafashijian, $chatid);

            if ($jinris != $usdt_m_xiafashijian) {
                $this->xiaoxi("禁止跨日下发！", $chatid);
            }

            //记录下发数据：再去调用下发数据接口：
            $this->xiafausdt_zuori($pid, $usdt_m, $usdt_fm, $message_id, $chatid, $data, $chatinfo, '1');


        }
        if (strpos($text, "结算下发商户_") !== false) {


            $uid_arr = explode("商户_", $text);
            $uid = $uid_arr[1];

            $uid_end = $uid;


            $today = date("Y-m-d", strtotime("-1 day"));
            $todays = date("Y年m月d日");

            $huilvinfo = $this->huilvinfo("99999", "99999");
            $fufonginfo = $this->fudonginfo($chatinfo[0]['merchant'], $chatid);


            $fenchenginfo = $this->fenchenginfo($chatinfo[0]['merchant'], $chatid);


            $tongdaoxinxi = $this->tongdaoxinxi();
            $zhifuxinxi = $this->zhifuxinxi();


            $sql_zhifu = "select id,showname from pay_type";

            $zhifu_fetch = $this->shujuku($sql_zhifu);
            $zhifu_info_arr = array();
            foreach ($zhifu_fetch as $kp => $vp) {
                $zhifu_info_arr[$vp['id']] = $vp['showname'];
            }

            if (count($zhifuxinxi) <= 0) {
                $this->xiaoxi("当前商户暂未设置支付类型费率，请先设置！", $chatid);
            }
            $all_zhifu = array();  //纯支付方式的量
            $all_tongdao = array(); //纯设置通道的量

            $all_tongdao_zhifu = array();  //支付方式下的各个通道跑的数据


            //查询次商户号今日总收入信息：
            $sql_info = "select * from pay_order where status = '1' and uid ='" . $uid . "' and date='" . $today . "'";


            $order_query3 = $this->pdo->query($sql_info);
            $chatinfo = $order_query3->fetchAll();
            if (count($chatinfo) <= 0) {
                $this->xiaoxi("未查询到商户昨日支付订单成功数据记录！", $chatid);
            }


            $all_money = 0;
            foreach ($chatinfo as $key => $value) {
                $all_money += $value['money'];
                //支付方式计算

                $all_tongdao_zhifu[$value['type']][$value['channel']] += $value['money'];

            }
            $sql_info3 = "select username,usdt_str from pay_user where  uid ='" . $uid . "'";
            $order_query7 = $this->pdo->query($sql_info3);
            $chatinfo3 = $order_query7->fetchAll();
            $uidinfo2 = $chatinfo3[0];


            $msg = "✅" . $todays . "量情况如下\r\n🆔商户号:" . $uid . "\r\n🧑🏻‍💼名字:" . $uidinfo2['username'] . "\r\n";


            if (count($all_zhifu) > 0) {
                foreach ($all_zhifu as $kt => $vt) {
                    $sql_zhifu = "select showname from pay_type where  id ='" . $kt . "'";

                    $zhifu_fetch = $this->shujuku($sql_zhifu);

                    $zhifu_info = $zhifu_fetch[0]['showname'];
                    $msg .= "🔔" . $zhifu_info . "总量:" . $vt . "\r\n";
                }

            }


            //$this->xiaoxi(json_encode($all_tongdao_zhifu),$chat_id);

            if (count($all_tongdao_zhifu) <= 0) {
                $msg .= "暂无支付订单成功数据记录！";
                $this->xiaoxi($msg, $chatid);
                exit();
            }
            $msg .= "💹总跑量:" . $all_money . "\r\n";

            $type = substr($fufonginfo, 0, 1);


            if ($type == "-") {
                $changs = explode("-", $fufonginfo);
                $shiji_huilv = $huilvinfo - $changs[1];
            } else {
                $changs = explode("+", $fufonginfo);
                $shiji_huilv = $huilvinfo + $changs[1];
            }

            $all_usdt_m = 0;
            $all_fusdt_money = 0;
            $xiafa_str = "";

            foreach ($all_tongdao_zhifu as $kv => $vv) {
                //$zhifu_info_arr[$kv]
                $msg .= "\r\n📮" . $zhifu_info_arr[$kv] . "跑量如下：\r\n\r\n";
                foreach ($vv as $kp => $vp) {
                    $channel_sql = "select id,name from pay_channel where id='" . $kp . "'";
                    $channel_info_query = $this->shujuku($channel_sql);
                    $channel_info = $channel_info_query[0];
                    $msg .= "(" . $channel_info['id'] . ")" . $channel_info['name'] . ":" . $vp . "\r\n";
                    if (array_key_exists($kp, $tongdaoxinxi)) {

                        $zhifu_lixi = $tongdaoxinxi[$kp];

                    } else {
                        $zhifu_lixi = $zhifuxinxi[$kv];

                    }
                    $type = substr($fufonginfo, 0, 1);

                    $jisuan = round(($vp * $zhifu_lixi * $fenchenginfo) / ($shiji_huilv), 2);
                    $msg .= $vp . "*" . $zhifu_lixi . "*" . $fenchenginfo . "/(" . $shiji_huilv . ")=" . $jisuan . "U\r\n\r\n";

                    $xiafa_str .= $jisuan . "+";


                    $all_usdt_m += $jisuan;

                }
            }

            $tousu_info2 = "select * from pay_usertousu where pid ='" . $uid . "'";
            $order_tousu2 = $this->pdo->query($tousu_info2);
            $tousu_m2 = $order_tousu2->fetchAll();
            $tousu_today = 0;
            $tousu_today2 = 0;
            $tousu_U2 = 0;
            foreach ($tousu_m2 as $k => $v) {
                $time = date('m-d', strtotime($v['date']));
                $tousu_today += $v['money'];

                if ($v['status'] == "1") {
                    //已扣除
                    $pp = "已扣除";
                } else {
                    //待扣除
                    $pp = "待扣除 ---- /delete_tousu_" . $v['id'];
                    $tousu_today2 += $v['money'];
                    $tousu_U2 += round($v['money'] / $shiji_huilv, 2);

                }


                $msg .= "❌" . $time . ":投诉退款:" . $v['money'] . "元  ----" . $pp . "\r\n";
            }

            //查看今日的投诉金额：
            $tousu_info = "select sum(money) as tousumoney from pay_usertousu where status='0' and  pid ='" . $uid . "'";
            $order_tousu = $this->pdo->query($tousu_info);
            $tousu_m = $order_tousu->fetchAll();
            $tousu_today = round($tousu_m[0]['tousumoney'], 2);

            //查看投诉退款数据：
            if ($tousu_U2 > 0) {
                $tousu_U = $tousu_U2;
            } else {
                $tousu_U = 0;
            }

            $msg .= "❌合计待投诉退款:" . $tousu_today . "元/" . $shiji_huilv . "=" . $tousu_U . "U\r\n";


            //查看今日下发数据记录：
            $jinri_info = "select money,jutishijian from pay_jinrixiafa where status='1' and pid ='" . $uid . "' and xiafatime='" . $today . "' and chatid='" . $chatid . "'";
            $order_jinri = $this->pdo->query($jinri_info);
            $tjinri_arr = $order_jinri->fetchAll();
            $all_jinri_xiafa = 0.00;

            $xiafa_str = substr($xiafa_str, 0, -1);

            if ($tjinri_arr) {

                $msg .= "\r\n📮" . $todays . "下发历史记录" . "\r\n";
                foreach ($tjinri_arr as $kj => $vj) {
                    $ti = date('H:i:s', $vj['jutishijian']);
                    $msg .= "🔈" . $ti . " 已下发：" . $vj['money'] . "U\r\n";
                    $all_jinri_xiafa += $vj['money'];

                    $xiafa_str .= "-" . $vj['money'];
                }
            }
            $xiafa_str .= "-" . $tousu_U;

            $keyixiafa = round($all_usdt_m, 2) - round($all_jinri_xiafa, 2) - $tousu_U;
            $msg .= "\r\n🈴当前可下发:" . $xiafa_str . "=" . $keyixiafa . "U";
            $msg .= "\r\n✅下发地址:\r\n" . $uidinfo2['usdt_str'];

            //$this->xiaoxi($keyixiafa,$chatid);
            $xiafade_day = date("d");
            //查看下发地址：
            if ($keyixiafa > 0) {
                $inline_keyboard_arr[0] = array('text' => "立即结算:" . $keyixiafa . "U", "callback_data" => "zuotianxiafa_user_" . $uid_end . "&&" . $keyixiafa . "!!!" . $xiafade_day);

            } else {
                $inline_keyboard_arr[0] = array('text' => $todays . "收益下发成功", "callback_data" => "wufaxiafa_user_" . $uid_end);

            }


            $keyboard = [
                'inline_keyboard' => [
                    $inline_keyboard_arr
                ]
            ];
            $parameter = array(
                'chat_id' => $chatid,
                'parse_mode' => 'HTML',
                'text' => $msg,
                'reply_markup' => $keyboard,

            );

            $this->http_post_data('sendMessage', json_encode($parameter));
        }
        if (strpos($text, "订单拉取商户_") !== false) {

            /*$quanxian = "拉取订单";
            $this->quanxian($chatid, $userid, $quanxian,$username);*/

            $sql_info = "select * from pay_botsettle where chatid ='" . $chatid . "'";
            $order_query2 = $this->pdo->query($sql_info);
            $order_info2 = $order_query2->fetchAll();
            $pid2 = explode("订单拉取商户_", $text);
            $pid = $pid2[1];


            $sql_info2 = "select * from pay_user where uid ='" . $pid . "'";
            $order_query3 = $this->pdo->query($sql_info2);
            $order_info_gr = $order_query3->fetchAll();
            $gid = $order_info_gr['0']['gid'];

            $sql_info3 = "select * from pay_group where gid ='" . $gid . "'";
            $order_query4 = $this->pdo->query($sql_info3);
            $order_info_gp = $order_query4->fetchAll();

            $type_arr_json = $order_info_gp[0]['info'];
            $type_arr = json_decode($type_arr_json, true);
            $info = "<b>请选择支付方式:</b>\r\n\r\n";
            $ps = 1;

            $pss = 0;
            $inline_keyboard_arr3 = array();
            foreach ($type_arr as $k => $v) {

                $sql_info4 = "select * from pay_type where id ='" . $k . "'";
                $order_query5 = $this->pdo->query($sql_info4);
                $order_info_gp2 = $order_query5->fetchAll();
                //$ids = $v['channel']
                if ($v['channel'] > 0) {
                    $showname = $order_info_gp2[0]['showname'];

                    if ($v['type'] == "channel") {
                        //方式

                        //$info .=$ps.":".$showname."\r\n";

                        $inline_keyboard_arr3[$pss] = array('text' => $showname, "callback_data" => "zhifu_channel_" . $k . "###" . $pid);
                    } else {
                        //轮询
                        //$info .=$ps.":".$showname."\r\n";

                        $inline_keyboard_arr3[$pss] = array('text' => $showname, "callback_data" => "zhifu_roll_" . $k . "###" . $pid);
                    }
                    $ps += 1;
                    $pss += 1;
                }


            }


            $keyboard = [
                'inline_keyboard' => [
                    $inline_keyboard_arr3,
                ]
            ];
            $parameter = array(
                'chat_id' => $chatid,
                'parse_mode' => 'HTML',
                'text' => $info,
                'reply_markup' => $keyboard,
                'disable_web_page_preview' => true,

            );

            $this->http_post_data('sendMessage', json_encode($parameter));
            exit();


        }
        /*if (strpos($text, "实时下发商户_") !== false) {


            $uid_arr = explode("商户_", $text);
            $uid = $uid_arr[1];

            $uid_end = $uid;


            $today = date("Y-m-d");
            $todays = date("Y年m月d日");

            $huilvinfo = $this->huilvinfo("99999", "99999");
            $fufonginfo = $this->fudonginfo($chatinfo[0]['merchant'], $chatid);


            $fenchenginfo = $this->fenchenginfo($chatinfo[0]['merchant'], $chatid);


            $tongdaoxinxi = $this->tongdaoxinxi();
            $zhifuxinxi = $this->zhifuxinxi();


            $sql_zhifu = "select id,showname from pay_type";

            $zhifu_fetch = $this->shujuku($sql_zhifu);
            $zhifu_info_arr = array();
            foreach ($zhifu_fetch as $kp => $vp) {
                $zhifu_info_arr[$vp['id']] = $vp['showname'];
            }

            if (count($zhifuxinxi) <= 0) {
                $this->xiaoxi("当前商户暂未设置支付类型费率，请先设置！", $chatid);
            }
            $all_zhifu = array();  //纯支付方式的量
            $all_tongdao = array(); //纯设置通道的量

            $all_tongdao_zhifu = array();  //支付方式下的各个通道跑的数据


            //查询次商户号今日总收入信息：
            $sql_info = "select * from pay_order where status = '1' and uid ='" . $uid . "' and date='" . $today . "'";


            $order_query3 = $this->pdo->query($sql_info);
            $chatinfo = $order_query3->fetchAll();
            if (count($chatinfo) <= 0) {
                $this->xiaoxi("未查询到今日支付订单成功数据记录！", $chatid);
            }


            $all_money = 0;
            foreach ($chatinfo as $key => $value) {
                $all_money += $value['money'];
                //支付方式计算

                $all_tongdao_zhifu[$value['type']][$value['channel']] += $value['money'];

            }
            $sql_info3 = "select username,usdt_str from pay_user where  uid ='" . $uid . "'";
            $order_query7 = $this->pdo->query($sql_info3);
            $chatinfo3 = $order_query7->fetchAll();
            $uidinfo2 = $chatinfo3[0];


            $msg = "✅今天跑量情况如下\r\n🆔商户号:" . $uid . "\r\n🧑🏻‍💼名字:" . $uidinfo2['username'] . "\r\n";


            if (count($all_zhifu) > 0) {
                foreach ($all_zhifu as $kt => $vt) {
                    $sql_zhifu = "select showname from pay_type where  id ='" . $kt . "'";

                    $zhifu_fetch = $this->shujuku($sql_zhifu);

                    $zhifu_info = $zhifu_fetch[0]['showname'];
                    $msg .= "🔔" . $zhifu_info . "总量:" . $vt . "\r\n";
                }

            }


            //$this->xiaoxi(json_encode($all_tongdao_zhifu),$chat_id);

            if (count($all_tongdao_zhifu) <= 0) {
                $msg .= "暂无支付订单成功数据记录！";
                $this->xiaoxi($msg, $chatid);
                exit();
            }
            $msg .= "💹总跑量:" . $all_money . "\r\n";

            $type = substr($fufonginfo, 0, 1);


            if ($type == "-") {
                $changs = explode("-", $fufonginfo);
                $shiji_huilv = $huilvinfo - $changs[1];
            } else {
                $changs = explode("+", $fufonginfo);
                $shiji_huilv = $huilvinfo + $changs[1];
            }

            $all_usdt_m = 0;
            $all_fusdt_money = 0;
            $xiafa_str = "";

            foreach ($all_tongdao_zhifu as $kv => $vv) {
                //$zhifu_info_arr[$kv]
                $msg .= "\r\n📮" . $zhifu_info_arr[$kv] . "跑量如下：\r\n\r\n";
                foreach ($vv as $kp => $vp) {
                    $channel_sql = "select id,name from pay_channel where id='" . $kp . "'";
                    $channel_info_query = $this->shujuku($channel_sql);
                    $channel_info = $channel_info_query[0];
                    $msg .= "(" . $channel_info['id'] . ")" . $channel_info['name'] . ":" . $vp . "\r\n";
                    if (array_key_exists($kp, $tongdaoxinxi)) {

                        $zhifu_lixi = $tongdaoxinxi[$kp];

                    } else {
                        $zhifu_lixi = $zhifuxinxi[$kv];

                    }
                    $type = substr($fufonginfo, 0, 1);

                    $jisuan = round(($vp * $zhifu_lixi * $fenchenginfo) / ($shiji_huilv), 2);
                    $msg .= $vp . "*" . $zhifu_lixi . "*" . $fenchenginfo . "/(" . $shiji_huilv . ")=" . $jisuan . "U\r\n\r\n";

                    $xiafa_str .= $jisuan . "+";

                    $all_fusdt_money += $vp;
                    $all_usdt_m += $jisuan;

                }
            }
            //查看今日的投诉金额：
            $tousu_info = "select sum(money) as tousumoney from pay_usertousu where status='0' and  pid ='" . $uid . "'";
            $order_tousu = $this->pdo->query($tousu_info);
            $tousu_m = $order_tousu->fetchAll();
            $tousu_today = round($tousu_m[0]['tousumoney'], 2);

            //查看投诉退款数据：
            if($tousu_today>0){
                $tousu_U =round(($tousu_today / $shiji_huilv),2);

            }else{
                $tousu_U =0;
            }

            $msg .= "❌投诉退款:" . $tousu_today . "元/" . $shiji_huilv . "=" . $tousu_U . "U\r\n";


            //查看今日下发数据记录：
            $jinri_info = "select money,jutishijian from pay_jinrixiafa where status='1' and pid ='" . $uid . "' and xiafatime='" . $today . "' and chatid='" . $chatid . "'";
            $order_jinri = $this->pdo->query($jinri_info);
            $tjinri_arr = $order_jinri->fetchAll();
            $all_jinri_xiafa = 0.00;

            $xiafa_str = substr($xiafa_str, 0, -1);

            if ($tjinri_arr) {

                $msg .= "\r\n📮今天下发历史记录" . "\r\n";
                foreach ($tjinri_arr as $kj => $vj) {
                    $ti = date('H:i:s', $vj['jutishijian']);
                    $msg .= "🔈" . $ti . " 成功下发：" . $vj['money'] . "U\r\n";
                    $all_jinri_xiafa += $vj['money'];

                    $xiafa_str .= "-" . $vj['money'];
                }
            }

            $xiafa_str .="-".$tousu_U;
              $this->xiaoxi($tousu_U,$chatid);

            $keyixiafa = round($all_usdt_m, 2) - round($all_jinri_xiafa, 2)-$tousu_U;
            $msg .= "\r\n🈴当前可下发:" . $xiafa_str . "=" . $keyixiafa . "U";
            $msg .= "\r\n✅下发地址:\r\n" . $uidinfo2['usdt_str'];



            //查看下发地址：
            if ($keyixiafa > 0) {
                $inline_keyboard_arr[0] = array('text' => "立即下发今日:" . $keyixiafa . "U", "callback_data" => "jinrixiafa_user_" . $uid_end . "&&" . $keyixiafa . "###" . $all_fusdt_money);

            } else {
                $inline_keyboard_arr[0] = array('text' => "今日收益下发成功", "callback_data" => "wufaxiafa_user_" . $uid_end);

            }


            $keyboard = [
                'inline_keyboard' => [
                    $inline_keyboard_arr
                ]
            ];
            $parameter = array(
                'chat_id' => $chatid,
                'parse_mode' => 'HTML',
                'text' => $msg,
                'reply_markup' => $keyboard,

            );

            $this->http_post_data('sendMessage', json_encode($parameter));
        }*/


        if (strpos($text, "支付费率设置") !== false) {
            //$chuge_userid_arr = $this->chaojiyonghu;
            $quanxian = "支付费率设置";
            $this->quanxian($chatid, $userid, $quanxian, $username);
            /*if (!in_array($from_id, $chuge_userid_arr)) {
                $ids_str = implode(",", $chuge_userid_arr);
                $parameter = array(
                    'chat_id' => $chat_id,
                    'parse_mode' => 'HTML',
                    'text' => "仅Tg_ID:" . $ids_str . "有此权限！"
                );
                $this->http_post_data('sendMessage', json_encode($parameter));

                $parameter = array(
                    'callback_query_id' => $data['callback_query']['id'],
                    'text' => "",
                );
                $this->http_post_data('answerCallbackQuery', json_encode($parameter));
                exit();
            }*/

            //查看所有启用的支付方式
            $sql_info1 = "select * from pay_type where status ='1'";
            $type_info = $this->shujuku($sql_info1);
            $pay_list = array();
            $pay_str = "";
            foreach ($type_info as $ke => $ve) {
                $pay_list[$ve['id']] = $ve['showname'];
                $pay_str .= "\r\n" . $ve['showname'] . "=0.775";
            }


            $sql_info = "select * from pay_botsettle where chatid ='" . $chatid . "'";

            $order_info2 = $this->shujuku($sql_info);
            $pid = $order_info2[0]['merchant'];


            //查看是否有支付方式的费率信息
            $sql_info3 = "select * from pay_userfeilv where typelist='1' and pid ='" . $pid . "' and chatid='" . $chatid . "'";
            $order_info3 = $this->shujuku($sql_info3);


            if ($order_info3) {
                $hava_type = array();
                foreach ($order_info3 as $kp => $vp) {
                    $hava_type[$vp['type']] = $vp['feilv'];

                }

                $pay_str2 = "";
                $pay_str3 = "<b>当前支付方式费率信息,注意:如果费率是22.5个点，请设置：0.775:</b>\r\n";
                foreach ($pay_list as $kl => $l) {

                    if (array_key_exists($kl, $hava_type)) {
                        $pay_str2 .= "\r\n" . $l . "=" . $hava_type[$kl];
                        $pay_str3 .= "\r\n" . $l . "=" . $hava_type[$kl];
                    } else {
                        $pay_str2 .= "\r\n" . $l . "=";
                    }


                }


                $msg = "<b>你当前的费率信息如下:</b>\r\n\r\n" . $pay_str3;
                $switch_inline_query_current_msg = "#userzhifufeilv_tianjia_#\r\n" . $pay_str2;
                $inline_keyboard_arr3[0] = array('text' => "修改支付费率 ", "switch_inline_query_current_chat" => $switch_inline_query_current_msg);
                $keyboard = [
                    'inline_keyboard' => [
                        $inline_keyboard_arr3,
                    ]
                ];
            } else {


                $msg = "<b>你尚未设置费率,注意:如果费率是22.5个点，请设置：0.775，请设置</b>";
                $switch_inline_query_current_msg = "#userzhifufeilv_tianjia_#\r\n" . $pay_str;
                $inline_keyboard_arr3[0] = array('text' => "添加支付费率 ", "switch_inline_query_current_chat" => $switch_inline_query_current_msg);
                $keyboard = [
                    'inline_keyboard' => [
                        $inline_keyboard_arr3,
                    ]
                ];


            }
            $parameter = array(
                'chat_id' => $chatid,
                'parse_mode' => 'HTML',
                'text' => $msg,
                'reply_markup' => $keyboard,
                'disable_web_page_preview' => true,
            );

            $this->http_post_data('sendMessage', json_encode($parameter));
            exit();


        }
        if (strpos($text, "通道费率设置") !== false) {
            $chuge_userid_arr = $this->chaojiyonghu;
            if (!in_array($from_id, $chuge_userid_arr)) {
                $ids_str = implode(",", $chuge_userid_arr);
                $parameter = array(
                    'chat_id' => $chat_id,
                    'parse_mode' => 'HTML',
                    'text' => "仅Tg_ID:" . $ids_str . "有此权限！"
                );
                $this->http_post_data('sendMessage', json_encode($parameter));

                $parameter = array(
                    'callback_query_id' => $data['callback_query']['id'],
                    'text' => "",
                );
                $this->http_post_data('answerCallbackQuery', json_encode($parameter));
                exit();
            }

            //查看所有启用的支付方式
            $sql_info1 = "select * from pay_type where status ='1'";
            $type_info = $this->shujuku($sql_info1);
            $pay_list = array();
            $pay_str = "";
            foreach ($type_info as $ke => $ve) {
                $pay_list[$ve['id']] = $ve['showname'];
                $pay_str .= $ve['showname'] . "=" . "\r\n";
            }

            $sql_info = "select * from pay_botsettle where chatid ='" . $chatid . "'";

            $order_info2 = $this->shujuku($sql_info);
            $pid = $order_info2[0]['merchant'];


            //查看是否有通道费率信息：
            $sql_info4 = "select * from pay_userfeilv where typelist='2' and pid ='" . $pid . "' and chatid='" . $chatid . "'";
            $order_info4 = $this->shujuku($sql_info4);

            $tongdao_str = "";
            if ($order_info4) {

                foreach ($order_info4 as $kt => $vt) {
                    $tongdao_str .= "\r\n" . $vt['type'] . "=" . $vt['feilv'];

                }
            } else {
                $tongdao_str = "通道费率(优先)，格式:通道=费率\r\n231=0.775";

            }


            if ($order_info4) {

                $msg = "<b>你当前通道费率信息,注意:如果汇率16个点,需要设置：0.84(优先):</b>\r\n" . $tongdao_str;
                $switch_inline_query_current_msg2 = "#usertongdaofeilv_tianjia_#\r\n" . $tongdao_str;
                $inline_keyboard_arr3[0] = array('text' => "修改通道费率 ", "switch_inline_query_current_chat" => $switch_inline_query_current_msg2);
                $keyboard = [
                    'inline_keyboard' => [
                        $inline_keyboard_arr3,
                    ]
                ];
            } else {


                $msg = "<b>你尚未设置通道费率,注意:如果汇率16个点,需要设置：0.84，请设置</b>";
                $switch_inline_query_current_msg2 = "#usertongdaofeilv_tianjia_#\r\n" . $tongdao_str;
                $inline_keyboard_arr3[0] = array('text' => "添加通道费率 ", "switch_inline_query_current_chat" => $switch_inline_query_current_msg2);
                $keyboard = [
                    'inline_keyboard' => [
                        $inline_keyboard_arr3,
                    ]
                ];


            }
            $parameter = array(
                'chat_id' => $chatid,
                'parse_mode' => 'HTML',
                'text' => $msg,
                'reply_markup' => $keyboard,
                'disable_web_page_preview' => true,
            );

            $this->http_post_data('sendMessage', json_encode($parameter));
            exit();


        }
        if (strpos($text, "分成比例") !== false) {
            $chuge_userid_arr = $this->chaojiyonghu;
            if (!in_array($from_id, $chuge_userid_arr)) {
                $ids_str = implode(",", $chuge_userid_arr);
                $parameter = array(
                    'chat_id' => $chat_id,
                    'parse_mode' => 'HTML',
                    'text' => "仅Tg_ID:" . $ids_str . "有此权限！"
                );
                $this->http_post_data('sendMessage', json_encode($parameter));

                $parameter = array(
                    'callback_query_id' => $data['callback_query']['id'],
                    'text' => "",
                );
                $this->http_post_data('answerCallbackQuery', json_encode($parameter));
                exit();
            }
            $sql_info = "select * from pay_botsettle where chatid ='" . $chatid . "'";

            $order_info2 = $this->shujuku($sql_info);
            $pid = $order_info2[0]['merchant'];


            $typelist = "5";
            //查看是否有通道费率信息：
            $sql_info4 = "select * from pay_userfeilv where typelist='" . $typelist . "' and pid ='" . $pid . "' and chatid='" . $chatid . "'";
            $order_info4 = $this->shujuku($sql_info4);

            $tongdao_str = "";
            if ($order_info4) {


                $tongdao_str .= "\r\n分成比例" . "=" . $order_info4[0]['feilv'];


            } else {
                $tongdao_str = "分成比例，格式:分成比例[固定]=浮动值\r\n分成比例=1";

            }


            if ($order_info4) {

                $msg = "<b>你当前分成比例信息:</b>\r\n" . $tongdao_str;
                $switch_inline_query_current_msg2 = "#usertongfencheng_tianjia_#\r\n" . $tongdao_str;
                $inline_keyboard_arr3[0] = array('text' => "修改分成比例 ", "switch_inline_query_current_chat" => $switch_inline_query_current_msg2);
                $keyboard = [
                    'inline_keyboard' => [
                        $inline_keyboard_arr3,
                    ]
                ];
            } else {


                $msg = "<b>你尚未设置分成比例，请设置</b>";
                $switch_inline_query_current_msg2 = "#usertongfencheng_tianjia_#\r\n" . $tongdao_str;
                $inline_keyboard_arr3[0] = array('text' => "添加分成比例 ", "switch_inline_query_current_chat" => $switch_inline_query_current_msg2);
                $keyboard = [
                    'inline_keyboard' => [
                        $inline_keyboard_arr3,
                    ]
                ];


            }
            $parameter = array(
                'chat_id' => $chatid,
                'parse_mode' => 'HTML',
                'text' => $msg,
                'reply_markup' => $keyboard,
                'disable_web_page_preview' => true,
            );

            $this->http_post_data('sendMessage', json_encode($parameter));
            exit();
        }
        if (strpos($text, "U币汇率浮点设置") !== false) {
            $chuge_userid_arr = $this->chaojiyonghu;
            if (!in_array($from_id, $chuge_userid_arr)) {
                $ids_str = implode(",", $chuge_userid_arr);
                $parameter = array(
                    'chat_id' => $chat_id,
                    'parse_mode' => 'HTML',
                    'text' => "仅Tg_ID:" . $ids_str . "有此权限！"
                );
                $this->http_post_data('sendMessage', json_encode($parameter));

                $parameter = array(
                    'callback_query_id' => $data['callback_query']['id'],
                    'text' => "",
                );
                $this->http_post_data('answerCallbackQuery', json_encode($parameter));
                exit();
            }
            $sql_info = "select * from pay_botsettle where chatid ='" . $chatid . "'";

            $order_info2 = $this->shujuku($sql_info);
            $pid = $order_info2[0]['merchant'];


            $typelist = "3";
            //查看是否有通道费率信息：
            $sql_info4 = "select * from pay_userfeilv where typelist='" . $typelist . "' and pid ='" . $pid . "' and chatid='" . $chatid . "'";
            $order_info4 = $this->shujuku($sql_info4);

            $tongdao_str = "";
            if ($order_info4) {


                $tongdao_str .= "\r\nU币浮动" . "=" . $order_info4[0]['feilv'];


            } else {
                $tongdao_str = "U币浮动费率，格式:U币浮动[固定]=浮动值\r\nU币浮动=+0.1";

            }


            if ($order_info4) {

                $msg = "<b>你当前U币浮动信息:</b>\r\n" . $tongdao_str;
                $switch_inline_query_current_msg2 = "#usertongfudong_tianjia_#\r\n" . $tongdao_str;
                $inline_keyboard_arr3[0] = array('text' => "修改U币浮动 ", "switch_inline_query_current_chat" => $switch_inline_query_current_msg2);
                $keyboard = [
                    'inline_keyboard' => [
                        $inline_keyboard_arr3,
                    ]
                ];
            } else {


                $msg = "<b>你尚未设置U币浮动，请设置</b>";
                $switch_inline_query_current_msg2 = "#usertongfudong_tianjia_#\r\n" . $tongdao_str;
                $inline_keyboard_arr3[0] = array('text' => "添加U币浮动 ", "switch_inline_query_current_chat" => $switch_inline_query_current_msg2);
                $keyboard = [
                    'inline_keyboard' => [
                        $inline_keyboard_arr3,
                    ]
                ];


            }
            $parameter = array(
                'chat_id' => $chatid,
                'parse_mode' => 'HTML',
                'text' => $msg,
                'reply_markup' => $keyboard,
                'disable_web_page_preview' => true,
            );

            $this->http_post_data('sendMessage', json_encode($parameter));
            exit();
        }
        if (strpos($text, "U币汇率设置") !== false) {
            $chuge_userid_arr = $this->chaojiyonghu;
            if (!in_array($from_id, $chuge_userid_arr)) {
                $ids_str = implode(",", $chuge_userid_arr);
                $parameter = array(
                    'chat_id' => $chat_id,
                    'parse_mode' => 'HTML',
                    'text' => "仅Tg_ID:" . $ids_str . "有此权限！"
                );
                $this->http_post_data('sendMessage', json_encode($parameter));

                $parameter = array(
                    'callback_query_id' => $data['callback_query']['id'],
                    'text' => "",
                );
                $this->http_post_data('answerCallbackQuery', json_encode($parameter));
                exit();
            }
            $sql_info = "select * from pay_botsettle where chatid ='" . $chatid . "'";

            $order_info2 = $this->shujuku($sql_info);
            $pid = $order_info2[0]['merchant'];


            $typelist = "4";
            //查看是否有通道费率信息：
            $sql_info4 = "select * from pay_userfeilv where typelist='" . $typelist . "' and pid ='" . $pid . "' and chatid='" . $chatid . "'";
            $order_info4 = $this->shujuku($sql_info4);

            $tongdao_str = "";
            if ($order_info4) {

                $tongdao_str .= "\r\nU币汇率" . "=" . $order_info4[0]['feilv'];


            } else {
                $tongdao_str = "U币汇率，格式:U币汇率[固定]=U币汇率值\r\nU币汇率=6.92";

            }


            if ($order_info4) {

                $msg = "<b>你当前U币汇率信息(优先):</b>\r\n" . $tongdao_str;
                $switch_inline_query_current_msg2 = "#usertonghuilv_tianjia_#\r\n" . $tongdao_str;
                $inline_keyboard_arr3[0] = array('text' => "修改U币汇率 ", "switch_inline_query_current_chat" => $switch_inline_query_current_msg2);
                $keyboard = [
                    'inline_keyboard' => [
                        $inline_keyboard_arr3,
                    ]
                ];
            } else {


                $msg = "<b>你尚未设置U币汇率，请设置</b>";
                $switch_inline_query_current_msg2 = "#usertonghuilv_tianjia_#\r\n" . $tongdao_str;
                $inline_keyboard_arr3[0] = array('text' => "修改U币汇率 ", "switch_inline_query_current_chat" => $switch_inline_query_current_msg2);
                $keyboard = [
                    'inline_keyboard' => [
                        $inline_keyboard_arr3,
                    ]
                ];


            }
            $parameter = array(
                'chat_id' => $chatid,
                'parse_mode' => 'HTML',
                'text' => $msg,
                'reply_markup' => $keyboard,
                'disable_web_page_preview' => true,
            );

            $this->http_post_data('sendMessage', json_encode($parameter));
            exit();
        }
        if (strpos($text, 'paliangshang###') !== false) {
            $exp = explode("###", $text);
            $typelist = $exp[1];
            switch ($typelist) {
                case '1':
                    $this->tuisongxiaoxi(1, $chat_id);
                    // code...
                    break;
                case '2':
                    $this->tuisongxiaoxi(2, $chat_id);
                    // code...
                    break;
                case '3':
                    $this->tuisongxiaoxi(3, $chat_id);
                    // code...
                    break;
                case '5':
                    $this->tuisongxiaoxi(5, $chat_id);
                    // code...
                    break;
                case '6':
                    $this->tuisongxiaoxi(6, $chat_id);
                    // code...
                    break;
                default:
                    $this->tuisongxiaoxi(4, $chat_id);
                    // 4
                    break;
            }
        }
        //正常通道
        if (strpos($text, 'zhifu_channel_') !== false) {


            $idarr = explode("_channel_", $text);


            $ids = $idarr[1];
            $messages = "请点按钮输入支付金额";
            $switch_inline_query_current_msg = "#qyaozhi_roll_*" . $ids . "*#\r\n\r\n支付金额:50";
            $inline_keyboard_arr3[0] = array('text' => "输入金额", "switch_inline_query_current_chat" => $switch_inline_query_current_msg);
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
        //轮询
        if (strpos($text, 'zhifu_roll_') !== false) {
            $idarr = explode("_roll_", $text);
            $ids = $idarr[1];
            $messages = "请点按钮输入支付金额";
            $switch_inline_query_current_msg = "#qyaozhi_roll_*" . $ids . "*#\r\n\r\n支付金额:50";
            $inline_keyboard_arr3[0] = array('text' => "输入金额", "switch_inline_query_current_chat" => $switch_inline_query_current_msg);

            $keyboard = [
                'inline_keyboard' => [
                    $inline_keyboard_arr3,
                ]
            ];

            $parameter = array(
                'chat_id' => $chat_id,
                'parse_mode' => 'HTML',
                'text' => $messages,
                'reply_markup' => $keyboard,
                'disable_web_page_preview' => true,

            );

            $this->http_post_data('sendMessage', json_encode($parameter));
            exit();
        }
        if (strpos($text, 'quedingusdt_') !== false) {
            $chuge_userid_arr = $this->chaojiyonghu;
            if (!in_array($from_id, $chuge_userid_arr)) {
                $ids_str = implode(",", $chuge_userid_arr);
                $parameter = array(
                    'chat_id' => $chat_id,
                    'parse_mode' => 'HTML',
                    'text' => "仅Tg_ID:" . $ids_str . "有此权限！"
                );
                $this->http_post_data('sendMessage', json_encode($parameter));
                exit();
            }
            $usdts = explode("quedingusdt_", $text);
            $usdt_arr = $usdts[1];

            $usdt_arr2 = explode("###", $usdts[1]);

            $usdt = trim($usdt_arr2[0]);
            $pid = trim($usdt_arr2[1]);


            $status = $this->pdo->exec("UPDATE pay_user SET usdt_str='" . $usdt . "' WHERE uid='" . $pid . "'");
            if ($status) {
                $this->xiaoxi("更改USDT成功！", $chat_id);
            } else {
                $this->xiaoxi("更改USDT失败！", $chat_id);
            }


        }
        //删除用户组所有的用户
        if (strpos($text, 'deleteallyonghu') !== false) {

            $chuge_userid_arr = $this->chaojiyonghu;
            if (!in_array($from_id, $chuge_userid_arr)) {
                $ids_str = implode(",", $chuge_userid_arr);
                $parameter = array(
                    'chat_id' => $chat_id,
                    'parse_mode' => 'HTML',
                    'text' => "仅Tg_ID:" . $ids_str . "有此权限！"
                );
                $this->http_post_data('sendMessage', json_encode($parameter));
                exit();
            }
            $instruction_arr = explode("deleteallyonghu###", $text);

            $yonghzuid = $instruction_arr[1];


            $set_sql1 = "select * FROM pay_zuren where typelist='2' and yonghuzu_id='" . $yonghzuid . "'";
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

            $set_sql = "DELETE FROM pay_zuren where typelist='2' and yonghuzu_id='" . $yonghzuid . "'";
            $is_gengxin = $this->pdo->exec($set_sql);
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
                $ids_str = implode(",", $chuge_userid_arr);
                $parameter = array(
                    'chat_id' => $chat_id,
                    'parse_mode' => 'HTML',
                    'text' => "仅Tg_ID:" . $ids_str . "有此权限！"
                );
                $this->http_post_data('sendMessage', json_encode($parameter));
                exit();
            }
            $instruction_arr = explode("deleteallmingling###", $text);
            $instruction_id = $instruction_arr[1];
            $instruction_arr2 = explode("###", $instruction_id);
            $yonghzuid = $instruction_arr2[0];
            $yonghzumingling = $instruction_arr2[1];

            $set_sql1 = "select * FROM pay_yonghuzu  where typelist='2' and id='" . $yonghzuid . "'";
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

            $all_mingling_arr_str = "";
            $set_sql = "update pay_yonghuzu set mingling='" . $all_mingling_arr_str . "' where id='" . $yonghzuid . "' and typelist='2'";
            $is_gengxin = $this->pdo->exec($set_sql);
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
                $ids_str = implode(",", $chuge_userid_arr);
                $parameter = array(
                    'chat_id' => $chat_id,
                    'parse_mode' => 'HTML',
                    'text' => "仅Tg_ID:" . $ids_str . "有此权限！"
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
                $ids_str = implode(",", $chuge_userid_arr);
                $parameter = array(
                    'chat_id' => $chat_id,
                    'parse_mode' => 'HTML',
                    'text' => "仅Tg_ID:" . $ids_str . "有此权限！"
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
        foreach ($user_type as $item => $v) {
            $new_type[$v['name']] = $v['showname'];
        }
        if (strpos($text, 'fanhuiuser_') !== false) {
            if ($from_id != "982124360") {  //
                $parameter = array(
                    'chat_id' => $chatid,
                    'parse_mode' => 'HTML',
                    'text' => "操作失败！设置操作只可以由楚歌运行操作！"
                );
                $this->http_post_data('sendMessage', json_encode($parameter));
                exit();
            }
        }
        if (strpos($text, 'fanhuiuser_people_') !== false) {

            $uids_arr = explode("people_", $text);
            $uid = $uids_arr['1'];

            $set_sqlq = "select * FROM pay_usercaozuo where uid='" . $uid . "'";
            $order_query_q = $this->pdo->query($set_sqlq);
            $user_caozuo = $order_query_q->fetchAll();
            if ($user_caozuo) {
                if ($user_caozuo[0]['types'] == "1") {
                    $messages = "你正在添加通知人的输入，你直接输入例如：@111 @222 @333";
                } elseif ($user_caozuo[0]['types'] == "2") {
                    $messages = "你正在添加当达到多少单未支付进行通知，当达到多少单未支付进行通知，例如：60,50,40,30,10  必须英文逗号隔开！";
                } elseif ($user_caozuo[0]['types'] == "3") {
                    $messages = "你正在添加通道检索时间范围，例如输入：60   就是只检索最近60分钟用过的所有通道的未支付情况";
                } else {
                    $messages = "你正在添加设置同一个通道相同的两条消息最少间隔通知时间，例如输入：60  就是如果60分钟内同样的消息如果通知过一次，就不会再次通知";
                }
                $parameter = array(
                    'chat_id' => $chat_id,
                    'parse_mode' => 'HTML',
                    'text' => $messages,
                );
                $this->http_post_data('sendMessage', json_encode($parameter));
                exit();
            }
            $parameter = array(
                'chat_id' => $chat_id,
                'parse_mode' => 'HTML',
                'text' => "请输入通知的消息内容，例如：出现大量未支付，请查看 @111 @222 @333",
            );


            $set_sql = "insert into pay_usercaozuo (types,uid,createtime,chat_id) values ('1','" . $uid . "', '" . time() . "','" . $chat_id . "')";

            $this->pdo->exec($set_sql);

            $this->http_post_data('sendMessage', json_encode($parameter));
            exit();
        }
        if (strpos($text, 'fanhuiuser_danshu_') !== false) {
            $uids_arr = explode("danshu_", $text);
            $uid = $uids_arr['1'];

            $set_sqlq = "select * FROM pay_usercaozuo where uid='" . $uid . "'";
            $order_query_q = $this->pdo->query($set_sqlq);
            $user_caozuo = $order_query_q->fetchAll();
            if ($user_caozuo) {
                if ($user_caozuo[0]['types'] == "1") {
                    $messages = "你正在添加通知人的输入，你直接输入例如：@111 @222 @333";
                } elseif ($user_caozuo[0]['types'] == "2") {
                    $messages = "你正在添加当达到多少单未支付进行通知，当达到多少单未支付进行通知，例如：60,50,40,30,10  必须英文逗号隔开！";
                } elseif ($user_caozuo[0]['types'] == "3") {
                    $messages = "你正在添加通道检索时间范围，例如输入：60   就是只检索最近60分钟用过的所有通道的未支付情况";
                } else {
                    $messages = "你正在添加设置同一个通道相同的两条消息最少间隔通知时间，例如输入：60  就是如果60分钟内同样的消息如果通知过一次，就不会再次通知";
                }
                $parameter = array(
                    'chat_id' => $chat_id,
                    'parse_mode' => 'HTML',
                    'text' => $messages,
                );
                $this->http_post_data('sendMessage', json_encode($parameter));
                exit();
            }
            $parameter = array(
                'chat_id' => $chat_id,
                'parse_mode' => 'HTML',
                'text' => "请输入当达到多少单未支付进行通知，例如：60,50,40,30,10  必须英文逗号隔开！",

            );

            $set_sql = "insert into pay_usercaozuo (types,uid,createtime,chat_id) values ('2','" . $uid . "', '" . time() . "','" . $chat_id . "')";

            $this->pdo->exec($set_sql);

            $this->http_post_data('sendMessage', json_encode($parameter));
            exit();
        }
        if (strpos($text, 'fanhuiuser_fanwei_') !== false) {
            $uids_arr = explode("fanwei_", $text);
            $uid = $uids_arr['1'];

            $set_sqlq = "select * FROM pay_usercaozuo where uid='" . $uid . "'";
            $order_query_q = $this->pdo->query($set_sqlq);
            $user_caozuo = $order_query_q->fetchAll();
            if ($user_caozuo) {
                if ($user_caozuo[0]['types'] == "1") {
                    $messages = "你正在添加通知人的输入，你直接输入例如：@111 @222 @333";
                } elseif ($user_caozuo[0]['types'] == "2") {
                    $messages = "你正在添加当达到多少单未支付进行通知，当达到多少单未支付进行通知，例如：60,50,40,30,10  必须英文逗号隔开！";
                } elseif ($user_caozuo[0]['types'] == "3") {
                    $messages = "你正在添加通道检索时间范围，例如输入：60   就是只检索最近60分钟用过的所有通道的未支付情况";
                } else {
                    $messages = "你正在添加设置同一个通道相同的两条消息最少间隔通知时间，例如输入：60  就是如果60分钟内同样的消息如果通知过一次，就不会再次通知";
                }
                $parameter = array(
                    'chat_id' => $chat_id,
                    'parse_mode' => 'HTML',
                    'text' => $messages,
                );


                $this->http_post_data('sendMessage', json_encode($parameter));
                exit();
            }
            $set_sql = "insert into pay_usercaozuo (types,uid,createtime,chat_id) values ('3','" . $uid . "', '" . time() . "','" . $chat_id . "')";

            $this->pdo->exec($set_sql);
            $parameter = array(
                'chat_id' => $chat_id,
                'parse_mode' => 'HTML',
                'text' => "请输入通道检索时间范围，例如输入：60   就是只检索最近60分钟用过的所有通道的未支付情况",
            );
            $this->http_post_data('sendMessage', json_encode($parameter));
            exit();
        }
        if (strpos($text, 'fanhuiuser_jiange_') !== false) {
            $uids_arr = explode("jiange_", $text);
            $uid = $uids_arr['1'];

            $set_sqlq = "select * FROM pay_usercaozuo where uid='" . $uid . "'";
            $order_query_q = $this->pdo->query($set_sqlq);
            $user_caozuo = $order_query_q->fetchAll();
            if ($user_caozuo) {
                if ($user_caozuo[0]['types'] == "1") {
                    $messages = "你正在添加通知人的输入，你直接输入例如：@111 @222 @333";
                } elseif ($user_caozuo[0]['types'] == "2") {
                    $messages = "你正在添加当达到多少单未支付进行通知，当达到多少单未支付进行通知，例如：60,50,40,30,10  必须英文逗号隔开！";
                } elseif ($user_caozuo[0]['types'] == "3") {
                    $messages = "你正在添加通道检索时间范围，例如输入：60   就是只检索最近60分钟用过的所有通道的未支付情况";
                } else {
                    $messages = "你正在添加设置同一个通道相同的两条消息最少间隔通知时间，例如输入：60  就是如果60分钟内同样的消息如果通知过一次，就不会再次通知";
                }
                $parameter = array(
                    'chat_id' => $chat_id,
                    'parse_mode' => 'HTML',
                    'text' => $messages,
                );
                $this->http_post_data('sendMessage', json_encode($parameter));
                exit();
            }
            $parameter = array(
                'chat_id' => $chat_id,
                'parse_mode' => 'HTML',
                'text' => "设置同一个通道相同的两条消息最少间隔通知时间，例如输入：60  就是如果60分钟内同样的消息如果通知过一次，就不会再次通知",
            );

            $set_sql = "insert into pay_usercaozuo (types,uid,createtime,chat_id) values ('4','" . $uid . "', '" . time() . "','" . $chat_id . "')";
            $this->pdo->exec($set_sql);

            $this->http_post_data('sendMessage', json_encode($parameter));
            exit();
        }
        if (strpos($text, 'findorderonly') !== false) {

            $set_sql = "insert into pay_ordercha (type,chat_id,from_id,createtime) values ('1','" . $chat_id . "', '" . $from_id . "', '" . time() . "')";
            $this->pdo->exec($set_sql);

            $messages = "搜索内容选择:
0：全部，
1：订单号，
2：商户订单号
3：终端渠道

商户号选择2:
0：全部
1：商户号

支付方式选择:
0：全部
1：支付宝
2：微信
3：QQ红包
4：云闪付

状态选择:
0：全部
1：已完成";
            $parameter = array(
                'chat_id' => $chat_id,
                'parse_mode' => 'HTML',
                'text' => $messages,


            );


            $this->http_post_data('sendMessage', json_encode($parameter));
            exit();
        }
        //订单处理：changorder_finish_     changorder_notice_   changorder_delete_
        if (strpos($text, 'changorder_') !== false) {
            $text_trade_no = explode("_", $text);
            $trade_no = $text_trade_no[2];
            $this->pdo->exec("update pay_order set status='1' where trade_no='$trade_no'");
            $order_query_q = $this->pdo->query("select * from pay_order where trade_no='$trade_no' limit 1");
            $srow_all = $order_query_q->fetchAll();
            $srow = $srow_all[0];
            $uid = $srow['uid'];

            if (strpos($text, 'finish') !== false) {
                //订单修改完成:https://ceshi.freewing123.xyz/admin/ajax.php?act=setStatus&trade_no=2022062115261391159&status=1

                $money = $srow["getmoney"];
                $date = date("Y-m-d H:i:s");
                $this->pdo->exec("update `pay_order` set `api_trade_no` ='-1',`endtime` ='$date',`date` =NOW() where `trade_no`='$trade_no'");
                //changeUserMoney($srow['uid'], $money, true, '订单收入', $srow['trade_no']);

                $oldmoney_find = $this->pdo->query("SELECT money FROM pay_user WHERE uid='{$uid}' LIMIT 1");
                $oldmoney = $oldmoney_find->fetchColumn();

                $action = 1;
                $newmoney = round($oldmoney + $money, 2);


                $this->pdo->exec("UPDATE pay_user SET money='{$newmoney}' WHERE uid='{$uid}'");
                // $this->pdo->exec("INSERT INTO `pay_record` (`uid`, `action`, `money`, `oldmoney`, `newmoney`, `type`, `trade_no`, `date`) VALUES (:uid, :action, :money, :oldmoney, :newmoney, :type, :orderid, NOW())", [':uid'=>$uid, ':action'=>$action, ':money'=>$money, ':oldmoney'=>$oldmoney, ':newmoney'=>$newmoney, ':type'=>"订单收入", ':orderid'=>$trade_no]);

                $this->pdo->exec("INSERT INTO `pay_record` (`uid`, `action`, `money`, `oldmoney`, `newmoney`, `type`, `trade_no`, `date`) VALUES ('" . $uid . "', '" . $action . "', '" . $money . "', '" . $oldmoney . "', '" . $newmoney . "', '" . "订单收入" . "', '" . $trade_no . "', '" . $date . "')");


                $parameter = array(
                    'chat_id' => $chat_id,
                    'parse_mode' => 'HTML',
                    'text' => "处理成功！"
                );
                $this->http_post_data('sendMessage', json_encode($parameter));
                $parameter = array(
                    'callback_query_id' => $data['callback_query']['id'],
                    'text' => "",
                );
                $this->http_post_data('answerCallbackQuery', json_encode($parameter));
                exit();

            } elseif (strpos($text, 'notice') !== false) {
                //订单重新通知


                $key_find = $this->pdo->query("SELECT * FROM pay_user WHERE uid='{$uid}' LIMIT 1");
                $key = $key_find->fetchColumn(3);


                $type_find = $this->pdo->query("SELECT name FROM pay_type WHERE id='{$srow['type']}' LIMIT 1");
                $type = $type_find->fetchColumn();
                $data = $srow;
                $array = array('pid' => $data['uid'], 'trade_no' => $data['trade_no'], 'out_trade_no' => $data['out_trade_no'], 'type' => $type, 'name' => $data['name'], 'money' => (float)$data['money'], 'trade_status' => $data['type'] > 0 ? 'TRADE_SUCCESS' : 'TRADE_CLOSED');
                $para_filter = array();
                foreach ($array as $key => $val) {
                    if ($key == "sign" || $key == "sign_type" || $val == "" || $key == "stype") continue;
                    else $para_filter[$key] = $array[$key];
                }
                ksort($para_filter);
                reset($para_filter);
                $arg = "";
                foreach ($para_filter as $key => $val) {
                    $arg .= $key . "=" . $val . "&";
                }

                $prestr = substr($arg, 0, -1);


                $arg = "";
                foreach ($para_filter as $key => $val) {
                    $arg .= $key . "=" . urlencode($val) . "&";
                }

                $urlstr = substr($arg, 0, -1);
                $sign = md5($prestr . $key);

                if (strpos($data['notify_url'], '?'))
                    $url['notify'] = $data['notify_url'] . '&' . $urlstr . '&sign=' . $sign . '&sign_type=MD5';
                else
                    $url['notify'] = $data['notify_url'] . '?' . $urlstr . '&sign=' . $sign . '&sign_type=MD5';
                if (strpos($data['return_url'], '?'))
                    $url['return'] = $data['return_url'] . '&' . $urlstr . '&sign=' . $sign . '&sign_type=MD5';
                else
                    $url['return'] = $data['return_url'] . '?' . $urlstr . '&sign=' . $sign . '&sign_type=MD5';
                if ($data['tid'] > 0) {
                    $url['return'] = $data['return_url'];
                }
                $sasas = Http::get($url['notify']);
                $parameter = array(
                    'chat_id' => $chat_id,
                    'parse_mode' => 'HTML',
                    'text' => "通知成功"
                );

                $this->http_post_data('sendMessage', json_encode($parameter));
            } elseif (strpos($text, 'delete') !== false) {
                //订单删除
                if ($this->pdo->exec("DELETE FROM pay_order WHERE trade_no='$trade_no'")) {
                    $parameter = array(
                        'chat_id' => $chat_id,
                        'parse_mode' => 'HTML',
                        'text' => "删除成功！"
                    );
                    $this->http_post_data('sendMessage', json_encode($parameter));
                } else {
                    $parameter = array(
                        'chat_id' => $chat_id,
                        'parse_mode' => 'HTML',
                        'text' => "删除失败！"
                    );
                    $this->http_post_data('sendMessage', json_encode($parameter));
                }
            }

        }
        //设置汇率
        if (strpos($text, 'oneset') !== false) {
            //纪录当前用户正在录入信息：查询是不是正在设置概率
            $sql = "select * from pay_chatgroupset where from_id ='" . $from_id . "'";
            $order_query = $this->pdo->query($sql);
            $order_info = $order_query->fetchAll();
            if ($order_info) {
                if ($order_info['uid'] > 0) {
                    $text = '你正在调整商户号：' . $order_info['uid'] . '的设置,结束请回复：0000';
                } else {
                    $text = '你正在添加某商户号的设值：,结束请回复：0000';
                }
                $parameter = array(
                    'chat_id' => $chat_id,
                    'parse_mode' => 'HTML',
                    'text' => $text
                );
                $this->http_post_data('sendMessage', json_encode($parameter));
                exit();
            }
            $set_sql = "insert into pay_chatgroupset (chat_id,status,createtime,from_id) values ('" . $chat_id . "','0','" . time() . "', '" . $from_id . "')";
            $this->pdo->exec($set_sql);

            $parameter = array(
                'chat_id' => $chat_id,
                'parse_mode' => 'HTML',
                'text' => "good!请直接:支付类型,商户号,xx,xx,U率,+/-上浮指数,U币地址",
            );

            $this->http_post_data('sendMessage', json_encode($parameter));

        } elseif (strpos($text, 'changeuser_') !== false) {
            //纪录当前用户正在录入信息：查询是不是正在设置概率
            $sql = "select * from pay_chatgroupset where from_id ='" . $from_id . "'";

            $uid_arr = explode("_", $text);

            $order_query = $this->pdo->query($sql);
            $order_info = $order_query->fetchAll();
            if ($order_info) {
                if ($order_info[0]['uid'] > 0) {
                    $text = '你正在调整商户号：' . $order_info[0]['uid'] . '的设置';
                } else {
                    $text = '你正在修改商户号' . $uid_arr['1'] . '的设置：';
                }
                $texts = $text . ",结束请回复：0000";

                $parameter = array(
                    'chat_id' => $chat_id,
                    'parse_mode' => 'HTML',
                    'text' => $texts
                );
                $this->http_post_data('sendMessage', json_encode($parameter));
                exit();
            } else {
                $text = '你正在修改商户号' . $uid_arr['1'] . '的设置：';
            }
            $set_sql = "insert into pay_chatgroupset (chat_id,status,createtime,from_id,uid) values ('" . $chat_id . "','0','" . time() . "', '" . $from_id . "', '" . $uid_arr[1] . "')";
            $this->pdo->exec($set_sql);

            $parameter = array(
                'chat_id' => $chat_id,
                'parse_mode' => 'HTML',
                'text' => "good!" . $text . ",请直接:支付类型,商户号,xx,xx,U率,+/-上浮指数,U币地址",
            );

            $this->http_post_data('sendMessage', json_encode($parameter));
        } elseif (strpos($text, 'nextgroup') !== false) {
            //订单的下一页集合进来：
            //nextgroup###2&&&order
            $text_arr = explode("###", $text);  //findnext  1
            $page = $text_arr[1];   //第几页

            if (strpos($text, '&&&order') !== false) {
                //下一页
                $uid_arr = explode("***", $text);  //findnext  1
                $uid_info = $uid_arr[1];
                if ($uid_info == "0") {
                    $sql_count = "select count(*) from pay_order";
                } else {
                    $sql_count = "select count(*) from pay_order where uid = '" . $uid_info . "'";
                }


                $q = $this->pdo->query($sql_count);
                $rows = $q->fetch();
                $count_info = $rows[0];


                $pageshow = 20;
                $pagesize = ($page - 1) * $pageshow;
                //计算总页数:
                $numpages = ceil($count_info / $pageshow);   //向上取整；

                $prevpage = $page - 1;
                $nextpage = $page + 1;

                if ($uid_info == "0") {
                    $sql = "select waiwangip,trade_no,money,type,status from pay_order order by trade_no desc  limit " . $pagesize . "," . $pageshow;
                } else {
                    $sql = "select waiwangip,trade_no,money,type,status from pay_order where uid='" . $uid_info . "' order by trade_no desc  limit " . $pagesize . "," . $pageshow;
                }
                $order_query = $this->pdo->query($sql);
                $order_info = $order_query->fetchAll();

                $messages = "";
                foreach ($order_info as $key => $value) {
                    //2022062114155153521 (https://g.com/)~50元~🦋~✅
                    ///order_detail2022062114155153521~50元~🍀~✖️
                    if ($value['type'] == "1") {
                        $change_type = "🦋";
                    } else {
                        $change_type = "🍀";
                    }
                    if ($value['status'] == "1") {
                        $change_type2 = "✅";
                    } else {
                        $change_type2 = "✖";
                    }
                    $new_order_sn = $this->orderzhuan($value['trade_no']);
                    //$messages .= "/order_detail" . $value['trade_no'] . "~" . $value['money'] . "元~" . $change_type . "~" . $change_type2 . "\n\r";
                    //$messages .= "<b><a href='https://t.me/tianshidierg_bot?start=order_detail" . $value['trade_no'] . "'>" . $value['trade_no'] . "</a></b>~<b>" . $value['money'] . "元</b>~" . $change_type . "~" . $change_type2 . "\n\r";
                    $tianshi_bot_url = $this->tianshi_bot_url;
                    if ($uid_info == "0") {
                        $typelist = "order_detail";
                    } else {
                        $typelist = "shang_detail";
                    }
                    /*if($value['waiwangip']==0){
                        $waiwang = "内";
                    }else{
                        $waiwang = "外";
                    }*/

                    if ($value['type'] == 1) {
                        $waiwang = "支";
                    } else {
                        $waiwang = "微";
                    }
                    $messages .= "<b><a href='" . $tianshi_bot_url . "?start=" . $typelist . $value['trade_no'] . "'>" . $new_order_sn . "</a></b>~" . $waiwang . "~<b>" . $value['money'] . "元</b>~" . $change_type2 . "\n\r";

                }


                $inline_keyboard_arr[0] = array('text' => "上一页", "callback_data" => "lastgroup###" . $prevpage . "&&&order***" . $uid_info);
                if ($numpages > $page) {
                    $inline_keyboard_arr[1] = array('text' => "下一页", "callback_data" => "nextgroup###" . $nextpage . "&&&order***" . $uid_info);
                    // $inline_keyboard_arr[2] = array('text' => "搜索", "callback_data" => "findorderonly");
                } else {
                    // $inline_keyboard_arr[1] = array('text' => "搜索", "callback_data" => "findorderonly");
                }

                $keyboard = [
                    'inline_keyboard' => [
                        $inline_keyboard_arr
                    ]
                ];
            } else {


                //下一页


                $sql_count = "select count(*) from pay_uset";

                $q = $this->pdo->query($sql_count);
                $rows = $q->fetch();
                $count_info = $rows[0];


                $pageshow = 20;
                $pagesize = ($page - 1) * $pageshow;
                //计算总页数:
                $numpages = ceil($count_info / $pageshow);   //向上取整；

                $prevpage = $page - 1;
                $nextpage = $page + 1;

                $sql = "select a.id,a.uid,b.money,b.username from pay_uset as a left join pay_user as b on b.uid=a.uid group by a.uid limit " . $pagesize . "," . $pageshow;


                $order_query = $this->pdo->query($sql);
                $order_info = $order_query->fetchAll();

                $messages = "";
                $inline_keyboard_arr = array();
                foreach ($order_info as $key => $value) {
                    //$messages .= "1";
                    $messages .= "/userxq" . $value['uid'] . "---" . $value['money'] . "----" . $value['username'] . " /del" . $value['uid'] . "\n\r";

                }


                $inline_keyboard_arr[0] = array('text' => "上一页", "callback_data" => "lastgroup###" . $prevpage);
                if ($numpages > $page) {
                    $inline_keyboard_arr[1] = array('text' => "下一页", "callback_data" => "nextgroup###" . $nextpage);
                }
                $keyboard = [
                    'inline_keyboard' => [
                        $inline_keyboard_arr
                    ]
                ];
            }
            $parameter = array(
                "chat_id" => $chat_id,
                "message_id" => $message_id,
                "text" => $messages,
                "parse_mode" => "HTML",
                "disable_web_page_preview" => true,
                'reply_markup' => $keyboard
            );
            $this->http_post_data('editMessageText', json_encode($parameter));


        } elseif (strpos($text, 'lastgroup') !== false) {
            //上一页：
            $text_arr = explode("###", $text);  //findnext  1
            $page = $text_arr[1];   //第几页


            if (strpos($text, '&&&order') !== false) {
                //下一页
                $uid_arr = explode("***", $text);  //findnext  1
                $uid_info = $uid_arr[1];
                if ($uid_info == "0") {
                    $sql_count = "select count(*) from pay_order";
                } else {
                    $sql_count = "select count(*) from pay_order where uid = '" . $uid_info . "'";
                }

                // $sql_count = "select count(*) from pay_order";

                $q = $this->pdo->query($sql_count);
                $rows = $q->fetch();
                $count_info = $rows[0];


                $pageshow = 20;
                $pagesize = ($page - 1) * $pageshow;
                //计算总页数:
                $numpages = ceil($count_info / $pageshow);   //向上取整；

                $prevpage = $page - 1;
                $nextpage = $page + 1;

                //$sql = "select trade_no,money,type,status from pay_order order by trade_no desc  limit " . $pagesize . "," . $pageshow;
                if ($uid_info == "0") {
                    $sql = "select waiwangip,trade_no,money,type,status from pay_order order by trade_no desc  limit " . $pagesize . "," . $pageshow;
                } else {
                    $sql = "select waiwangip,trade_no,money,type,status from pay_order where uid='" . $uid_info . "' order by trade_no desc  limit " . $pagesize . "," . $pageshow;
                }


                $order_query = $this->pdo->query($sql);
                $order_info = $order_query->fetchAll();

                $messages = "";
                foreach ($order_info as $key => $value) {
                    //2022062114155153521 (https://g.com/)~50元~🦋~✅
                    ///order_detail2022062114155153521~50元~🍀~✖️
                    if ($value['type'] == "1") {
                        $change_type = "🦋";
                    } else {
                        $change_type = "🍀";
                    }
                    if ($value['status'] == "1") {
                        $change_type2 = "✅";
                    } else {
                        $change_type2 = "✖";
                    }
                    /*if($value['waiwangip']==0){
                        $waiwang = "内";
                    }else{
                        $waiwang = "外";
                    }*/
                    if ($value['type'] == 1) {
                        $waiwang = "支";
                    } else {
                        $waiwang = "微";
                    }

                    $new_order_sn = $this->orderzhuan($value['trade_no']);

                    //$messages .= "/order_detail" . $value['trade_no'] . "~" . $value['money'] . "元~" . $change_type . "~" . $change_type2 . "\n\r";
                    // $messages .= "<b><a href='https://t.me/tianshidierg_bot?start=order_detail" . $value['trade_no'] . "'>" . $value['trade_no'] . "</a></b>~<b>" . $value['money'] . "元</b>~" . $change_type . "~" . $change_type2 . "\n\r";
                    $tianshi_bot_url = $this->tianshi_bot_url;
                    if ($uid_info == "0") {
                        $typelist = "order_detail";
                    } else {
                        $typelist = "shang_detail";
                    }

                    $messages .= "<b><a href='" . $tianshi_bot_url . "?start=" . $typelist . $value['trade_no'] . "'>" . $new_order_sn . "</a></b>~" . $waiwang . "~<b>" . $value['money'] . "元</b>~" . $change_type2 . "\n\r";


                }
                $inline_keyboard_arr = array();
                if ($prevpage != "0") {
                    $inline_keyboard_arr[] = array('text' => "上一页", "callback_data" => "lastgroup###" . $prevpage . "&&&order***" . $uid_info);
                }

                if ($numpages > $page) {
                    $inline_keyboard_arr[] = array('text' => "下一页", "callback_data" => "nextgroup###" . $nextpage . "&&&order***" . $uid_info);
                    // $inline_keyboard_arr[] = array('text' => "搜索", "callback_data" => "findorderonly");
                } else {
                    // $inline_keyboard_arr[] = array('text' => "搜索", "callback_data" => "findorderonly");
                }

                $keyboard = [
                    'inline_keyboard' => [
                        $inline_keyboard_arr
                    ]
                ];
            } else {


                $sql_count = "select count(*) from pay_uset ";
                $q = $this->pdo->query($sql_count);
                $rows = $q->fetch();
                $count_info = $rows[0];

                $pageshow = 20;
                $pagesize = ($page - 1) * $pageshow;
                //计算总页数:
                $numpages = ceil($count_info / $pageshow);   //向上取整；

                $prevpage = $page - 1;
                $nextpage = $page + 1;

                $sql = "select a.id,a.uid,b.money,b.username from pay_uset as a left join pay_user as b on b.uid=a.uid group by a.uid limit " . $pagesize . "," . $pageshow;
                $order_query = $this->pdo->query($sql);
                $order_info = $order_query->fetchAll();

                $messages = "";
                $inline_keyboard_arr = array();
                foreach ($order_info as $key => $value) {

                    $messages .= "/userxq" . $value['uid'] . "---" . $value['money'] . "----" . $value['username'] . " /del" . $value['uid'] . "\n\r";

                }


                if ($page > 1) {
                    $inline_keyboard_arr[0] = array('text' => "上一页", "callback_data" => "nextgroup###" . $prevpage);
                    if ($numpages > $page) {
                        $inline_keyboard_arr[1] = array('text' => "下一页", "callback_data" => "lastgroup###" . $nextpage);
                    }
                } else {
                    if ($numpages > $page) {
                        $inline_keyboard_arr[0] = array('text' => "下一页", "callback_data" => "lastgroup###" . $nextpage);
                    }
                }

                // if ($prevpage != "0") {
                //     $inline_keyboard_arr[0] = array('text' => "下一页", "callback_data" => "lastgroup###" . $nextpage);
                // }


                // if ($numpages > $page) {
                //     $inline_keyboard_arr[1] = array('text' => "上一页", "callback_data" => "nextgroup###" . $prevpage);

                // }
            }
            $keyboard = [
                'inline_keyboard' => [
                    $inline_keyboard_arr
                ]
            ];
            $parameter = array(
                "chat_id" => $chat_id,
                "message_id" => $message_id,
                "text" => $messages,
                "parse_mode" => "HTML",
                "disable_web_page_preview" => true,
                'reply_markup' => $keyboard
            );
            $this->http_post_data('editMessageText', json_encode($parameter));
        } elseif (strpos($text, 'xiafa_user_') !== false) {

            $chuge_userid_arr = $this->chaojiyonghu;
            if (!in_array($from_id, $chuge_userid_arr)) {
                $ids_str = implode(",", $chuge_userid_arr);
                $parameter = array(
                    'chat_id' => $chat_id,
                    'parse_mode' => 'HTML',
                    'text' => "仅Tg_ID:" . $ids_str . "有此权限！"
                );
                $this->http_post_data('sendMessage', json_encode($parameter));

                $parameter = array(
                    'callback_query_id' => $data['callback_query']['id'],
                    'text' => "",
                );
                $this->http_post_data('answerCallbackQuery', json_encode($parameter));
                exit();
            }


            $xiafatime = strtotime(date('Y-m-d', time()));
            $sql_xiafa = "select id from pay_xfxz where chatid ='" . $chat_id . "' and xiafatime='" . $xiafatime . "'";

            $order_xiafa = $this->pdo->query($sql_xiafa);
            $chatinfo_xiafa = $order_xiafa->fetchAll();
            if ($chatinfo_xiafa) {
                $parameter = array(
                    'chat_id' => $chat_id,
                    'text' => "正在进行下发U的操作！请勿重复点击按钮",
                    'show_alert' => true
                );
                $this->http_post_data('sendMessage', json_encode($parameter));
                $parameter = array(
                    'callback_query_id' => $data['callback_query']['id'],
                    'text' => "",
                );
                $this->http_post_data('answerCallbackQuery', json_encode($parameter));
                exit();
            }


            //下发：xiafa_user_  xiafa_user_1010&&39U
            /*$parameter = array(
                'chat_id' => $chat_id,
                'text' => $text,
                'show_alert'=>true
            );
            $this->http_post_data('sendMessage', json_encode($parameter));
            exit();*/
            //多 下发：xiafa_user_  xiafa_user_1010|1010&&1#1

            $text_new = explode("user_", $text);  //findnext  1
            $text_arr = explode("&&", $text_new[1]);  //findnext  1
            $today = date("Y-m-d", strtotime("-1 day"));
            $ubi = $text_arr['1'];

            $uid_arr = explode("|", $text_arr['0']);
            $uid = $text_arr['0'];

            //查询结算是否已经下发：
            if (count($uid_arr) > 1) {
                $uids = $uid_arr['1'];
                $sql_info_u2 = "select * from pay_xiafau where uid ='" . $uids . "' and date='" . $today . "'";
            } else {
                $sql_info_u2 = "select * from pay_xiafau where uid ='" . $uid . "' and date='" . $today . "'";
            }

            $order_query_user_u2 = $this->pdo->query($sql_info_u2);
            $xiafa_i_u2 = $order_query_user_u2->fetchAll();
            if ($xiafa_i_u2) {
                $parameter = array(
                    'chat_id' => $chat_id,
                    'text' => "已经下发过了！请勿重复点击！！！",
                    'show_alert' => true
                );
                $this->http_post_data('sendMessage', json_encode($parameter));
                $parameter = array(
                    'callback_query_id' => $data['callback_query']['id'],
                    'text' => "",
                );
                $this->http_post_data('answerCallbackQuery', json_encode($parameter));
                exit();
            }


            $set_sql1 = "select * FROM pay_uset where uid='" . $uid . "'";
            $order_query2 = $this->pdo->query($set_sql1);
            $order_info2 = $order_query2->fetchAll();
            $not_time = strtotime(date('Y-m-d'), time());
            $set_sql = "insert into pay_xfxz (chatid,status,xiafatime) values ('" . $chat_id . "','" . "1" . ",'" . $not_time . "')";
            $this->pdo->exec($set_sql);

            if (count($uid_arr) > 1) {
                //多个uid

                //分开别人的U币：
                $every_ubi = explode("#", $ubi);


                $param_data = "";
                $ownerAddress = $this->ownerAddress;
                //获取trx信息  get
                $url2 = "http://66.42.50.142:8595/tronapi/tron/trc20QueryBalance/" . $ownerAddress;
                $submitData2 = Http::get($url2, $param_data);
                $two_result = json_decode($submitData2, true);
                if ($two_result['balance'] / 1000000 < $ubi) {

                    $set_sql = "DELETE FROM pay_xfxz where chatid='" . $chat_id . "'";
                    $this->pdo->exec($set_sql);

                    $parameter = array(
                        'chat_id' => $chat_id,
                        'parse_mode' => 'HTML',
                        'text' => "很抱歉，你的U币不足以下发,当前余额：" . $two_result['balance'] / 1000000
                    );
                    $this->http_post_data('sendMessage', json_encode($parameter));
                    exit();
                }

                $all_ui = 0;

                for ($i = 0; $i < count($uid_arr); $i++) {
                    $set_sql1 = "select * FROM pay_uset where uid='" . $uid_arr[$i] . "'";
                    $order_query2 = $this->pdo->query($set_sql1);
                    $order_info2 = $order_query2->fetchAll();


                    $ToAdress = $order_info2[0]['five'];

                    $param_data = array(
                        "ownerAddress" => $ownerAddress,
                        "toAddress" => $ToAdress,
                        "memo" => "",
                        "amount" => $every_ubi[$i] * 1000000
                    );
                    $param_data_new = array(
                        "owner_address" => $ownerAddress,
                        "to_address" => $ToAdress,
                        "private_key" => "0a788da1d4c21fb7f4cddb74f10b25ca53b7ed339129939d056826e108e175d4",
                        "amount" => $every_ubi[$i]
                    );
                    //改用最新的：
                    $url4 = $this->pay_pay_url."/api/index/transferUsdt";
                    $url3 = "http://66.42.50.142:8595/tronapi/tron/trc20CreateTransaction";
                    //$submitData3 = Http::http_post_data_two($url3, json_encode($param_data));
                    $submitData3 = Http::http_post_data_two($url4, json_encode($param_data_new));

                    $three_result = json_decode($submitData3, true);


                    if (!empty($three_result['data']['txid'])) {

                        $all_ui += $every_ubi[$i];


                        $set_sql = "insert into pay_xiafau (uid,date,createtime,xiafau,txId) values ('" . $uid_arr[$i] . "','" . $today . "','" . time() . "','" . $every_ubi[$i] . "','" . $three_result['data']['txid'] . "')";
                        $this->pdo->exec($set_sql);


                        $set_sql = "DELETE FROM pay_xfxz where chatid='" . $chat_id . "'";
                        $this->pdo->exec($set_sql);

                        $parameter = array(
                            'chat_id' => $chat_id,
                            'parse_mode' => 'HTML',
                            'text' => "成功下发商户" . "[" . $uid_arr[$i] . "]" . $every_ubi[$i] . "U"
                        );


                        $this->http_post_data('sendMessage', json_encode($parameter));
                    } else {

                        $set_sql = "DELETE FROM pay_xfxz where chatid='" . $chat_id . "'";
                        $this->pdo->exec($set_sql);

                        $parameter = array(
                            'chat_id' => $chat_id,
                            'parse_mode' => 'HTML',
                            'text' => "下发失败！请联系天使楚歌,单独结算！"
                        );
                        $this->http_post_data('sendMessage', json_encode($parameter));
                        exit();
                    }
                }
                $uid_end = $uid;

                $today = date("Y-m-d", strtotime("-1 day"));
                $todays = date("Y年m月d日", strtotime("-1 day"));

                $uid_arr = explode("|", $uid);
                if (count($uid_arr) > 1) {
                    $message = "";
                    $message .= "<strong>⏰" . $todays . "结算:</strong>\n\r\n\r";


                    $ems_all_end = "0";
                    $ems_all_str_end = "";

                    for ($j = 0; $j < count($uid_arr); $j++) {
                        //查询次商户号今日总收入信息：
                        $uid = $uid_arr[$j];
                        $sql_info = "select sum(getmoney) as getmoney from pay_order where status = '1' and uid ='" . $uid . "' and date='" . $today . "'";

                        $order_query3 = $this->pdo->query($sql_info);
                        $chatinfo = $order_query3->fetchAll();
                        $order_today = round($chatinfo[0]['getmoney'], 2);


                        $sql_info2 = "select * from pay_uset where  uid ='" . $uid . "'";
                        $order_query6 = $this->pdo->query($sql_info2);
                        $chatinfo2 = $order_query6->fetchAll();
                        $uidinfo = $chatinfo2[0];


                        $sql_info3 = "select username from pay_user where  uid ='" . $uid . "'";
                        $order_query7 = $this->pdo->query($sql_info3);
                        $chatinfo3 = $order_query7->fetchAll();
                        $uidinfo2 = $chatinfo3[0];

                        //ｕ＝2323＊0.8＊0.94／6.4=238u

                        $message .= "<strong>🆔商户号:" . $uid . "</strong>\n\r";
                        $message .= "<strong>🧑🏻‍💼名字:" . $uidinfo2['username'] . "</strong>\n\r";
                        //$message .= "昨日收入：".$order_today."元\n\r";

                        if ($order_today <= 0) {

                            $message .= "<strong>💰收入结算:" . "0" . "u</strong>\n\r\n\r\n\r";
                        } else {
                            $ems_all = "0";
                            $ems_all_str = "";
                            $set_sql1 = "select typelist FROM pay_uset where uid='" . $uid . "'";
                            $order_query_user = $this->pdo->query($set_sql1);
                            $chatinfo_usertype = $order_query_user->fetchAll();
                            foreach ($chatinfo_usertype as $key2 => $value2) {
                                $sql_info2 = "select * from pay_uset where  uid ='" . $uid . "' and typelist='" . $value2['typelist'] . "'";
                                $order_query6 = $this->pdo->query($sql_info2);
                                $chatinfo2 = $order_query6->fetchAll();
                                $uidinfo = $chatinfo2[0];

                                $type = substr($uidinfo['four'], 0, 1);


                                $sql_info2 = "SELECT sum(getmoney) as getmoney FROM pay_order WHERE uid='" . $uid . "' AND type=(SELECT id FROM pay_type WHERE name='" . $value2['typelist'] . "') AND status=1 AND date='" . $today . "'";
                                $order_query4 = $this->pdo->query($sql_info2);
                                $chatinfo2 = $order_query4->fetchAll();
                                $order_today2 = round($chatinfo2[0]['getmoney'], 2);
                                if ($type == "-") {
                                    $changs = explode("-", $uidinfo['four']);
                                    $ems = intval($order_today2 * $uidinfo['one'] * $uidinfo['two'] / ($uidinfo['three'] - $changs[1]));
                                    $sss = $uidinfo['three'] - $changs[1];
                                    $message .= "<strong>💰" . $new_type[$value2['typelist']] . "结算:" . $order_today2 . "*" . $uidinfo['one'] . "*" . $uidinfo['two'] . "/" . $sss . "=" . $ems . "u" . "</strong>" . "\n\r";

                                } else {
                                    $changs = explode("+", $uidinfo['four']);
                                    $ems = intval($order_today2 * $uidinfo['one'] * $uidinfo['two'] / ($uidinfo['three'] + $changs[1]));
                                    $sss = $uidinfo['three'] + $changs[1];
                                    $message .= "<strong>💰" . $new_type[$value2['typelist']] . "结算:" . $order_today2 . "*" . $uidinfo['one'] . "*" . $uidinfo['two'] . "/" . $sss . "=" . $ems . "u</strong>" . "\n\r";
                                }
                                $ems_all += $ems;
                                $ems_all_str .= $ems . "u+";
                            }


                            $ems_all_str = substr($ems_all_str, 0, -1);

                            $message .= "<strong>🈴单商户合计:" . $ems_all_str . "=" . $ems_all . "u</strong>\n\r\n\r";

                            $ems_all_end += $ems_all;
                            $ems_all_str_end .= $ems_all . "u+";
                        }

                    }
                    $ems_all_str_end = substr($ems_all_str_end, 0, -1);
                    $message .= "<strong>🈴总合计:" . $ems_all_str_end . "=" . $ems_all_end . "u</strong>\n\r\n\r";

                    //查询结算是否已经下发：
                    $sql_info_u = "select * from pay_xiafau where uid ='" . $uid_end . "' and date='" . $today . "'";
                    $order_query_user_u = $this->pdo->query($sql_info_u);
                    $xiafa_i_u = $order_query_user_u->fetchAll();
                    //if($xiafa_i_u){
                    $inline_keyboard_arr[0] = array('text' => "已经下发:" . $ems_all_end . "U", "callback_data" => "yijingxiafa_" . $uid_end);
                    //}else{
                    //  $inline_keyboard_arr[0] = array('text' => "确定下发:".$ems_all_end."U", "callback_data" => "xiafa_user_".$uid_end."&&".$ems_all_end);

                    //}

                } else {
                    //查询次商户号今日总收入信息：
                    $sql_info = "select sum(getmoney) as getmoney from pay_order where status = '1' and uid ='" . $uid . "' and date='" . $today . "'";

                    $order_query3 = $this->pdo->query($sql_info);
                    $chatinfo = $order_query3->fetchAll();
                    $order_today = round($chatinfo[0]['getmoney'], 2);

                    $sql_info3 = "select username from pay_user where  uid ='" . $uid . "'";
                    $order_query7 = $this->pdo->query($sql_info3);
                    $chatinfo3 = $order_query7->fetchAll();
                    $uidinfo2 = $chatinfo3[0];

                    //ｕ＝2323＊0.8＊0.94／6.4=238u
                    $message = "<strong>⏰" . $todays . "结算:</strong>\n\r";
                    $message .= "<strong>🆔商户号:" . $uid . "</strong>\n\r";
                    $message .= "<strong>🧑🏻‍💼名字:" . $uidinfo2['username'] . "</strong>\n\r";
                    //$message .= "昨日收入：".$order_today."元\n\r";

                    $set_sql1 = "select typelist FROM pay_uset where uid='" . $uid . "'";
                    $order_query_user = $this->pdo->query($set_sql1);
                    $chatinfo_usertype = $order_query_user->fetchAll();


                    if ($order_today <= 0) {

                        $message .= "<strong>💰收入结算:0u</strong>";
                    } else {
                        $ems_all = "0";
                        $ems_all_str = "";
                        foreach ($chatinfo_usertype as $key2 => $value2) {
                            $sql_info2 = "select * from pay_uset where  uid ='" . $uid . "' and typelist='" . $value2['typelist'] . "'";
                            $order_query6 = $this->pdo->query($sql_info2);
                            $chatinfo2 = $order_query6->fetchAll();
                            $uidinfo = $chatinfo2[0];

                            $type = substr($uidinfo['four'], 0, 1);


                            $sql_info2 = "SELECT sum(getmoney) as getmoney FROM pay_order WHERE uid='" . $uid . "' AND type=(SELECT id FROM pay_type WHERE name='" . $value2['typelist'] . "') AND status=1 AND date='" . $today . "'";
                            $order_query4 = $this->pdo->query($sql_info2);
                            $chatinfo2 = $order_query4->fetchAll();
                            $order_today2 = round($chatinfo2[0]['getmoney'], 2);
                            if ($type == "-") {
                                $changs = explode("-", $uidinfo['four']);
                                $ems = intval($order_today2 * $uidinfo['one'] * $uidinfo['two'] / ($uidinfo['three'] - $changs[1]));
                                $sss = $uidinfo['three'] - $changs[1];
                                $message .= "<strong>💰" . $new_type[$value2['typelist']] . "结算:" . $order_today2 . "*" . $uidinfo['one'] . "*" . $uidinfo['two'] . "/" . $sss . "=" . $ems . "u" . "</strong>" . "\n\r";

                            } else {
                                $changs = explode("+", $uidinfo['four']);
                                $ems = intval($order_today2 * $uidinfo['one'] * $uidinfo['two'] / ($uidinfo['three'] + $changs[1]));
                                $sss = $uidinfo['three'] + $changs[1];
                                $message .= "<strong>💰" . $new_type[$value2['typelist']] . "结算:" . $order_today2 . "*" . $uidinfo['one'] . "*" . $uidinfo['two'] . "/" . $sss . "=" . $ems . "u</strong>" . "\n\r";
                            }
                            $ems_all += $ems;
                            $ems_all_str .= $ems . "u+";
                        }
                        $ems_all_str = substr($ems_all_str, 0, -1);

                        $message .= "<strong>🈴合计:" . $ems_all_str . "=" . $ems_all . "u</strong>";

                        //查询结算是否已经下发：
                        $sql_info_u = "select * from pay_xiafau where uid ='" . $uid . "' and date='" . $today . "'";
                        $order_query_user_u = $this->pdo->query($sql_info_u);
                        $xiafa_i_u = $order_query_user_u->fetchAll();
                        //if($xiafa_i_u){
                        $inline_keyboard_arr[0] = array('text' => "已经下发:" . $ems_all . "U", "callback_data" => "yijingxiafa_" . $uid_end);
                        //}else{
                        // $inline_keyboard_arr[0] = array('text' => "确定下发:".$ems_all."U", "callback_data" => "xiafa_user_".$uid_end."&&".$ems_all);

                        //}
                    }
                }


                $keyboard = [
                    'inline_keyboard' => [
                        $inline_keyboard_arr
                    ]
                ];


                $parameter2 = array(
                    "chat_id" => $chat_id,
                    "message_id" => $message_id,
                    "text" => $message,
                    "parse_mode" => "HTML",
                    "disable_web_page_preview" => true,
                    'reply_markup' => $keyboard
                );
                $this->http_post_data('editMessageText', json_encode($parameter2));


                $set_sql1 = "select * FROM pay_botsettle where merchant='" . $uid_end . "'";
                $order_query2 = $this->pdo->query($set_sql1);
                $order_info2 = $order_query2->fetchAll();

                $set_sql = "DELETE FROM pay_xfxz where chatid='" . $chat_id . "'";
                $this->pdo->exec($set_sql);

                $parameter = array(
                    'chat_id' => $chat_id,
                    'parse_mode' => 'HTML',
                    'text' => "成功下发：" . $all_ui . "U请知悉：" . " " . $order_info2[0]['atyonghu']
                );
                $this->http_post_data('sendMessage', json_encode($parameter));
                //exit();


            } else {
                //单个uid
                $set_sql1 = "select * FROM pay_uset where uid='" . $uid . "'";
                $order_query2 = $this->pdo->query($set_sql1);
                $order_info2 = $order_query2->fetchAll();

                $param_data = "";

                $ownerAddress = $this->ownerAddress;
                //获取trx信息  get
               /* $url2 = "http://66.42.50.142:8595/tronapi/tron/trc20QueryBalance/" . $ownerAddress;
                $submitData2 = Http::get($url2, $param_data);
                $two_result = json_decode($submitData2, true);
                if ($two_result['balance'] / 1000000 < $ubi) {
                    $set_sql = "DELETE FROM pay_xfxz where chatid='" . $chat_id . "'";
                    $this->pdo->exec($set_sql);
                    $parameter = array(
                        'chat_id' => $chat_id,
                        'parse_mode' => 'HTML',
                        'text' => "很抱歉，你的U币不足以下发,当前余额：" . $two_result['balance'] / 1000000
                    );
                    $this->http_post_data('sendMessage', json_encode($parameter));
                    exit();
                }*/
                $ToAdress = $order_info2[0]['five'];
                //$ToAdress = "TXF56XtSwpbszkpPFJ31FHHrPakVUk9YqJ";
                $param_data = array(
                    "ownerAddress" => $ownerAddress,
                    "toAddress" => $ToAdress,
                    "memo" => "",
                    "amount" => $ubi * 1000000
                );
                $param_data_new = array(
                    "owner_address" => $ownerAddress,
                    "to_address" => $ToAdress,
                    "private_key" => $this->private_key,
                    "amount" => $ubi
                );
                $url3 = "http://66.42.50.142:8595/tronapi/tron/trc20CreateTransaction";
                //改用最新的：
                $url4 = $this->pay_pay_url."/api/index/transferUsdt";

                //$submitData3 = Http::http_post_data_two($url3, json_encode($param_data));
                $submitData3 = Http::http_post_data_two($url4, json_encode($param_data_new));
                $three_result = json_decode($submitData3, true);
               /* if ($three_result['code'] == "0") {
                    $parameter = array(
                        'chat_id' => $chat_id,
                        'parse_mode' => 'HTML',
                        'text' => "转账下发失败，请联系天使客服,错误信息：" . $submitData3['msg']
                    );
                    $this->http_post_data('sendMessage', json_encode($parameter));
                    exit();

                }*/
                if ($three_result['code'] == "0") {

                    if (strpos($three_result['msg'], '能量不足') !== false || strpos($three_result['msg'], '带宽不足') !== false) {
                        // 包含能量不足或带宽不足
                        //echo "匹配到限制信息";
                        // 发生异常时执行删除操作
                        $set_sql = "DELETE FROM pay_zuorixiafau where id='" . $insert_id . "'";
                        $this->pdo->exec($set_sql);
                        $this->xiaoxi($three_result['msg'],$chat_id);
                    }

                    $inline_keyboard_arr_xianzhi[0] = array('text' => "解除昨日下发限制", "callback_data" => "jiechuxiafaxianzhi_".$uid);
                    $keyboard_xianzhi = [
                        'inline_keyboard' => [
                            $inline_keyboard_arr_xianzhi
                        ]
                    ];
                    $parameter2 = array(
                        "chat_id" => $chat_id,
                        'text' => "转账下发失败，请联系天使客服,错误信息：" . $three_result['msg'],
                        "parse_mode" => "HTML",
                        "disable_web_page_preview" => true,
                        'reply_markup' => $keyboard_xianzhi
                    );

                    $this->http_post_data('sendMessage', json_encode($parameter2));

                    exit();

                }

                /*$param_data2 = array(
                    'address' => "TDCZarzhayFWro6BWAoA1qPsvnVDecZaYL",
                    "txid" => $three_result['txId']
                );
                $url4 = "http://66.42.50.142:8595/tronapi/tron/getTransactionById";
                $submitData4 = Http::http_post_data_two($url4, json_encode($param_data2));

                $foru_result = json_decode($submitData4, true);*/


                if (!empty($three_result['data']['txid'])) {
                    //确定下发了，也要改变状态：
                    $set_sql = "insert into pay_xiafau (uid,date,createtime,xiafau,txId) values ('" . $uid . "','" . $today . "','" . time() . "','" . $ubi . "','" . $three_result['data']['txid'] . "')";
                    $this->pdo->exec($set_sql);

                    $uid_end = $uid;

                    $today = date("Y-m-d", strtotime("-1 day"));
                    $todays = date("Y年m月d日", strtotime("-1 day"));

                    $uid_arr = explode("|", $uid);
                    if (count($uid_arr) > 1) {
                        $message = "";
                        $message .= "<strong>⏰" . $todays . "结算:</strong>\n\r\n\r";


                        $ems_all_end = "0";
                        $ems_all_str_end = "";

                        for ($j = 0; $j < count($uid_arr); $j++) {
                            //查询次商户号今日总收入信息：
                            $uid = $uid_arr[$j];
                            $sql_info = "select sum(getmoney) as getmoney from pay_order where status = '1' and uid ='" . $uid . "' and date='" . $today . "'";

                            $order_query3 = $this->pdo->query($sql_info);
                            $chatinfo = $order_query3->fetchAll();
                            $order_today = round($chatinfo[0]['getmoney'], 2);


                            $sql_info2 = "select * from pay_uset where  uid ='" . $uid . "'";
                            $order_query6 = $this->pdo->query($sql_info2);
                            $chatinfo2 = $order_query6->fetchAll();
                            $uidinfo = $chatinfo2[0];


                            $sql_info3 = "select username from pay_user where  uid ='" . $uid . "'";
                            $order_query7 = $this->pdo->query($sql_info3);
                            $chatinfo3 = $order_query7->fetchAll();
                            $uidinfo2 = $chatinfo3[0];

                            //ｕ＝2323＊0.8＊0.94／6.4=238u

                            $message .= "<strong>🆔商户号:" . $uid . "</strong>\n\r";
                            $message .= "<strong>🧑🏻‍💼名字:" . $uidinfo2['username'] . "</strong>\n\r";
                            //$message .= "昨日收入：".$order_today."元\n\r";

                            if ($order_today <= 0) {

                                $message .= "<strong>💰收入结算:" . "0" . "u</strong>\n\r\n\r\n\r";
                            } else {
                                $ems_all = "0";
                                $ems_all_str = "";
                                $set_sql1 = "select typelist FROM pay_uset where uid='" . $uid . "'";
                                $order_query_user = $this->pdo->query($set_sql1);
                                $chatinfo_usertype = $order_query_user->fetchAll();
                                foreach ($chatinfo_usertype as $key2 => $value2) {
                                    $sql_info2 = "select * from pay_uset where  uid ='" . $uid . "' and typelist='" . $value2['typelist'] . "'";
                                    $order_query6 = $this->pdo->query($sql_info2);
                                    $chatinfo2 = $order_query6->fetchAll();
                                    $uidinfo = $chatinfo2[0];

                                    $type = substr($uidinfo['four'], 0, 1);


                                    $sql_info2 = "SELECT sum(getmoney) as getmoney FROM pay_order WHERE uid='" . $uid . "' AND type=(SELECT id FROM pay_type WHERE name='" . $value2['typelist'] . "') AND status=1 AND date='" . $today . "'";
                                    $order_query4 = $this->pdo->query($sql_info2);
                                    $chatinfo2 = $order_query4->fetchAll();
                                    $order_today2 = round($chatinfo2[0]['getmoney'], 2);
                                    if ($type == "-") {
                                        $changs = explode("-", $uidinfo['four']);
                                        $ems = intval($order_today2 * $uidinfo['one'] * $uidinfo['two'] / ($uidinfo['three'] - $changs[1]));
                                        $sss = $uidinfo['three'] - $changs[1];
                                        $message .= "<strong>💰" . $new_type[$value2['typelist']] . "结算:" . $order_today2 . "*" . $uidinfo['one'] . "*" . $uidinfo['two'] . "/" . $sss . "=" . $ems . "u" . "</strong>" . "\n\r";

                                    } else {
                                        $changs = explode("+", $uidinfo['four']);
                                        $ems = intval($order_today2 * $uidinfo['one'] * $uidinfo['two'] / ($uidinfo['three'] + $changs[1]));
                                        $sss = $uidinfo['three'] + $changs[1];
                                        $message .= "<strong>💰" . $new_type[$value2['typelist']] . "结算:" . $order_today2 . "*" . $uidinfo['one'] . "*" . $uidinfo['two'] . "/" . $sss . "=" . $ems . "u</strong>" . "\n\r";
                                    }
                                    $ems_all += $ems;
                                    $ems_all_str .= $ems . "u+";
                                }


                                $ems_all_str = substr($ems_all_str, 0, -1);

                                $message .= "<strong>🈴单商户合计:" . $ems_all_str . "=" . $ems_all . "u</strong>\n\r\n\r";

                                $ems_all_end += $ems_all;
                                $ems_all_str_end .= $ems_all . "u+";
                            }

                        }
                        $ems_all_str_end = substr($ems_all_str_end, 0, -1);
                        $message .= "<strong>🈴总合计:" . $ems_all_str_end . "=" . $ems_all_end . "u</strong>\n\r\n\r";

                        //查询结算是否已经下发：
                        $sql_info_u = "select * from pay_xiafau where uid ='" . $uid_end . "' and date='" . $today . "'";
                        $order_query_user_u = $this->pdo->query($sql_info_u);
                        $xiafa_i_u = $order_query_user_u->fetchAll();
                        //if($xiafa_i_u){
                        $inline_keyboard_arr[0] = array('text' => "已经下发:" . $ems_all_end . "U", "callback_data" => "yijingxiafa_" . $uid_end);
                        //}else{
                        //  $inline_keyboard_arr[0] = array('text' => "确定下发:".$ems_all_end."U", "callback_data" => "xiafa_user_".$uid_end."&&".$ems_all_end);

                        //}

                    } else {
                        //查询次商户号今日总收入信息：
                        $sql_info = "select sum(getmoney) as getmoney from pay_order where status = '1' and uid ='" . $uid . "' and date='" . $today . "'";

                        $order_query3 = $this->pdo->query($sql_info);
                        $chatinfo = $order_query3->fetchAll();
                        $order_today = round($chatinfo[0]['getmoney'], 2);

                        $sql_info3 = "select username from pay_user where  uid ='" . $uid . "'";
                        $order_query7 = $this->pdo->query($sql_info3);
                        $chatinfo3 = $order_query7->fetchAll();
                        $uidinfo2 = $chatinfo3[0];

                        //ｕ＝2323＊0.8＊0.94／6.4=238u
                        $message = "<strong>⏰" . $todays . "结算:</strong>\n\r";
                        $message .= "<strong>🆔商户号:" . $uid . "</strong>\n\r";
                        $message .= "<strong>🧑🏻‍💼名字:" . $uidinfo2['username'] . "</strong>\n\r";
                        //$message .= "昨日收入：".$order_today."元\n\r";

                        $set_sql1 = "select typelist FROM pay_uset where uid='" . $uid . "'";
                        $order_query_user = $this->pdo->query($set_sql1);
                        $chatinfo_usertype = $order_query_user->fetchAll();


                        if ($order_today <= 0) {

                            $message .= "<strong>💰收入结算:0u</strong>";
                        } else {
                            $ems_all = "0";
                            $ems_all_str = "";
                            foreach ($chatinfo_usertype as $key2 => $value2) {
                                $sql_info2 = "select * from pay_uset where  uid ='" . $uid . "' and typelist='" . $value2['typelist'] . "'";
                                $order_query6 = $this->pdo->query($sql_info2);
                                $chatinfo2 = $order_query6->fetchAll();
                                $uidinfo = $chatinfo2[0];

                                $type = substr($uidinfo['four'], 0, 1);


                                $sql_info2 = "SELECT sum(getmoney) as getmoney FROM pay_order WHERE uid='" . $uid . "' AND type=(SELECT id FROM pay_type WHERE name='" . $value2['typelist'] . "') AND status=1 AND date='" . $today . "'";
                                $order_query4 = $this->pdo->query($sql_info2);
                                $chatinfo2 = $order_query4->fetchAll();
                                $order_today2 = round($chatinfo2[0]['getmoney'], 2);
                                if ($type == "-") {
                                    $changs = explode("-", $uidinfo['four']);
                                    $ems = intval($order_today2 * $uidinfo['one'] * $uidinfo['two'] / ($uidinfo['three'] - $changs[1]));
                                    $sss = $uidinfo['three'] - $changs[1];
                                    $message .= "<strong>💰" . $new_type[$value2['typelist']] . "结算:" . $order_today2 . "*" . $uidinfo['one'] . "*" . $uidinfo['two'] . "/" . $sss . "=" . $ems . "u" . "</strong>" . "\n\r";

                                } else {
                                    $changs = explode("+", $uidinfo['four']);
                                    $ems = intval($order_today2 * $uidinfo['one'] * $uidinfo['two'] / ($uidinfo['three'] + $changs[1]));
                                    $sss = $uidinfo['three'] + $changs[1];
                                    $message .= "<strong>💰" . $new_type[$value2['typelist']] . "结算:" . $order_today2 . "*" . $uidinfo['one'] . "*" . $uidinfo['two'] . "/" . $sss . "=" . $ems . "u</strong>" . "\n\r";
                                }
                                $ems_all += $ems;
                                $ems_all_str .= $ems . "u+";
                            }
                            $ems_all_str = substr($ems_all_str, 0, -1);

                            $message .= "<strong>🈴合计:" . $ems_all_str . "=" . $ems_all . "u</strong>";

                            //查询结算是否已经下发：
                            $sql_info_u = "select * from pay_xiafau where uid ='" . $uid . "' and date='" . $today . "'";
                            $order_query_user_u = $this->pdo->query($sql_info_u);
                            $xiafa_i_u = $order_query_user_u->fetchAll();
                            //if($xiafa_i_u){
                            $inline_keyboard_arr[0] = array('text' => "已经下发:" . $ems_all . "U", "callback_data" => "yijingxiafa_" . $uid_end);
                            //}else{
                            // $inline_keyboard_arr[0] = array('text' => "确定下发:".$ems_all."U", "callback_data" => "xiafa_user_".$uid_end."&&".$ems_all);

                            //}
                        }
                    }


                    $keyboard = [
                        'inline_keyboard' => [
                            $inline_keyboard_arr
                        ]
                    ];


                    $parameter2 = array(
                        "chat_id" => $chat_id,
                        "message_id" => $message_id,
                        "text" => $message,
                        "parse_mode" => "HTML",
                        "disable_web_page_preview" => true,
                        'reply_markup' => $keyboard
                    );
                    $this->http_post_data('editMessageText', json_encode($parameter2));

                    $set_sql1 = "select * FROM pay_botsettle where merchant='" . $uid . "'";
                    $order_query2 = $this->pdo->query($set_sql1);
                    $order_info2 = $order_query2->fetchAll();

                    $set_sql = "DELETE FROM pay_xfxz where chatid='" . $chat_id . "'";
                    $this->pdo->exec($set_sql);


                    $msp = "<b>" . date("m月d日", time()) . "---成功下发" . $ubi . "U,请知悉！</b>\r\n\r\nhttps://tronscan.org/#/transaction/" . $three_result['data']['txid'];
                    //"成功下发：" . $ubi . "U,请知悉:" . " " . $order_info2['0']['atyonghu']
                    $parameter = array(
                        'chat_id' => $chat_id,
                        'parse_mode' => 'HTML',
                        'text' => $msp
                    );
                    $this->http_post_data('sendMessage', json_encode($parameter));

                } else {
                    $set_sql = "DELETE FROM pay_xfxz where chatid='" . $chat_id . "'";
                    $this->pdo->exec($set_sql);

                    $inline_keyboard_arr_xianzhi[0] = array('text' => "解除昨日下发限制", "callback_data" => "jiechuxiafaxianzhi_".$uid);
                    $keyboard_xianzhi = [
                        'inline_keyboard' => [
                            $inline_keyboard_arr_xianzhi
                        ]
                    ];
                    /*$parameter = array(
                        'chat_id' => $chat_id,
                        'parse_mode' => 'HTML',
                        'text' => "最后环节下发失败，请联系天使客服"
                    );*/
                    $parameter2 = array(
                        "chat_id" => $chat_id,
                        "text" => "最后环节下发失败，请联系天使客服",
                        "parse_mode" => "HTML",
                        "disable_web_page_preview" => true,
                        'reply_markup' => $keyboard_xianzhi
                    );

                    $this->http_post_data('sendMessage', json_encode($parameter2));
                    exit();
                }

            }
        } elseif (strpos($text, 'yijingxiafa_') !== false) {
            $text_new = explode("_", $text);  //findnext  1
            $parameter = array(
                'chat_id' => $chat_id,
                'parse_mode' => 'HTML',
                'text' => "商户号：" . $text_new[1] . "昨日结算，已经下发,异常情况，请联系天使：@fu_008"
            );
            $this->http_post_data('sendMessage', json_encode($parameter));

        }


        $parameter = array(
            'callback_query_id' => $data['callback_query']['id'],
            'text' => "",
        );
        $this->http_post_data('answerCallbackQuery', json_encode($parameter));


    }

    //type = 0  今日  1=昨日
    public function xiafausdt($pid, $ubi, $usdt_fm, $message_id, $chatid, $data, $chatinfo, $type = "0", $tousu_U2)
    {

        $uid = $pid;
        $chat_id = $chatid;
        $not_time = date('Y-m-d');
        //查看当天是不是有正在下发的数据记录，不管是不是真正成功了，都需要查询
        $set_sql3 = "select * FROM pay_jinrixiafa where pid='" . $pid . "' and chatid='" . $chatid . "' and xiafatime='" . $not_time . "' and status='0'";
        $order_query3 = $this->pdo->query($set_sql3);

        $xiafa_info3 = $order_query3->fetchAll();


        if ($xiafa_info3) {
            //$this->xiaoxi("当前商户今日有正在下发的操作,USDT官方未返回准确消息,\r\n无法再进行下发！需要天使核对，是否发送成功！", $chat_id, '1', $data['callback_query']['id']);
            $insert_id = $xiafa_info3[0]['id'];
            $inline_keyboard_arr_jinrixianzhi[0] = array('text' => "解除今日下发限制", "callback_data" => "jiechujinrixiafaxianzhi_".$insert_id);
            $keyboard_xianzhi = [
                'inline_keyboard' => [
                   $inline_keyboard_arr_jinrixianzhi
                ]
            ];
            $parameter = array(
                'chat_id' => $chat_id,
                'parse_mode' => 'HTML',
                'text' => "当前商户今日有正在下发的操作,USDT官方未返回准确消息,\r\n无法再进行下发！需要天使核对，是否发送成功！",
                "disable_web_page_preview" => true,
                'reply_markup' => $keyboard_xianzhi
            );
            $this->http_post_data('sendMessage', json_encode($parameter));
            exit();
        }

        //单个uid
        $set_sql1 = "select * FROM pay_user where uid='" . $uid . "'";
        $order_query2 = $this->pdo->query($set_sql1);
        $order_info2 = $order_query2->fetchAll();
        //商户USDT地址：
        $ToAdress = $order_info2[0]['usdt_str'];
        if (empty($ToAdress)) {
            $this->xiaoxi("当前商户暂未设置下发USDT的地址,请核对后再下发！", $chat_id, '1', $data['callback_query']['id']);
        }
        $huilvinfo = $this->huilvinfo("99999", "99999");
        $fufonginfo = $this->fudonginfo($uid, $chatid);
        $type222 = substr($fufonginfo, 0, 1);
        if ($type222 == "-") {
            $changs = explode("-", $fufonginfo);
            $shiji_huilv = $huilvinfo - $changs[1];
        } else {
            $changs = explode("+", $fufonginfo);
            $shiji_huilv = $huilvinfo + $changs[1];
        }

        //查询用户的下发限制信息[是否可以下发]：
        $user_xianzhi = $this->shujuku("select * from pay_xiafashezhi where pid='" . $uid . "' and leixing='1' and type='1'");
        if (!$user_xianzhi) {
            $this->xiaoxi("当前商户暂未设置实时下发限制配置,请先联系天使相关人员设置！", $chat_id, '1', $data['callback_query']['id']);
        } else {
            foreach ($user_xianzhi as $k => $v) {
                if ($v['typelist'] == '1') {
                    if ($v['svalue'] != "是") {
                        $this->xiaoxi("当前商户不支持实时下发！", $chat_id, '1', $data['callback_query']['id']);
                    }
                }
                if ($v['typelist'] == '2') {
                    if ($v['svalue'] <= 0) {
                        $this->xiaoxi("当前商户U币金额实时下发不满足条件！", $chat_id, '1', $data['callback_query']['id']);
                    } else {

                        $xiafa_bu = $ubi * $shiji_huilv;


                        if ($v['svalue'] > $xiafa_bu) {
                            $this->xiaoxi("当前商户金额实时下发不满足条件！最低：" . $v['svalue'] . "元", $chat_id, '1', $data['callback_query']['id']);
                        }
                    }
                }
                if ($v['typelist'] == '3') {
                    if ($v['svalue'] <= 0) {
                        $this->xiaoxi("当前商户实时下发的次数限制不符合条件！", $chat_id, '1', $data['callback_query']['id']);
                    } else {
                        //查询今日下发的次数：
                        $set_sql4 = "select * FROM pay_jinrixiafa where pid='" . $pid . "' and chatid='" . $chatid . "' and xiafatime='" . $not_time . "' and status='1'";
                        $order_query4 = $this->shujuku($set_sql4);
                        if (count($order_query4) >= $v['svalue']) {
                            $this->xiaoxi("当前商户今日实时下发次数已过：" . $v['svalue'] . "次！暂不支持继续实时下发", $chat_id, '1', $data['callback_query']['id']);

                        }
                    }
                }

            }
        }
        $trx_info = "select * from pay_usertrx";
        $trx_jinri = $this->pdo->query($trx_info);
        $trx_arr = $trx_jinri->fetchAll();

        if ($trx_arr) {
            $trx_shouxufei = $trx_arr[0]['trx'];
        } else {
            $trx_shouxufei = 0.00;
        }

        $ubi_shouxu = $ubi;// + $trx_shouxufei;

        $huilvinfo = $this->huilvinfo("99999", "99999");
        $fufonginfo = $this->fudonginfo($uid, $chat_id);
        $type222 = substr($fufonginfo, 0, 1);
        if ($type222 == "-") {
            $changs = explode("-", $fufonginfo);
            $shiji_huilv = $huilvinfo - $changs[1];
        } else {
            $changs = explode("+", $fufonginfo);
            $shiji_huilv = $huilvinfo + $changs[1];
        }


        $set_sql = "insert into pay_jinrixiafa (pid,chatid,xiafatime,money,feiu_money,jutishijian,status,feilv) values ('" . $pid . "','" . $chatid . "','" . $not_time . "','" . $ubi_shouxu . "','" . $usdt_fm . "','" . time() . "','0','" . $shiji_huilv . "')";
        $this->pdo->exec($set_sql);
        $insert_id = $this->pdo->lastInsertId();


        $param_data = "";


        //先查下发对方地址的余额：
        /*$yecha_url = "http://66.42.50.142:8595/tronapi/tron/trc20Balance/".$ToAdress;
        $json_result_yuecha = Http::get($yecha_url, $param_data);
        $result_yuecha = json_decode($json_result_yuecha, true);
        $daikuan = 360;
        if($result_yuecha['usdt']>0){
            //1,如果对方有u  我方打款地址能量必须>64k，带宽>360，否则提示：能量带宽不足64k，请补充
            $nengliang = 64000;
            $nengliang_str = "64K";
        }else{
            //如果对方没有u   我方打款地址能量必须>130k，带宽>360，，否则提示：能量带宽不足130k，请补充
            $nengliang = 130000;
            $nengliang_str = "130k";
        }*/
        $nengliang_arr = $this->duifangyuer($ToAdress);
        $nengliang = $nengliang_arr[0];
        $nengliang_str =  $nengliang_arr[1];

        $ownerAddress = $this->ownerAddress;
         //查老板的钱包能量
//        $nengliang_url = "http://66.42.50.142:8595/tronapi/tron/simpleAccountInfo/".$ownerAddress;
//        $json_result_nengliang = Http::get($nengliang_url, $param_data);
//        $result_nengliang = json_decode($json_result_nengliang, true);
//        if($result_nengliang['能量']<=$nengliang){
//            //下发失败的话
//            $set_sql = "DELETE FROM pay_jinrixiafa where id='" . $insert_id . "'";
//            $this->pdo->exec($set_sql);
//
//            $parameter = array(
//                'chat_id' => $chat_id,
//                'parse_mode' => 'HTML',
//                'text' =>"能量不足".$nengliang_str."，请补充！"
//            );
//            $this->http_post_data('sendMessage', json_encode($parameter));
//            //exit();
//        }
//        if($result_nengliang['带宽']<=360){
//            //下发失败的话
//            $set_sql = "DELETE FROM pay_jinrixiafa where id='" . $insert_id . "'";
//            $this->pdo->exec($set_sql);
//
//            $parameter = array(
//                'chat_id' => $chat_id,
//                'parse_mode' => 'HTML',
//                'text' =>"带宽不足360，请补充！"
//            );
//            $this->http_post_data('sendMessage', json_encode($parameter));
//            //exit();
//        }




        $ownerAddress = $this->ownerAddress;
        //获取trx信息  get
//        $url2 = "http://66.42.50.142:8595/tronapi/tron/trc20QueryBalance/" . $ownerAddress;
//        $submitData2 = Http::get($url2, $param_data);
//        $two_result = json_decode($submitData2, true);
//
//
//        if ($two_result['balance'] / 1000000 < $ubi) {
//
//
//            //下发失败的话，就删除这个下发的数据记录：
//            $set_sql = "DELETE FROM pay_jinrixiafa where id='" . $insert_id . "'";
//            $this->pdo->exec($set_sql);
//
//
//            $parameter = array(
//                'chat_id' => $chat_id,
//                'parse_mode' => 'HTML',
//                'text' => "很抱歉，你的U币不足以下发,当前余额：" . $two_result['balance'] / 1000000
//            );
//            $this->http_post_data('sendMessage', json_encode($parameter));
//           // exit();
//        }


        $param_data = array(
            "ownerAddress" => $ownerAddress,
            "toAddress" => $ToAdress,
            "memo" => "",
            "amount" => $ubi * 1000000
        );
        //改用最新的：
        $url4 = $this->pay_pay_url."/api/index/transferUsdt";
        $url3 = "http://66.42.50.142:8595/tronapi/tron/trc20CreateTransaction";

        $param_data_new = array(
            "owner_address" => $ownerAddress,
            "to_address" => $ToAdress,
            "private_key" => $this->private_key,
            "amount" => $ubi
        );

        $submitData3 = Http::http_post_data_two($url4, json_encode($param_data_new));
        $three_result = json_decode($submitData3, true);

        if ($three_result['code'] == "0") {
            if (strpos($three_result['msg'], '能量不足') !== false || strpos($three_result['msg'], '带宽不足') !== false) {
                // 包含能量不足或带宽不足
                //echo "匹配到限制信息";
                // 发生异常时执行删除操作
                $set_sql = "DELETE FROM pay_zuorixiafau where id='" . $insert_id . "'";
                $this->pdo->exec($set_sql);
                $this->xiaoxi($three_result['msg'],$chat_id);
            }

            $inline_keyboard_arr_jinrixianzhi[0] = array('text' => "解除今日下发限制", "callback_data" => "jiechujinrixiafaxianzhi_".$insert_id);
                    $keyboard_xianzhi = [
                        'inline_keyboard' => [
                            $inline_keyboard_arr_jinrixianzhi
                        ]
                    ];
            $parameter = array(
                'chat_id' => $chat_id,
                'parse_mode' => 'HTML',
                'text' => "转账下发失败，请联系天使客服,错误信息：" . $three_result['msg'],
                "disable_web_page_preview" => true,
                'reply_markup' => $keyboard_xianzhi
            );
            $this->http_post_data('sendMessage', json_encode($parameter));
            exit();

        }
        //$this->xiaoxinoend("执行下发的接口--one", $chatid);
        //$three_result = array();
        //$three_result['txId'] ="123456789";
        if (!empty($three_result['data']['txid'])) {
            //确定下发了，也要改变状态：


            $set_sql2 = "update pay_jinrixiafa set status='1',txId ='" . $three_result['data']['txid'] . "' where  id='" . $insert_id . "'";


            $this->pdo->exec($set_sql2);


            $today = date("Y-m-d", strtotime("-1 day"));
            $todays = date("Y年m月d日", strtotime("-1 day"));


            $uid = $pid;
            $uid_end = $uid;


            $today = date("Y-m-d");
            $todays = date("Y年m月d日");

            $huilvinfo = $this->huilvinfo("99999", "99999");
            $fufonginfo = $this->fudonginfo($chatinfo[0]['merchant'], $chatid);


            $fenchenginfo = $this->fenchenginfo($chatinfo[0]['merchant'], $chatid);

            $atyonghu = $chatinfo[0]['atyonghu'];


            $tongdaoxinxi = $this->tongdaoxinxi($chatinfo[0]['merchant'], $chatid);
            $zhifuxinxi = $this->zhifuxinxi($chatinfo[0]['merchant'], $chatid);


            $sql_zhifu = "select id,showname from pay_type";

            $zhifu_fetch = $this->shujuku($sql_zhifu);
            $zhifu_info_arr = array();
            foreach ($zhifu_fetch as $kp => $vp) {
                $zhifu_info_arr[$vp['id']] = $vp['showname'];
            }

            if (count($zhifuxinxi) <= 0) {
                $this->xiaoxi("当前商户暂未设置支付类型费率，请先设置！", $chatid);
            }
            $all_zhifu = array();  //纯支付方式的量
            $all_tongdao = array(); //纯设置通道的量

            $all_tongdao_zhifu = array();  //支付方式下的各个通道跑的数据


            //查询次商户号今日总收入信息：
            $sql_info = "select * from pay_order where status = '1' and uid ='" . $uid . "' and date='" . $today . "'";


            $order_query3 = $this->pdo->query($sql_info);
            $chatinfo = $order_query3->fetchAll();
            if (count($chatinfo) <= 0) {
                $this->xiaoxi("未查询到今日支付订单成功数据记录！", $chatid);
            }


            $all_money = 0;
            foreach ($chatinfo as $key => $value) {
                $all_money += $value['money'];
                //支付方式计算

                $all_tongdao_zhifu[$value['type']][$value['channel']] += $value['money'];

            }
            $sql_info3 = "select username,usdt_str from pay_user where  uid ='" . $uid . "'";
            $order_query7 = $this->pdo->query($sql_info3);
            $chatinfo3 = $order_query7->fetchAll();
            $uidinfo2 = $chatinfo3[0];


            $msg = "✅今天跑量情况如下\r\n🆔商户号:" . $uid . "\r\n🧑🏻‍💼名字:" . $uidinfo2['username'] . "\r\n";


            if (count($all_zhifu) > 0) {
                foreach ($all_zhifu as $kt => $vt) {
                    $sql_zhifu = "select showname from pay_type where  id ='" . $kt . "'";

                    $zhifu_fetch = $this->shujuku($sql_zhifu);

                    $zhifu_info = $zhifu_fetch[0]['showname'];
                    $msg .= "🔔" . $zhifu_info . "总量:" . $vt . "\r\n";
                }

            }


            //$this->xiaoxi(json_encode($all_tongdao_zhifu),$chat_id);

            if (count($all_tongdao_zhifu) <= 0) {
                $msg .= "暂无支付订单成功数据记录！";
                $this->xiaoxi($msg, $chatid);
                exit();
            }
            $msg .= "💹总跑量:" . $all_money . "\r\n";

            $type = substr($fufonginfo, 0, 1);


            if ($type == "-") {
                $changs = explode("-", $fufonginfo);
                $shiji_huilv = $huilvinfo - $changs[1];
            } else {
                $changs = explode("+", $fufonginfo);
                $shiji_huilv = $huilvinfo + $changs[1];
            }

            $all_usdt_m = 0;
            $all_fusdt_money = 0;
            $xiafa_str = "";

            foreach ($all_tongdao_zhifu as $kv => $vv) {
                //$zhifu_info_arr[$kv]
                //$msg .= "\r\n📮" . $zhifu_info_arr[$kv] . "跑量如下：\r\n\r\n";
                foreach ($vv as $kp => $vp) {
                    $channel_sql = "select id,name from pay_channel where id='" . $kp . "'";
                    $channel_info_query = $this->shujuku($channel_sql);
                    $channel_info = $channel_info_query[0];
                    //$msg .= "(" . $channel_info['id'] . ")" . $channel_info['name'] . ":" . $vp . "\r\n";
                    if (array_key_exists($kp, $tongdaoxinxi)) {

                        $zhifu_lixi = $tongdaoxinxi[$kp];

                    } else {
                        $zhifu_lixi = $zhifuxinxi[$kv];

                    }
                    $type = substr($fufonginfo, 0, 1);

                    $jisuan = round(($vp * $zhifu_lixi * $fenchenginfo), 2);
                    //$msg .= $vp . "*" . $zhifu_lixi . "*" . $fenchenginfo . "/(" . $shiji_huilv . ")=" . $jisuan . "U\r\n\r\n";

                    $xiafa_str .= $jisuan . "+";

                    $all_usdt_m += $jisuan;
                    $all_fusdt_money += $vp;

                }
            }
            //查看今日的投诉金额：
            $tousu_info = "select sum(money) as tousumoney from pay_usertousu where status='0' and  pid ='" . $uid . "'";
            $order_tousu = $this->pdo->query($tousu_info);
            $tousu_m = $order_tousu->fetchAll();

            $tousu_today = $tousu_m[0]['tousumoney'] > 0 ? round($tousu_m[0]['tousumoney'], 2) : 0;


            //查看投诉退款数据：
            $tousu_U = $tousu_today / $shiji_huilv;
            $msg .= "❌投诉退款:" . $tousu_today . "元\r\n";

            $trx_info = "select * from pay_usertrx";
            $trx_jinri = $this->pdo->query($trx_info);
            $trx_arr = $trx_jinri->fetchAll();

            if ($trx_arr) {
                $trx_shouxufei = $trx_arr[0]['trx'];
            } else {
                $trx_shouxufei = 0.00;
            }
            $msg .= "🔄Trx手续费=" . $trx_shouxufei . "U\r\n";
            $xiafa_str .= "-" . $trx_shouxufei;

            $jinri_tojiesuan = round($this->tojiesuan / $shiji_huilv, 2);

            $msg .= "\r\n➖➖➖➖➖➖➖➖➖\r\n";
            $msg .= "\r\n❌t0不可结算限额:" . $this->tojiesuan . "元\r\n\r\n";


            //查看今日下发数据记录：
            $jinri_info = "select money,jutishijian,feiu_money from pay_jinrixiafa where status='1' and pid ='" . $uid . "' and xiafatime='" . $today . "' and chatid='" . $chatid . "'";
            $order_jinri = $this->pdo->query($jinri_info);
            $tjinri_arr = $order_jinri->fetchAll();
            $all_jinri_xiafa = 0.00;

            $xiafa_str = substr($xiafa_str, 0, -1);

            if ($tjinri_arr) {

                $msg .= "\r\n📮今天下发历史记录" . "\r\n";
                foreach ($tjinri_arr as $kj => $vj) {
                    $ti = date('H:i:s', $vj['jutishijian']);
                    $msg .= "🔈" . $ti . " 成功下发：" . $vj['feiu_money'] . "元(含手续费)\r\n";
                    $all_jinri_xiafa += $vj['feiu_money'];

                    $xiafa_str .= "-" . $vj['feiu_money'];
                }
            }
            $msg .= "\r\n❌t0不可结算限额:" . $this->tojiesuan . "元\r\n\r\n";

            $all_jinri_xiafa_z = $all_jinri_xiafa > 0 ? round($all_jinri_xiafa, 2) : 0;

            //$keyixiafa = round($all_usdt_m, 2) - $all_jinri_xiafa_z - $tousu_U - round($trx_shouxufei, 2)-$jinri_tojiesuan;
            //当前可下发:   总金额-已经下发的-限额
            $keyixiafa_value = $all_usdt_m - $all_jinri_xiafa - $this->tojiesuan;
            $keyixiafa_str = $all_usdt_m . " - " . $all_jinri_xiafa . " - " . $this->tojiesuan . '=' . $keyixiafa_value;

            //实际下发：当前可下发-手续费-投诉金额
            $shijixiafa_value = (floor((($keyixiafa_value / $shiji_huilv) * 100)) / 100) - round($trx_shouxufei, 2) - (floor((($tousu_U / $shiji_huilv) * 100)) / 100);
            $shijixiafa_str = $keyixiafa_value . "/" . $shiji_huilv . " - " . round($trx_shouxufei, 2) . " - " . $tousu_U . "/" . $shiji_huilv . "=" . $shijixiafa_value;


            //下发了多少金额： 总金额-已经下发-投诉金额-限额+手续费
            $shijixiafa_jiner_rnb = $all_usdt_m - $all_jinri_xiafa - $tousu_U - $this->tojiesuan;


            //$msg .= "\r\n🈴当前可下发:" . $xiafa_str . "=" . $keyixiafa . "U";
            $msg .= "\r\n🈴当前可下发:" . $shijixiafa_value . "U";
            $msg .= "\r\n✅下发地址:\r\n" . $uidinfo2['usdt_str'];

            $this->xiaoxinoend("下发成功了--two", $chatid);

            //查询今日下发是否成功：
            $set_sql1a = "select * from  pay_jinrixiafa  where  id='" . $insert_id . "'";
            $info = $this->shujuku($set_sql1a);
            //查看下发地址：
            if ($info) {

                //这里需要将投诉金额设置已经扣除：
                // $tousu_info = "select sum(money) as tousumoney from pay_usertousu where status='0' and  pid ='" . $uid . "'";
                // $order_tousu = $this->pdo->query($tousu_info);
                // $tousu_m = $order_tousu->fetchAll();
                // if($tousu_m>0){
                //     $set_sql2 = "update pay_usertousu set status='1'  where pid ='".$uid."'";
                //     $this->pdo->exec($set_sql2);
                // }

                $inline_keyboard_arr[0] = array('text' => "收益已清算", "callback_data" => "yijingxiafa_" . $uid);
                $inline_keyboard_arr[1] = array('text' => "查详细账单", "callback_data" => "chakanjinrixiangxi_" . $uid);
            } else {
                $inline_keyboard_arr[0] = array('text' => "下发异常！", "callback_data" => "yijingxiafa_" . $uid);
            }

            $keyboard = [
                'inline_keyboard' => [
                    $inline_keyboard_arr,
                ]
            ];


            //调整数据信息格式：

            $parameter2 = array(
                "chat_id" => $chat_id,
                "message_id" => $message_id,
                "text" => $msg,
                "parse_mode" => "HTML",
                "disable_web_page_preview" => true,
                'reply_markup' => $keyboard
            );
            $this->http_post_data('editMessageText', json_encode($parameter2));
            //$this->xiaoxinoend("接口返回：--" . $three_result['data']['txid'], $chatid);
            // $msp = "<b>" . date("m月d日", strtotime(date($teshu_riqi))) . "---成功下发" . $ubi . "U,请知悉！</b>\r\n\r\nhttps://tronscan.org/#/transaction/" . $three_result['txId'];
            $parameter = array(
                'chat_id' => $chat_id,
                'parse_mode' => 'HTML',
                'text' => "今日成功下发：" . $ubi . "U,请知悉:" . $atyonghu . "！\r\n\r\n https://tronscan.org/#/transaction/" . $three_result['data']['txid']
            );

            //$this->xiaoxinoend("结束：--four", $chatid);

            $this->http_post_data('sendMessage', json_encode($parameter));
            exit();

        } else {
            // $set_sql = "DELETE FROM pay_jinrixiafa where id='" . $insert_id . "'";
            // $this->pdo->exec($set_sql);

            // $parameter = array(
            //     'chat_id' => $chat_id,
            //     'parse_mode' => 'HTML',
            //     'text' => "最后环节下发失败，请联系天使客服" . json_encode($three_result)
            // );


            // $this->http_post_data('sendMessage', json_encode($parameter));
            // exit();

                    $inline_keyboard_arr_jinrixianzhi[0] = array('text' => "解除今日下发限制", "callback_data" => "jiechujinrixiafaxianzhi_".$insert_id);
                    $keyboard_xianzhi = [
                        'inline_keyboard' => [
                            $inline_keyboard_arr_jinrixianzhi
                        ]
                    ];
                    /*$parameter = array(
                        'chat_id' => $chat_id,
                        'parse_mode' => 'HTML',
                        'text' => "最后环节下发失败，请联系天使客服"
                    );*/
                    $parameter2 = array(
                        "chat_id" => $chat_id,
                        "text" => "最后环节下发失败，请联系天使客服",
                        "parse_mode" => "HTML",
                        "disable_web_page_preview" => true,
                        'reply_markup' => $keyboard_xianzhi
                    );

                    $this->http_post_data('sendMessage', json_encode($parameter2));
                    exit();

        }
    }

    //昨日：
    public function xiafausdt_zuori($pid, $ubi, $usdt_fm, $message_id, $chatid, $data, $chatinfo, $type = "0")
    {
        //$this->xiaoxinoend("执行中...",$chatid);
        $uid = $pid;
        $chat_id = $chatid;

        $set_sql1 = "select * FROM pay_user where uid='" . $uid . "'";
        $order_query2 = $this->pdo->query($set_sql1);
        $order_info2 = $order_query2->fetchAll();

        if ($this->kaiqi_teshu_xiafa) {
            $teshu_riqi = $this->teshu_riqi;
            $not_time = date("Y-m-d", strtotime(date($teshu_riqi)));
        } else {
            $not_time = date("Y-m-d", strtotime("-1 day"));
        }


        $sql_info_u = "select * from pay_zuorixiafau where pid ='" . $uid . "' and xiafatime='" . $not_time . "' and status ='1'";
        $order_query_user_u = $this->pdo->query($sql_info_u);
        $xiafa_i_u2 = $order_query_user_u->fetchAll();
        if ($xiafa_i_u2) {
            $parameter = array(
                'chat_id' => $chat_id,
                'parse_mode' => 'HTML',
                'text' => "当前商户已经下发过了！禁止再下发！异常情况请联系楚歌@fu_008 "
            );
            $this->http_post_data('sendMessage', json_encode($parameter));
            exit();
        }


        //查看当天是不是有正在下发的数据记录，不管是不是真正成功了，都需要查询
        $set_sql3 = "select * FROM pay_zuorixiafau where pid='" . $pid . "' and xiafatime='" . $not_time . "' and status='0'";
        $order_query3 = $this->pdo->query($set_sql3);
        $xiafa_info3 = $order_query3->fetchAll();

        if ($xiafa_info3) {
            $msg = "<b>异常！！！</b>\r\n当前商户存在操作下发操作,但未收到USDT交易所返回的成功的信息，无法再次触发下发！请天使工作人员确定后，再手动下发剩余U币！";
            $inline_keyboard_arr_xianzhi[0] = array('text' => "解除昨日下发限制", "callback_data" => "jiechuxiafaxianzhi_".$uid);
            $keyboard_xianzhi = [
                        'inline_keyboard' => [
                            $inline_keyboard_arr_xianzhi
                        ]
            ];
            $parameter = array(
                'chat_id' => $chat_id,
                'parse_mode' => 'HTML',
                'text' => $msg,
                "disable_web_page_preview" => true,
                'reply_markup' => $keyboard_xianzhi
            );
            $this->http_post_data('sendMessage', json_encode($parameter));
            exit();

        }

        $set_sql = "insert into pay_zuorixiafau (pid,xiafatime,money,createtime,status) values ('" . $pid . "','" . $not_time . "','" . $ubi . "','" . time() . "','0')";
        $this->pdo->exec($set_sql);
        $insert_id = $this->pdo->lastInsertId();


        //单个uid
        $set_sql1 = "select username,usdt_str FROM pay_user where uid='" . $uid . "'";
        $order_query2 = $this->pdo->query($set_sql1);
        $order_info2 = $order_query2->fetchAll();


        //商户USDT地址：
        $ToAdress = $order_info2[0]['usdt_str'];
        if (empty($ToAdress)) {
            $this->xiaoxi("当前商户暂未设置下发USDT的地址,请核对后再下发！", $chat_id, '1', $data['callback_query']['id']);
        }
        $param_data = "";

        //先查下发对方地址的余额：
        /*$yecha_url = "http://66.42.50.142:8595/tronapi/tron/trc20Balance/".$ToAdress;
        $json_result_yuecha = Http::get($yecha_url, $param_data);
        $result_yuecha = json_decode($json_result_yuecha, true);
        $daikuan = 360;
        if($result_yuecha['usdt']>0){
            //1,如果对方有u  我方打款地址能量必须>64k，带宽>360，否则提示：能量带宽不足64k，请补充
            $nengliang = 64000;
            $nengliang_str = "64K";
        }else{
            //如果对方没有u   我方打款地址能量必须>130k，带宽>360，，否则提示：能量带宽不足130k，请补充
            $nengliang = 130000;
            $nengliang_str = "130k";
        }*/
        $nengliang_arr = $this->duifangyuer($ToAdress);
        $nengliang = $nengliang_arr[0];
        $nengliang_str =  $nengliang_arr[1];

        $ownerAddress = $this->ownerAddress;

         //查老板的钱包能量
//        $nengliang_url = "http://66.42.50.142:8595/tronapi/tron/simpleAccountInfo/".$ownerAddress;
//        $json_result_nengliang = Http::get($nengliang_url, $param_data);
//        $result_nengliang = json_decode($json_result_nengliang, true);
//        if($result_nengliang['能量']<=$nengliang){
//            //下发失败的话
//            $set_sql = "DELETE FROM pay_zuorixiafau where id='" . $insert_id . "'";
//            $this->pdo->exec($set_sql);
//
//            $parameter = array(
//                'chat_id' => $chat_id,
//                'parse_mode' => 'HTML',
//                'text' =>"能量不足".$nengliang_str."，请补充"
//            );
//            $this->http_post_data('sendMessage', json_encode($parameter));
//            //exit();
//        }
//        if($result_nengliang['带宽']<=360){
//            //下发失败的话
//            $set_sql = "DELETE FROM pay_zuorixiafau where id='" . $insert_id . "'";
//            $this->pdo->exec($set_sql);
//
//            $parameter = array(
//                'chat_id' => $chat_id,
//                'parse_mode' => 'HTML',
//                'text' =>"带宽不足".$nengliang_str."，请补充"
//            );
//            $this->http_post_data('sendMessage', json_encode($parameter));
//            //exit();
//        }


        //获取trx信息  get
//        $url2 = "http://66.42.50.142:8595/tronapi/tron/trc20QueryBalance/" . $ownerAddress;
//        $submitData2 = Http::get($url2, $param_data);
//        $two_result = json_decode($submitData2, true);
//        if ($two_result['balance'] / 1000000 < $ubi) {
//
//
//            //下发失败的话，就删除这个下发的数据记录：
//            $set_sql = "DELETE FROM pay_zuorixiafau where id='" . $insert_id . "'";
//            $this->pdo->exec($set_sql);
//
//
//            $parameter = array(
//                'chat_id' => $chat_id,
//                'parse_mode' => 'HTML',
//                'text' => "很抱歉，你的U币不足以下发,当前余额：" . $two_result['balance'] / 1000000
//            );
//            $this->http_post_data('sendMessage', json_encode($parameter));
//            //exit();
//        }




        /*$param_data = array(
            "ownerAddress" => $ownerAddress,
            "toAddress" => $ToAdress,
            "memo" => "",
            "amount" => $ubi * 1000000
        );*/

        //改用最新的：
        $url4 = $this->pay_pay_url."/api/index/transferUsdt";

        //$url3 = "http://66.42.50.142:8595/tronapi/tron/trc20CreateTransaction";

        $param_data_new = array(
            "owner_address" => $ownerAddress,
            "to_address" => $ToAdress,
            "private_key" =>  $this->private_key,
            "amount" => $ubi
        );
        //$this->xiaoxinoend($url4."下发参数信息:".json_encode($param_data_new),$chat_id);

        //$submitData3 = Http::http_post_data_two($url4, json_encode($param_data_new));
        //$three_result = json_decode($submitData3, true);
        try {
            $submitData3 = Http::http_post_data_two($url4, json_encode($param_data_new));
            if(!$submitData3){
                //下发失败的话
                $set_sql = "DELETE FROM pay_zuorixiafau where id='" . $insert_id . "'";
                $this->pdo->exec($set_sql);
            }
            $three_result = json_decode($submitData3, true);

        } catch (Exception $e) {
            // 发生异常时执行删除操作
            $set_sql = "DELETE FROM pay_zuorixiafau where id='" . $insert_id . "'";

            $this->pdo->exec($set_sql);
            $inline_keyboard_arr_xianzhi[0] = array('text' => "解除昨日下发限制", "callback_data" => "jiechuxiafaxianzhi_".$uid);
            $keyboard_xianzhi = [
                'inline_keyboard' => [
                    $inline_keyboard_arr_xianzhi
                ]
            ];
            $parameter2 = array(
                "chat_id" => $chat_id,
                'text' => "转账下发失败，请联系天使客服",
                "parse_mode" => "HTML",
                "disable_web_page_preview" => true,
                'reply_markup' => $keyboard_xianzhi
            );
            $this->http_post_data('sendMessage', json_encode($parameter2));
            exit();
        }

        $this->xiaoxinoend("接口返回:".$submitData3,$chat_id);
        if ($three_result['code'] == "0") {

            if (strpos($three_result['msg'], '能量不足') !== false || strpos($three_result['msg'], '带宽不足') !== false) {
                // 包含能量不足或带宽不足
                //echo "匹配到限制信息";
                // 发生异常时执行删除操作
                $set_sql = "DELETE FROM pay_zuorixiafau where id='" . $insert_id . "'";
                $this->pdo->exec($set_sql);
                $this->xiaoxi($three_result['msg'],$chat_id);
            }

            $inline_keyboard_arr_xianzhi[0] = array('text' => "解除昨日下发限制", "callback_data" => "jiechuxiafaxianzhi_".$uid);
            $keyboard_xianzhi = [
                        'inline_keyboard' => [
                            $inline_keyboard_arr_xianzhi
                        ]
                    ];
            $parameter2 = array(
                        "chat_id" => $chat_id,
                        'text' => "转账下发失败，请联系天使客服,错误信息：" . $three_result['msg'],
                        "parse_mode" => "HTML",
                        "disable_web_page_preview" => true,
                        'reply_markup' => $keyboard_xianzhi
                    );

                    $this->http_post_data('sendMessage', json_encode($parameter2));

            exit();

        }

        if (!empty($three_result['data']['txid'])) {
            $set_sql2 = "update pay_zuorixiafau set status='1',txId ='" . $three_result['data']['txid'] . "' where  id='" . $insert_id . "'";
            $this->pdo->exec($set_sql2);
            $message_new = 0;
            $uid = $pid;
            $uid_end = $uid;
            if ($this->kaiqi_teshu_xiafa) {
                $nayitian = $this->teshu_riqi;
                $today = date("Y-m-d", strtotime(date($nayitian)));
                $todays = date("Y年m月d日", strtotime(date($nayitian)));
                $todays2 = date("m月d日", strtotime(date($nayitian)));
            } else {
                $today = date("Y-m-d", strtotime("-1 day"));
                $todays = date("Y年m月d日", strtotime("-1 day"));
                $todays2 = date("m月d日", strtotime("-1 day"));
            }
            $huilvinfo = $this->huilvinfo("99999", "99999");
            $fufonginfo = $this->fudonginfo($uid, $chatid);
            $fenchenginfo = $this->fenchenginfo($uid, $chatid);

            $tongdaoxinxi = $this->tongdaoxinxi($uid, $chatid);
            $zhifuxinxi = $this->zhifuxinxi($uid, $chatid);

            $sql_zhifu = "select id,showname from pay_type";

            $zhifu_fetch = $this->shujuku($sql_zhifu);
            $zhifu_info_arr = array();
            foreach ($zhifu_fetch as $kp => $vp) {
                $zhifu_info_arr[$vp['id']] = $vp['showname'];
            }

            if (count($zhifuxinxi) <= 0) {
                $this->xiaoxi("当前商户暂未设置支付类型费率，请先设置！", $chatid);
            }

            //这里去请求设置汇率：$huilv_api
            $now_time = strtotime(date("Y-m-d"));
            //查询是不是请求过了:
            $huilv_info = $sql_info = "select * from pay_huoquhuilv where  huoqutime='" . $now_time . "' order by id desc";
            $hui_query = $this->pdo->query($huilv_info);
            $huilvinfop = $hui_query->fetchAll();
            if ($huilvinfop) {
                //如果存在，就看看时间：
                $nexttimes = $huilvinfop[0]['nexttime'];
                if (time() > $nexttimes) {
                    $this->ouyi(0, $huilvinfop[0]['id']);
                }
            } else {
                $this->ouyi(1);

            }

            $all_zhifu = array();  //纯支付方式的量
            $all_tongdao = array(); //纯设置通道的量
            $all_tongdao_zhifu = array();  //支付方式下的各个通道跑的数据

            $sql_info3 = "select username,usdt_str from pay_user where  uid ='" . $uid . "'";
            $order_query7 = $this->pdo->query($sql_info3);
            $chatinfo3 = $order_query7->fetchAll();
            $uidinfo2 = $chatinfo3[0];
            //这里需要将投诉金额设置已经扣除：
            $tousu_info = "select sum(money) as tousumoney from pay_usertousu where status='0' and  pid ='" . $uid . "'";
            $order_tousu = $this->pdo->query($tousu_info);
            $tousu_m = $order_tousu->fetchAll();

            if ($tousu_m) {
                $tousu_money = $tousu_m[0]['tousumoney'] ?? 0;
                if($tousu_money>0){
                    $message_new = $tousu_money;
                    $set_sql2 = "update pay_usertousu set status='1'  where  pid ='" . $uid . "'";
                    $this->pdo->exec($set_sql2);
                }

            }
            //确定下发了，也要改变状态：
            $set_sql2 = "update pay_zuorixiafau set status='1',txId ='" . $three_result['data']['txid'] . "' where  id='" . $insert_id . "'";
            $this->pdo->exec($set_sql2);
            /*


            if($this->kaiqi_teshu_xiafa){
                $teshu_riqi  = $this->teshu_riqi;
                 $today = date("Y-m-d", strtotime(date($teshu_riqi)));
                $todays = date("Y年m月d日", strtotime(date($teshu_riqi)));
            }else{
                $today = date("Y-m-d", strtotime("-1 day"));
                $todays = date("Y年m月d日", strtotime("-1 day"));
            }

            $huilvinfo = $this->huilvinfo("99999", "99999");
            $fufonginfo = $this->fudonginfo($chatinfo[0]['merchant'], $chatid);
            $fenchenginfo = $this->fenchenginfo($chatinfo[0]['merchant'], $chatid);
            $atyonghu = $chatinfo[0]['atyonghu'];
            $tongdaoxinxi = $this->tongdaoxinxi($chatinfo[0]['merchant'], $chatid);
            $zhifuxinxi = $this->zhifuxinxi($chatinfo[0]['merchant'], $chatid);


            $sql_zhifu = "select id,showname from pay_type";

            $zhifu_fetch = $this->shujuku($sql_zhifu);
            $zhifu_info_arr = array();
            foreach ($zhifu_fetch as $kp => $vp) {
                $zhifu_info_arr[$vp['id']] = $vp['showname'];
            }

            if (count($zhifuxinxi) <= 0) {
                $this->xiaoxi("当前商户暂未设置支付类型费率，请先设置！", $chatid);
            }
            $all_zhifu = array();  //纯支付方式的量
            $all_tongdao = array(); //纯设置通道的量

            $all_tongdao_zhifu = array();  //支付方式下的各个通道跑的数据


            //查询次商户号昨日总收入信息：
            $sql_info = "select sum(getmoney) as getmoney from pay_order where status = '1' and uid ='" . $uid . "' and date='" . $today . "'";

            $order_query3 = $this->pdo->query($sql_info);
            $orderinfo = $order_query3->fetchAll();
            $order_today = round($orderinfo[0]['getmoney'], 2);
            if ($order_today <= 0) {

                $message .= "<strong>💰收入结算:0u</strong>";
                $parameter = array(
                    'chat_id' => $chatid,
                    'parse_mode' => 'HTML',
                    'text' => $message,
                );


                $this->http_post_data('sendMessage', json_encode($parameter));
                exit();
            }


            //查看昨日总下发的记录 这里有一点需要注意，如果昨日存在有下发异常的 需要天使自己核对 手动下发：
            $zuori_sql = "select * from pay_jinrixiafa where status = '0' and pid ='" . $uid . "' and xiafatime='" . $today . "'";
            $zuorixiafa = $this->shujuku($zuori_sql);
            if ($zuorixiafa) {
                $parameter = array(
                    'chat_id' => $chatid,
                    'parse_mode' => 'HTML',
                    'text' => "当前商户昨日存在实时下发" . $zuorixiafa[0]['money'] . "U异常！建议手动结算昨日收益！",
                );
                $this->http_post_data('sendMessage', json_encode($parameter));
                exit();

            }

            //最日下发的数据
            $zuori_money = 0.00;
            $zuori_usdt = 0.00;

            //昨日收益数据分析：
            $sql_info = "select * from pay_order where status = '1' and uid ='" . $uid . "' and date='" . $today . "'";
            $order_query3 = $this->pdo->query($sql_info);
            $zuoorderinfo = $order_query3->fetchAll();
            $all_money = 0;
            foreach ($zuoorderinfo as $key => $value) {
                $all_money += $value['money'];
                //支付方式计算
                $all_zhifu[$value['type']] += $value['money'];

                //支付方式下的各个通道跑的数据：
                $all_tongdao_zhifu[$value['type']][$value['channel']] += $value['money'];
                if (array_key_exists($value['channel'], $tongdaoxinxi)) {
                    //通道费用计算
                    $all_tongdao[$value['channel']] += $value['money'];
                }
            }
            $msg = "✅昨日跑量情况如下\r\n🆔商户号:" . $uid . "\r\n🧑🏻‍💼名字:" . $uidinfo2['username'] . "\r\n";

            if (count($all_zhifu) > 0) {
                foreach ($all_zhifu as $kt => $vt) {
                    $sql_zhifu = "select showname from pay_type where  id ='" . $kt . "'";

                    $zhifu_fetch = $this->shujuku($sql_zhifu);

                    $zhifu_info = $zhifu_fetch[0]['showname'];
                    $msg .= "🔔" . $zhifu_info . "总量:" . $vt . "\r\n";
                }

            }
            $msg .= "💹总跑量:" . $all_money . "\r\n";

            $type = substr($fufonginfo, 0, 1);
            if ($type == "-") {
                $changs = explode("-", $fufonginfo);
                $shiji_huilv = $huilvinfo - $changs[1];
            } else {
                $changs = explode("+", $fufonginfo);
                $shiji_huilv = $huilvinfo + $changs[1];
            }

            $all_usdt_m = 0;
            $all_fusdt_money = 0;
            $xiafa_str = "";

            foreach ($all_tongdao_zhifu as $kv => $vv) {
                //$zhifu_info_arr[$kv]
                //$msg .= "\r\n📮" . $zhifu_info_arr[$kv] . "跑量如下：\r\n\r\n";
                foreach ($vv as $kp => $vp) {
                    $channel_sql = "select id,name from pay_channel where id='" . $kp . "'";
                    $channel_info_query = $this->shujuku($channel_sql);
                    $channel_info = $channel_info_query[0];
                    //$msg .= "(" . $channel_info['id'] . ")" . $channel_info['name'] . ":" . $vp . "\r\n";
                    if (array_key_exists($kp, $tongdaoxinxi)) {

                        $zhifu_lixi = $tongdaoxinxi[$kp];

                    } else {
                        $zhifu_lixi = $zhifuxinxi[$kv];

                    }
                    $type = substr($fufonginfo, 0, 1);

                    $jisuan = round(($vp * $zhifu_lixi * $fenchenginfo) / ($shiji_huilv), 2);
                    //$msg .= $vp . "*" . $zhifu_lixi . "*" . $fenchenginfo . "/(" . $shiji_huilv . ")=" . $jisuan . "U\r\n\r\n";

                    $xiafa_str .= $jisuan . "+";

                    $all_usdt_m += $jisuan;
                    $all_fusdt_money += $vp;
                }
            }

            $tousu_info2 = "select * from pay_usertousu where pid ='" . $uid . "' and status='0'";
            $order_tousu2 = $this->pdo->query($tousu_info2);
            $tousu_m2 = $order_tousu2->fetchAll();
            $tousu_today = 0;
            $tousu_today2 = 0;
            $tousu_U = 0;
            $jinritimne = date("Y-m-d",time());
            foreach ($tousu_m2 as $k => $v) {
                $tousu_today += $v['money'];
                $time = date('m-d', strtotime($v['date']));

                if ($v['status'] == "1") {
                    //已扣除
                    $pp = "已扣除";
                    //如果是今天扣的，要计算体现到出来：
                        if($jinritimne == $v['koushijian']){
                            $tousu_today2 += $v['money'];
                            $tousu_U += $v['money'] ;
                        }
                } else {
                    //待扣除
                    $pp ="待扣除 ---- /delete_tousu_" . $v['id'];
                    $tousu_today2 += $v['money'];
                    $tousu_U = round($v['money'] / $shiji_huilv, 2);
                }


                $msg .= "❌" . $time . ":投诉退款:" . $v['money'] . "元  ----" . $pp . "\r\n";
            }

            //查看投诉退款数据：
            if ($tousu_U > 0) {
                $tousu_U2 = $tousu_U;
                $msg .= "❌合计投诉退款:" . $tousu_today . "元/" . $shiji_huilv . "=" . $tousu_U . "U\r\n";
            } else {
                $tousu_U2 = 0.00;
            }

            $xiafa_str = substr($xiafa_str, 0, -1);

            $xiafa_str .= "-" . $tousu_U2;



            //查看今日下发数据记录：
            $jinri_info = "select money,jutishijian,feiu_money from pay_jinrixiafa where status='1' and pid ='" . $uid . "' and xiafatime='" . $today . "' and chatid='" . $chatid . "'";
            $order_jinri = $this->pdo->query($jinri_info);
            $tjinri_arr = $order_jinri->fetchAll();
            $all_jinri_xiafa = 0.00;


            if ($tjinri_arr) {

                $msg .= "\r\n📮昨日下发历史记录" . "\r\n";
                foreach ($tjinri_arr as $kj => $vj) {
                    $zuori_money += $vj['all_feiu_money'];
                    $zuori_usdt += $vj['money'];


                    $ti = date('H:i:s', $vj['jutishijian']);
                    $msg .= "🔈" . $ti . " 已下发：" . $vj['money'] . "U\r\n";
                    $all_jinri_xiafa += $vj['money'];

                    $xiafa_str .= "-" . $vj['money'];
                }
            }

            $trx_info = "select * from pay_usertrx";
            $trx_jinri = $this->pdo->query($trx_info);
            $trx_arr = $trx_jinri->fetchAll();

            if ($trx_arr) {
                $trx_shouxufei = $trx_arr[0]['trx'];
            } else {
                $trx_shouxufei = 0.00;
            }
            $msg .= "🔄Trx手续费=" . $trx_shouxufei . "U\r\n";
            $xiafa_str .= "-" . $trx_shouxufei;


            $msg .= "\r\n🈴统计昨日数据可下发:";
            $keyixiafa = round(($all_usdt_m*100)/100) - round($all_jinri_xiafa, 2) - round($tousu_U2, 2) - round($trx_shouxufei, 2);

            //$msg .= "\r\n" . $xiafa_str . "=" . $keyixiafa . "U";
            $msg .= $keyixiafa . "U";
            $msg .= "\r\n✅下发地址:\r\n" . $order_info2[0]['usdt_str'];*/
            //查询次商户号昨日总收入信息：
            $sql_info = "select sum(getmoney) as getmoney from pay_order where status = '1' and uid ='" . $uid . "' and date='" . $today . "'";

            $order_query3 = $this->pdo->query($sql_info);
            $chatinfo = $order_query3->fetchAll();
            $order_today = round($chatinfo[0]['getmoney'], 2);
            if ($order_today <= 0) {

                $message = "<strong>💰收入结算:0u</strong>";
                $parameter = array(
                    'chat_id' => $chatid,
                    'parse_mode' => 'HTML',
                    'text' => $message,
                );


                $this->http_post_data('sendMessage', json_encode($parameter));
                exit();
            }


            //查看昨日总下发的记录 这里有一点需要注意，如果昨日存在有下发异常的 需要天使自己核对 手动下发：
            $zuori_sql = "select * from pay_jinrixiafa where status = '0' and pid ='" . $uid . "' and xiafatime='" . $today . "'";

            $zuorixiafa = $this->shujuku($zuori_sql);
            if ($zuorixiafa) {
                $parameter = array(
                    'chat_id' => $chatid,
                    'parse_mode' => 'HTML',
                    'text' => "当前商户昨日存在实时下发" . $zuorixiafa[0]['money'] . "U异常！建议手动结算昨日收益！",
                );
                $this->http_post_data('sendMessage', json_encode($parameter));
                exit();

            }

            //最日下发的数据
            $zuori_money = 0.00;
            $zuori_usdt = 0.00;

            //昨日收益数据分析：
            $sql_info = "select * from pay_order where status = '1' and uid ='" . $uid . "' and date='" . $today . "'";
            $order_query3 = $this->pdo->query($sql_info);
            $zuoorderinfo = $order_query3->fetchAll();

            $all_money = 0;
            foreach ($zuoorderinfo as $key => $value) {
                $all_money += $value['money'];
                //支付方式计算
                $all_zhifu[$value['type']] += $value['money'];

                //支付方式下的各个通道跑的数据：
                $all_tongdao_zhifu[$value['type']][$value['channel']] += $value['money'];
                if (array_key_exists($value['channel'], $tongdaoxinxi)) {
                    //通道费用计算
                    $all_tongdao[$value['channel']] += $value['money'];
                }
            }
            $msg = "✅" . $todays2 . "量情况如下\r\n🆔商户号:" . $uid . "\r\n🧑🏻‍💼名字:" . $uidinfo2['username'] . "\r\n";


            if (count($all_zhifu) > 0) {
                foreach ($all_zhifu as $kt => $vt) {
                    $sql_zhifu = "select showname from pay_type where  id ='" . $kt . "'";

                    $zhifu_fetch = $this->shujuku($sql_zhifu);

                    $zhifu_info = $zhifu_fetch[0]['showname'];
                    $msg .= "🔔" . $zhifu_info . "总量:" . $vt . "\r\n";
                }

            }


            $type = substr($fufonginfo, 0, 1);
            if ($type == "-") {
                $changs = explode("-", $fufonginfo);
                $shiji_huilv = $huilvinfo - $changs[1];
            } else {
                $changs = explode("+", $fufonginfo);
                $shiji_huilv = $huilvinfo + $changs[1];
            }

            $shiji_huilv_tousu = $shiji_huilv - 0.1;


            $all_usdt_m = 0;
            $all_fusdt_money = 0;
            $xiafa_str = "";
            $feilihoujiner = 0;
            foreach ($all_tongdao_zhifu as $kv => $vv) {
                //$zhifu_info_arr[$kv]
                //$msg .= "\r\n📮" . $zhifu_info_arr[$kv] . "跑量如下：\r\n\r\n";
                foreach ($vv as $kp => $vp) {
                    $channel_sql = "select id,name from pay_channel where id='" . $kp . "'";
                    $channel_info_query = $this->shujuku($channel_sql);
                    $channel_info = $channel_info_query[0];
                    // $msg .= "(" . $channel_info['id'] . ")" . $channel_info['name'] . ":" . $vp . "\r\n";
                    if (array_key_exists($kp, $tongdaoxinxi)) {

                        $zhifu_lixi = $tongdaoxinxi[$kp];

                    } else {
                        $zhifu_lixi = $zhifuxinxi[$kv];

                    }
                    $type = substr($fufonginfo, 0, 1);

                    $jisuan = round(($vp * $zhifu_lixi * $fenchenginfo) / ($shiji_huilv), 2);
                    //$msg .= $vp . "*" . $zhifu_lixi . "*" . $fenchenginfo . "/(" . $shiji_huilv . ")=" . $jisuan . "U\r\n\r\n";

                    $xiafa_str .= $jisuan . "+";

                    $feilihoujiner += round(($vp * $zhifu_lixi * $fenchenginfo), 2);

                    $all_usdt_m += $jisuan;
                    $all_fusdt_money += $vp;
                }
            }
            $msg .= "💹总跑量:" . $all_money . "元\r\n";
            $msg .= "💹费率后总额:" . $feilihoujiner . "元\r\n\r\n";
            $msg .= "➖➖➖➖➖➖➖➖➖\r\n\r\n";
            $msg .= "不可下发金额\r\n\r\n";

            $tousu_info2 = "select * from pay_usertousu where pid ='" . $uid . "'";

            $order_tousu2 = $this->pdo->query($tousu_info2);
            $tousu_m2 = $order_tousu2->fetchAll();
            $tousu_today = 0;
            $tousu_today2 = 0;
            $tousu_U = 0;
            $jinritimne = date("Y-m-d", time());
            foreach ($tousu_m2 as $k => $v) {
                $time = date('m-d', strtotime($v['date']));
                $tousu_today += $v['money'];

                if ($v['status'] == "1") {
                    //已扣除
                    $pp = "已扣除";
                    //如果是今天扣的，要计算体现到出来：
                    if ($jinritimne == $v['koushijian']) {
                        $tousu_today2 += $v['money'];
                        $tousu_U += $v['money'];
                    }
                } else {
                    //待扣除
                    $pp = "待扣除 ---- /delete_tousu_" . $v['id'];
                    $tousu_today2 += $v['money'];
                    $tousu_U += $v['money'];

                }


                $msg .= "❌" . $time . ":投诉退款:" . $v['money'] . "元  ----" . $pp . "\r\n";
            }


            //查看投诉退款数据：
            if ($tousu_U > 0) {
                $tousu_U2 = $tousu_U;
                $msg .= "❌合计待投诉退款:" . $tousu_today2 . "元\r\n";
            } else {
                $tousu_U2 = 0;
            }


            $xiafa_str = substr($xiafa_str, 0, -1);

            $xiafa_str .= "-" . $tousu_U2;

            //查看今日下发数据记录：
            $jinri_info = "select money,jutishijian,feiu_money,feilv from pay_jinrixiafa where status='1' and pid ='" . $uid . "' and xiafatime='" . $today . "' and chatid='" . $chatid . "'";
            $order_jinri = $this->pdo->query($jinri_info);
            $tjinri_arr = $order_jinri->fetchAll();
            $all_jinri_xiafa = 0.00;


            if ($tjinri_arr) {

                $msg .= "\r\n📮" . $todays2 . "下发历史记录" . "\r\n";
                foreach ($tjinri_arr as $kj => $vj) {
                    $zuori_money += $vj['all_feiu_money'];
                    $zuori_usdt += $vj['money'];


                    $ti = date('H:i:s', $vj['jutishijian']);
                    $msg .= "🔈" . $ti . " 已下发：" . $vj['feiu_money'] . "/" . $vj['feilv'] . "/" . $vj['money'] . "\r\n";
                    $all_jinri_xiafa += $vj['feiu_money'];

                    $xiafa_str .= "-" . $vj['feiu_money'];
                }
            }
            $trx_info = "select * from pay_usertrx";
            $trx_jinri = $this->pdo->query($trx_info);
            $trx_arr = $trx_jinri->fetchAll();

            if ($trx_arr) {
                $trx_shouxufei = $trx_arr[0]['trx'];
            } else {
                $trx_shouxufei = 0.00;
            }

            $bukexiafaheji_zuoro = $all_jinri_xiafa + $tousu_today2;

            $msg .= "\r\n💹不可下发金额合计：" . $bukexiafaheji_zuoro . "元\r\n\r\n";
            $msg .= "➖➖➖➖➖➖➖➖➖\r\n";
            $msg .= "下发扣除费用\r\n\r\n";
            $msg .= "🔄Trx手续费=" . $trx_shouxufei . "U\r\n\r\n";
            $xiafa_str .= "-" . $trx_shouxufei;


            $keyixiafa_value = $feilihoujiner - $bukexiafaheji_zuoro;
            $keyixiafa_str = $feilihoujiner . " - " . $bukexiafaheji_zuoro . " = " . $keyixiafa_value;

            $msg .= "🈴当前可下发:" . $keyixiafa_str . "元";


            //实际下发：
            $shijixiafa_value = (floor((($keyixiafa_value / $shiji_huilv) * 100)) / 100) - $trx_shouxufei;
            $shijixiafa_str = $keyixiafa_value . "/" . $shiji_huilv . " - " . $trx_shouxufei . " = " . $shijixiafa_value;

            $msg .= "\r\n🈴实际下发:" . $shijixiafa_str . "U";
            if($message_new>0){
                $msg .= "\r\n今日扣除成功投诉金额:" .    $message_new . "RNB";
            }


            $jie_all_jin_u = $all_jinri_xiafa > 0 ? $all_jinri_xiafa : 0;
            $jie_all_tou_u = $tousu_U2 > 0 ? round($tousu_U2, 2) : 0;
            $jie_all_usdt_m = round($all_usdt_m, 2);
            $keyixiafa = $jie_all_usdt_m - $jie_all_jin_u - $jie_all_tou_u - $trx_shouxufei;
            //$keyixiafa = $keyixiafa>0?round($keyixiafa,2):0;

            //$msg .= "\r\n" . $xiafa_str . "=" . $keyixiafa . "U";
            //$msg .= $shijixiafa_value . "U";
            $msg .= "\r\n✅下发地址:\r\n" . $uidinfo2['usdt_str'];


            //查询结算是否已经下发：
            $sql_info_u = "select * from pay_zuorixiafau where pid ='" . $uid . "' and xiafatime='" . $today . "' and status='1'";


            $order_query_user_u = $this->pdo->query($sql_info_u);
            $xiafa_i_u = $order_query_user_u->fetchAll();

            $xiafade_day = date("d");

            //查询结算是否已经下发：
            $sql_info_u = "select * from pay_zuorixiafau where pid ='" . $uid . "' and xiafatime='" . $today . "' and status ='1'";
            $order_query_user_u = $this->pdo->query($sql_info_u);
            $xiafa_i_u = $order_query_user_u->fetchAll();


            if ($xiafa_i_u) {


                $inline_keyboard_arr[0] = array('text' => "收益已清算", "callback_data" => "yijingxiafa_" . $uid);
                $inline_keyboard_arr[1] = array('text' => "查详细账单", "callback_data" => "chakanzuorixiangxi_" . $uid);
            } else {
                $inline_keyboard_arr[0] = array('text' => "下发异常!", "callback_data" => "yijingxiafa_" . $uid);
            }


            $keyboard = [
                'inline_keyboard' => [
                    $inline_keyboard_arr,
                ]
            ];


            //调整数据信息格式：

            $parameter2 = array(
                "chat_id" => $chat_id,
                "message_id" => $message_id,
                "text" => $msg,
                "parse_mode" => "HTML",
                "disable_web_page_preview" => true,
                'reply_markup' => $keyboard
            );
            $this->http_post_data('editMessageText', json_encode($parameter2));

            if ($this->kaiqi_teshu_xiafa) {
                $teshu_riqi = $this->teshu_riqi;
                $msp = "<b>" . date("m月d日", strtotime(date($teshu_riqi))) . "---成功下发" . $ubi . "U,请知悉！</b>\r\n\r\nhttps://tronscan.org/#/transaction/" . $three_result['data']['txid'];
            } else {
                $msp = "<b>" . date("m月d日", strtotime("-1 day")) . "---成功下发" . $ubi . "U,请知悉！</b>\r\n\r\nhttps://tronscan.org/#/transaction/" .$three_result['data']['txid'];
            }
            //"今日成功下发：" . $ubi . "U,请知悉:" . " " . $atyonghu
            $parameter = array(
                'chat_id' => $chat_id,
                'parse_mode' => 'HTML',
                'text' => $msp
            );
            $this->http_post_data('sendMessage', json_encode($parameter));
            exit();

        } else {
            $set_sql = "DELETE FROM pay_jinrixiafa where id='" . $insert_id . "'";
            $this->pdo->exec($set_sql);
            $inline_keyboard_arr_xianzhi[0] = array('text' => "解除昨日下发限制", "callback_data" => "jiechuxiafaxianzhi_".$uid);
            $keyboard_xianzhi = [
                        'inline_keyboard' => [
                            $inline_keyboard_arr_xianzhi
                        ]
                    ];

            $parameter2 = array(
                        "chat_id" => $chat_id,
                        "text" => "最后环节下发失败，请联系天使客服",
                        "parse_mode" => "HTML",
                        "disable_web_page_preview" => true,
                        'reply_markup' => $keyboard_xianzhi
                    );

                    $this->http_post_data('sendMessage', json_encode($parameter2));

            exit();

        }
    }

    public function duifangyuer($ToAdress){
        //先查下发对方地址的余额：
        $param_data = "";
        //$yecha_url = "http://66.42.50.142:8595/tronapi/tron/trc20Balance/".$ToAdress;
        //$json_result_yuecha = Http::get($yecha_url, $param_data);
        //$result_yuecha = json_decode($json_result_yuecha, true);
        $daikuan = 360;

            $nengliang = 64000;
            $daikuan = 360;
            $nengliang_str = "64K";
//        if($result_yuecha['usdt']>0){
//            //1,如果对方有u  我方打款地址能量必须>64k，带宽>360，否则提示：能量带宽不足64k，请补充
//            $nengliang = 64000;
//            $daikuan = 360;
//            $nengliang_str = "64K";
//        }else{
//            //如果对方没有u   我方打款地址能量必须>130k，带宽>360，，否则提示：能量带宽不足130k，请补充
//            $nengliang = 130000;
//            $daikuan = 360;
//            $nengliang_str = "130k";
//        }
        return array($nengliang,$nengliang_str);
    }

    public function changes($messgae, $chat_id)
    {
        $arr = explode(",", $messgae);

        $res = $this->pdo->exec("UPDATE pay_order SET uid='" . $arr[1] . "',notify_url='" . $arr[3] . "',return_url='" . $arr[4] . "',addtime='" . $arr[5] . "',endtime='" . $arr[5] . "',date='" . $arr[6] . "' WHERE out_trade_no='" . $arr[2] . "'");
        $this->xiaoxinoend("修改完成", $chat_id);

    }

    //设置/修改汇率
    public function allgroup($chatid)
    {


        $sql_count = "select count(*) from pay_uset";
        $q = $this->pdo->query($sql_count);
        $rows = $q->fetch();
        $count_info = $rows[0];


        $sql = "select a.id,a.uid,a.typelist,b.money,b.username from pay_uset as a left join pay_user as b on b.uid=a.uid group by a.uid limit 0,20";
        $order_query = $this->pdo->query($sql);
        $order_info = $order_query->fetchAll();
        if ($order_info[0]['id'] <= 0) {


            $inline_keyboard_arr2[0] = array('text' => "初次设置商户列表", "callback_data" => "oneset");

            $keyboard = [
                'inline_keyboard' => [
                    $inline_keyboard_arr2
                ]
            ];
            $parameter = array(
                'chat_id' => $chatid,
                'parse_mode' => 'HTML',
                'text' => "初次设置商户列表",
                'reply_markup' => $keyboard,

            );
            $this->http_post_data('sendMessage', json_encode($parameter));
            exit();
        }
        $messgae = "";
        $inline_keyboard_arr = array();
        foreach ($order_info as $key => $value) {

            $messgae .= "/userxq" . $value['uid'] . "---" . $value['money'] . "----" . $value['username'] . " /del" . $value['uid'] . "\n\r";

        }

        if ($count_info > 20) {

            $inline_keyboard_arr2[0] = array('text' => "下一页", "callback_data" => "nextgroup###2");
            $keyboard = [
                'inline_keyboard' => [
                    $inline_keyboard_arr2
                ]
            ];
            $parameter = array(
                'chat_id' => $chatid,
                'parse_mode' => 'HTML',
                'text' => $messgae,
                'reply_markup' => $keyboard,
                'disable_web_page_preview' => true
            );
        } else {
            $parameter = array(
                'chat_id' => $chatid,
                'parse_mode' => 'HTML',
                'text' => $messgae,

                'disable_web_page_preview' => true
            );
        }


        $this->http_post_data('sendMessage', json_encode($parameter));
        exit();
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
    // post的json数据请求（带超时和重试）
    public function http_post_data_new($action, $data_string)
    {
        $start_time = microtime(true);
        echo "[" . date('H:i:s') . "] 开始请求: $action" . PHP_EOL;

        $url = rtrim($this->link, '/') . '/' . $action;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json; charset=utf-8',
            'Content-Length: ' . strlen($data_string)
        ]);

        // 超时设置：连接最多 1 秒，总请求最多 3 秒
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 3);

        // 返回结果而不是直接输出
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // HTTP 状态码 >= 400 时返回 false
        curl_setopt($ch, CURLOPT_FAILONERROR, true);

        $result = curl_exec($ch);
        $error = curl_error($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $end_time = microtime(true);
        $duration = round($end_time - $start_time, 3);
        echo "[" . date('H:i:s') . "] 请求结束: $action, 耗时: {$duration}s, HTTP状态码: {$http_code}" . PHP_EOL;

        return [
            'success'   => ($result !== false && $http_code == 200),
            'http_code' => $http_code,
            'response'  => $result,
            'error'     => $error,
            'duration'  => $duration
        ];
    }






    //系统后台：
    public function start($chatid)
    {
        $keyboard2 = [
            'keyboard' => [
                [

                    ['text' => '查看商户列表'],
                    //  ['text' => '新增商户汇率'],
                    ['text' => 'U币汇率设置'],
                ],
            ],
            //可选。请求客户端垂直调整键盘大小以获得最佳适配（例如，如果只有两行按钮，则使键盘更小）。默认为false，在这种情况下，自定义键盘始终与应用程序的标准键盘高度相同。
            'resize_keyboard' => true,
            //可选。要求客户在使用后立即隐藏键盘。键盘仍然可用，但客户端会在聊天中自动显示常用的字母键盘——用户可以在输入字段中按下一个特殊的按钮来再次看到自定义键盘。默认为false。
            'one_time_keyboard' => false,
            //string 可选。键盘处于活动状态时要在输入字段中显示的占位符；1-64 个字符
            //'input_field_placeholder'=>'',
            //可选。如果您只想向特定用户显示键盘，请使用此参数。目标：1），其在用户@mentioned文本的的消息对象; 2）如果机器人的消息是回复（有reply_to_message_id），原始消息的发件人。

            //'selective'=>''
        ];
        $encodedKeyboard2 = json_encode($keyboard2);


        $parameter = array(
            'chat_id' => $chatid,
            'text' => "你好:" . "欢迎使用本系统后台！",
            'reply_markup' => $encodedKeyboard2
        );
        //设置当前用户进入后台：


        //发送消息

        $this->http_post_data('sendMessage', json_encode($parameter));
        exit();

    }

    public function tuisong($chatid, $uid, $from_id)
    {
        $sql = "select * from pay_userpayorder where chat_id='" . $chatid . "'";
        $order_query = $this->pdo->query($sql);
        $order_info = $order_query->fetchAll();
        if (empty($uid)) {
            $parameter = array(
                'chat_id' => $chatid,
                'parse_mode' => 'HTML',
                'text' => "格式错误！例如：/tuisong1000"
            );
            $this->http_post_data('sendMessage', json_encode($parameter));
            exit();
        }
        if ($order_info[0]['uid'] == $uid) {
            $parameter = array(
                'chat_id' => $chatid,
                'parse_mode' => 'HTML',
                'text' => "当前账号已经设置成功过了！"
            );
            $this->http_post_data('sendMessage', json_encode($parameter));
            exit();
        }
        if ($order_info) {
            $parameter = array(
                'chat_id' => $chatid,
                'parse_mode' => 'HTML',
                'text' => "当前群已经存在商户号：" . $order_info[0]['uid']
            );
            $this->http_post_data('sendMessage', json_encode($parameter));
            exit();
        }
        if ($from_id != "982124360") {
            $parameter = array(
                'chat_id' => $chatid,
                'parse_mode' => 'HTML',
                'text' => "当前群设置推送只能由！@fu_008 处理！"
            );
            $this->http_post_data('sendMessage', json_encode($parameter));
            exit();
        }


        $sql2 = "select * from pay_botsettle where merchant='" . $uid . "'";
        $order_query2 = $this->pdo->query($sql2);
        $order_info2 = $order_query2->fetchAll();
        $tuisong = $order_info2[0]['atyonghu'];
        $dingdanshu = "60,50,40,30,20,10";
        $jiansuotime = "60";//60分钟
        $jiangetime = "60";//60分钟
        $this->pdo->exec("INSERT INTO `pay_userpayorder` (`uid`, `from_id`,`chat_id` ,`tuisong`, `dingdanshu`, `jiansuotime`, `jiangetime`) VALUES ('" . $uid . "', '" . $from_id . "', '" . $chatid . "', '" . $tuisong . "', '" . $dingdanshu . "', '" . $jiansuotime . "', '" . $jiangetime . "')");
        $parameter = array(
            'chat_id' => $chatid,
            'parse_mode' => 'HTML',
            'text' => "设置成功！"
        );
        $this->http_post_data('sendMessage', json_encode($parameter));

        $messages .= "\r\n你可以提前设置你要的关注限制";

        $inline_keyboard_arr2[0] = array('text' => "通知人", "callback_data" => "fanhuiuser_people_" . $uid);
        $inline_keyboard_arr2[1] = array('text' => "通知单数", "callback_data" => "fanhuiuser_danshu_" . $uid);
        $inline_keyboard_arr2[2] = array('text' => "时间范围", "callback_data" => "fanhuiuser_fanwei_" . $uid);
        $inline_keyboard_arr2[3] = array('text' => "通知间隔", "callback_data" => "fanhuiuser_jiange_" . $uid);
        $keyboard = [
            'inline_keyboard' => [
                $inline_keyboard_arr2
            ]
        ];
        $parameter = array(
            'chat_id' => $chatid,
            'parse_mode' => 'HTML',
            'text' => $messages,
            'reply_markup' => $keyboard,
            'disable_web_page_preview' => true
        );


        $this->http_post_data('sendMessage', json_encode($parameter));

        exit();
    }

    //删除推送：
    public function tuisongs($chatid, $uid, $from_id)
    {
        $sql = "select * from pay_userpayorder where chat_id='" . $chatid . "'";
        $order_query = $this->pdo->query($sql);
        $order_info = $order_query->fetchAll();
        if (!$order_info[0]['uid']) {
            $parameter = array(
                'chat_id' => $chatid,
                'parse_mode' => 'HTML',
                'text' => "当前群尚未绑定推送的商户号！"
            );
            $this->http_post_data('sendMessage', json_encode($parameter));
            exit();
        }
        if ($from_id != "982124360") {
            $parameter = array(
                'chat_id' => $chatid,
                'parse_mode' => 'HTML',
                'text' => "当前群设置删除只能由！@fu_008 处理！"
            );
            $this->http_post_data('sendMessage', json_encode($parameter));
            exit();
        }
        $set_sql = "DELETE FROM pay_userpayorder where chat_id='" . $chatid . "'";
        $this->pdo->exec($set_sql);

        $parameter = array(
            'chat_id' => $chatid,
            'parse_mode' => 'HTML',
            'text' => "删除成功！"
        );
        $this->http_post_data('sendMessage', json_encode($parameter));


        exit();
    }

    public function quanxian($chatid, $userid, $quanxian, $username)
    {
        $username = "@" . $username;
        if (!in_array($userid, $this->chaojiyonghu)) {

            // 查询用户所在的所有用户组（通过 userid 和 username 两种方式）
            $set_sql1 = "select * FROM pay_zuren where typelist ='2' and (username='" . $userid . "' or username='" . $username . "')";

            $order_query2 = $this->pdo->query($set_sql1);
            $order_info2 = $order_query2->fetchAll();

            // 如果用户不在任何用户组中
            if (!$order_info2 || empty($order_info2)) {
                $parameter = array(
                    'chat_id' => $chatid,
                    'parse_mode' => 'HTML',
                    'text' => $userid."-->你没有当前在权限用户组内,请联系楚歌添加权限",
                );
                $this->http_post_data('sendMessage', json_encode($parameter));
                exit();
            }

            // 收集所有用户组ID
            $yonghuzu_id_array = array();
            foreach ($order_info2 as $row) {
                if (!empty($row['yonghuzu_id'])) {
                    $yonghuzu_id_array[] = $row['yonghuzu_id'];
                }
            }

            // 去重
            $yonghuzu_id_array = array_unique($yonghuzu_id_array);

            if (empty($yonghuzu_id_array)) {
                $parameter = array(
                    'chat_id' => $chatid,
                    'parse_mode' => 'HTML',
                    'text' => "你没有当前在权限用户组内,请联系楚歌@fu_008添加权限",
                );
                $this->http_post_data('sendMessage', json_encode($parameter));
                exit();
            }

            // 查询所有用户组的权限列表
            $yonghuzu_id_str = implode("','", $yonghuzu_id_array);
            $set_sql2 = "select * FROM pay_yonghuzu where typelist ='2' and id IN ('" . $yonghuzu_id_str . "')";
            $order_query3 = $this->pdo->query($set_sql2);
            $order_info3 = $order_query3->fetchAll();

            // 合并所有用户组的权限
            $all_mingling_arr = array();
            foreach ($order_info3 as $group) {
                if (!empty($group['mingling'])) {
                    $mingling_arr = explode(",", $group['mingling']);
                    // 去除空白并合并
                    foreach ($mingling_arr as $mingling) {
                        $mingling = trim($mingling);
                        if (!empty($mingling)) {
                            $all_mingling_arr[] = $mingling;
                        }
                    }
                }
            }

            // 去重权限列表
            $all_mingling_arr = array_unique($all_mingling_arr);

            // 检查权限
            if (empty($all_mingling_arr)) {
                $parameter = array(
                    'chat_id' => $chatid,
                    'parse_mode' => 'HTML',
                    'text' => "当前用户组没有此项权限,请联系楚歌@fu_008添加",
                );
                $this->http_post_data('sendMessage', json_encode($parameter));
                exit();
            }

            if (!in_array($quanxian, $all_mingling_arr)) {
                $parameter = array(
                    'chat_id' => $chatid,
                    'parse_mode' => 'HTML',
                    'text' => "你没有当前   <b>" . $quanxian . "</b>   操作此命令,请联系楚歌@fu_008添加",
                );
                $this->http_post_data('sendMessage', json_encode($parameter));
                exit();
            }

        }


    }

    public function ouyi($type = "0", $idsp = "0")
    {
        //请求api:
        $huilv_infos = json_decode(Http::get($this->huilv_api), true);
        $huilv_info_price = $huilv_infos['data']['sell'][0]['price'];
        if ($huilv_info_price > 0) {
            //发消息给老板：
            $this->xiaoxinoend(date("Y年m月d日", time()) . ":获取到最新欧意的U价:" . $huilv_info_price . "(未加0.07)", $this->laoban_chatid);
            //添加记录：并且将最新的U价修改成最新下发的
            $guding_fudian = $this->guding_fudian;
            $typevalue = $huilv_info_price + $guding_fudian;
            $pid = "99999";
            $typelist = "4";
            $typeid = "huilv";
            $chatid_all = "99999";
            $sql_info2 = "select id from pay_userfeilv where typelist='" . $typelist . "' and pid ='" . $pid . "' and chatid='" . $chatid_all . "' and type='" . $typeid . "'";
            $order_info2 = $this->shujuku($sql_info2);
            if ($order_info2) {
                $ids = $order_info2[0]['id'];
                //存在
                $set_sql2 = "update pay_userfeilv set feilv ='" . $typevalue . "' where  id='" . $ids . "'";
                $chang_status = $this->pdo->exec($set_sql2);
            } else {
                //不存在
                $set_sql2 = "insert into pay_userfeilv (pid,chatid,type,createtime,typelist,feilv) values ('" . $pid . "','" . $chatid_all . "','" . $typeid . "','" . $times . "','" . $typelist . "','" . $typevalue . "')";
                $chang_status = $this->pdo->exec($set_sql2);
            }
            $this->xiaoxinoend(date("Y年m月d日", time()) . ":将商户下发U率修改成：【" . $typevalue . "】成功", $this->laoban_chatid);
            $nexttime = time() + 3600;
            //这里查询一下是不是已经存在了：
            $now_time = strtotime(date("Y-m-d"));
            if ($type == "0") {
                $set_sql2 = "update pay_huoquhuilv set price='" . $huilv_info_price . "',nexttime='" . $nexttime . "',createtime='" . time() . "' where  id='" . $idsp . "'";
                $chang_status = $this->pdo->exec($set_sql2);
            } else {
                $set_sql_add2 = "insert into pay_huoquhuilv (price,huoqutime,createtime,nexttime) values ('" . $huilv_info_price . "','" . $now_time . "','" . time() . "','" . $nexttime . "')";
            }

            $order_info_add = $this->pdo->exec($set_sql_add2);

        } else {
            $this->xiaoxinoend("没有获取到最新欧意的U价，请查看接口是否异常！", $this->laoban_chatid);
        }
    }

    //系统后台：
    public function start_hou($chatid)
    {
        $keyboard2 = [
            'keyboard' => [
                [

                    ['text' => '首页'],
                    ['text' => '订单管理'],
                    ['text' => '结算管理'],
                    ['text' => '统一费率'],
                ],
                [

                    ['text' => '查看商户列表'],
                    ['text' => '商户管理'],
                    // ['text' => '支付接口'],
                    ['text' => "广播推送"],
                    ['text' => '其他功能'],
                ],

                [
                    ['text' => 'Trx手续费'],
                    ['text' => '权限用户组'],

                ],

            ],
            //可选。请求客户端垂直调整键盘大小以获得最佳适配（例如，如果只有两行按钮，则使键盘更小）。默认为false，在这种情况下，自定义键盘始终与应用程序的标准键盘高度相同。
            'resize_keyboard' => true,
            //可选。要求客户在使用后立即隐藏键盘。键盘仍然可用，但客户端会在聊天中自动显示常用的字母键盘——用户可以在输入字段中按下一个特殊的按钮来再次看到自定义键盘。默认为false。
            'one_time_keyboard' => false,
            //string 可选。键盘处于活动状态时要在输入字段中显示的占位符；1-64 个字符
            //'input_field_placeholder'=>'',
            //可选。如果您只想向特定用户显示键盘，请使用此参数。目标：1），其在用户@mentioned文本的的消息对象; 2）如果机器人的消息是回复（有reply_to_message_id），原始消息的发件人。

            //'selective'=>''
        ];
        $encodedKeyboard2 = json_encode($keyboard2);


        $parameter = array(
            'chat_id' => $chatid,
            'text' => "你好:" . "欢迎使用本系统支付后台！",
            'reply_markup' => $encodedKeyboard2
        );
        //设置当前用户进入后台：


        //发送消息

        $this->http_post_data('sendMessage', json_encode($parameter));
        exit();

    }

    function generateVisitorToken()
    {
        return bin2hex(random_bytes(32)); // 生成64字符的十六进制字符串
    }

    /**
     * 发起一个GET请求
     *
     * @param string $url 请求的URL
     * @param array $headers 可选的HTTP头信息
     * @return mixed 响应数据或错误信息
     */
    function httpGet($url, $headers = [])
    {
        // 初始化 cURL
        $ch = curl_init();

        // 设置 cURL 选项
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        // 如果有HTTP头信息，则设置
        if (!empty($headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        // 执行请求并获取响应
        $response = curl_exec($ch);

        // 检查是否有错误发生
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
            return false;
        }

        // 关闭 cURL 会话
        curl_close($ch);

        // 返回响应
        return json_decode($response, true);
    }

    /**
     * 从 Telegram 获取文件路径
     */
    function getTelegramFilePath($fileId)
    {
        $url = "https://api.telegram.org/bot" . $this->token . "/getFile?file_id=" . $fileId;
        $response = file_get_contents($url);
        $fileData = json_decode($response, true);
        return "https://api.telegram.org/file/bot" . $this->token . "/" . $fileData['result']['file_path'];
    }

    /**
     * 生成随机字符串
     *
     * @param int $length 字符串长度
     * @return string 随机字符串
     */
    function generateRandomString($length = 10)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    /**
     * 下载 Telegram 文件到本地
     */

    function downloadTelegramFile($fileUrl)
    {
        $saveDir = __DIR__ . "/upload/shanghuchat";
        // 检查并创建保存目录
        if (!is_dir($saveDir)) {
            mkdir($saveDir, 0777, true);
        }

        // 生成随机文件名
        $fileExtension = pathinfo($fileUrl, PATHINFO_EXTENSION);
        $randomFileName = $this->generateRandomString(10) . '.' . $fileExtension;
        $localFilePath = $saveDir . '/' . $randomFileName;

        // 打开文件句柄
        $fileHandler = fopen($fileUrl, 'rb');
        if ($fileHandler === false) {
            return "error";
            return 'Failed to open URL: ' . $fileUrl;
        }

        // 打开本地文件句柄
        $localFileHandler = fopen($localFilePath, 'wb');
        if ($localFileHandler === false) {
            fclose($fileHandler);
            return "error";
            return 'Failed to create local file: ' . $localFilePath;
        }

        // 将远程文件内容写入本地文件
        while (!feof($fileHandler)) {
            fwrite($localFileHandler, fread($fileHandler, 8192));
        }

        // 关闭文件句柄
        fclose($fileHandler);
        fclose($localFileHandler);

        // 检查文件是否存在
        if (file_exists($localFilePath)) {
            return $localFilePath;
        } else {
            return "error";
            return 'Failed to download file to local path: ' . $localFilePath;
        }
    }

    /**
     * 上传文件到 Rocket.Chat
     */
    function uploadImage($filePath, $visitorToken)
    {
        $url = $this->chat_url . 'api/v1/livechat/upload/' . $visitorToken;

        // 初始化 cURL
        $ch = curl_init();

        // 设置 cURL 选项
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: multipart/form-data'
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, [
            'file' => new CURLFile($filePath)
        ]);

        // 执行请求并获取响应
        $response = curl_exec($ch);

        // 检查是否有错误发生
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
            return false;
        }

        // 关闭 cURL 会话
        curl_close($ch);

        // 返回响应
        $uploadResponse = json_decode($response, true);
        $fileId = $uploadResponse['file']['_id'];
        return $fileId;

    }

    /**
     * 发送包含图片的消息
     */
    function sendImageMessage($roomId, $visitorToken, $text)
    {
        $url = $this->chat_url . 'api/v1/livechat/message';
        $postData = [
            'rid' => $roomId,
            'msg' => $text,
            'token' => $visitorToken
        ];
        $headers = [
            'Content-Type: application/json'
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }

        curl_close($ch);


    }

    /**
     * 发送消息到 Rocket.Chat 的 Live Chat
     */
    function sendMessageToRocketChat($text, $room_id, $token, $agentId, $kefu_username, $chatid)
    {
        $url = $this->chat_url . '/api/v1/livechat/message';
        $postData = [
            'rid' => $room_id,
            'msg' => $text,
            'token' => $token,
            'agent' => [
                'agentId' => $agentId,
                'username' => $kefu_username
            ]
        ];
        $headers = [
            'Content-Type: application/json'
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }

        curl_close($ch);
    }

    /**
     * 上传图片到 Rocket.Chat 的 Live Chat
     *
     * @param string $filePath 文件路径
     * @param string $visitorToken 访客 Token
     * @return mixed 文件 URL 或错误信息
     */
    function uploadFileToLiveChatRoom($filePath, $roomId, $visitorToken, $description = '', $chatid)
    {
        $url = $this->chat_url . '/api/v1/livechat/upload/' . $roomId;
        $postData = [
            'file' => new CURLFile($filePath),
            'description' => $description,
        ];
        $headers = [
            'x-visitor-token: ' . $visitorToken,
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);


        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
            return false;
        }

        $result = json_decode($response, true);
        curl_close($ch);

        return $result;
    }

    public function quedingxiafazuoriuid($content, $chatid)
    {

        $sql_info = "select * from pay_botsettle where chatid ='" . $chatid . "'";

        $order_query2 = $this->pdo->query($sql_info);
        $chatinfo = $order_query2->fetchAll();

        if (!$chatinfo) {
            $this->xiaoxi("该群暂未绑定商户号，请输入快捷命令：/bd商户号", $chatid);
        }
        $uid = $chatinfo['0']['merchant'];

        if ($this->kaiqi_teshu_xiafa) {
            $nayitian = $this->teshu_riqi;
            $today = date("Y-m-d", strtotime(date($nayitian)));
            $todays = date("Y年m月d日", strtotime(date($nayitian)));
            $todays2 = date("m月d日", strtotime(date($nayitian)));
        } else {
            $today = date("Y-m-d", strtotime("-1 day"));
            $todays = date("Y年m月d日", strtotime("-1 day"));
            $todays2 = date("m月d日", strtotime("-1 day"));
        }


        $huilvinfo = $this->huilvinfo("99999", "99999");
        $fufonginfo = $this->fudonginfo($uid, $chatid);
        $fenchenginfo = $this->fenchenginfo($uid, $chatid);

        $tongdaoxinxi = $this->tongdaoxinxi($uid, $chatid);
        $zhifuxinxi = $this->zhifuxinxi($uid, $chatid);

        $sql_zhifu = "select id,showname from pay_type";

        $zhifu_fetch = $this->shujuku($sql_zhifu);
        $zhifu_info_arr = array();
        foreach ($zhifu_fetch as $kp => $vp) {
            $zhifu_info_arr[$vp['id']] = $vp['showname'];
        }

        if (count($zhifuxinxi) <= 0) {
            $this->xiaoxinoend("当前商户暂未设置支付类型费率，请先设置！", $chatid);
            return false;
        }

        //这里去请求设置汇率：$huilv_api
        $now_time = strtotime(date("Y-m-d"));
        //查询是不是请求过了:
        $huilv_info = $sql_info = "select * from pay_huoquhuilv where  huoqutime='" . $now_time . "' order by id desc";
        $hui_query = $this->pdo->query($huilv_info);
        $huilvinfop = $hui_query->fetchAll();
        if ($huilvinfop) {
            //如果存在，就看看时间：
            $nexttimes = $huilvinfop[0]['nexttime'];
            if (time() > $nexttimes) {
                $this->ouyi(0, $huilvinfop[0]['id']);
            }
        } else {
            $this->ouyi(1);

        }
        $message = "";
        $all_zhifu = array();  //纯支付方式的量
        $all_tongdao = array(); //纯设置通道的量
        $all_tongdao_zhifu = array();  //支付方式下的各个通道跑的数据

        $sql_info3 = "select username,usdt_str from pay_user where  uid ='" . $uid . "'";
        $order_query7 = $this->pdo->query($sql_info3);
        $chatinfo3 = $order_query7->fetchAll();
        $uidinfo2 = $chatinfo3[0];

        //查询次商户号昨日总收入信息：
        $sql_info = "select sum(getmoney) as getmoney from pay_order where status = '1' and uid ='" . $uid . "' and date='" . $today . "'";

        $order_query3 = $this->pdo->query($sql_info);
        $chatinfo = $order_query3->fetchAll();
        $order_today = round($chatinfo[0]['getmoney'], 2);
        if ($order_today <= 0) {

            /*$message .= "<strong>💰收入结算:0u</strong>";
            $parameter = array(
                'chat_id' => $chatid,
                'parse_mode' => 'HTML',
                'text' => $message,
            );


            $this->http_post_data('sendMessage', json_encode($parameter));
            exit();*/
            $this->xiaoxinoend("当前商户暂未设置支付类型费率，请先设置！", $chatid);
            return false;
        }


        //查看昨日总下发的记录 这里有一点需要注意，如果昨日存在有下发异常的 需要天使自己核对 手动下发：
        $zuori_sql = "select * from pay_jinrixiafa where status = '0' and pid ='" . $uid . "' and xiafatime='" . $today . "'";

        $zuorixiafa = $this->shujuku($zuori_sql);
        if ($zuorixiafa) {
            /*$parameter = array(
                'chat_id' => $chatid,
                'parse_mode' => 'HTML',
                'text' => "当前商户昨日存在实时下发" . $zuorixiafa[0]['money'] . "U异常！建议手动结算昨日收益！",
            );
            $this->http_post_data('sendMessage', json_encode($parameter));
            exit();*/
            $this->xiaoxinoend("当前商户昨日存在实时下发" . $zuorixiafa[0]['money'] . "U异常！建议手动结算昨日收益！", $chatid);
            return false;

        }

        //最日下发的数据
        $zuori_money = 0.00;
        $zuori_usdt = 0.00;

        //昨日收益数据分析：
        $sql_info = "select * from pay_order where status = '1' and uid ='" . $uid . "' and date='" . $today . "'";
        $order_query3 = $this->pdo->query($sql_info);
        $zuoorderinfo = $order_query3->fetchAll();

        $all_money = 0;
        foreach ($zuoorderinfo as $key => $value) {
            $all_money += $value['money'];
            //支付方式计算
            $all_zhifu[$value['type']] += $value['money'];

            //支付方式下的各个通道跑的数据：
            $all_tongdao_zhifu[$value['type']][$value['channel']] += $value['money'];
            if (array_key_exists($value['channel'], $tongdaoxinxi)) {
                //通道费用计算
                $all_tongdao[$value['channel']] += $value['money'];
            }
        }
        $msg = "✅" . $todays2 . "量情况如下\r\n🆔商户号:" . $uid . "\r\n🧑🏻‍💼名字:" . $uidinfo2['username'] . "\r\n";


        if (count($all_zhifu) > 0) {
            foreach ($all_zhifu as $kt => $vt) {
                $sql_zhifu = "select showname from pay_type where  id ='" . $kt . "'";

                $zhifu_fetch = $this->shujuku($sql_zhifu);

                $zhifu_info = $zhifu_fetch[0]['showname'];
                $msg .= "🔔" . $zhifu_info . "总量:" . $vt . "\r\n";
            }

        }


        $type = substr($fufonginfo, 0, 1);
        if ($type == "-") {
            $changs = explode("-", $fufonginfo);
            $shiji_huilv = $huilvinfo - $changs[1];
        } else {
            $changs = explode("+", $fufonginfo);
            $shiji_huilv = $huilvinfo + $changs[1];
        }

        $shiji_huilv_tousu = $shiji_huilv - 0.1;

        $all_usdt_m = 0;
        $all_fusdt_money = 0;
        $xiafa_str = "";
        $feilihoujiner = 0;
        foreach ($all_tongdao_zhifu as $kv => $vv) {
            //$zhifu_info_arr[$kv]
            //$msg .= "\r\n📮" . $zhifu_info_arr[$kv] . "跑量如下：\r\n\r\n";
            foreach ($vv as $kp => $vp) {
                $channel_sql = "select id,name from pay_channel where id='" . $kp . "'";
                $channel_info_query = $this->shujuku($channel_sql);
                $channel_info = $channel_info_query[0];
                // $msg .= "(" . $channel_info['id'] . ")" . $channel_info['name'] . ":" . $vp . "\r\n";
                if (array_key_exists($kp, $tongdaoxinxi)) {

                    $zhifu_lixi = $tongdaoxinxi[$kp];

                } else {
                    $zhifu_lixi = $zhifuxinxi[$kv];

                }
                $type = substr($fufonginfo, 0, 1);

                $jisuan = round(($vp * $zhifu_lixi * $fenchenginfo) / ($shiji_huilv), 2);
                //$msg .= $vp . "*" . $zhifu_lixi . "*" . $fenchenginfo . "/(" . $shiji_huilv . ")=" . $jisuan . "U\r\n\r\n";

                $xiafa_str .= $jisuan . "+";

                $feilihoujiner += round(($vp * $zhifu_lixi * $fenchenginfo), 2);

                $all_usdt_m += $jisuan;
                $all_fusdt_money += $vp;
            }
        }
        $msg .= "💹总跑量:" . $all_money . "元\r\n";
        $msg .= "💹费率后总额:" . $feilihoujiner . "元\r\n\r\n";
        $msg .= "➖➖➖➖➖➖➖➖➖\r\n\r\n";
        $msg .= "不可下发金额\r\n\r\n";

        $tousu_info2 = "select * from pay_usertousu where pid ='" . $uid . "'";

        $order_tousu2 = $this->pdo->query($tousu_info2);
        $tousu_m2 = $order_tousu2->fetchAll();
        $tousu_today = 0;
        $tousu_today2 = 0;
        $tousu_U = 0;
        $jinritimne = date("Y-m-d", time());
        foreach ($tousu_m2 as $k => $v) {
            $time = date('m-d', strtotime($v['date']));
            $tousu_today += $v['money'];

            if ($v['status'] == "1") {
                //已扣除
                $pp = "已扣除";
                //如果是今天扣的，要计算体现到出来：
                if ($jinritimne == $v['koushijian']) {
                    $tousu_today2 += $v['money'];
                    $tousu_U += $v['money'];
                }
            } else {
                //待扣除
                $pp = "待扣除 ---- /delete_tousu_" . $v['id'];
                $tousu_today2 += $v['money'];
                $tousu_U += $v['money'];

            }


            $msg .= "❌" . $time . ":投诉退款:" . $v['money'] . "元  ----" . $pp . "\r\n";
        }


        //查看投诉退款数据：
        if ($tousu_U > 0) {
            $tousu_U2 = $tousu_U;
            $msg .= "❌合计待投诉退款:" . $tousu_today2 . "元\r\n";
        } else {
            $tousu_U2 = 0;
        }

        $xiafa_str = substr($xiafa_str, 0, -1);

        $xiafa_str .= "-" . $tousu_U2;

        //查看今日下发数据记录：
        $jinri_info = "select money,jutishijian,feiu_money,feilv from pay_jinrixiafa where status='1' and pid ='" . $uid . "' and xiafatime='" . $today . "' and chatid='" . $chatid . "'";
        $order_jinri = $this->pdo->query($jinri_info);
        $tjinri_arr = $order_jinri->fetchAll();
        $all_jinri_xiafa = 0.00;


        if ($tjinri_arr) {

            $msg .= "\r\n📮" . $todays2 . "下发历史记录" . "\r\n";
            foreach ($tjinri_arr as $kj => $vj) {
                $zuori_money += $vj['all_feiu_money'];
                $zuori_usdt += $vj['money'];


                $ti = date('H:i:s', $vj['jutishijian']);
                $msg .= "🔈" . $ti . " 已下发：" . $vj['feiu_money'] . "/" . $vj['feilv'] . "/" . $vj['money'] . "\r\n";
                $all_jinri_xiafa += $vj['feiu_money'];

                $xiafa_str .= "-" . $vj['feiu_money'];
            }
        }
        $trx_info = "select * from pay_usertrx";
        $trx_jinri = $this->pdo->query($trx_info);
        $trx_arr = $trx_jinri->fetchAll();

        if ($trx_arr) {
            $trx_shouxufei = $trx_arr[0]['trx'];
        } else {
            $trx_shouxufei = 0.00;
        }

        $bukexiafaheji_zuoro = $all_jinri_xiafa + $tousu_today2;

        $msg .= "\r\n💹不可下发金额合计：" . $bukexiafaheji_zuoro . "元\r\n\r\n";
        $msg .= "➖➖➖➖➖➖➖➖➖\r\n";
        $msg .= "下发扣除费用\r\n\r\n";
        $msg .= "🔄Trx手续费=" . $trx_shouxufei . "U\r\n\r\n";
        $xiafa_str .= "-" . $trx_shouxufei;


        $keyixiafa_value = $feilihoujiner - $bukexiafaheji_zuoro;
        $keyixiafa_str = $feilihoujiner . " - " . $bukexiafaheji_zuoro . " = " . $keyixiafa_value;

        $msg .= "🈴当前可下发:" . $keyixiafa_str . "元";


        //实际下发：
        $shijixiafa_value = (floor((($keyixiafa_value / $shiji_huilv) * 100)) / 100) - $trx_shouxufei;
        $shijixiafa_str = $keyixiafa_value . "/" . $shiji_huilv . " - " . $trx_shouxufei . " = " . $shijixiafa_value;

        $msg .= "\r\n🈴实际下发:" . $shijixiafa_str . "U";

        $jie_all_jin_u = $all_jinri_xiafa > 0 ? $all_jinri_xiafa : 0;
        $jie_all_tou_u = $tousu_U2 > 0 ? round($tousu_U2, 2) : 0;
        $jie_all_usdt_m = round($all_usdt_m, 2);
        $msg .= "\r\n✅下发地址:\r\n" . $uidinfo2['usdt_str'];

        //查询结算是否已经下发：
        $sql_info_u = "select * from pay_zuorixiafau where pid ='" . $uid . "' and xiafatime='" . $today . "' and status='1'";


        $order_query_user_u = $this->pdo->query($sql_info_u);
        $xiafa_i_u = $order_query_user_u->fetchAll();

        $xiafade_day = date("d");
        if ($xiafa_i_u) {
            $this->xiaoxinoend("当前商户已经下发过了", $chatid);
            return false;

        }
        /*if ($xiafa_i_u) {
            $inline_keyboard_arr[0] = array('text' => "收益已清算", "callback_data" => "yijingxiafa_" . $uid);
        } else {
            $inline_keyboard_arr[0] = array('text' => "确定下发:" . $shijixiafa_value . "U", "callback_data" => "zuotianxiafa_user_" . $uid . "&&" . $shijixiafa_value."!!!".$xiafade_day);
        }
        $inline_keyboard_arr2[0] = array('text' => "查详细账单", "callback_data" => "chakanzuorixiangxi_" . $uid);*/
        $text_new = "zuotianxiafa_user_" . $uid . "&&" . $shijixiafa_value . "!!!" . $xiafade_day;
        $this->quedingxiafa($msg, $shijixiafa_value, $uid, $xiafade_day, $chatid, $text_new);

    }

    public function quedingxiafa($msg, $shijixiafa_value, $uid, $xiafade_day, $chatid, $text)
    {


        $str_arr = explode("zuotianxiafa_user_", $text);
        $arr_new = explode("&&", $str_arr[1]);
        $pid = $arr_new[0];

        $arr_new_change = explode("!!!", $arr_new[1]);

        $usdt_m = $arr_new_change[0];
        $usdt_fm = 0;
        if ($usdt_m <= 0) {
            //$this->xiaoxi("余额不足！", $chatid);
            $this->xiaoxinoend("余额不足", $chatid);
            return false;
        }

        $usdt_m_xiafashijian = $arr_new_change[1];
        $jinris = date("d");


        if ($jinris != $usdt_m_xiafashijian) {
           // $this->xiaoxi("禁止跨日下发！", $chatid);
            $this->xiaoxinoend("禁止跨日下发！", $chatid);
            return false;
        }
        $message_id = '';
        //记录下发数据：再去调用下发数据接口：
        $this->quedingxiafa_xiafa($pid, $usdt_m, $usdt_fm, $message_id, $chatid, '1');
    }

    public function quedingxiafa_xiafa($pid, $ubi, $usdt_fm, $message_id, $chatid, $type = "0")
    {

        $uid = $pid;
        $chat_id = $chatid;

        $set_sql1 = "select * FROM pay_user where uid='" . $uid . "'";
        $order_query2 = $this->pdo->query($set_sql1);
        $order_info2 = $order_query2->fetchAll();

        if ($this->kaiqi_teshu_xiafa) {
            $teshu_riqi = $this->teshu_riqi;
            $not_time = date("Y-m-d", strtotime(date($teshu_riqi)));
        } else {
            $not_time = date("Y-m-d", strtotime("-1 day"));
        }


        $sql_info_u = "select * from pay_zuorixiafau where pid ='" . $uid . "' and xiafatime='" . $not_time . "' and status ='1'";
        $order_query_user_u = $this->pdo->query($sql_info_u);
        $xiafa_i_u2 = $order_query_user_u->fetchAll();
        if ($xiafa_i_u2) {
            /*$parameter = array(
                'chat_id' => $chat_id,
                'parse_mode' => 'HTML',
                'text' => "当前商户已经下发过了！禁止再下发！异常情况请联系楚歌@fu_008 "
            );
            $this->http_post_data('sendMessage', json_encode($parameter));
            exit();*/
            $this->xiaoxinoend("当前商户已经下发过了！禁止再下发！异常情况请联系楚歌 @fu_008", $chatid);
            return false;

        }


        //查看当天是不是有正在下发的数据记录，不管是不是真正成功了，都需要查询
        $set_sql3 = "select * FROM pay_zuorixiafau where pid='" . $pid . "' and xiafatime='" . $not_time . "' and status='0'";
        $order_query3 = $this->pdo->query($set_sql3);
        $xiafa_info3 = $order_query3->fetchAll();

        if ($xiafa_info3) {
            /*$msg = "<b>异常！！！</b>\r\n当前商户存在操作下发操作,但未收到USDT交易所返回的成功的信息，无法再次触发下发！请天使工作人员确定后，再手动下发剩余U币！";

            $parameter = array(
                'chat_id' => $chat_id,
                'parse_mode' => 'HTML',
                'text' => $msg
            );
            $this->http_post_data('sendMessage', json_encode($parameter));
            exit();*/
            $this->xiaoxinoend("前商户存在操作下发操作,但未收到USDT交易所返回的成功的信息，无法再次触发下发！请天使工作人员确定后，再手动下发剩余U币！", $chatid);
            return false;

        }

        $set_sql = "insert into pay_zuorixiafau (pid,xiafatime,money,createtime,status) values ('" . $pid . "','" . $not_time . "','" . $ubi . "','" . time() . "','0')";
        $this->pdo->exec($set_sql);
        $insert_id = $this->pdo->lastInsertId();


        //单个uid
        $set_sql1 = "select username,usdt_str FROM pay_user where uid='" . $uid . "'";
        $order_query2 = $this->pdo->query($set_sql1);
        $order_info2 = $order_query2->fetchAll();


        //商户USDT地址：
        $ToAdress = $order_info2[0]['usdt_str'];
        if (empty($ToAdress)) {
            //$this->xiaoxi("当前商户暂未设置下发USDT的地址,请核对后再下发！", $chat_id, '1', $data['callback_query']['id']);
            $this->xiaoxinoend("当前商户暂未设置下发USDT的地址,请核对后再下发！", $chatid);
            return false;

        }
        $param_data = "";


        $ownerAddress = $this->ownerAddress;
        //获取trx信息  get
        $url2 = "http://66.42.50.142:8595/tronapi/tron/trc20QueryBalance/" . $ownerAddress;
        $submitData2 = Http::get($url2, $param_data);
        $two_result = json_decode($submitData2, true);
        if ($two_result['balance'] / 1000000 < $ubi) {


            //下发失败的话，就删除这个下发的数据记录：
            $set_sql = "DELETE FROM pay_zuorixiafau where id='" . $insert_id . "'";
            $this->pdo->exec($set_sql);


            /*$parameter = array(
                'chat_id' => $chat_id,
                'parse_mode' => 'HTML',
                'text' => "很抱歉，你的U币不足以下发,当前余额：" . $two_result['balance'] / 1000000
            );
            $this->http_post_data('sendMessage', json_encode($parameter));
            exit();*/
            $this->xiaoxinoend("很抱歉，你的U币不足以下发,当前余额：" . $two_result['balance'] / 1000000, $chatid);
            return false;

        }
        $param_data = array(
            "ownerAddress" => $ownerAddress,
            "toAddress" => $ToAdress,
            "memo" => "",
            "amount" => $ubi * 1000000
        );

        //$url3 = "http://66.42.50.142:8595/tronapi/tron/trc20CreateTransaction";
        //改用最新的：
        $url4 = $this->pay_pay_url."/api/index/transferUsdt";
        $param_data_new = array(
            "owner_address" => $ownerAddress,
            "to_address" => $ToAdress,
            "private_key" => $this->private_key,
            "amount" => $ubi
        );
        $submitData3 = Http::http_post_data_two($url4, json_encode($param_data_new));
        $three_result = json_decode($submitData3, true);



        if ($three_result['code'] == "0") {

            if (strpos($three_result['msg'], '能量不足') !== false || strpos($three_result['msg'], '带宽不足') !== false) {
                // 包含能量不足或带宽不足
                //echo "匹配到限制信息";
                // 发生异常时执行删除操作
                $set_sql = "DELETE FROM pay_zuorixiafau where id='" . $insert_id . "'";
                $this->pdo->exec($set_sql);
                $this->xiaoxi($three_result['msg'],$chatid);
            }

            $inline_keyboard_arr_xianzhi[0] = array('text' => "解除昨日下发限制", "callback_data" => "jiechuxiafaxianzhi_".$pid);
            $keyboard_xianzhi = [
                'inline_keyboard' => [
                    $inline_keyboard_arr_xianzhi
                ]
            ];
            $parameter2 = array(
                "chat_id" => $chatid,
                'text' => "转账下发失败，请联系天使客服,错误信息：" . $three_result['msg'],
                "parse_mode" => "HTML",
                "disable_web_page_preview" => true,
                'reply_markup' => $keyboard_xianzhi
            );

            $this->http_post_data('sendMessage', json_encode($parameter2));

            exit();

        }

        //$three_result['txId'] ="45679";
        if (!empty($three_result['data']['txid'])) {
            $set_sql2 = "update pay_zuorixiafau set status='1',txId ='" . $three_result['data']['txid'] . "' where  id='" . $insert_id . "'";
            $this->pdo->exec($set_sql2);

            $uid = $pid;
            $uid_end = $uid;
            if ($this->kaiqi_teshu_xiafa) {
                $nayitian = $this->teshu_riqi;
                $today = date("Y-m-d", strtotime(date($nayitian)));
                $todays = date("Y年m月d日", strtotime(date($nayitian)));
                $todays2 = date("m月d日", strtotime(date($nayitian)));
            } else {
                $today = date("Y-m-d", strtotime("-1 day"));
                $todays = date("Y年m月d日", strtotime("-1 day"));
                $todays2 = date("m月d日", strtotime("-1 day"));
            }
            $huilvinfo = $this->huilvinfo("99999", "99999");
            $fufonginfo = $this->fudonginfo($uid, $chatid);
            $fenchenginfo = $this->fenchenginfo($uid, $chatid);

            $tongdaoxinxi = $this->tongdaoxinxi($uid, $chatid);
            $zhifuxinxi = $this->zhifuxinxi($uid, $chatid);

            $sql_zhifu = "select id,showname from pay_type";

            $zhifu_fetch = $this->shujuku($sql_zhifu);
            $zhifu_info_arr = array();
            foreach ($zhifu_fetch as $kp => $vp) {
                $zhifu_info_arr[$vp['id']] = $vp['showname'];
            }

            if (count($zhifuxinxi) <= 0) {
                //$this->xiaoxi("当前商户暂未设置支付类型费率，请先设置！", $chatid);
                $this->xiaoxinoend(  "当前商户暂未设置支付类型费率，请先设置！", $chatid);
                return false;
            }

            //这里去请求设置汇率：$huilv_api
            $now_time = strtotime(date("Y-m-d"));
            //查询是不是请求过了:
            $huilv_info = $sql_info = "select * from pay_huoquhuilv where  huoqutime='" . $now_time . "' order by id desc";
            $hui_query = $this->pdo->query($huilv_info);
            $huilvinfop = $hui_query->fetchAll();
            if ($huilvinfop) {
                //如果存在，就看看时间：
                $nexttimes = $huilvinfop[0]['nexttime'];
                if (time() > $nexttimes) {
                    $this->ouyi(0, $huilvinfop[0]['id']);
                }
            } else {
                $this->ouyi(1);

            }

            $all_zhifu = array();  //纯支付方式的量
            $all_tongdao = array(); //纯设置通道的量
            $all_tongdao_zhifu = array();  //支付方式下的各个通道跑的数据

            $sql_info3 = "select username,usdt_str from pay_user where  uid ='" . $uid . "'";
            $order_query7 = $this->pdo->query($sql_info3);
            $chatinfo3 = $order_query7->fetchAll();
            $uidinfo2 = $chatinfo3[0];
            //这里需要将投诉金额设置已经扣除：
            $tousu_info = "select sum(money) as tousumoney from pay_usertousu where status='0' and  pid ='" . $uid . "'";
            $order_tousu = $this->pdo->query($tousu_info);
            $tousu_m = $order_tousu->fetchAll();
            if ($tousu_m > 0) {
                $set_sql2 = "update pay_usertousu set status='1'  where  pid ='" . $uid . "'";
                $this->pdo->exec($set_sql2);
            }
            //确定下发了，也要改变状态：
            $set_sql2 = "update pay_zuorixiafau set status='1',txId ='" . $three_result['data']['txid']. "' where  id='" . $insert_id . "'";
            $this->pdo->exec($set_sql2);

            //查询次商户号昨日总收入信息：
            $sql_info = "select sum(getmoney) as getmoney from pay_order where status = '1' and uid ='" . $uid . "' and date='" . $today . "'";
            $message = "";
            $order_query3 = $this->pdo->query($sql_info);
            $chatinfo = $order_query3->fetchAll();
            $order_today = round($chatinfo[0]['getmoney'], 2);
            if ($order_today <= 0) {

                /*$message .= "<strong>💰收入结算:0u</strong>";
                $parameter = array(
                    'chat_id' => $chatid,
                    'parse_mode' => 'HTML',
                    'text' => $message,
                );

                $this->http_post_data('sendMessage', json_encode($parameter));
                exit();*/
                $this->xiaoxinoend(  "收入结算:0u", $chatid);
                return false;
            }


            //查看昨日总下发的记录 这里有一点需要注意，如果昨日存在有下发异常的 需要天使自己核对 手动下发：
            $zuori_sql = "select * from pay_jinrixiafa where status = '0' and pid ='" . $uid . "' and xiafatime='" . $today . "'";

            $zuorixiafa = $this->shujuku($zuori_sql);
            if ($zuorixiafa) {
                /*$parameter = array(
                    'chat_id' => $chatid,
                    'parse_mode' => 'HTML',
                    'text' => "当前商户昨日存在实时下发" . $zuorixiafa[0]['money'] . "U异常！建议手动结算昨日收益！",
                );
                $this->http_post_data('sendMessage', json_encode($parameter));
                exit();*/
                $this->xiaoxinoend("当前商户昨日存在实时下发" . $zuorixiafa[0]['money'] . "U异常！建议手动结算昨日收益！", $chatid);
                return false;
            }

            //最日下发的数据
            $zuori_money = 0.00;
            $zuori_usdt = 0.00;

            //昨日收益数据分析：
            $sql_info = "select * from pay_order where status = '1' and uid ='" . $uid . "' and date='" . $today . "'";
            $order_query3 = $this->pdo->query($sql_info);
            $zuoorderinfo = $order_query3->fetchAll();

            $all_money = 0;
            foreach ($zuoorderinfo as $key => $value) {
                $all_money += $value['money'];
                //支付方式计算
                $all_zhifu[$value['type']] += $value['money'];

                //支付方式下的各个通道跑的数据：
                $all_tongdao_zhifu[$value['type']][$value['channel']] += $value['money'];
                if (array_key_exists($value['channel'], $tongdaoxinxi)) {
                    //通道费用计算
                    $all_tongdao[$value['channel']] += $value['money'];
                }
            }
            $msg = "✅" . $todays2 . "量情况如下\r\n🆔商户号:" . $uid . "\r\n🧑🏻‍💼名字:" . $uidinfo2['username'] . "\r\n";


            if (count($all_zhifu) > 0) {
                foreach ($all_zhifu as $kt => $vt) {
                    $sql_zhifu = "select showname from pay_type where  id ='" . $kt . "'";

                    $zhifu_fetch = $this->shujuku($sql_zhifu);

                    $zhifu_info = $zhifu_fetch[0]['showname'];
                    $msg .= "🔔" . $zhifu_info . "总量:" . $vt . "\r\n";
                }

            }


            $type = substr($fufonginfo, 0, 1);
            if ($type == "-") {
                $changs = explode("-", $fufonginfo);
                $shiji_huilv = $huilvinfo - $changs[1];
            } else {
                $changs = explode("+", $fufonginfo);
                $shiji_huilv = $huilvinfo + $changs[1];
            }

            $shiji_huilv_tousu = $shiji_huilv - 0.1;


            $all_usdt_m = 0;
            $all_fusdt_money = 0;
            $xiafa_str = "";
            $feilihoujiner = 0;
            foreach ($all_tongdao_zhifu as $kv => $vv) {
                //$zhifu_info_arr[$kv]
                //$msg .= "\r\n📮" . $zhifu_info_arr[$kv] . "跑量如下：\r\n\r\n";
                foreach ($vv as $kp => $vp) {
                    $channel_sql = "select id,name from pay_channel where id='" . $kp . "'";
                    $channel_info_query = $this->shujuku($channel_sql);
                    $channel_info = $channel_info_query[0];
                    // $msg .= "(" . $channel_info['id'] . ")" . $channel_info['name'] . ":" . $vp . "\r\n";
                    if (array_key_exists($kp, $tongdaoxinxi)) {

                        $zhifu_lixi = $tongdaoxinxi[$kp];

                    } else {
                        $zhifu_lixi = $zhifuxinxi[$kv];

                    }
                    $type = substr($fufonginfo, 0, 1);

                    $jisuan = round(($vp * $zhifu_lixi * $fenchenginfo) / ($shiji_huilv), 2);
                    //$msg .= $vp . "*" . $zhifu_lixi . "*" . $fenchenginfo . "/(" . $shiji_huilv . ")=" . $jisuan . "U\r\n\r\n";

                    $xiafa_str .= $jisuan . "+";

                    $feilihoujiner += round(($vp * $zhifu_lixi * $fenchenginfo), 2);

                    $all_usdt_m += $jisuan;
                    $all_fusdt_money += $vp;
                }
            }
            $msg .= "💹总跑量:" . $all_money . "元\r\n";
            $msg .= "💹费率后总额:" . $feilihoujiner . "元\r\n\r\n";
            $msg .= "➖➖➖➖➖➖➖➖➖\r\n\r\n";
            $msg .= "不可下发金额\r\n\r\n";

            $tousu_info2 = "select * from pay_usertousu where pid ='" . $uid . "'";

            $order_tousu2 = $this->pdo->query($tousu_info2);
            $tousu_m2 = $order_tousu2->fetchAll();
            $tousu_today = 0;
            $tousu_today2 = 0;
            $tousu_U = 0;
            $jinritimne = date("Y-m-d", time());
            foreach ($tousu_m2 as $k => $v) {
                $time = date('m-d', strtotime($v['date']));
                $tousu_today += $v['money'];

                if ($v['status'] == "1") {
                    //已扣除
                    $pp = "已扣除";
                    //如果是今天扣的，要计算体现到出来：
                    if ($jinritimne == $v['koushijian']) {
                        $tousu_today2 += $v['money'];
                        $tousu_U += $v['money'];
                    }
                } else {
                    //待扣除
                    $pp = "待扣除 ---- /delete_tousu_" . $v['id'];
                    $tousu_today2 += $v['money'];
                    $tousu_U += $v['money'];

                }


                $msg .= "❌" . $time . ":投诉退款:" . $v['money'] . "元  ----" . $pp . "\r\n";
            }

            //查看投诉退款数据：
            if ($tousu_U > 0) {
                $tousu_U2 = $tousu_U;
                $msg .= "❌合计待投诉退款:" . $tousu_today2 . "元\r\n";
            } else {
                $tousu_U2 = 0;
            }

            $xiafa_str = substr($xiafa_str, 0, -1);

            $xiafa_str .= "-" . $tousu_U2;

            //查看今日下发数据记录：
            $jinri_info = "select money,jutishijian,feiu_money,feilv from pay_jinrixiafa where status='1' and pid ='" . $uid . "' and xiafatime='" . $today . "' and chatid='" . $chatid . "'";
            $order_jinri = $this->pdo->query($jinri_info);
            $tjinri_arr = $order_jinri->fetchAll();
            $all_jinri_xiafa = 0.00;


            if ($tjinri_arr) {

                $msg .= "\r\n📮" . $todays2 . "下发历史记录" . "\r\n";
                foreach ($tjinri_arr as $kj => $vj) {
                    $zuori_money += $vj['all_feiu_money'];
                    $zuori_usdt += $vj['money'];
                    $ti = date('H:i:s', $vj['jutishijian']);
                    $msg .= "🔈" . $ti . " 已下发：" . $vj['feiu_money'] . "/" . $vj['feilv'] . "/" . $vj['money'] . "\r\n";
                    $all_jinri_xiafa += $vj['feiu_money'];

                    $xiafa_str .= "-" . $vj['feiu_money'];
                }
            }
            $trx_info = "select * from pay_usertrx";
            $trx_jinri = $this->pdo->query($trx_info);
            $trx_arr = $trx_jinri->fetchAll();

            if ($trx_arr) {
                $trx_shouxufei = $trx_arr[0]['trx'];
            } else {
                $trx_shouxufei = 0.00;
            }

            $bukexiafaheji_zuoro = $all_jinri_xiafa + $tousu_today2;

            $msg .= "\r\n💹不可下发金额合计：" . $bukexiafaheji_zuoro . "元\r\n\r\n";
            $msg .= "➖➖➖➖➖➖➖➖➖\r\n";
            $msg .= "下发扣除费用\r\n\r\n";
            $msg .= "🔄Trx手续费=" . $trx_shouxufei . "U\r\n\r\n";
            $xiafa_str .= "-" . $trx_shouxufei;


            $keyixiafa_value = $feilihoujiner - $bukexiafaheji_zuoro;
            $keyixiafa_str = $feilihoujiner . " - " . $bukexiafaheji_zuoro . " = " . $keyixiafa_value;

            $msg .= "🈴当前可下发:" . $keyixiafa_str . "元";


            //实际下发：
            $shijixiafa_value = (floor((($keyixiafa_value / $shiji_huilv) * 100)) / 100) - $trx_shouxufei;
            $shijixiafa_str = $keyixiafa_value . "/" . $shiji_huilv . " - " . $trx_shouxufei . " = " . $shijixiafa_value;

            $msg .= "\r\n🈴实际下发:" . $shijixiafa_str . "U";

            $jie_all_jin_u = $all_jinri_xiafa > 0 ? $all_jinri_xiafa : 0;
            $jie_all_tou_u = $tousu_U2 > 0 ? round($tousu_U2, 2) : 0;
            $jie_all_usdt_m = round($all_usdt_m, 2);
            $keyixiafa = $jie_all_usdt_m - $jie_all_jin_u - $jie_all_tou_u - $trx_shouxufei;

            $msg .= "\r\n✅下发地址:\r\n" . $uidinfo2['usdt_str'];

            //查询结算是否已经下发：
            $sql_info_u = "select * from pay_zuorixiafau where pid ='" . $uid . "' and xiafatime='" . $today . "' and status='1'";

            $order_query_user_u = $this->pdo->query($sql_info_u);
            $xiafa_i_u = $order_query_user_u->fetchAll();
            $xiafade_day = date("d");
            //查询结算是否已经下发：
            $sql_info_u = "select * from pay_zuorixiafau where pid ='" . $uid . "' and xiafatime='" . $today . "' and status ='1'";
            $order_query_user_u = $this->pdo->query($sql_info_u);
            $xiafa_i_u = $order_query_user_u->fetchAll();


            if (!$xiafa_i_u) {
                /*$inline_keyboard_arr[0] = array('text' => "收益已清算", "callback_data" => "yijingxiafa_" . $uid);
                $inline_keyboard_arr[1] = array('text' => "查详细账单", "callback_data" => "chakanzuorixiangxi_" . $uid);*/
                //} else {
//                $inline_keyboard_arr[0] = array('text' => "下发异常!", "callback_data" => "yijingxiafa_" . $uid);
                $this->xiaoxinoend("下发异常!", $chatid);
                return false;

            }

            if ($this->kaiqi_teshu_xiafa) {
                $teshu_riqi = $this->teshu_riqi;
                $msp = "<b>" . date("m月d日", strtotime(date($teshu_riqi))) . "---成功下发" . $ubi . "U,请知悉！</b>\r\n\r\nhttps://tronscan.org/#/transaction/" . $three_result['data']['txid'];
            } else {
                $msp = "<b>" . date("m月d日", strtotime("-1 day")) . "---成功下发" . $ubi . "U,请知悉！</b>\r\n\r\nhttps://tronscan.org/#/transaction/" . $three_result['data']['txid'];
            }
            //"今日成功下发：" . $ubi . "U,请知悉:" . " " . $atyonghu
            /*$parameter = array(
                'chat_id' => $chat_id,
                'parse_mode' => 'HTML',
                'text' => $msg
            );
            $this->http_post_data('sendMessage', json_encode($parameter));
            $parameter = array(
                'chat_id' => $chat_id,
                'parse_mode' => 'HTML',
                'text' => $msp
            );
            $this->http_post_data('sendMessage', json_encode($parameter));*/

            $this->xiaoxinoend($msg, $chatid);
            $this->xiaoxinoend($msp, $chatid);
            return true;
            // exit();

        } else {
            $set_sql = "DELETE FROM pay_jinrixiafa where id='" . $insert_id . "'";
            $this->pdo->exec($set_sql);
            /*$parameter = array(
                'chat_id' => $chat_id,
                'parse_mode' => 'HTML',
                'text' => "最后环节下发失败，请联系天使客服"
            );
            $this->http_post_data('sendMessage', json_encode($parameter));*/

            $this->xiaoxinoend('最后环节下发失败，请联系天使客服', $chatid);
            return false;
        }
        return array(10, $uid, '收益已清算');
    }

}

$oen = new five();
$oen->index();

?>
