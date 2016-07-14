$(function(){
	// 加载动画
	var login = $('#login');
	var height = $(window).height();
	login.css('margin-bottom', '2em').animate({top: '2em'}, 500);

	// 表单处理
	var username = $('#username');
	var password = $('#password');
	var remember = $('#remember');
	var submit = $('#submit');
	var error_value = $('#error_value');
	var requesting = false;
	var input_event = function(dom) {
		dom.bind('focus', function() {
			$(this).parent().addClass('focus');
		});
		dom.bind('blur', function() {
			$(this).parent().removeClass('focus');
		});
	};
	input_event(username);
	input_event(password);
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
						$(this).text('登录').fadeIn(function() {
							requesting = false;
						});
					});
				});
			}, 2000);
		};
		if (requesting) return false;
		requesting = true;
		t.fadeOut(function() {
			$(this).text('登录中...').fadeIn(function() {
				$.ajax({
					url: window.location.href,
					type: 'POST',
					cache: false,
					dataType: 'html',
					data: {username: username.val(), password: password.val(), remember: remember.attr('checked') === 'checked' ? '1' : '0'},
					error: function() {
						error_tip('请求失败，请重试');
					},
					success: function(data, textStatus, xhr) {
						var error_message = xhr.getResponseHeader('X-Error-Message');
						if (error_message) {
							error_tip(decodeURIComponent(error_message));
						} else {
							login.fadeOut(function() {
								window.location.href = '/';
							});
						}
					},
				});
			});
		});
	});
});