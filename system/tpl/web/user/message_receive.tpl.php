<div id="message-receive-dom" class="common">
	<div class="head">
		<div class="name icon-mail-alt">私信</div>
		<form action="/user/message_receive_search" class="search" data-ispage="true" container="#message-receive-dom .body" target="ajax" func="form_submit">
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
			<span>收件箱</span>
			<a href="/user/message_send" target="ajax" func="loadpage">发件箱</a>
			<a href="/user/message_write" target="ajax" func="loadpage">编写私信</a>
		</div>
	</div>
	<div class="body"><?php require(TPL.'/user/message_receive_body.tpl.php');?></div>
</div>
<script type="text/javascript">
$(function() {
	$('#home').removeClass('on');
	$('#message-receive').addClass('on');
	// 其他选择
	$('#message-receive-dom select.cs-select').each(function() {
		new SelectFx(this);
	});
	// 菜单下拉固定
	$.scroll_fixed('#message-receive-dom .head');
	// 时间选择插件
	$('#datetimepicker_fromTime,#datetimepicker_toTime').datetimepicker(datetimepicker_opt);
});
</script>