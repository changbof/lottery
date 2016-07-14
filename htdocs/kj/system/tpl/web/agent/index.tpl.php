<div id="agent-index-dom" class="common">
	<div class="intro">
		<div class="title">代理中心</div>
		<p class="desc">完善的推广、佣金及分红体系助您一臂之力</p>
	</div>
	<div class="head">
		<div class="name icon-shield">团队信息总览</div>
		<a href="javascript:$('#agent-spread').trigger('click');" class="link icon-link-ext">获取推广链接</a>
	</div>
	<div class="body">
		<table width="100%" border="0" cellspacing="0" cellpadding="0">
			<tr>
				<td class="key">账号类型</td>
				<td class="val"><?php echo $this->user['type'] ? '代理' : '会员';?></td>
				<td class="key">我的账号</td>
				<td class="val"><?php echo $this->user['username'];?></td>
			</tr>
			<tr>
				<td class="key">可用余额</td>
				<td class="val"><?php echo $this->user['coin'];?></td>
				<td class="key">团队余额</td>
				<td class="val"><?php echo $team_data_1['coin'];?></td>
			</tr>
			<tr>
				<td class="key">直属下级</td>
				<td class="val"><?php echo $team_data_2['count'];?></td>
				<td class="key">所有下级</td>
				<td class="val"><?php echo $team_data_1['count'] - 1;?></td>
			</tr>
		</table>
	</div>
	<div class="head">
		<div class="name icon-glass">代理分红</div>
		<?php if ($getShareBonus) {?>
		<a href="/agent/bonus_get" target="ajax" func="loadpage" class="link icon-flash">领取分红</a>
		<?php }?>
	</div>
	<div class="body">
		<table width="100%" border="0" cellspacing="0" cellpadding="0">
			<tr>
				<td class="key">未结算盈亏</td>
				<td class="val"><?php echo sprintf('%.2f',$lossAmount);?> 元</td>
				<td class="key">已结算盈亏</td>
				<td class="val"><?php echo sprintf('%.2f', $lossAmoutCount);?> 元</td>
			</tr>
			<tr>
				<td class="key">当前分红开始时间</td>
				<td class="val"><?php echo $startTime;?></td>
				<td class="key">当前分红截止时间</td>
				<td class="val"><?php echo $endTime;?></td>
			</tr>
			<tr>
				<td class="key">当前分红金额</td>
				<td class="val"><?php echo sprintf('%.2f',$bonusAmount);?> 元</td>
				<td class="key">已分红总计</td>
				<td class="val"><?php echo sprintf('%.2f', $bonusAmoutCount);?> 元</td>
			</tr>
			<tr>
				<td class="key">已结算次数</td>
				<td class="val"><?php echo $bonusCount;?> 次</td>
				<td class="key"></td>
				<td class="val"></td>
			</tr>
		</table>
	</div>
</div>