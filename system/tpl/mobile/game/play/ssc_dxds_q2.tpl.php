<input type="hidden" name="playedGroup" value="<?php echo $group_id;?>" />
<input type="hidden" name="playedId" value="<?php echo $play_id;?>" />
<input type="hidden" name="type" value="<?php echo $type_id;?>" />
<?php foreach(array('万','千') as $var){ ?>
<div class="pp" action="tzDXDS" length="2" random="sscRandom">
	<div class="title"><?=$var?>位</div>
	&nbsp;
	&nbsp;
	<input type="button" value="大" class="code" />
	<input type="button" value="小" class="code" />
	<input type="button" value="单" class="code" />
	<input type="button" value="双" class="code" />

</div>
<?php
	}
	$maxPl = $this->get_play_bonus($play_id);
?>
<script type="text/javascript">
$(function(){
	lottery.set_play_Pl(<?php echo json_encode($maxPl);?>, false, <?php echo $this->user['fanDianBdw'];?>);
})
</script>
