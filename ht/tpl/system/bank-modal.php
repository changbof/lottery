<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<input type="hidden" value="<?=$this->user['username']?>" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title></title>
<link rel="stylesheet" type="text/css" href="/skin/admin/layout.css" media="all" />
</head>
<body>
<form name="system_addBank" action="/index.php/system/updateBank" enctype="multipart/form-data" method="POST">
<?php
	$id = intval($args[0]);
	$sql="select m.*, b.name bankName, b.id bankId from {$this->prename}admin_bank m, {$this->prename}bank_list b where b.id=m.bankid and b.isDelete=0 AND m.id={$id}";
	if (!$id || !($bank = $this->getRow($sql))) {
		echo '<table class="tablesorter left" cellspacing="0" width="100%"><tr><td>银行ID错误</td></tr></table>';
	} else {
?>
<input type="hidden" name="id" value="<?php echo $bank['id'];?>">
<input type="hidden" name="bankid" value="<?php echo $bank['bankId'];?>">
<table class="tablesorter left" cellspacing="0" width="100%">
	<thead> 
		<tr> 
			<td>项目</td> 
			<td>值</td> 
		</tr> 
	</thead>
	<tbody>
		<tr> 
			<td>银行名称</td> 
			<td><?php echo $bank['bankName'];?></td>
		</tr>
		<tr> 
			<td>账号</td> 
			<td><input type="text" name="account" value="<?=$bank['account']?>"/></td>
		</tr>
		<tr> 
			<td>收款人</td> 
			<td><input type="text" name="username" value="<?=$bank['username']?>"/></td>
		</tr>
		<tr> 
			<td>状态</td> 
			<td>
				<label><input type="radio" value="1" name="enable"<?php if ($bank['enable']) echo ' checked="checked"';?>>开启</label>
				<label><input type="radio" value="0" name="enable"<?php if (!$bank['enable']) echo ' checked="checked"';?>>关闭</label>
			</td> 
		<tr> 
	</tbody> 
</table>
<?php }?>
</form>
</body>
</html>