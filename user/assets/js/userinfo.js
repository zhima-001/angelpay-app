
function invokeSettime(obj){
    var countdown=60;
    settime(obj);
    function settime(obj) {
        if (countdown == 0) {
            $(obj).attr("data-lock", "false");
            $(obj).text("获取验证码");
            countdown = 60;
            return;
        } else {
			$(obj).attr("data-lock", "true");
            $(obj).attr("disabled",true);
            $(obj).text("(" + countdown + ") s 重新发送");
            countdown--;
        }
        setTimeout(function() {
                    settime(obj) }
                ,1000)
    }
}
	$("#mmxgs").click(function(){
		$('#mmxg').modal('show');
	});
	$("#zfmmgs").click(function(){
		$('#zfmm').modal('show');
	});
var handlerEmbed = function (captchaObj,type) {
	var target;
	//极验验证
			captchaObj.onReady(function () {
				$("#wait").hide();
			}).onSuccess(function () {
				var result = captchaObj.getValidate();
				if (!result) {
					return alert('请完成验证');
				}

	//发送验证码	     
        var situation=$("#situation").val();
        var situations=$("#situations").val();
        var situationc=$("#situationc").val();
        var ii = layer.load(2, {shade:[0.1,'#fff']});
		$.ajax({
			type : "POST",
			url : "/user/jsajax/ajax2.php?act=sendcode",
			data : {type:situation,situations:situations,target:target,geetest_challenge:result.geetest_challenge,geetest_validate:result.geetest_validate,geetest_seccode:result.geetest_seccode},
			dataType : 'json',
			success : function(data) {
				layer.close(ii);
				if(data.code == 0){
				if(situationc==1){
					new invokeSettime(".sendcode");
					}else if(situationc==2){
					new invokeSettime(".sendcode2");
					}else{
					new invokeSettime(".sendcode3");
					}
					Dialog.close();
					layer.msg('发送成功，请注意查收！', {icon: 1});
				}else{
					layer.msg(data.msg, {icon: 2});
					captchaObj.reset();
				}
			} 
		});
	});

	$('.sendcode').click(function () {
		captchaObj.verify();
	});

	$('.sendcode2').click(function () {
		$("#situations").val("bind");

		if ($(this).attr("data-lock") === "true") return;
		var situation=$("#situation").val();
		if(situation=='phone'){
			target=$("input[name='phone_n']").val();
			if(target==''){layer.alert('手机号码不能为空！');return false;}
			if(target.length!=11){layer.alert('手机号码不正确！');return false;}
		}else if(situation=="email"){
			target=$("input[name='email_n']").val();
			if(target==''){layer.alert('邮箱不能为空！');return false;}
			var reg = /^([a-zA-Z0-9_-])+@([a-zA-Z0-9_-])+(.[a-zA-Z0-9_-])+/;
			if(!reg.test(target)){layer.alert('邮箱格式不正确！');return false;}
		}else{
			layer.alert("请求错误！");
		}
		captchaObj.verify();
	})


	$('#sendcode3').click(function () {
		if ($(this).attr("data-lock") === "true") return;
		target=$("input[name='phone_n']").val();
		if(target==''){layer.alert('手机号码不能为空！');return false;}
		if(target.length!=11){layer.alert('手机号码不正确！');return false;}
		captchaObj.verify();
	});
};

$(document).ready(function(){
	$("select[name='stype']").change(function(){
		if($(this).val() == 1){
			$("#typename").html("支付宝账号");
		}else if($(this).val() == 2){
			$("#typename").html("微信Openid");
		}else if($(this).val() == 3){
			$("#typename").html("QQ号");
		}else if($(this).val() == 4){
			$("#typename").html("银行卡号");
		}
	});
//收款信息修改
	$("#editSettle").click(function(){
		var stype=$("select[name='stype']").val();
		var account=$("input[name='account']").val();
		var username=$("input[name='username']").val();
		if(account=='' || username==''){layer.alert('请确保各项不能为空！');return false;}
		var ii = layer.load(2, {shade:[0.1,'#fff']});
		$.ajax({
			type : "POST",
			url : "/user/jsajax/ajax2.php?act=edit_settle",
			data : {stype:stype,account:account,username:username},
			dataType : 'json',
			success : function(data) {
				layer.close(ii);
				if(data.code == 1){
					layer.alert('修改成功！');
				}else{
					layer.alert(data.msg);
				}
			}
		});
	});
//基本信息修改
	$("#editInfo").click(function(){
		var accounts=$("select[name='accounts']").val();
		var url=$("input[name='url']").val();
		if (url.indexOf(" ")>=0){
			url = url.replace(/ /g,"");
		}
		if (url.toLowerCase().indexOf("http://")==0){
			url = url.slice(7);
		}
		if (url.toLowerCase().indexOf("https://")==0){
			url = url.slice(8);
		}
		if (url.slice(url.length-1)=="/"){
			url = url.slice(0,url.length-1);
		}
		$("input[name='url']").val(url);
		var ii = layer.load(2, {shade:[0.1,'#fff']});
		$.ajax({
			type : "POST",
			url : "/user/jsajax/ajax2.php?act=edit_info",
			data : {accounts:accounts,url:url},
			dataType : 'json',
			success : function(data) {
				layer.close(ii);
				if(data.code == 1){
					layer.alert('修改成功！');
				}else{
					layer.alert(data.msg);
				}
			}
		});
	});

	$(".checkbind").click(function(){
		$("#situationc").val("1");
		var type = $(this).attr('data-type');
		var types = $(this).attr('data-types');
				if(type == 'phone'){
					$("#situation").val("phone");
					$('#myModalphone').modal('show');
				}else if(type == 'email'){
					$("#situation").val("email");
					$('#myModalemail').modal('show');
				}else if(types=='phone'){
					$('#phonegk').show();
					$('#emailgk').hide();
					$("#situation").val("phone");
					$('#myModal2').modal('show');
				}else if(types=='email'){
					$('#emailgk').show();
				    $('#phonegk').hide();
				    $("#situation").val("email");
				    $('#myModal2').modal('show');
				}else{
					layer.alert("请求错误！");
				}
		});
	$("#editBind").click(function(){
		var situation=$("#situation").val();
		var phone=$("input[name='phone_n']").val();
		var email=$("input[name='email_n']").val();
		if(situation=='phone'){
		var type='phone';
		var email='1';
		var code=$("input[name='code_n']").val();
		}else if(situation=='email'){
		var type='email';
		var phone='1';
		var code=$("input[name='code_u']").val();
		if(email==''){layer.alert('邮箱不能为空！');return false;}
		if(email.length>0){
			var reg = /^([a-zA-Z0-9_-])+@([a-zA-Z0-9_-])+(.[a-zA-Z0-9_-])+/;
			if(!reg.test(email)){layer.alert('邮箱格式不正确！');return false;}
		}
		}else{
			layer.alert("请求错误！");
		}
		var situation=$("#situation").val();

		if(code==''){layer.alert('请输入验证码！');return false;}
		var ii = layer.load(2, {shade:[0.1,'#fff']});
		$.ajax({
			type : "POST",
			url : "/user/jsajax/ajax2.php?act=edit_bind",
			data : {type:type,phone:phone,email:email,code:code},
			dataType : 'json',
			success : function(data) {
				layer.close(ii);
				if(data.code == 1){
					layer.msg('修改绑定成功，正在跳转中...', {icon: 16,shade: 0.01,time: 15000});
					window.setTimeout(function () {
						window.location.reload()
					}, 2000);
				
				}else{
					layer.alert(data.msg);
				}
			}
		});
	});

	$(".verifycode").click(function(){
		$("#situationc").val("2");
        var type = $(this).attr('data-type');
		if(type=='email'){
			var code=$("input[name='code2']").val();
		}else if(type=='phone'){
			var code=$("input[name='code']").val();
		}else{
			layer.alert('请求错误！');return false;
		}
		var situation=$(this).attr('date-situation');
		if(code==''){layer.alert('请输入验证码！');return false;}
		var ii = layer.load(2, {shade:[0.1,'#fff']});
		$.ajax({
			type : "POST",
			url : "/user/jsajax/ajax2.php?act=verifycode",
			data : {type:type,code:code},
			dataType : 'json',
			success : function(data) {
				layer.close(ii);
				if(data.code == 1){
					layer.msg('验证成功！');
					if(type=='phone'){
						$('#phonegk').show();
						$('#emailgk').hide();
						$('#myModalphone').modal('hide');
					}else if(type=='email'){
						$('#emailgk').show();
				        $('#phonegk').hide();
						$('#myModalemail').modal('hide');
					}
					if(situation=='bind'){
						$('#myModal2').modal('show');
					}else if(situation=='mibao'){
						$("#situation").val("bind");
						$('#myModal2').modal('show');
					}else if(situation=='show'){
						$('#myModal2').modal('hide');
					}

				}else{

					layer.alert(data.msg);
				}
			}
		});
	});
	$.ajax({
		url: "/user/jsajax/ajax.php?act=captcha&t=" + (new Date()).getTime(), // 加随机数防止缓存
		type: "get",
		dataType: "json",
		success: function (data) {
			console.log(data);
			initGeetest({
				width: '100%',
				gt: data.gt,
				challenge: data.challenge,
				new_captcha: data.new_captcha,
				product: "bind", 
				offline: !data.success 
			}, handlerEmbed);
		}
	});

	var items = $("select[default]");
	for (i = 0; i < items.length; i++) {
		$(items[i]).val($(items[i]).attr("default")||1);
	}


});