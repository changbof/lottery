<div id="activity-exchange" class="common">
	<div class="head">
		<div class="name icon-exchange">积分兑换</div>
		<div class="tab">
			<a href="/activity/rotary" target="ajax" func="loadpage">幸运大转盘</a>
			<span style="background-color:#f5ecdc">积分兑换</span>
			<!--<a href="/activity/treasure" target="ajax" func="loadpage">夺宝奇兵</a>
			<a href="/activity/bank" target="ajax" func="loadpage">电子银行</a>-->
		</div>
	</div>
	<div class="addon" style="border-top:none;border-bottom:1px solid #f0e6d4">
		<ul class="list">
			<li>您当前积分为<span class="btn btn-red" id="dom-activity-score"><?php echo $this->user['score'];?></span>，可以兑换<span class="btn btn-green" id="dom-activity-times"><?php echo $this->user['score'] < $this->exchange_config['score'] ? 0 : intval($this->user['score'] / $this->exchange_config['score']);?></span>元；</li>
			<li>兑换1元需要<span class="btn btn-blue"><?php echo $this->exchange_config['score'];?></span>积分；</li>
			<li>积分不足不能参与兑换活动，兑换积分数量不限；</li>
			<li>兑换积分数量必须是<span class="color red"><?php echo $this->exchange_config['score'];?></span>的整数。</li>
		</ul>
	</div>
	<div class="body money-panel" style="padding:50px 245px">
		<form action="/activity/exchange_submit" target="ajax" func="form_submit">
		<div class="input mr15">
			<span class="icon icon-yen"></span>
			<input autocomplete="off" type="text" name="score" required min="<?php echo $this->exchange_config['score'];?>" placeholder="请输入您的要兑换的积分数量，最低为：<?php echo $this->exchange_config['score'];?>">
		</div>
		<button type="submit" class="submit btn btn-blue icon-ok">兑换</button>
		</form>
	</div>
</div>
<script type="text/javascript">
$(function() {
	$('#home').removeClass('on');
	$('#activity').addClass('on');
});
</script>