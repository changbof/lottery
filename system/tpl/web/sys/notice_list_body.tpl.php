<?php
	if ($data) {
?>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr class="title">
		<td class="tleft">通知标题</td>
		<td>发布时间</td>
	</tr>
	<?php
		foreach ($data as $v) {
	?>
	<tr>
		<td class="tleft"><a href="/sys/notice?type=content&id=<?php echo $v['id'];?>" target="ajax" func="loadpage"><?php echo $v['title'];?></a></td>
		<td><?php echo date('Y-m-d H:i', $v['addTime']);?></td>
	</tr>
	<?php }?>
</table>
<?php } else {?>
<div class="empty"></div>
<?php }?>
<?php require(TPL.'/page.tpl.php');?>