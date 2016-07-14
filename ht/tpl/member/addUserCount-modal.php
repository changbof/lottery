<div class="UserCountAdd">
<input type="hidden" value="<?=$this->user['username']?>" />
<form action="/index.php/member/addUserCount_func" call="memberAddUserCount" target="ajax" method="post">
	<table cellpadding="2" cellspacing="2" class="popupModal">
		<tr>
			<td class="title" width="180">返点：</td>
			<td><input type="text" name="fanDian"/></td>
		</tr>
		<tr>
			<td class="title" width="180">用户限额：</td>
			<td><input type="text" name="userCount"/></td>
		</tr>
	</table>
</form>
</div>