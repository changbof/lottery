<input type="hidden" name="playedGroup" value="<?php echo $group_id;?>" />
<input type="hidden" name="playedId" value="<?php echo $play_id;?>" />
<input type="hidden" name="type" value="<?php echo $type_id;?>" />
<div class="dantuo">
    <label><input type="radio" name="dantuo" value="" checked="checked"/>普通</label>
    <label><input type="radio" name="dantuo" value="1"/>胆拖</label>
</div>
<div>
<div class="pp pp11" action="tz11x5Select" length="2" >
	<input type="button" value="1" class="code" />
	<input type="button" value="2" class="code" />
    <input type="button" value="3" class="code" />
    <input type="button" value="4" class="code" />
    <input type="button" value="5" class="code" />
    <input type="button" value="6" class="code" />
</div>
</div>
<div class="dmtm unique" style="display:none;">
    <div class="pp pp11">
        <div class="title">胆码</div>
        <input type="button" value="1" class="code" />
        <input type="button" value="2" class="code" />
        <input type="button" value="3" class="code" />
        <input type="button" value="4" class="code" />
        <input type="button" value="5" class="code" />
        <input type="button" value="6" class="code" />
    </div>
    <div class="pp pp11">
        <div class="title">拖码</div>
        <input type="button" value="1" class="code" />
        <input type="button" value="2" class="code" />
        <input type="button" value="3" class="code" />
        <input type="button" value="4" class="code" />
        <input type="button" value="5" class="code" />
        <input type="button" value="6" class="code" />
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