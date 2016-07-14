<div id="message-receive-dom" class="common">
	<div class="head">
		<div class="name icon-mail-alt">私信</div>
		<div class="tab">
			<a href="/user/message_receive" target="ajax" func="loadpage">收件箱</a>
			<a href="/user/message_send" target="ajax" func="loadpage">发件箱</a>
			<span>编写私信</span>
		</div>
	</div>
	<div class="body">
		<form action="/user/message_write_submit" class="search clearfix" target="ajax" func="form_submit">
			<dl>
				<dt>收件人：</dt>
				<dd style="height:40px;line-height:40px">
					<?php if ($uid >= 0) {?>
					<input type="hidden" name="touser" value="<?php echo $uid;?>">
					<?php echo $username;?>
					<?php } else {?>
					<?php if ($this->user['parentId']) {?>
					<label><input name="touser" value="parent" checked="checked" type="radio">上级代理</label>
					<label><input name="touser" value="children" type="radio">直属下级会员</label>
					<?php } else {?>
					<label><input name="touser" value="children" checked="checked" type="radio">直属下级会员</label>
					<?php }?>
					<?php }?>
				</dd>
			</dl>
			<dl>
				<dt>主&nbsp;&nbsp;&nbsp;题：</dt>
				<dd style="height:40px;line-height:40px"><input name="title" required style="padding:5px 10px;width:400px" type="text" placeholder="请输入私信主题"></dd>
			</dl>
			<dl>
				<dt>内&nbsp;&nbsp;&nbsp;容：</dt>
				<dd style="margin-top:17px"><textarea name="content" required style="width:700px;border:1px solid #ddd;color:#666;padding:10px;height:200px" placeholder="请输入私信内容"></textarea></dd>
			</dl>
			<button type="submit" class="btn btn-green icon-ok" style="float:left;width:722px;height:40px;line-height:40px;font-size:15px;margin:15px 0 20px 95px">发送</button>
		</form>
	</div>
</div>
<script type="text/javascript">
$(function() {
	$('#home').removeClass('on');
	$('#message-receive').addClass('on');
});
</script>