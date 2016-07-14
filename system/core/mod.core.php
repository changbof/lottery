<?php

class mod {

	protected $db_prefix; // 数据库表前缀
	protected $version; // 版本号
	protected $db; // 数据库实例
	protected $post; // 是否post请求
	protected $ispage; // 是否页面框架内部请求
	protected $time; // 当前时间
	protected $request_time_from; // 传入的起始时间
	protected $request_time_to; // 传入的结束时间
	protected $config = array(); // 网站配置
	protected $clients = array('software', 'web', 'mobile'); //客户端类型列表
	protected $client_type = 'web'; //客户端类型
	protected $user_check = true; //登录检查
	protected $user_session = 'USER'; // 用户session名称
	protected $user; // 用户信息
	protected $pagesize = 20; // 每页的数据条数
	protected $modes = array('2.000' => '元', '0.200' => '角', '0.020' => '分', '0.002' => '厘');
	protected $coin_type_data = array(
		'账户类' => array(
			55  => '注册奖励',
			1   => '用户充值',
			9   => '系统充值',
			54  => '充值奖励',
			106 => '提现冻结',
			12  => '上级转款',
			8   => '提现失败返还',
			107 => '提现成功扣除',
			51  => '绑定银行奖励',
		),
		'游戏类' => array(
			101 => '投注扣款',
			108 => '开奖扣除',
			6   => '中奖奖金',
			7   => '撤单返款',
			102 => '追号投注',
			5   => '追号撤单',
			//11  => '合买收单',
			255 => '未开奖返还',
		),
		/*
		'抢庄类' => array(
			100 => '抢庄冻结',
			10  => '撤庄返款',
			103 => '抢庄返点',
			104 => '抢庄抽水',
			105 => '抢庄赔付',
		),
		*/
		'代理类' => array(
			2   => '下级返点',
			3   => '代理分红',
			52  => '充值佣金',
			53  => '消费佣金',
			56  => '亏损佣金',
			13  => '转款给下级',
		),
		'活动类' => array(
			50  => '签到赠送',
			120 => '幸运大转盘',
			121 => '积分兑换',
		),
	);
	protected $coin_types = array();
	protected $dzpsettings = array();
	protected $exchange_config = array();
	
	public function __construct() {
		$this->db_prefix = DB_PREFIX;
		$this->version = VERSION;
		$this->db = core::lib('db');
		$this->post = strtolower($_SERVER['REQUEST_METHOD']) === 'post' ? true : false;
		$this->ispage = (array_key_exists('ispage', $_POST) && $_POST['ispage'] === 'true') ? true : false;
		$this->time = time();
		$config_data = $this->db->query("SELECT * FROM `{$this->db_prefix}params`", 3);
		foreach ($config_data as $v) $this->config[$v['name']] = $v['value'];
		if (!$this->config['switchWeb']) core::error($this->config['webCloseServiceResult']);
		//判断是否移动设备访问
		require(SYSTEM.'/core/Mobile_Detect.php');
		$detect = new Mobile_Detect;
		if($detect->isMobile()){
			$this->client_type = 'mobile';
			$this->pagesize = 10;
		}

		if ($this->user_check) $this->user_check_func();
		foreach ($this->coin_type_data as $vs) {
			foreach ($vs as $k => $v) $this->coin_types[$k] = $v;
		}
	}
	
	// 用户登录检查
	protected function user_check_func() {
		$opt = array();
		$url_login = '/user/login?client_type='.$this->client_type;
		if(array_key_exists($this->user_session, $_SESSION) && $_SESSION[$this->user_session]) {
			$this->user = unserialize($_SESSION[$this->user_session]);
			$user_key = session_id();
			$user_sql = "SELECT `isOnLine`, `state` FROM `{$this->db_prefix}member_session` WHERE `uid`={$this->user['uid']} AND `session_key`='$user_key' ORDER BY `id` DESC LIMIT 1";
			$user_info = $this->db->query($user_sql, 2);
			if(!$user_info['isOnLine'] && $user_info['state'] == 1) {
				$opt = array(
					'type' => 'error',
					'text' => '您的账号在别处登陆，您被强迫下线',
					'auto' => true,
					'yes'  => array(
						'text' => '重新登录',
						'func' => '$.reload("'.$url_login.'");',
					),
				);
			} else if (!$user_info['isOnLine']) {
				$opt = array(
					'type' => 'error',
					'text' => '由于登陆超时或网络不稳定，您的登录已失效',
					'auto' => true,
					'yes'  => array(
						'text' => '重新登录',
						'func' => '$.reload("'.$url_login.'");',
					),
				);
			} else if (!array_key_exists('access_update', $_SESSION) || $_SESSION['access_update'] < $this->time - 15) {
				$id = $this->user['sessionId'];
				$update_sql = "UPDATE `{$this->db_prefix}member_session` SET `accessTime`={$this->time} WHERE `id`='$id' LIMIT 1";
				$this->db->query($update_sql, 0);
				$_SESSION['access_update'] = $this->time;
			}
		} else {
			$opt = array(
				'type' => 'error',
				'text' => '您还没有登录',
				'auto' => true,
				'yes'  => array(
					'text' => '登录',
					'func' => '$.reload("'.$url_login.'");',
				),
			);
		}
		if (!empty($opt)) {
			unset($_SESSION[$this->user_session]);
			if ($this->post) {
				$this->dialogue($opt);
			} else {
				header('Location: '.$url_login);
				exit;
			}
		}
	}
	
	/**
	 * @name 前台对话框交互
	 * @param array opt 配置数组
	 * 	|-- type: error|success
	 * 	|-- text: 提示文本
	 * 	|-- auto: true|false 是否自动关闭(如果有确认选项，关闭时执行确认选项内容)
	 * 	|-- yes: 确认选项内容
	 * 		|-- text: 确认文本
	 * 		|-- func: 点击确认时执行函数(没有则默认为关闭对话框)
	 * 	|-- no: 取消选项内容
	 * 		|-- text: 取消文本
	 * 		|-- func: 点击取消时执行函数(没有则默认为关闭对话框)
	 */
	protected function dialogue($opt) {
		header('X-Error-Message: dialogue');
		echo json_encode($opt);
		exit;
	}
	
	protected function ip($return_long = false) {
		$ip = '';
		if (isset($HTTP_SERVER_VARS)) {
			if (array_key_exists('HTTP_X_FORWARDED_FOR', $HTTP_SERVER_VARS)) {
				$ip = $HTTP_SERVER_VARS['HTTP_X_FORWARDED_FOR'];
			} else if (array_key_exists('HTTP_CLIENT_IP', $HTTP_SERVER_VARS)) {
				$ip = $HTTP_SERVER_VARS['HTTP_CLIENT_IP'];
			} else if (array_key_exists('REMOTE_ADDR', $HTTP_SERVER_VARS)) {
				$ip = $HTTP_SERVER_VARS['REMOTE_ADDR'];
			}
		}
		if (empty($ip)) {
			if (array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER)) {
				$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
			} else if (array_key_exists('HTTP_CLIENT_IP', $_SERVER)) {
				$ip = $_SERVER['HTTP_CLIENT_IP'];
			} else if (array_key_exists('REMOTE_ADDR', $_SERVER)) {
				$ip = $_SERVER['REMOTE_ADDR'];
			} else {
				$ip = '0.0.0.0';
			}
		}
		if (strpos($ip, ',') !== false) {
			$ip = explode(',', $ip, 2);
			$ip = current($ip);
		}
		return $return_long ?  bindec(decbin(ip2long($ip))) : $ip;
	}
	
	// 获取玩法奖金数据
	protected function get_play_bonus($play_id) {
		$sql = "SELECT `bonusProp`,`bonusPropBase` FROM `{$this->db_prefix}played` WHERE `id`=$play_id LIMIT 1";
		$data = $this->db->query($sql, 2);
		$diff_fanDian = $this->config['fanDianMax'] - $this->user['fanDian'];
		$proportion = 1 - $diff_fanDian / 100;
		$data['bonusProp'] = number_format($data['bonusProp'] * $proportion, 2, '.', '');
		$data['bonusPropBase'] = number_format($data['bonusPropBase'] * $proportion, 2, '.', '');
		return $data;
	}
	
	// 刷新session
	protected function fresh_user_session() {
		if(!$this->user) return false;
		$sessionId = $this->user['sessionId'];
		$uid = $this->user['uid'];
		$sql = "SELECT * FROM `{$this->db_prefix}members` WHERE `uid`=$uid LIMIT 1";
		$user = $this->db->query($sql, 2);
		$user['sessionId'] = $sessionId;
		$user['_gameFanDian'] = $this->config['fanDianMax'];
		$_SESSION[$this->user_session] = serialize($user);
		$this->user = $user;
		return true;
	}
	
	// 用户资金变动(请在一个事务里使用)
	protected function set_coin($log) {
		$default = array(
			'coin' => 0,
			'fcoin' => 0,
			'uid' => $this->user['uid'],
			'liqType' => 0,
			'type' => 0,
			'info' => '',
			'extfield0' => 0,
			'extfield1' => '',
			'extfield2' => '',
		);
		$sql = 'call setCoin(';
		foreach ($default as $k => $v) {
			$val = (array_key_exists($k, $log) && $log[$k]) ? $log[$k] : $v;
			if ($v !== 0) $val = "'$val'";
			$sql .= $val.',';
		}
		$sql = substr($sql, 0, -1).')';
		$this->db->query($sql, 0);
	}

	// 根据类型获取玩法列表
	protected function get_plays($group_id = 0) {
		$where = $group_id === 0 ? '' : " AND `groupId`=$group_id ";
		$sql = "SELECT `id`,`name`,`playedTpl`,`enable`,`maxcount`,`betCountFun`,`bonusPropBase`,`bonusProp`,`groupId`,`type`,`minCharge` FROM `{$this->db_prefix}played` WHERE `enable`=1 $where ORDER BY `sort`";
		$data = $this->db->query($sql, 3);
		$plays = array();
		foreach ($data as $v) $plays[$v['id']] = $v;
		return $plays;
	}

	// 获取GET中的数字参数
	protected function get_id($key = 'id') {
		$id = array_key_exists($key, $_GET) ? $_GET[$key] : '';
		if (!core::lib('validate')->number($id)) core::__403();
		return intval($id);
	}
	
	// 获取当前页码
	protected function get_page() {
		return (array_key_exists('page', $_GET) && core::lib('validate')->number($_GET['page'])) ? intval($_GET['page']) : 1;
	}
	
	// 获取数据列表最大页码
	protected function get_page_max($total) {
		$page_max = ceil($total / $this->pagesize);
		return $page_max ? $page_max : 1;
	}
	
	// 获取查询起始时间和结束时间
	protected function get_time($get = true) {
		$data = $get ? $_GET : $_POST;
		$time_from = $this->time - 86400 * 7;
		$time_from = date('Y-m-d H:i', $time_from < $this->user['regTime'] ? $this->user['regTime'] : $time_from);
		$time_to = date('Y-m-d H:i', $this->time);
		$this->request_time_from = strtotime((array_key_exists('fromTime', $data) && $data['fromTime']) ? $data['fromTime'] : $time_from);
		$this->request_time_to = strtotime((array_key_exists('toTime', $data) && $data['toTime']) ? $data['toTime'] : $time_to);
		if (!$this->request_time_from || !$this->request_time_to) core::__403();
		if ($this->request_time_from >= $this->request_time_to) core::error('查询[起始时间]必须小于[结束时间]');
		$now = date('H:i');
		if (date('H:i', $this->request_time_from) === $now) $this->request_time_from -= 60;
		if (date('H:i', $this->request_time_to)) $this->request_time_to += 60;
	}
	
	// 获取彩种列表(带分类)
	protected function get_types() {
		static $types = array();
		if (!$types) {
			$games = array(
				1 => '时时彩',
				2 => '11选5',
				9 => '快三',
				3 => '低频彩',
				6 => 'PK10',
				8 => '快乐8',
				4 => '快乐十分',
			);
			foreach ($games as $type => $name) {
				$types[$name] = $this->db->query("SELECT `id`,`title` FROM `{$this->db_prefix}type` WHERE enable=1 AND type=$type", 3);
			}
		}
		return $types;
	}
	
	// post校验
	protected function check_post() {
		if (!$this->post) core::__403();
	}
	
	// ajax加载
	protected function ajax() {
		$this->display('index', array('load_self' => true));
	}
	
	// 组装时间范围查询条件
	protected function build_where_time($field) {
		$where = '';
		$time_from = $this->request_time_from;
		$time_to = $this->request_time_to;
		if ($time_from && $time_to) {
			$where = " AND $field BETWEEN $time_from AND $time_to";
		} else if ($time_from) {
			$where = " AND $field>=$time_from";
		} else if ($time_to) {
			$where = " AND $field<$time_to";
		}
		return $where;
	}
	
	// 根据用户ID获取用户名
	protected function get_username($uid) {
		static $usernames = array();
		if (!array_key_exists($uid, $usernames)) {
			$data = $this->db->query("SELECT `username` FROM `{$this->db_prefix}members` WHERE `uid`={$uid} LIMIT 1", 2);
			$usernames[$uid] = $data['username'];
		}
		return $usernames[$uid];
	}
	
	protected function myxor($string, $key = '') {
		if ('' == $string) return '';
		if ('' == $key) $key = 'cd';
		$len1 = strlen($string);
		$len2 = strlen($key);
		if ($len1 > $len2) $key = str_repeat($key, ceil($len1 / $len2));
		return $string ^ $key;
	}
	
	protected function str2hex($string) {
		$hex = "";
		for ($i=0;$i<strlen($string);$i++) {
			$hex .= dechex(ord($string[$i]));
		}
		$hex = strtoupper($hex);
		return $hex;
	}
	
	protected function hex2str($hex) {
		$string = "";
		for ($i=0;$i<strlen($hex)-1;$i+=2) {
			$string .= chr(hexdec($hex[$i].$hex[$i+1]));
		}
		return $string;
	}
	
	/**
	 * @name 模板加载
	 * @param string tpl_name 模板名称
	 * @param array args 模板参数
	 */
	protected function display($tpl_name, $args = array()) {
		define('TPL', SYSTEM.'/tpl/'.$this->client_type);
		extract($args);
		require(TPL.'/'.$tpl_name.'.tpl.php');
	}

}