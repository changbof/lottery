<?php

class mod_tip extends mod {
	
	// 私信提醒
	public function message() {
		$this->check_post();
		$uid = $this->user['uid'];
		$temp = $this->db->query("SELECT `id` FROM `{$this->db_prefix}message_receiver` WHERE `to_uid`={$uid} AND `is_deleted`=0 AND `is_readed`=0 AND `flag`=0 ORDER BY `id` DESC", 3);
		if ($temp) {
			$ids = array();
			foreach ($temp as $v) array_push($ids, $v['id']);
			$cookie = array_key_exists('message', $_COOKIE) ? explode(',', $_COOKIE['message']) : array();
			if (array_diff($ids, $cookie)) {
				$id = $ids[0];
				$this->db->query("UPDATE `{$this->db_prefix}message_receiver` SET `flag`=1 WHERE `id`={$id} LIMIT 1", 0);
				setcookie('message', implode(',', $ids));
				$this->dialogue(array(
					'type' => 'success',
					'text' => '您有新的私信',
					'auto' => true,
					'yes'  => array('text' => '我知道了'),
					'no'  => array(
						'text' => '查看私信',
						'func' => 'setTimeout(function() {$("#message-receive").trigger("click");},300);',
					),
				));
			}
		}
	}
	
	// 充值或提现提示
	public function money() {
		$this->check_post();
		$uid = $this->user['uid'];
		// 提现提示
		$sql = "SELECT `id` FROM `{$this->db_prefix}member_cash` WHERE (`state`=0 OR `state`=4) AND `isDelete`=0 AND `flag`=0 AND `uid`=$uid ORDER BY `id` DESC";
		$temp = $this->db->query($sql, 3);
		if ($temp) {
			$ids = array();
			foreach ($temp as $v) array_push($ids, $v['id']);
			$cookie = array_key_exists('money_cash', $_COOKIE) ? explode(',', $_COOKIE['money_cash']) : array();
			if (array_diff($ids, $cookie)) {
				$id = $ids[0];
				$this->db->query("UPDATE `{$this->db_prefix}member_cash` SET `flag`=1 WHERE `id`=$id LIMIT 1", 0);
				$data = $this->db->query("SELECT `amount`,`state`,`info` FROM `{$this->db_prefix}member_cash` WHERE `id`=$id LIMIT 1", 2);
				$amount = $data['amount'];
				$state = $data['state'];
				$info = $data['info'];
				setcookie('money_cash', implode(',', $ids));
				if ($state == 4) {
					$type = 'error';
					$text = '您的提现请求被驳回，原因：'.$info;
				} else {
					$type = 'success';
					$text = '您的提现请求已处理，金额<span class="btn btn-red">'.$amount.'</span>元';
				}
				$this->dialogue(array(
					'type' => $type,
					'text' => $text,
					'auto' => true,
					'yes'  => array(
						'text' => '我知道了',
						'func' => 'lottery.user_fresh();',
					),
					'no'  => array(
						'text' => '查看提现记录',
						'func' => 'setTimeout(function() {$("#user-cash").trigger("click");},300);',
					),
				));
				exit;
			}
		}
		// 充值提示
		$sql = "SELECT `id` FROM `{$this->db_prefix}member_recharge` WHERE (`state`=1 OR `state`=9) AND `isDelete`=0 AND `flag`=0 AND `uid`=$uid ORDER BY `id` DESC";
		$temp = $this->db->query($sql, 3);
		if ($temp) {
			$ids = array();
			foreach ($temp as $v) array_push($ids, $v['id']);
			$cookie = array_key_exists('money_recharge', $_COOKIE) ? explode(',', $_COOKIE['money_recharge']) : array();
			if (array_diff($ids, $cookie)) {
				$id = $ids[0];
				$this->db->query("UPDATE `{$this->db_prefix}member_recharge` SET `flag`=1 WHERE `id`=$id LIMIT 1", 0);
				$data = $this->db->query("SELECT CASE WHEN `state`=9 THEN `amount` ELSE `rechargeAmount` END `CZAmount` FROM `{$this->db_prefix}member_recharge` WHERE `id`=$id LIMIT 1", 2);
				$CZAmount = $data['CZAmount'];
				setcookie('money_recharge', implode(',', $ids));
				if ($CZAmount > 0) {
					$text = '您的充值请求已处理，金额<span class="btn btn-red">'.$CZAmount.'</span>元';
					$no = array(
						'text' => '查看充值记录',
						'func' => 'setTimeout(function() {$("#user-recharge").trigger("click");},300);',
					);
				} else {
					$text = '您的账户已被扣款<span class="btn btn-red">'.abs($CZAmount).'</span>元，如有疑问请与管理员联系';
					$no = null;
				}
				$this->dialogue(array(
					'type' => 'success',
					'text' => $text,
					'auto' => true,
					'yes'  => array(
						'text' => '我知道了',
						'func' => 'lottery.user_fresh();',
					),
					'no' => $no,
				));
				exit;
			}
		}
	}
	
	// 盈亏提示
	public function loss_gain() {
		$this->check_post();
		$type_id = $this->get_id();
		$actionNo = array_key_exists('actionNo', $_GET) ? $_GET['actionNo'] : '';
		if (!is_numeric($actionNo)) core::__403();
		if ($type_id && $actionNo) {
			$ykMoney=0;
			//获取彩种名称
			$type_title = $this->db->query("SELECT `title` FROM `{$this->db_prefix}type` WHERE `id`={$type_id} LIMIT 1", 2);
			$type_title = $type_title['title'];
			
			$uid = $this->user['uid'];
			$whereStr = " WHERE `type`={$type_id} AND `uid`={$uid} AND `actionNo`='{$actionNo}' AND `isDelete`=0 AND `flag`=0 AND length(`lotteryNo`)>0";
			if ($this->db->query("SELECT `id` FROM `{$this->db_prefix}bets` ".$whereStr.' LIMIT 1', 2)) {
				$sql = "SELECT IFNULL(sum(`bonus`-(`mode`*`beiShu`*`actionNum`*(1-`fanDian`/100))),'0') tMoney FROM `{$this->db_prefix}bets` ".$whereStr."";
				$data = $this->db->query($sql, 2);
				$ykMoney = $data ? $data['tMoney'] : 0;
				$text = '['.$type_title.']第 '.$actionNo.' 期：盈亏<span class="btn '.($ykMoney > 0 ? 'btn-green' : 'btn-red').'">'.round($ykMoney, 2).'</span>元';
				$this->db->query("UPDATE `{$this->db_prefix}bets` SET `flag`=1 ".$whereStr, 0);
				$this->dialogue(array(
					'type' => 'success',
					'text' => $text,
					'auto' => true,
					'yes'  => array(
						'text' => '我知道了',
						'func' => 'lottery.user_fresh();',
					),
				));
			}
		}
	}
	
}