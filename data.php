<?php
// data.php

// 检查请求方法是否为 GET
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // 设置响应内容类型为 JSON
    header('Content-Type: application/json');

    // 获取用户令牌（usertoken）参数，如果不存在则默认为空字符串
    $userToken = $_GET['usertoken'] ?? '';

    // 引入数据库连接文件（假设 db.php 中有 getDBConnection() 函数）
    require 'db.php';
    $pdo = getDBConnection();

    // 如果数据库连接失败，返回 JSON 错误信息并终止脚本
    if (!$pdo) {
        echo json_encode([
            "status" => "error",
            "message" => "数据库连接失败。"
        ]);
        exit;
    }

    // 准备并执行 SQL 语句以验证用户令牌，获取用户的 uid
    $stmt = $pdo->prepare("SELECT uid FROM pay_user WHERE `key` = :usertoken LIMIT 1");
    $stmt->execute([':usertoken' => $userToken]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // 如果用户存在，获取 uid，否则返回禁止访问的错误信息
    if ($user) {
        $uid = $user['uid'];
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "禁止访问！"
        ]);
        exit;
    }

    // 获取当前时间和各个时间段的开始和结束时间
    $todayStart = date('Y-m-d 00:00:00');
    $todayEnd = date('Y-m-d 23:59:59');
    $yesterdayStart = date('Y-m-d 00:00:00', strtotime('-1 day'));
    $yesterdayEnd = date('Y-m-d 23:59:59', strtotime('-1 day'));
    $monthStart = date('Y-m-01 00:00:00'); // 本月开始
    $monthEnd = date('Y-m-t 23:59:59'); // 本月结束
    $lastMonthStart = date('Y-m-01 00:00:00', strtotime('first day of last month')); // 上月开始
    $lastMonthEnd = date('Y-m-t 23:59:59', strtotime('last day of last month')); // 上月结束

    // 定义用于查询订单统计的 SQL 语句，包含支付宝和微信的分类统计
    $orderStatsSQL = "
        SELECT 
            COUNT(*) AS total_orders,
            COALESCE(SUM(CASE WHEN status = 1 THEN money ELSE 0 END), 0) AS total_paid,
            COALESCE(SUM(CASE WHEN status = 0 THEN money ELSE 0 END), 0) AS total_unpaid,
            COALESCE(SUM(CASE WHEN status = 1 AND type = 1 THEN money ELSE 0 END), 0) AS total_alipay, -- type=1 为支付宝
            COALESCE(SUM(CASE WHEN status = 1 AND type = 2 THEN money ELSE 0 END), 0) AS total_wechat  -- type=2 为微信
        FROM pay_order
        WHERE uid = :uid AND addtime BETWEEN :start AND :end
    ";

    // 预处理订单统计的 SQL 语句
    $stmt_order = $pdo->prepare($orderStatsSQL);

    // 执行查询今日订单统计
    $stmt_order->execute([':uid' => $uid, ':start' => $todayStart, ':end' => $todayEnd]);
    $todayData = $stmt_order->fetch(PDO::FETCH_ASSOC);

    // 执行查询昨日订单统计
    $stmt_order->execute([':uid' => $uid, ':start' => $yesterdayStart, ':end' => $yesterdayEnd]);
    $yesterdayData = $stmt_order->fetch(PDO::FETCH_ASSOC);

    // 执行查询本月订单统计
    $stmt_order->execute([':uid' => $uid, ':start' => $monthStart, ':end' => $monthEnd]);
    $monthData = $stmt_order->fetch(PDO::FETCH_ASSOC);

    // 执行查询上月订单统计
    $stmt_order->execute([':uid' => $uid, ':start' => $lastMonthStart, ':end' => $lastMonthEnd]);
    $lastMonthData = $stmt_order->fetch(PDO::FETCH_ASSOC);

    // 查询总充值金额（不限时间），仅统计已付款的订单
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(money), 0) AS total_recharge FROM pay_order WHERE uid = :uid AND status = 1");
    $stmt->execute([':uid' => $uid]);
    $totalData = $stmt->fetch(PDO::FETCH_ASSOC);

    // 查询总投诉金额，统计 pay_usertousu 表中与该用户相关且未处理的投诉金额
    $stmt_tousu = $pdo->prepare("SELECT COALESCE(SUM(money), 0) AS total_complaints FROM pay_usertousu WHERE pid = :uid AND status = 0");
    $stmt_tousu->execute([':uid' => $uid]);
    $complaintsData = $stmt_tousu->fetch(PDO::FETCH_ASSOC);

    // 获取总投诉金额，如果为空则默认为 0
    $complaints = $complaintsData['total_complaints'] ?: 0;

    // 查询今日下发的金额，统计 pay_jinrixiafa 表中该用户在今日范围内已下发且状态为 1 的金额总和
    $stmt_xiafa = $pdo->prepare("SELECT COALESCE(SUM(money), 0) AS total_feiu_money FROM pay_jinrixiafa WHERE pid = :uid AND status = 1 AND jutishijian BETWEEN :start AND :end");
    $todayStart_min = strtotime($todayStart); // 将时间字符串转换为时间戳
    $todayEnd_min = strtotime($todayEnd);
    $stmt_xiafa->execute([':uid' => $uid, ':start' => $todayStart_min, ':end' => $todayEnd_min]);
    $xiafa_moneydata = $stmt_xiafa->fetch(PDO::FETCH_ASSOC);

    // 查询浮动费率，假设固定使用 pid 和 chatid 为 "99999"
    $stmt_feilv = $pdo->prepare("SELECT feilv FROM pay_userfeilv WHERE pid = :pid AND chatid = :chatid");
    $stmt_feilv->execute([':pid' => "99999", ':chatid' => "99999"]);
    $stmt_feilvData = $stmt_feilv->fetch(PDO::FETCH_ASSOC);

    // 计算下发金额与费率后的实际下发金额
    $xiafa_money = $xiafa_moneydata['total_feiu_money'] ?: 0; // 总下发金额
    if ($xiafa_money > 0 && isset($stmt_feilvData['feilv'])) {
        $xiafa_money = $xiafa_money * $stmt_feilvData['feilv']; // 应用费率
    } else {
        $xiafa_money = 0; // 如果没有下发金额或费率数据，则设为 0
    }

    // 如果查询结果为空，设置默认值以防止后续使用时出现未定义变量
    $todayData = $todayData ?: [
        'total_orders' => 0,
        'total_paid' => 0,
        'total_unpaid' => 0,
        'total_alipay' => 0,
        'total_wechat' => 0
    ];
    $yesterdayData = $yesterdayData ?: [
        'total_orders' => 0,
        'total_paid' => 0,
        'total_unpaid' => 0,
        'total_alipay' => 0,
        'total_wechat' => 0
    ];
    $monthData = $monthData ?: [
        'total_orders' => 0,
        'total_paid' => 0,
        'total_alipay' => 0,
        'total_wechat' => 0
    ];
    $lastMonthData = $lastMonthData ?: [
        'total_orders' => 0,
        'total_paid' => 0,
        'total_alipay' => 0,
        'total_wechat' => 0
    ];

    // 计算总充值金额和扣费后的到账金额
    $totalRecharge = $totalData['total_recharge'] ?: 0; // 总充值金额
    $totalReceived = $totalRecharge * 0.9; // 总到账金额（扣费率 90%）

    // 计算各时间段扣费后的实到款金额
    $todayReceived = $todayData['total_paid'] * 0.9;
    $yesterdayReceived = $yesterdayData['total_paid'] * 0.9;
    $monthReceived = $monthData['total_paid'] * 0.9;
    $lastMonthReceived = $lastMonthData['total_paid'] * 0.9;

    // 计算账户剩余可提现金额
    $withdrawals = $todayData['total_paid'] - $xiafa_money;

    // 准备并返回 JSON 格式的统计数据
    echo json_encode([
        "status" => "success",
        
        // 今日统计数据
        "today_orders" => "{$todayData['total_orders']}单", // 今日总订单
        "today_payments" => "{$todayData['total_paid']}元", // 今日付款订单金额
        "today_recharge" => "{$todayData['total_paid']}元", // 今日总充值金额
        "today_received" => round($todayReceived, 2) . "元", // 今日实到款
        "today_alipay" => "{$todayData['total_alipay']}元", // 今日支付宝金额
        "today_wechat" => "{$todayData['total_wechat']}元", // 今日微信金额

        // 昨日统计数据
        "yesterday_orders" => "{$yesterdayData['total_orders']}单", // 昨日总订单
        "yesterday_payments" => "{$yesterdayData['total_paid']}元", // 昨日付款订单金额
        "yesterday_recharge" => "{$yesterdayData['total_paid']}元", // 昨日总充值金额
        "yesterday_alipay" => "{$yesterdayData['total_alipay']}元", // 昨日支付宝金额
        "yesterday_wechat" => "{$yesterdayData['total_wechat']}元", // 昨日微信金额

        // 本月统计数据
        "month_orders" => "{$monthData['total_orders']}单", // 本月总订单
        "month_payments" => "{$monthData['total_paid']}元", // 本月付款订单金额
        "month_recharge" => "{$monthData['total_paid']}元", // 本月总充值金额
        "month_alipay" => "{$monthData['total_alipay']}元", // 本月支付宝金额
        "month_wechat" => "{$monthData['total_wechat']}元", // 本月微信金额

        // 上月统计数据
        "last_month_orders" => "{$lastMonthData['total_orders']}单", // 上月总订单
        "last_month_payments" => "{$lastMonthData['total_paid']}元", // 上月付款订单金额
        "last_month_recharge" => "{$lastMonthData['total_paid']}元", // 上月总充值金额
        "last_month_alipay" => "{$lastMonthData['total_alipay']}元", // 上月支付宝金额
        "last_month_wechat" => "{$lastMonthData['total_wechat']}元", // 上月微信金额

        // 其他统计数据
        "total_recharge" => round($totalRecharge, 2) . "元", // 总充值金额
        "complaints" => round($complaints, 2) . "元", // 总投诉金额
        'withdrawals' => round($withdrawals, 2) . "元",   // 可下发金额
        'todaypay' => round($xiafa_money, 2) . "元",      // 今日已经下发金额
    ]);
}
?>
