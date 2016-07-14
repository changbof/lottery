<?php
$sql="select * from {$this->prename}exchange_params";
$scoresettings = array();
$data = $this->getRows($sql);
foreach ($data as $v) $scoresettings[$v['name']] = $v['value'];
?>
<article class="module width_full">
<input type="hidden" value="<?=$this->user['username']?>" />
	<header><h3 class="tabs_involved">大转盘配置</h3></header>
	<form name="system_install" action="/index.php/Score/updateSettings" method="post" target="ajax" call="sysSettings" onajax="sysSettingsBefor">
	<table class="tablesorter left" cellspacing="0" width="100%">
		<thead>
			<tr>
				<td width="160" style="text-align:left;">配置项目</td>
				<td style="text-align:left;">配置值</td>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td>大转盘开关</td>
				<td>
					<label><input type="radio" value="1" name="switchWeb" <?=$this->iff($scoresettings['switchWeb'],'checked="checked"')?>/>开启</label>
					<label><input type="radio" value="0" name="switchWeb" <?=$this->iff(!$scoresettings['switchWeb'],'checked="checked"')?>/>关闭</label>
				</td>
			</tr>
			<tr>
				<td>兑换1元需要积分</td>
				<td>
					<input type="text" class="textWid1" value="<?=$scoresettings['score']?>" name="score"/>&nbsp个
				</td>
			</tr>
		</tbody>
	</table>
	<footer>
		<div class="submit_link">
			<input type="submit" value="保存修改设置" title="保存设置" class="alt_btn">&nbsp;&nbsp;
			<input type="button" onclick="load('Score/settings')" value="重置" title="重置原来的设置" >
		</div>
	</footer>
	</form>
</article>