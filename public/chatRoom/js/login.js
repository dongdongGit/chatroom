$(function() {
	var origin = window.location.origin,
		loginForm = $("#loginForm"),
		password = $("#passwordTxt"),
		captcha = $("#captcha"),
		captchaBtn = $("#captchaBtn"),
		captchaImg = $(".captchaImage");
	captcha.one("focus", function() {
		captchaImg.fadeIn();
	});
	console.log(origin);
	$("#captchaBtn,.captchaImage").on("click", function(e) {
		e.preventDefault();
		console.log(origin);
		captchaImg.attr("src", origin + "/chatRoom/Login/Captcha");
	});
	// boostrapValidator 验证表单
	loginForm.bootstrapValidator({
		message: '信息错误',
		feedbackIcons: { /* input状态样式图片 */
			valid: 'glyphicon glyphicon-ok',
			invalid: 'glyphicon glyphicon-remove',
			validating: 'glyphicon glyphicon-refresh'
		},
		// 验证：规则
		fields: {
			userLgnName: {
				message: 'The userLgnName is not valid',
				validators: {
					notEmpty: { // 非空验证：提示消息
						message: '用户名不能为空'
					},
					stringLength: {
						min: 6,
						max: 16,
						message: '用户名长度必须在6到16个字符之间'
					},
					regexp: {
						regexp: /^[a-zA-Z0-9_\.]+$/,
						message: '用户名由数字字母下划线和.组成'
					},
					different: {
						field: 'passwd',
						message: '用户名和密码不能相同'
					}
				}
			},
			passwd: {
				message: '密码输入出错',
				validators: {
					notEmpty: {
						message: '密码不能为空'
					},
					stringLength: {
						min: 6,
						max: 20,
						message: '密码长度不符合要求'
					},
					different: {
						field: 'userLgnName',
						message: '密码不能与用户名相同'
					},
					regexp: {
						regexp: /^[a-zA-Z0-9_\.]+$/,
						message: '密码不符合要求'
					}
				}
			},
			captcha: {
				message: '验证码格式出错',
				validators: {
					notEmpty: {
						message: '验证码不能为空'
					},
					stringLength: {
						min: 4,
						max: 4,
						message: '验证码长度错误'
					},
					regexp: {
						regexp: /^[a-zA-Z0-9]+$/,
						message: '验证码格式错误'
					},
					threshold: 4,
					remote: {
						message: '验证码错误',
						url: origin + "/chatRoom/login/captchaExists",
						delay: 1000,
						type: 'POST',
					}
				},

			},
		}
	}).on('success.form.bv', function(e) { // 点击提交之后
		// Prevent form submission
		e.preventDefault();
		// Get the form instance
		var $form = $(e.target);
		password.val($.md5(password.val()));
		// Get the BootstrapValidator instance
		var bv = $form.data('bootstrapValidator');
		var formData = $form.serialize();
		
		$.ajax({
			url: origin + "/chatRoom/Login/login",
			type: "post",
			dataType: "json",
			timeout: 10000,
			data: formData,
			success: function(result) {
				switch(result.status) {
					case '0':
						alert(result.errors);
						$form.bootstrapValidator('resetForm', true);
						captchaImg.attr("src", origin + "/chatroom/login/captcha");
						break;
					case '1':
						alert(result.msg);
						$form.bootstrapValidator('resetForm', true);
						captchaImg.attr("src", origin + "/chatroom/login/captcha");
						break;
					case '2':
						alert(result.msg);
						window.location.href = result.url;
						break;
				}
			},
			error: function(result) {
				// alert('发送失败!');
				// alert(result.msg);
				console.log(result);
			}
		});
	});
});