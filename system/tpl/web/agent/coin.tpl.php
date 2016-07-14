<div id="coin-panel" class="panel-head">
	<div class="warp">
		<?php foreach ($this->coin_type_data as $n => $vs) {?>
		<div class="item">
			<div class="item-name icon-right-dir"><?php echo $n;?></div>
			<div class="item-data">
				<?php foreach ($vs as $k => $v) {?>
				<a href="javascript:;" data-type="<?php echo $k;?>"<?php if ($args['type'] === $k) echo ' class="active"';?>><?php echo $v;?></a>
				<?php }?>
			</div>
		</div>
		<?php }?>
	</div>
	<span class="triangle"></span>
</div>
<div id="agent-coin-dom" class="common">
	<div class="head">
		<div class="name icon-chart-bar">帐变日志</div>
		<form action="/agent/coin_search" class="search" data-ispage="true" container="#agent-coin-dom .body" target="ajax" func="form_submit">
			<div class="select trans fixed" id="select-type">
				<input type="hidden" name="type" id="input-type" value="<?php echo $args['type'];?>">
				<span class="icon icon-dot-circled"></span>
				<span class="text" id="text-type"><?php echo $args['type'] ? $this->coin_types[$args['type']] : '请选择帐变类型';?></span>
			</div>
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
	<div class="body"><?php require(TPL.'/agent/coin_body.tpl.php');?></div>
</div>
<script type="text/javascript">
$(function() {
	$('#home').removeClass('on');
	$('#agent-coin').addClass('on');
	$('#agent').addClass('on');
	// 帐变类型选择 
	var coin_panel_warp = $('#coin-panel .warp');
	var select_type = $('#select-type');
	var input_type = $('#input-type');
	var text_type = $('#text-type');
	var selecting = false;
	select_type.bind('click', function() {
		if (selecting) return;
		selecting = true;
		var times = 0;
		var change_active = function() {
			for (var i=1;6>=i;i++) {
				coin_panel_warp.animate({left: 13}, 50);
				coin_panel_warp.animate({left: 17}, 50);
			}
			setTimeout(function() {
				selecting = false;
			}, 300);
		};
		$('body').animate({scrollTop:0}, 500, change_active);
	});
	coin_panel_warp.find('a').bind('click', function() {
		if ($(this).hasClass('active')) {
			$(this).removeClass('active');
			input_type.val(0);
			text_type.text('请选择帐变类型');
		} else {
			input_type.val($(this).data('type'));
			text_type.text($(this).text());
			coin_panel_warp.find('a.active').removeClass('active');
			$(this).addClass('active');
		}
	});
	// 其他选择
	$('#agent-coin-dom select.cs-select').each(function() {
		new SelectFx(this);
	});
	// 菜单下拉固定
	$.scroll_fixed('#agent-coin-dom .head');
	// 时间选择插件
	$('#datetimepicker_fromTime,#datetimepicker_toTime').datetimepicker(datetimepicker_opt);
});
</script>