<div class="play-info">
	<div class="help">
		玩法说明：<?php echo $play_info['simpleInfo'];?>
		<a href="javascript:;" class="btn btn-green"><span action="play-example" class="icon-lamp showeg">示例</span></a>
		<a href="javascript:;" class="btn btn-blue"><span action="play-help" class="icon-help showeg">说明</span></a>
	</div>
	<div id="play-example" class="play-eg hide"><?php echo $play_info['example'];?></div>
	<div id="play-help" class="play-eg hide"><?php echo $play_info['info'];?></div>
</div>
<div class="play-select">
	<div class="num-table" id="num-select">
	<?php require(TPL.'/game/play/'.$play_tpl.'.tpl.php');?>
	</div>
</div>
<script type="text/javascript">
~(function() {
	var dom_all = $('#num-select .pp');
	if (dom_all.length > 0) {
		var dom_this = $(dom_all[0]);
		var code = dom_this.find('input.code');
		if (code.length > 0) {
			// 针对数字长度的样式修复
			var code_length = $(code[0]).val().length;
			if (code_length === 3) {
				dom_all.addClass('l3');
			} else if (code_length > 3) {
				dom_all.addClass('lm');
			}
			// 针对数字个数的样式修复
			var code_count = code.length;
			if (code_count > 13 && code_count <= 20) {
				dom_all.addClass('c20');
			} else if (code_count > 20) {
				var action = dom_this.find('input.action');
				var action_length = action.length;
				if (action_length == 0) {
					var h = Math.ceil(code_count / 21) * 40 + 'px';
					dom_all.addClass('cm').css({'height': h, });
				} else {
					var h = ((Math.ceil(code_count / 21) + 1) * 40 + 10) + 'px';
					var i = 0;
					var base = (930 - action_length * 45) / 2 + 5;
					dom_all.addClass('cm').css({'height': h, 'position': 'relative'});
					action.each(function() {
						$(this).css('left', (base + i * 45) + 'px');
						i++;
					});
				}
			}
		}
	}
})();
</script>