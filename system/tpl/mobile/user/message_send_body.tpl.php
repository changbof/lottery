<?php
	if ($data) {
?>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr class="title">
		<td>选择</td>
		<td>状态</td>
		<td>主题</td>
		<td>收件人</td>
		<td>时间</td>
	</tr>
	<?php foreach ($data as $v) {?>
	<tr id="m-<?php echo $v['id'];?>">
		<td><input type="checkbox" value="<?php echo $v['id'];?>" name="check"></td>
		<td class="state"><?php echo $v['is_readed'] ? '<span class="green">已读</span>' : '<span class="red">未读</span>';?></td>
		<td><a href="/user/message_send_content?id=<?php echo $v['id'];?>" target="ajax" func="loadpage"><?php echo $v['title'];?></a></td>
		<td><?php echo htmlspecialchars($v['to_username']);?></td>
		<td><?php echo date('Y-m-d H:i',$v['time']);?></td>
	</tr>
	<?php }?>
	<tr>
		<td><input type="checkbox" class="message_check"></td>
		<td colspan="4"><a href="javascript:message_delete();">删除选中条目</a></td>
	</tr>
</table>
<?php } else {?>
<div class="empty"></div>
<?php }?>
<?php require(TPL.'/page.tpl.php');?>