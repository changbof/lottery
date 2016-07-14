<div class="cash-modal" data="<?=$args[0]['id']?>">
<form action="/index.php/business/cashDealWith/<?=$args[0]['id']?>"  target="ajax" method="post" call="rechargeSubmitCode" dataType="html">
	<ul>
		<li> 银行类型：<?=$args[0]['bankName']?></li>
		<li>开户姓名：<?=$args[0]['username']?> <input name="username" id="username" type="hidden" value="<?=$args[0]['username']?>" />
        		<div style="float:right; width:62px; height:20px; line-height:20px; text-align:center; border:#CCC 1px solid; background-color:white;" onclick="copy_code($('#username').val())">复 制</div>
           </li>
           <li>银行帐号：<?=$args[0]['account']?> <input name="account" id="account" type="hidden" value="<?=$args[0]['account']?>" />
        		<div style="float:right; width:62px; height:20px; line-height:20px; text-align:center; border:#CCC 1px solid; background-color:white;" onclick="copy_code($('#account').val())">复 制</div>
           </li>
           <li>提取金额：<?=$args[0]['amount']?> <input name="amount" id="amount" type="hidden" value="<?=$args[0]['amount']?>" />
        		<div style="float:right; width:62px; height:20px; line-height:20px; text-align:center; border:#CCC 1px solid; background-color:white;" onclick="copy_code($('#amount').val())">复 制</div>
           </li>
	</ul>
	<p>
		<label><input type="radio" name="type" value="0" checked onclick="cashTrue()"/>提现成功（扣除冻结款）</label>
		<label><input type="radio" name="type" value="1" onclick="cashFalse()"/>提现失败（返还冻结款）</label>
        <input type="text" class="cashFalseSM" name="info" style="display:none; overflow-y:auto; width:100%;"  value=""/>
	</p>
</form>
</div>