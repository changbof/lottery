<div id="lottery-dom" class="common clearfix">
	<div class="head">
		<div class="name icon-award">开奖记录：<?php echo $type_this['title'];?></div>
		<div class="right">
			<a href="javascript:;" class="gray" onclick="voice.switcher()">
				<span id="voice" class="icon-volume-down">关闭声音</span>
			</a>
		</div>
	</div>
	<div class="body" id="game-lottery" type="<?php echo $type_id;?>" ctype="<?php echo $types[$type_id]['type']?>">
		<div class="lottery-container clearfix">
			<div class="loading hide">开奖数据加载中，请稍候...</div>
			<?php
			if ($lottery) {
				$_lottery = $lottery;
			} else {
				$_lottery = array();
				for ($i=0;$i<100;$i++) array_push($_lottery, 0);
			}
			?>
			<div class="info">
				<span id="kjsay" class="hide">开奖倒计时(<em class="kjtips">00:00</em>)</span>
			</div>
			<div class="data1 clearfix">
				<div class="current blockc">
					<div class="name icon-award"><span id="lottery-current-text">第 <?php echo $last['actionNo'];?> 期<span class="val">开奖号码</span></span></div>
					<?php if($types[$type_id]['type']==4) { //快乐十分?>
						<div class="num_right_new kj-hao"  ctype="kl10">
							<em class="num_red_b_new ball_01"> <?php echo $_lottery[0];?> </em>
							<em class="num_red_b_new ball_02"> <?php echo $_lottery[1];?> </em>
							<em class="num_red_b_new ball_03"> <?php echo $_lottery[2];?> </em>
							<em class="num_red_b_new ball_04"> <?php echo $_lottery[3];?> </em>
							<em class="num_red_b_new ball_01"> <?php echo $_lottery[4];?> </em>
							<em class="num_red_b_new ball_02"> <?php echo $_lottery[5];?> </em>
							<em class="num_red_b_new ball_03"> <?php echo $_lottery[6];?> </em>
							<em class="num_red_b_new ball_04"> <?php echo $_lottery[7];?> </em>
						</div>
					<?php }else if($types[$type_id]['type']==6) { //PK10?>
						<div class="num_right_pk10 kj-hao" ctype="pk10">
							<em class="num_red_b_pk10 ball_01"> <?php echo $_lottery[0];?> </em>
							<em class="num_red_b_pk10 ball_02"> <?php echo $_lottery[1];?> </em>
							<em class="num_red_b_pk10 ball_03"> <?php echo $_lottery[2];?> </em>
							<em class="num_red_b_pk10 ball_04"> <?php echo $_lottery[3];?> </em>
							<em class="num_red_b_pk10 ball_01"> <?php echo $_lottery[4];?> </em>
							<em class="num_red_b_pk10 ball_02"> <?php echo $_lottery[5];?> </em>
							<em class="num_red_b_pk10 ball_03"> <?php echo $_lottery[6];?> </em>
							<em class="num_red_b_pk10 ball_04"> <?php echo $_lottery[7];?> </em>
							<em class="num_red_b_pk10 ball_01"> <?php echo $_lottery[8];?> </em>
							<em class="num_red_b_pk10 ball_02"> <?php echo $_lottery[9];?> </em>
						</div>
					<?php }else if($types[$type_id]['type']==8) { //快8
						/*$lotteryS=explode("|",$_lottery[19]);
						$_lottery[19]=$lotteryS[0];
						$feipan=$lotteryS[1];*/
						?>
						<div class="num_right_kl kj-hao" ctype="g1" cnum="80">
							<em id="span_lot_0" class="num_red_b_kl gr_s gr_s020"> <?php echo $_lottery[0];?> </em>
							<em id="span_lot_1" class="num_red_b_kl gr_s gr_s020"> <?php echo $_lottery[1];?> </em>
							<em id="span_lot_2" class="num_red_b_kl gr_s gr_s020"> <?php echo $_lottery[2];?> </em>
							<em id="span_lot_3" class="num_red_b_kl gr_s gr_s020"> <?php echo $_lottery[3];?> </em>
							<em id="span_lot_4" class="num_red_b_kl gr_s gr_s020"> <?php echo $_lottery[4];?> </em>
							<em id="span_lot_5" class="num_red_b_kl gr_s gr_s020"> <?php echo $_lottery[5];?> </em>
							<em id="span_lot_6" class="num_red_b_kl gr_s gr_s020"> <?php echo $_lottery[6];?> </em>
							<em id="span_lot_7" class="num_red_b_kl gr_s gr_s020"> <?php echo $_lottery[7];?> </em>
							<em id="span_lot_8" class="num_red_b_kl gr_s gr_s020"> <?php echo $_lottery[8];?> </em>
							<em id="span_lot_9" class="num_red_b_kl gr_s gr_s020"> <?php echo $_lottery[9];?> </em>
							<em id="span_lot_10" class="num_red_b_kl gr_s gr_s020"> <?php echo $_lottery[10];?> </em>
							<em id="span_lot_11" class="num_red_b_kl gr_s gr_s020"> <?php echo $_lottery[11];?> </em>
							<em id="span_lot_12" class="num_red_b_kl gr_s gr_s020"> <?php echo $_lottery[12];?> </em>
							<em id="span_lot_13" class="num_red_b_kl gr_s gr_s020"> <?php echo $_lottery[13];?> </em>
							<em id="span_lot_14" class="num_red_b_kl gr_s gr_s020"> <?php echo $_lottery[14];?> </em>
							<em id="span_lot_15" class="num_red_b_kl gr_s gr_s020"> <?php echo $_lottery[15];?> </em>
							<em id="span_lot_16" class="num_red_b_kl gr_s gr_s020"> <?php echo $_lottery[16];?> </em>
							<em id="span_lot_17" class="num_red_b_kl gr_s gr_s020"> <?php echo $_lottery[17];?> </em>
							<em id="span_lot_18" class="num_red_b_kl gr_s gr_s020"> <?php echo $_lottery[18];?> </em>
							<em id="span_lot_19" class="num_red_b_kl gr_s gr_s020"> <?php echo $_lottery[19];?> </em>
						</div>
					<?php }else if($types[$type_id]['type']==9) { //快3?>
						<div class="num_right_k3 kj-hao" ctype="k3">
							<em class="num_red_b_k3  ball_0"><?php echo intval($_lottery[0]);?> </em>
							<em class="num_red_b_k3  ball_1"><?php echo intval($_lottery[1]);?> </em>
							<em class="num_red_b_k3  ball_2"><?php echo intval($_lottery[2]);?> </em>
						</div>
					<?php }else if($types[$type_id]['type']==3) { //3D?>
						<div class="num_right_3d  kj-hao" ctype="3d">
							<em class="num_red_b ball_0"><?php echo intval($_lottery[0]);?> </em>
							<em class="num_red_b ball_1"><?php echo intval($_lottery[1]);?> </em>
							<em class="num_red_b ball_2"><?php echo intval($_lottery[2]);?> </em>
						</div>
					<?php }else if($types[$type_id]['type']==2) { //11选5?>
						<div class="num_right  kj-hao" ctype="11x5">
							<em class="num_red_b ball_0"><?php echo intval($_lottery[0]);?> </em>
							<em class="num_red_b ball_1"><?php echo intval($_lottery[1]);?> </em>
							<em class="num_red_b ball_2"><?php echo intval($_lottery[2]);?> </em>
							<em class="num_red_b ball_3"><?php echo intval($_lottery[3]);?> </em>
							<em class="num_red_b ball_4"><?php echo intval($_lottery[4]);?> </em>
						</div>
					<?php }else{?>
						<div class="num_right kj-hao"  ctype="ssc">
							<em class="num_red_b ball_0"><?php echo intval($_lottery[0]);?></em>
							<em class="num_red_b ball_1"><?php echo intval($_lottery[1]);?></em>
							<em class="num_red_b ball_2"><?php echo intval($_lottery[2]);?></em>
							<em class="num_red_b ball_3"><?php echo intval($_lottery[3]);?></em>
							<em class="num_red_b ball_4"><?php echo intval($_lottery[4]);?></em>
						</div>
					<?php }?>
				</div>
			</div>
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
		lottery.countdown(<?php echo $diffTime;?>); // 开奖倒计时
	}, 1000);
	<?php if($kjDiffTime > 0){?>
	lottery.timer.KT = setTimeout(function() {
		lottery.waiting(<?php echo $kjDiffTime;?>); // 正在开奖中(等待开奖中)
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