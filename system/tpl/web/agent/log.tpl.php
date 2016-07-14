<div id="agent-log-dom" class="common">
	<div class="head">
		<div class="name icon-th-list">团队记录</div>
		<form action="/agent/log_search" class="search" data-ispage="true" container="#agent-log-dom .body" target="ajax" func="form_submit">
			<select name="type">
				<option>选择彩种</option>
				<?php foreach ($_types as $n => $vs) {?>
				<option disabled><?php echo $n;?></option>
				<?php foreach ($vs as $v) {?>
				<option value="<?php echo $v['id'];?>"<?php if ($args['type'] == $v['id']) echo ' selected';?>>&nbsp;&nbsp;&nbsp;|--<?php echo $v['title'];?></option>
				<?php }?>
				<?php }?>
			</select>
			<div class="select-box">
				<select name="state" class="cs-select">
					<?php foreach ($state as $k => $v) {?>
					<option value="<?php echo $k;?>"<?php if ($k == $args['state']) echo ' selected';?>><?php echo $v;?></option>
					<?php }?>
				</select>
			</div>
			<div class="select-box mode">
				<select name="a_type" class="cs-select mode">
					<option value="0"<?php if ($args['a_type'] == 0) echo ' selected';?>>所有成员</option>
					<option value="1"<?php if ($args['a_type'] == 1) echo ' selected';?>>直属下级</option>
					<option value="2"<?php if ($args['a_type'] == 2) echo ' selected';?>>所有下级</option>
				</select>
			</div>
			<input type="text" name="username" value="<?php echo $args['username'] ? $args['username'] : '用户名';?>" class="input" style="width:40px" onfocus="if(this.value==='用户名') this.value='';" onblur="if (this.value==='') this.value='用户名';">
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
	<div class="body"><?php require(TPL.'/agent/log_body.tpl.php');?></div>
</div>
<script type="text/javascript">
$(function() {
	$('#home').removeClass('on');
	$('#agent-log').addClass('on');
	$('#agent').addClass('on');
	// 其他选择
	$('#agent-log-dom select.cs-select').each(function() {
		new SelectFx(this);
	});
	// 菜单下拉固定
	$.scroll_fixed('#agent-log-dom .head');
	// 时间选择插件
	$('#datetimepicker_fromTime,#datetimepicker_toTime').datetimepicker(datetimepicker_opt);
});
</script>