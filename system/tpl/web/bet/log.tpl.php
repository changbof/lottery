<div id="bet-log-dom" class="common">
	<div class="head">
		<div class="name icon-th-list">投注记录</div>
		<form action="/bet/log_search" class="search" data-ispage="true" container="#bet-log-dom .body" target="ajax" func="form_submit">
			<select name="type">
				<option>选择彩种</option>
				<?php foreach ($_types as $n => $vs) {?>
				<option disabled><?php echo $n;?></option>
				<?php foreach ($vs as $v) {?>
				<option value="<?php echo $v['id'];?>"<?php if ($args['type'] == $v['id']) echo ' selected';?>>&nbsp;&nbsp;&nbsp;|--<?php echo $v['title'];?></option>
				<?php }?>
				<?php }?>
			</select>
			<input type="text" name="betId" value="<?php echo $args['betId'] ? $args['betId'] : '投注编号';?>" class="input" style="width:65px" onfocus="if(this.value==='投注编号') this.value='';" onblur="if (this.value==='') this.value='投注编号';">
			<div class="select-box">
				<select name="state" class="cs-select">
					<?php foreach ($state as $k => $v) {?>
					<option value="<?php echo $k;?>"<?php if ($k == $args['state']) echo ' selected';?>><?php echo $v;?></option>
					<?php }?>
				</select>
			</div>
			<div class="select-box mode">
				<select name="mode" class="cs-select state">
					<?php
						$modes = array('0.000' => '模式');
						$modes = array_merge($modes, $this->modes);
						foreach ($modes as $k => $v) {
					?>
					<option value="<?php echo $k;?>"<?php if ($k == $args['mode']) echo ' selected';?>><?php echo $v;?></option>
					<?php }?>
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
	</div>
	<div class="body"><?php require(TPL.'/bet/log_body.tpl.php');?></div>
</div>
<script type="text/javascript">
$(function() {
	$('#home').removeClass('on');
	$('#bet-log').addClass('on');
	// 绑定撤单事件
	$('#bet-log-dom .remove_single').live('click', beter.remove_single);
	// 其他选择
	$('#bet-log-dom select.cs-select').each(function() {
		new SelectFx(this);
	});
	// 菜单下拉固定
	$.scroll_fixed('#bet-log-dom .head');
	// 时间选择插件
	$('#datetimepicker_fromTime,#datetimepicker_toTime').datetimepicker(datetimepicker_opt);
});
</script>