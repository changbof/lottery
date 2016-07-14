<?php
require_once('safe.php');
$conf['debug']['level']=5;

/*		数据库配置		*/
$conf['db']['dsn'] = 'mysql:host=localhost;dbname=lottery;port=3306';
$conf['db']['user'] = 'root';
$conf['db']['password'] = '123456@';
$conf['db']['charset'] = 'utf8';
$conf['db']['prename'] = 'lottery_';

$conf['safepass']='b123456';     //后台登陆安全码

$conf['cache']['expire'] = 0;
$conf['cache']['dir'] = '_cache/';     //前台缓存目录
$conf['url_modal'] = 2;
$conf['action']['template'] = 'tpl/';
$conf['action']['modals'] = 'mod/';
$conf['member']['sessionTime'] = 15 * 60;	// 用户有效时长
$conf['node']['access'] = 'http://localhost:65531';	// node访问基本路径

error_reporting(E_ERROR & ~E_NOTICE);
ini_set('date.timezone', 'asia/shanghai');
ini_set('display_errors', 'Off');