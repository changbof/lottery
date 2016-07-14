$(function(){
	// 加载动画
	var reg = $('#reg');
	var height = $(window).height();
	if (height > 560) {
		var ph = (height - 560) / 2;
		reg.animate({top: ph + 'px'}, 500);
	} else {
		reg.css('margin-bottom', '30px').animate({top: '30px'}, 500);
	}
	// 表单处理
	var lid = $('#lid');
	var username = $('#username');
	var password = $('#password');
	var password_repeat = $('#password_repeat');
	var qq = $('#qq');
	var submit = $('#submit');
	var error_value = $('#error_value');
	var requesting = false;
	var input_event = function(dom) {
		dom.bind('focus', function() {
			var p = $(this).parent();
			p.addClass('focus');
			if (p.hasClass('has_tip')) p.next().slideDown();
		});
		dom.bind('blur', function() {
			var p = $(this).parent();
			p.removeClass('focus');
			if (p.hasClass('has_tip')) p.next().slideUp();
		});
	};
	input_event(username);
	input_event(password);
	input_event(password_repeat);
	input_event(qq);
	document.onkeydown = function(e) {
		var ev = document.all ? window.event : e;
		if (ev.keyCode == 13) submit.trigger('click');
	}
	submit.bind('click', function() {
		var t = $(this).find('span');
		var error_tip = function(val) {
			error_value.text(val).parent().slideDown();
			setTimeout(function() {
				error_value.parent().slideUp(function() {
					t.fadeOut(function() {
						$(this).text('提交注册').fadeIn(function() {
							requesting = false;
						});
					});
				});
			}, 2000);
		};
		if (requesting) return false;
		requesting = true;
		t.fadeOut(function() {
			$(this).text('提交中...').fadeIn(function() {
				$.ajax({
					url: window.location.href,
					type: 'POST',
					cache: false,
					dataType: 'html',
					data: {lid: lid.val(), username: username.val(), password: password.val(), password_repeat: password_repeat.val(), qq: qq.val()},
					error: function() {
						error_tip('请求失败，请重试');
					},
					success: function(data, textStatus, xhr) {
						var error_message = xhr.getResponseHeader('X-Error-Message');
						if (error_message) {
							error_tip(decodeURIComponent(error_message));
						} else {
							error_value.parent().addClass('success');
							error_tip(data);
							setTimeout(function() {
								reg.fadeOut(function() {
									window.location.href = $('#login').attr('href');
								});
							}, 2000);
						}
					},
				});
			});
		});
	});
});