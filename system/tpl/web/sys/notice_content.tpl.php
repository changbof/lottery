<div id="sys-notice-dom" class="common">
	<div class="head">
		<div class="name icon-bell"><?php echo $data['title'];?></div>
		<div class="desc"><?php echo date('Y-m-d H:i', $data['addTime']);?></div>
	</div>
	<div class="body text">
		<pre><?php echo $data['content'];?></pre>
	</div>
	<a href="javascript:history.go(-1)" class="back icon-undo">返回通知列表</a>
</div>
<script type="text/javascript">
$(function() {
	$('#home').removeClass('on');
	$('#sys-notice').addClass('on');
});
</script>