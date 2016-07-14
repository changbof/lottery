<div id="agent-member-dom" class="common">
	<div class="head">
		<div class="name icon-address-book">会员管理</div>
		<form action="/agent/member_search" class="search" data-ispage="true" container="#agent-member-dom .body" target="ajax" func="form_submit">
			<div class="select-box mode">
				<select name="type" class="cs-select mode">
					<option value="0"<?php if ($args['type'] == 0) echo ' selected';?>>所有成员</option>
					<option value="1"<?php if ($args['type'] == 1) echo ' selected';?>>直属下级</option>
					<option value="2"<?php if ($args['type'] == 2) echo ' selected';?>>所有下级</option>
				</select>
			</div>
			<div class="select-box state">
				<select name="online" class="cs-select state">
					<option<?php if ($args['online'] != 0 && $args['online'] != 1) echo ' selected';?>>状态</option>
					<option value="0"<?php if ($args['online'] == 0) echo ' selected';?>>在线</option>
					<option value="1"<?php if ($args['online'] == 1) echo ' selected';?>>离线</option>
				</select>
			</div>
			<input type="text" name="username" value="<?php echo $args['username'] ? $args['username'] : '用户名';?>" class="input" style="width:100px" onfocus="if(this.value==='用户名') this.value='';" onblur="if (this.value==='') this.value='用户名';">
			<button type="submit" class="btn btn-brown icon-search">查询</button>
		</form>
		<a href="javascript:;" class="member_add btn btn-green icon-plus">添加会员</a>
	</div>
	<form class="member_add_box hide" action="/agent/member_add" target="ajax" func="form_submit">
		<div class="item">
			<div class="name">用户名</div>
			<div class="value fandian">
				<input type="text" name="username" required title="用户名" placeholder="请输入用户名" style="width:300px">
			</div>
		</div>
		<div class="item">
			<div class="name">登录密码</div>
			<div class="value fandian">
				<input type="text" name="password" required title="登录密码" placeholder="请输入登录密码" style="width:300px">
			</div>
		</div>
		<div class="item">
			<div class="name">腾讯QQ</div>
			<div class="value fandian">
				<input type="text" name="qq" required title="腾讯QQ" placeholder="请输入腾讯QQ" style="width:300px">
			</div>
		</div>
		<div class="item">
			<div class="name">会员类型</div>
			<div class="value type">
				<label><input type="radio" name="type" value="1" title="代理" checked="checked">代理</label>
				<label><input name="type" type="radio" value="0" title="会员">会员</label>
			</div>
		</div>
		<div class="item">
			<div class="name">用户返点</div>
			<div class="value fandian">
				<input type="text" name="fanDian" required title="用户返点" placeholder="投注返点最大值" style="width:90px">
			</div>
			<div class="addon name">%，设置上限：<?php echo $max;?></div>
		</div>
		<button type="submit" class="btn btn-blue icon-ok">确认添加</button>
	</form>
	<div class="body"><?php require(TPL.'/agent/member_body.tpl.php');?></div>
</div>
<script type="text/javascript">
$(function() {
	$('#home').removeClass('on');
	$('#agent-member').addClass('on');
	$('#agent').addClass('on');
	// 添加会员
	$('#agent-member-dom .member_add').bind('click', function() {
		$('#agent-member-dom .member_add_box').slideToggle();
	});
	// 其他选择
	$('#agent-member-dom select.cs-select').each(function() {
		new SelectFx(this);
	});
	// 菜单下拉固定
	$.scroll_fixed('#agent-member-dom .head');
});
</script>