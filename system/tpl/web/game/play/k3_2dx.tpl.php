<input type="hidden" name="playedGroup" value="<?php echo $group_id;?>" />
<input type="hidden" name="playedId" value="<?php echo $play_id;?>" />
<input type="hidden" name="type" value="<?php echo $type_id;?>" />
<div class="zhixu115 unique">
    <div class="pp pp11" action="tzAllSelect" length="2" delimiter=" ">
        <div class="title">同号</div>
        &nbsp;
        <input type="button" value="11" class="code" />
        <input type="button" value="22" class="code" />
        <input type="button" value="33" class="code" />
        <input type="button" value="44" class="code" />
        <input type="button" value="55" class="code" />
        <input type="button" value="66" class="code" />
    
    </div>
    <div class="pp pp11" action="tzAllSelect" length="2" delimiter=" ">
        <div class="title">不同号</div>
        &nbsp;
        <input type="button" value="1" class="code" />
        <input type="button" value="2" class="code" />
        <input type="button" value="3" class="code" />
        <input type="button" value="4" class="code" />
        <input type="button" value="5" class="code" />
        <input type="button" value="6" class="code" />
    
    </div>
<?php
	$maxPl = $this->get_play_bonus($play_id);
?>
</div>

<script type="text/javascript">
$(function(){
	lottery.set_play_Pl(<?php echo json_encode($maxPl);?>);
})
</script>

