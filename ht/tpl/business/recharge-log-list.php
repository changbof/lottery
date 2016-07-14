<?php
	$para=$_GET;
	
	// 用户限制
	if(array_key_exists('username', $para) && $para['username'] && $para['username']!="用户名"){
		$para['username']=wjStrFilter($para['username']);
		if(!preg_match('/^\w{4,16}$/',$para['username'])) throw new Exception('用户名包含非法字符,请重新输入');
		$userWhere="and u.username like '%{$para['username']}%'";
	} else {
		$userWhere = '';
	}

	// 充值编号限制
	if(array_key_exists('rechargeId', $para) && $para['rechargeId'] && $para['rechargeId']!="充值编号"){
		$para['rechargeId']=wjStrFilter($para['rechargeId'],0,0);
		if(!ctype_digit($para['rechargeId'])) throw new Exception('充值编号包含非法字符');
		$rechargeIdWhere="and c.rechargeId={$para['rechargeId']}";
	} else {
		$rechargeIdWhere = '';
	}

	//状态类型限制
	if(array_key_exists('type', $para) && ($para['type'] = intval($para['type']))) {
		if($para['type']==99){
			$typeWhere="and c.state=0";
		} else {
			$typeWhere="and c.state={$para['type']}";
		}
	} else {
		$typeWhere = '';
	}
	
	// 时间限制
	if(array_key_exists('fromTime', $para) && $para['fromTime'] && $para['toTime']){
		$fromTime=strtotime($para['fromTime']);
		$toTime=strtotime($para['toTime'])+24*3600;
		$timeWhere="and c.actionTime between $fromTime and $toTime";
	}elseif(array_key_exists('fromTime', $para) && $para['fromTime']){
		$fromTime=strtotime($para['fromTime']);
		$timeWhere="and c.actionTime>=$fromTime";
	}elseif(array_key_exists('toTime', $para) && $para['toTime']){
		$toTime=strtotime($para['toTime'])+24*3600;
		$timeWhere="and c.actionTime<$fromTime";
	}else{
		$timeWhere='';
	}

	$sql="select c.*, u.username, u.parents from {$this->prename}member_recharge c, {$this->prename}members u where c.isDelete=0 $rechargeIdWhere $timeWhere $userWhere $typeWhere and c.uid=u.uid order by c.id desc";
	//echo $sql;
	$data=$this->getPage($sql, $this->page, $this->pageSize);
	
	$sql="select * from {$this->prename}bank_list where isDelete=0";
	$banks = array();
	$bdata=$this->getRows($sql);
	foreach ($bdata as $v) $banks[$v['id']] = $v;
?>
<table class="tablesorter" cellspacing="0">
<input type="hidden" value="<?=$this->user['username']?>" />
<thead>
    <tr>
        <th>UserID</th>
        <th>用户名</th>
		<th>上级关系</th>
        <th>充值金额</th>
        <th>实际到账</th>
        <th>充值前资金</th>
        <th>充值编号</th>
        <th>充值银行</th>
        <th>状态</th>
        <th>备注</th>
        <th>时间</th>
        <th>操作</th>
    </tr>
</thead>
<tbody id="nav01">
<?php
	if($data['data']) {
		$amount = 0;
		foreach($data['data'] as $var){
			if($var['state']) $amount+=$var['rechargeAmount'];
			$var['parents'] = trim($var['parents'], ',');
			if (array_key_exists($var['mBankId'], $banks)) {
				$bank_name = $banks[$var['mBankId']]['name'];
			} else {
				$bank_name = '';
			}
?>
    <tr>
        <td><?=$var['uid']?></td>
        <td><?=$var['username']?></td>
		<td><?=implode('> ',$this->getCol("select username from {$this->prename}members where uid in ({$var['parents']})"))?></td>
        <td><?=$var['amount']?></td>
        <td><?=$var['rechargeAmount']?></td>
        <td><?=$this->iff($var['state'], $var['coin'], '--')?></td>
        
        
        <td><?=$var['rechargeId']?></td>
        <td><?=$bank_name?></td>
        <td><?=$this->iff($var['state'], '充值成功', '正在充值')?></td>
        <td><?=$var['info']?></td>
        <td><?=date('Y-m-d H:i:s', $var['actionTime'])?></td>
        <td>
            <?php if(!$var['state']){ ?>
            <a href="/index.php/business/rechargeActionModal/<?=$var['id']?>" target="modal"  width="420" title="编辑用户" modal="true" button="确定:dataAddCode|取消:defaultCloseModal">到帐处理</a>
            <a href="/index.php/business/rechargeDelete/<?=$var['id']?>" target="ajax" dataType="json" call="defaultAjaxLink">删除</a>
            <?php }else{ ?>
            <a>--</a>
            <?php }?>
            
        </td>
    </tr>
<?php }}else{ ?>
    <tr>
        <td colspan="9" align="center">暂时没有充值记录。</td>
    </tr>
<?php } ?>
</tbody>
</table>
<tr><span style="font-size:15px;color:#FF0000;margin-left:540px;line-height:40px">本次统计充值总额：<?=$this->iff($amount,$amount,0)?>元</span></tr>
<footer>
    <?php
		$rel=get_class($this).'/rechargeLog-{page}?'.http_build_query($_GET,'','&');
		$this->display('inc/page.php', 0, $data['total'], $rel, 'defaultReplacePageAction');
	?>
</footer>
<script type="text/javascript">  
ghhs("nav01","tr");  
</script>