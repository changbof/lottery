<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="renderer" content="webkit">
<title><?php echo $this->config['webName'];?></title>
<link href="/static/css/reg.css" rel="stylesheet" type="text/css"/>
<link href="/static/css/icon.css" rel="stylesheet" type="text/css"/>
<script src="/static/script/jquery.1.7.2.min.js"></script>
<script src="/static/script/reg.js"></script>
</head>
<body>
<div id="reg">
	<div class="container">
		<div class="body clearfix">
			<div class="content">
				<h1 class="logo"></h1>
				<h2>让彩票融入生活，让生活更有乐趣</h2>
					<div id="form">
						<input type="text" class="hide">
						<input type="password" class="hide">
						<input type="hidden" value="<?php echo $lid;?>" id="lid">
						<div class="input has_tip trans">
							<span class="icon icon-user"></span>
							<input type="text" autocomplete="off" id="username" placeholder="请输入账户名" title="账户名">
						</div>
						<div class="tip">由字母、数字、下划线组成，4~16位字符</div>
						<div class="input trans">
							<span class="icon icon-key"></span>
							<input type="password" autocomplete="off" id="password" placeholder="请输入登录密码" title="登录密码">
						</div>
						<div class="input trans">
							<span class="icon icon-ccw"></span>
							<input type="password" autocomplete="off" id="password_repeat" placeholder="请再次输入登录密码" title="登录密码">
						</div>
						<div class="input has_tip trans">
							<span class="icon icon-qq"></span>
							<input type="text" autocomplete="off" id="qq" placeholder="请输入QQ" title="腾讯QQ">
						</div>
						<div class="tip">忘记密码时，可作为找回密码的凭据</div>
						<div class="bottom">
							<button id="submit"><span>提交注册</span></button>
						</div>
						<div class="error">
							<span class="icon-attention-alt"></span>
							<span class="text" id="error_value"></span>
						</div>
					</div>
			</div>
		</div>
		<div class="footer">
			<span class="icon-user"></span>如果您已有本站账号，您可以直接<a id="login" href="/user/login?client_type=<?php echo $this->client_type;?>">点击登录</a>
		</div>
	</div>
</div>
</body>
</html>