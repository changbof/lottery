<input type="hidden" name="playedGroup" value="<?php echo $group_id;?>" />
<input type="hidden" name="playedId" value="<?php echo $play_id;?>" />
<input type="hidden" name="type" value="<?php echo $type_id;?>" />

<div class="pp pp11" action="tz11x5Select" length="1" >
	<div class="title">号码</div>
	<input type="button" value="111" class="code reset" />
	<input type="button" value="222" class="code reset" />
    <input type="button" value="333" class="code reset" />
    <input type="button" value="444" class="code reset" />
    <input type="button" value="555" class="code reset" />
    <input type="button" value="666" class="code reset" />
</div>
<?php
	
	$maxPl = $this->get_play_bonus($play_id);
?>
<script type="text/javascript">
$(function(){
	lottery.set_play_Pl(<?php echo json_encode($maxPl);?>);
})
</script>