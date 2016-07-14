<input type="hidden" name="playedGroup" value="<?php echo $group_id;?>" />
<input type="hidden" name="playedId" value="<?php echo $play_id;?>" />
<input type="hidden" name="type" value="<?php echo $type_id;?>" />

<div class="pp pp11" action="tzKLSFSelect" length="1" >

	<input type="button" value="19" class="code d max" />
	<input type="button" value="20" class="code s max" />
</div>

<?php
	$maxPl = $this->get_play_bonus($play_id);
?>
<script type="text/javascript">
$(function(){
	lottery.set_play_Pl(<?php echo json_encode($maxPl);?>);
})
</script>

