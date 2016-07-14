<div class="play-list hide" >
	<?php
		foreach ($plays as $play) {
			$class = $play_id == $play['id'] ? ' class="on"' : '';
	?>
	<a data-id="<?php echo $play['id'];?>" href="javascript:;"<?php echo $class;?>><?php echo $play['name'];?></a>
	<?php }?>
</div>
<div id="play-data">
	<?php require(TPL.'/game/play_data.tpl.php');?>
</div>