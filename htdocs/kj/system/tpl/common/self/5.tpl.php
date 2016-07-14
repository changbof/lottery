<?php
// 生成随机数
function rand_keys($len = 5) {
	$str = '9038627451';
	$rand = '';
	for ($x=0;$x<$len;$x++) {
		$rand .= ($rand != '' ? ',' : '').substr($str, rand(0, strlen($str) - 1), 1);
	}
	return $rand;
}
$lastNo = core::lib('game')->get_game_last_no(5);

echo '<?xml version="1.0" encoding="utf-8"?>';
echo '<xml><row expect="'.$lastNo['actionNo'].'" opencode="'.rand_keys().'" opentime="'.$lastNo['actionTime'].'"/></xml>';