<div id="recharge-panel" class="money-panel">
	<div class="main">
		<form action="/user/pay" id="recharge-form" method="post" target="_blank" func="form_submit">
		<input type="hidden" id="bank-id" name="bankid" value="<?php echo $bank_default['id'];?>">
		<div id="recharge-type" class="type">
			<div id="recharge-current" class="current"><img width="88" height="35" src="/static/image/bank/bank_<?php echo $bank_default['id'];?>.jpg" title="<?php echo $bank_default['name'];?>"></div>
			<span class="choose icon-down-dir">切换</span>
			<span class="hover icon-down-dir">点击切换银行</span>
		</div>
		<div class="input mr15">
			<span class="icon icon-yen"></span>
			<input autocomplete="off" name="amount" required="required" type="text" id="input-money" min="<?php echo $this->config['rechargeMin'];?>" max="<?php echo $this->config['rechargeMax'];?>" placeholder="请输入您的充值金额，最低：<?php echo $this->config['rechargeMin'];?>元，最高：<?php echo $this->config['rechargeMax'];?>元">
		</div>
		<button type="submit" class="submit btn btn-blue icon-ok">充值</button>
		</form>
	</div>
	<div id="bank-list" class="addon hide">
		<?php foreach ($banks as $bank) {?>
		<img width="103" height="41" class="trans<?php if($bank['id'] === $bank_default['id']) echo ' active';?>" src="/static/image/bank/bank_<?php echo $bank['id'];?>.jpg" title="<?php echo $bank['name'];?>" data-id="<?php echo $bank['id'];?>">
		<?php }?>
	</div>
</div>
<div id="recharge-log" class="common">
	<div class="head">
		<div class="name icon-credit-card">充值记录</div>
		<form action="/user/recharge_search" class="search" data-ispage="true" container="#recharge-log .body" target="ajax" func="form_submit">
			<div class="timer">
				<input autocomplete="off" type="text" name="fromTime" value="<?php echo date('Y-m-d H:i', $this->request_time_from);?>" id="datetimepicker_fromTime" class="timer">
				<span class="icon icon-calendar"></span>
			</div>
			<div class="sep icon-exchange"></div>
			<div class="timer">
				<input autocomplete="off" type="text" name="toTime" value="<?php echo date('Y-m-d H:i', $this->request_time_to);?>" id="datetimepicker_toTime" class="timer">
				<span class="icon icon-calendar"></span>
			</div>
			<button type="submit" class="btn btn-brown icon-search">查询</button>
		</form>
	</div>
	<div class="body"><?php require(TPL.'/user/recharge_body.tpl.php');?></div>
</div>
<script type="text/javascript">
$(function() {
	$('#home').removeClass('on');
	$('#user-recharge').addClass('on');
	// 菜单下拉固定
	$.scroll_fixed('#recharge-log .head');
	// 切换银行
	var recharge_type = $('#recharge-type');
	var recharge_type_hover = recharge_type.find('.hover');
	var recharge_type_choose = recharge_type.find('.choose');
	var bank_id = $('#bank-id');
	var bank_list = $('#bank-list');
	var recharge_form = $('#recharge-form');
	var recharge_current_img = $('#recharge-current img');
	recharge_type.hover(function() {
		recharge_type_hover.animate({'top': 0});
	}, function() {
		recharge_type_hover.animate({'top': '41px'});
	});
	recharge_type.bind('click', function() {
		if (bank_list.is(':hidden')) {
			bank_list.slideDown();
			recharge_type_choose.removeClass('icon-down-dir').addClass('icon-up-dir').text('收起');
			recharge_type_hover.removeClass('icon-down-dir').addClass('icon-up-dir').text('点击收起银行');
		} else {
			bank_list.slideUp();
			recharge_type_choose.removeClass('icon-up-dir').addClass('icon-down-dir').text('切换');
			recharge_type_hover.removeClass('icon-up-dir').addClass('icon-down-dir').text('点击切换银行');
		}
	});
	bank_list.find('img').bind('click', function() {
		recharge_current_img.attr('src', $(this).attr('src'));
		$(this).addClass('active').siblings().removeClass('active');
		bank_id.val($(this).data('id'));
		if ($(this).data('id') == 2) {
			recharge_form.attr('target', 'ajax');
		} else {
			recharge_form.attr('target', '_blank');
		}
	});
	// 输入框焦点效果
	$('#input-money').focus(function() {
		$(this).parent().addClass('focus');
	}).blur(function() {
		$(this).parent().removeClass('focus');
	});
	// 时间选择插件
	$('#datetimepicker_fromTime,#datetimepicker_toTime').datetimepicker(datetimepicker_opt);
});
</script>