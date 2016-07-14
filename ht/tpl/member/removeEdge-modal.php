<div>
<form action="/index.php/Member/removeEdged/<?=$args[0]?>" target="ajax" method="post" call="userDataSubmitCode" dataType="html">
	<input type="hidden" name="uid" value="<?=$args[0]?>"/>
	<input type="number" name="target" placeholder="填写转移到哪个用户ID下" style="width: 90%;margin: 15px 0;display: block;padding: 5px 4%;" />
	系统将会把当前用户转移到指定的用户ID下。<br />
	<span style="color:#F00; text-align:center; line-height:50px;">确定转移将不能恢复，是否确定？</span><br />
</form>
</div>