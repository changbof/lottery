<?php

class mod_game extends mod {
	
	// 获取玩法的相关说明
	private function get_play_info($play_id) {
		$sql = "SELECT `simpleInfo`,`info`,`example`,`groupId`,`playedTpl` FROM `{$this->db_prefix}played` WHERE `id`=$play_id LIMIT 1";
		return $this->db->query($sql, 2);
	}
	
	// 获取用户在指定彩种的近期投注记录
	private function get_recent_bets($type_id) {
		$recentNo = core::lib('game')->get_game_recent_no($type_id, 5);
		$actionNo = $recentNo['actionNo'];
		$uid = $this->user['uid'];
		$sql = "SELECT * FROM `{$this->db_prefix}bets` WHERE `isDelete`=0 AND `type`={$type_id} AND `uid`=$uid AND `actionNo`>='{$actionNo}' ORDER BY `id` DESC, `actionTime` DESC";
		return $this->db->query($sql, 3);
	}
	
	public function index() {
		//print_r($this->client_type);
		if (!array_key_exists('id', $_GET)) $_GET['id'] = 1; // 默认加载重庆时时彩
		$type_id = $this->get_id();
		$data = $this->db->query("SELECT `enable`,`title`,`type` FROM `{$this->db_prefix}type` WHERE `id`={$type_id} LIMIT 1", 2);
		if (!$data) core::__403();
		if (!$data['enable']) core::error($data['title'].'已经关闭');
		if ($this->post) {
			// 初始化默认值
			$group_id = 6; // 默认玩法类型ID：定位胆玩法
			$play_id = 37; // 默认玩法ID：五星定位胆	
			// 获取彩种列表
			$types = core::lib('game')->get_types();
			// 获取玩法类型列表
			$sql_type = $types[$type_id]['type'];
			$sql = "SELECT `id`,`groupName`,`enable` FROM `{$this->db_prefix}played_group` WHERE `enable`=1 AND `type`=$sql_type ORDER BY `sort`";
			$data = $this->db->query($sql, 3);
			if (!$data) core::error('当前彩种下暂未添加玩法');
			$groups = array();
			foreach ($data as $v) $groups[$v['id']] = $v;
			if (!array_key_exists($group_id, $groups)) $group_id = $data[0]['id'];
			// 获取玩法列表
			$plays = $this->get_plays($group_id);
			if (!$plays) core::error('当前彩种下暂未添加玩法');
			if (!array_key_exists($play_id, $plays)) {
				foreach ($plays as $play) {
					$play_id = $play['id'];
					$play_tpl = $play['playedTpl'];
					break;
				}
			} else {
				$play_tpl = $plays[$play_id]['playedTpl'];
			}
			// 获取玩法相关说明
			$play_info = $this->get_play_info($play_id);
			// 近期投注记录
			$bets_recent = $this->get_recent_bets($type_id);
			// 模板参数
			$args = array();
			$args['type_id'] = $type_id; // 彩种ID
			$args['group_id'] = $group_id;
			$args['play_id'] = $play_id;
			$args['types'] = $types; // 彩种列表
			$args['groups'] = $groups; // 玩法类型列表
			$args['plays'] = $plays; // 玩法列表
			$args['play_info'] = $play_info; // 玩法相关说明
			$args['play_tpl'] = $play_tpl; // 玩法数据录入模板
			$args['all_plays'] = $this->get_plays(); // 获取所有玩法
			$args['bets_recent'] = $bets_recent; // 近期投注记录
			$args['client_type'] = $this->client_type; // 前端访问设备
			$this->display('game/index', $args);
		} else {
			$this->ajax();
		}
	}
	
	// 用户追号
	public function zhuihao() {
		$this->check_post();
		$types = core::lib('game')->get_types();
		if (
			!array_key_exists('type', $_GET) || !array_key_exists($_GET['type'], $types) ||
			!array_key_exists('num', $_GET) || !in_array($_GET['num'], array(0, 10, 20, 30, 40, 50)) ||
			!array_key_exists('beiShu', $_GET) || !core::lib('validate')->number($_GET['beiShu']) ||
			!array_key_exists('mode', $_GET) || !array_key_exists($_GET['mode'] = number_format($_GET['mode'], 3, '.', ''), $this->modes) ||
			!array_key_exists('amount', $_GET) || !core::lib('validate')->number_float($_GET['amount'], 3)
		) core::error('403');
		$type = intval($_GET['type']);
		$num = intval($_GET['num']);
		$beiShu = intval($_GET['beiShu']);
		$mode = floatval($_GET['mode']);
		$amount = floatval($_GET['amount']);
		$html  = '<div id="submit-bets" class="zhuihao_box">';
		$html .= '<input type="hidden" id="zhuihao_amount" value="'.$amount.'">';
		$html .= '<input type="hidden" id="zhuihao_beiShu" value="'.$beiShu.'">';
		$html .= '<div class="submit-bets-head">';
		$html .= '<div class="submit-bets-title icon-basket">请选择追号期数';
		$html .= '<select onchange="lottery.zhuihao_load(this.value);" style="margin-left:10px">';
		$html .= '<option value="10"'.($num === 10 ? ' selected' : '').'>10期</option>';
		$html .= '<option value="20"'.($num === 20 ? ' selected' : '').'>20期</option>';
		$html .= '<option value="30"'.($num === 30 ? ' selected' : '').'>30期</option>';
		$html .= '<option value="40"'.($num === 40 ? ' selected' : '').'>40期</option>';
		$html .= '<option value="50"'.($num === 50 ? ' selected' : '').'>50期</option>';
		$html .= '<option value="0"'.($num === 0 ? ' selected' : '').'>今天全部</option>';
		$html .= '</select>';
		$html .= '<label style="margin-left:10px;cursor:pointer">';
		$html .= '<input type="checkbox" id="zhuiHao_mode" style="vertical-align:-2px" checked>';
		$html .= '中奖后停止追号';
		$html .= '</label>';
		$html .= '</div>';
		$html .= '<div class="submit-bets-info">';
		$html .= '<div class="count left icon-chart-bar">追号期数：<span class="num" id="zhuihao_num">0</span>期</div>';
		$html .= '<div class="amount left icon-yen">所需金额：<span class="num" id="zhuihao_total">0</span>元</div>';
		$html .= '</div>';
		$html .= '</div>';
		$html .= '<div class="submit-bets-body">';
		$html .= '<table width="100%" cellpadding="0" cellspacing="0">';
		$html .= '<tr class="head">';
		$html .= '<td class="choose_all" style="cursor:pointer">全选</td>';
		$html .= '<td>期号</td>';
		$html .= '<td>倍数</td>';
		$html .= '<td>模式</td>';
		$html .= '<td>金额</td>';
		$html .= '<td>开奖时间</td>';
		$html .= '</tr>';
		$nos = core::lib('game')->get_game_next_nos($type, $num);
		foreach ($nos as $no) {
			$html .= '<tr>';
			$html .= '<td><input type="checkbox" data-actionno="'.$no['actionNo'].'" data-actiontime="'.$no['actionTime'].'"></td>';
			$html .= '<td>'.$no['actionNo'].'</td>';
			$html .= '<td>'.$beiShu.'</td>';
			$html .= '<td>'.$mode.'</td>';
			$html .= '<td>'.$amount.'</td>';
			$html .= '<td>'.$no['actionTime'].'</td>';
			$html .= '</tr>';
		}
		$html .= '</table>';
		$html .= '</div>';
		$html .= '</div>';
		$this->dialogue(array(
			'class' => 'big',
			'body' => $html,
			'yes'  => array(
				'text' => '确定',
				'func' => 'lottery.zhuihao_sure();',
			),
			'no' => array('text' => '取消追号'),
		));
	}
	
	// 用户投注
	public function submit() {
		$this->check_post();
		if (
			!array_key_exists('code', $_POST) ||
			!array_key_exists('para', $_POST) ||
			!is_array($_POST['code']) ||
			!is_array($_POST['para'])
		) core::__403();
		$codes = $_POST['code'];
		$para = $_POST['para'];
		$amount = 0;
		$mincoin = 0;
		$maxcount = 0;
		$allNum = 0;
		$arr4 = array('15','23','27','29','30','31');
		$arr4id = array('9');
		$arr3 = array('7','11','13','14','15','19','21','22','23','25','26','27','28','29','30','31');
		$arr3id = array('15','22','23','24','41','196','201','202','219');
		$arr2 = array('3','5','6','7','9','10','11','12','13','14','15','17','18','19','20','21','22','23','24','25','26','27','28','29','30','31');
		$arr2id = array('30','35','36','213','214','208');
		if ($this->config['switchBuy'] == 0) core::error('本平台已经停止购买');
		if ($this->config['switchDLBuy'] == 0 && $this->user['type']) core::error('代理不能下注');
		if ($this->config['switchZDLBuy'] == 0 && $this->user['parents'] == $this->user['uid']) core::error('总代理不能下注');
		if (count($codes) == 0) core::error('请先选择号码再提交投注');
		// 检查时间、期数(1)
		if (!array_key_exists('kjTime', $para)) core::__403();
		$para['kjTime'] = intval($para['kjTime']);
		if ($para['kjTime'] < $this->time) core::error('提交数据出错,请刷新再投');
		if (!array_key_exists('type', $para)) core::__403();
		$para['type'] = intval($para['type']);
		if (!array_key_exists('actionNo', $para) || !preg_match('/^[0-9_\-]+$/', $para['actionNo'])) core::__403();
		$ftime = core::lib('game')->get_type_ftime($para['type']);  // 封单时间
		$actionTime = core::lib('game')->get_game_current_time($para['type']);  // 当期时间
		$actionNo = core::lib('game')->get_game_no($para['type']);  // 当期期数
		if (
			$actionTime != $para['kjTime'] ||
			$actionNo['actionNo'] != $para['actionNo'] ||
			$actionTime - $ftime < $this->time
		) core::error('投注失败，您投注的第<span class="btn btn-red">'.$para['actionNo'].'</span>期已过购买时间');
		// 查检每注的赔率是否正常
		$plays = $this->get_plays();
		$types = core::lib('game')->get_types();
		foreach ($codes as $key => $code) {
			if (!array_key_exists('actionData', $code) || !is_string($code['actionData'])) core::__403();
			// 大数据投注解压
			if (preg_match('/^deflate\-(\d+)\-([a-zA-Z0-9\+\/\=]+)$/is', trim($code['actionData']), $match)) {
				$actionData = base64_decode($match[2]);
				if (!$actionData) core::error('请求数据错误，请重新提交');
				$actionData = gzinflate($actionData);
				if (!$actionData || strlen($actionData) !== intval($match[1])) core::error('请求数据错误，请重新提交');
				$code['actionData'] = $actionData;
				$codes[$key]['actionData'] = $actionData;
			}
			if (preg_match('/[a-z]/i', $code['actionData'])) core::error('请求数据错误，请重新提交');
			// 任三混合组选处理
			if (!array_key_exists('playedId', $code) || !core::lib('validate')->number($code['playedId'])) core::__403();
			$code['playedId'] = intval($code['playedId']);
			if ($code['playedId'] === 24) {
				if (!array_key_exists('playedName', $code) || !is_string($code['playedName'])) core::__403();
				if ($code['playedName'] === '任选三组六') {
					$codes[$key]['playedId'] = 23;
					$code['playedId'] = 23;
				} else if ($code['playedName'] === '任选三组三') {
					$codes[$key]['playedId'] = 22;
					$code['playedId'] = 22;
				}
			}
			// 检查时间、期数(2)
			if (!array_key_exists('type', $code) || !core::lib('validate')->number($code['type'])) core::__403();
			$code['type'] = intval($code['type']);
		    $ftime2 = core::lib('game')->get_type_ftime($code['type']);  // 封单时间
		    $actionTime2 = core::lib('game')->get_game_current_time($code['type']);  // 当期时间
		    $actionNo2 = core::lib('game')->get_game_no($code['type']);  // 当期期数
			if (
				$actionTime2 != $para['kjTime'] ||
				$actionNo2['actionNo'] != $para['actionNo'] ||
				$actionTime - $ftime2 < $this->time
			) core::error('投注失败，您投注的第<span class="btn btn-red">'.$para['actionNo'].'</span>期已过购买时间');
			if (!array_key_exists($code['playedId'], $plays)) core::__403();
			$played = $plays[$code['playedId']];
			// 获取实际奖金数额
			$diff_fanDian = $this->config['fanDianMax'] - $this->user['fanDian'];
			$proportion = 1 - $diff_fanDian / 100;
			$bonusProp = number_format($played['bonusProp'] * $proportion, 2, '.', '');
			$bonusPropBase = number_format($played['bonusPropBase'] * $proportion, 2, '.', '');
			// 检查开启
			if(!$played['enable']) core::error('游戏玩法组已被禁用，请刷新后重新投注');
            // 检查ID
			if (!array_key_exists($code['type'], $types)) core::error('您提交的彩种不存在或已被禁用');
			if ($played['type'] != $types[$code['type']]['type']) core::__403();
			if (!array_key_exists('playedGroup', $code) || !core::lib('validate')->number($code['playedGroup'])) core::__403();
            if ($played['groupId'] != $code['playedGroup']) core::__403();
			if ($played['id'] != $code['playedId']) core::__403();
			// 检查赔率和返点
			if (!array_key_exists('bonusProp', $code) || !core::lib('validate')->number_float($code['bonusProp'], 2)) core::__403();
			if ($code['bonusProp'] > $bonusProp) core::error('提交奖金大于最大奖金，请重新投注');
			if ($code['bonusProp'] < $bonusPropBase) core::error('提交奖金小于最小奖金，请重新投注');
			if (!array_key_exists('fanDian', $code) || !core::lib('validate')->number_float($code['fanDian'], 1)) core::__403();
			// 获取不同模式的返点的
			$fandian_modes = array(
				'2.000' => $this->config['betModeMaxFanDian0'],
				'0.200' => $this->config['betModeMaxFanDian1'],
				'0.020' => $this->config['betModeMaxFanDian2'],
				'0.002' => $this->config['betModeMaxFanDian3'],
			);
			$fandian_this = array_key_exists($code['mode'], $fandian_modes) ? $fandian_modes[$code['mode']] : 0;
			if (
				!$fandian_this || $code['fanDian'] > $fandian_this || ($code['fanDian'] % 0.5) ||
				$code['bonusProp'] < $bonusPropBase || $code['bonusProp'] > $bonusProp ||
				$code['bonusProp'] > number_format(ceil(strval($bonusProp * (1 - $code['fanDian'] / 100) * 100)) / 100, 2, '.', '')
			) core::__403();
			// 检查倍数
			if (!array_key_exists('beiShu', $code) || !core::lib('validate')->number($code['beiShu'])) core::__403();
			//检查位数
			if (!array_key_exists('weiShu', $code) || (!core::lib('validate')->number($code['weiShu']) && $code['weiShu'] != 0)) core::__403();
			if (in_array($code['playedId'], $arr4id)) {
				if (!in_array($code['weiShu'], $arr4)) core::__403();
			}
			if (in_array($code['playedId'], $arr3id)) {
				if (!in_array($code['weiShu'], $arr3)) core::__403();
			}
			if (in_array($code['playedId'], $arr2id)) {
				if (!in_array($code['weiShu'], $arr2)) core::__403();
			}
			//检查模式
			if (!array_key_exists('mode', $code) || !core::lib('validate')->number_float($code['mode'], 3)) core::__403();
			$mosi = array();
			if ($this->config['yuanmosi'] == 1) array_unshift($mosi, '2.000');
			if ($this->config['jiaomosi'] == 1) array_unshift($mosi, '0.200');
			if ($this->config['fenmosi'] == 1) array_unshift($mosi, '0.020');
			if ($this->config['limosi'] == 1) array_unshift($mosi, '0.002');
			if (!in_array($code['mode'], $mosi)) core::error('投注模式出错，请重新投注');
			// 检查注数
			if (!array_key_exists('actionNum', $code) || !core::lib('validate')->number($code['actionNum'])) core::__403();
			if ($betCountFun = $played['betCountFun']) {
				if ($code['actionNum'] != core::lib('bet')->$betCountFun($code['actionData'])) core::error('下单失败，您投注号码不符合投注规则，请重新投注');
			}
			// 最大注数检查
            $maxcount = $played['maxcount'];
			$playedname = $played['name'];
            if ($code['actionNum'] > $maxcount) core::error('['.$playedname.']投注上限为<span class="btn btn-red">'.$maxcount.'</span>注，请重新投注');
			//最低消费金额计算
			$mincoin += $played['minCharge'];
			//总注数计算
			$allNum += $code['actionNum'];
		}
        $code = current($codes);
		if (isset($para['actionNo'])) unset($para['actionNo']);
		if (isset($para['kjTime'])) unset($para['kjTime']);
		$para = array_merge($para, array(
			'actionTime' => $this->time,
			'actionNo' => $actionNo['actionNo'],
			'kjTime' => $actionTime,
			'actionIP' => $this->ip(true),
			'uid' => $this->user['uid'],
			'username' => $this->user['username'],
			'serializeId' => uniqid()
		));
		$code = array_merge($code, $para);
		if (array_key_exists('zhuiHao', $_POST) && ($zhuihao = $_POST['zhuiHao'])) {
			$liqType = 102;
			$codes = array();
			$info = '追号投注';
			if (isset($para['actionNo'])) unset($para['actionNo']);
			if (isset($para['kjTime'])) unset($para['kjTime']);
			$zhuihao = explode(';', $zhuihao);
			foreach($zhuihao as $var){
				list($code['actionNo'], $code['beiShu'], $code['kjTime']) = explode('|', $var);
				$code['type'] = intval($code['type']);
				$code['kjTime'] = strtotime($code['kjTime']);
				$code['beiShu'] = abs(intval($code['beiShu']));
				$actionNo = core::lib('game')->get_game_no($para['type'], $code['kjTime'] - 1);
				$ano = core::lib('game')->get_game_no($code['type']);
				if ($code['actionNo'] != $ano['actionNo']) {
					if (strpos($code['actionNo'], '-') !== false && strpos($ano['actionNo'], '-') !== false) {
						list($dt1, $b1) = explode('-', $code['actionNo']); // 提交的
						list($dt2, $b2) = explode('-', $ano['actionNo']); // 当前的
						if ($dt2 < $dt1 || ($dt2 == $dt1 && $b2 < $b1)) {
						} else {
							core::error('投注失败，您追投的第<span class="btn btn-red">'.$ano['actionNo'].'</span>期已经过购买时间');
						}
					}
				}
				if (strtotime($actionNo['actionTime']) - $ftime < $this->time) {
					core::error('投注失败，您追投的第<span class="btn btn-red">'.$ano['actionNo'].'</span>期已经过购买时间');
				}
				$amount += abs($code['actionNum'] * $code['mode'] * $code['beiShu']);
				$codes[] = $code;
			}
		} else {
			$liqType = 101;
			$info = '投注';
            if ($actionNo['actionNo'] != $code['actionNo']) {
				core::error('投注失败，您投注的第<span class="btn btn-red">'.$actionNo['actionNo'].'</span>期已经过购买时间');
			}
			foreach ($codes as $i => $code) {
				$codes[$i] = array_merge($code, $para);
				$this_amount = abs($code['actionNum'] * $code['mode'] * $code['beiShu']);
				if ($this_amount < 0.01) core::error('单笔投注金额不得小于0.01元');
				$amount += $this_amount;
			}
		}
		// 最低消费金额检查
		if ($amount < $mincoin) core::error('您的投注金额小于最低消费金额<span class="btn btn-red">'.$mincoin.'</span>元，请重新投注');
		// 查询用户可用资金
		$uid = $this->user['uid'];
		$userAmount = $this->db->query("SELECT `coin` FROM `{$this->db_prefix}members` WHERE `uid`={$uid} LIMIT 1", 2);
		$userAmount = $userAmount['coin'];
		if ($userAmount < $amount){
			if($this->client_type === 'web') core::error('您的可用资金不足，是否充值？');
			else core::error('您的可用资金不足，请前往网页版后台充值！');
		}
		// 开始事务处理
		$this->db->transaction('begin');
		try {
			foreach ($codes as $code) {
				unset($code['playedName']);
				// 插入投注表
				$code['wjorderId'] = $code['type'].$code['playedId'].$this->randomkeys(8 - strlen($code['type'].$code['playedId']));
				$code['actionNum'] = abs($code['actionNum']);
				$code['mode'] = abs($code['mode']);
				$code['beiShu'] = abs($code['beiShu']);
				$amount = abs($code['actionNum'] * $code['mode'] * $code['beiShu']);
				$id = $this->db->insert($this->db_prefix .'bets', $code);
				// 添加用户资金流动日志
				$this->set_coin(array(
					'uid' => $this->user['uid'],
					'type' => $code['type'],
					'liqType' => $liqType,
					'info' => $info,
					'extfield0' => $id,
					'extfield1' => $para['serializeId'],
					'coin' => -$amount,
				));
			}
			// 返点与积分等开奖时结算
			$this->db->transaction('commit');
			echo '投注成功';
		} catch(Exception $e) {
			$this->db->transaction('rollBack');
			core::error($e->getMessage());
		}
	}
	
	// 随机函数
	private function randomkeys($length) {
		$key = "";
		$pattern = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
		$pattern1 = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$pattern2 = '0123456789';
		for ($i=0;$i<$length;$i++) $key .= $pattern{mt_rand(0,35)};
		return $key;
	}
	
	// 获取用户近期投注
	public function bets() {
		$this->check_post();
		$type_id = $this->get_id();
		$bets_recent = $this->get_recent_bets($type_id);
		$this->display('game/bets_recent', array(
			'bets_recent' => $bets_recent,
			'types' => core::lib('game')->get_types(),
			'all_plays' => $this->get_plays(),
		));
	}
	
	public function group() {
		$this->check_post();
		$type_id = $this->get_id('type_id');
		$group_id = $this->get_id('group_id');
		// 获取玩法列表
		$plays = $this->get_plays($group_id);
		if (!$plays) core::__403();
		foreach ($plays as $play) {
			$play_id = $play['id'];
			$play_tpl = $play['playedTpl'];
			break;
		}
		// 获取玩法相关说明
		$play_info = $this->get_play_info($play_id);
		// 加载模板
		$this->display('game/play_index', array(
			'type_id' => $type_id,
			'group_id' => $group_id,
			'play_id' => $play_id,
			'plays' => $plays,
			'play_info' => $play_info,
			'play_tpl' => $play_tpl,
		));
	}
	
	public function play() {
		$this->check_post();
		$type_id = $this->get_id('type_id');
		$play_id = $this->get_id('play_id');
		// 获取玩法相关说明
		$play_info = $this->get_play_info($play_id);
		if (!$play_info) core::__403();
		// 加载模板
		$this->display('game/play_data', array(
			'type_id' => $type_id,
			'group_id' => $play_info['groupId'],
			'play_id' => $play_id,
			'play_info' => $play_info,
			'play_tpl' => $play_info['playedTpl'],
		));
	}
	
	public function lottery() {
		$this->check_post();
		$type_id = $this->get_id();
		$mode = array_key_exists('mode', $_GET) ? $_GET['mode'] : '';
		// 获取上期期号数据(期号,开奖时间)
		$last = core::lib('game')->get_game_last_no($type_id);
		$actionNo = $last['actionNo'];
		$sql = "SELECT `data` FROM `{$this->db_prefix}data` WHERE `type`={$type_id} AND `number`='{$actionNo}' LIMIT 1";
		$lottery = $this->db->query($sql, 2);
		$lottery = $lottery ? explode(',', $lottery['data']) : array();
		// 获取下期期号数据(期号,开奖时间)
		$current = core::lib('game')->get_game_no($type_id);
		$types = core::lib('game')->get_types();

        // 获取开奖时间数据
		$kjdTime = $types[$type_id]['data_ftime'];  // 开奖等待时间,即开奖前停止下注时间
		$diffTime = strtotime($current['actionTime']) - $this->time - $kjdTime;  //投注结束剩余时间
		$kjDiffTime = strtotime($last['actionTime']) - $this->time;   // 开奖剩余时间

        // 获取开奖历史: 获取20期/10期(mobile)
		$sql = "SELECT `time`,`number`,`data` FROM `{$this->db_prefix}data` WHERE `type`={$type_id} ORDER BY `id` DESC LIMIT ".$this->pagesize;
		$history = $this->db->query($sql, 3);

		$tpl = 'game/lottery';
		if($mode==='1') $tpl = 'game/lottery_recent';
		
		$this->display($tpl, array(
			'types' => $types,
			'type_id' => $type_id,
			'type_this' => $types[$type_id],
			'last' => $last,
			'current' => $current,
			'lottery' => $lottery,
			'kjdTime' => $kjdTime,
			'diffTime' => $diffTime,
			'kjDiffTime' => $kjDiffTime,
			'history' => $history,
		));
	}
	
	// 获取上期开奖数据
	public function last() {
		$this->check_post();
		$type_id = $this->get_id();
		$last = core::lib('game')->get_game_last_no($type_id);
		if (!$last) core::error('查找最后开奖期号出错');
		$actionNo = $last['actionNo'];
		$lottery = $this->db->query("SELECT `data` FROM `{$this->db_prefix}data` WHERE `type`={$type_id} AND `number`='{$actionNo}' LIMIT 1", 2);
		echo $lottery ? 1 : 0;
	}
	
	// 获取彩种当期期号及时间数据
	public function current() {
		$this->check_post();
		$type_id = $this->get_id();
		$actionNo = core::lib('game')->get_game_no($type_id);
		if ($type_id == 1 && $actionNo['actionTime'] == '00:00:00') {
			$actionNo['actionTime'] = strtotime($actionNo['actionTime']) + 24*3600;
		} else {
			$actionNo['actionTime'] = strtotime($actionNo['actionTime']);
		}
		echo json_encode($actionNo);
	}
	
}
