<?php

class mod_agent extends mod {
	
	public function __construct() {
		parent::__construct();
		if (!$this->user['type']) core::error('您不是代理，没有权限执行此操作');
	}
	
	private function is_child($uid, $username = null) {
		$self = $this->user['uid'];
		if ($uid) {
			return $this->db->query("SELECT `uid` FROM `{$this->db_prefix}members` WHERE `uid`='{$uid}' AND CONCAT(',',parents,',') LIKE '%,{$self},%' LIMIT 1", 2) ? true : false;
		} else if ($username) {
			return $this->db->query("SELECT `uid` FROM `{$this->db_prefix}members` WHERE `username`='{$username}' AND CONCAT(',',parents,',') LIKE '%,{$self},%' LIMIT 1", 2) ? true : false;
		} else {
			return false;
		}
	}

	public function index() {
		$this->user_check_func();
		if ($this->post) {
			// 分红
			$uid = $this->user['uid'];
			$args = array();
			$sql = "SELECT * FROM `{$this->db_prefix}bonus_log` WHERE `uid`={$uid} AND `bonusStatus`=0 ORDER BY `id` DESC LIMIT 1";
			$last_bonus = $this->db->query($sql, 2);
			if ($last_bonus) {
				$args['lossAmount'] = $last_bonus['lossAmount'];
				$args['bonusAmount'] = $last_bonus['bonusAmount'];
				$args['startTime'] = date('Y-m-d H:i:s', $last_bonus['startTime']);
				$args['endTime'] = date('Y-m-d H:i:s', $last_bonus['endTime']);
			} else {
				$dayDate = date('d',time());
				if (1 <= $dayDate && $dayDate < 11) {
					$startTime = date('Y-m-1',time()).' 03:00:00';
					$endTime = date('Y-m-11',time()).' 03:00:00';
				} else if (11 <= $dayDate && $dayDate < 21) {
					$startTime = date('Y-m',time()).'-11 03:00:00';
					$endTime = date('Y-m',time()).'-21 03:00:00';
				} else if (21 <= $dayDate) {
					$startTime = date('Y-m',time()).'-21 03:00:00';
					$endTime = date('Y-m-1',strtotime('+1 month')).' 03:00:00';
				}
				$args['lossAmount'] = 0;
				$args['bonusAmount'] = 0;
				$args['startTime'] = $startTime;
				$args['endTime'] = $endTime;
			}
			$lossAmoutCount	= $this->db->query("SELECT SUM(lossAmount) AS lossAmount FROM `{$this->db_prefix}bonus_log` WHERE `uid`={$uid} AND `bonusStatus`=1", 2);
			$args['lossAmoutCount'] = $lossAmoutCount['lossAmount'];
			$bonusAmoutCount = $this->db->query("SELECT SUM(bonusAmount) AS bonusAmount FROM `{$this->db_prefix}bonus_log` WHERE `uid`={$uid} AND `bonusStatus`=1", 2);
			$args['bonusAmoutCount'] = $bonusAmoutCount['bonusAmount'];
			$bonusCount = $this->db->query("SELECT COUNT(*) AS __total FROM `{$this->db_prefix}bonus_log` WHERE `uid`={$uid} AND `bonusStatus`=1", 2);
			$args['bonusCount'] = $bonusCount['__total'];
			$args['getShareBonus'] = ($last_bonus && floatval($last_bonus['bonusAmount']) > 0) ? true : false;
			// 信息总览
			$sql_team_data_1 = "SELECT SUM(u.coin) coin, COUNT(u.uid) count FROM `{$this->db_prefix}members` u WHERE u.isDelete=0 AND CONCAT(',', u.parents, ',') LIKE '%,{$uid},%'";
			$args['team_data_1'] = $this->db->query($sql_team_data_1, 2);
			$sql_team_data_2 = "SELECT COUNT(u.uid) count FROM `{$this->db_prefix}members` u WHERE u.isDelete=0 AND u.parentId={$uid}";
			$args['team_data_2'] = $this->db->query($sql_team_data_2, 2);
			$this->display('agent/index', $args);
		} else {
			$this->ajax();
		}
	}
	
	public function member() {
		$this->user_check_func();
		if ($this->post) {
			$tpl = $this->ispage ? '/agent/member_body' : '/agent/member';
			$args = $this->member_get_args();
			$page_current = $this->get_page();
			$page_args = $this->member_page_args($args);
			$member_list = $this->member_search_func($args, $page_current);
			$page_max = $this->get_page_max($member_list['total']);
			$max = $this->user['fanDian'] - $this->config['fanDianDiff'];
			$this->display($tpl, array(
				'max' => $max < 0 ? 0 : $max,
				'args' => $args,
				'data' => $member_list['data'],
				'page_current' => $page_current,
				'page_max' => $page_max,
				'page_url' => '/agent/member?'.http_build_query($page_args),
				'page_container' => '#agent-member-dom .body',
			));
		} else {
			$this->ajax();
		}
	}
	
	public function member_search() {
		$this->user_check_func();
		$this->check_post();
		$page_current = $this->get_page(false);
		$args = $this->member_get_args(false);
		$page_args = $this->member_page_args($args);
		$member_list = $this->member_search_func($args, 1);
		$page_max = $this->get_page_max($member_list['total']);
		$max = $this->user['fanDian'] - $this->config['fanDianDiff'];
		$this->display('/agent/member_body', array(
			'max' => $max < 0 ? 0 : $max,
			'data' => $member_list['data'],
			'page_current' => $page_current,
			'page_max' => $page_max,
			'page_url' => '/agent/member?'.http_build_query($page_args),
			'page_container' => '#agent-member-dom .body',
		));
	}
	
	private function member_search_func($args, $page_current) {
		$uid = $this->user['uid'];
		$pagesize = $this->pagesize;
		$skip = ($page_current - 1) * $pagesize;
		$sql = "SELECT ~field~ FROM `{$this->db_prefix}members` WHERE ";
		if ($args['username']) {
			$sql .= "`username`='{$args['username']}' AND CONCAT(',',parents,',') LIKE '%,{$uid},%'";
		} else {
			switch($args['type']){
				case 0: // 所有人
					$sql .= "CONCAT(',',parents,',') LIKE '%,{$uid},%'";
				break;

				case 1: // 直属下级
					if (!$this->is_child($args['uid'])) core::__403();
					$sql .= "parentId={$args['uid']}";
				break;
				
				case 2: // 所有下级
					$sql .= "CONCAT(',',parents,',') LIKE '%,{$uid},%' AND `uid`!={$uid}";
				break;
			}
		}
		$sql .= ' ~order~ ~limit~';
		$sql_total = str_replace('~field~', 'COUNT(1) AS __total', $sql);
		$sql_total = str_replace('~limit~', '', $sql_total);
		$sql_total = str_replace('~order~', '', $sql_total);
		$total = $this->db->query($sql_total, 2);
		$total = $total['__total'];
		$sql_data = str_replace('~field~', '*', $sql);
		$sql_data = str_replace('~limit~', "LIMIT $skip,$pagesize", $sql_data);
		$sql_data = str_replace('~order~', 'ORDER BY `uid` DESC', $sql_data);
		$data = $this->db->query($sql_data, 3);
		return array(
			'data' => $data,
			'total' => $total,
		);
	}
	
	private function member_get_args($get = true) {
		$data = $get ? $_GET : $_POST;
		$args = array();
		$args['type'] = (array_key_exists('type', $data) && in_array($data['type'], array(0, 1, 2))) ? intval($data['type']) : 0;
		$args['online'] = (array_key_exists('online', $data) && in_array($data['online'], array(0, 1))) ? intval($data['online']) : -1;
		$args['username'] = (array_key_exists('username', $data) && core::lib('validate')->username($data['username'])) ? $data['username'] : '';
		$args['uid'] = (array_key_exists('uid', $data) && core::lib('validate')->number($data['uid'])) ? intval($data['uid']) : $this->user['uid'];
		return $args;
	}
	
	private function member_page_args($args) {
		$page_args = array();
		if ($args['type'] !== 0) $page_args['type'] = $args['type'];
		if ($args['online'] !== -1) $page_args['online'] = $args['online'];
		if ($args['username']) $page_args['username'] = $args['username'];
		if ($args['uid'] != $this->user['uid']) $page_args['uid'] = $args['uid'];
		$page_args['page'] = '{page}';
		return $page_args;
	}

	public function log() {
		if ($this->post) {
			$tpl = $this->ispage ? '/agent/log_body' : '/agent/log';
			$this->get_time();
			$args = $this->log_get_args();
			$page_current = $this->get_page();
			$game_log = $this->log_search_func($args, $page_current);
			$page_max = $this->get_page_max($game_log['total']);
			if ($page_current > $page_max) core::__403();
			$page_args = $this->common_page_args($args);
			$this->display($tpl, array(
				'args' => $args,
				'_types' => $this->get_types(),
				'types' => core::lib('game')->get_types(),
				'plays' => $this->get_plays(),
				'state' => array(0 => '所有状态', 1=> '已派奖', 2 => '未中奖', 3 => '未开奖', 4 => '追号', 5 => '合买跟单', 6 => '撤单'),
				'data' => $game_log['data'],
				'page_current' => $page_current,
				'page_max' => $page_max,
				'page_url' => '/agent/log?'.http_build_query($page_args),
				'page_container' => '#agent-log-dom .body',
			));
		} else {
			$this->ajax();
		}
	}

	public function log_search() {
		$this->check_post();
		$this->get_time(false);
		$args = $this->log_get_args(false);
		$game_log = $this->log_search_func($args, 1);
		$page_max = $this->get_page_max($game_log['total']);
		$page_args = $this->common_page_args($args);
		$this->display('/agent/log_body', array(
			'types' => core::lib('game')->get_types(),
			'plays' => $this->get_plays(),
			'data' => $game_log['data'],
			'page_current' => 1,
			'page_max' => $page_max,
			'page_url' => '/agent/log?'.http_build_query($page_args),
			'page_container' => '#agent-log-dom .body',
		));
	}

	private function log_search_func($args, $page_current) {
		$uid = $this->user['uid'];
		$pagesize = $this->pagesize;
		$skip = ($page_current - 1) * $pagesize;
		$sql = "SELECT ~field~ FROM `{$this->db_prefix}bets` b";
		if ($args['username']) {
			$where = '';
		} else {
			$sql .= ", `{$this->db_prefix}members` u";
			$where = 'u.uid = b.uid';
		}
		$sql .= ' WHERE ';
		if ($args['type']) $where .= " AND b.`type`={$args['type']}";
		if ($args['state']) {
			switch ($args['state']) {
				case 1: // 已派奖
					$where .= ' AND b.`zjCount`>0';
				break;

				case 2: // 未中奖
					$where .= " AND b.`zjCount`=0 AND b.`lotteryNo`!='' AND b.`isDelete`=0";
				break;

				case 3: // 未开奖
					$where .= " AND b.`lotteryNo`=''";
				break;

				case 4: // 追号
					$where .= ' AND b.`zhuiHao`=1';
				break;
				
				case 5: // 合买跟单
					$where .= ' AND b.`hmEnable`=1';
				break;

				case 6: // 撤单
					$where .= ' AND b.`isDelete`=1';
				break;
			}
		}
		if ($args['username']) {
			if (!$this->is_child(null, $args['username'])) core::error('['.$args['username'].']不是您的下级会员');
			$where .= " AND b.`username`='{$args['username']}'";
		} else {
			switch($args['a_type']){
				case 0: // 所有人
					$where .= " AND CONCAT(',',u.parents,',') LIKE '%,{$uid},%'";
				break;

				case 1: // 直属下级
					$where .= " AND u.parentId={$uid}";
				break;
				
				case 2: // 所有下级
					$where .= " AND CONCAT(',',u.parents,',') LIKE '%,{$uid},%' AND u.`uid`!={$uid}";
				break;
			}
		}
		$where .= $this->build_where_time('b.`actionTime`');
		if (substr($where, 0, 5) === ' AND ') $where = substr($where, 5);
		$sql .= $where;
		$sql .= ' ~order~ ~limit~';
		$sql_total = str_replace('~field~', 'COUNT(1) AS __total', $sql);
		$sql_total = str_replace('~limit~', '', $sql_total);
		$sql_total = str_replace('~order~', '', $sql_total);
		$total = $this->db->query($sql_total, 2);
		$total = $total['__total'];
		$sql_data = str_replace('~field~', 'b.*', $sql);
		$sql_data = str_replace('~limit~', "LIMIT $skip,$pagesize", $sql_data);
		$sql_data = str_replace('~order~', 'ORDER BY b.`id` DESC', $sql_data);
		$data = $this->db->query($sql_data, 3);
		return array(
			'data' => $data,
			'total' => $total,
		);
	}

	private function log_get_args($get = true) {
		$data = $get ? $_GET : $_POST;
		$args = array();
		$args['type'] = (array_key_exists('type', $data) && core::lib('validate')->number($data['type'])) ? intval($data['type']) : 0;
		$args['state'] = (array_key_exists('state', $data) && in_array($data['state'], array(0, 1, 2, 3, 4, 5, 6))) ? intval($data['state']) : 0;
		$args['a_type'] = (array_key_exists('a_type', $data) && in_array($data['a_type'], array(0, 1, 2))) ? intval($data['a_type']) : 0;
		$args['username'] = (array_key_exists('username', $data) && core::lib('validate')->username($data['username'])) ? $data['username'] : '';
		return $args;
	}

	private function common_page_args($args) {
		$page_args = array_filter($args);
		if ($this->request_time_from) $page_args['fromTime'] = date('Y-m-d H:i', $this->request_time_from);
		if ($this->request_time_to) $page_args['toTime'] = date('Y-m-d H:i', $this->request_time_to);
		$page_args['page'] = '{page}';
		return $page_args;
	}
	
	public function money() {
		if ($this->post) {
			$tpl = $this->ispage ? '/agent/money_body' : '/agent/money';
			$this->get_time();
			$page_current = $this->get_page();
			$args = $this->money_get_args();
			$money_log = $this->money_search_func($args, $page_current);
			$page_max = $this->get_page_max($money_log['total']);
			if ($page_current > $page_max) core::__403();
			$page_args = $this->common_page_args($args);
			$this->display($tpl, array(
				'args' => $args,
				'total' => $money_log['total'],
				'data' => $money_log['data'],
				'all' => $money_log['all'],
				'page_current' => $page_current,
				'page_max' => $page_max,
				'page_url' => '/agent/money?'.http_build_query($page_args),
				'page_container' => '#agent-money-dom .body',
			));
		} else {
			$this->ajax();
		}
	}
	
	public function money_search() {
		$this->check_post();
		$this->get_time(false);
		$args = $this->money_get_args(false);
		$money_log = $this->money_search_func($args, 1);
		$page_max = $this->get_page_max($money_log['total']);
		if ($page_current > $page_max) core::__403();
		$page_args = $this->common_page_args($args);
		$this->display('/agent/money_body', array(
			'args' => $args,
			'total' => $money_log['total'],
			'data' => $money_log['data'],
			'all' => $money_log['all'],
			'page_current' => 1,
			'page_max' => $page_max,
			'page_url' => '/agent/money?'.http_build_query($page_args),
			'page_container' => '#agent-money-dom .body',
		));
	}
	
	private function money_search_func($args, $page_current) {
		$uid = $this->user['uid'];
		$pagesize = $this->pagesize;
		$skip = ($page_current - 1) * $pagesize;
		if ($args['parentId']) { // 用户ID限制
			if (!$this->is_child($args['parentId'])) core::__403();
			$where = "AND u.parentId={$args['parentId']}";
			$uid = $args['parentId'];
		} else if ($args['uid']) { // 用户ID限制
			if (!$this->is_child($args['uid'])) core::__403();
			$uParentId = $this->db->query("SELECT `parentId` FROM `{$this->db_prefix}members` WHERE `uid`={$args['uid']} LIMIT 1", 2);
			if ($uParentId) {
				$uParentId = $uParentId['parentId'];
				$where = " AND u.uid={$uParentId}";
				$uid = $uParentId;
			}
		} else if ($args['username']) { // 用户名限
			$uid = $this->db->query("SELECT `uid` FROM `{$this->db_prefix}members` WHERE `username`='{$args['username']}' AND CONCAT(',',parents,',') LIKE '%,{$uid},%'", 2);
			if ($uid) {
				$uid = $uid['uid'];
				$where = " AND u.username='{$args['username']}' AND concat(',', u.parents, ',') LIKE '%,{$this->user['uid']},%'";
			}
		} else if ($args['a_type'] === 1) {
			$where = " AND u.parentId={$uid}";
		} else if ($args['a_type'] === 2) {
			$where = " AND concat(',', u.parents, ',') like '%,{$uid},%' AND u.uid!={$uid}";
		} else {
			$where = " AND (u.parentId={$uid} OR u.uid={$uid})";
		}
		
		$sql_total = "SELECT COUNT(u.uid) AS `__total` FROM `{$this->db_prefix}members` u WHERE 1 ".$where;
		$total = $this->db->query($sql_total, 2);
		$total = $total['__total'];
		
		$sql_data = "SELECT (SELECT SUM(l.coin) FROM `{$this->db_prefix}coin_log` l WHERE l.uid=u.uid AND l.coin > 0 ".$this->build_where_time('l.actionTime').") income,(SELECT SUM(l.coin) FROM `{$this->db_prefix}coin_log` l WHERE l.uid=u.uid AND l.coin < 0 ".$this->build_where_time('l.actionTime').") expenditure,u.uid,u.parentId,u.username FROM `{$this->db_prefix}members` u WHERE 1 $where ORDER BY u.uid DESC LIMIT $skip,$pagesize";
		$data = $this->db->query($sql_data, 3);
		
		$user_where = " AND CONCAT(',', u.parents, ',') LIKE '%,$uid,%'";
		$sql_income = "SELECT SUM(l.coin) AS `income` FROM `{$this->db_prefix}members` u,`{$this->db_prefix}coin_log` l WHERE l.uid=u.uid AND l.coin>0 ".$this->build_where_time('l.actionTime').$user_where;
		$income = $this->db->query($sql_income, 2);
		$sql_expenditure = "SELECT SUM(l.coin) AS `expenditure` FROM `{$this->db_prefix}members` u,`{$this->db_prefix}coin_log` l WHERE l.uid=u.uid AND l.coin<0 ".$this->build_where_time('l.actionTime').$user_where;
		$expenditure = $this->db->query($sql_expenditure, 2);
		$all = array(
			'income' => (isset($income['income']) && $income['income']) ? $income['income'] : 0,
			'expenditure' => (isset($expenditure['expenditure']) && $expenditure['expenditure']) ? $expenditure['expenditure'] : 0,
		);
		$all['total'] = $all['income'] + $all['expenditure'];
		
		return array(
			'data' => $data,
			'total' => $total,
			'all' => $all,
		);
	}
	
	private function money_get_args($get = true) {
		$data = $get ? $_GET : $_POST;
		$args = array();
		$args['a_type'] = (array_key_exists('a_type', $data) && in_array($data['a_type'], array(0, 1, 2))) ? intval($data['a_type']) : 0;
		$args['parentId'] = (array_key_exists('parentId', $data) && core::lib('validate')->number($data['parentId'])) ? intval($data['parentId']) : 0;
		$args['uid'] = (array_key_exists('uid', $data) && core::lib('validate')->number($data['uid'])) ? intval($data['uid']) : 0;
		$args['username'] = (array_key_exists('username', $data) && core::lib('validate')->username($data['username'])) ? $data['username'] : '';
		return $args;
	}
	
	public function coin() {
		$this->user_check_func();
		if ($this->post) {
			$tpl = $this->ispage ? '/agent/coin_body' : '/agent/coin';
			$this->get_time();
			$args = $this->coin_get_args();
			$page_current = $this->get_page();
			$coin_log = $this->coin_search_func($args, $page_current);
			$page_max = $this->get_page_max($coin_log['total']);
			if ($page_current > $page_max) core::__403();
			$page_args = $this->coin_page_args($args);
			$this->display($tpl, array(
				'args' => $args,
				'data' => $coin_log['data'],
				'page_current' => $page_current,
				'page_max' => $page_max,
				'page_url' => '/agent/coin?'.http_build_query($page_args),
				'page_container' => '#agent-coin-dom .body',
			));
		} else {
			$this->ajax();
		}
	}
	
	public function coin_search() {
		$this->user_check_func();
		$this->check_post();
		$this->get_time(false);
		$args = $this->coin_get_args(false);
		$coin_log = $this->coin_search_func($args, 1);
		$page_max = $this->get_page_max($coin_log['total']);
		$page_args = $this->coin_page_args($args);
		$this->display('/agent/coin_body', array(
			'args' => $args,
			'data' => $coin_log['data'],
			'page_current' => 1,
			'page_max' => $page_max,
			'page_url' => '/agent/coin?'.http_build_query($page_args),
			'page_container' => '#agent-coin-dom .body',
		));
	}
	
	private function coin_search_func($args, $page_current) {
		$uid = $this->user['uid'];
		$pagesize = $this->pagesize;
		$skip = ($page_current - 1) * $pagesize;
		$where = $args['type'] ? " AND l.liqType={$args['type']} " : '';
		if ($args['username']) {
			if (!$this->is_child(null, $args['username'])) core::error('['.$args['username'].']不是您的下级会员');
			$where .= " AND l.uid = (SELECT `uid` FROM `{$this->db_prefix}members` u WHERE u.username='{$args['username']}' LIMIT 1)";
		} else {
			switch ($args['a_type']) {
				case 0:
					$where .= " AND l.uid IN (SELECT `uid` FROM `{$this->db_prefix}members` u WHERE CONCAT(',', u.parents, ',') like '%,{$uid},%')";
				break;
				
				case 1:
					$where .= " AND l.uid IN (SELECT `uid` FROM `{$this->db_prefix}members` u WHERE u.parentId={$uid})";
				break;
				
				case 2:
					$where .= " AND l.uid IN (SELECT `uid` FROM `{$this->db_prefix}members` u WHERE CONCAT(',', u.parents, ',') like '%,{$uid},%') AND l.uid!={$uid}";
				break;
			}
		}
		$where .= $this->build_where_time('l.actionTime');
		if (substr($where, 0, 4) === ' AND') $where = substr($where, 4);
		$sql = "SELECT ~field~ FROM `{$this->db_prefix}coin_log` l LEFT JOIN `{$this->db_prefix}bets` b ON b.id=l.extfield0 AND b.uid=l.uid WHERE $where ~order~ ~limit~";
		$sql_total = str_replace('~field~', 'COUNT(1) AS __total', $sql);
		$sql_total = str_replace('~limit~', '', $sql_total);
		$sql_total = str_replace('~order~', '', $sql_total);
		$total = $this->db->query($sql_total, 2);
		$total = $total['__total'];
		$field = 'b.wjorderId,b.username,l.uid,l.liqType,l.coin,l.fcoin,l.userCoin,l.actionTime,l.extfield0,l.extfield1,l.info';
		$sql_data = str_replace('~field~', $field, $sql);
		$sql_data = str_replace('~limit~', "LIMIT $skip,$pagesize", $sql_data);
		$sql_data = str_replace('~order~', 'ORDER BY l.id DESC', $sql_data);
		$data = $this->db->query($sql_data, 3);
		return array(
			'data' => $data,
			'total' => $total,
		);
	}
	
	private function coin_get_args($get = true) {
		$data = $get ? $_GET : $_POST;
		$args = array();
		$args['type'] = (array_key_exists('type', $data) && array_key_exists($data['type'], $this->coin_types)) ? intval($data['type']) : 0;
		$args['a_type'] = (array_key_exists('a_type', $data) && in_array($data['a_type'], array(0, 1, 2))) ? intval($data['a_type']) : 0;
		$args['username'] = (array_key_exists('username', $data) && core::lib('validate')->username($data['username'])) ? $data['username'] : '';
		return $args;
	}
	
	private function coin_page_args($args) {
		$page_args = array_filter($args);
		if ($this->request_time_from) $page_args['fromTime'] = date('Y-m-d H:i', $this->request_time_from);
		if ($this->request_time_to) $page_args['toTime'] = date('Y-m-d H:i', $this->request_time_to);
		$page_args['page'] = '{page}';
		return $page_args;
	}
	
	public function spread() {
		if ($this->post) {
			$tpl = $this->ispage ? '/agent/spread_body' : '/agent/spread';
			$page_current = $this->get_page();
			$spread_data = $this->spread_func($page_current);
			$page_max = $this->get_page_max($spread_data['total']);
			if ($page_current > $page_max) core::__403();
			$max = $this->user['fanDian'] - $this->config['fanDianDiff'];
			$this->display($tpl, array(
				'max' => $max < 0 ? 0 : $max,
				'data' => $spread_data['data'],
				'page_current' => $page_current,
				'page_max' => $page_max,
				'page_url' => '/agent/spread?'.http_build_query(array('page' => '{page}')),
				'page_container' => '#agent-spread-dom .body',
			));
		} else {
			$this->ajax();
		}
	}
	
	public function spread_link_add() {
		$this->check_post();
		if (!array_key_exists('type', $_POST) || ($_POST['type'] != 0 && $_POST['type'] != 1)) core::__403();
		if (!array_key_exists('fanDian', $_POST) || strlen($_POST['fanDian'] = trim($_POST['fanDian'])) === 0) core::error('请输入[用户返点]');
		if (!core::lib('validate')->number_float($_POST['fanDian'], 1)) core::error('您输入的[用户返点]格式错误');
		$fanDian = floatval($_POST['fanDian']);
		if ($fanDian < 0) core::error('[用户返点]不能小于0');
		$max = $this->user['fanDian'] - $this->config['fanDianDiff'];
		$max = $max < 0 ? 0 : $max;
		if ($fanDian > $max) core::error('[用户返点]不能大于'.$max);
		if ($fanDian) {
			$temp_fanDian = intval(str_replace('.', '', $fanDian));
			$temp_fanDianDiff = intval(str_replace('.', '', $this->config['fanDianDiff']));
			if ($temp_fanDian % $temp_fanDianDiff) core::error(sprintf('返点只能是%.1f%的倍数', $this->config['fanDianDiff']));
		} else {
			$fanDian = 0.0;
		}
		$lid = $this->db->insert($this->db_prefix.'links', array(
			'enable' => 1,
			'uid' => $this->user['uid'],
			'type' => $_POST['type'],
			'fanDian' => $fanDian,
		));
		if (!$lid) core::error('添加推广链接到数据库失败');
		$spread_data = $this->spread_func(1);
		$page_max = $this->get_page_max($spread_data['total']);
		$this->display('/agent/spread_body', array(
			'max' => $max < 0 ? 0 : $max,
			'data' => $spread_data['data'],
			'page_current' => 1,
			'page_max' => $page_max,
			'page_url' => '/agent/spread?'.http_build_query(array('page' => '{page}')),
			'page_container' => '#agent-spread-dom .body',
		));
	}
	
	private function spread_func($page_current) {
		$uid = $this->user['uid'];
		$pagesize = $this->pagesize;
		$skip = ($page_current - 1) * $pagesize;
		$total = $this->db->query("SELECT COUNT(1) AS __total FROM `{$this->db_prefix}links` WHERE `uid`={$uid}", 2);
		$total = $total['__total'];
		$data = $this->db->query("SELECT * FROM `{$this->db_prefix}links` WHERE `uid`={$uid} ORDER BY `lid` DESC LIMIT $skip,$pagesize", 3);
		return array(
			'total' => $total,
			'data' => $data,
		);
	}
	
	public function spread_link_remove() {
		$this->check_post();
		if (!array_key_exists('lid', $_GET) || !core::lib('validate')->number($_GET['lid'])) core::__403();
		$lid = intval($_GET['lid']);
		$sql = "DELETE FROM `{$this->db_prefix}links` WHERE `lid`=$lid LIMIT 1";
		if (!$this->db->query($sql, 0)) core::error('删除失败，请重试');
		$this->dialogue(array(
			'type' => 'success',
			'text' => '删除成功',
			'auto' => true,
			'yes'  => array(
				'text' => '我知道了',
				'func' => '$("#spread-'.$lid.'").remove();',
			),
		));
	}
	
	public function spread_link_disable() {
		$this->check_post();
		if (!array_key_exists('lid', $_GET) || !core::lib('validate')->number($_GET['lid'])) core::__403();
		$lid = intval($_GET['lid']);
		$sql = "UPDATE `{$this->db_prefix}links` SET `enable`=0 WHERE `lid`=$lid LIMIT 1";
		if (!$this->db->query($sql, 0)) core::error('禁用失败，请重试');
		$this->dialogue(array(
			'type' => 'success',
			'text' => '禁用成功',
			'auto' => true,
			'yes'  => array(
				'text' => '我知道了',
				'func' => '$("#spread-'.$lid.'").find(".disable").hide().next().show();',
			),
		));
	}
	
	public function spread_link_enable() {
		$this->check_post();
		if (!array_key_exists('lid', $_GET) || !core::lib('validate')->number($_GET['lid'])) core::__403();
		$lid = intval($_GET['lid']);
		$sql = "UPDATE `{$this->db_prefix}links` SET `enable`=1 WHERE `lid`=$lid LIMIT 1";
		if (!$this->db->query($sql, 0)) core::error('启用失败，请重试');
		$this->dialogue(array(
			'type' => 'success',
			'text' => '启用成功',
			'auto' => true,
			'yes'  => array(
				'text' => '我知道了',
				'func' => '$("#spread-'.$lid.'").find(".enable").hide().prev().show();',
			),
		));
	}
	
	public function bonus_get() {
		if (!$this->user['type']) core::__403();
		$uid = $this->user['uid'];
		$sql = "SELECT * FROM `{$this->db_prefix}bonus_log` WHERE `uid`={$uid} AND `bonusStatus`=0 ORDER BY `id` DESC LIMIT 1";
		$last_bonus = $this->db->query($sql, 2);
		if (!$last_bonus) core::error('您本期没有可分红金额或者您已经领取了本期分红');
		$bank = $this->db->query("SELECT * FROM `{$this->db_prefix}member_bank` WHERE `uid`={$uid} LIMIT 1", 2);
		if (!$bank || !$bank['bankId']) core::error('您还没有设置银行账户，无法领取分红');
		$para['username'] = $bank['username'];
		$para['account'] = $bank['account'];
		$para['bankId'] = $bank['bankId'];
		$this->db->transaction('begin');
		try{
			$this->fresh_user_session();
			// 插入提现请求表
			$para['actionTime'] = $this->time;
			$para['uid'] = $this->user['uid'];
			$para['info'] = '分红提现';
			$para['amount'] = $last_bonus['bonusAmount'];
			if(!$this->db->insert($this->db_prefix.'member_cash', $para)) throw new Exception('领取分红请求出错');
			$sql_update = "UPDATE `{$this->db_prefix}bonus_log` SET `bonusStatus`=1 WHERE `id`={$last_bonus['id']} LIMIT 1";
			if(!$this->db->query($sql_update, 0)) throw new Exception('领取分红请求出错');
			$this->db->transaction('commit');
			$this->dialogue(array(
				'type' => 'success',
				'text' => '您的分红提现请求提交成功，请等待管理员处理',
				'auto' => true,
				'yes'  => array('text' => '我知道了'),
			));
		} catch(Exception $e) {
			$this->db->transaction('rollBack');
			core::error($e->getMessage());
		}
	}
	
	// 修改返点
	public function user_edit() {
		$this->check_post();
		if (!array_key_exists('uid', $_GET) || !core::lib('validate')->number($_GET['uid'])) core::__403();
		$uid = intval($_GET['uid']);
		$cur_fandian = $this->db->query("SELECT `fanDian` FROM `{$this->db_prefix}members` WHERE `uid`={$uid} AND CONCAT(',', parents, ',') LIKE '%,{$this->user['uid']},%' LIMIT 1", 2);
		if (!$cur_fandian) core::__403();
		$cur_fandian = $cur_fandian['fanDian'];
		$max_fandian = $this->user['fanDian'] - $this->config['fanDianDiff'];
		$max_fandian = $max_fandian < 0 ? 0 : $max_fandian;
		$html  = '<form action="/agent/user_edit_func" method="POST" target="ajax" func="form_submit" style="height:40px">';
		$html .= '<input type="hidden" name="uid" value="'.$uid.'">';
		$html .= '<input type="text" name="fandian" placeholder="请输入新的返点" style="float:left;width:400px;padding:10px 15px">';
		$html .= '<button type="submit" class="btn btn-blue" style="float:left;margin-left:15px;width:120px;height:39px;line-height:35px;font-size:14px">修改</button>';
		$html .= '</form>';
		$html .= '<style>.uetip .btn{padding:0 5px;margin:0 5px}</style>';
		$html .= '<p class="uetip" style="font-size:12px;color:#999;margin-top:10px">新返点必须是<span class="btn btn-red">'.$this->config['fanDianDiff'].'</span>的倍数，并且不得小于<span class="btn btn-blue">'.$cur_fandian.'</span>不得大于<span class="btn btn-green">'.$max_fandian.'</span></p>';
		$this->dialogue(array(
			'class' => 'mid',
			'body' => $html,
			'no'  => array('text' => '取消修改'),
		));
	}
	
	public function user_edit_func() {
		$this->check_post();
		if (!array_key_exists('uid', $_POST) || !core::lib('validate')->number($_POST['uid']) || !array_key_exists('fandian', $_POST)) core::__403();
		if (!core::lib('validate')->number_float($_POST['fandian'], 1)) core::error('您输入的[用户返点]格式错误');
		$uid = intval($_POST['uid']);
		$fanDian = floatval($_POST['fandian']);
		if ($fanDian < 0) core::error('[用户返点]不能小于0');
		$cur_fandian = $this->db->query("SELECT `fanDian` FROM `{$this->db_prefix}members` WHERE `uid`={$uid} AND CONCAT(',', parents, ',') LIKE '%,{$this->user['uid']},%' LIMIT 1", 2);
		if (!$cur_fandian) core::__403();
		$cur_fandian = $cur_fandian['fanDian'];
		$max_fandian = $this->user['fanDian'] - $this->config['fanDianDiff'];
		$max_fandian = $max_fandian < 0 ? 0 : $max_fandian;
		if ($fanDian > $max_fandian) core::error('[用户返点]不能大于'.$max_fandian);
		if ($fanDian <= $cur_fandian) core::error('[用户返点]必须大于其当前返点'.$cur_fandian);
		$temp_fanDian = intval(str_replace('.', '', $fanDian));
		$temp_fanDianDiff = intval(str_replace('.', '', $this->config['fanDianDiff']));
		if ($temp_fanDian % $temp_fanDianDiff) core::error(sprintf('返点只能是%.1f%的倍数', $this->config['fanDianDiff']));
		$this->db->query("UPDATE `{$this->db_prefix}members` SET `fanDian`={$fanDian} WHERE `uid`={$uid} LIMIT 1", 0);
		$this->dialogue(array(
			'type' => 'success',
			'text' => '修改成功',
			'auto' => true,
			'yes'  => array(
				'text' => '我知道了',
				'func' => '$.reload();',
			),
		));
	}
	
	// 给下级转账
	public function recharge() {
		$this->check_post();
		if (!array_key_exists('uid', $_GET) || !core::lib('validate')->number($_GET['uid'])) core::__403();
		$html  = '<form action="/agent/recharge_func" method="POST" target="ajax" func="form_submit" style="height:40px">';
		$html .= '<input type="hidden" name="uid" value="'.$_GET['uid'].'">';
		$html .= '<input type="text" name="money" placeholder="请输入您需要转账的数额" style="float:left;width:400px;padding:10px 15px">';
		$html .= '<button type="submit" class="btn btn-blue" style="float:left;margin-left:15px;width:120px;height:39px;line-height:35px;font-size:14px">转账</button>';
		$html .= '</form>';
		$this->dialogue(array(
			'class' => 'mid',
			'body' => $html,
			'no'  => array('text' => '取消转账'),
		));
	}
	
	public function recharge_func() {
		$this->check_post();
		if (!array_key_exists('uid', $_POST) || !core::lib('validate')->number($_POST['uid'])) core::__403();
		if (!array_key_exists('money', $_POST) || !core::lib('validate')->number_float($_POST['money'], 2)) core::error('您输入的金额错误');
		$uid = intval($_POST['uid']);
		$username = $this->get_username($uid);
		if (!$username) core::error('您要转账的用户不存在');
		$money = floatval($_POST['money']);
		if ($this->user['coin'] < $money) core::error('您的可用余额不足');
		$this->db->transaction('begin');
		try {
			// 扣除用户自己的资金
			$this->set_coin(array(
				'uid' => $this->user['uid'],
				'liqType' => 13,
				'info' => '转款给['.$username.']',
				'coin' => -$money,
			));
			// 给下级充值
			$this->set_coin(array(
				'uid' => $uid,
				'liqType' => 12,
				'info' => '上级['.$this->user['username'].']转款',
				'coin' => $money,
			));
			$this->db->transaction('commit');
			$this->dialogue(array(
				'type' => 'success',
				'text' => '转款成功',
				'auto' => true,
				'yes'  => array(
					'text' => '我知道了',
					'func' => '$.reload();',
				),
			));
		} catch(Exception $e) {
			$this->db->transaction('rollBack');
			core::error($e->getMessage());
		}
	}
	
	// 添加会员
	public function member_add() {
		$this->check_post();
		$username = array_key_exists('username', $_POST) ? trim($_POST['username']) : '';
		$password = array_key_exists('password', $_POST) ? trim($_POST['password']) : '';
		$qq = array_key_exists('qq', $_POST) ? trim($_POST['qq']) : '';
		$type = array_key_exists('type', $_POST) ? intval($_POST['type']) : -1;
		$fanDian = array_key_exists('fanDian', $_POST) ? floatval($_POST['fanDian']) : -1;
		if (empty($username)) core::error('用户名不能为空');
		if (!core::lib('validate')->username($username)) core::error('用户名格式错误');
		if ($this->db->query("SELECT `uid` FROM `{$this->db_prefix}members` WHERE `username`='{$username}' LIMIT 1", 2)) core::error('账户名已存在');
		if (empty($password)) core::error('登录密码不能为空');
		if (empty($qq)) core::error('腾讯QQ不能为空');
		if (!core::lib('validate')->qq($qq)) core::error('您输入的QQ有误');
		$max = $this->user['fanDian'] - $this->config['fanDianDiff'];
		$max = $max < 0 ? 0 : $max;
		if (!in_array($type, array(0, 1))) core::error('会员类型错误');
		if ($fanDian > $max) core::error('用户返点超过最大值');
		$sql = "SELECT `userCount`, (SELECT COUNT(*) FROM `{$this->db_prefix}members` m WHERE m.parentId={$this->user['uid']} AND m.fanDian=s.fanDian) registerCount FROM `{$this->db_prefix}params_fandianset` s WHERE s.fanDian={$fanDian}";
		$count = $this->db->query($sql, 2);
		if($count && $count['registerCount'] >= $count['userCount']) {
			core::error('对不起返点为<span class="btn btn-red">'.$fanDian.'</span>的下级人数已经达到上限');
		}
		$para = array(
			'source' => 1,
			'username' => $username,
			'type' => $type,
			'password' => md5($password),
			'parentId' => $this->user['uid'],
			'parents' => $this->user['parents'],
			'fanDian' => $fanDian,
			'regIP' => $this->ip(true),
			'regTime' => $this->time,
			'qq' => $qq,
			'coin' => 0,
			'fcoin' => 0,
			'score' => 0,
			'scoreTotal' => 0,			
		);
		$this->db->transaction('begin');
		try {
			$id = $this->db->insert($this->db_prefix.'members', $para);
			if ($id) {
				$sql = "UPDATE `{$this->db_prefix}members` SET `parents`=CONCAT(parents, ',', $id) WHERE `uid`=$id LIMIT 1";
				$this->db->query($sql, 0);
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
				$msg = $zczs !== 0 ? '添加成功，系统赠送给会员['.$username.'] '.$zczs.' 元' : '添加成功';
				$this->dialogue(array(
					'type' => 'success',
					'text' => $msg,
					'auto' => true,
					'yes'  => array(
						'text' => '我知道了',
						'func' => '$.reload();',
					),
				));
			} else {
				throw new Exception('添加用户信息到数据库失败');
			}
		} catch(Exception $e) {
			$this->db->transaction('rollBack');
			core::error($e->getMessage());
		}
	}

}