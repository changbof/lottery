<?php $z3Pl=$this->get_play_bonus(19);$z6Pl=$this->get_play_bonus(20); ?>
<input type="hidden" name="playedGroup" value="<?php echo $group_id;?>" />
<input type="hidden" name="type" value="<?php echo $type_id;?>" />
<div class="pp pp_fix_1" action="tzSscHhzxInput_1" played="后" length="3" z3min="<?=$z3Pl['bonusPropBase']?>" z6min="<?=$z6Pl['bonusPropBase']?>" z3max="<?=$z3Pl['bonusProp']?>" z6max="<?=$z6Pl['bonusProp']?>">
	<textarea id="textarea-code" placeholder="请按照玩法说明填写您选择的号码"></textarea>
	<a href="javascript:;" id="clear_num_func">双击清空号码</a>
</div>
<script type="text/javascript">
$(function(){
	lottery.set_play_Pl(<?php echo json_encode($z3Pl);?>, true);
})
</script>
