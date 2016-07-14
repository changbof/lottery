<input type="hidden" name="playedGroup" value="<?php echo $group_id;?>" />
<input type="hidden" name="playedId" value="<?php echo $play_id;?>" />
<input type="hidden" name="type" value="<?php echo $type_id;?>" />
<div class="pp pp11" action="tzAllSelect" length="1">
    &nbsp
	<input type="button" value="5单0双" class="code reset2" />
	<input type="button" value="4单1双" class="code reset2" />
	<input type="button" value="3单2双" class="code reset2" />
	<input type="button" value="2单3双" class="code reset2" />
	<input type="button" value="1单4双" class="code reset2" />
	<input type="button" value="0单5双" class="code reset2" />
</div>
<?php $maxPl = $this->get_play_bonus($play_id); ?>
<script type="text/javascript">
$(function(){
	lottery.set_play_Pl(<?php echo json_encode($maxPl);?>);
})
</script>