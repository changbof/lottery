<?php
header('content-Type: text/html;charset=utf-8');
header("Cache-Control:no-cache");
error_reporting(E_ERROR & ~E_NOTICE);
date_default_timezone_set('PRC'); //标准化时间
error_reporting(0);
ini_set('display_errors', 'Off');

//数据库配置
$dbconf = array("conn"=>DB_HOST, "user"=>DB_USER, "pwd"=>DB_PASS, "db"=>DB_NAME);
$conf['db']['prename']=DB_PREFIX; //表前缀
?>