<input type="hidden" name="playedGroup" value="<?php echo $group_id;?>" />
<input type="hidden" name="playedId" value="<?php echo $play_id;?>" />
<input type="hidden" name="type" value="<?php echo $type_id;?>" />
<div class="pp pp_fix_1" action="tz11x5Input" length="2" >
	<textarea id="textarea-code" placeholder="请按照玩法说明填写您选择的号码"></textarea>
	<a href="javascript:;" id="clear_num_func">双击清空号码</a>
</div>
<?php $maxPl = $this->get_play_bonus($play_id); ?>
<script type="text/javascript">
$(function(){
	lottery.set_play_Pl(<?php echo json_encode($maxPl);?>);
})
</script>
