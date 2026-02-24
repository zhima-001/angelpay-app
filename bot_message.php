<?php
$nosession = true;
require './includes/common.php';

// 引入机器人配置
include "cron_jiqi.php";

$tianshi_id = $_REQUEST['tianshi_id'];
$trade_no = $_REQUEST['trade_no'];
$txId = $_REQUEST['txid_url'];
$message = $_REQUEST['message'];
$chat_id = $_REQUEST['chat_id'];
$end_time = $_REQUEST['pay_at'];
$amount = $_REQUEST['amount'];

if(!empty($_REQUEST['$txId'])){

    $row = $DB->getRow("select addtime from pre_zuorixiafau WHERE id='$tianshi_id'");
    $start = strtotime($row['addtime']);
}else{
    $return_data = array(
        'code'=>0,
        'msg'=>"异常"
    );
}


// 更新订单时间
$row = $DB->exec("UPDATE pre_zuorixiafau SET txId='{$txId}',status='1' WHERE trade_no='$tianshi_id'");

// 发送机器人消息的类
class TelegramBot {
    private $token;
    private $link;

    public function __construct($token) {
        $this->token = $token;
        $this->link = 'https://api.telegram.org/bot' . $token;
    }

    /**
     * 发送消息到Telegram
     */
    public function sendMessage($chat_id, $text, $parse_mode = 'HTML') {
        $parameter = array(
            'chat_id' => $chat_id,
            'parse_mode' => $parse_mode,
            'text' => $text,
            'disable_web_page_preview' => true
        );

        return $this->http_post_data('sendMessage', json_encode($parameter));
    }

    /**
     * HTTP POST 请求
     */
    private function http_post_data($action, $data_string) {
        $url = $this->link . "/" . $action;
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json; charset=utf-8',
            'Content-Length: ' . strlen($data_string)
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $return_content = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return array(
            'http_code' => $http_code,
            'content' => $return_content
        );
    }
}

if($row) {
    // 创建机器人实例
    $bot = new TelegramBot($token);
    $msp = "<b>" . date("m月d日", strtotime("-1 day")) . "---成功下发" . $amount . "U,请知悉！</b>\r\n\r\nhttps://tronscan.org/#/transaction/" .$txId;

    // 构建消息内容
    $bot_message = "🎉 <b>下发信息</b>\n\n";
    $end_time = date("Y-m-d H:i:s",$end_time);
    $bot_message .= "⏱️ 处理时间: {$end_time} \n";
    $bot_message .= "📝 备注: {$message}\n";
    $bot_message .= "🕐 时间: " . date('Y-m-d H:i:s');

    // 发送消息到机器人
    $result = $bot->sendMessage($chat_id, $bot_message);


    // 返回成功响应
    exit('{"code":1,"msg":"付款成功","bot_message":"机器人消息发送成功","processing_time":"' . $end_time . '秒"}');
} else {
    // 创建机器人实例用于发送错误消息
    $bot = new TelegramBot($token);

    // 构建错误消息
    $error_message = "❌ <b>下发结算处理失败</b>\n\n";
    $error_message .= "📋 订单号: <code>{$trade_no}</code>\n";
    $error_message .= "📝 错误信息: 未付款或订单不存在\n";
    $error_message .= "🕐 时间: " . date('Y-m-d H:i:s');

    // 发送错误消息到机器人
    $bot->sendMessage($chat_id, $error_message);

    exit('{"code":-1,"msg":"未付款","bot_message":"机器人错误消息已发送"}');
}
?>

