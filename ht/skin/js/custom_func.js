$(document).ready(function(){
	//快速撤单
	var simDelBet_btn = $('#simDelBet_btn');
	var simDelBet_box = $('#simDelBet_box');
	var simDelBet_table;
	var simDelBet_exec;
	var simDelBet_type = simDelBet_btn.data('type');
	var simDelBet_get = '/index.php/display/sim_del_bet/' + simDelBet_type;
	var simDelBet_post = '/index.php/game/deleteCodes/' + simDelBet_type;
	var simDelBet_disable = 0;
	var error_tip = function(t) {
		simDelBet_box.hide(300, function() {
			$(this).html('');
			simDelBet_disable = 0;
			winjinAlert(t, 'err');	
		});
	};
	simDelBet_btn.bind('click', function() {
		if (simDelBet_disable === 2) {
			simDelBet_box.slideUp(300, function() {
				simDelBet_table.remove();
				simDelBet_exec.remove();
				simDelBet_disable = 0;
			});
		} else if (simDelBet_disable == 1) {
			winjinAlert('正在请求数据，请稍后...', 'err');
		} else {
			simDelBet_box.html('<div class="loading">加载中，请稍后...</div>').slideDown(300, function() {
				simDelBet_disable = 1;
				$.get(simDelBet_get, function(data, status) {
					if (status === 'success' && data) {
						simDelBet_box.fadeOut(300, function() {
							$(this).html(data).fadeIn(300, function() {
								simDelBet_disable = 2;
								simDelBet_table = $(this).find('table');
								simDelBet_exec = $(this).find('.simDelBet_exec');
								simDelBet_table.find('td.choose').bind('click', function() {
									var choosed = $(this).attr('choosed');
									if (choosed === 'false') {
										$(this).find('input').attr('checked', true);
										$(this).attr('choosed', 'true');
									} else {
										$(this).find('input').attr('checked', false);
										$(this).attr('choosed', 'false');
									}
								});
								simDelBet_table.find('td.choose_all').bind('click', function() {
									var choosed = $(this).attr('choosed');
									if (choosed === 'false') {
										simDelBet_table.find('td.choose[choosed="false"]').each(function() {
											$(this).find('input').attr('checked', true);
											$(this).attr('choosed', 'true');
										});
										$(this).attr('choosed', 'true');
									} else {
										simDelBet_table.find('td.choose[choosed="true"]').each(function() {
											$(this).find('input').attr('checked', false);
											$(this).attr('choosed', 'false');
										});
										$(this).attr('choosed', 'false');
									}
								});
								var execing = false;
								simDelBet_exec.bind('click', function() {
									if (execing) return;
									execing = true;
									var self = $(this);
									self.find('span').fadeOut(300, function() {
										$(this).text('正在撤单，请稍后...').fadeIn(300, function() {
											var exec_tip = function(t) {
												self.find('span').fadeOut(300, function() {
													$(this).text('正在撤单，请稍后...').fadeIn(300, function() {
														execing = false;
														winjinAlert(t, 'err');
													});
												});
											};
											var selected = simDelBet_table.find('td.choose[choosed="true"]');
											if (selected.length > 0) {
												var ids = [];
												selected.each(function() {
													var id = $(this).data('id');
													ids.push(parseInt(id));
												});
												$.post(simDelBet_post, {ids: ids}, function(data, status) {
													if (status === 'success') {
														if (data === '1') {
															$('html,body').animate({scrollTop: '0px'}, 500, function() {
																simDelBet_box.hide(300, function() {
																	simDelBet_table.remove();
																	simDelBet_exec.remove();
																	simDelBet_disable = 0;
																	winjinAlert('撤单成功', 'ok');
																});
															});
														} else {
															exec_tip(data);
														}
													} else {
														exec_tip('撤单异常，请刷新网页重试。');
													}
												}, 'text');
											} else {
												exec_tip('请选择您需要撤销的下注');
											}
										});
									});
								});
							});
						});
					} else {
						error_tip('请求数据失败，请重试');
					}
				}, 'html');
			});
		}
	});
	//清空号码
	$('#clear_num_func').live('click', function() {
		$('#textarea-code').val('');
		gameCalcAmount();
	});
	//iframe自适应高度
	$('iframe').load(function(){
		var mainHeight = $(this).contents().find('body').height() + 30;
		if ($(this).css('visibility') == 'hidden') {
			$(this).slideUp(function() {
				$(this).css('visibility', 'visible').height(mainHeight).slideDown();
			});
		} else {
			$(this).height(mainHeight);
		}
	});
});