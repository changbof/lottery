<?php
	if ($lottery) {
		$_lottery = $lottery;
	} else {
		$_lottery = array();
		for ($i=0;$i<100;$i++) array_push($_lottery, 0);
	}
?>
<div class="last">
	<div class="time_tip icon-clock" title="投注截止计时">第 <span id="last_action_no"><?php echo $current['actionNo'];?></span>期封盘计时 </div>
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
</div>
<div class="current">
	<div class="name_tip icon-award"><span id="lottery-current-text">第 <?php echo $last['actionNo'];?> 期<span class="val">开奖号码</span></span></div>
	<div class="num_right_k3 kj-hao" ctype="k3">
		<em class="num_red_b_k3  ball_0"><?php echo intval($_lottery[0]);?></em>
		<em class="num_red_b_k3  ball_1"><?php echo intval($_lottery[1]);?></em>
		<em class="num_red_b_k3  ball_2"><?php echo intval($_lottery[2]);?></em>
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