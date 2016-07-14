<?php
	if ($data) {
		$stateName = array(
			'已到帐',
			'<span class="green">处理中</span>',
			'已取消',
			'已支付',
			'<span class="red">失败</span>',
		);
?>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr class="title">
		<td>提现编号</td>
		<td>提现金额</td>
		<td>申请时间</td>
		<td>提现银行</td>
		<td>银行尾号</td>
		<td>提现状态</td>
		<td>提现备注</td>
	</tr>
	<?php foreach ($data as $v) {?>
	<tr>
		<td><?php echo $v['id'];?></td>
		<td><?php echo $v['amount'];?></td>
		<td><?php echo date('Y-m-d H:i:s', $v['actionTime']);?></td>
		<td><?php echo $v['bankName'];?></td>
		<td><?php echo preg_replace('/^.*(.{4})$/', "$1", $v['account']);?></td>
		<td><?php echo $stateName[$v['state']];?></td>
		<td><?php echo $v['info'] ? $v['info'] : '--';?></td>
	</tr>
	<?php }?>
</table>
<?php } else {?>
<div class="empty"></div>
<?php }?>
<?php require(TPL.'/page.tpl.php');?>