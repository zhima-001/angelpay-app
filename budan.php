<?php 
require './includes/common.php';
$localurl =$conf['localurl'];
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <title>天使快速自助补单</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap');
        /* 新增模态对话框样式 */
        .modal {
            display: none; 
            position: fixed; 
            z-index: 1; 
            padding-top: 100px; 
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto; 
            background-color: rgb(0,0,0); 
            background-color: rgba(0,0,0,0.4); 
        }
        .modal-content {
            background-color: #fefefe;
            margin: auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 500px; 
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }
        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
        .modal img {
            width: 100%;
            height: auto;
        }
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(to right, #f8f9fa, #e9ecef);
            color: #333;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            box-sizing: border-box;
            overflow-x: hidden; /* 防止溢出显示滚动条 */
        }
        .container {
            background-color: #fff;
            border-radius: 15px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            width: 100%;
            padding: 20px;
            margin: 20px;
            box-sizing: border-box;
            overflow: hidden;
            position: relative; /* 新增 */
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            position: relative; /* 新增 */
        }
        .query-link { /* 新增 */
            position: absolute;
            top: 0;
            right: 0;
            background-color: transparent;
            color: #ff6f61;
            padding: 10px 15px;
            text-decoration: none;
            font-size: 14px;
            font-weight: bold;
            transition: color 0.3s ease;
        }
        .query-link:hover { /* 新增 */
            color: #ff4e42;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            color: #ff6f61;
        }
        .header p {
            margin: 5px 0 0 0;
            font-size: 16px;
            color: #888;
        }
        .step {
            margin-bottom: 20px;
        }
        .step h2 {
            font-size: 18px;
            margin: 0 0 10px 0;
            color: #ff6f61;
            position: relative;
        }
        .step h2::before {
            content: '';
            position: absolute;
            width: 30px;
            height: 3px;
            background-color: #ff6f61;
            bottom: -5px;
            left: 0;
        }
        .step p {
            margin: 0 0 10px 0;
            font-size: 14px;
            color: #555;
        }
        .upload-area {
            border: 2px dashed #ccc;
            padding: 20px;
            text-align: center;
            margin-bottom: 20px;
            border-radius: 8px;
            cursor: pointer;
            background-color: #f9f9f9;
            transition: background-color 0.3s ease, border-color 0.3s ease;
            position: relative;
            box-sizing: border-box;
            width: 100%;
        }
        .upload-area:hover {
            background-color: #e9ecef;
            border-color: #ff6f61;
        }
        .upload-area input {
            width: 100%;
            height: 100%;
            opacity: 0;
            position: absolute;
            top: 0;
            left: 0;
            cursor: pointer;
        }
        .upload-area img {
            display: none;
            width: 100px;
            height: auto;
            margin-top: 10px;
        }
        .upload-area::before {
            content: '点击上传图片';
            color: #888;
            font-size: 14px;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
        }
        .upload-area img.uploaded {
            display: block;
        }
        .input-area input, .input-area textarea {
            width: 100%;
            padding: 12px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
            font-size: 16px; /* 设置较大的默认字体大小 */
            transition: border-color 0.3s ease;
        }
        .input-area input:focus, .input-area textarea:focus {
            border-color: #ff6f61;
            outline: none;
            box-shadow: 0 0 8px rgba(255, 111, 97, 0.3);
        }
        .input-area textarea {
            resize: none; /* 禁用调整大小功能 */
        }
        .submit-button {
            width: 100%;
            padding: 15px;
            background-color: #ff6f61;
            color: #fff;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease, box-shadow 0.3s ease;
            position: relative; /* 新增 */
        }
        .submit-button:hover {
            background-color: #ff4e42;
            box-shadow: 0 5px 15px rgba(255, 78, 66, 0.3);
        }
        .submit-button:active {
            background-color: #ff4e42;
            box-shadow: 0 3px 10px rgba(255, 78, 66, 0.2);
        }
        .submit-button .spinner {
            display: none;
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            width: 20px;
            height: 20px;
            border: 2px solid #fff;
            border-top: 2px solid #ff4e42;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .link {
            color: #ff6f61;
            text-decoration: none;
            transition: color 0.3s ease;
        }
        .link:hover {
            color: #ff4e42;
        }
        @media (max-width: 480px) {
            .container {
                padding: 15px;
                margin: 10px;
            }
            .header h1 {
                font-size: 20px;
            }
            .header p {
                font-size: 14px;
            }
            .step h2 {
                font-size: 16px;
            }
            .step p {
                font-size: 12px;
            }
            .input-area input, .input-area textarea {
                font-size: 16px; /* 保持字体大小一致，防止放大 */
                padding: 10px;
            }
            .submit-button {
                padding: 12px;
                font-size: 14px;
            }
            .query-link { /* 调整按钮大小以适应手机屏幕 */
                padding: 5px 8px;
                font-size: 12px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <a href="<?php echo $localurl ?>/budancha.html" class="query-link">补单查询</a> <!-- 新增 -->
            <h1>快速自动补单</h1>
            <p>Quick Auto Replenishment</p>
        </div>
        <form method="post" action="<?php echo $localurl ?>/budaoapi.php" enctype="multipart/form-data" onsubmit="return handleSubmit()">
            <div class="step">
                <h2>步骤一</h2>
                <p>点击下面按钮上传一个付款截图，要求能清晰显示付款的明细，如不懂可<a class="link" href="#" onclick="xianshi()">查看示例</a></p>
                <div class="upload-area">
                    <input type="file" name="file" id="file" required onchange="previewImage();" />
                    <img id="uploadedImage" src="#" alt="图片预览" style="">
                </div>
            </div>
            <div class="step">
                <h2>步骤二</h2>
                <p>需和付款截图上的金额一致</p>
                <div class="input-area">
                    <input type="number" name="money" id="money" placeholder="请输入付款金额">
                </div>
            </div>
            <div class="step">
                <h2>步骤三</h2>
                <p>请输入该付款对应的单号（单号可在你提交订单的网站查看，或者咨询你提交订单的客服联系），如提交多个订单，且不确定是那个为真实支付成功的订单，则把所有可能和付款相关的单号都要填写到下方输入框，一行一个单号！</p>
                <div class="input-area">
                    <textarea name="ordersn" id="ordersn" rows="4" placeholder="一行一条单号，例如：209893902091&#10;209893902082&#10;209893902042"></textarea>
                </div>
            </div>
            <button class="submit-button" type="submit" value="提交补单">提交
                <div class="spinner" id="spinner"></div>
            </button>
        </form>
    </div>
     <!-- 模态对话框 -->
    <div id="myModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <img src="./photo_2024-06-08_16-26-35.jpg" alt="示例图片">
        </div>
    </div>
    <script>
        // 强制刷新网页
        window.onload = function() {
            if (!window.location.search.includes('refreshed')) {
                window.location.href = window.location.href.split('?')[0] + '?refreshed=' + new Date().getTime();
            }
        }

        function xianshi() {
            var modal = document.getElementById("myModal");
            modal.style.display = "block";
        }

        var modal = document.getElementById("myModal");
        var span = document.getElementsByClassName("close")[0];

        span.onclick = function() {
            modal.style.display = "none";
        }

        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }

        function previewImage() {
            var preview = document.getElementById('uploadedImage');
            var file = document.getElementById('file').files[0];
            var reader = new FileReader();
            reader.onloadend = function () {
                preview.src = reader.result;
                preview.style.display = 'block';
            };
            if (file) {
                reader.readAsDataURL(file); // 读取文件内容
            } else {
                preview.src = "";
                preview.style.display = 'none';
            }
        }

        function handleSubmit() {
            var button = document.querySelector('.submit-button');
            var spinner = document.getElementById('spinner');
            button.disabled = true;
            spinner.style.display = 'block';
            return true;
        }
    </script>
</body>
</html>
