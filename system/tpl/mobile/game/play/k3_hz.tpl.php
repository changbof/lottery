<input type="hidden" name="playedGroup" value="<?php echo $group_id;?>" />
<input type="hidden" name="playedId" value="<?php echo $play_id;?>" />
<input type="hidden" name="type" value="<?php echo $type_id;?>" />

<div class="pp pp11" action="tz11x5Select" length="1" >
	<div class="lists">
		<input type="button" value="3" class="code min d" />
		<input type="button" value="4" class="code min s" />
		<input type="button" value="5" class="code min d" />
		<input type="button" value="6" class="code min s" />
		<input type="button" value="7" class="code min d" />
		<input type="button" value="8" class="code min s" />
		<input type="button" value="9" class="code min d" />
		<input type="button" value="10" class="code min s" />
	</div>
	<div class="lists">
		<input type="button" value="11" class="code max d" />
		<input type="button" value="12" class="code max s" />
		<input type="button" value="13" class="code max d" />
		<input type="button" value="14" class="code max s" />
		<input type="button" value="15" class="code max d" />
		<input type="button" value="16" class="code max s" />
		<input type="button" value="17" class="code max d" />
		<input type="button" value="18" class="code max s" />
	</div>
	<div class="lists act">
		<input type="button" value="大" class="action large" />
		<input type="button" value="小" class="action small" />
		<input type="button" value="单" class="action odd" />
		<input type="button" value="双" class="action even" />
		<input type="button" value="全" class="action all" />
		<input type="button" value="清" class="action none" />
	</div>
</div>
<?php
	
	$maxPl = $this->get_play_bonus($play_id);
?>
<script type="text/javascript">
$(function(){
	lottery.set_play_Pl(<?php echo json_encode($maxPl);?>);
})
</script>