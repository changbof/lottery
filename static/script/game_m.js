$(function() {
	// 玩法分类切换
	$('#group_list li a').live('click', lottery.group_tab);
	// 玩法切换
	$('#game-play .play .play-list a').live('click', lottery.play_tab);
	// 位数一键全选
	$('#digit_select_all').live('click', function() {
		$('#wei-shu :checkbox').attr('checked', true);
	});
	// 文本框投注号码清空
	$('#clear_num_func').live('dblclick', function() {
		$('#textarea-code').val('');
		lottery.calc_amount();
	});
	// 选号按钮点击事件
	$('#num-select input.code').live('click', function() {
		var call = $(this).attr('action');
		if (call && $.isFunction(call = lottery.select_funcs[call])) {
			call.call(this, $(this).parent());
		} else {
			if ($(this).is('.checked')) {
				$(this).removeClass('checked');
			} else {
				$(this).addClass('checked');
			}
		}
		// 重新计算总预投注数和金额
		lottery.prepare_bets();
	});
	// 操作快速选号按钮点击事件
	$('#num-select input.action').live('click', function() {
		var call = $(this).attr('action');
		var pp = $(this).closest('div.pp');
		$(this).addClass('on').siblings('.action').removeClass('on');
		if (call && $.isFunction(call = lottery.select_funcs[call])) {
			call.call(this, pp);
		} else if ($(this).is('.all')) { // 全: 全部选中
			$('input.code', pp).addClass('checked');
		} else if ($(this).is('.large')) { // 大: 选中5到9
			$('input.code.max', pp).addClass('checked');
			$('input.code.min', pp).removeClass('checked');
		} else if ($(this).is('.small')) { // 小: 选中0到4
			$('input.code.min', pp).addClass('checked');
			$('input.code.max', pp).removeClass('checked');
		} else if ($(this).is('.odd')) { // 单: 选中单数
			$('input.code.d', pp).addClass('checked');
			$('input.code.s', pp).removeClass('checked');
		} else if ($(this).is('.even')) { // 双: 选中双数
			$('input.code.s', pp).addClass('checked');
			$('input.code.d', pp).removeClass('checked');
		} else if ($(this).is('.none')) { // 清: 全不选
			$('input.code', pp).removeClass('checked');
		}
		lottery.prepare_bets();
	});
	// 点击查看选号
	$('#bets-cart td.code-list').live('click', function() {
		var data = $(this).parent().data('code');
		lottery.display_code_list(data);
	});
	// 预投注号码移除
	$('#bets-cart .del').live('click', function() {
		var $this = $(this);
		$('#bets-cart').fadeOut(function() {
			$this.closest('tr').remove();
			$(this).fadeIn(function() {
				$('#zhuiHao').val(0);
				lottery.calc_amount();
			});
		});
	});
	// 胆拖模式: 选项卡切换
	$('#num-select .dantuo :radio').live('click', function() {
		var dom = $(this).closest('.dantuo');
		if (this.value) {
			dom.next().fadeOut(function() {
				$(this).next().fadeIn();
			});
		} else {
			dom.next().next().fadeOut(function() {
				$(this).prev().fadeIn();
			});
		}
	});
	// 胆拖模式: 胆码与拖码校验
	$('#num-select .dmtm :input.code').live('click', function(event) {
		var $this = $(this);
		var $dom = $this.closest('.dmtm');
		if ($('.code.checked[value=' + this.value +']', $dom).not(this).length == 1) {
			$this.removeClass('checked');
			$.error('选择胆码不能与拖码相同');
			return false;
		}
	});
	// 快3: 二同号单选处理
	$('#num-select .zhixu115 :input.code').live('click', function(event) {
		var $this = $(this);
		if (!$this.is('.checked')) return false;
		var $dom = $this.closest('.zhixu115');
		$('.code.checked[value=' + this.value +']', $dom).removeClass('checked');
		$this.addClass('checked');
	});
	// 录入式投注录入框键盘事件
	var prepare_add_code = false;
	$('#textarea-code')
	.live('keypress', function(event) {
		event.keyCode = event.keyCode||event.charCode;
		return !!(
			// 按Ctrl、Alt、Shift时有效
			event.ctrlKey || event.altKey || event.shiftKey
			// 回车键有效
			|| event.keyCode == 13
			// 退格键有效
			|| event.keyCode == 8
			// 空格键有效
			|| event.keyCode == 32
			// 数字键有效
			|| (event.keyCode >= 48 && event.keyCode <= 57)
		);
	})
	.live('keyup', lottery.prepare_bets)
	.live('change', function() {
		var str = $(this).val();
		if (/[^\d\s\r\n]+/.test(str)) {
			$.error('投注号码仅能由非数字、空格、换行组成');
			$(this).val('');
		}
	});
	// 模式切换
	$('#play-mod .danwei').live('click', lottery.mod_tab);
	// 变更投注倍数事件处理
	$('#beishu-value').live('change', lottery.prepare_bets);
	$('#beishu-warp .sur').live('click', function() {
		var dom = $('#beishu-value');
		var new_val = parseInt(dom.val()) - 1;
		if(new_val < 1) new_val = 1;
		dom.val(new_val);
		lottery.prepare_bets();
	});
	$('#beishu-warp .add').live('click', function() {
		var dom = $('#beishu-value');
		var new_val = parseInt(dom.val()) + 1;
		dom.val(new_val);
		lottery.prepare_bets();
	});
	// 追号处理
	$('#btnZhuiHao').live('click', lottery.game_zhui_hao);
	// 追号期数选择
	var zhuihao_data_func = function() {
		var num = $('.zhuihao_box td input:checked').length;
		var amount = $('#zhuihao_amount').val();
		var total = (amount * num).toFixed(2);
		$('#zhuihao_num').text(num);
		$('#zhuihao_total').text(total);
	};
	$('.zhuihao_box td.choose_all').live('click', function() {
		$('.zhuihao_box td input:not(:checked)').attr('checked', 'checked');
		zhuihao_data_func();
	});
	$('.zhuihao_box td input').live('click', zhuihao_data_func);
	// 玩法说明
	$('#game-play .play-info .showeg').live('mouseover',function() {
		var action = $(this).attr('action');
		var ps = $(this).position();
		$('#' + action).siblings('.play-eg').hide();
		$('#' + action).css({top: ps.top + 22, left: ps.left - 7}).fadeIn();
		
	});
	$('#game-play .play-info .showeg').live('mouseout',function() {
		$('#game-play .play-info .play-eg').hide();
	});
});