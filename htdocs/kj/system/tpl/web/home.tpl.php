<div id="home">
	<div class="bonus_data">
		<div id="recent_bonus_data"></div>
		<div id="yt_bonus_data">
			<div class="overview_today">
				<p class="overview_day">今日统计</p>
				<p class="overview_count" style="color:#76a4fa"><?php echo $yt_bonus_data['today']['money'];?></p>
				<p class="overview_type">盈亏</p>
				<p class="overview_count" style="color:#81c65b"><?php echo $yt_bonus_data['today']['bets'];?></p>
				<p class="overview_type">投注额</p>
			</div>
			<div class="overview_previous">
				<p class="overview_day">昨日统计</p>
				<p class="overview_count" style="color:#76a4fa"><?php echo $yt_bonus_data['yestoday']['money'];?></p>
				<p class="overview_type">盈亏</p>
				<p class="overview_count" style="color:#81c65b"><?php echo $yt_bonus_data['yestoday']['bets'];?></p>
				<p class="overview_type">投注额</p>
			</div>
		</div>
		<a href="/user/money" class="bonus_data_more btn btn-green icon-gauge" target="ajax" func="loadpage">查看更多盈亏数据</a>
	</div>
	<?php if ($this->user['type']) {?>
	<div class="agent common">
		<div class="head">
			<div class="name icon-sitemap">代理推广数据</div>
			<a href="javascript:$('#agent-spread').trigger('click');" class="link icon-link-ext">获取推广链接</a>
		</div>
		<div class="body">
			<div class="block money">
				<span class="icon icon-yen"></span>
				<span class="value red"><?php echo $agent_data['money'];?></span>
				<span class="text">返点佣金总额</span>
			</div>
			<div class="block child">
				<span class="icon icon-briefcase"></span>
				<span class="value green"><?php echo $agent_data['child'];?></span>
				<span class="text">直属下线数量</span>
			</div>
			<div class="block childs">
				<span class="icon icon-suitcase"></span>
				<span class="value blue"><?php echo $agent_data['childs'];?></span>
				<span class="text">所有下线数量</span>
			</div>
		</div>
	</div>
	<?php }?>
	<div class="bet common">
		<div class="head">
			<div class="name icon-sweden">近期投注记录</div>
			<a href="javascript:$('#bet-log').trigger('click');" class="link icon-dot-3">更多投注记录</a>
		</div>
		<div class="body">
			<?php
				if ($bet_data) {
			?>
			<table width="100%" border="0" cellspacing="0" cellpadding="0">
				<tr class="title">
					<td>编号</td>
					<td>投注时间</td>
					<td>彩种</td>
					<td>期号</td>
					<td>玩法</td>
					<td>倍数模式</td>
					<td>总额(元)</td>
					<td>奖金(元)</td>
					<td>开奖号码</td>
					<td>状态</td>
					<td>操作</td>
				</tr>
				<?php
					foreach ($bet_data as $v) {
						$this_type = $gtypes[$v['type']];
				?>
				<tr>
					<td><a href="/bet/info?id=<?php echo $v['id'];?>" title="投注信息" target="ajax" func="loadpage"><?php echo $v['wjorderId'];?></a></td>
					<td><?php echo date('m-d H:i:s', $v['actionTime']);?></td>
					<td><?php echo array_key_exists('shortName', $this_type) ? $this_type['shortName'] : $this_type['title'];?></td>
					<td><?php echo $v['actionNo'];?></td>
					<td><?php echo $plays[$v['playedId']]['name'];?></td>
					<td><?php echo $v['beiShu'];?> [<?php echo $this->modes[$v['mode']];?>]</td>
					<td><?php echo $v['mode'] * $v['beiShu'] * $v['actionNum'];?></td>
					<td><?php echo $v['lotteryNo'] ? number_format($v['bonus'], 2, '.', '') : '0.00';?></td>
					<td><?php echo $v['lotteryNo'] ? $v['lotteryNo'] : '--';?></td>
					<td><?php
						if ($v['isDelete'] == 1) {
							echo '<span class="gray">已撤单</span>';
						} elseif (!$v['lotteryNo']) {
							echo '<span class="green">未开奖</span>';
						}elseif($v['zjCount']){
							echo '<span class="red">已派奖</span>';
						}else{
							echo '未中奖';
						}
					?></td>
					<td>
					<?php if ($v['lotteryNo'] || $v['isDelete']==1 || $v['kjTime'] < $this->time) { ?>
						--
					<?php } else { ?>
						<a href="javascript:;" data-id="<?php echo $v['id'];?>" remove="false" class="remove_single">撤单</a>
					<?php } ?>
					</td>
				</tr>
				<?php }?>
			</table>
			<?php } else {?>
			<div class="empty"></div>
			<?php }?>
		</div>
	</div>
</div>
<script type="text/javascript">
$(function () {
	// 绑定撤单事件
	$('#home .bet .body .remove_single').live('click', beter.remove_single);
	// 菜单下拉固定
	$.scroll_fixed('#home .bet .head');
	// 近期中奖数据统计
    $('#recent_bonus_data').highcharts({
		credits: {
			enabled: false
		},
        chart: {
            type: 'area'
        },
        title: {
            text: '近期收益统计'
        },
        xAxis: {
            categories: [<?php echo $recent_bonus_data['xAxis'];?>],
            tickmarkPlacement: 'on',
            title: {
                enabled: false
            }
        },
        yAxis: {
            title: {
                text: '收益数额(元)'
            },
            labels: {
                formatter: function () {
                    return this.value / 1000;
                }
            }
        },
        tooltip: {
            shared: true,
            valueSuffix: ' 元'
        },
        plotOptions: {
            area: {
                stacking: 'normal',
                lineColor: '#666666',
                lineWidth: 1,
                marker: {
                    lineWidth: 1,
                    lineColor: '#666666'
                }
            }
        },
        series: [{
            name: '近期收益',
            data: [<?php echo $recent_bonus_data['series'];?>]
        }]
    });
});
</script>