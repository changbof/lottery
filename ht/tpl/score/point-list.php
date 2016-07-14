<?php
	// 帐号限制
	if($_REQUEST['username']&&$_REQUEST['username']!="用户名"){
		$_REQUEST['username']=wjStrFilter($_REQUEST['username']);
		if(!preg_match('/^\w{4,16}$/',$_REQUEST['username'])) throw new Exception('用户名包含非法字符,请重新输入');
		$userWhere="and u.username like '%{$_REQUEST['username']}%'";
	}

	// 时间限制
	if($_REQUEST['fromTime'] && $_REQUEST['toTime']){
		$fromTime=strtotime($_REQUEST['fromTime']);
		$toTime=strtotime($_REQUEST['toTime'])+24*3600;
		$timeWhere="and s.swapTime between $fromTime and $toTime";
	}elseif($_REQUEST['fromTime']){
		$fromTime=strtotime($_REQUEST['fromTime']);
		$timeWhere="and s.swapTime>=$fromTime";
	}elseif($_REQUEST['toTime']){
		$toTime=strtotime($_REQUEST['toTime'])+24*3600;
		$timeWhere="and s.swapTime<$fromTime";
	}
	
	$sql="select s.*,u.username userName from {$this->prename}exchange_swap s, {$this->prename}members u where s.uid=u.uid $userWhere $typeWhere $timeWhere order by s.swapTime desc";
	//echo $sql;
	$data=$this->getPage($sql, $this->page, $this->pageSize);
?>

<script type="text/javascript">
$(function(){
	$('.tabs_involved input[name=username]')
	.focus(function(){
		if(this.value=='用户名') this.value='';
	})
	.blur(function(){
		if(this.value=='') this.value='用户名';
	})
	.keypress(function(e){
		if(e.keyCode==13) $(this).closest('form').submit();
	});
	
});
</script>

<article class="module width_full">
<input type="hidden" value="<?=$this->user['username']?>" />
    <header>
    	<h3 class="tabs_involved">兑换记录
            <div class="submit_link wz">
            	<form action="/index.php/dzp/pointList" target="ajax" call="defaultSearch" dataType="html">
                会员：<input name="username" type="text" style="width:100px;" value="用户名"/>&nbsp;&nbsp;
                时间：从 <input type="date" style="width:75px;" name="fromTime"/> 到 <input type="date" style="width:75px;" name="toTime"/>&nbsp;&nbsp;
                <input type="submit" value="查找" class="alt_btn">
                <input type="reset" value="重置条件">
                </form>
            </div>
        </h3>
    </header>
    <div class="tab_content">
	<table class="tablesorter" cellspacing="0"> 
	<thead> 
		<tr> 
			<th>单号</th> 
			<th>用户</th>  
			<th>消耗积分</th>
			<th>剩余积分</th>
			<th>中奖日期</th>
			<th>操作</th> 
		</tr> 
	</thead> 
	<tbody> 
		<?php if($data['data']) foreach($data['data'] as $var){
		?>
		<tr> 
			<td><?=$var['id']?></td>
			<td><?=$var['userName']?></td>
			<td><?=$var['xscore']?></td>
			<td><?=$var['score']?></td>
			<td><?=date('Y-m-d H:i:s',$var['swapTime'])?></td> 
			<td>
                <a href="/index.php/dzp/pointDel/<?=$var['id']?>" target="ajax" call="pointHandle" dataType="json">删除</a>
            </td> 
		</tr>
		<?php }else{ ?>
			<tr>
				<td colspan="5">暂时没有兑换订单</td>
			</tr>
		<?php } ?>
	</tbody> 
    </table>
	<footer>
	<?php
		$rel=get_class($this).'/pointList-{page}?'.http_build_query($_GET,'','&');
		$this->display('inc/page.php', 0, $data['total'], $rel, 'betLogSearchPageAction'); 
	?>
	</footer>
    </div><!-- end of .tab_container -->
</article><!-- end of content manager article -->
