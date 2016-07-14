<div id="sys-notice-dom" class="common">
	<div class="head">
		<div class="name icon-bell-alt">系统公告</div>
	</div>
	<div class="body"><?php require(TPL.'/sys/notice_list_body.tpl.php');?></div>
</div>
<script type="text/javascript">
$(function() {
	$('#system-notice').addClass('on');
});
</script>