<div id="user-setting-dom" class="common">
	<div class="head">
		<div class="name icon-user">个人基本信息</div>
	</div>
	<div class="body">
		<table width="100%" border="0" cellspacing="0" cellpadding="0">
			<tr>
				<td class="key">账户名称</td>
				<td class="val"><?php echo htmlspecialchars($this->user['username']);?></td>
				<td class="key">账户等级</td>
				<td class="val">VIP<?php echo $this->user['grade'];?></td>

			</tr>
			<tr>
				<td class="key">账户类型</td>
				<td class="val"><?php echo $this->user['type'] ? '代理' : '会员';?></td>
				<td class="key">可用积分</td>
				<td class="val"><?php echo $this->user['score'];?></td>
			</tr>
			<tr>
				<td class="key">可用资金</td>
				<td class="val"><?php echo $this->user['coin'];?> 元</td>
				<td class="key">返点比例</td>
				<td class="val"><?php echo $this->user['fanDian'];?> %</td>
			</tr>
			<tr>
				<td class="key">腾讯QQ</td>
				<td class="val"><?php echo $this->user['qq'];?></td>
				<td class="key">注册时间</td>
				<td class="val"><?php echo date('Y-m-d H:i:s', $this->user['regTime']);?></td>
			</tr>
			<tr>
				<td class="key">最后登录</td>
				<td class="val"><?php echo $this->user['updateTime'];?></td>
				<td class="key"></td>
				<td class="val"></td>
			</tr>
		</table>
	</div>
	<div class="head">
		<div class="name icon-key">密码管理</div>
		<div class="desc">如果不修改密码，请忽略此项</div>
	</div>
	<div class="body password">
		<form method="POST" action="/user/setting_login_password" target="ajax" func="form_submit" class="mb">
			<div class="pwd_name">登录密码：</div>
			<input type="password" name="oldpassword" placeholder="请输入[当前登录密码]">
			<input type="password" name="newpassword" placeholder="请输入[新登录密码]">
			<input type="password" name="newpassword_confirm" placeholder="请重复输入[新登录密码]">
			<button type="submit" class="btn btn-blue">修改登录密码</button>
		</form>
		<form method="POST" action="/user/setting_coin_password" target="ajax" func="form_submit">
			<div class="pwd_name">资金密码：</div>
			<?php if (empty($this->user['coinPassword'])) {?>
			<input type="password" name="oldpassword" placeholder="[资金密码]未设置，此项不需填写" readonly>
			<?php } else {?>
			<input type="password" name="oldpassword" placeholder="请输入[当前资金密码]">
			<?php }?>
			<input type="password" name="newpassword" placeholder="请输入[新资金密码]">
			<input type="password" name="newpassword_confirm" placeholder="请重复输入[新资金密码]">
			<button type="submit" class="btn btn-green">修改资金密码</button>
		</form>
	</div>
	<div class="head">
		<div class="name icon-credit-card">银行账户</div>
		<div class="desc">为了您的账户安全，确认银行账户后只能通过联系客服修改</div>
	</div>
	<div class="body card">
		<?php if (empty($this->user['coinPassword'])) {?>
		<div class="card-tip">设置[资金密码]之后您才可以设置您的[银行账户]信息</div>
		<?php } else {?>
		<form method="POST" action="/user/setting_bank" target="ajax" func="form_submit">
		<table width="100%" border="0" cellspacing="0" cellpadding="0">
			<tr>
				<td class="key">银行类型</td>
				<td class="val">
					<?php
						$uid = $this->user['uid'];
						$bank_me = $this->db->query("SELECT * FROM `{$this->db_prefix}member_bank` WHERE `uid`=$uid LIMIT 1", 2);
						if ($bank_me) {
							$bank_id = $bank_me['bankId'];
							$disabled = true;
						} else {
							$bank_id = 0;
							$disabled = false;
						}
						$bank_id = $bank_me ? $bank_me['bankId'] : 0;
						$bank_list = $this->db->query("SELECT * FROM `{$this->db_prefix}bank_list` WHERE `isDelete`=0 ORDER BY `sort` DESC", 3);
					?>
					<?php if ($disabled) {?>
						<?php
							foreach($bank_list as $bank) {
								if ($bank_id == $bank['id']) {
									echo $bank['name'];
									break;
								}
							}
						?>
					<?php } else {?>
					<select name="bankId">
						<?php foreach($bank_list as $bank){ ?>
						<option value="<?php echo $bank['id'];?>" <?php echo $bank_id == $bank['id'] ? 'selected' : '';?>><?php echo $bank['name'];?></option>
						<?php } ?>
					</select>
					<?php }?>
				</td>
				<td class="key">银行账户</td>
				<td class="val">
				<?php if ($disabled) {?>
				<?php echo preg_replace('/^(\w{4}).*(\w{4})$/', '\1***\2', htmlspecialchars($bank_me['account']));?>
				<?php } else {?>
				<input type="text" name="account" placeholder="请输入[银行账户]">
				<?php }?>
				</td>
			</tr>
			<tr>
				<td class="key">银行户名</td>
				<td class="val">
				<?php if ($disabled) {?>
				<?php echo '*'.mb_substr(htmlspecialchars($bank_me['username']), 1);?>
				<?php } else {?>
				<input type="text" name="username" placeholder="请输入[银行户名]">
				<?php }?>
				</td>
				<td class="key">开户行</td>
				<td class="val">
				<?php if ($disabled) {?>
				<?php echo preg_replace('/^(\w{4}).*(\w{4})$/', '\1***\2', htmlspecialchars($bank_me['countname']));?>
				<?php } else {?>
				<input type="text"  name="countname" placeholder="请输入[开户行]"></td>
				<?php }?>
			</tr>
			<?php if ($disabled) {?>
			<tr>
				<td class="key">更新时间</td>
				<td class="val"><?php echo date('Y-m-d H:i:s', $bank_me['bdtime']);?></td>
				<td></td>
				<td></td>
			</tr>
			<?php } else {?>
			<tr>
				<td class="key">资金密码</td>
				<td class="val"><input type="password" name="coinPassword" placeholder="请输入[资金密码]"></td>
				<td></td>
				<td></td>
			</tr>
			<?php }?>
		</table>
		<?php if (!$disabled) {?>
		<button type="submit" class="btn btn-brown">设置银行账户</button>
		<?php }?>
		</form>
		<?php }?>
	</div>
</div>
<script type="text/javascript">
$(function() {
	$('#home').removeClass('on');
	$('#user-setting').addClass('on');
});
</script>