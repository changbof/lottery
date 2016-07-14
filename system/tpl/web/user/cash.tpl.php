<div id="cash-panel" class="money-panel common">
	<div class="main">
		<form action="/user/cash_submit" target="ajax" func="cash_submit">
			<input type="text" class="hide">
			<input type="password" class="hide">
			<div id="cash-type" class="type">
				<div id="cash-current" class="current"><img width="88" height="35" src="/static/image/bank/bank_<?php echo $bank['id'];?>.jpg" title="<?php echo $bank['bankName'];?>"></div>
				<span class="choose icon-wrench">修改</span>
				<span class="hover icon-wrench">点击修改银行信息</span>
			</div>
			<div class="input<?php if (!$enable['result']) echo ' disabled';?>">
				<span class="icon icon-yen"></span>
				<input autocomplete="off" type="text" id="input-money" name="money" min="<?php echo $this->config['cashMin'];?>" max="<?php echo $this->config['cashMax'];?>" placeholder="请输入您的提现金额，最低：<?php echo $this->config['cashMin'];?>元，最高：<?php echo $this->config['cashMax'];?>元"<?php if (!$enable['result']) echo ' disabled';?>>
			</div>
			<div class="input password mr15<?php if (!$enable['result']) echo ' disabled';?>">
				<span class="icon icon-key"></span>
				<input type="password" id="input-password" name="password" min="<?php echo $this->config['cashMin'];?>" max="<?php echo $this->config['cashMax'];?>" placeholder="请输入资金密码"<?php if (!$enable['result']) echo ' disabled';?>>
			</div>
			<button type="submit" class="submit btn btn-blue icon-ok">提现</button>
		</form>
	</div>
	<div id="cash-intro" class="addon<?php if (!$enable['result']) echo ' nb';?>">
		<?php if (!$enable['result']) {?>
		<div class="tip icon-attention-alt"><?php echo $enable['reason'];?><span class="triangle"></span></div>
		<?php }?>
		<ul class="list">
			 <li>您是尊贵的<span class="btn btn-red">VIP <?php echo $this->user['grade'];?></span>用户，每天提现次数上限为<span class="btn btn-green"><?php echo $info['times_limit'];?></span>次，今天您已经提交<span class="btn btn-blue"><?php echo $info['times'];?></span>次申请；</li>
			 <li>每天受理提现请求的时间段为<span class="color blue"><?php echo $this->config['cashFromTime'];?> ~ <?php echo $this->config['cashToTime'];?></span>；</li>
			 <li>提现金额最小为<span class="color red"><?php echo $this->config['cashMin'];?></span>元，最大为<span class="color red"><?php echo $this->config['cashMax'];?></span>元；</li>
			 <li>消费比例公式：今日消费比例=今日投注量/今日充值额，消费比例未达到<?php echo $this->config['cashMinAmount'];?>%则不能提现；</li>
			 <li>如果今日未充值，则消费比例默认为100%，即使未投注也可随时提款（系统是从当天凌晨0点至第二天凌晨0点算一天）；</li>
			 <li>今日投注<span class="color green"><?php echo $info['amount_bets'];?></span>元，今日充值<span class="color blue"><?php echo $info['amount_recharge'];?></span>元，您今日消费比例已达到<span class="color red"><?php echo $info['proportion'];?>%</span>；</li>
		</ul>
	</div>
</div>
<div id="cash-log" class="common">
	<div class="head">
		<div class="name icon-paper-plane">提现记录</div>
		<form action="/user/cash_search" class="search" data-ispage="true" container="#cash-log .body" target="ajax" func="form_submit">
			<div class="timer">
				<input type="text" autocomplete="off" name="fromTime" value="<?php echo date('Y-m-d H:i', $this->request_time_from);?>" id="datetimepicker_fromTime" class="timer">
				<span class="icon icon-calendar"></span>
			</div>
			<div class="sep icon-exchange"></div>
			<div class="timer">
				<input type="text" autocomplete="off" name="toTime" value="<?php echo date('Y-m-d H:i', $this->request_time_to);?>" id="datetimepicker_toTime" class="timer">
				<span class="icon icon-calendar"></span>
			</div>
			<button type="submit" class="btn btn-brown icon-search">查询</button>
		</form>
	</div>
	<div class="body"><?php require(TPL.'/user/cash_body.tpl.php');?></div>
</div>
<script type="text/javascript">
$(function() {
	$('#home').removeClass('on');
	$('#user-cash').addClass('on');
	// 菜单下拉固定
	$.scroll_fixed('#cash-log .head');
	// 修改银行信息
	var cash_type = $('#cash-type');
	var cash_type_hover = cash_type.find('.hover');
	cash_type.hover(function() {
		cash_type_hover.animate({'top': 0});
	}, function() {
		cash_type_hover.animate({'top': '41px'});
	});
	cash_type.bind('click', function() {
		$('#user-setting').trigger('click');
	});
	// 输入框焦点效果
	$('#input-money,#input-password').focus(function() {
		$(this).parent().addClass('focus');
	}).blur(function() {
		$(this).parent().removeClass('focus');
	});
	// 时间选择插件
	$('#datetimepicker_fromTime,#datetimepicker_toTime').datetimepicker(datetimepicker_opt);
});
</script>