<div id="lottery-dom" class="common clearfix">
	<input type="hidden" id="mode" value="<?php echo $mode  ?>" />
	<div class="head">
		<div class="name icon-award">开奖记录：<?php echo $type_this['title'];?></div>
		<div class="right">
			<a href="javascript:;" class="gray" onclick="voice.switcher()">
				<span id="voice" class="icon-volume-down">关闭声音</span>
			</a>
		</div>
	</div>
	<div class="body">
		<div class="blockc" id="history">
			<table width="100%" cellpadding="0" cellspacing="0">
				<tr class="head">
					<td>最近期号</td>
					<td>开奖号码</td>
				</tr>
				<?php foreach($history as $key => $var) {?>
					<tr>
						<td title="于 <?php echo date('Y-m-d H:i:s', $var['time']);?> 开奖"><?php echo $var['number'];?></td>
						<td><?php echo $var['data'];?></td>
					</tr>
				<?php }?>
			</table>
		</div>
	</div>
</div>

<script type="text/javascript">
$(function() {
	$('#lottery-recent').addClass('on');
	window.S = <?php echo json_encode($diffTime > 0);?>;
	window.KS = <?php echo json_encode($kjDiffTime > 0);?>;
	window.kjTime = parseInt(<?php echo json_encode($kjdTime);?>);
	if (lottery.timer.T) clearTimeout(lottery.timer.T);
	if (lottery.timer.KT) clearTimeout(lottery.timer.KT);
	if (lottery.timer.moveno) clearInterval(lottery.timer.moveno);
	lottery.timer.T = setTimeout(function() {
		lottery.countdown(<?php echo $diffTime;?>);
	}, 1000);
	<?php if($kjDiffTime > 0){?> 
	lottery.timer.KT = setTimeout(function() {
		lottery.waiting(<?php echo $kjDiffTime;?>);
	}, 1000);
	<?php }?>
	<?php if($lottery){?>
	voice.play('/static/sound/voice-lottery.wav', 'voice-lottery');
	lottery.get_loss_gain(game.type, '<?php echo $last['actionNo'];?>');
	if (lottery.switcher.bets_fresh) lottery.bets_fresh();
	<?php } else {?>
	lottery.load_last_data();
	<?php }?>
});
</script>