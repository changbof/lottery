<?php if ($bets_recent) {?>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr class="head">
		<td class="select_all cs" choosed="false">全选</td>
		<td>编号</td>
		<td>投注时间</td>
		<td>彩种</td>
		<td>期号</td>
		<td>玩法</td>
		<td>倍数模式</td>
		<td>总额(元)</td>
		<td>奖金(元)</td>
		<td>开奖号码</td>
		<td>状态</td>
		<td>操作</td>
	</tr>
	<?php foreach($bets_recent as $var){ ?>
		<tr>
			
            <?php if ($var['lotteryNo'] || $var['isDelete'] == 1 || $var['kjTime'] < $this->time) { ?>
			<td>--</td>
			<?php } else { ?>
			<td class="select cs"><input type="checkbox" data-id="<?php echo $var['id'];?>"></td>
			<?php } ?>
			<td><a href="/bet/info?id=<?php echo $var['id'];?>" target="ajax" func="loadpage"><?php echo $var['wjorderId'];?></a></td>
			<td><?php echo date('m-d H:i:s', $var['actionTime']);?></td>
			<td><?php echo $types[$var['type']]['shortName'] ? $types[$var['type']]['shortName'] : $types[$var['type']]['title'];?></td>
			<td><?php echo $var['zhuiHao'] ? '<span class="t btn btn-red">追号</span>' : '<span class="t btn btn-green">直投</span>';?><?php echo $var['actionNo'];?></td>
			<td><?php echo $all_plays[$var['playedId']]['name'];?></td>
			<td><?php echo $var['beiShu'];?> [<?php echo $this->modes[$var['mode']];?>]</td>
			<td><?php echo $var['mode'] * $var['beiShu'] * $var['actionNum'];?></td>
			<td><?php echo $var['lotteryNo'] ? number_format($var['bonus'], 2, '.', '') : '0.00';?></td>
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
            <td>
            <?php if ($var['lotteryNo'] || $var['isDelete'] == 1 || $var['kjTime'] < $this->time) { ?>
				--
			<?php } else { ?>
				<a href="javascript:;" data-id="<?php echo $var['id'];?>" class="remove_single">撤单</a>
			<?php } ?>
            </td>
		</tr>
	<?php }?>
</table>
<?php } else {?>
<div class="none">您还没有当前彩种的近期投注记录</div>
<?php }?>