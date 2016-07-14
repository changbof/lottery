<?php if ($bets_recent) {?>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr class="head">
		<td>投注时间</td>
		<td>彩种</td>
		<td>期号</td>
		<td>总额(元)</td>
		<td>开奖号码</td>
		<td>状态</td>
	</tr>
	<?php foreach($bets_recent as $var){ ?>
		<tr>
			<td><?php echo date('H:i:s', $var['actionTime']);?></td>
			<td><?php echo $all_plays[$var['playedId']]['name'];?></td>
			<td><?php echo $var['actionNo'];?></td>
			<td><?php echo $var['mode'] * $var['beiShu'] * $var['actionNum'];?></td>
			<td class="lotteryNo" title="<?php echo $var['lotteryNo'] ? $var['lotteryNo'] : '--';?>"><?php echo $var['lotteryNo'] ? $var['lotteryNo'] : '--';?></td>
			<td>
			<?php
				if ($var['isDelete'] ==1) {
					echo '<span class="gray">已撤单</span>';
				} else if (!$var['lotteryNo']) {
					echo '<span class="green">未开奖</span>';
				} else if ($var['zjCount']) {
					echo '<span class="red">已派奖</span>';
				} else {
					echo '未中奖';
				}
			?>
			</td>
		</tr>
	<?php }?>
</table>
<?php } else {?>
<div class="none">您还没有当前彩种的近期投注记录</div>
<?php }?>