<?php
	$sql="select * from {$this->prename}bank_list";
	$data=$this->getRows($sql);
?>
<article class="module width_full">
<input type="hidden" value="<?=$this->user['username']?>" />
	<header>
		<h3 class="tabs_involved">银行设置
			<div class="submit_link wz"><input type="submit" value="添加银行请联系技术人员" class="alt_btn"></div>
		</h3>
	</header>
	<table class="tablesorter" cellspacing="0" width="100%">
		<thead>
			<tr>
			    <td>ID</td>
			    <td>标识</td>
				<td>名称</td>
				<td>排序</td>
				<td>开关</td>
				<td>操作</td>
			</tr>
		</thead>
		<tbody>
		<?php if($data) foreach($data as $var){ ?>
			<tr>
			    <td><?=$var['id']?></td>
			    <td><img class="pointer" src="/skin/images/bank_<?=$var['id']?>.jpg" width="139" height="38" border="0"/></td>
				<td><?=$var['name']?></td>
				<td><?=$var['sort']?></td>
				<td><?=$this->iff($var['isDelete'], '关', '开')?></td>
				<td><a href="/index.php/system/switchBankStatus3/<?=$var['id']?>" target="ajax" call="ReloadBanklist"><?=$this->iff($var['isDelete'], '开启', '关闭')?></a> | <a href="javascript:;" onclick="sysEditBanklist(<?=$var['id']?>)">修改</a></td>
			</tr>
		<?php }else{ ?>
			<tr>
				<td colspan="5">暂时没有银行信息，请点右上角按钮添加银行</td>
			</tr>
		<?php } ?>
		</tbody>
	</table>
</article>