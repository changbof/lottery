<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="utf-8" />
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
<meta name="renderer" content="webkit" />
<title>Request Error</title>
<style type="text/css">
body{font:12px 'Century Gothic','Microsoft YaHei',\5FAE\8F6F\96C5\9ED1,Tahoma,Verdana,Arial,Arial,helvetica,sans-serif;background-color:#F5F5F5;margin:0}
#error{border:1px solid #DDD;box-shadow:0 0 5px 0 #DDD;margin:5% auto;width:500px;background-color:#FFF}
#error .head{height:34px;line-height:34px;padding:10px 15px;border-bottom:1px solid #DDD;border-top:2px solid #5990C4}
#error .head .name{color:#5990C4;font-size:15px;float:left}
#error .head .back{float:right;text-align:center;border:1px solid #CDCDCD;cursor:pointer;border-radius:2px;color:#888;background-color:#F9F9F9;box-shadow:0 2px 2px 0 rgba(0,0,0,0.08);text-decoration:none;width:80px;font-size:14px;height:32px;line-height:32px;}
a{text-decoration:none;-webkit-transition:all .218s linear;-moz-transition:all .218s linear;transition:all .218s linear}
#error .head .back:hover{color:#777;border-color:#C5C5C5;background-color:#F2F2F2}
#error .head .back:active{background-color:#EDEDED;box-shadow:inset 0 0 5px #D4D4D4}
#error .body{border-top:1px solid #EDEDED}
#error .body .container{line-height:24px;padding:30px 15px 20px;border-bottom:1px solid #DDD}
#error .body .error{background-color:#FFECEC}
#error .body .success{background-color:#EEFFEC}
#error .body .container .errtit{font-size:23px;color:#666}
#error .body .container .errmsg{font-size:13px;margin-top:10px;color:#999}
#error .footer{background-color:#F9F9F9;padding:15px;color:#AAA;height:17px;text-align:right}
</style>
</head>
<body>
<div id="error">
	<div class="head">
		<div class="name">系统错误提示</div>
		<a href="javascript:history.back(-1)" class="back">返回</a>
	</div>
	<div class="body">
		<div class="container error">
			<div class="errtit">200 . Request Error</div>
			<div class="errmsg"><?php echo $msg;?></div>
		</div>
	</div>
	<div class="footer">&copy; <?php echo date('Y');?> <?php echo $_SERVER['SERVER_NAME'];?></div>
</div>
</body>
</html>