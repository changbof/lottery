<?php
	if ($lottery) {
		$_lottery = $lottery;
	} else {
		$_lottery = array();
		for ($i=0;$i<100;$i++) array_push($_lottery, 0);
	}
?>
<div class="time_tip icon-clock" title="投注截止计时"><span id="last_action_no"><?php echo $current['actionNo'];?></span>期封盘计时 </div>
<div id="timer_lottery">
	<div class="h">
		<span class="number">0</span>
		<span class="number">0</span>
	</div>
	<div class="sep"></div>
	<div class="m">
		<span class="number">0</span>
		<span class="number">0</span>
	</div>
	<div class="sep"></div>
	<div class="s">
		<span class="number">0</span>
		<span class="number">0</span>
	</div>
</div>
<script type="text/javascript">
$(function() {
	window.S = <?php echo json_encode($diffTime > 0);?>;    // 投注截止剩余时间
	window.KS = <?php echo json_encode($kjDiffTime > 0);?>; // 开奖剩余时间
	window.kjTime = parseInt(<?php echo json_encode($kjdTime);?>); // 开奖时间
	if (lottery.timer.T) clearTimeout(lottery.timer.T);
	if (lottery.timer.KT) clearTimeout(lottery.timer.KT);
	if (lottery.timer.moveno) clearInterval(lottery.timer.moveno);
	lottery.timer.T = setTimeout(function() {
		lottery.countdown(<?php echo $diffTime;?>);  //投注截止倒计时
	}, 1000);
	<?php if($kjDiffTime > 0){?> 
	lottery.timer.KT = setTimeout(function() {
		lottery.waiting(<?php echo $kjDiffTime;?>);  //开奖等待
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