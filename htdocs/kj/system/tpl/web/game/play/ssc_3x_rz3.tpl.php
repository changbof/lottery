<?php $z3Pl=$this->get_play_bonus(22);$z6Pl=$this->get_play_bonus(23); ?>
<input type="hidden" name="playedId" value="<?php echo $play_id;?>" />
<input type="hidden" name="playedGroup" value="<?php echo $group_id;?>" />
<input type="hidden" name="type" value="<?php echo $type_id;?>" />
<div class="pp pp_fix_1" action="tzSscHhzxInput_2" played="任选" length="3" z3min="<?=$z3Pl['bonusPropBase']?>" z6min="<?=$z6Pl['bonusPropBase']?>" z3max="<?=$z3Pl['bonusProp']?>" z6max="<?=$z6Pl['bonusProp']?>">
	<div id="wei-shu" length="3" type='3x_rz3_zuhetouzhu' playedIdjsh="<?php echo $play_id;?>">
		<label><input type="checkbox" value="16" />万</label>
		<label><input type="checkbox" value="8" />千</label>
		<label><input type="checkbox" value="4" />百</label>
		<label><input type="checkbox" value="2" />十</label>
		<label><input type="checkbox" value="1" />个</label>
        <span id="digit_select_all" class="btn btn-white trans">一键全选</span>
	</div>
	<textarea id="textarea-code" placeholder="请按照玩法说明填写您选择的号码"></textarea>
	<a href="javascript:;" id="clear_num_func">双击清空号码</a>
</div>
<script type="text/javascript">
$(function(){
	lottery.set_play_Pl(<?php echo json_encode($z3Pl);?>, true);
})
</script>