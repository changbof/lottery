<?php
// 支付回调处理接口
class pay {

	private $db; // 数据库连接
	private $db_prefix; // 数据库表前缀
	private $config = array(); // 系统配置
	private $time; // 当前时间

	public function __construct() {
		try {
			$this->db = new pay_db(DB_HOST, DB_PORT, DB_NAME, DB_USER, DB_PASS);
			$this->db_prefix = DB_PREFIX;
			$config_data = $this->db->query("SELECT * FROM `{$this->db_prefix}params`", 3);
			foreach ($config_data as $v) $this->config[$v['name']] = $v['value'];
			$this->time = time();
		} catch (Exception $e) {
			exit($e->getMessage());
		}
	}
	
	/**
	 * @name 支付回调接口
	 * @param int order_amount 充值金额
	 * @param string order_no 充值订单号
	 */
	public function call($order_amount, $order_no) {
		try {
			// 开始事务
			$this->db->transaction('begin');
			// 充值金额强制校验
			$this->check_amount($order_amount);
			// 获取充值信息
			$info_recharge = $this->get_recharge_info($order_no);
			// 订单处理状态校验
			$this->check_state($info_recharge);
			// 获取用户信息
			$info_user = $this->get_user_info($info_recharge['uid']);
			// 根据系统配置的充值赠送比例计算赠送金额
			$recharge_complimentary = $this->get_recharge_complimentary($order_amount);
			// 执行充值操作
			$this->set_amount($info_user['uid'], $recharge_complimentary, $info_user['coin'], $order_amount, $order_no, $info_recharge['id']);
			// 每天首次充值的金额达到系统设置则返回佣金
			$this->set_rebate($info_user['uid'], $order_amount, $order_no, $info_recharge['id']);
			// 提交事务
			$this->db->transaction('commit');
			echo '充值成功';
		} catch (Exception $e) {
			$this->db->transaction('rollBack');
			exit($e->getMessage());
		}
	}
	
	private function set_rebate($uid, $order_amount, $order_no, $recharge_id) {
		// 查检是否当天首次充值
		$time = strtotime('00:00');
		$sql = "SELECT `id` FROM `{$this->db_prefix}member_recharge` WHERE `rechargeTime`>=$time AND `uid`=$uid limit 1,1";
		$data = $this->db->query($sql, 2);
		if (!$data && $order_amount > floatval($this->config['rechargeCommissionAmount'])) {
			$log = array(
				'type' => 0,
				'fcoin' => 0,
				'liqType' => 52,
				'info' => '充值佣金',
				'extfield0' => $order_no,
				'extfield1' => $recharge_id,
				'extfield2' => '',
			);
			// 返回上家佣金
			$parent_id = $this->set_rebate_func($uid, floatval($this->config['rechargeCommission']), $log);
			if ($parent_id) $this->set_rebate_func($parent_id, floatval($this->config['rechargeCommission2']), $log);
		}
	}
	
	private function set_rebate_func($uid, $recharge_commission, $log) {
		// 获取账户的上家
		$sql = "SELECT `parentId` FROM `{$this->db_prefix}members` WHERE `uid`=$uid LIMIT 1";
		$data = $this->db->query($sql, 2);
		if (!$data) return null;
		$parent_id = $data['parentId'];
		// 存在上家并且系统设置返回佣金则执行返回佣金操作
		if ($parent_id && $recharge_commission) {
			$log['coin'] = $recharge_commission;
			$log['uid'] = $parent_id;
			$this->set_coin($log);
		}
		// 返回上家用户ID
		return $parent_id;
	}
	
	private function set_amount($uid, $recharge_complimentary, $old_coin, $order_amount, $order_no, $recharge_id) {
		// 更新充值信息
		$new_amount = $order_amount + $recharge_complimentary;
		$sql = "UPDATE `{$this->db_prefix}member_recharge` SET `state`=1,`rechargeAmount`=$new_amount,`coin`=$old_coin WHERE `rechargeId`='$order_no' LIMIT 1";
		$this->db->query($sql, 0);
		// 添加充值帐变
		$log = array(
			'coin' => $order_amount,
			'fcoin' => 0,
			'uid' => $uid,
			'liqType' => 1,
			'type' => 0,
			'info' => '充值',
			'extfield0' => $recharge_id,
			'extfield1' => $order_no,
			'extfield2' => '',
		);
		$this->set_coin($log);
		// 添加充值赠送帐变
		if ($recharge_complimentary) {
			$log = array(
				'coin' => $recharge_complimentary,
				'fcoin' => 0,
				'uid' => $uid,
				'liqType' => 54,
				'type' => 0,
				'info' => '充值赠送',
				'extfield0' => $recharge_id,
				'extfield1' => $order_no,
				'extfield2' => '',
			);
			$this->set_coin($log);
		}
	}
	
	private function set_coin($log) {
		static $default = array(
			'coin' => 0,
			'fcoin' => 0,
			'uid' => 0,
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
	
	private function get_recharge_complimentary($order_amount) {
		$czzs = intval($this->config['czzs']);
		return $czzs > 0 ? number_format($order_amount * $czzs / 100.00, 2, '.', '') : 0;
	}
	
	private function get_user_info($uid) {
		$info = $this->db->query("SELECT * FROM `{$this->db_prefix}members` WHERE `uid`=$uid LIMIT 1", 2);
		if (!$info) throw new Exception('充值失败: 充值用户已被删除或不存在');
		return $info;
	}
	
	private function get_recharge_info($id) {
		$info = $this->db->query("SELECT * FROM `{$this->db_prefix}member_recharge` WHERE `rechargeId`='$id' LIMIT 1", 2);
		if (!$info) throw new Exception('充值失败: 订单号不存在');
		return $info;
	}
	
	private function check_state($info) {
		if ($info['state'] != 0 || !$info['uid']) throw new Exception('充值成功');
	}
	
	private function check_amount($amount) {
		$rechargeMin = intval($this->config['rechargeMin']); //最小充值金额
		$rechargeMax = intval($this->config['rechargeMax']); //最大充值金额
		if ($amount < $rechargeMin || $amount > $rechargeMax) throw new Exception('充值失败: 您的充值金额不符合系统设置');
	}

}

class pay_db {

	private $db;
	
	public function __construct($host, $port, $name, $user, $pass) {
		$dsn = 'mysql:host='.$host.';dbname='.$name.';port='.$port;
		try{
			$this->db = new PDO($dsn, $user, $pass, array(
				PDO::ATTR_PERSISTENT => false,
				PDO::ATTR_CASE => PDO::CASE_NATURAL,
				PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
				PDO::ATTR_AUTOCOMMIT => false,
				PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
				PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
			));
		} catch (Exception $e) {
			throw $e;
		}
	}
	
	public function query($sql, $return) {
		try {
			switch ($return) {
				case 0:
					$result = $this->db->exec($sql);
				break;
				
				case 1:
					$this->db->exec($sql);
					$result = $this->db->lastInsertId();
				break;
				
				case 2:
				case 3:
					$query = $this->db->query($sql);
					$action = $return === 2 ? 'fetch' : 'fetchAll';
					$result = call_user_func_array(array($query, $action), array(PDO::FETCH_ASSOC));
					$query->closeCursor();
				break;
				
				default:
			}
			return $result;
		} catch (Exception $e) {
			throw $e;
		}
	}
	
	public function transaction($command) {
		try {
			switch ($command) {
				case 'begin': $this->db->beginTransaction(); break;
				case 'commit': $this->db->commit(); break;
				case 'rollBack': $this->db->rollBack(); break;
				default:
			}
		} catch (Exception $e) {
			throw $e;
		}
	}
	
	public function __destruct() {
		$this->db = null;
	}

}