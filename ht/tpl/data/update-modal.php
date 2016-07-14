<?php
$para = $args[0];
$actionTime = explode(' ', $para['actionTime']);
$actionTime = $actionTime[1];
$actionData = $this->getGameLastNo($para['type'], $para['actionNo'], $actionTime, strtotime($para['actionTime']));
$id = $this->getValue("select id from lottery_data where number=? and type={$para['type']}", $actionData['actionNo']);
?>
<div>
<input type="hidden" value="<?=$this->user['username']?>" />
<form action="/index.php/data/updatedataed" target="ajax" method="post" call="dataSubmitCode" onajax="dataBeforeSubmitCode" dataType="html">
	<input type="hidden" name="id" value="<?=$id?>"/>
	<table class="popupModal">
		<tr>
			<td class="title" width="180">期号：</td>
			<td><?php echo $actionData['actionNo'];?></td>
		</tr>
		<tr>
			<td class="title">开奖时间：</td>
			<td><?php echo $actionData['actionTime'];?></td>
		</tr>
		<tr>
			<td class="title">开奖号码：</td>
			<td><input type="text" name="data"/></td>
		</tr>
		<tr>
			<td align="right"><span class="spn4">提示：</span></td>
			<td><span class="spn4">号码格式如: 1,2,3,4,5</span></td>
		</tr>
	</table>
</form>
</div>