<?php
	$sql="select m.*, b.name bankName from {$this->prename}admin_bank m, {$this->prename}bank_list b where b.id=m.bankid and b.isDelete=0";
	$data=$this->getPage($sql, $this->page, $this->pageSize);
?>
<article class="module width_full">
<input type="hidden" value="<?=$this->user['username']?>" />
	<header>
		<h3 class="tabs_involved">银行设置</h3>
	</header>
	<table class="tablesorter" cellspacing="0" width="100%">
		<thead>
			<tr>
			    <td>标识</td>
				<td>银行</td>
				<td>账号</td>
				<td>收款人</td>
				<td>状态(开/关)</td>
				<td>操作</td>
			</tr>
		</thead>
		<tbody>
		<?php if($data['data']) foreach($data['data'] as $var){ ?>
			<tr>
			    <td><img src="/skin/images/bank_<?=$var['id']?>.jpg" width="139" height="38" border="0"/></td>
				<td><?=$var['bankName']?></td>
				<td><?=$var['account']?></td>
				<td><?=$var['username']?></td>
				<td><?=$this->iff($var['enable'], '开', '关')?></td>
				<td><a href="/index.php/system/switchBankStatus2/<?=$var['id']?>" target="ajax" call="sysReloadBank"><?=$this->iff($var['enable'], '关闭', '开启')?></a> | <a href="javascript:;" onclick="sysEditBank(<?=$var['id']?>)">修改</a></td>
			</tr>
		<?php }else{ ?>
			<tr>
				<td colspan="5">暂时没有银行信息，请点右上角按钮添加银行</td>
			</tr>
		<?php } ?>
		<tr>
			<td colspan="5" style="padding:20px 0;border-bottom:none">其他银行收款由第三方支付完成，如需新增第三方的收款银行，请联系技术人员</td>
		</tr>
		</tbody>
	</table>
</article>