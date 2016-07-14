<div id="game-lottery" type="<?php echo $type_id;?>" ctype="<?php echo $types[$type_id]['type']?>">
	<div class="lottery-container">
		<div class="loading">开奖数据加载中，请稍候...</div>
	</div>
</div>
<div id="game-play">
	<div class="group">
		<div class="name icon-th-large">玩法分类</div>
		<ul class="list" id="group_list">
			<?php
				foreach ($groups as $gid => $group) {
					$class = $gid == $group_id ? ' class="on"' : '';
			?>
			<li><a data-id="<?php echo $group['id'];?>" href="javascript:;"<?php echo $class;?>><?php echo $group['groupName'];?></a></li>
			<?php }?>
		</ul>
	</div>
	<div class="play">
		<?php require(TPL.'/game/play_index.tpl.php');?>
	</div>
	<div class="play-work">
		<div id="play-work-setting">
			<div id="fandian-value" data-bet-count="<?php echo $this->config['betMaxCount'];?>" data-bet-zj-amount="<?php echo $this->config['betMaxZjAmount'];?>" max="<?php echo $this->user['fanDian'];?>" game-fan-dian="<?php echo $this->config['fanDianMax'];?>" fan-dian="<?php echo $this->user['fanDian'];?>" game-fan-dian-bdw="<?php echo $this->config['fanDianBdwMax'];?>" fan-dian-bdw="<?php echo $this->user['fanDianBdw'];?>" class="left"></div>
			<div id="play-mod">
				<span class="name icon-gauge">模式：</span>
				<?php
					$mods = array(
						array(
							'switch' => $this->config['yuanmosi'],
							'rebate' => $this->config['betModeMaxFanDian0'],
							'value'  => '2.000',
							'name'   => '元',
						),
						array(
							'switch' => $this->config['jiaomosi'],
							'rebate' => $this->config['betModeMaxFanDian1'],
							'value'  => '0.200',
							'name'   => '角',
						),
						array(
							'switch' => $this->config['fenmosi'],
							'rebate' => $this->config['betModeMaxFanDian2'],
							'value'  => '0.020',
							'name'   => '分',
						),
						array(
							'switch' => $this->config['limosi'],
							'rebate' => $this->config['betModeMaxFanDian3'],
							'value'  => '0.002',
							'name'   => '厘',
						),
					);
					$first = true;
					foreach ($mods as $mod) {
						if ($mod['switch'] == 1) {
							if ($first) {
								$class = 'danwei trans on';
								$first = false;
							} else {
								$class = 'danwei trans';
							}
							echo '<b value="'.$mod['value'].'" data-max-fan-dian="'.$mod['rebate'].'" class="'.$class.'">'.$mod['name'].'</b>';
						}
					}
				?>
			</div>
			<div id="beishu-warp">
				<span class="name icon-wrench">倍数：</span>
				<i class="sur trans icon-minus"></i>
				<input type="text" autocomplete="off" id="beishu-value" value="<?php echo (array_key_exists('beiShu', $_COOKIE) && is_numeric($_COOKIE['beiShu']) && $_COOKIE['beiShu'] > 0) ? intval($_COOKIE['beiShu']) : 1;?>">
				<i class="add trans icon-plus"></i>
			</div>
			<div class="opt">
				<a href="javascript:lottery.game_add_code();" class="add btn btn-red icon-pin">添加</a>
				<a href="javascript:lottery.game_remove_code();" class="del btn btn-green icon-trash-1">清空</a>
			</div>
			<div class="bet-info icon-chart-bar"><span id="all-count">0</span>注，<span id="all-amount">0.00</span>元</div>
		</div>
		<div id="play-work-data">
			<div id="bets-cart">
				<table width="100%" cellpadding="0" cellspacing="0">
					<tr class="head">
						<td>玩法</td>
						<td>投注号码</td>
						<td>投注数量</td>
						<td>投注金额</td>
						<td>投注倍数</td>
						<td>投注模式</td>
						<td>奖金 - 返点</td>
						<td>操作</td>
					</tr>
				</table>
			</div>
			<div id="play-btn">
				<input type="hidden" id="zhuiHao" name="zhuiHao" value="0">
				<a href="javascript:;" id="btnPostBet" class="btn btn-red icon-basket">确认投注</a>
				<a href="javascript:;" id="btnZhuiHao" class="btn btn-yellow icon-magic">智能追号</a>
				<a href="javascript:;" class="btn btn-purple icon-sitemap hide">合买跟单</a>
			</div>
		</div>
	</div>
</div>
<div id="game-bets">
	<div class="menu">
		<a href="javascript:;" class="icon-flag-empty on">近期投注<span class="triangle"></span></a>
		<a href="javascript:beter.remove_batch();" class="icon-trash hide" id="bet-cancel">批量撤销选中投注</a>
		<a href="javascript:$('#bet-log').trigger('click');" class="more icon-flag">所有投注记录</a>
	</div>
	<div class="container">
		<div id="my-bets">
			<?php require(TPL.'/game/bets_recent.tpl.php');?>
		</div>
	</div>
</div>
<script type="text/javascript">
~(function() {
	window.game = {
		type: <?php echo $type_id;?>,
		played: <?php echo $play_id;?>,
		groupId: <?php echo $group_id;?>,
		stop: <?php echo $this->config['switchBuy'] == 0 ? 'true' : 'false';?>,
		ban: <?php echo ($this->config['switchDLBuy'] == 0 && $this->user['type']) ? 'true' : 'false';?>,
	};
	lottery.switcher.bets_fresh = false;
	// 声音初始化
	voice.init();
	// 初始化左侧菜单
	$('#home').removeClass('on');
	var dom_nav = $('#game-nav .g .list li a[type-id=' + game.type + ']');
	if (!dom_nav.hasClass('on')) {
		dom_nav.addClass('on').parent().parent().slideDown(function() {
			$(this).prev().removeClass('icon-bookmark').addClass('icon-bookmark-empty');
		});
	}
	// 初始化历史选择模式
	var mode = $.cookie('mode');
	if (mode) $('#play-mod b[value="' + mode + '"]').addClass('on').siblings('b.on').removeClass('on');
	// 选择追号投注
	$('#bets-cart tr.code').live('click', function() {
		$(this).addClass('choosed').siblings('tr.choosed').removeClass('choosed');
	});
	// 确认购买事件绑定
	$('#btnPostBet').unbind('click');
	$('#btnPostBet').bind('click', lottery.game_post_code);
	// 开奖数据块首次加载
	setTimeout(function() {
		$.load('/game/lottery?id=<?php echo $type_id;?>', '#game-lottery .lottery-container');
	}, 1000);
	// 订单菜单下拉固定
	beter.game_bets_menu_fixed();
	// 订单选择
	beter.bet_select();
})();
</script>