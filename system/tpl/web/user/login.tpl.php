<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="renderer" content="webkit">
<title><?php echo $this->config['webName'];?></title>
<link href="/static/css/login.css" rel="stylesheet" type="text/css"/>
<link href="/static/css/icon.css" rel="stylesheet" type="text/css"/>
<script src="/static/script/jquery.1.7.2.min.js"></script>
<script src="/static/script/login.js"></script>
</head>
<body>
<div id="login">
	<div class="container">
		<div class="body clearfix">
			<div class="content">
				<h1 class="logo"></h1>
				<h2>让彩票融入生活，让生活更有乐趣</h2>
					<div id="form">
						<input type="text" class="hide">
						<input type="password" class="hide">
						<div class="input trans">
							<span class="icon icon-user"></span>
							<input type="text" autocomplete="off" id="username" placeholder="请输入账户名" title="账户名"<?php echo $username;?>>
						</div>
						<div class="input trans">
							<span class="icon icon-key"></span>
							<input type="password" autocomplete="off" id="password" placeholder="请输入登录密码" title="登录密码">
						</div>
						<div class="bottom">
							<label><input type="checkbox" id="remember"<?php echo $remember;?>>记住账户</label>
							<button id="submit"><span>登录</span></button>
						</div>
						<div class="error">
							<span class="icon-attention-alt"></span>
							<span class="text" id="error_value"></span>
						</div>
					</div>
			</div>
			<div class="sidebar">
				<div class="name icon-sweden">客户端下载</div>
				<a href="javascript:;" class="download blue">
					<span class="icon icon-windows"></span>
					<span class="text">Windows</span>
					<span class="tip trans">开发中...</span>
				</a>
				<a href="javascript:;" class="download green">
					<span class="icon icon-android-1"></span>
					<span class="text">Android</span>
					<span class="tip trans">开发中...</span>
				</a>
				<a href="javascript:;" class="download purple">
					<span class="icon icon-appstore"></span>
					<span class="text">iPhone</span>
					<span class="tip trans">开发中...</span>
				</a>
			</div>
		</div>
		<div class="footer">
			<span class="icon-html5"></span>本站建议您使用IE8以上等支持HTML5的浏览器获取最佳使用体验
		</div>
	</div>
</div>
</body>
</html>