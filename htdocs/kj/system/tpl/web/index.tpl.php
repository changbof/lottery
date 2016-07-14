<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="renderer" content="webkit">
<title><?php echo $this->config['webName'];?></title>
<link href="/static/css/common.css?v=<?php echo $this->version;?>" rel="stylesheet" type="text/css"/>
<link href="/static/css/icon.css?v=<?php echo $this->version;?>" rel="stylesheet" type="text/css"/>
<link href="/static/css/page.css?v=<?php echo $this->version;?>" rel="stylesheet" type="text/css"/>
<script src="/static/script/jquery.1.7.2.min.js?v=<?php echo $this->version;?>"></script>
<script src="/static/script/highcharts.js?v=<?php echo $this->version;?>"></script>
<script src="/static/script/jquery.zclip.min.js?v=<?php echo $this->version;?>"></script>
<script src="/static/script/jquery.cookie.js?v=<?php echo $this->version;?>"></script>
<script src="/static/script/jquery.datetimepicker.js?v=<?php echo $this->version;?>"></script>
<script src="/static/script/array.ext.js?v=<?php echo $this->version;?>"></script>
<script src="/static/script/rawdeflate.js?v=<?php echo $this->version;?>"></script>
<script src="/static/script/select.js?v=<?php echo $this->version;?>"></script>
<script src="/static/script/common.js?v=<?php echo $this->version;?>"></script>
<script src="/static/script/function.js?v=<?php echo $this->version;?>"></script>
<script src="/static/script/game.js?v=<?php echo $this->version;?>"></script>
<script src="/static/script/jquery.rotate.js?v=<?php echo $this->version;?>"></script>
<script type="text/javascript">
var datetimepicker_opt = {
	lang: 'ch',
	format: 'Y-m-d H:i',
	closeOnDateSelect: true,
	validateOnBlur: true,
	formatDate: 'Y-m-d',
	minDate: '<?php echo date('Y-m-d', $this->user['regTime']);?>',
	maxDate: '<?php echo date('Y/m/d');?>',
};
</script>
</head>
<body>
<div id="dom_body">
<div id="header">
	<div class="center">
		<a href="javascript:$.reload('/');" class="logo"><?php echo $this->config['webName'];?></a>
		<?php if ($this->config['webGG'] && (!array_key_exists('ntc', $_COOKIE) || $_COOKIE['ntc'] != 1)) {?>
		<div class="notice">
			<span class="icon icon-volume-down"></span>
			<marquee direction="left" scrollamount="2" scrolldelay="1"><?php echo $this->config['webGG'];?></marquee>
			<span class="close btn btn-green icon-cancel" id="nt-close">不再显示</span>
		</div>
		<?php }?>
		<div class="client">
			<a href="javascript:;">
				<span class="icon icon-windows"></span>
				<span class="text">Windows</span>
			</a>
			<a href="javascript:;">
				<span class="icon icon-android-1"></span>
				<span class="text">Android</span>
			</a>
			<a href="javascript:;">
				<span class="icon icon-appstore"></span>
				<span class="text">iPhone</span>
			</a>
		</div>
	</div>
</div>
<div id="nav">
	<div class="center">
		<div class="game-bg"></div>
		<a href="javascript:$.reload('/');" class="home icon-home on" id="home">首页</a>
		<a href="/user/setting" target="ajax" func="loadpage" class="icon-cog" id="user-setting">个人设置</a>
		<a href="/bet/log" target="ajax" func="loadpage" class="icon-th-list" id="bet-log">投注记录</a>
		<a href="/user/money" target="ajax" func="loadpage" class="icon-chart-pie" id="user-money">盈亏报表</a>
		<?php if ($this->user['type']) {?>
		<div class="agent-nav">
			<a href="/agent/index" target="ajax" func="loadpage" class="icon-diamond" id="agent">代理中心</a>
			<ul class="agent-nav-list hide">
				<li><a href="/agent/member" target="ajax" func="loadpage" class="icon-address-book" id="agent-member">会员管理</a></li>
				<li><a href="/agent/log" target="ajax" func="loadpage" class="icon-list-alt" id="agent-log">团队记录</a></li>
				<li><a href="/agent/money" target="ajax" func="loadpage" class="icon-yen" id="agent-money">盈亏统计</a></li>
				<li><a href="/agent/coin" target="ajax" func="loadpage" class="icon-chart-bar" id="agent-coin">帐变日志</a></li>
				<li><a href="/agent/spread" target="ajax" func="loadpage" class="icon-link-ext" id="agent-spread">推广链接</a></li>
			</ul>
		</div>
		<?php }?>
		<a href="/activity/rotary" target="ajax" func="loadpage" class="icon-coffee" id="activity">活动中心</a>
		<a href="/sys/notice" target="ajax" func="loadpage" class="icon-bell-alt" id="system-notice">系统公告</a>
		<?php if ($this->config['kefuStatus']) {?>
		<a href="<?php echo $this->config['kefuGG'];?>" target="_blank" class="sign icon-cloud">在线客服</a>
		<?php }?>
	</div>
</div>
<div id="main" class="center clearfix">
	<div id="sidebar">
		<div class="game icon-th-large">游戏中心</div>
		<div class="user">
			<div class="info" id="user-info">
				<div class="username"><span title="---" class="unval icon-user">彩神大名：---</span><span class="level">0</span></div>
				<div class="balance icon-fire-1">可用余额：00.000</div>
				<div class="score icon-certificate">账户积分：0</div>
			</div>
			<div class="opt">
				<div class="line">
					<a href="/user/recharge" target="ajax" func="loadpage" id="user-recharge">
						<span class="icon icon-credit-card"></span>
						<span class="text">充值</span>
					</a>
					<a href="/user/cash" target="ajax" func="loadpage" id="user-cash">
						<span class="icon icon-paper-plane-empty"></span>
						<span class="text">提现</span>
					</a>
					<a href="/user/message_receive" target="ajax" func="loadpage" class="pr" id="message-receive">
						<span class="icon icon-mail"></span>
						<span class="text">私信</span>
					</a>
				</div>
				<div class="line">
					<a href="/user/sign" target="ajax" func="loadpage">
						<span class="icon icon-calendar-empty"></span>
						<span class="text">签到</span>
					</a>
					<a href="/user/coin" target="ajax" func="loadpage" id="user-coin">
						<span class="icon icon-chart-line"></span>
						<span class="text">帐变</span>
					</a>
					<a href="/user/logout" target="ajax" func="loadpage">
						<span class="icon icon-off"></span>
						<span class="text">退出</span>
					</a>
				</div>
			</div>
		</div>
		<div class="gs" id="game-nav">
			<?php
				$types = $this->get_types();
				foreach ($types as $name => $list) {
					if (!$list) continue;
			?>
			<div class="g">
				<div class="game-name icon-bookmark">
					<span class="text"><?php echo $name;?></span>
					<span class="count"><?php echo count($list);?> 款彩种</span>
				</div>
				<ul class="list">
					<?php foreach ($list as $v) {?>
					<li>
						<a type-id="<?php echo $v['id'];?>" href="/game/index?id=<?php echo $v['id'];?>" target="ajax" func="loadpage">
							<?php echo $v['title'];?>
						</a>
					</li>
					<?php }?>
				</ul>
			</div>
			<?php }?>
		</div>
	</div>
	<div id="container">
		<div id="container_warp">
			<?php if (isset($load_self) && $load_self) {?>
			<script type="text/javascript">
				$(function() {
					$.load(window.location.href, '#container_warp', {
						callback: function() {
							$.inited = true;
						},
					});
				});
			</script>
			<?php
				} else {
					require(TPL.'/home.tpl.php');
					echo '<script type="text/javascript">$.inited = true;</script>';
				}
			?>
		</div>
	</div>
</div>
<div id="loading-page" class="dialogue"><div class="dialogue-warp"></div></div>
<div id="dialogue" class="dialogue">
	<div class="dialogue-warp">
		<div class="dialogue-head">
			<span class="dialogue-title icon-lamp">系统提示</span>
		</div>
		<div class="dialogue-body"></div>
		<div class="dialogue-foot">
			<div class="dialogue-auto">
				<span class="dialogue-sec"></span>秒后自动关闭
			</div>
			<div class="right">
				<button class="dialogue-yes btn btn-blue icon-ok"></button>
				<button class="dialogue-no btn btn-white icon-undo"></button>
			</div>
		</div>
	</div>
</div>
</div>
<script type="text/javascript">
$(function() {
	// 更新用户信息
	lottery.user_fresh();
	// 代理中心下拉菜单
	var agent_nav = $('#nav .agent-nav');
	if (agent_nav.length > 0) {
		agent_nav.hover(function() {
			$(this).find('.agent-nav-list').fadeIn(218);
		}, function() {
			$(this).find('.agent-nav-list').fadeOut(218);
		});
	}
	// 关闭滚动公告
	$('#nt-close').bind('click', function() {
		$(this).parent().fadeOut(function() {
			$.cookie('ntc', 1);
		});
	});
});
</script>
</body>
</html>