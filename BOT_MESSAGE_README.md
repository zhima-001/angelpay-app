# 机器人消息发送功能

基于 `gaibian.php` 和 `five.php` 的代码，创建了能够向 Telegram 机器人发送消息的功能。

## 文件说明

### 1. `bot_message.php` - 主要功能文件
类似 `gaibian.php`，但增加了向机器人发送消息的功能。

**功能特点：**
- 更新订单处理时间
- 自动向 Telegram 机器人发送状态通知
- 支持自定义消息内容
- 支持指定接收消息的聊天ID
- 完整的错误处理和日志记录

### 2. `simple_bot_message.php` - 简化版本
只发送消息，不处理订单时间更新。

**功能特点：**
- 纯消息发送功能
- 支持 HTML 和 Markdown 格式
- 支持自定义聊天ID
- 轻量级实现

### 3. `bot_message_example.php` - 使用示例
展示如何使用上述功能的完整示例。

## 使用方法

### 基本使用

```php
// 方法1: 直接调用
$_REQUEST['trade_no'] = 'ORDER123456789';
$_REQUEST['message'] = '订单处理完成';
$_REQUEST['chat_id'] = '982124360';
$_REQUEST['start'] = 0;
$_REQUEST['end'] = time() * 1000;
include 'bot_message.php';
```

### HTTP 请求方式

```bash
# 基本使用
curl -X POST "http://your-domain.com/bot_message.php" \
  -d "trade_no=ORDER123456789&start=0&end=1640998800000"

# 带自定义消息
curl -X POST "http://your-domain.com/bot_message.php" \
  -d "trade_no=ORDER123456789&message=订单已成功处理&chat_id=982124360&start=0&end=1640998800000"
```

### JavaScript/AJAX 调用

```javascript
function sendBotMessage(tradeNo, message, chatId) {
    const params = {
        trade_no: tradeNo,
        message: message || "订单状态更新",
        chat_id: chatId || "982124360",
        start: 0,
        end: Date.now()
    };
    
    fetch("bot_message.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded",
        },
        body: new URLSearchParams(params)
    })
    .then(response => response.json())
    .then(data => {
        console.log("机器人消息发送结果:", data);
    })
    .catch(error => {
        console.error("错误:", error);
    });
}
```

## 参数说明

| 参数 | 类型 | 必填 | 说明 |
|------|------|------|------|
| trade_no | string | 是 | 订单号 |
| start | int | 否 | 开始时间（毫秒时间戳），0表示从数据库获取 |
| end | int | 是 | 结束时间（毫秒时间戳） |
| message | string | 否 | 自定义消息内容，默认为"订单状态更新通知" |
| chat_id | string | 否 | 接收消息的聊天ID，默认为老板的chat_id |
| parse_mode | string | 否 | 消息格式（仅simple_bot_message.php），HTML或Markdown |

## 返回结果

### 成功响应
```json
{
    "code": 1,
    "msg": "付款成功",
    "bot_message": "机器人消息发送成功",
    "processing_time": "10.5秒"
}
```

### 失败响应
```json
{
    "code": -1,
    "msg": "未付款",
    "bot_message": "机器人错误消息已发送"
}
```

## 消息格式

发送到机器人的消息格式：

```
🎉 订单状态更新

📋 订单号: ORDER123456789
⏱️ 处理时间: 10.5 秒
📝 备注: 订单已成功处理
🕐 时间: 2024-01-01 12:00:00
```

## 配置说明

代码会自动读取 `cron_jiqi.php` 中的配置：

- `$token` - Telegram 机器人 Token
- `$laoban_chatid` - 默认接收消息的聊天ID
- 数据库连接配置

## 日志记录

- `bot_message.php` 会记录到 `bot_message_log.txt`
- `simple_bot_message.php` 会记录到 `simple_bot_message_log.txt`

日志格式：
```
2024-01-01 12:00:00 - 订单 ORDER123456789 更新成功，处理时间: 10.5秒，机器人消息发送成功
```

## 错误处理

- 网络请求超时：30秒
- 自动重试机制（可扩展）
- 详细的错误日志记录
- 友好的错误响应

## 扩展功能

可以基于现有代码扩展以下功能：

1. **批量发送消息**
2. **消息模板系统**
3. **定时发送功能**
4. **消息队列处理**
5. **多机器人支持**

## 注意事项

1. 确保 `cron_jiqi.php` 中的 Token 配置正确
2. 确保目标聊天ID有效且有权限接收消息
3. 消息内容需要符合 Telegram 的格式要求
4. 建议在生产环境中添加更多的错误处理和重试机制

