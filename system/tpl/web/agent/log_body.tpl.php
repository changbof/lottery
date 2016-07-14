<?php
	if ($data) {
?>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr class="title">
		<td>编号</td>
		<td>用户</td>
		<td>投注时间</td>
		<td>彩种</td>
		<td>期号</td>
		<td>玩法</td>
		<td>倍数模式</td>
		<td>总额(元)</td>
		<td>奖金(元)</td>
		<td>开奖号码</td>
		<td>状态</td>
	</tr>
	<?php
		foreach ($data as $v) {
			$this_type = $types[$v['type']];
	?>
	<tr>
		<td><a href="/bet/info?id=<?php echo $v['id'];?>" title="投注信息" target="ajax" func="loadpage"><?php echo $v['wjorderId'];?></a></td>
		<td><?php echo $v['username'];?></td>
		<td><?php echo date('m-d H:i:s', $v['actionTime']);?></td>
		<td><?php echo array_key_exists('shortName', $this_type) ? $this_type['shortName'] : $this_type['title'];?></td>
		<td><?php echo $v['actionNo'];?></td>
		<td><?php echo $plays[$v['playedId']]['name'];?></td>
		<td><?php echo $v['beiShu'];?> [<?php echo $this->modes[$v['mode']];?>]</td>
		<td><?php echo $v['mode'] * $v['beiShu'] * $v['actionNum'];?></td>
		<td><?php echo $v['lotteryNo'] ? number_format($v['bonus'], 2, '.', '') : '0.00';?></td>
		<td><?php echo $v['lotteryNo'] ? $v['lotteryNo'] : '--';?></td>
		<td><?php
			if ($v['isDelete'] == 1) {
				echo '<span class="gray">已撤单</span>';
			} elseif (!$v['lotteryNo']) {
				echo '<span class="green">未开奖</span>';
			}elseif($v['zjCount']){
				echo '<span class="red">已派奖</span>';
			}else{
				echo '未中奖';
			}
		?></td>
	</tr>
	<?php }?>
</table>
<?php } else {?>
<div class="empty"></div>
<?php }?>
<?php require(TPL.'/page.tpl.php');?>