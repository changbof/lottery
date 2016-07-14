<div id="message-send-dom" class="common">
	<div class="head">
		<div class="name icon-mail-alt">私信</div>
		<form action="/user/message_send_search" class="search" data-ispage="true" container="#message-send-dom .body" target="ajax" func="form_submit">
			<div class="select-box mode">
				<select name="state" class="cs-select state">
					<option value="0"<?php if ($state === 0) echo ' selected';?>>所有</option>
					<option value="1"<?php if ($state === 1) echo ' selected';?>>未读</option>
					<option value="2"<?php if ($state === 2) echo ' selected';?>>已读</option>
				</select>
			</div>
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
		<div class="tab">
			<a href="/user/message_receive" target="ajax" func="loadpage">收件箱</a>
			<span>发件箱</span>
			<a href="/user/message_write" target="ajax" func="loadpage">编写私信</a>
		</div>
	</div>
	<div class="body"><?php require(TPL.'/user/message_send_body.tpl.php');?></div>
</div>
<script type="text/javascript">
$(function() {
	$('#home').removeClass('on');
	$('#message-receive').addClass('on');
	// 全选
	$('.message_check').live('click', function() {
		var checked = $(this).attr('checked') === 'checked' ? true : false;
		$('input[name="check"]').each(function() {
			if (checked) {
				if ($(this).attr('checked') !== 'checked') $(this).attr('checked', 'checked');
			} else {
				if ($(this).attr('checked') === 'checked') $(this).removeAttr('checked');
			}
		});
	});
	// 删除选中条目
	window.message_delete = function() {
		var ids = [];
		$('input[name="check"]').each(function() {
			if ($(this).attr('checked') === 'checked') ids.push($(this).val());
		});
		$.load('/user/message_delete', '', {}, {ids: ids});
	};
	// 其他选择
	$('#message-send-dom select.cs-select').each(function() {
		new SelectFx(this);
	});
	// 菜单下拉固定
	$.scroll_fixed('#message-send-dom .head');
	// 时间选择插件
	$('#datetimepicker_fromTime,#datetimepicker_toTime').datetimepicker(datetimepicker_opt);
});
</script>