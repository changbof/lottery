<input type="hidden" name="playedGroup" value="<?php echo $group_id;?>" />
<input type="hidden" name="playedId" value="<?php echo $play_id;?>" />
<input type="hidden" name="type" value="<?php echo $type_id;?>" />

<div class="pp pp11" action="tz11x5Select" length="1" >
	<input type="button" value="11*" class="code reset" />
	<input type="button" value="22*" class="code reset" />
    <input type="button" value="33*" class="code reset" />
    <input type="button" value="44*" class="code reset" />
    <input type="button" value="55*" class="code reset" />
    <input type="button" value="66*" class="code reset" />
</div>
<?php
	
	$maxPl = $this->get_play_bonus($play_id);
?>
<script type="text/javascript">
$(function(){
	lottery.set_play_Pl(<?php echo json_encode($maxPl);?>);
})
</script>