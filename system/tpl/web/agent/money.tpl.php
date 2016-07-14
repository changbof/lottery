<div id="agent-money-dom" class="common">
	<div class="head">
		<div class="name icon-yen">盈亏统计</div>
		<form action="/agent/money_search" class="search" container="#agent-money-dom .body" data-ispage="true" target="ajax" func="form_submit">
			<div class="select-box mode">
				<select name="a_type" class="cs-select mode">
					<option value="0"<?php if ($args['a_type'] == 0) echo ' selected';?>>所有成员</option>
					<option value="1"<?php if ($args['a_type'] == 1) echo ' selected';?>>直属下级</option>
					<option value="2"<?php if ($args['a_type'] == 2) echo ' selected';?>>所有下级</option>
				</select>
			</div>
			<input type="text" name="username" value="<?php echo $args['username'] ? $args['username'] : '用户名';?>" class="input" style="width:100px" onfocus="if(this.value==='用户名') this.value='';" onblur="if (this.value==='') this.value='用户名';">
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
	<div class="body"><?php require(TPL.'/agent/money_body.tpl.php');?></div>
</div>
<script type="text/javascript">
$(function() {
	$('#home').removeClass('on');
	$('#agent-money').addClass('on');
	$('#agent').addClass('on');
	// 其他选择
	$('#agent-money-dom select.cs-select').each(function() {
		new SelectFx(this);
	});
	// 菜单下拉固定
	$.scroll_fixed('#agent-money-dom .head');
	// 时间选择插件
	$('#datetimepicker_fromTime,#datetimepicker_toTime').datetimepicker(datetimepicker_opt);
});
</script>