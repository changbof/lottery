<?php
require_once('safe.php');
$conf['debug']['level']=5;

/*		���ݿ�����		*/
$conf['db']['dsn'] = 'mysql:host=localhost;dbname=lottery;port=3306';
$conf['db']['user'] = 'root';
$conf['db']['password'] = '123456@';
$conf['db']['charset'] = 'utf8';
$conf['db']['prename'] = 'lottery_';

$conf['safepass']='b123456';     //��̨��½��ȫ��

$conf['cache']['expire'] = 0;
$conf['cache']['dir'] = '_cache/';     //ǰ̨����Ŀ¼
$conf['url_modal'] = 2;
$conf['action']['template'] = 'tpl/';
$conf['action']['modals'] = 'mod/';
$conf['member']['sessionTime'] = 15 * 60;	// �û���Чʱ��
$conf['node']['access'] = 'http://localhost:65531';	// node���ʻ���·��

error_reporting(E_ERROR & ~E_NOTICE);
ini_set('date.timezone', 'asia/shanghai');
ini_set('display_errors', 'Off');