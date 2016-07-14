<input type="hidden" name="playedGroup" value="<?php echo $group_id;?>" />
<input type="hidden" name="playedId" value="<?php echo $play_id;?>" />
<input type="hidden" name="type" value="<?php echo $type_id;?>" />
<?php
	$wfName = $this->db->query("SELECT `name` FROM `{$this->db_prefix}played` WHERE `id`={$play_id} LIMIT 1", 2);
	$wfName = $wfName['name'];
?>
<div class="pp" action="tzAllSelect" length="1" random="sscRandom">
    <div class="_title"><?=$wfName?>大小</div>
	<input type="button" value="大" class="code" />
	<input type="button" value="小" class="code" />
    <div style="height:34px;width:30px;float:left"></div>
	<div class="_title"><?=$wfName?>单双</div>
	<input type="button" value="单" class="code" />
	<input type="button" value="双" class="code" />

</div>
<?php
	$maxPl = $this->get_play_bonus($play_id);
?>
<script type="text/javascript">
$(function(){
	lottery.set_play_Pl(<?php echo json_encode($maxPl);?>, false, <?php echo $this->user['fanDianBdw'];?>);
})
</script>
