<div id="sys-notice-dom" class="common">
	<div class="head">
		<div class="name icon-bell-alt">系统公告</div>
	</div>
	<div class="body"><?php require(TPL.'/sys/notice_list_body.tpl.php');?></div>
</div>
<script type="text/javascript">
$(function() {
	$('#home').removeClass('on');
	$('#sys-notice').addClass('on');
	// 菜单下拉固定
	$.scroll_fixed('#sys-notice-dom .head');
});
</script>