<?php
	if ($data) {
?>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr class="title">
		<td>单号</td>
		<td>用户</td>
		<td>类型</td>
		<td>时间</td>
		<td>资金</td>
		<td>余额</td>
		<td>备注</td>
	</tr>
	<?php foreach ($data as $v) {?>
	<tr>
		<td>
		<?php
			if ($v['extfield0']) {
				if (in_array($v['liqType'], array(101, 108, 255, 6, 7, 102, 5, 11, 100, 10, 103, 104, 105, 2))) {
					echo '<a href="/bet/info?id='.$v['extfield0'].'" title="投注信息" target="ajax" func="loadpage">投注：'.$v['extfield0'].'</a>';
				} else if (in_array($v['liqType'], array(1, 9, 52, 54))) {
					echo '<a href="/user/recharge_info?id='.$v['extfield0'].'" title="充值信息" target="ajax" func="loadpage">充值：'.$v['extfield0'].'</a>';
				} else if (in_array($v['liqType'], array(8, 106, 107))) {
					echo '<a href="/user/cash_info?id='.$v['extfield0'].'" title="提现信息" target="ajax" func="loadpage">提现：'.$v['extfield0'].'</a>';
				} else {
					echo '--';
				}
			} else {
				echo '--';
			}
		?>
		</td>
		<td><?php
			if ($v['username']) {
				echo $v['username'];
			} else {
				echo $this->get_username($v['uid']);
			}
		?></td>
		<td><?php echo array_key_exists($v['liqType'], $this->coin_types) ? $this->coin_types[$v['liqType']] : '--';?></td>
		<td><?php echo date('Y-m-d H:i:s', $v['actionTime']);?></td>
        <td><?php $coin = number_format($v['coin'], 2, '.', '');echo $coin > 0 ? '<span class="green">'.$coin.'</span>' : '<span class="red">'.$coin.'</span>';?></td>
		<td><?php echo $v['userCoin'];?></td>
		<td><?php echo $v['info'] ? $v['info'] : '--';?></td>
	</tr>
	<?php }?>
</table>
<?php } else {?>
<div class="empty"></div>
<?php }?>
<?php require(TPL.'/page.tpl.php');?>