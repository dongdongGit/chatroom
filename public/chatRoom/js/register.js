$(function() {
	var passwd = $("#passwd");
	var rePasswd = $("#rePasswd");
	var regForm = $("#regForm");
	// boostrapValidator 验证表单
	regForm.bootstrapValidator({
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
					threshold: 6,
					regexp: {
						regexp: /^[a-zA-Z0-9_\.]+$/,
						message: '用户名由数字字母下划线和.组成'
					},
					different: {
						field: 'passwd',
						message: '用户名和密码不能相同'
					},
					remote: {
						message: '用户名已存在',
						url: "./index.php?p=chatRoom&c=register&a=lgnExists",
						delay: 1000,
						type: 'POST',
					}
				}
			},
			user: {
				message: '昵称错误',
				validators: {
					notEmpty: {
						message: '昵称不能为空'
					},
					stringLength: {
						min: 2,
						max: 16,
						message: '昵称长度必须在2到16个字符'
					},
					regexp: {
						regexp: /^[a-zA-Z0-9_\.\S]+$/,
						message: '用户名由数字字母下划线、中文和.组成'
					},
					threshold: 2,
					remote: {
						message: '昵称已存在',
						url: "./index.php?p=chatRoom&c=register&a=nameExists",
						delay: 1000,
						type: 'POST',
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
					identical: {
						field: 'rePasswd', // 需要进行比较的input
						// name值
						message: '两次密码输入不一致'
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
			rePasswd: {
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
					identical: { // 相同
						field: 'passwd',
						message: '两次密码输入不一致'
					},
					different: { // 不同
						field: 'userLgnName',
						message: '用户名和密码不能相同'
					},
					regexp: {
						regexp: /^[a-zA-Z0-9_\.]+$/,
						message: '密码不符合要求'
					}
				}
			},
			email: {
				message: '邮箱错误',
				validators: {
					notEmpty: {
						message: '邮箱地址不能为空'
					},
					stringLength: {
						min: 2,
						max: 30,
						message: '邮箱长度不符合'
					},
					regexp: {
						regexp: /^([a-zA-Z0-9_-])+@([a-zA-Z0-9_-])+(\.[a-zA-Z0-9_-])+/,
						message: '邮箱格式错误'
					}
				}
			},
			mobliePhone: {
				message: '手机号错误',
				validators: {
					notEmpty: {
						message: '手机号码不能为空'
					},
					stringLength: {
						min: 11,
						max: 11,
						message: '请输入11位手机号码'
					},
					regexp: {
						regexp: /^1[3,4,5,7,8]\d{9}$/,
						message: '手机号码格式错误'
					},
					threshold: 11,
					remote: {
						message: '该手机号码已注册(同城**找铿哥联系方式：18367746208)',
						url: "./index.php?p=chatRoom&c=register&a=moblieExists",
						delay: 1000,
						type: 'POST',
					}
				}
			}
		}
	}).on('success.form.bv', function(e) { // 点击提交之后
		// Prevent form submission
		e.preventDefault();
		// Get the form instance
		var $form = $(e.target);
		passwd.val($.md5(passwd.val()));
		rePasswd.val($.md5(rePasswd.val()));
		// Get the BootstrapValidator instance
		var bv = $form.data('bootstrapValidator');
		var formData = $form.serialize();
		$.ajax({
			url: "./index.php?p=chatRoom&c=Register&a=register",
			type: "post",
			dataType: "json",
			timeout: 30000,
			data: formData,
			success: function(result) {
				if(result.status == '0') {
					$.each(result, function() {
						console.log(result.errors);
					});
//					window.location.href = "./register.php";
					$form.bootstrapValidator('resetForm', true);
				} else if(result.status == '1') {
					alert(result.msg);
					window.location.href = result.url;
				} else if(result.status == '2') {
					alert(result.msg);
					$form.bootstrapValidator('resetForm', true);
				} else if(result.status == '3') {
					alert(result.msg);
					$form.bootstrapValidator('resetForm', true);
				}
			},
			error: function(result) {
				alert("连接失败");
			}
		});
	});
});