<input type="hidden" name="playedGroup" value="<?php echo $group_id;?>" />
<input type="hidden" name="playedId" value="<?php echo $play_id;?>" />
<input type="hidden" name="type" value="<?php echo $type_id;?>" />
<div class="pp pp_fix_2" action="ssc_z3_r6" length="3" random="combineRandom">
	<div id="wei-shu" length="3" type='z3_r6_zuhetouzhu'>
		<label><input type="checkbox" name="ss" value="16" />万</label>
		<label><input type="checkbox" name="ss" value="8" />千</label>
		<label><input type="checkbox" name="ss" value="4" />百</label>
		<label><input type="checkbox" name="ss" value="2" />十</label>
		<label><input type="checkbox" name="ss" value="1" />个</label>
        <span id="digit_select_all" class="btn btn-white trans">一键全选</span>
	</div>
	<input type="button" name="kk" value="0" class="code min s" />
	<input type="button" name="kk" value="1" class="code min d" />
	<input type="button" name="kk" value="2" class="code min s" />
	<input type="button" name="kk" value="3" class="code min d" />
	<input type="button" name="kk" value="4" class="code min s" />
	<input type="button" name="kk" value="5" class="code max d" />
	<input type="button" name="kk" value="6" class="code max s" />
	<input type="button" name="kk" value="7" class="code max d" />
	<input type="button" name="kk" value="8" class="code max s" />
	<input type="button" name="kk" value="9" class="code max d" />
	&nbsp;
	<input type="button" value="清" class="action none" />
    <input type="button" value="双" class="action even" />
    <input type="button" value="单" class="action odd" />
    <input type="button" value="小" class="action small" />
    <input type="button" value="大" class="action large" />
    <input type="button" value="全" class="action all" />
</div>
<?php $maxPl = $this->get_play_bonus($play_id); ?>
<script type="text/javascript">
$(function(){
	lottery.set_play_Pl(<?php echo json_encode($maxPl);?>);
})
</script>
