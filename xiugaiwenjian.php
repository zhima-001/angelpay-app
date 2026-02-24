<?php
$filePath = $_GET['wenjianming'].".php"; 
$file_token = $_GET['file_token'];

// 读取文件内容
$content = file_get_contents($filePath);

// 替换指定的值
$newContent = str_replace('$xiaomadaoli_token', '"'.$file_token.'"', $content);

// 写回文件
file_put_contents($filePath, $newContent);

echo "修改完成";
?>
