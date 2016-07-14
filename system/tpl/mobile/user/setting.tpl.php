<div id="user-setting-dom" class="common">
	<div class="head">
		<div class="name icon-user">个人基本信息</div>
		<div class="ulogout">
			<a href="/user/logout" class="gray" target="ajax" func="loadpage">
				<span class="icon icon-off"></span><span class="text">退出</span>
			</a>
		</div>
	</div>
	<div class="body">
		<div class="info" id="user-info">
			<div class="username"><span title="---" class="unval icon-user">彩神大名：---</span><span class="level">0</span></div>
			<div class="balance icon-fire-1">可用余额：00.000</div>
			<div class="score icon-certificate">账户积分：0</div>
		</div>
	</div>
	<div class="head">
		<div class="name icon-key">密码管理[登录密码]</div>
	</div>
	<div class="body password">
		<form method="POST" action="/user/setting_login_password" target="ajax" func="form_submit" class="mb">
			<div class="input trans">
				<span class="icon icon-edit"></span>
				<input type="password" name="oldpassword" placeholder="请输入当前密码" />
			</div>
			<div class="input trans">
				<span class="icon icon-key"></span>
				<input type="password" name="newpassword" placeholder="请输入新密码]" />
			</div>
			<div class="input trans">
				<span class="icon icon-key"></span>
				<input type="password" name="newpassword_confirm" placeholder="重复输入新密码" />
			</div>
			<div class="bottom">
				<button type="submit" class="btn btn-blue"><span>确认修改</span></button>
			</div>
		</form>
	</div>
</div>
<script type="text/javascript">
$(function() {
	$('#user-setting').addClass('on');
	// 更新用户信息
	lottery.user_fresh();
	
});
</script>