<?php

class mod_bet extends mod {
	
	private function remove($id) {
		$this->db->transaction('begin');
		try {
			$data = $this->db->query("SELECT * FROM `{$this->db_prefix}bets` WHERE `id`=$id LIMIT 1", 2);
			if (!$data) core::__403();
			if ($data['isDelete']) return;
			if ($data['uid'] != $this->user['uid']) core::__403();
			if ($data['kjTime'] <= $this->time || $data['lotteryNo']) core::error('您提交的下注已经开奖，不能撤单');
			// 冻结时间后不能撤单
			$types = core::lib('game')->get_types();
			$ftime = core::lib('game')->get_type_ftime($data['type']);
			if ($data['kjTime'] - $ftime < $this->time) core::error('您提交的下注正在开奖，不能撤单');
			// 添加用户资金变更日志
			$amount = $data['beiShu'] * $data['mode'] * $data['actionNum'];
			$amount = abs($amount);
			$this->set_coin(array(
				'uid' => $data['uid'],
				'type' => $data['type'],
				'playedId' => $data['playedId'],
				'liqType' => 7,
				'info' => '撤单',
				'extfield0' => $id,
				'coin' => $amount,
			));
			// 更改定单为已经删除状态
			$this->db->query("UPDATE `{$this->db_prefix}bets` SET `isDelete`=1 WHERE `id`=$id LIMIT 1", 0);
			$this->db->transaction('commit');
		} catch (Exception $e) {
			$this->db->transaction('rollBack');
			core::error($e->getMessage());
		}
	}
	
	public function remove_batch() {
		$this->check_post();
		if (!array_key_exists('ids', $_POST) || !is_array($_POST['ids'])) core::__403();
		foreach ($_POST['ids'] as $id) {
			if (!core::lib('validate')->number($id)) core::__403();
			$id = intval($id);
			$this->remove($id);
		}
	}
	
	public function remove_single() {
		$this->check_post();
		$id = $this->get_id();
		$this->remove($id);
	}

	public function info() {
		$this->check_post();
		$id = $this->get_id();
		$bet = $this->db->query("SELECT * FROM `{$this->db_prefix}bets` WHERE `id`=$id LIMIT 1", 2);
		if (!$bet) core::__403();
		$weiShu = $bet['weiShu'];
		$wei = '';
		if($weiShu){
			$w = array(16 => '万', 8 => '千', 4 => '百', 2 => '十', 1 => '个');
			foreach($w as $p=>$v){
				if($weiShu & $p) $wei .= $v;
			}
			$wei .='：';
		}
		$betCont=$bet['mode'] * $bet['beiShu'] * $bet['actionNum'];
		$types = core::lib('game')->get_types();
		$plays = $this->get_plays();
		$html  = '<div class="detail">';
		$html .= '<table cellpadding="0" cellspacing="0" width="100%">';

		if($this->client_type=='mobile'){
			$html .= '<tr>';
			$html .= '<td class="k" width="30%">彩种</td>';
			$html .= '<td class="v" width="70%">'.($types[$bet['type']]['shortName'] ? $types[$bet['type']]['shortName'] : $types[$bet['type']]['title']).' - '.$plays[$bet['playedId']]['name'].'</td>';
			$html .= '</tr>';
			$html .= '<tr>';
			$html .= '<td class="k">状态</td>';
			if ($bet['isDelete'] ==1) {
				$status = '<span class="gray">已撤单</span>';
			} else if (!$bet['lotteryNo']) {
				$status = '<span class="green">未开奖</span>';
			} else if ($bet['zjCount']) {
				$status = '<span class="red">已派奖</span>';
			} else {
				$status = '未中奖';
			}
			$html .= '<td class="v">'.$status.'</td>';
			$html .= '</tr>';
//			$html .= '<tr>';
//			$html .= '<td class="k">倍数模式</td>';
//			$html .= '<td class="v">'.$bet['beiShu'].' ['.$this->modes[$bet['mode']].']</td>';
//			$html .= '<td class="k">奖金返点</td>';
//			$html .= '<td class="v">'.number_format($bet['bonusProp'], 2, '.', '').' - '.number_format($bet['fanDian'], 1, '.', '').'%</td>';
//			$html .= '</tr>';
			$html .= '<tr>';
			$html .= '<td class="k">投注期号</td>';
			$html .= '<td class="v">'.$bet['actionNo'].'</td>';
			$html .= '</tr>';
//			$html .= '<tr>';
//			$html .= '<td class="k">投注时间</td>';
//			$html .= '<td class="v">'.date('Y-m-d H:i:s', $bet['actionTime']).'</td>';
//			$html .= '</tr>';
			$html .= '<tr>';
			$html .= '<td class="k">开奖号码</td>';
			$html .= '<td class="v">'.($bet['lotteryNo'] ? $bet['lotteryNo'] : '--').'</td>';
			$html .= '</tr>';
			$html .= '<tr>';
			$html .= '<td class="k">开奖时间</td>';
			$html .= '<td class="v">'.($bet['lotteryNo'] ? date('m-d H:i:s', $bet['kjTime']) : '--').'</td>';
			$html .= '</tr>';
			$html .= '<tr>';
			$html .= '<td class="k">购买金额</td>';
			$html .= '<td class="v">'.number_format($betCont, 3, '.', '').' ('.$this->modes[$bet['mode']].') / 共 '.$bet['actionNum'].' 注</td>';
			$html .= '</tr>';
			$html .= '<tr>';
			$html .= '<td class="k">中奖金额</td>';
			$html .= '<td class="v">'.($bet['lotteryNo'] ? number_format($bet['bonus'], 3, '.', '').' 元' : '--').'</td>';
			$html .= '</tr>';
			$html .= '<tr>';
			$html .= '<td class="k">购买盈亏</td>';
			if ($bet['lotteryNo']) {
				$money = number_format($bet['bonus'] - $betCont + ($bet['fanDian'] / 100) * $betCont, 3, '.', '');
				$loss_gain = ($money > 0 ? '赢' : '亏').abs($money).'元';
			} else {
				$loss_gain = '---';
			}
			$html .= '<td class="v">'.$loss_gain.'</td>';
			$html .= '</tr>';
			$html .= '<tr>';
			$html .= '<td colspan="2">';
			$html .= '<div class="actionData">'.$wei.$bet['actionData'].'</div>';
			$html .= '</td>';
			$html .= '</tr>';
		} else {
			$html .= '<tr>';
			$html .= '<td class="k" width="14%">所属彩种</td>';
			$html .= '<td class="v" width="20%">'.($types[$bet['type']]['shortName'] ? $types[$bet['type']]['shortName'] : $types[$bet['type']]['title']).'</td>';
			$html .= '<td class="k" width="13%">订单玩法</td>';
			$html .= '<td class="v" width="20%">'.$plays[$bet['playedId']]['name'].'</td>';
			$html .= '<td class="k" width="13%">订单状态</td>';
			if ($bet['isDelete'] ==1) {
				$status = '<span class="gray">已撤单</span>';
			} else if (!$bet['lotteryNo']) {
				$status = '<span class="green">未开奖</span>';
			} else if ($bet['zjCount']) {
				$status = '<span class="red">已派奖</span>';
			} else {
				$status = '未中奖';
			}
			$html .= '<td class="v" width="20%">'.$status.'</td>';
			$html .= '</tr>';
			$html .= '<tr>';
			$html .= '<td class="k">订单编号</td>';
			$html .= '<td class="v">'.$bet['wjorderId'].'</td>';
			$html .= '<td class="k">倍数模式</td>';
			$html .= '<td class="v">'.$bet['beiShu'].' ['.$this->modes[$bet['mode']].']</td>';
			$html .= '<td class="k">奖金返点</td>';
			$html .= '<td class="v">'.number_format($bet['bonusProp'], 2, '.', '').' - '.number_format($bet['fanDian'], 1, '.', '').'%</td>';
			$html .= '</tr>';
			$html .= '<tr>';
			$html .= '<td class="k">开奖号码</td>';
			$html .= '<td class="v">'.($bet['lotteryNo'] ? $bet['lotteryNo'] : '--').'</td>';
			$html .= '<td class="k">投注时间</td>';
			$html .= '<td class="v">'.date('Y-m-d H:i:s', $bet['actionTime']).'</td>';
			$html .= '<td class="k">投注期号</td>';
			$html .= '<td class="v">'.$bet['actionNo'].'</td>';
			$html .= '</tr>';
			$html .= '<tr>';
			$html .= '<td class="k">开奖时间</td>';
			$html .= '<td class="v">'.($bet['lotteryNo'] ? date('m-d H:i:s', $bet['kjTime']) : '--').'</td>';
			$html .= '<td class="k">购买注数</td>';
			$html .= '<td class="v">'.$bet['actionNum'].' 注</td>';
			$html .= '<td class="k">购买金额</td>';
			$html .= '<td class="v">'.number_format($betCont, 3, '.', '').'</td>';
			$html .= '</tr>';
			$html .= '<tr>';
			$html .= '<td class="k">返点金额</td>';
			$html .= '<td class="v">'.($bet['fanDian'] ? number_format(($bet['fanDian'] / 100) * $betCont, 3, '.', '') : '0').' 元</td>';
			$html .= '<td class="k">中奖金额</td>';
			$html .= '<td class="v">'.($bet['lotteryNo'] ? number_format($bet['bonus'], 3, '.', '').' 元' : '--').'</td>';
			$html .= '<td class="k">购买盈亏</td>';
			if ($bet['lotteryNo']) {
				$money = number_format($bet['bonus'] - $betCont + ($bet['fanDian'] / 100) * $betCont, 3, '.', '');
				$loss_gain = ($money > 0 ? '赢' : '亏').abs($money).'元';
			} else {
				$loss_gain = '---';
			}
			$html .= '<td class="v">'.$loss_gain.'</td>';
			$html .= '</tr>';
			$html .= '<tr>';
			$html .= '<td colspan="6">';
			$html .= '<div class="actionData">'.$wei.$bet['actionData'].'</div>';
			$html .= '</td>';
			$html .= '</tr>';
		}

		$html .= '</table>';
		$html .= '</div>';
		$this->dialogue(array(
			'class' => 'big',
			'body' => $html,
			'yes'  => array('text' => '确定'),
		));
	}
	
	// 获取游戏记录
	public function log() {
		if ($this->post) {
			$tpl = $this->ispage ? '/bet/log_body' : '/bet/log';
			$this->get_time();
			$args = $this->log_get_args();
			$page_current = $this->get_page();
			$game_log = $this->log_search_func($args, $page_current);
			$page_max = $this->get_page_max($game_log['total']);
			if ($page_current > $page_max) core::__403();
			$page_args = $this->log_page_args($args);
			$this->display($tpl, array(
				'args' => $args,
				'_types' => $this->get_types(),
				'types' => core::lib('game')->get_types(),
				'plays' => $this->get_plays(),
				'state' => array(0 => '所有状态', 1=> '已派奖', 2 => '未中奖', 3 => '未开奖', 4 => '追号', /*5 => '合买跟单',*/ 6 => '撤单'),
				'data' => $game_log['data'],
				'page_current' => $page_current,
				'page_max' => $page_max,
				'page_url' => '/bet/log?'.http_build_query($page_args),
				'page_container' => '#bet-log-dom .body',
			));
		} else {
			$this->ajax();
		}
	}
	
	// 搜索游戏记录
	public function log_search() {
		$this->check_post();
		$this->get_time(false);
		$args = $this->log_get_args(false);
		$game_log = $this->log_search_func($args, 1);
		$page_max = $this->get_page_max($game_log['total']);
		$page_args = $this->log_page_args($args);
		$this->display('/bet/log_body', array(
			'types' => core::lib('game')->get_types(),
			'plays' => $this->get_plays(),
			'data' => $game_log['data'],
			'page_current' => 1,
			'page_max' => $page_max,
			'page_url' => '/bet/log?'.http_build_query($page_args),
			'page_container' => '#bet-log-dom .body',
		));
	}
	
	// 游戏记录搜索函数
	private function log_search_func($args, $page_current) {
		$uid = $this->user['uid'];
		$pagesize = $this->pagesize;
		$skip = ($page_current - 1) * $pagesize;
		$sql = "SELECT ~field~ FROM `{$this->db_prefix}bets` WHERE";
		$where = " `uid`={$uid}";
		if ($args['type']) $where .= " AND `type`={$args['type']}";
		if ($args['state']) {
			switch ($args['state']) {
				case 1: // 已派奖
					$where .= ' AND `zjCount`>0';
				break;

				case 2: // 未中奖
					$where .= " AND `zjCount`=0 AND `lotteryNo`!='' AND `isDelete`=0";
				break;

				case 3: // 未开奖
					$where .= " AND `lotteryNo`=''";
				break;

				case 4: // 追号
					$where .= ' AND `zhuiHao`=1';
				break;
				
				case 5: // 合买跟单
					$where .= ' AND `hmEnable`=1';
				break;

				case 6: // 撤单
					$where .= ' AND `isDelete`=1';
				break;
			}
		}
		if ($args['mode'] !== '0.000') $where .= " AND `mode`={$args['mode']}";
		if ($args['betId']) $where .= " AND `wjorderId`='{$args['betId']}'";
		$where .= $this->build_where_time('`actionTime`');
		if (substr($where, 0, 5) === ' AND ') $where = substr($where, 5);
		$sql .= $where;
		$sql .= ' ~order~ ~limit~';
		$sql_total = str_replace('~field~', 'COUNT(1) AS __total', $sql);
		$sql_total = str_replace('~limit~', '', $sql_total);
		$sql_total = str_replace('~order~', '', $sql_total);
		$total = $this->db->query($sql_total, 2);
		$total = $total['__total'];
		$sql_data = str_replace('~field~', '*', $sql);
		$sql_data = str_replace('~limit~', "LIMIT $skip,$pagesize", $sql_data);
		$sql_data = str_replace('~order~', 'ORDER BY `id` DESC', $sql_data);
		$data = $this->db->query($sql_data, 3);
		return array(
			'data' => $data,
			'total' => $total,
		);
	}
	
	// 获取搜索参数
	private function log_get_args($get = true) {
		$data = $get ? $_GET : $_POST;
		$args = array();
		$args['type'] = (array_key_exists('type', $data) && core::lib('validate')->number($data['type'])) ? intval($data['type']) : 0;
		$args['state'] = (array_key_exists('state', $data) && in_array($data['state'], array(0, 1, 2, 3, 4, 5, 6))) ? intval($data['state']) : 0;
		$args['mode'] = (array_key_exists('mode', $data) && array_key_exists($data['mode'], $this->modes)) ? strval($data['mode']) : '0.000';
		$args['betId'] = (array_key_exists('betId', $data) && preg_match('/^[a-zA-Z0-9]{8}$/', $data['betId'])) ? $data['betId'] : '';
		return $args;
	}
	
	// 组装网址参数
	private function log_page_args($args) {
		$page_args = array_filter($args);
		if ($this->request_time_from) $page_args['fromTime'] = date('Y-m-d H:i', $this->request_time_from);
		if ($this->request_time_to) $page_args['toTime'] = date('Y-m-d H:i', $this->request_time_to);
		$page_args['page'] = '{page}';
		return $page_args;
	}

}