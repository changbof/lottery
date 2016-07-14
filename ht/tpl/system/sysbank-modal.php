<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<input type="hidden" value="<?=$this->user['username']?>" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>银行管理</title>
<link rel="stylesheet" type="text/css" href="/skin/admin/layout.css" media="all" />
</head>
<body>
<form name="system_addBanklist" action="/index.php/system/updateBanklist" enctype="multipart/form-data" method="POST">
<?php
	$id = intval($args[0]);
	$bank=$this->getRow("select * from {$this->prename}bank_list where id={$id} order by sort");
	if($bank) {
?>
<table class="tablesorter left" cellspacing="0" width="100%">
	<thead> 
		<tr> 
			<td>项目</td> 
			<td>值</td> 
		</tr> 
	</thead>
	<tbody>
	    <tr> 
			<td>银行ID</td> 
			<td><input type="text" name="id" value="<?=$bank['id']?>" readonly></td>
		</tr>
		<tr>
		<tr> 
			<td>银行名称</td> 
			<td><input type="text" name="name" value="<?=$bank['name']?>"/></td>
		</tr>
		<tr> 
			<td>银行顺序</td> 
			<td><input type="text" name="sort" value="<?=$bank['sort']?>"/></td>
		</tr>
		<tr> 
			<td>状态</td> 
			<td>
				<label><input type="radio" value="0" name="isDelete"<?php if ($bank['enable']) echo ' checked="checked"';?>>开启</label>
				<label><input type="radio" value="1" name="isDelete"<?php if (!$bank['enable']) echo ' checked="checked"';?>>关闭</label>
			</td> 
		<tr> 
	</tbody> 
</table>
<?php }?>
</form>
</body>
</html>