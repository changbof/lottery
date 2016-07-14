<input type="hidden" name="playedGroup" value="<?php echo $group_id;?>" />
<input type="hidden" name="playedId" value="<?php echo $play_id;?>" />
<input type="hidden" name="type" value="<?php echo $type_id;?>" />
<div class="pp pp11" delimiter=" " action="tzAllSelect" length="1" >
	<input type="button" value="1-2" class="code reset" />
	<input type="button" value="1-3" class="code reset" />
	<input type="button" value="1-4" class="code reset" />
	<input type="button" value="1-5" class="code reset" />
	<input type="button" value="1-6" class="code reset" />
    <input type="button" value="1-7" class="code reset" />
    <input type="button" value="1-8" class="code reset" />
    <input type="button" value="1-9" class="code reset" />
    <input type="button" value="1-10" class="code reset" />
	<input type="button" value="2-3" class="code reset" />
	<input type="button" value="2-4" class="code reset" />
	<input type="button" value="2-5" class="code reset" />
	<input type="button" value="2-6" class="code reset" />
	<input type="button" value="2-7" class="code reset" />
    <input type="button" value="2-8" class="code reset" />
    <input type="button" value="2-9" class="code reset" />
    <input type="button" value="2-10" class="code reset" />
    <input type="button" value="3-4" class="code reset" />
	<input type="button" value="3-5" class="code reset" />
	<input type="button" value="3-6" class="code reset" />
	<input type="button" value="3-7" class="code reset" />
	<input type="button" value="3-8" class="code reset" />
	<input type="button" value="3-9" class="code reset" />
    <input type="button" value="3-10" class="code reset" />
    <input type="button" value="4-5" class="code reset" />
    <input type="button" value="4-6" class="code reset" />
    <input type="button" value="4-7" class="code reset" />
	<input type="button" value="4-8" class="code reset" />
	<input type="button" value="4-9" class="code reset" />
	<input type="button" value="4-10" class="code reset" />
	<input type="button" value="5-6" class="code reset" />
	<input type="button" value="5-7" class="code reset" />
    <input type="button" value="5-8" class="code reset" />
    <input type="button" value="5-9" class="code reset" />
    <input type="button" value="5-10" class="code reset" />
    <input type="button" value="6-7" class="code reset" />
	<input type="button" value="6-8" class="code reset" />
	<input type="button" value="6-9" class="code reset" />
	<input type="button" value="6-10" class="code reset" />
	<input type="button" value="7-8" class="code reset" />
	<input type="button" value="7-9" class="code reset" />
    <input type="button" value="7-10" class="code reset" />
    <input type="button" value="8-9" class="code reset" />
    <input type="button" value="8-10" class="code reset" />
    <input type="button" value="9-10" class="code reset" />
    
</div>
<?php
	
	$maxPl = $this->get_play_bonus($play_id);
?>
<script type="text/javascript">
$(function(){
	lottery.set_play_Pl(<?php echo json_encode($maxPl);?>);
})
</script>