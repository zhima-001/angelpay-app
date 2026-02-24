<?php
// 允许上传的图片后缀
$allowedExts = array("gif", "jpeg", "jpg", "png");
$temp = explode(".", $_FILES["photo"]["name"]);
$arr['code'] = '';
$extension = end($temp);     // 获取文件后缀名
if ((($_FILES["photo"]["type"] == "image/gif")
|| ($_FILES["photo"]["type"] == "image/jpeg")
|| ($_FILES["photo"]["type"] == "image/jpg")
|| ($_FILES["photo"]["type"] == "image/pjpeg")
|| ($_FILES["photo"]["type"] == "image/x-png")
|| ($_FILES["photo"]["type"] == "image/png"))
&& ($_FILES["photo"]["size"] < 2048000)   // 小于 200 kb
&& in_array($extension, $allowedExts))
{
	if ($_FILES["photo"]["error"] > 0)
	{
		$arr['code'] = 'err';
		//echo "错误：: " . $_FILES["photo"]["error"] . "<br>";
		$arr['msg'] = "错误: " . $_FILES["photo"]["error"];
	}
	else
	{
		
		
		// 判断当期目录下的 upload 目录是否存在该文件
		// 如果没有 upload 目录，你需要创建它，upload 目录权限为 777
		if (file_exists("upload/" . $_FILES["photo"]["name"]))
		{
			//echo $_FILES["photo"]["name"] . " 文件已经存在。 ";
		}
		else
		{
			// 如果 upload 目录不存在该文件则将文件上传到 upload 目录下
			//move_uploaded_file($_FILES["photo"]["tmp_name"], "upload/" . $_FILES["photo"]["name"]);
			$wenjian = time().'.'.$extension;
			$arr['code'] = 'succ';
			move_uploaded_file($_FILES["photo"]["tmp_name"], "upload/" . $wenjian);
			$arr['wenjian'] = $wenjian;
		}
	}
}
else
{
	//echo "非法的文件格式";
	$arr['code'] = 'err';
		//echo "错误：: " . $_FILES["photo"]["error"] . "<br>";
		$arr['msg'] = "非法的文件格式";
}
echo json_encode($arr);
?>