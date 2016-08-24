<?php
	$para=$_GET;
	if (!array_key_exists('actionNo', $para)) $para['actionNo'] = '';
	// 默认取今天的数据
	if(isset($para['date']) && $para['date']){
		$date=strtotime($para['date']);
	}else{
		$date=strtotime('today');
	}
	// 取彩种信息
	$sql="select * from {$this->prename}type where id=?";
	$typeInfo=$this->getRow($sql, $this->type);
	
	// 取当前彩种开奖时间表
	$sql="select * from {$this->prename}data_time where type={$this->type} order by actionTime";
	if ($para['actionNo']) $this->pageSize = 1440;
	$times=$this->getPage($sql, $this->page, $this->pageSize);
	
	$dateString=date('Y-m-d ');
	
	$sqlAmount="select sum(b.mode * b.beiShu * b.actionNum) betAmount, count(distinct(b.playedId)) facount, count(distinct(b.username)) useracount, sum(b.bonus) zjAmount, sum(b.fanDianAmount) fanDianAmount from lottery_bets b where type={$this->type} and b.isDelete=0";
	$all=$this->getRow($sqlAmount);
?>
<article class="module width_full">
<input type="hidden" value="<?=$this->user['username']?>" />
	<header>
		<h3 class="tabs_involved"><?=$typeInfo['title']?>开奖数据
		<form class="submit_link wz" action="/index.php/data/index/<?=$this->type?>" target="ajax" call="defaultSearch" dataType="html">
			期数：<input name="actionNo" type="text" value="<?php if ($para['actionNo']) echo $para['actionNo'];?>" />
			<label style="margin-left:30px;"><a class="item" href="data/index/<?=$this->type?>?date=<?=date('Y-m-d', $date-24*3600)?>">前一天</a></label>
			<label><a class="item" href="data/index/<?=$this->type?>?date=<?=date('Y-m-d', $this->time)?>">今天</a></label>
			<label><a class="item" href="data/index/<?=$this->type?>?date=<?=date('Y-m-d', $date+24*3600)?>">后一天</a></label>
			<label>日期：<input name="date" type="date" /></label>
			<input type="submit" value="查找" class="alt_btn">
			<input type="reset" value="重置条件">
		</form>
		</h3>
	</header>

	<table class="tablesorter" cellspacing="0">
		<thead>
			<tr>
				<th>彩种</th>
				<th>场次</th>
				<th>期数</th>
				<th>日期</th>
				<th>开奖数据</th>
				<th>状态</th>
				<th>开奖时间</th>
				<th>方案总数</th>
				<th>参与人数</th>
				<th>投注金额</th>
				<th>中奖金额</th>
				<th>返点金额</th>
				<th>手动开奖</th>
                <th>手动退款</th>
			</tr>
		</thead>
		<tbody>
			<?php
				$count=array();
				$dateString=date('Y-m-d ', $date);
				$search_result = false;
				$AOU = 'add';
				foreach($times['data'] as $var){
					$actionData = $this->getGameLastNo($this->type, $var['actionNo'], $var['actionTime'], $date);
					if ($para['actionNo']) {
						if ($actionData['actionNo'] == $para['actionNo']) {
							$search_result = true;
						} else {
							continue;
						}
					}
					$number = $actionData['actionNo'];
					$sql="select * from {$this->prename}data where type={$this->type} and number='$number'";
					$data=$this->getRow($sql);
					if(isset($data['data'])){
						$AOU = 'update';
						$amountData=$this->getRow($sqlAmount." and actionNo=?",$data['number']);
					}else{
						$AOU = 'add';
						$amountData=array(
							'facount' => 0,
							'useracount' => 0,
							'betAmount' => 0,
							'zjAmount' => 0,
							'fanDianAmount' => 0,
						);
					}
					$count['betAmount']+=$amountData['betAmount'];
					$count['zjAmount']+=$amountData['zjAmount'];
					$count['fanDianAmount']+=$amountData['fanDianAmount'];
			?>
			<tr>
				<td><?=$typeInfo['title']?></td>
				<td><?=$var['actionNo']?></td>
				<td><?=$this->ifs($number, '--')?></td>
				<td><?=date('Y-m-d', $date)?></td>
				<td><?=$this->ifs($data['data'], '--')?></td>
				<td><?=$this->iff($data['data'], '已开奖', '未开奖')?></td>
				<td><?=$actionData['actionTime']?></td>
				<td><?=$this->ifs($amountData['facount'], '0')?></td>
				<td><?=$this->ifs($amountData['useracount'], '0')?></td>
				<td><?=$this->ifs($amountData['betAmount'], '--')?></td>
				<td><?=$this->ifs($amountData['zjAmount'], '--')?></td>
				<td><?=$this->ifs($amountData['fanDianAmount'], '--')?></td>
				<td>
				    <?php if($AOU == 'update'){ ?>
					<a href="/index.php/data/updatedata/<?=$this->type?>/<?=$var['actionNo']?>/<?=$dateString.$var['actionTime']?>" target="modal" width="340" title="添加开奖号码" modal="true" button="确定:dataAddCode|取消:defaultCloseModal">修改</a>
					<a href="/index.php/data/kj" target="ajax" data-type="<?=$typeInfo['id']?>" data-number="<?=$data['number']?>" data-time="<?=$dateString.$var['actionTime']?>" data-data="<?=$data['data']?>" onajax="setKjData" call="setKj" title="重新对没有开奖的投注开奖">开奖</a>
					<?}else{?>
					<a href="/index.php/data/add/<?=$this->type?>/<?=$var['actionNo']?>/<?=$dateString.$var['actionTime']?>" target="modal" width="340" title="添加开奖号码" modal="true" button="确定:dataAddCode|取消:defaultCloseModal">添加</a>
					<?}?>
                    
				</td>
                <td>
                <a href="/index.php/data/back/<?=$this->type?>/<?=$var['actionNo']?>/<?=$dateString.$var['actionTime']?>"  target="modal" width="340" title="对未开奖期号进行投注退款"  modal="true" button="确定:dataAddCode|取消:defaultCloseModal" style="color:#F00;">退款</a>
                </td>
			</tr>
			<?php
				}
				if ($para['actionNo'] && !$search_result) {
					echo '<tr>';
					echo '<td colspan="14">在您选择的日期当中没有查询到对应的期号，请选择正确的日期</td>';
					echo '</tr>';
				}
			?>
            <tr>
                <td><span class="spn9">本页总结</span></td>
                <td>--</td>
                <td>--</td>
                <td>--</td>
                <td>--</td>
                <td>--</td>
                <td>--</td>
				<td>--</td>
                <td>--</td>
                <td><?=$this->ifs($count['betAmount'], '--')?></td>
                <td><?=$this->ifs($count['zjAmount'], '--')?></td>
                <td><?=$this->ifs($count['fanDianAmount'], '--')?></td>
                <td>--</td>
				<td>--</td>
            </tr>
            <tr>
                <td><span class="spn9">全部总结</span></td>
                <td>--</td>
                <td>--</td>
                <td>--</td>
                <td>--</td>
                <td>--</td>
                <td>--</td>
				<td>--</td>
                <td>--</td>
                <td><?=$this->ifs($all['betAmount'], '--')?></td>
                <td><?=$this->ifs($all['zjAmount'], '--')?></td>
                <td><?=$this->ifs($all['fanDianAmount'], '--')?></td>
                <td>--</td>
				<td>--</td>
            </tr>

		</tbody>
	</table>
	<footer>
	<?php
		$rel = $this->type;
		if($para){
			$rel.='?'.http_build_query($para,'','&');
		}
		$rel=$this->controller.'/'.$this->action .'-{page}/'.$this->type.'?'.http_build_query($_GET,'','&');
		$this->display('inc/page.php', 0, $times['total'], $rel, 'dataPageAction');
	?>
	</footer>
</article>