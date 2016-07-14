<input type="hidden" name="playedGroup" value="<?php echo $group_id;?>" />
<input type="hidden" name="playedId" value="<?php echo $play_id;?>" />
<input type="hidden" name="type" value="<?php echo $type_id;?>" />
<div class="pp pp11" action="ssch3ts" length="1">
    &nbsp
	<div class="title">选择</div>
	<input type="button" value="豹子" class="code reset2" />
	<input type="button" value="顺子" class="code reset2" />
	<input type="button" value="对子" class="code reset2" />
</div>
<?php $maxPl = $this->get_play_bonus($play_id); ?>
<script type="text/javascript">
$(function(){
	lottery.set_play_Pl(<?php echo json_encode($maxPl);?>);
})
</script>