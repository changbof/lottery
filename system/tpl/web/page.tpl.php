<?php
function page_url($page, $page_url, $flag = '{page}') {
	return str_replace(urlencode($flag), $page, $page_url);
}
if ($page_max <= 1) return;
$page_num = 5;
$page_start = $page_current - floor($page_num / 2);
if ($page_start < 1) $page_start = 1;
$page_prev = $page_current - 1;
if ($page_prev < 1) $page_prev = 1;
$page_next = $page_current + 1;
if ($page_next > $page_max) $page_next = $page_max;
$page_attr = 'target="ajax" func="loadpage" container="'.$page_container.'" data-ispage="true"';
?>
<div id="page">
	<?php if($page_current == 1) { ?>
	<a href="javascript:;" class="icon-angle-double-left">首页</a>
	<a href="javascript:;" class="icon-angle-left">上一页</a>
	<?php } else { ?>
	<a href="<?php echo page_url(1, $page_url);?>" class="icon-angle-double-left" <?php echo $page_attr;?>>首页</a>
    <a href="<?php echo page_url($page_prev, $page_url);?>" class="icon-angle-left" <?php echo $page_attr;?>>上一页</a>
	<?php
		}
		for ($i=$page_start;$i<=$page_start+$page_num;$i++) {
			if ($i > $page_max) break;
	?>
	<a href="<?php echo page_url($i, $page_url);?>"<?php echo $i == $page_current ? ' class="active"' : '';?> <?php echo $page_attr;?>><?php echo $i;?></a>
	<?php
		}
		if ($page_current == $page_max) {
	?>
	<a href="javascript:;" class="icon-angle-right">下一页</a>
	<a href="javascript:;" class="icon-angle-double-right">尾页</a>
	<?php } else { ?>
	<a href="<?php echo page_url($page_next, $page_url);?>" class="icon-angle-right" <?php echo $page_attr;?>>下一页</a>
	<a href="<?php echo page_url($page_max, $page_url);?>" class="icon-angle-double-right" <?php echo $page_attr;?>>尾页</a>
	<?php } ?>
	<div class="info">第<b><?php echo $page_current;?></b>页 / 共<b><?php echo $page_max;?></b>页</div>
</div>