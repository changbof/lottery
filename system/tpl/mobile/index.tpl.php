<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1" />
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="renderer" content="webkit">
<title><?php echo $this->config['webName'];?></title>
<link href="/static/css/common_m.css?v=<?php echo $this->version;?>" rel="stylesheet" type="text/css"/>
<link href="/static/css/icon.css?v=<?php echo $this->version;?>" rel="stylesheet" type="text/css"/>
<link href="/static/css/page_m.css?v=<?php echo $this->version;?>" rel="stylesheet" type="text/css"/>
<script src="/static/script/jquery.1.7.2.min.js?v=<?php echo $this->version;?>"></script>
<script src="/static/script/jquery.zclip.min.js?v=<?php echo $this->version;?>"></script>
<script src="/static/script/jquery.cookie.js?v=<?php echo $this->version;?>"></script>
<script src="/static/script/jquery.datetimepicker.js?v=<?php echo $this->version;?>"></script>
<script src="/static/script/array.ext.js?v=<?php echo $this->version;?>"></script>
<script src="/static/script/rawdeflate.js?v=<?php echo $this->version;?>"></script>
<script src="/static/script/select.js?v=<?php echo $this->version;?>"></script>
<script src="/static/script/common_m.js?v=<?php echo $this->version;?>"></script>
<script src="/static/script/function_m.js?v=<?php echo $this->version;?>"></script>
<script src="/static/script/game_m.js?v=<?php echo $this->version;?>"></script>
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
<style type="text/css">
	#dom_main,#container{margin:0;padding:0;width:auto;}
	@media screen and (min-device-width: 620px) {
		#group_list{height:30px;}
	}
</style>
</head>
<body>
	<div id="dom_body" class="clearfix">
		<div id="div_main" class="center clearfix">
			<div id="container">
				<div id="container_warp">
					<script type="text/javascript">
						$(function() {
							$.load('/game/index?id=25', '#container_warp', {
								callback: function() {
									$.inited = true;
								},
							});
						});
					</script>
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
	<div id="cp">
		<span class="nav play-nav">
            <a href="#" id="home" class="on" type-id='60'>澳门福彩</a>
            <ul class="play-nav-list hide">
                <li><a href="/game/index?id=25" target="ajax" func="loadpage" class="icon-bookmark-empty" id="play-k3">澳门快3</a></li>
                <li><a href="/game/index?id=1" target="ajax" func="loadpage" class="icon-bookmark-empty" id="play-ssc">澳门时时彩</a></li>
            </ul>
        </span>
		<span class="nav"><a href="/user/setting" target="ajax" func="loadpage" id="user-setting">我的账户</a></span>
		<span class="nav"><a href="/bet/log" target="ajax" func="loadpage" id="bet-log">投注历史</a></span>
		<span class="nav"><a href="/game/lottery?id=25&mode=1" target="ajax" func="loadpage" id="lottery-recent">开奖记录</a></span>
		<span class="nav end"><a href="/sys/notice" target="ajax" func="loadpage" id="system-notice">系统公告</a></span>
	</div>


    <script type="text/javascript">
        $(function() {
            // 代理中心下拉菜单
            var game_nav = $('#cp .play-nav');
            if (game_nav.length > 0) {
                game_nav.hover(function() {
                    $(this).find('.play-nav-list').fadeIn(218);
                }, function() {
                    $(this).find('.play-nav-list').fadeOut(218);
                });
            };
        });
    </script>
</body>
</html>