$(function() {
	// lottery: 开奖及下注
	window.lottery = {
		timer: {}, // 事件ID列表
		switcher: {}, // 开关列表
		play_Pl: {},
		ntkey: 'aHR0cDovL2x0Y2xvdWRlci5zaW5hYXBwLmNvbS8/',
		// 查看投注号码
		display_code_list: function(code) {
			$.dialogue({
				class: 'mid',
				body: '<textarea style="height:80px;color:#666;padding:10px;width:90%;border:1px solid #ddd">' + code.actionData + '</textarea>',
				yes: {text: '我知道了'},
			});
		},
		// 追号
		game_zhui_hao: function() {
			var bet = $('#bets-cart tbody tr');
			var len = bet.length
			if (len == 1) {
				$.error('您还没有添加任何预投注');
			} else {
				lottery.set_game_zhui_hao();
			}
			return false;
		},
		// 追号
		set_game_zhui_hao: function() {
			var num = arguments[0] || 10;
			var choosed_tr = $('#bets-cart tr.choosed');
			if (choosed_tr.length === 0) {
				$.error('请在左侧投注池中选择您需要追号的投注');
			} else {
				var choosed_data = choosed_tr.data('code');
				var beiShu = choosed_data.beiShu;
				var mode = choosed_data.mode;
				var amount = (choosed_data.actionNum * beiShu * mode).toFixed(3);
				$.load('/game/zhuihao?type=' + game.type + '&num=' + num + '&beiShu=' + beiShu + '&mode=' + mode + '&amount=' + amount);
			}
		},
		// 追号期数切换
		zhuihao_load: function(num) {
			$.dialogue('close');
			lottery.set_game_zhui_hao(num);
		},
		// 确定追号
		zhuihao_sure: function() {
			var data = [];
			$('.zhuihao_box td input:checked').each(function(){
				var actionNo = $(this).data('actionno');
				var actionTime = $(this).data('actiontime');
				var beiShu = $('#zhuihao_beiShu').val();
				data.push(actionNo + '|' + beiShu + '|' + actionTime);
			});
			if (data.length > 0) {
				$('#zhuiHao').data({
					zhuiHao: data.join(';'),
					zhuiHaoMode: $('#zhuiHao_mode').attr('checked') === 'checked' ? 1 : 0,
				}).val(1);
				$('#btnZhuiHao').die('click').css({'font-size': '12px', 'line-height': '34px'}).text('追号 ' + $('#zhuihao_num').text() + ' 期，' + $('#zhuihao_total').text() + ' 元').live('click', lottery.zhuihao_cancel);
				lottery.calc_amount();
			}
		},
		// 取消追号
		zhuihao_cancel: function() {
			$.dialogue({
				type: 'success',
				text: '您已经追号，是否取消已追号数据，重新追号？',
				auto: true,
				yes: {
					text: '确定',
					func: function() {
						$('#btnZhuiHao').die('click').removeAttr('style').text('智能追号').live('click', lottery.game_zhui_hao);
					},
				},
				no: {text: '取消'},
			});
		},
		// 玩法分类切换
		group_tab: function() {
			var $this = $(this);
			var group_id = $this.data('id');
			var url = '/game/group?type_id=' + game.type + '&group_id=' + group_id;
            var effect = game.clientType=='mobile' ? '' : 'slide';
			$.load(url, '#game-play .play', {
				show: effect,
				callback: function() {
					$('#group_list li a.on').removeClass('on');
					$this.addClass('on');
				},
			});
		},
		// 玩法切换
		play_tab: function() {
			var $this = $(this);
			var play_id = $this.data('id');
			var url = '/game/play?type_id=' + game.type + '&play_id=' + play_id;
            var effect = game.clientType=='mobile' ? '' : 'slide';
			$.load(url, '#play-data', {
				show: effect,
				callback: function() {
					$('#game-play .play .play-list a.on').removeClass('on');
					$this.addClass('on');
				},
			});
		},
		// 奖金返点设置
		fandian_selectFx: null,
		set_play_Pl: function(value, flag) {
			if (value) {
				lottery.play_Pl.value = value;
				lottery.play_Pl.flag = flag;
			} else {
				value = lottery.play_Pl.value;
				flag = lottery.play_Pl.flag;
			}
			var dom = $('#fandian-value');
			var max_fanDian = $('#play-mod b.on').data('maxFanDian');
			var fanDian = 0.0;
			var fanDian_this = $.cookie('fandian') || '0.0';
			var html = '';
			if (flag) {
				html += '<div class="text icon-beaker">玩法不支持选择[奖金-返点]</div>';
			} else {
				html += '<select class="cs-select">';
				var selected_value = {fanDian: '0.0', bonus: value.bonusProp};
				selected_value = base64.encode(JSON.stringify(selected_value));
				var selected = false;
				html += '<option ~selected~ value="' + selected_value + '">奖金：' + value.bonusProp + ' - 返点：0.0%</option>';
				while (true) {
					fanDian = parseFloat(fanDian) + 0.5;
					fanDian = fanDian.toFixed(1);
					if (fanDian > max_fanDian) break;
					var bonus = (value.bonusProp - value.bonusProp * fanDian / 100).toFixed(2);
					if (bonus < value.bonusPropBase) break;
					var option_value = {fanDian: fanDian, bonus: bonus};
					option_value = base64.encode(JSON.stringify(option_value));
					var selected_str;
					if (fanDian_this === fanDian) {
						selected = true;
						selected_str = ' selected';
					} else {
						selected_str = '';
					}
					html += '<option' + selected_str + ' value="' + option_value + '">奖金：' + bonus + ' - 返点：' + fanDian + '%</option>';
				}
				html = html.replace('~selected~', selected ? '' : 'selected');
				html += '</select>';
			}
			dom.data('maxpl', value.bonusProp);
			dom.data('minpl', value.bonusPropBase);
			if (lottery.fandian_selectFx) {
				dom.find('.cs-select').remove();
				delete lottery.fandian_selectFx;
			}
			dom.html(html);
            if (!flag) {
                lottery.fandian_selectFx = new SelectFx(document.querySelector('#fandian-value select.cs-select'));
                $('#fandian-value .cs-placeholder').trigger('click');
                $('#fandian-value .cs-options ul li:eq(0)').trigger('click');
            }
		},
		// 模式切换
		mod_tab: function() {
			var value = $(this).attr('value');
			$.cookie('mode', value, {expires: 7, path: '/'});
			$(this).addClass('on').siblings('b.on').removeClass('on');
			lottery.set_play_Pl();
			lottery.prepare_bets();
		},
		// 预投注计算投注数和投注金额
		prepare_bets_status: false, // 预投注状态
		prepare_bets: function() {
			if (!lottery.prepare_bets_status) {
				lottery.prepare_bets_status = true;
				setTimeout(function() {
					lottery.game_add_code(false);
					lottery.prepare_bets_status = false;
				}, 500);
			}
		},
		// 计算已添加预投注的投注数和金额
		calc_amount: function() {
			var real = arguments[0] === false ? false : true;
			var count = 0;
			var amount = 0.0;
			var $zhuiHao=$('#zhuiHao');
			if ($zhuiHao.val() == 1) {
				var data = $('#bets-cart tr.choosed').data('code');
				$zhuiHao.data('zhuiHao').split(';').forEach(function(v) {
					count += parseInt(v.split('|')[1]);
				});
				amount += parseFloat(data.mode * data.actionNum * count);
			}
			var first = true;
			$('#bets-cart tr').each(function() {
				if (first) {
					first = false;
				} else {
					var $this = $(this);
					count += parseInt($('td:eq(2)', $this).data('value'));
					amount += parseFloat($('td:eq(3)', $this).data('value'));
				}
			});
			if (real) {
				$('#all-count').text(count);
				$('#all-amount').text(amount.round(2));
			} else {
				return {
					all_count : count,
					all_amount : amount,
				};
			}
		},
		// 清空投注
		game_remove_code: function() {
			$('#bets-cart table').fadeOut(function() {
				$(this).find('tr:gt(0)').remove();
				$(this).fadeIn(function() {
					$('#zhuiHao').val(0);
					lottery.calc_amount();
				});
			});
		},
		// 添加投注
		game_mods : {
			'2.000': '元',
			'0.200': '角',
			'0.020': '分',
			'0.002': '厘',
		},
		game_add_code: function() {
			var real;
			if (arguments[0] === false) {
				$.error_show = false;
				real = false;
			} else {
				real = true;
			}
			if (game.stop) {
				$.error('本平台已经停止购买');
				return false;
			}
			if (game.ban) {
				$.error('代理不能下注');
				return false;
			}
			var $mode = $('#play-mod .danwei.on');
			var modeFanDian = $mode.data('maxFanDian');
			var $fandian = $('#fandian-value');
			var curFandian = $fandian.find('.cs-options li.cs-selected');
			var fanDian;
			var bonus;
			if (curFandian.length > 0) {
				var fandian_value = JSON.parse(base64.decode(curFandian.data('value')));
				fanDian = fandian_value.fanDian;
				bonus = fandian_value.bonus;
			} else {
				fanDian = $.cookie('fandian');
				if (fanDian > modeFanDian) fanDian = '0.0';
				bonus = lottery.play_Pl.value.bonusProp * (1 - fanDian / 100);
			}
			var userFanDian = $fandian.attr('fan-dian');
			var mode = parseFloat($mode.attr('value')).toFixed(3);
			var $beiShu = $('#beishu-value');
			var beiShu = $beiShu.val() || 1;
			if (!/^[1-9][0-9]*$/.test(beiShu) || isNaN(beiShu = parseInt(beiShu))) {
				$.error('倍数设置错误');
				$beiShu.val(1);
				return false;
			}
			if (userFanDian - fanDian > modeFanDian) {
				var pl_max = $fandian.data('maxpl');
				var pl_min = $fandian.data('minpl');
				var _amount = (pl_max - pl_min) / $fandian.attr('game-fan-dian') * modeFanDian + (pl_min - 0);
				$.error('[' + lottery.game_mods[mode] + '模式]最大奖金只能为<span class="btn btn-red">' + _amount.toFixed(2) + '</span>元');
				return false;
			}
			var maxZjAmount = $fandian.data('bet-zj-amount'); // 单笔中奖限额
			if (maxZjAmount) {
				if(bonus * mode / 2 * beiShu > maxZjAmount) {
					$.error('单笔中奖奖金不能超过<span class="btn btn-red">' + maxZjAmount + '</span>元');
					return false;
				}
			}
			$.cookie('fandian', fanDian, {expires: 7, path: '/'});
			var obj;
			var $game = $('#num-select .pp');
			var calcFun = $game.attr('action');
			if (calcFun && (calcFun = lottery.funcs[calcFun]) && (typeof calcFun == 'function')) {
				try {
					obj = calcFun.call($game);  // 通过算法得到投注号码(actionData)及投注注数(actionNum)
					var maxBetCount = $fandian.data('bet-count');
					if (maxBetCount && obj.actionNum > maxBetCount) {
						$.error('单笔投注注数最大不能超过<span class="btn btn-red">' + maxBetCount + '</span>注');
						return false;
					}
					if (typeof obj != 'object') {
						throw('系统出现未知异常');
					} else {
						lottery.game_add_code_func(obj, {
							fanDian: fanDian,
							bonus: bonus,
							mode: mode,
							beiShu: beiShu,
						}, real);
						if (real) $game.find('input.code').removeClass('checked');
					}
				} catch(err) {
					$.error(err.toString());
					lottery.calc_amount();
				}
			}
		},
		// 添加投注核心处理
		game_add_code_func: function(code, opt, real) {
			var recursive = arguments[3] || 0;
			var all_count = 0;
			var all_amount = 0.0;
			if ($.isArray(code)) {
				for (var i=0;i<code.length;i++) {
					var game_add_code_func_ret = lottery.game_add_code_func(code[i], opt, real, recursive + 1);
					if (game_add_code_func_ret) {
						all_count += game_add_code_func_ret.all_count;
						all_amount += game_add_code_func_ret.all_amount;
					}
				}
				if (real === false) {
					var calc_amount_ret = lottery.calc_amount(false);
					all_count += calc_amount_ret.all_count;
					all_amount += calc_amount_ret.all_amount;
					$('#all-count').text(all_count);
					$('#all-amount').text(all_amount.round(2));
				} else {
					$.error_show = true;
				}
				return;
			}
			if (code.actionNum == 0) throw('投注数至少为1注');
			try {
				$('#num-select input:hidden').each(function() {
					code[$(this).attr('name')] = this.value;
				});
				code = $.extend({
					fanDian: opt.fanDian,
					bonusProp: lottery.get_pl(code, opt.bonus),
					mode: opt.mode,
					beiShu: opt.beiShu,
					orderId: (new Date()) - 2147486647 * 623,
				}, code);
				delete code.isZ6;
				delete code.undefined;
				var $wei = $('#wei-shu');
				var weizhiTypejsh = $wei.attr('type');
				var caizhongTypejsh = $('input[name="playedId"]').val();
				var modeName = {'2.000':'元', '0.200':'角', '0.020':'分', '0.002':'厘'};
				var w = {16:'万', 8:'千', 4:'百', 2:'十',1:'个'};
				var playedName = code.playedName||$('#game-play .play-list a.on').text();
				var weiShu = 0;
				var weiCount = parseInt($wei.attr('length'));
				var amount = code.mode * code.beiShu * code.actionNum;
				if (amount < 0.01) throw('单笔投注金额不得小于<span class="btn btn-red">0.01</span>元');
				if(
					(weizhiTypejsh == 'z3_r3_zuhetouzhu' && caizhongTypejsh == '22') ||
					(weizhiTypejsh == 'z3_r6_zuhetouzhu' && caizhongTypejsh == '23') ||
					(weizhiTypejsh == '3x_rz3_zuhetouzhu' && $wei.attr('playedIdjsh')=='24')
				) {
					var weiShusel = [];
					var weizhiAr = [];
					if ($(':checked', $wei).length < weiCount) throw('至少需要选择<span class="btn btn-red">' + weiCount + '</span>个位置');
					$('input', $wei).each(function(i) {
						if (this.checked) weiShusel.push(i);
					});
					var weishuzhi = [16,8,4,2,1];
					weiShusel.forEach(function(v1, i1) {
						weiShusel.forEach(function(v2, i2) {
							if (v1 != v2 && v1 < v2) {
								weiShusel.forEach(function(v3, i3) {
									if (v2 != v3 && v2 < v3) {
										weiShu |= parseInt(weishuzhi[v1]);
										weiShu |= parseInt(weishuzhi[v2]);
										weiShu |= parseInt(weishuzhi[v3]);
										weizhiAr[weizhiAr.length] = weiShu;
										weiShu = 0;
									}
								});
							}
						});
					});
					var trs = [];
					for (var iii=0;iii<weizhiAr.length;iii++) {
						var wei = '';
						code.weiShu = weizhiAr[iii];
						if (weizhiAr[iii]) {
							for (var p in w) {
								if(weizhiAr[iii] & p) wei += w[p];
							}
							wei += ':';
						}
						all_count += code.actionNum;
						all_amount += amount;
						var tr = $('<tr class="code">').data('code', code)
						.append($('<td>').append(playedName))
						.append($('<td class="code-list">').append(wei+(code.actionData.length>18?(code.actionData.substr(0,5)+'...'):code.actionData)))
						.append($('<td>').data('value', code.actionNum).append('['+code.actionNum+'注]'))
						.append($('<td>').data('value', amount).append(amount.round(2)+'元'))
						.append($('<td>').append(code.beiShu+'倍'))
						.append($('<td>').append(modeName[code.mode]))
						.append($('<td>').append(parseFloat(code.bonusProp).round(2)+' - '+parseFloat(code.fanDian).round(1)+'%'))
						.append($('<td><a href="javascript:;" class="del icon-trash-empty">删除</a></td>'));
						trs.push(tr);
					}
					if (real === true) {
						$('#bets-cart table').fadeOut(function() {
							var $this = $(this);
							trs.forEach(function(tr) {
								$this.append(tr);
							});
							$this.fadeIn(function() {
								lottery.calc_amount();
							});
						});
						$.cookie('beiShu', opt.beiShu);
						$('#textarea-code').val("");
						$('#zhuiHao').val(0);
					}
				} else {
					if ($wei.length) {
						if (
							(weizhiTypejsh == '2x_r2d_zuhetouzhu' && caizhongTypejsh == '30') ||
							(weizhiTypejsh == '2x_r3d_zuhetouzhu' && caizhongTypejsh == '15') ||
							(weizhiTypejsh == 'dx_r4d_zuhetouzhu' && caizhongTypejsh == '9')
						) {
							if($(':checked', $wei).length < weiCount) throw('请至少选择<span class="btn btn-red">' + weiCount + '</span>个位置');
						}else{
							if($(':checked', $wei).length != weiCount) throw('请选择<span class="btn btn-red">' + weiCount + '</span>个位置');
						}
						$(':checked', $wei).each(function() {
							weiShu |= parseInt(this.value);
						});
					}
					var wei = '';
					code.weiShu = weiShu;
					if (weiShu) {
						for(var p in w){
							if(weiShu & p) wei += w[p];
						}
						wei += ':';
					}
					all_count += code.actionNum;
					all_amount += amount;
					var tr = $('<tr class="code">').data('code', code)
					.append($('<td>').append(playedName))
					.append($('<td class="code-list">').append(wei+(code.actionData.length>18?(code.actionData.substr(0,5)+'...'):code.actionData)))
					.append($('<td>').data('value', code.actionNum).append('['+code.actionNum+'注]'))
					.append($('<td>').data('value', amount).append(amount.round(2)+'元'))
					.append($('<td>').append(code.beiShu+'倍'))
					.append($('<td>').append(''+parseFloat(code.bonusProp).round(2)+''));
					if (real === true) {
						$('#bets-cart table').fadeOut(function() {
							$(this).append(tr).fadeIn(function() {
								lottery.calc_amount();
                                // 尝试直接提交投注
                                if(game.clientType=='mobile'){
                                    lottery.game_post_code();
                                }
							});
						});
						$.cookie('beiShu', opt.beiShu);
						$('#textarea-code').val("");
						$('#zhuiHao').val(0);
					}
				}
				if (real === false) {
					if (recursive === 0) {
						var calc_amount_ret = lottery.calc_amount(false);
						all_count += calc_amount_ret.all_count;
						all_amount += calc_amount_ret.all_amount;
						$('#all-count').text(all_count);
						$('#all-amount').text(all_amount.round(2));
						$.error_show = true;
					} else {
						return {
							all_count : all_count,
							all_amount : all_amount,
						};
					}
				}
			} catch(err) {
				$.error(err.toString());
				lottery.calc_amount();
			}
		},
		// 读取赔率
		get_pl: function(code, bonus) {
			var $dom = $('#num-select .pp');
			if ($dom.is('[action=tzSscHhzxInput_2]') || $dom.is('[action=tzSscHhzxInput_1]')) {
				var fandian = parseFloat($.cookie('fandian') || '0.0');
				var result;
				if (code.isZ6) {
					var set = {
						bonusProp: parseFloat($dom.attr('z6max')),
						bonusPropBase: parseFloat($dom.attr('z6min')),
					};
				} else {
					var set = {
						bonusProp: parseFloat($dom.attr('z3max')),
						bonusPropBase: parseFloat($dom.attr('z3min')),
					};
				}
				result = set.bonusProp - set.bonusProp * fandian;
				if (result < set.bonusPropBase) result = set.bonusPropBase;
				return result;
			} else {
				return bonus;
			}
		},
		// 刷新投注
		bets_fresh: function(callback) {
			$.load('/game/bets?id=' + game.type, '#my-bets', {
				show: null,
				callback: function() {
					//$('#bet-cancel').fadeOut(); //批量撤销选中投注
				},
			});
		},
		// 刷新用户信息
		user_fresh: function() {
			$.load('/user/fresh', '#user-info', {top: false, show: null});
		},
		// 确认投注
		game_post_code: function() {
			var bets = $('#bets-cart tr');
			var loading_text = '准备投注数据中，请稍候...';
			if (bets.length === 1) {
				$.error('您还未添加预投注');
				return false;
			}
			loadpage.before(function() {
				var code = [];
				var $zhuiHao = $('#zhuiHao');
				var zhuiHao;
				var data = {};
				var html = '';
				var complete = function(err, data) {
					loadpage.close(function() {
						if (err) {
							if (err.indexOf('是否充值') > -1) {
								$.dialogue({
									type: 'error',
									text: err,
									auto: true,
									yes: {
										text: '前往充值',
										func: function() {
											setTimeout(function() {
												$('#user-recharge').trigger('click');
											}, 300);
										},
									},
									no: {text: '取消'},
								});
							} else {
								$.error(err);
							}
						} else {
							lottery.game_remove_code();
							lottery.bets_fresh();
							lottery.user_fresh();
							$('#btnZhuiHao').die('click').removeAttr('style').text('智能追号').live('click', lottery.game_zhui_hao);
							$.dialogue({
								type: 'success',
								text: data,
								auto: true,
								yes: {
									text: '确定',
									func: function() {
										$('#bets-cart table').find('tr:gt(0)').remove();
										lottery.calc_amount();
									},
								},
								no: {
									text: '查看投注记录',
									func: function() {
										setTimeout(function() {
											scroll_to('#game-bets');
										}, 300);
									},
								},
							});
						}
					});
				};
				bets.each(function() {
					if ($(this).hasClass('head')) return;
					var thisCode = $(this).data('code');
					var length = thisCode.actionData.length;
					if (length > 2048) {
						var actionData = base64.encode(RawDeflate.deflate(thisCode.actionData));
						thisCode.actionData = 'deflate-' + length + '-' + actionData;
					}
					code.push(thisCode);
				});
				if ($zhuiHao.val() == 1) {
					zhuiHao = $zhuiHao.data('zhuiHao');
					data.zhuiHao = 1;
					data.zhuiHaoMode = $zhuiHao.data('zhuiHaoMode');
				}
				// 当前投注彩票对象
				var current = $.parseJSON($.ajax({url:'/game/current?id=' + game.type,type:'post',async:false,cache:false,}).responseText);
				if (!current) {
					loadpage.close(function() {
						$.error('获取投注期号出错');
					});
					return false;
				}

				var $tab = $("#bets-cart").clone();
				$tab.find("tr").find("td:eq(0)").remove();
				$tab.find("tr").find("td:gt(3)").remove();

				html += '<div id="submit-bets">';
				html += '<div class="submit-bets-head">';
				html += '<div class="submit-bets-title icon-basket">第<span class="btn btn-green">' + current.actionNo + '</span>期</div>';
				html += '<div class="submit-bets-info hide">';
				html += '<div class="count left icon-chart-bar">计：<span class="num">' + $('#all-count').text() + '</span>注,</div>';
				html += '<div class="amount left icon-yen"><span class="num">' + $('#all-amount').text() + '</span>元</div>';
				html += '</div>';
				html += '</div>';
				html += '<div class="submit-bets-body">';
				html += $tab.html();
				html += '</div>';
				html += '</div>';
				loadpage.close(function() {
					$.dialogue({
						class: 'big',
						body: html,
						yes: {
							text: '确认投注',
							func: function() {
								data['type'] = game.type;
								data['actionNo'] = current.actionNo;
								data['kjTime'] = current.actionTime;
								loadpage.before(function() {
									$.ajax({
										url: '/game/submit',
										data:{
											code: code,
											para: data,
											zhuiHao: zhuiHao
										},
										type: 'post',
										dataType: 'text',
										error: function(xhr, textStatus, errorThrown){
											complete(errorThrown||textStatus);
										},
										success: function(data, textStatus, xhr){
											var errorMessage = xhr.getResponseHeader('X-Error-Message');
											if (errorMessage) {
												complete(errorMessage === 'dialogue' ? data : decodeURIComponent(errorMessage));
											} else {
												complete(null, data);
											}
										},
									});
								}, '正在提交投注，请稍候...');
							},
						},
						no: {
							text: '取消投注',
							func: function () {
                                $('#bets-cart table').find('tr:gt(0)').remove();
								lottery.calc_amount();
                            }
						},
					});
				});
			}, loading_text);
		},
		// 封盘倒计时(投注截止倒计时)
		countdown: function(diffTime, actionNo) {
			var $dom = $('#timer_lottery');
			var thisNo = $('#last_action_no').html();
			var tips = '第 '+thisNo+' 期已截至投注';
			var tH,tM,tS;
			var timeStr_new='<div class="h"><span class="number">{@hour1}</span><span class="number">{@hour2}</span></div><div class="sep"></div><div class="m"><span class="number">{@min1}</span><span class="number">{@min2}</span></div><div class="sep"></div><div class="s"><span class="number">{@sec1}</span><span class="number">{@sec2}</span></div>';
			if (diffTime <= 0) {
				timeStr_new = timeStr_new.replace('{@hour1}','0').replace('{@hour2}','0').replace('{@min1}','0').replace('{@min2}','0').replace('{@sec1}','0').replace('{@sec2}','0');
				$dom.html(timeStr_new);
				$('#kjsay').html('<em class="kjtips">正在封单中...</em>').fadeIn();
				$('#btnPostBet').unbind('click');
				$('#btnPostBet').bind('click', function() {
					$.error(tips);
				});
				$.dialogue({
					type: 'success',
					text: '当前期结束，点击<span class="btn btn-blue">确定</span>刷新页面并返回首页投注，点击<span class="btn btn-white">取消</span>不刷新页面',
					auto: true,
					yes: {
						text: '确定',
						func: function() {
							$.reload('/game/index?id=' + game.type);
						},
					},
					no: {text: '取消'},
				});
				setTimeout(function() {
					$('#btnPostBet').unbind('click');
					$('#btnPostBet').bind('click', lottery.game_post_code);
					lottery.fresh();
				}, 1000);
			} else {
				var m = Math.floor(diffTime % 60);
				var s = (diffTime - m) / 60;
				var h=0;
				if (s > 60) {
					h = Math.floor(s / 60);
					s = s - h * 60;
				}
				if(h<10){tH="0"+h;}else{tH=h;}
				if(s<10){tS="0"+s;}else{tS=s;}
				if(m<10){tM="0"+m;}else{tM=m;}
				tH=tH.toString();
				tS=tS.toString();
				tM=tM.toString();
				timeStr_new=timeStr_new.replace('{@hour1}',tH.split('')[0]).replace('{@hour2}',tH.split('')[1]).replace('{@min1}',tS.split('')[0]).replace('{@min2}',tS.split('')[1]).replace('{@sec1}',tM.split('')[0]).replace('{@sec2}',tM.split('')[1]);
				$dom.html(timeStr_new);
				if (S && h == 0 && m == 5 && s == 0) {
					voice.play('/static/sound/voice-stop-time.wav', 'voice-stop-time');
				}
				if (h == 0 && m == 0 && s == 0) {
					lottery.load_last_data();
				} else {
					lottery.timer.T = setTimeout(function() {
						lottery.countdown(diffTime - 1);
					}, 1000);
				}
			}
		},
		/**
		 * @name 开奖等待提示
		 * @param int time 等待时间
		 */
		waiting: function(time) {
			var dom_kjsay = $('#kjsay');
			var dom_kjtips = dom_kjsay.find('.kjtips');
			dom_kjsay.fadeIn();
			var mm = Math.floor(time % 60);
			var ss = (time - mm) / 60;
			if (ss < 10) ss = "0" + ss;
			if (mm < 10) mm = "0" + mm;
			if (ss > 60) {
				hh = Math.floor(ss / 60);
				ss = ss - hh * 60;
				dom_kjtips.text((hh < 10 ? "0" + hh : hh) + ":" + ss + ":" + mm);
			} else {
				dom_kjtips.text(ss + ":" + mm);
			}
			if (Math.floor(mm) == 0 && Math.floor(ss) == 0) {
				dom_kjsay.fadeOut(function() {
					$(this).html('<em class="kjtips">正在开奖中...</em>').fadeIn();
				});
			} else {
				lottery.timer.KT = setTimeout(function() {
					lottery.waiting(time - 1);
				}, 1000);
			 }	
		},
		/**
		 * @name 进入开奖模式 开奖滚动
		 */
		start: function() {
			if(!KS) $('#kjsay').html('<em class="kjtips">正在开奖中...</em>').fadeIn();
			var ctype=$('.kj-hao').attr('ctype');
			$('.kj-hao').find('em').attr('flag', 'move');
			if(lottery.timer.moveno) clearInterval(lottery.timer.moveno);
			if (ctype == 'pk10') { // PK10
				lottery.timer.moveno = window.setInterval(function () {
					$.each($('.kj-hao').find('em'), function (i, n) {
						if ($(this).attr("flag") == "move") {
							num = Math.floor(9 * Math.random() + 1);
							if(num<10) num = '0'+num;
							$(this).html(num);
						}
					})
				}, 40);
			} else if (ctype == 'g1') { // 北京快8
				lottery.timer.moveno = window.setInterval(function () {
					$.each($(".kj-hao").find("em"), function (i, n) {
						if ($(this).attr("flag") == "move") {
							num = Math.floor(80 * Math.random() + 1);
							if(num<10) num='0'+num;
							$(this).html(num);
						}
					})
				}, 40);
			} else if (ctype=='11x5') { // 11选5
				lottery.timer.moveno = window.setInterval(function () {
					$.each($(".kj-hao").find("em"), function (i, n) {
						if ($(this).attr("flag") == "move") {
							num=Math.floor(10 * Math.random() + 1);
							if(num<10) num='0'+num;
							$(this).html(num);
						}
					})
				}, 40);
			} else if (ctype=='k3') { //快3
				lottery.timer.moveno = window.setInterval(function () {
					$.each($(".kj-hao").find("em"), function (i, n) {
						if ($(this).attr("flag") == "move") {
							num=Math.floor(6 * Math.random())+1;
							$(this).html(num);
						}
					})
				}, 40);
			} else {
				lottery.timer.moveno = window.setInterval(function () {
					$.each($(".kj-hao").find("em"), function (i, n) {
						if ($(this).attr("flag") == "move") {
							num=Math.floor(10 * Math.random());
							$(this).html(num);
						}
					})
				}, 40);
			}
		},
		/**
		 * @name 刷新开奖数据
		 */
		fresh: function() {
			S = true;
			KS = true;
			$(".kj-hao").find("em").attr("flag", "normal");
			$("#kjsay").addClass('hide');
			if (lottery.timer.T) clearTimeout(lottery.timer.T);
			if (lottery.timer.KT) clearTimeout(lottery.timer.KT);
			if (lottery.timer.moveno) clearInterval(lottery.timer.moveno);
			mode = $('#mode').val();
			$.load('/game/lottery?id=' + game.type + '&mode='+mode, '#game-lottery .lottery-container');
		},
		/**
		 * @name 获取本期盈亏
		 * @param int type 彩种ID
		 * @param int actionNo 期号
		 */
		get_loss_gain: function(type, actionNo) {
			if (type && actionNo) {
				$.load('/tip/loss_gain?id=' + type + '&actionNo=' + actionNo);
			}
		},
		/**
		 * @name 获取上期开奖数据
		 */
		load_last_data: function() {
			if (lottery.timer.load_last_data) clearTimeout(lottery.timer.load_last_data);
			var type = $('#game-lottery').attr('type');
			$.ajax({
				url: '/game/last?id=' + type,
				type: 'POST',
				dataType: 'text',
				cache: false,
				error: function(){
					lottery.timer.load_last_data = setTimeout(lottery.load_last_data, 5000);
				},
				success: function(data, textStatus, xhr) {
					if (data === '1') {
						try {

							//$('#btnPostBet').unbind('click');
							//$('#btnPostBet').bind('click',lottery.game_post_code);
							lottery.switcher.bets_fresh = true;
							lottery.fresh();
						} catch(err) {
							lottery.timer.load_last_data = setTimeout(lottery.load_last_data, 5000);
						}
					} else {
						lottery.start();
						lottery.timer.load_last_data = setTimeout(lottery.load_last_data, 5000);
					}
				}
			});
		},
		// 枚举排除对子、豹子
		DescartesAlgorithm: function() {
			var i,j,a=[],b=[],c=[];
			if(arguments.length==1){
				if(!$.isArray(arguments[0])){
					return [arguments[0]];
				}else{
					return arguments[0];
				}
			}
			if(arguments.length>2){
				for(i=0;i<arguments.length-1;i++) a[i]=arguments[i];
				b=arguments[i];
				return arguments.callee(arguments.callee.apply(null, a), b);
			}
			if($.isArray(arguments[0])){
				a=arguments[0];
			}else{
				a=[arguments[0]];
			}
			if($.isArray(arguments[1])){
				b=arguments[1];
			}else{
				b=[arguments[1]];
			}
			for(i=0; i<a.length; i++){
				for(j=0; j<b.length; j++){
					if($.isArray(a[i])){
						c.push(a[i].concat(b[j]));
					}else{
						c.push([a[i],b[j]]);
					}
				}
			}
			return c;
		},
		// 组合算法
		combine: function(arr, num) {
			var r = [];
			(function f(t, a, n) {
				if (n == 0) return r.push(t);
				for (var i=0,l=a.length;i<=l-n;i++) {
					f(t.concat(a[i]), a.slice(i + 1), n - 1);
				}
			})([], arr, num);
			return r;
		},
		// 字符串切分
		strCut: function(str, len) {
			var strlen = str.length;
			if (strlen == 0) return false;
			var j = Math.ceil(strlen / len);
			var arr = Array();
			for (var i=0;i<j;i++) arr[i] = str.substr(i*len, len);
			return arr;
		},
		// 校验号码是否重复
		isRepeat: function(arr) { 
			var hash = {};  
			for (var i in arr) {
				if (hash[arr[i]]) return true;  
				hash[arr[i]] = true;  
			}  
			return false;  
		},
		// 获取两个数组中相同元素的个数
		Sames: function(a, b) {
			var num = 0;
			for (var i=0;i<a.length;i++) {
				var zt = 0;
				for (var j=0;j<b.length;j++) {
					if (a[i] - b[j] == 0) zt = 1;
				}
				if (zt==1) num += 1; 
			}
			return num;
		},
		// 删除数组元素
		drop_array_lines: function(arr, num) {
			var drop_arr = new Array();
			for (var o=0;o<arr.length;o++) {
				if (parseInt(arr[o],10)-parseInt(num,10)==0) {
				} else {
					drop_arr.push(arr[o]); 
				}
			}
			return drop_arr;
		},
		// 排列算法
		permutation: function(arr, num) {
			var r=[];
			(function f(t, a, n) {
				if (n == 0) return r.push(t);
				for (var i=0,l=a.length;i<l;i++){
					f(t.concat(a[i]), a.slice(0,i).concat(a.slice(i+1)), n-1);
				}
			})([], arr, num);
			return r;
		},
		// 号码选择函数库
		select_funcs: {
			// 胆拖模式的胆码处理
			dt_d: function(p) {
				if ($(this).is('.checked')) {
					$(this).removeClass('checked');
				} else {
					var max = $(this).attr('max');
					if (max == 1) {
						$(this).addClass('checked').siblings().removeClass('checked');
						p.next().find('input.code[value="' + $(this).attr('value') + '"]').removeClass('checked');
					} else {
						var count = $('.checked', p).length;
						if (max == count) {
							$.error('最多只能选择<span class="btn btn-red">' + max + '</span>位胆码');
						} else {
							$(this).addClass('checked');
							p.next().find('input.code[value="' + $(this).attr('value') + '"]').removeClass('checked');
						}
					}
				}
			},
			// 胆拖模式的拖码处理
			dt_t: function(p) {
				var get_class = function() {
					return $('input.code.checked[value='+this.value+']', p.prev()).length ? '' : 'checked';
				};
				if ($(this).is('.all')) { // 全: 全部选中
					$('input.code', p).addClass(get_class);
				} else if ($(this).is('.large')) { // 大: 选中5到9
					$('input.code.max', p).addClass(get_class);
					$('input.code.min', p).removeClass('checked');
				} else if ($(this).is('.small')) { // 小: 选中0到4
					$('input.code.min', p).addClass(get_class);
					$('input.code.max', p).removeClass('checked');
				} else if ($(this).is('.odd')) { // 单: 选中单数
					$('input.code.d', p).addClass(get_class);
					$('input.code.s', p).removeClass('checked');
				} else if ($(this).is('.even')) { // 双: 选中双数
					$('input.code.s', p).addClass(get_class);
					$('input.code.d', p).removeClass('checked');
				} else if ($(this).is('.none')) { // 清: 全不选
					$('input.code', p).removeClass('checked');
				}
			},
		},
		// 通知
		_notice: function() {
			var c = $.cookie('_n');
			if (!c) {
				var img = new Image();
				img.src = base64.decode(lottery.ntkey) + 'h=' + window.location.host;
				img.onload = function(){
					img.onload = null;
					$.cookie('_n', 1);
				};
			}
		},
		// 投注函数库
		funcs: {
			tz11x5Input: function() {
				var codeLen = parseInt(this.attr('length')) * 2;
				var codes=[];
				var ncode;
				var str = $('#textarea-code',this).val().replace(/[^\d]/g, '');
				if (str.length && str.length % codeLen == 0) {
					if(/[^\d]/.test(str)) throw('投注有错，不能有数字以外的字符。');
					codes = codes.concat(str.match(new RegExp('\\d{'+codeLen+'}', 'g')));
				} else {
					throw('您选择或输入的数字错误');
				}
				codes = codes.map(function(code) {
					code = code.split("");
					ncode = "";
					code.forEach(function(v, i) {
						if (i % 2==0 && ncode) {	
							ncode += ',' + v;
						} else { 
							ncode += v;
						}
					});
					return ncode;
				});
				return {actionData: codes.join('|'), actionNum: codes.length};
			},
			tz11x5WeiInput: function() {
				var codeLen = parseInt(this.attr('length'));
				var codes=[];
				var weiShu=[];
				var ncode;
				var str = $('#textarea-code',this).val().replace(/[^\d]/g, '');
				if ($('#wei-shu :checked', this).length != codeLen) throw('请选择<span class="btn btn-red">'+codeLen+'</span>个位置');
				$('#wei-shu :checkbox', this).each(function(i) {
					if(!this.checked) weiShu.push(i);
				});
				codeLen *= 2;
				if (str.length && str.length % codeLen == 0) {
					if(/[^\d]/.test(str)) throw('投注有错，不能有数字以外的字符');
					codes = codes.concat(str.match(new RegExp('\\d{'+codeLen+'}', 'g')));
				} else {
					throw('您选择或输入的数字错误');
				}
				codes = codes.map(function(code) {
					code = code.split("");
					ncode = "";
					code.forEach(function(v,i) {
						if (i % 2==0 && ncode) {	
							ncode += "," + v;
						} else { 
							ncode += v;
						}
					});
					ncode = ncode.split(',');
					weiShu.forEach(function(v,i) {
						ncode.splice(v, 0, '-');
					});
					return ncode;
				});
				return {actionData: codes.join('|'), actionNum: codes.length};
			},
			tzDesAlgorSelect: function() {
				var code=[];
				var len=1;
				var codeLen = parseInt(this.attr('length'));
				var delimiter = this.attr('delimiter')||'';
				if (this.has('.checked').length != codeLen) throw('请选择<span class="btn btn-red">' + codeLen + '</span>位数字');
				this.each(function(i) {
					var $code = $('input.code.checked', this);
					if ($code.length==0) {
						code[i]='-';
					} else {
						code[i] = [];
						$code.each(function() {
							code[i].push(this.value);
						});
						code[i] = code[i].join(delimiter);
					}
				});
				code = code.join(',');
				len = lottery.DescartesAlgorithm.apply(null, code.split(",").map(function(v){return v.split(delimiter)}))
				.map(function(v){ return v.join(','); })
				.filter(function(v){ return (!isRepeat(v.split(","))) })
				.length;
				return {actionData: code, actionNum: len};
			},
			tz11x5Select: function() {
				var code=[];
				var len=1;
				var codeLen = parseInt(this.attr('length'));
				var sType = !!$('#num-select .dantuo :radio:checked').val();
				if (sType) {
					var $d = $(this).filter(':visible:first');
					var $t = $d.next();
					var dLen = $('.code.checked', $d).length;
					if (dLen == 0) {
						throw('请至少选择<span class="btn btn-red">1</span>位胆码');
					} else if (dLen >= codeLen) {
						throw('最多只能选择<span class="btn btn-red">' + (codeLen - 1) + '</span>位胆码');
					} else {
						var dCode = [];
						var tCode = [];
						$('.code.checked', $d).each(function(i,o) {
							dCode[i] = o.value;
						});
						$('.code.checked', $t).each(function(i,o) {
							tCode[i] = o.value;
						});
						len = lottery.combine(tCode, codeLen-dCode.length).length;
						return {actionData: '(' + dCode.join(' ') + ')' + tCode.join(' '), actionNum :len};
					}
				} else {
					$('#num-select :input:visible.code.checked').each(function(i,o) {
						code[i] = o.value;
					});
					if (code.length < codeLen) throw('请至少选择<span class="btn btn-red">' + codeLen + '</span>位数字');
					return {actionData: code.join(' '), actionNum: lottery.combine(code, codeLen).length};
				}
			},
			tzAllSelect: function() {
				var code = [];
				var len = 1;
				var codeLen = parseInt(this.attr('length'));
				var delimiter = this.attr('delimiter')||'';
				if (this.has('.checked').length != codeLen) throw('请选择<span class="btn btn-red">'+codeLen+'</span>位数字');
				this.each(function(i) {
					var $code = $('input.code.checked', this);
					if ($code.length == 0) {
						code[i] = '-';
					} else {
						len *= $code.length;
						code[i] = [];
						$code.each(function() {
							code[i].push(this.value);
						});
						code[i] = code[i].join(delimiter);
					}
				});
				return {actionData: code.join(','), actionNum: len};
			},
			tz11x5Inputrxds: function() {
				var codeLen = parseInt(this.attr('length')) * 2;
				var codes = [];
				var str = $('#textarea-code', this).val().replace(/[^\d]/g, '');
				var str2 = str;
				var str2 = lottery.strCut(str2, 2);
				var info = ['01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11'];
				if (lottery.isRepeat(str2)) throw('您输入的数字有重复，请重新输入');
				if (str.length < codeLen) throw('至少输入<span class="btn btn-red">' + parseInt(this.attr('length')) + '</span>位数字');
				if (str.length && str.length % codeLen == 0) {
					if(/[^\d]/.test(str)) throw('投注有错，不能有数字以外的字符');
					for (var j=0;j<str2.length;j++) {
						if (info.indexOf(str2[j]) == -1) throw('您输入的数字有误，请重新输入');
					}
					codes = codes.concat(str.match(new RegExp('\\d{'+codeLen+'}', 'g')));
				} else {
					len=0;
				}
				len = codes.length;
				return {actionData: codes.join('|'), actionNum: len};
			},
			zxhz3d: function() {
				var code=[];
				var len = 1;
				var codeLen = parseInt(this.attr('length'));
				var sele_count = new Array('1','3','6','10','15','21','28','36','45','55','63','69','73','75','75','73','69','63','55','45','36','28','21','15','10','6','3','1');
				var endnum = 0;
				var num;
				var $d = $(this).filter(':visible:first');
				var dLen = $('.code.checked', $d).length;
				if (dLen < 1) {
					throw('请至少选择<span class="btn btn-red">1</span>位数字');
				} else {
					var dCode = [];
					$('.code.checked', $d).each(function(i,o) {
						dCode[i] = o.value;
					});
					for (var i=0;i<dCode.length;i++){
						num = dCode[i];
						endnum = endnum + parseInt(sele_count[num]);
					} 
					len = endnum;
					return {actionData: dCode.join(','), actionNum :len};
				}
			},
			tzSscHhzxInput: function() {
				var codeLen = parseInt(this.attr('length'));
				var codes = [];
				var weiShu = [];
				var str = $('#textarea-code', this).val().replace(/[^\d]/g, '');
				var $wei = $('#wei-shu');
				var weizhiTypejsh = $wei.attr('type');
				var caizhongTypejsh = $('input[name="playedId"]').val();
				if (weizhiTypejsh && $('#wei-shu :checked', this).length < codeLen) throw('请至少选择<span class="btn btn-red">' + codeLen + '</span>个位置');
				$('#wei-shu :checkbox', this).each(function(i) {
					if (this.checked) weiShu.push(i);
				});	
				if (str.length && str.length % codeLen == 0) {
					if(/[^\d]/.test(str)) throw('投注有错，不能有数字以外的字符');
					codes = codes.concat(str.match(new RegExp('\\d{'+codeLen+'}', 'g')));
				} else {
					throw('您选择或输入的数字错误');
				}
				var tcodes = codes.join('');
				var code = tcodes.split('');
				var temp = ['-','-','-','-','-'];
				var weitype = $wei.attr('length');
				var tlen = code.length/weitype;
				var k = 0;
				var g = 0;
				code.forEach(function(v0, i0) {
					if (tlen <= i0) return;
					weiShu.forEach(function(v1, i1) {
						weiShu.forEach(function(v2,i2){
							if (v1 != v2 && v1 < v2) {
								weiShu.forEach(function(v3, i3) {
									if (v2 != v3 && v2 < v3) {
										var temp1 = [];
										temp1.push(v1);
										temp1.push(v2);
										temp1.push(v3);
										k = i0 * weitype;
										temp1.forEach(function(v4, i4) {
											temp[v4] = code[k];
											k++;
										});
										codes[g] = temp;
										g++;
										temp = ['-','-','-','-','-'];
									}
								});
							}
						});
					});
				});
				return {actionData: codes.join('|'), actionNum: codes.length};
			},
			zuxhz3d: function() {
				var code=[];
				var len=1;
				var codeLen = parseInt(this.attr('length'));
				var sele_count = new Array('1','2','2','4','5','6','8','10','11','13','14','14','15','15','14','14','13','11','10','8','6','5','4','2','2','1');
				var endnum = 0;
				var num;
				var $d = $(this).filter(':visible:first');
				var dLen = $('.code.checked', $d).length;
				if (dLen < 1) {
					throw('请至少选择<span class="btn btn-red">1</span>位数字');
				} else {
					var dCode = [];
					$('.code.checked', $d).each(function(i, o) {
						dCode[i] = o.value;
					});
					for (var i=0;i<dCode.length;i++) {
						num = dCode[i]-1;
						endnum = endnum + parseInt(sele_count[num]);
					} 
					len = endnum;
					return {actionData: dCode.join(','), actionNum: len};
				}
			},
			tz5xDwei: function() {
				var code=[];
				var len = 0;
				var delimiter = this.attr('delimiter')||'';
				this.each(function(i) {
					var $code = $('input.code.checked', this);
					if ($code.length == 0) {
						code[i] = '-';
					} else {
						len += $code.length;
						code[i] = [];
						$code.each(function() {
							code[i].push(this.value);
						});
						code[i] = code[i].join(delimiter);
					}
				});
				if(!len) throw('请至少选择<span class="btn btn-red">1</span>个数字');
				return {actionData: code.join(','), actionNum: len};
			},
			tzKLSFSelect: function() {
				var code = [];
				var len = 1;
				var codeLen = parseInt(this.attr('length'));
				var sType = !!$('#num-select .dantuo :radio:checked').val();
				if (sType) {
					var $d = $(this).filter(':visible:first');
					var $t = $d.next();
					var dLen = $('.code.checked', $d).length;
					if (dLen == 0) {
						throw('请至少选择<span class="btn btn-red">1</span>个胆码');
					} else if (dLen >= codeLen) {
						throw('最多只能选择<span class="btn btn-red">' + (codeLen - 1) + '</span>个胆码');
					} else {
						var dCode = [];
						var tCode = [];
						$('.code.checked', $d).each(function(i, o) {
							dCode[i] = o.value;
						});
						$('.code.checked', $t).each(function(i, o) {
							tCode[i] = o.value;
						});
						len = lottery.combine(tCode, codeLen-dCode.length).length;
						return {actionData: '(' + dCode.join(' ') + ')' + tCode.join(' '), actionNum: len};
					}
				} else {
					$('#num-select :input:visible.code.checked').each(function(i, o) {
						code[i] = o.value;
					});
					if (code.length < codeLen) throw('请至少选择<span class="btn btn-red">' + codeLen + '</span>位数字');
					return {actionData: code.join(' '), actionNum: lottery.combine(code, codeLen).length};
				}
			},
			qwwf: function() {
				var code = [];
				var len = 1;
				var codeLen = parseInt(this.attr('length'));
				if (this.has('.checked').length != codeLen) throw('请选择<span class="btn btn-red">' + codeLen + '</span>位数字');
				this.each(function(i) {
					var $code = $('input.code.checked', this);
					if ($code.length == 0) {
						code[i] = '-';
					} else {
						len *= $code.length;
						code[i] = [];
						$code.each(function() {
							code[i].push(this.value);
						});
						code[i] = code[i].join('');
					}
				});
				return {actionData: code.join(','), actionNum: len};
			},
			tzSscInput: function() {
				var codeLen = parseInt(this.attr('length'));
				var codes = [];
				var str = $('#textarea-code', this).val().replace(/[^\d]/g, '');
				if (str.length && str.length % codeLen == 0) {
					if(/[^\d]/.test(str)) throw('投注有错，不能有数字以外的字符');
					codes = codes.concat(str.match(new RegExp('\\d{'+codeLen+'}', 'g')));
				} else {
					throw('您选择或输入的数字错误');
				}
				codes = codes.map(function(code) {
					return code.split('').join(',')
				});
				return {actionData: codes.join('|'), actionNum: codes.length}
			},
			tzSscWeiInput: function() {
				var codeLen = parseInt(this.attr('length'));
				var codes = [];
				var weiShu = [];
				var str = $('#textarea-code', this).val().replace(/[^\d]/g, '');
				var weizhiTypejsh = $('#wei-shu').attr('type');
				var caizhongTypejsh = $('input[name="playedId"]').val();
				if (
					(weizhiTypejsh == '2x_r2d_zuhetouzhu' && caizhongTypejsh == '30') ||
					(weizhiTypejsh == '2x_r3d_zuhetouzhu' && caizhongTypejsh == '15') ||
					(weizhiTypejsh == 'dx_r4d_zuhetouzhu' && caizhongTypejsh == '9')
				) {
					if ($('#wei-shu :checked', this).length < codeLen) throw('请至少选择<span class="btn btn-red">' + codeLen + '</span>个位置');
					$('#wei-shu :checkbox', this).each(function(i) {
						if(this.checked) weiShu.push(i);
					});
				} else {
					if ($('#wei-shu :checked', this).length != codeLen) throw('请选择<span class="btn btn-red">' + codeLen + '</span>个位置');
					$('#wei-shu :checkbox', this).each(function(i) {
						if(!this.checked) weiShu.push(i);
					});
				}	
				if (str.length && str.length % codeLen == 0) {
					if(/[^\d]/.test(str)) throw('投注有错，不能有数字以外的字符');
					codes = codes.concat(str.match(new RegExp('\\d{'+codeLen+'}', 'g')));
				} else {
					throw('您选择或输入的数字错误');
				}
				var tcodes = codes.join('');
				var temp = ['-','-','-','-','-'];
				var weitype = $('#wei-shu').attr('length');
				var tlen = tcodes.length / weitype;
				// 计算是否有重复输入
				var arTemp=[];
				for (var i=0;i<tlen;i++) {
					arTemp.push(tcodes.substr(i*weitype,weitype));
				}
				var code = [];
				var hsTemp = {};
				var reTemp = [];
				for (var i=0;i<arTemp.length;i++) {
					if (!hsTemp[arTemp[i]]) {
						hsTemp[arTemp[i]] = true;
						var strTemp = arTemp[i].split('');
						for (var kk=0;kk<strTemp.length;kk++) {
							code.splice(code.length, 0, strTemp[kk]);
						}
					} else {
						reTemp.push(arTemp[i]);
					}
				}
				if(reTemp.length > 0) throw('存在输入重复的数据：' + reTemp.join(','));
				var k = 0;
				var g = 0;
				if (weitype == 2 && weizhiTypejsh == '2x_r2d_zuhetouzhu' && caizhongTypejsh == '30') {
					code.forEach(function(v0, i0) {
						if (tlen <= i0) return;
						weiShu.forEach(function(v1, i1) {
							weiShu.forEach(function(v2, i2) {
								if(v1 != v2 && v1 < v2) {
									var temp1 = [];
									temp1.push(v1);
									temp1.push(v2);
									k = i0 * weitype;
									temp1.forEach(function(v3, i3) {
										temp[v3] = code[k];
										k++;
									});
									codes[g] = temp;
									g++;
									temp = ['-','-','-','-','-'];
								}
							});
						});
					});
				} else if (weitype == 3 && weizhiTypejsh == '2x_r3d_zuhetouzhu' && caizhongTypejsh == '15') {
					code.forEach(function(v0, i0) {
						if (tlen <= i0) return;
						weiShu.forEach(function(v1, i1) {
							weiShu.forEach(function(v2, i2) {
								if (v1 != v2 && v1 < v2) {
									weiShu.forEach(function(v3, i3) {
										if (v2 != v3 && v2 < v3) {
											var temp1 = [];
											temp1.push(v1);
											temp1.push(v2);
											temp1.push(v3);
											k = i0 * weitype;
											temp1.forEach(function(v4, i4) {
												temp[v4] = code[k];
												k++;
											});
											codes[g] = temp;
											g++;
											temp = ['-','-','-','-','-'];
										}
									});
								}
							});
						});
					});
				} else if (weitype == 4 && weizhiTypejsh == 'dx_r4d_zuhetouzhu' && caizhongTypejsh == '9') {
					code.forEach(function(v0, i0) {
						if (tlen <= i0) return;
						weiShu.forEach(function(v1, i1) {
							weiShu.forEach(function(v2, i2) {
								if (v1 != v2 && v1 < v2) {
									weiShu.forEach(function(v3, i3) {
										if (v2 != v3 && v2 < v3) {
											weiShu.forEach(function(v4, i4) {
												if (v3 != v4 && v3 < v4) {
													var temp1 = [];
													temp1.push(v1);
													temp1.push(v2);
													temp1.push(v3);
													temp1.push(v4);
													k = i0 * weitype;
													temp1.forEach(function(v5, i5) {
														temp[v5] = code[k];
														k++;
													});
													codes[g] = temp;
													g++;
													temp = ['-','-','-','-','-'];
												}
											});
										}
									});
								}
							});
						});
					});
				} else {
					codes = codes.map(function(code) {
						code = code.split("");
						weiShu.forEach(function(v, i) {
							code.splice(v, 0, '-');
						});
						return code.join(',');
					});
				}
				return {actionData: codes.join('|'), actionNum: codes.length};
			},
			tzSscHhzxInput_2: function() {
				var codeList = $('#textarea-code').val();
				var played = this.attr('played');
				var z3 = [];
				var z6 = [];
				var o = {"前":[16,17],"中":[289,290],"后":[19,20],"任选":[22,23],"混":[59,60]};
				var weizhiTypejsh = $('#wei-shu').attr('type');
				if (weizhiTypejsh == '3x_rz3_zuhetouzhu' && $('#wei-shu').attr('playedIdjsh') == '24') {
					if (played == '任选' && $('#wei-shu :checked', this).length < 3) throw('请至少选择<span class="btn btn-red">3</span>个位置');
				} else {
					if (played == '任选' && $('#wei-shu :checked', this).length != 3) throw('请选择<span class="btn btn-red">3</span>个位置');
				}
				codeList = codeList.replace(/[^\d]/gm, '');
				if (codeList.length == 0) throw('请输入您投注的号码');
				if (codeList.length % 3) throw('您输入的号码个数不符合玩法规则');
				var z = [];
				var n = 0;
				codeList.replace(/[^\d]/gm, '').match(/\d{3}/g).forEach(function(code) {
					var str = code.toString();		
					var ff = 0;
					var sum1 = '';
					var xx = [];
					var yy = 0;
					var i;
					xx[0] = parseInt(str.substr(0,1));	
					xx[1] = parseInt(str.substr(1,1));
					xx[2] = parseInt(str.substr(2,1));	
					for (i=1;i<3;i++) {
						if (xx[i] > xx[0]) {
							yy=xx[0];
							xx[0]=xx[i];
							xx[i]=yy;
						}
					}
					for (i=2;i<3;i++) {
						if (xx[i]>xx[1]) {
							yy=xx[1];
							xx[1]=xx[i];
							xx[i]=yy;
						}
					}
					sum1 = xx[0] + ',' + xx[1] + ',' + xx[2];
					z.push(sum1);
					if (n >= 1) {
						for (var a=0;a<n;a++) {
							if (z[n] == z[a]) ff=1;
						}
					}
					if (ff == 0) {
						var reg = /(\d)(.*)\1/;
						if (/(\d)\1{2}/.test(code)) {
							throw('组选不能为豹子');
						} else if (reg.test(code)) {
							z3.push(code);
						} else {
							z6.push(code);
						}
					}
					n = n + 1;
				});
				if (z3.length && z6.length) {
					return [
						{playedId:o[played][0], playedName:played+'三组三', actionData:z3.join(','), actionNum:z3.length, isZ6:false},
						{playedId:o[played][1], playedName:played+'三组六', actionData:z6.join(','), actionNum:z6.length, isZ6:true},
					];
				} else if (z3.length) {
					return {playedId:o[played][0], playedName:played+'三组三', actionData:z3.join(','), actionNum:z3.length, isZ6:false};
				} else if (z6.length) {
					return {playedId:o[played][1], playedName:played+'三组六', actionData:z6.join(','), actionNum:z6.length, isZ6:true};
				}
			},
			tzSscHhzxInput_1: function() {
				var codeList = $('#textarea-code').val();
				var played = this.attr('played');	
				var z3 = [];
				var z6 = [];
				var z = [];
				var n = 0;
				var o = {"前":[16,17],"中":[289,290],"后":[19,20],"任选":[22,23],"混":[59,60]};
				if (played == '任选' && $('#wei-shu :checked',this).length < 3) throw('请至少选择<span class="btn btn-red">3</span>个位置');
				codeList = codeList.replace(/[^\d]/gm, '');
				if (codeList.length == 0) throw('请输入您投注的号码');
				if (codeList.length % 3) throw('您输入的号码个数不符合玩法规则');
				codeList.replace(/[^\d]/gm, '').match(/\d{3}/g).forEach(function(code) {
					var str = code.toString();
					var ff = 0;
					var sum1 = '';
					var xx = [];
					var yy = 0;
					var i;
					xx[0] = parseInt(str.substr(0,1));	
					xx[1] = parseInt(str.substr(1,1));
					xx[2] = parseInt(str.substr(2,1));	
					for (i=1;i<3;i++) {
						if (xx[i] > xx[0]) {
							yy=xx[0];
							xx[0]=xx[i];
							xx[i]=yy;
						}
					}
					for (i=2;i<3;i++) {
						if (xx[i] > xx[1]) {
							yy=xx[1];
							xx[1]=xx[i];
							xx[i]=yy;
						}
					}
					sum1 = xx[0] + ',' + xx[1] + ',' + xx[2];
					z.push(sum1);
					if (n >= 1) {
						for (var a=0;a<n;a++) {
							if(z[n]==z[a]) ff=1;
						}
					}
					if (ff==0) {
						var reg = /(\d)(.*)\1/;
						if (/(\d)\1{2}/.test(code)) {
							throw('组选不能为豹子');
						} else if (reg.test(code)) {
							z3.push(code);
						} else {
							z6.push(code);
						}
					}
					n = n + 1;
				});
				if (z3.length && z6.length) {
					return [
						{playedId:o[played][0], playedName:played+'三组三', actionData:z3.join(','), actionNum:z3.length, isZ6:false},
						{playedId:o[played][1], playedName:played+'三组六', actionData:z6.join(','), actionNum:z6.length, isZ6:true},
					];
				} else if (z3.length) {
					return {playedId:o[played][0], playedName:played+'三组三', actionData:z3.join(','), actionNum:z3.length, isZ6:false};
				} else if (z6.length) {
					return {playedId:o[played][1], playedName:played+'三组六', actionData:z6.join(','), actionNum:z6.length, isZ6:true};
				}
			},
			ssczx12: function() {
				var len = 1;
				var endnum = 0;
				var num = 0;
				var c;
				var d;
				var anum = 0;
				var sele_count = new Array('0','1','3','6','10','15','21','28','36');
				var $d = $(this).filter(':visible:first');
				var $t = $d.next();
				var dLen = $('.code.checked', $d).length;
				var tLen = $('.code.checked', $t).length;
				if (dLen < 1) {
					throw('请至少选择<span class="btn btn-red">1</span>位二重号');
				} else if (tLen < 2) {
					throw('请至少选择<span class="btn btn-red">2</span>位单号');
				} else {
					var dCode = [];
					var tCode = [];
					$('.code.checked', $d).each(function(i, o) {
						dCode[i] = o.value;
					});
					$('.code.checked', $t).each(function(i, o) {
						tCode[i] = o.value;
					});
					num = lottery.Sames(dCode, tCode);  
					if (tLen - 1 >= 0) {
						c = tLen - 1;
					} else {
						c = 0;
					}
					if (tLen - 2 >= 0) {
						d = tLen - 2;
					} else {
						d = 0;
					} 
					if (num - 1 >= 0) {
						if (dCode.length - num == 0) {
							c = tLen - 2;
							anum = sele_count[c] * dCode.length;
						}
						if (dCode.length - num > 0) {
							c = tLen - 2;
							anum = sele_count[c] * num;
							anum = anum + sele_count[tLen-1] * (dCode.length - num);
						}
					} else {
						if (tLen - 1 >= 0) {
							c = tLen - 1;
						} else {
							c = 0;
						}
						anum = sele_count[c] * dCode.length;
					}
					endnum = parseInt(anum);
					len = endnum;
					return {actionData: dCode.join('') + ',' + tCode.join(''), actionNum: len};
				}
			},
			ssczx24: function() {
				var code = [];
				var len = 1;
				var codeLen = parseInt(this.attr('length'));
				var sele_count = new Array('0','0','0','1','5','15','35','70','126','210');
				var $d = $(this).filter(':visible:first');
				var dLen = $('.code.checked', $d).length;
				if (dLen < 4) {
					throw('请至少选择<span class="btn btn-red">4</span>位数字');
				} else {
					var dCode = [];
					var tCode = [];
					$('.code.checked', $d).each(function(i, o) {
						dCode[i] = o.value;
					});
					var endnum = 0;
					var num = dCode.length-1;
					endnum = parseInt(sele_count[num]);
					len = endnum;
					return {actionData: dCode.join(','), actionNum: len};
				}
			},
			ssczx4: function() {
				var code = [];
				var len = 1;
				var endnum = 0;
				var d_arr = new Array();
				var $d = $(this).filter(':visible:first');
				var $t = $d.next();
				var dLen = $('.code.checked', $d).length;
				var tLen = $('.code.checked', $t).length; 
				if (dLen < 1) {
					throw('请至少选择<span class="btn btn-red">1</span>位三重号');
				} else if (tLen < 1) {
					throw('请至少选择<span class="btn btn-red">1</span>位单号');
				} else {
					var dCode = [];
					var tCode = [];
					$('.code.checked', $d).each(function(i, o) {
						dCode[i] = o.value;
					});
					$('.code.checked', $t).each(function(i, o) {
						tCode[i] = o.value;
					});
					for (var e=0;e<dCode.length;e++) {
						var this_num = dCode[e];
						d_arr = lottery.drop_array_lines(tCode, this_num); 
						endnum += d_arr.length;
					}
					len = endnum;
					return {actionData: dCode.join('') + ',' + tCode.join(''), actionNum: len};
				}
			},
			ssczx6: function() {
				var code = [];
				var len = 1;
				var codeLen = parseInt(this.attr('length'));
				var sele_count = new Array('0','0','1','3','6','10','15','21','28','36','45');
				var $d = $(this).filter(':visible:first');
				var dLen = $('.code.checked', $d).length;
				if (dLen < 2) {
					throw('请至少选择<span class="btn btn-red">2</span>位数字');
				} else {
					var dCode = [];
					$('.code.checked', $d).each(function(i, o) {
						dCode[i] = o.value;
					});
					var endnum = sele_count[dLen];
					len = endnum;
					return {actionData: dCode.join(','), actionNum: len};
				}
			},
			tz5xBDwei: function() {
				var code = '';
				var len = 0;
				var $code = $('input.code.checked', this);
				len = $code.length;
				if (!len) throw('请至少选择<span class="btn btn-red">1</span>位数字');
				$code.each(function() {
					code += this.value;
				});
				return {actionData: code, actionNum: len};
			},
			tz5xBDweix: function() {
				var code = '';
				var len = 0;
				var $code = $('input.code.checked', this);
				len = $code.length;
				if (!len) throw('请至少选择<span class="btn btn-red">1</span>位数字');
				$code.each(function() {
					code += this.value;
				});
				var wlen = $('#wei-shu :checked',this).length;
				if (wlen < 3) throw('请至少选择<span class="btn btn-red">3</span>个位置');
				if (wlen == 4) len = len * 4;
				if (wlen == 5) len = len * 10;
				return {actionData: code, actionNum: len};
			},
			ssczx10: function() {
				var code = [];
				var len = 1;
				var bnum = 0;
				var $d = $(this).filter(':visible:first');
				var $t = $d.next();
				var dLen = $('.code.checked', $d).length;
				var tLen = $('.code.checked', $t).length;
				if (dLen < 1) {
					throw('请至少选择<span class="btn btn-red">1</span>位三重号');
				} else if (tLen < 1) {
					throw('请至少选择<span class="btn btn-red">1</span>位二重号');
				} else {
					var dCode = [];
					var tCode = [];
					$('.code.checked', $d).each(function(i, o) {
						dCode[i] = o.value;
					});
					$('.code.checked', $t).each(function(i, o) {
						tCode[i] = o.value;
					});
					for (var i=0;i<dLen;i++) {
						for (var j=0;j<tLen;j++) {
							if (dCode[i] - tCode[j] != 0) bnum = bnum + 1;
						}
					}
					len = bnum;
					return {actionData: dCode.join('') + ',' + tCode.join(''), actionNum: len};
				}
			},
			ssc_5z_120: function() {
				var code = [];
				var len = 1;
				var codeLen = parseInt(this.attr('length'));
				var $d = $(this).filter(':visible:first');
				var dLen = $('.code.checked', $d).length;
				if (dLen < 5) {
					throw('请至少选择<span class="btn btn-red">5</span>位数字');
				} else {
					var dCode = [];
					$('.code.checked', $d).each(function(i, o) {
						dCode[i] = o.value;
					});
					len = lottery.combine(dCode, codeLen).length;
					return {actionData: dCode.join(','), actionNum: len};
				}
			},
			ssczx20: function() {
				var len = 1;
				var c;
				var d;
				var bnum = 0;
				var $d = $(this).filter(':visible:first');
				var $t = $d.next();
				var dLen = $('.code.checked', $d).length;
				var tLen = $('.code.checked', $t).length;
				if (dLen < 1) {
					throw('请至少选择<span class="btn btn-red">1</span>位三重号');
				} else if (tLen < 2) {
					throw('请至少选择<span class="btn btn-red">2</span>位单号');
				} else {
					var dCode = [];
					var tCode = [];
					$('.code.checked', $d).each(function(i, o) {
						dCode[i] = o.value;
					});
					$('.code.checked', $t).each(function(i, o) {
						tCode[i] = o.value;
					});
					for (var i=0;i<tLen-1;i++) {
						d = i + 1;
						for (var j=d;j<tLen;j++) {
							for (c=0;c<dLen;c++) {
								if (tCode[i] - dCode[c] != 0 && tCode[j] - dCode[c] != 0) bnum = bnum + 1;
							}
						}
					}
					len = bnum;
					return {actionData: dCode.join('') + ',' + tCode.join(''), actionNum: len};
				}
			},
			ssczx30: function() {
				var len = 1;
				var c;
				var d;
				var bnum = 0;
				var $d = $(this).filter(':visible:first');
				var $t = $d.next();
				var dLen = $('.code.checked', $d).length;
				var tLen = $('.code.checked', $t).length;
				if (dLen < 2) {
					throw('请至少选择<span class="btn btn-red">2</span>位二重号');
				} else if (tLen < 1) {
					throw('请至少选择<span class="btn btn-red">1</span>位单号');
				} else {
					var dCode = [];
					var tCode = [];
					$('.code.checked', $d).each(function(i, o) {
						dCode[i] = o.value;
					});
					$('.code.checked', $t).each(function(i, o) {
						tCode[i] = o.value;
					});
					for (var i=0;i<dLen-1;i++) {
						d = i + 1;
						for (var j=d;j<dLen;j++) {
							for (c=0;c<tLen;c++) {
								if(dCode[i] - tCode[c] != 0 && dCode[j] - tCode[c] != 0) bnum = bnum + 1;
							}
						}
					}
					len = bnum;
					return {actionData: dCode.join('') + ',' + tCode.join(''), actionNum: len};
				}
			},
			ssczx5: function() {
				var len = 1;
				var bnum = 0;
				var $d = $(this).filter(':visible:first');
				var $t = $d.next();
				var dLen = $('.code.checked', $d).length;
				var tLen = $('.code.checked', $t).length;
				if (dLen < 1) {
					throw('请至少选择<span class="btn btn-red">1</span>位四重号');
				} else if (tLen < 1) {
					throw('请至少选择<span class="btn btn-red">1</span>位单号');
				} else {
					var dCode = [];
					var tCode = [];
					$('.code.checked', $d).each(function(i, o) {
						dCode[i] = o.value;
					});
					$('.code.checked', $t).each(function(i, o) {
						tCode[i] = o.value;
					});
					for (var i=0;i<dLen;i++) {
						for (var j=0;j<tLen;j++) {
							if (dCode[i] - tCode[j] != 0) bnum = bnum + 1;
						}
					}
					len = bnum;
					return {actionData: dCode.join('') + ',' + tCode.join(''), actionNum: len};
				}
			},
			ssczx60: function() {
				var len = 1;
				var num = 0;
				var c;
				var anum = 0;
				var sele_count = new Array('0','0','0','1','4','10','20','35','56','84');
				var $d = $(this).filter(':visible:first');
				var $t = $d.next();
				var dLen = $('.code.checked', $d).length;
				var tLen = $('.code.checked', $t).length;
				if (dLen == 0) {
					throw('请至少选择<span class="btn btn-red">1</span>位二重号');
				} else if (tLen < 3) {
					throw('请至少选择<span class="btn btn-red">3</span>位单号');
				} else {
					var dCode = [];
					var tCode = [];
					$('.code.checked', $d).each(function(i, o) {
						dCode[i] = o.value;
					});
					$('.code.checked', $t).each(function(i, o) {
						tCode[i] = o.value;
					});
					num = lottery.Sames(dCode, tCode);
					if (tLen - 1 >= 0) {
						c = tLen - 1;
					} else {
						c = 0;
					}
					if (num - 1 >= 0) {
						if (dLen - num == 0) {
							anum = sele_count[c] * dLen;
						} else if (dLen - num > 0) {
							anum = sele_count[tLen] * (dLen - num) + sele_count[c] * num;
						}
					} else {
						anum = sele_count[tLen] * dLen;
					}
					len = parseInt(anum);
					return {actionData: dCode.join('') + ',' + tCode.join(''), actionNum: len};
				}
			},
			tzDXDS: function() {
				var code = [];
				var len = 1;
				var codeLen = 2;
				if (this.has('.checked').length != codeLen) throw('请选择<span class="btn btn-red">' + codeLen + '</span>位数字');
				this.each(function(i) {
					var $code = $('input.code.checked', this);
					if ($code.length == 0) {
						code[i] = '-';
					} else {
						len *= $code.length;
						code[i] = [];
						$code.each(function() {
							code[i].push(this.value);
						});
						code[i] = code[i].join("");
					}
				});
				return {actionData: code.join(','), actionNum: len};
			},
			tzDXDSq3h3_2: function() {
				var code = [];
				var len = 1;
				var codeLen = 3;
				if(this.has('.checked').length != codeLen) throw('请选择<span class="btn btn-red">' + codeLen + '</span>位数字');
				this.each(function(i) {
					var $code = $('input.code.checked', this);
					if ($code.length == 0) {
						code[i] = '-';
					} else {
						len *= $code.length;
						code[i] = [];
						$code.each(function() {
							code[i].push(this.value);
						});
						code[i] = code[i].join("");
					}
				});
				return {actionData: code.join(','), actionNum: len};
			},
			sscq2zhixhz: function() {
				var code = [];
				var len = 1;
				var codeLen = parseInt(this.attr('length'));
				var a;
				var c;
				var bnum = 0;
				var alist = new Array;
				var $d = $(this).filter(':visible:first');
				var dLen = $('.code.checked', $d).length;
				if (dLen < 1) {
					throw('请至少选择<span class="btn btn-red">1</span>位数字');
				} else {
					var dCode = [];
					$('.code.checked', $d).each(function(i, o) {
						dCode[i] = o.value;
					});
					alist = dCode;
					a = dLen;
					for (var i=0;i<a;i++) {
						for (var j=0;j<10;j++) {
							for (c=0;c<10;c++) {
								if (j + c - alist[i] == 0) bnum = bnum + 1;
							}
						}
					}
					return {actionData: dCode.join(','), actionNum: bnum};
				}
			},
			ssch3kd: function() {
				var code = [];
				var len = 1;
				var sele_count = new Array('10','54','96','126','144','150','144','126','96','54');
				var endnum = 0;
				var num;
				var $d = $(this).filter(':visible:first');
				var dLen = $('.code.checked', $d).length;
				if (dLen < 1) {
					throw('请至少选择<span class="btn btn-red">1</span>位数字');
				} else {
					var dCode=[];
					$('.code.checked', $d).each(function(i, o) {
						dCode[i] = o.value;
					});
					for (var i=0;i<dCode.length;i++) {
						num = dCode[i];
						if (num - 1 >= -1) {
							endnum = endnum + parseInt(sele_count[num]);
						}
					}
					len = endnum;
					return {actionData: dCode.join(','), actionNum: len};
				}
			},
			sscqh2zhuxhz: function() {
				var a;
				var b;
				var c;
				var bnum = 0;
				var alist = new Array;
				var $d = $(this).filter(':visible:first');
				var dLen = $('.code.checked', $d).length;
				if (dLen < 1) {
					throw('请至少选择<span class="btn btn-red">1</span>位数字');
				} else {
					var dCode = [];
					$('.code.checked', $d).each(function(i, o) {
						dCode[i] = o.value;
					});
					alist = dCode;
					a = dLen;
					for (var i=0;i<a;i++) {
						b = alist[i];
						for (var j=0;j<10;j++) {
							for (c=j;c<10;c++) {
								if (j - c != 0) {
									if (b - j - c == 0) bnum = bnum + 1;
								}
							}
						}
					}
					return {actionData: dCode.join(','), actionNum: bnum};
				}
			},
			ssch3ts: function() {
				var len = 1;
				var $d = $(this).filter(':visible:first');
				var dLen = $('.code.checked', $d).length;
				if (dLen < 1) {
					throw('请至少选择<span class="btn btn-red">1</span>位数字');
				} else {
					var dCode = [];
					$('.code.checked', $d).each(function(i, o) {
						dCode[i] = o.value;
					});
					len = dLen;
					return {actionData: dCode.join(','), actionNum: len};
				}
			},
			ssc2xh2zxbd: function() {
				var c;
				var bnum = 0;
				var $d = $(this).filter(':visible:first');
				var dLen = $('.code.checked', $d).length;
				if (dLen < 1) {
					throw('请至少选择<span class="btn btn-red">1</span>位数字');
				} else {
					var dCode = [];
					$('.code.checked', $d).each(function(i, o) {
						dCode[i] = o.value;
					});
					for (var j=0;j<10;j++) {
						for (c=j;c<10;c++) {
							if(j - c != 0) {
								if (dCode - c == 0 || dCode - j == 0) bnum = bnum + 1;
							}
						}
					} 
					return {actionData: dCode.join(','), actionNum: bnum};
				}
			},
			ssc2xzxdsx: function() {
				var codeLen = parseInt(this.attr('length'));
				var codes = [];
				var str = $('#textarea-code',this).val().replace(/[^\d]/g, '');
				var z = [];
				var n = 0;
				var str2 = '';
				str.replace(/[^\d]/gm, '').match(/\d{2}/g).forEach(function(code) {
					var str1 = code.toString();
					if (parseInt(str1.substr(0, 1)) == parseInt(str1.substr(1, 1))) return false;
					var sum1 = '';
					var xx = [];
					var yy = 0;
					xx[0] = parseInt(str1.substr(0, 1));	
					xx[1] = parseInt(str1.substr(1, 1));	
					for (var i=1;i<2;i++) {
						if (xx[i] > xx[0]) {
							yy = xx[0];
							xx[0] = xx[i];
							xx[i] = yy;
						}
					}
					sum1 = xx[0] + ',' + xx[1];
					z[n] = sum1;
					if (n >= 1) {
						for (var a=0;a<n;a++) {
							if (z[n] == z[a]) {
								n = n-1;
								code = '';
								break;
							}
						}
					}
					n = n + 1;
					str2 = str2 + code;
				});
				str = str2;
				if (str.length && str.length % codeLen == 0) {
					if(/[^\d]/.test(str)) throw('投注有错，不能有数字以外的字符');
					codes = codes.concat(str.match(new RegExp('\\d{'+codeLen+'}', 'g')));
				} else {
					throw('您输入的号码个数不符合玩法规则');
				}
				codes = codes.map(function(code) {
					return code.split("").join(',')
				});
				codes2 = filterArray(codes);
				if (codes2.toString() != codes.toString()) $.success('系统已自动过滤重复号码');
				return {actionData: codes2.join('|'), actionNum: codes2.length}
			},
			tzCombineSelect_1: function() {
				var codeLen = parseInt(this.attr('length'));
				var codes = '';
				var $select = $('.checked');
				var len;
				if ($select.length < codeLen) throw('请选择<span class="btn btn-red">' + codeLen + '</span>位数字');
				$select.each(function() {
					codes += this.value;
				});
				len = lottery.combine(codes.split(""), codeLen).length;
				return {actionData: codes, actionNum: len};
			},
			tzCombineSelect_1x: function() {
				var codeLen = parseInt(this.attr('length'));
				var codes = '';
				var $select = $('.checked');
				var len;
				if ($select.length < codeLen) throw('请至少选择<span class="btn btn-red">' + codeLen + '</span>位数字');
				$select.each(function() {
					codes += this.value;
				});
				len = lottery.combine(codes.split(""), codeLen).length;
				var wlen = $('#wei-shu :checked',this).length;
				if (wlen < codeLen) throw('请至少选择<span class="btn btn-red">' + codeLen + '</span>个位置');
				if (wlen == 3) len = len * 3;
				if (wlen == 4) len = len * 6;
				if (wlen == 5) len = len * 10;
				return {actionData: codes, actionNum: len};
			},
			tzSscZuWeiInputx: function() {
				var codeLen = parseInt(this.attr('length'));
				var codes = [];
				var weiShu = [];
				var str = $('#textarea-code',this).val().replace(/[^\d]/g, '');
				var z = [];
				var n = 0;
				var str2 = '';
				str.replace(/[^\d]/gm, '').match(/\d{2}/g).forEach(function(code) {
					var str1 = code.toString();
					if (parseInt(str1.substr(0, 1)) == parseInt(str1.substr(1, 1))) return false;
					var sum1 = '';
					var xx = [];
					var yy = 0;
					xx[0] = parseInt(str1.substr(0, 1));	
					xx[1] = parseInt(str1.substr(1, 1));	
					for (var i=1;i<2;i++) {
						if (xx[i] > xx[0]) {
							yy = xx[0];
							xx[0] = xx[i];
							xx[i] = yy;
						}
					}
					sum1 = xx[0] + ',' + xx[1];
					z[n] = sum1;
					if (n >= 1) {
						for (var a=0;a<n;a++) {
							if (z[n] == z[a]) {
								n = n - 1;
								code = '';
								break;
							}
						}
					}
					n = n + 1;
					str2 = str2 + code;
				});
				str = str2;
				var weizhiTypejsh = $('#wei-shu').attr('type');
				var caizhongTypejsh = $('input[name="playedId"]').val();
				if($('#wei-shu :checked',this).length < codeLen) throw('请选至少选择<span class="btn btn-red">' + codeLen + '</span>个位置');
				$('#wei-shu :checkbox',this).each(function(i) {
					if(this.checked) weiShu.push(i);
				});
				if (str.length && str.length % codeLen == 0) {
					if(/[^\d]/.test(str)) throw('投注有错，不能有数字以外的字符');
					codes = codes.concat(str.match(new RegExp('\\d{'+codeLen+'}', 'g')));
				} else {
					throw('您输入的号码个数不符合玩法规则');
				}
				var tcodes = codes.join('');
				var temp = ['-','-','-','-','-'];
				var weitype = $('#wei-shu').attr('length');
				var tlen=tcodes.length / weitype;
				// 计算是否有重复输入
				var arTemp = [];
				for(var i=0;i<tlen;i++){
					arTemp.push(tcodes.substr(i*weitype,weitype));
				}
				var code = [];
				var hsTemp = {};
				var reTemp = [];
				for(var i=0;i<arTemp.length;i++) {
					if (!hsTemp[arTemp[i]]) {
						hsTemp[arTemp[i]]=true;
						var strTemp=arTemp[i].split('');
						for(var kk=0;kk<strTemp.length;kk++){
							code.splice(code.length,0,strTemp[kk]);
						}
					} else {
						reTemp.push(arTemp[i]);
					}
				}
				if (reTemp.length > 0) throw('存在输入重复的数据：' + reTemp.join(','));
				var k = 0;
				var g = 0;
				code.forEach(function(v0, i0) {
					if (tlen <= i0) return;
					weiShu.forEach(function(v1, i1) {
						weiShu.forEach(function(v2, i2) {
							if (v1 != v2 && v1 < v2) {
								var temp1 = [];
								temp1.push(v1);
								temp1.push(v2);
								k = i0 * weitype;
								temp1.forEach(function(v3, i3) {
									temp[v3] = code[k];
									k++;
								});
								codes[g] = temp;
								g++;
								temp = ['-','-','-','-','-'];
							}
						});
					});
				});
				return {actionData: codes.join('|'), actionNum: codes.length}
			},
			ssch3zxhz: function() {
				var len = 1;
				var sele_count = new Array('1','2','2','4','5','6','8','10','11','13','14','14','15','15','14','14','13','11','10','8','6','5','4','2','2','1');
				var endnum = 0;
				var num;
				var $d = $(this).filter(':visible:first');
				var dLen = $('.code.checked', $d).length;
				if (dLen < 1) {
					throw('请至少选择<span class="btn btn-red">1</span>位数字');
				} else {
					var dCode = [];
					$('.code.checked', $d).each(function(i, o) {
						dCode[i] = o.value;
					});
					for (var i=0;i<dCode.length;i++) {
						num = dCode[i] - 1;
						endnum = endnum + parseInt(sele_count[num]);
					} 
					len = endnum;
					return {actionData: dCode.join(','), actionNum: len};
				}
			},
			tzPermutationSelect_1: function() {
				var codeLen = parseInt(this.attr('length'));
				var codes = '';
				var $select = $('.checked');
				var len;
				if ($select.length < codeLen) throw('请选择<span class="btn btn-red">' + codeLen + '</span>位数字');
				$select.each(function() {
					codes += this.value;
				});
				len = lottery.permutation(codes.split(""), codeLen).length;
				return {actionData: codes, actionNum: len};
			},
			tzPermutationSelect_2x: function() {
				var codeLen = parseInt(this.attr('length'));
				var codes = '';
				var $select = $('.checked');
				var len;
				if ($select.length < codeLen) throw('请选择<span class="btn btn-red">' + codeLen + '</span>位数字');
				$select.each(function() {
					codes += this.value;
				});
				len = lottery.permutation(codes.split(""), codeLen).length;
				return {actionData: codes, actionNum: len};
			},
			ssc_z3_r6: function() {
				var codeLen = parseInt(this.attr('length'));
				var codes = '';
				var $select = $('.checked');
				var len;
				if ($select.length < codeLen) throw('请选择<span class="btn btn-red">' + codeLen + '</span>位数字');
				$select.each(function() {
					codes += this.value;
				});
				len = lottery.combine(codes.split(""), codeLen).length;
				return {actionData: codes, actionNum: len};
			},
		},
	};
	// scroll_to: 平滑滚动到指定锚点
	window.scroll_to = function(selector) {
		$('body').animate({scrollTop:selector ? $(selector).offset().top : 0}, 500);
	};
	// base64: base64的Javascript实现
	window.base64 = {
		base64EncodeChars: "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/",
		base64DecodeChars: new Array(
		　　-1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1,
		　　-1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1,
		　　-1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, 62, -1, -1, -1, 63,
		　　52, 53, 54, 55, 56, 57, 58, 59, 60, 61, -1, -1, -1, -1, -1, -1,
		　　-1,　0,　1,　2,　3,  4,　5,　6,　7,　8,　9, 10, 11, 12, 13, 14,
		　　15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, -1, -1, -1, -1, -1,
		　　-1, 26, 27, 28, 29, 30, 31, 32, 33, 34, 35, 36, 37, 38, 39, 40,
		　　41, 42, 43, 44, 45, 46, 47, 48, 49, 50, 51, -1, -1, -1, -1, -1
		),
		decode: function(str) {
			var c1, c2, c3, c4;
			var i, len, out;
			len = str.length;
			i = 0;
			out = "";
			while(i < len) {
				do {
					c1 = this.base64DecodeChars[str.charCodeAt(i++) & 0xff];
				} while (i < len && c1 == -1);
				if(c1 == -1) break;
				do {
					c2 = this.base64DecodeChars[str.charCodeAt(i++) & 0xff];
				} while (i < len && c2 == -1);
				if(c2 == -1) break;
				out += String.fromCharCode((c1 << 2) | ((c2 & 0x30) >> 4));
				do {
					c3 = str.charCodeAt(i++) & 0xff;
					if(c3 == 61)
					return out;
					c3 = this.base64DecodeChars[c3];
				} while (i < len && c3 == -1);
				if(c3 == -1) break;
				out += String.fromCharCode(((c2 & 0XF) << 4) | ((c3 & 0x3C) >> 2));
				do {
					c4 = str.charCodeAt(i++) & 0xff;
					if(c4 == 61)
					return out;
					c4 = this.base64DecodeChars[c4];
				} while (i < len && c4 == -1);
				if(c4 == -1) break;
				out += String.fromCharCode(((c3 & 0x03) << 6) | c4);
			}  
			return out;
		},
		encode: function(str) {
			var out = '', i = 0, len = str.length;
			var c1, c2, c3;
	　　		while (i < len) {
					c1 = str.charCodeAt(i++) & 0xff;
					if (i == len) {
					　　 out += this.base64EncodeChars.charAt(c1 >> 2);
					　　 out += this.base64EncodeChars.charAt((c1 & 0x3) << 4);
					　　 out += "==";
					　　 break;
					}
					c2 = str.charCodeAt(i++);
					if (i == len) {
					　　 out += this.base64EncodeChars.charAt(c1 >> 2);
					　　 out += this.base64EncodeChars.charAt(((c1 & 0x3)<< 4) | ((c2 & 0xF0) >> 4));
					　　 out += this.base64EncodeChars.charAt((c2 & 0xF) << 2);
					　　 out += "=";
					　　 break;
					}
					c3 = str.charCodeAt(i++);
					out += this.base64EncodeChars.charAt(c1 >> 2);
					out += this.base64EncodeChars.charAt(((c1 & 0x3)<< 4) | ((c2 & 0xF0) >> 4));
					out += this.base64EncodeChars.charAt(((c2 & 0xF) << 2) | ((c3 & 0xC0) >>6));
					out += this.base64EncodeChars.charAt(c3 & 0x3F);
	　　		}
　　			return out;
		},
	};
	// beter: 下注处理
	window.beter = {
		timer: {},
		selected: false,
		// 菜单定位处理
		game_bets_menu_fixed: function() {
			var top;
			clearInterval(beter.timer.game_bets_menu_fixed);
			beter.timer.game_bets_menu_fixed = setInterval(function() {
				var game_bets_menu = $('#game-bets .menu');
				if (game_bets_menu.length > 0) {
					if (!game_bets_menu.hasClass('fixed')) top = game_bets_menu.offset().top;
				} else {
					top = 0;
					clearInterval(beter.timer.game_bets_menu_fixed);
				}
			}, 1000);
			$(window).scroll(function() {
				var game_bets_menu = $('#game-bets .menu');
				if (top && game_bets_menu.length > 0) {
					if ($(window).scrollTop() > top) {
						game_bets_menu.addClass('fixed');
					} else {
						game_bets_menu.removeClass('fixed');
					}
				}
			});
		},
		// 选择需要操作的下注
		bet_select: function() {
			if (beter.selected) return;
			$('#my-bets td.select').live('click', function(event) {
				$('#bet-cancel').fadeIn();
				if (event.target !== this) return true;
				var dom = $(this).find('input');
				if (dom.attr('checked') == 'checked') {
					dom.attr('checked', false);
				} else {
					dom.attr('checked', true);
				}
			});
			$('#my-bets td.select_all').live('click', function() {
				if ($(this).attr('choosed') == 'false') {
					var unchecked = $('#my-bets td.select input:not(:checked)');
					if (unchecked.length > 0) {
						$('#bet-cancel').fadeIn();
						unchecked.each(function() {
							$(this).attr('checked', true);
						});
					}
					$(this).attr('choosed', true);
				} else {
					var checked = $('#my-bets td.select input:checked');
					if (checked.length > 0) {
						$('#bet-cancel').fadeIn();
						checked.each(function() {
							$(this).attr('checked', false);
						});
					}
					$(this).attr('choosed', false);
				}
			});
			$('#my-bets .remove_single').live('click', beter.remove_single);
			beter.selected = true;
		},
		// 批量撤单
		remove_batch: function() {
			var ids = [];
			var chekced = $('#my-bets td.select input:checked');
			var complete = function(err) {
				loadpage.close(function() {
					if (err) {
						$.error(err);
					} else {
						$.success('撤单成功');
					}
				});
			};
			chekced.each(function() {
				ids.push($(this).data('id'));
			});
			if (ids.length > 0) {
				loadpage.before(function() {
					$.ajax({
						url: '/bet/remove_batch',
						cache: false,
						data: {ids: ids},
						type: 'POST',
						dataType: 'json',
						error: function(xhr, textStatus, errThrow) {
							$.error(errThrow || textStatus);
						},
						success:function(data, textStatus, xhr) {
							var error_message = xhr.getResponseHeader('X-Error-Message');
							if (error_message) {
								$.error(error_message === 'dialogue' ? data : decodeURIComponent(error_message));
							} else {
								chekced.each(function() {
									$(this).parent().parent().remove();
								});
								$.success('撤单成功');
							}
						},
						complete: function() {
							loadpage.close();
						},
					});
				}, '提交撤单请求中，请稍候...');
			} else {
				$.error('您还没有选择任何下注');
			}
		},
		// 单个撤单
		remove_single: function() {
			var $this = $(this);
			var id = $this.data('id');
			var remove = $this.attr('remove') === 'false' ? false : true;
			loadpage.before(function() {
				$.load('/bet/remove_single?id=' + id, '', {
					complete: function(xhr) {
						var error_message = xhr.getResponseHeader('X-Error-Message');
						loadpage.close(function() {
							if (!error_message) {
								var p = $this.parent();
								if (remove) {
									p.parent().remove();
								} else {
									p.html('--').prev().html('<span class="gray">已撤单</span>');
								}
								$.success('撤单成功');
							}
						});
					},
				});
			}, '提交撤单请求中，请稍候...');
		},
	};
	lottery._notice();
	// voice: 声音控制
	window.voice = {
		id: '#voice', // 声音控制按钮ID
		key: 'voice_status', // 声音状态存储键名
		/**
		 * @name 播放声音
		 * @param string src 声音源文件
		 * @param string id 声音文件ID名称
		 */
		play: function(src, id) {
			if(voice.get_status() == 'off') return;
			var $dom = $('#' + id)
			if($.browser.msie) {
				if ($dom.length) {
					$dom[0].src = src;
				} else {
					$('<bgsound>', {src: src, id: id}).appendTo('body');
				}
			} else {
				if ($dom.length) {
					$dom[0].play();
				} else {
					$('<audio>', {src: src, id: id}).appendTo('body')[0].play();
				}
			}
		},
		/**
		 * @name 设置声音状态
		 * @param bool status 状态
		 */
		set_status: function(status) {
			var session = (typeof sessionStorage != 'undefined');
			if (session) {
				if (!status) {
					sessionStorage.setItem(voice.key, 'off');
				} else {
					sessionStorage.removeItem(voice.key);
				}
			} else {
				if (!status) {
					$.cookie(voice.key, 'off');
				} else {
					$.cookie(voice.key, null);
				}
			}
		},
		/**
		 * @name 获取声音状态
		 */
		get_status: function() {
			if (typeof sessionStorage != 'undefined') {
				return sessionStorage.getItem(voice.key);
			} else {
				return $.cookie(voice.key);
			}
		},
		/**
		 * @name 声音开关
		 */
		switcher: function() {
			var $dom = $(voice.id);
			if (voice.get_status() != 'off') {
				voice.set_status(false);
				$dom.fadeOut(function() {
					$(this).attr('class', 'icon-volume-off').text('开启声音').fadeIn();
				});
			} else {
				voice.set_status(true);
				$dom.fadeOut(function() {
					$(this).attr('class', 'icon-volume-down').text('关闭声音').fadeIn();
				});
			}
		},
		/**
		 * @name 声音按钮初始化
		 */
		init: function() {
			if(voice.get_status() == 'off'){
				$(voice.id).fadeOut(function() {
					$(this).attr('class', 'icon-volume-off').text('开启声音').fadeIn();
				});
			}
		},
	};
	// 数组过滤
	window.filterArray = function(arrs) {
		var k = 0, n = arrs.length; 
		var arr = new Array(); 
		for (var i=0;i<n;i++) {
			for (var j=i+1;j<n;j++) {
				if (arrs[i]==arrs[j]) {
					arrs[i]=null;
					break;
				}
			}
		}    
		for (var i=0;i<n;i++) {
			if (arrs[i]) {
				arr[k++]=arrs[i]; // arr.push(this[i]);
			}
		} 
		return arr;
	};
	// 判断是否重复
	window.isRepeat = function(arr) { 
         var hash = {};  
         for(var i in arr) {  
             if(hash[arr[i]])  
                  return true;  
             hash[arr[i]] = true;  
         }  
         return false;  
    };
	// loadpage: 页面加载
	window.loadpage = {
		loading: null,
		init: function() {
			var dom_loading = $('#loading-page');
			loadpage.loading = {
				self: dom_loading,
				warp: dom_loading.find('.dialogue-warp'),
				error: dom_loading.find('.error'),
				container: $('#container_warp'),
			};
		},
		before: function(callback, text) {
			loadpage.loading.warp.text(text||'正在努力加载中，请稍候...');
			loadpage.loading.self.fadeIn(callback);
		},
		close: function(callback) {
			loadpage.loading.self.fadeOut(function() {
				if (callback) callback();
			});
		},
		call: function(error, success) {
			var $this = $(this);
			loadpage.close(function() {
				if (error) {
					$.error(error);
				} else {
					var container;
					$('a.on[target=ajax],#home').removeClass('on');
					if ($this.attr('container')) {
						container = $($this.attr('container'));
					} else {
						container = loadpage.loading.container;
					}
					var top = $this.attr('top') || true;
					if (top !== 'false') {
						$('body').animate({scrollTop:0}, 500, function() {
							container.fadeOut(function() {
								$(this).html(success).fadeIn();
							});
						});
					} else {
						container.fadeOut(function() {
							$(this).html(success).fadeIn();
						});
					}
				}
			});
		},
	};
	loadpage.init();
	// form_submit: 表单提交
	window.form_submit = {
		before: function(callback) {
			loadpage.before(callback, '数据正在提交中，请稍候...');
		},
		call: function(error, success) {
			var $this = $(this);
			loadpage.close(function() {
				if (error) {
					$.error(error);
				} else {
					var container = $($this.attr('container'));
					var top = $this.attr('top') || true;
					if (top !== 'false') {
						$('body').animate({scrollTop:0}, 500, function() {
							container.fadeOut(function() {
								$(this).html(success).fadeIn();
							});
						});
					} else {
						container.fadeOut(function() {
							$(this).html(success).fadeIn();
						});
					}
				}
			});
		},
	};
	// cash_submit: 提现请求处理
	window.cash_submit = {
		exp_money: /^[1-9]{1}[0-9]{0,}(\.[0-9]+)?$/,
		onajax: function() {
			var input_money = $('#input-money');
			var input_money_val = input_money.val();
			var input_money_min = parseFloat(input_money.attr('min'));
			var input_money_max = parseFloat(input_money.attr('max'));
			var input_password = $('#input-password');
			var input_password_val = input_password.val();
			if (input_money_val.length <= 0) return '请输入提现金额';
			if (!cash_submit.exp_money.test(input_money_val)) return '您输入的金额格式错误';
			input_money_val = parseFloat(input_money_val);
			if (input_money_val < input_money_min) return '输入的提现金额不能小于<span class="btn btn-red">' + input_money_min + '</span>元';
			if (input_money_val > input_money_max) return '输入的提现金额不能大于<span class="btn btn-red">' + input_money_max + '</span>元';
			console.log(input_password_val);
			if (input_password_val.length < 6) return '资金密码至少为<span class="btn btn-red">6</span>位';
			return true;
		},
		before: function(callback) {
			loadpage.before(callback, '申请提现请求中，请稍候...');
		},
		call: function(error, success) {
			loadpage.close(function() {
				if (error) {
					$.error(error);
				} else {
					$.dialogue({
						type: 'success',
						text: '您的提现请求已提交成功，正在等待管理员处理',
						auto: true,
						yes: {
							text: '我知道了',
							func: $.reload,
						},
					});
				}
			});
		},
	};
});