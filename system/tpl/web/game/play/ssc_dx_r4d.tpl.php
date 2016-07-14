<input type="hidden" name="playedGroup" value="<?php echo $group_id;?>" />
<input type="hidden" name="playedId" value="<?php echo $play_id;?>" />
<input type="hidden" name="type" value="<?php echo $type_id;?>" />
<div class="pp pp_fix_1" action="tzSscWeiInput" length="4" random="sscRandom">
	<div id="wei-shu" length="4" type="dx_r4d_zuhetouzhu">
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
<?php $maxPl = $this->get_play_bonus($play_id); ?>
<script type="text/javascript">
$(function(){
	lottery.set_play_Pl(<?php echo json_encode($maxPl);?>);
})
</script>
