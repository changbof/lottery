<?php

class mod_index extends mod {

	public function software() {
		$this->client_type = 'software';
		$this->display('index');
	}
	
	public function web() {
		$this->client_type = 'web';
		$this->display('index', array(
			'recent_bonus_data' => $this->recent_bonus_data(),
			'yt_bonus_data'     => $this->yt_bonus_data(),
			'agent_data'        => $this->user['type'] ? $this->agent_data() : array(),
			'plays'             => $this->get_plays(),
			'gtypes'            => core::lib('game')->get_types(),
			'bet_data'          => $this->bet_data(),
		));
	}
	
	public function mobile() {
		$this->client_type = 'mobile';
		$this->display('index');
	}
	
	// 近期收益统计
	private function recent_bonus_data() {
		$time = strtotime('today') - 7 * 86400;
		$xAxis = $series = '';
		$uid = $this->user['uid'];
		for ($i=0;$i<7;$i++) {
			$start = $time;
			$time += 86400;
			$end = $time;
			$sql = "SELECT SUM(coin) AS `total_coin` FROM `{$this->db_prefix}coin_log` WHERE `uid`=$uid AND `coin`>0 AND `actionTime` BETWEEN $start AND $end";
			$data = $this->db->query($sql, 2);
			$series .= number_format($data['total_coin'], 3, '.', '').',';
			$xAxis .= '"'.date('m-d', $start).'",';
		}
		return array(
			'xAxis' => substr($xAxis, 0, -1),
			'series' => substr($series, 0, -1),
		);
	}
	
	// 今日及昨日的数据统计
	private function yt_bonus_data() {
		$uid = $this->user['uid'];
		// 今日数据
		$start = strtotime('today');
		$end = $this->time;
		$sql_today_money = "SELECT SUM(coin) AS `total_coin` FROM `{$this->db_prefix}coin_log` WHERE `uid`=$uid AND `liqType` NOT IN(1,8,9,106,107) AND `actionTime` BETWEEN $start AND $end";
		$data_today_money = $this->db->query($sql_today_money, 2);
		$sql_today_bets = "SELECT SUM(beiShu*mode*actionNum*(fpEnable+1)) betAmount FROM `{$this->db_prefix}bets` WHERE `uid`=$uid AND `isDelete`=0 AND `actionTime` BETWEEN $start AND $end";
		$data_today_bets = $this->db->query($sql_today_bets, 2);
		// 昨日数据
		$end = strtotime('today');
		$start = $end - 86400;
		$sql_yestoday_money = "SELECT SUM(coin) AS `total_coin` FROM `{$this->db_prefix}coin_log` WHERE `uid`=$uid AND `liqType` NOT IN(1,8,9,106,107) AND `actionTime` BETWEEN $start AND $end";
		$data_yestoday_money = $this->db->query($sql_yestoday_money, 2);
		$sql_yestoday_bets = "SELECT SUM(beiShu*mode*actionNum*(fpEnable+1)) betAmount FROM `{$this->db_prefix}bets` WHERE `uid`=$uid AND `actionTime` BETWEEN $start AND $end AND `isDelete`=0";
		$data_yestoday_bets = $this->db->query($sql_yestoday_bets, 2);
		
		return array(
			'today' => array(
				'money' => number_format($data_today_money['total_coin'], 3, '.', ''),
				'bets'  => number_format($data_today_bets['betAmount'], 3, '.', ''),
			),
			'yestoday' => array(
				'money' => number_format($data_yestoday_money['total_coin'], 3, '.', ''),
				'bets'  => number_format($data_yestoday_bets['betAmount'], 3, '.', ''),
			),
		);
	}
	
	// 获取代理数据
	private function agent_data() {
		$uid = $this->user['uid'];
		$money = $this->db->query("SELECT SUM(coin) AS `total_coin` FROM `{$this->db_prefix}coin_log` WHERE `uid`=$uid AND `liqType` IN(2,3,52,53,56)", 2);
		$child = $this->db->query("SELECT COUNT(1) count FROM `{$this->db_prefix}members` WHERE `isDelete`=0 AND `parentId`={$uid}", 2);
		$childs = $this->db->query("SELECT COUNT(1) count FROM `{$this->db_prefix}members` WHERE `isDelete`=0 AND CONCAT(',',parents,',') LIKE '%,{$uid},%'", 2);
		return array(
			'money' => number_format($money['total_coin'], 3, '.', ''),
			'child' => $child['count'],
			'childs' => $childs['count'] - 1,
		);
	}
	
	// 近期投注记录
	private function bet_data() {
		$uid = $this->user['uid'];
		return $this->db->query("SELECT * FROM `{$this->db_prefix}bets` WHERE `uid`=$uid ORDER BY `id` DESC LIMIT 10", 3);
	}

}