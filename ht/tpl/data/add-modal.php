<?php
$para = $args[0];
$actionTime = explode(' ', $para['actionTime']);
$actionTime = $actionTime[1];
$actionData = $this->getGameLastNo($para['type'], $para['actionNo'], $actionTime, strtotime($para['actionTime']));
?>
<div>
<input type="hidden" value="<?=$this->user['username']?>" />
<form action="/index.php/data/added" target="ajax" method="post" call="dataSubmitCode" onajax="dataBeforeSubmitCode" dataType="html">
	<input type="hidden" name="type" value="<?=$para['type']?>"/>
	<table class="popupModal">
		<tr>
			<td class="title" width="180">期号：</td>
			<td><input type="text" name="number" value="<?php echo $actionData['actionNo'];?>"/></td>
		</tr>
		<tr>
			<td class="title">开奖时间：</td>
			<td><input type="text" name="time" value="<?php echo $actionData['actionTime'];?>"/></td>
		</tr>
		<tr>
			<td class="title">开奖号码：</td>
			<td><input type="text" name="data"/></td>
		</tr>
		<tr>
			<td align="right"><span class="spn4">提示：</span></td>
			<td><span class="spn4">请确认【期号】和【开奖号码】正确<br/>号码格式如: 1,2,3,4,5</span></td>
		</tr>
	</table>
</form>
</div>