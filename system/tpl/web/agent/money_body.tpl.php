<?php
	if ($total) {
?>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr class="title">
		<td>用户名</td>
		<td>总收入</td>
		<td>总支出</td>
		<td>总结余</td>
		<td>查看</td>
	</tr>
	<?php
		$count = array(
			'income' => 0,
			'expenditure' => 0,
			'total' => 0,
		);
		foreach ($data as $v) {
			$v['total'] = $v['income'] + $v['expenditure'];
			$count['income'] += $v['income'];
			$count['expenditure'] += $v['expenditure'];
			$count['total'] += $v['total'];
	?>
	<tr>
		<td><?php echo $v['username'];?></td>
		<td><?php echo $v['income'] ? number_format($v['income'], 3, '.', '') : '0.000';?></td>
		<td><?php echo $v['expenditure'] ? number_format($v['expenditure'], 3, '.', '') : '0.000';?></td>
		<td><?php echo $v['total'] ? number_format($v['total'], 3, '.', '') : '0.000';?></td>
		<td>
			<a href="/agent/money?parentId=<?php echo $v['uid'];?>&fromTime=<?php echo date('Y-m-d H:i', $this->request_time_from);?>&toTime=<?php echo date('Y-m-d H:i', $this->request_time_to);?>" container="#agent-money-dom .body" data-ispage="true" target="ajax" func="loadpage" class="icon-download">下级</a>
			<?php if($v['uid'] != $this->user['uid'] && $v['parentId']) {?>
			  <a href="/agent/money?uid=<?php echo $v['uid'];?>&fromTime=<?php echo date('Y-m-d H:i', $this->request_time_from);?>&toTime=<?php echo date('Y-m-d H:i', $this->request_time_to);?>" container="#agent-money-dom .body" data-ispage="true" target="ajax" func="loadpage" class="icon-upload">上级</a>
			<?php }?>
		</td>
	</tr>
	<?php }?>
	<tr>
		<td>本页总结</td>
		<td><?php echo $count['income'] ? number_format($count['income'], 3, '.', '') : '0.000';?></td>
		<td><?php echo $count['expenditure'] ? number_format($count['expenditure'], 3, '.', '') : '0.000';?></td>
		<td><?php echo $count['total'] ? number_format($count['total'], 3, '.', '') : '0.000';?></td>
		<td>--</td>
	</tr>
	<tr>
		<td><?php
			switch ($args['a_type']) {
				case 0: echo '团队总结'; break;
				case 1: echo '直属下级'; break;
				case 2: echo '所有下级'; break;
				default: echo '--';
			}
		?></td>
		<td><?php echo $all['income'] ? number_format($all['income'], 3, '.', '') : '0.000';?></td>
		<td><?php echo $all['expenditure'] ? number_format($all['expenditure'], 3, '.', '') : '0.000';?></td>
		<td><?php echo $all['total'] ? number_format($all['total'], 3, '.', '') : '0.000';?></td>
		<td>--</td>
	</tr>
</table>
<?php } else {?>
<div class="empty"></div>
<?php }?>
<?php require(TPL.'/page.tpl.php');?>