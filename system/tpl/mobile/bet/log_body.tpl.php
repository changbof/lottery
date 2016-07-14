<?php if ($data) { ?>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr class="title">
		<td>投注时间</td>
		<td>彩种</td>
		<td>倍数模式</td>
		<td>总额(元)</td>
		<td>奖金(元)</td>
		<td>开奖号码</td>
	</tr>
	<?php
		foreach ($data as $v) {
			$this_type = $types[$v['type']];
	?>
	<tr>
		<td><?php echo date('H:i:s', $v['actionTime']);?></td>
		<td><?php echo array_key_exists('shortName', $this_type) ? $this_type['shortName'] : $this_type['title'];?>-<?php echo $plays[$v['playedId']]['name'];?></td>
		<td><?php echo $v['beiShu'];?> [<?php echo $this->modes[$v['mode']];?>]</td>
		<td><?php echo $v['mode'] * $v['beiShu'] * $v['actionNum'];?></td>
		<td><?php echo $v['lotteryNo'] ? number_format($v['bonus'], 2, '.', '') : '0.00';?></td>
		<?php
		$zj_class = '';
		if ($v['isDelete'] == 1) {    //已撤单
			$zj_class = 'del';
		} elseif (!$v['lotteryNo']) { //未开奖
			$zj_class = 'wait';
		}elseif($v['zjCount']){       //已派奖
			$zj_class = 'award';
		}else{                        //未中奖
			$zj_class = 'none';
		}
		?>
		<td class="<?php echo $zj_class ?>"><?php echo $v['lotteryNo'] ? $v['lotteryNo'] : '--';?></td>
	</tr>
	<?php }?>
</table>
<?php } else {?>
<div class="empty"></div>
<?php }?>
<?php require(TPL.'/page.tpl.php');?>