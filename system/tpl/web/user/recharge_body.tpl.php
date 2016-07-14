<?php if ($data) {?>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr class="title">
		<td>充值编号</td>
		<td>充值金额</td>
		<td>实际到账</td>
		<td>充值银行</td>
		<td>充值状态</td>
		<td>成功时间</td>
	</tr>
	<?php foreach ($data as $v) {?>
	<tr>
		<td><?php echo $v['rechargeId'];?></td>
		<td><?php echo $v['amount'];?></td>
		<td><?php echo $v['rechargeAmount'] > 0 ? $v['rechargeAmount'] : '--';?></td>
		<td><?php echo $v['bankName'] ? $v['bankName'] : '--';?></td>
		<td><?php echo $v['state'] ? '充值成功' : '<span class="green">正在处理</span>';?></td>
		<td><?php echo $v['state'] ? date('m-d H:i:s', $v['actionTime']) : '--';?></td>
	</tr>
	<?php }?>
</table>
<?php } else {?>
<div class="empty"></div>
<?php }?>
<?php require(TPL.'/page.tpl.php');?>