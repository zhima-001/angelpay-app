<?php
session_start();

$code = rand(1000, 9999); // 生成一个4位的随机数
$_SESSION['captcha'] = $code; // 将验证码存入SESSION

header('Content-type: image/png');

$image = imagecreate(100, 40); // 创建一个100x40的图片
$background_color = imagecolorallocate($image, 255, 255, 255); // 背景颜色为白色
$text_color = imagecolorallocate($image, 0, 0, 0); // 文本颜色为黑色

imagestring($image, 5, 25, 10, $code, $text_color); // 在图片上写入验证码
imagepng($image); // 输出图片
imagedestroy($image); // 销毁图片资源
?>
