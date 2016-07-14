<div id="activity-rotary" class="common">
	<div class="head">
		<div class="name icon-globe">幸运大转盘</div>
		<div class="tab">
			<span style="background-color:#f5ecdc">幸运大转盘</span>
			<a href="/activity/exchange" target="ajax" func="loadpage">积分兑换</a>
			<!--<a href="/activity/treasure" target="ajax" func="loadpage">夺宝奇兵</a>
			<a href="/activity/bank" target="ajax" func="loadpage">电子银行</a>-->
		</div>
	</div>
	<div class="addon" style="border-top:none;border-bottom:1px solid #f0e6d4">
		<ul class="list">
			<li>您当前积分为<span class="btn btn-red" id="dom-activity-score"><?php echo $this->user['score'];?></span>，可以抽奖<span class="btn btn-green" id="dom-activity-times"><?php echo $this->user['score'] < $this->dzpsettings['score'] ? 0 : intval($this->user['score'] / $this->dzpsettings['score']);?></span>次；</li>
			<li>每次抽奖需要<span class="btn btn-blue"><?php echo $this->dzpsettings['score'];?></span>积分；</li>
			<li>积分不足不能参与抽奖活动，抽奖次数不限；</li>
			<li>本站全程监控，请勿作弊，否则直接冻结您的账户。</li>
		</ul>
	</div>
	<div class="body">
		<div class="container">
			<div class="wheel">
				<div class="wheel-content clearfix">
					<div class="wheel-box">
						<div class="wheel-exec" id="startbtn"></div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript">
$(function() {
	var title = "使用 <?php echo $this->dzpsettings['score'];?> 积分兑换一次抽奖，是否确定继续抽奖？";
	$('#home').removeClass('on');
	$('#activity').addClass('on');
	$("#startbtn").live("click",function() {
		if(confirm(title)) activity_rotary();
	});
});
var rotaring = false;
var score = <?php echo $this->dzpsettings['score'];?>;
var dom_activity_score = $('#dom-activity-score');
var dom_activity_times = $('#dom-activity-times');
function activity_update() {
	var activity_score = dom_activity_score.text();
	var activity_times = dom_activity_times.text();
	dom_activity_score.text(activity_score - score);
	dom_activity_times.text(activity_times - 1);
}
function activity_rotary() {
	if (rotaring) return false;
	rotaring = true;
    $.ajax({
        type: 'POST',
        url: '/activity/rotary_submit',
        dataType: 'json',
        cache: false,
        error: function() {
            $.error('抽奖出错，请重试');
        },
        success: function(json) {
            var a = json.angle; // 角度
            var p = json.prize; // 奖项 
			if (parseInt(a)==0) {
				$.error(p);
			} else {
				$("#startbtn").rotate({
					duration: 3000, // 转动时间
					angle: 0,
					animateTo: 1800 + a, // 转动角度
					easing: $.easing.easeOutSine,
					callback: function() {
						if (p === '谢谢参与' || p === '再接再厉') {
							activity_update();
							$.error(p);
						} else if (p === '再来一次') {
							if (confirm('再来一次？')) activity_rotary();
						} else {
							activity_update();
							$.success('恭喜您，抽取到了' + p + '！');
						}
					}
				});
			}
        },
		complete: function() {
			rotaring = false;
		},
    });
}
</script>