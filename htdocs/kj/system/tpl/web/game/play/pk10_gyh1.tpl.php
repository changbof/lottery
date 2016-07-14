<input type="hidden" name="playedGroup" value="<?php echo $group_id;?>" />
<input type="hidden" name="playedId" value="<?php echo $play_id;?>" />
<input type="hidden" name="type" value="<?php echo $type_id;?>" />
<?php
	$wfName = $this->db->query("SELECT `name` FROM `{$this->db_prefix}played` WHERE `id`={$play_id} LIMIT 1", 2);
	$wfName = $wfName['name'];
?>
<?php foreach(array($wfName) as $var){ ?>
<div class="pp pp11" delimiter=" " action="tzAllSelect" length="1" >
	<div class="title"><?=$var?></div>
	<input type="button" value="3" class="code min d" />
	<input type="button" value="4" class="code min s" />
	<input type="button" value="5" class="code min d" />
	<input type="button" value="6" class="code min s" />
	<input type="button" value="7" class="code min d" />
	<input type="button" value="8" class="code min s" />
	<input type="button" value="9" class="code min d" />
	<input type="button" value="10" class="code min s" />
	<input type="button" value="11" class="code max d" />
    <input type="button" value="12" class="code max s" />
    <input type="button" value="13" class="code max d" />
	<input type="button" value="14" class="code max s" />
	<input type="button" value="15" class="code max d" />
	<input type="button" value="16" class="code max s" />
	<input type="button" value="17" class="code max d" />
	<input type="button" value="18" class="code max s" />
	<input type="button" value="19" class="code max d" />
	
</div>
<?php
	}
	
	$maxPl = $this->get_play_bonus($play_id);
?>
<script type="text/javascript">
$(function(){
	lottery.set_play_Pl(<?php echo json_encode($maxPl);?>);
})
</script>