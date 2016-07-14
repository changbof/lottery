<?php
	if ($data) {
?>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr class="title">
		<td>状态</td>
		<td>主题</td>
		<td>发件人</td>
		<td>时间</td>
	</tr>
	<?php foreach ($data as $v) {?>
	<tr id="m-<?php echo $v['id'];?>">
		<td class="state"><?php echo $v['is_readed'] ? '<span class="green">已读</span>' : '<span class="red">未读</span>';?></td>
		<td><a href="/user/message_receive_content?id=<?php echo $v['id'];?>" target="ajax" func="loadpage"><?php echo $v['title'];?></a></td>
		<td><?php echo htmlspecialchars($v['from_username']);?></td>
		<td><?php echo date('Y-m-d H:i',$v['time']);?></td>
	</tr>
	<?php }?>
</table>
<?php } else {?>
<div class="empty"></div>
<?php }?>
<?php require(TPL.'/page.tpl.php');?>