<?php

class mod_activity extends mod {
	
	// 积分兑换: 兑换界面
	public function exchange() {
		if ($this->post) {
			$this->get_exchange_config();
			$this->display('/activity/exchange');
		} else {
			$this->ajax();
		}
	}
	
	// 积分兑换: 兑换函数
	public function exchange_submit() {
		$this->check_post();
		$this->get_exchange_config();
		$uid = $this->user['uid'];
		if (!$this->exchange_config['switchWeb'] || !$this->exchange_config['score']) core::error('积分兑换活动未开启，敬请关注！');
		if (!array_key_exists('score', $_POST) || !core::lib('validate')->number($_POST['score'])) core::__403();
		$score = intval($_POST['score']);
		if ($score < $this->exchange_config['score']) core::error('兑换的积分数量必须大于<span class="btn btn-red">'.$this->exchange_config['score'].'</span>');
		if ($score % $this->exchange_config['score']) core::error('兑换的积分数量必须是<span class="btn btn-red">'.$this->exchange_config['score'].'</span>的倍数');
		$user_data = $this->db->query("SELECT `score` FROM `{$this->db_prefix}members` WHERE `uid`={$uid} LIMIT 1", 2);
		if ($user_data['score'] < $score) core::error('您的积分不足低于<span class="btn btn-red">'.$score.'</span>，无法完成兑换');
		$money = intval($score / $this->exchange_config['score']);
		$this->db->transaction('begin');
		try {
			$sql = "UPDATE `{$this->db_prefix}members` SET `score`=`score`-{$score} where uid={$uid} LIMIT 1";
			if (!$this->db->query($sql, 0)) throw new Exception('数据库查询失败，请重试');
			$this->user['score'] -= $score;
			$_SESSION[$this->user_session] = serialize($this->user);
			$this->set_coin(array(
				'uid' => $this->user['uid'],
				'coin' => $money,
				'liqType' => 121,
				'extfield0' => 0,
				'extfield1' => 0,
				'info' => '积分兑换'
			));
			$para = array(
				'uid' => $this->user['uid'],
				'swapTime' => $this->time,
				'swapIp' => $this->ip(true),
				'coin' => $money,
				'score' => $this->user['score']-$score,
				'xscore' => $score,
			);
			if (!$this->db->insert($this->db_prefix.'exchange_swap', $para)) throw new Exception('数据库更新失败，请重试');
			$this->db->transaction('commit');
		} catch(Exception $e) {
			$this->db->transaction('rollBack');
			core::error($e->getMessage());
		}
		$this->dialogue(array(
			'type' => 'success',
			'text' => '兑换成功',
			'auto' => true,
			'yes'  => array(
				'text' => '我知道了',
				'func' => '$.reload();',
			),
		));
	}
	
	private function get_exchange_config() {
		$sql = "SELECT * from `{$this->db_prefix}exchange_params`";
		if ($data = $this->db->query($sql, 3)) {
			foreach ($data as $var) {
				$this->exchange_config[$var['name']] = $var['value'];
			}
		}
	}

	// 幸运大转盘: 抽奖界面
	public function rotary() {
		if ($this->post) {
			$this->getdzpSettings();
			$this->display('/activity/rotary');
		} else {
			$this->ajax();
		}
	}
	
	// 幸运大转盘: 抽奖函数
	public function rotary_submit() {
		$this->check_post();
		$this->getdzpSettings();
		$config = $this->dzpsettings;
		$score = $config['score'];
		$uid = $this->user['uid'];
		$user_data = $this->db->query("SELECT `score` FROM `{$this->db_prefix}members` WHERE `uid`={$uid} LIMIT 1", 2);
		if ($user_data['score'] < $score) {
			$result['angle'] = 0;
			$result['prize'] = '你拥有积分不足，不能能参加转盘抽奖活动！';
		} else if (!$config['switchWeb'] || !$score) {
			$result['angle'] = 0;
			$result['prize'] = '幸运大转盘活动未开启，敬请关注！';
		} else {
			$prize_arr = array(
				'0' => array('id'=>1,'min'=>289,'max'=>323,'prize'=>$config['goods289323'],'v'=>$config['chance289323'],'j'=>$config['coin289323']),
				'1' => array('id'=>2,'min'=>181,'max'=>215,'prize'=>$config['goods181215'],'v'=>$config['chance181215'],'j'=>$config['coin181215']),
				'2' => array('id'=>3,'min'=>37,'max'=>71,'prize'=>$config['goods3771'],'v'=>$config['chance3771'],'j'=>$config['coin3771']),
				'3' => array('id'=>4,'min'=>73,'max'=>107,'prize'=>$config['goods73107'],'v'=>$config['chance73107'],'j'=>$config['coin73107']),
				'4' => array('id'=>5,'min'=>253,'max'=>287,'prize'=>$config['goods253287'],'v'=>$config['chance253287'],'j'=>$config['coin253287']),
				'5' => array('id'=>6,'min'=>0,'max'=>35,'prize'=>$config['goods035'],'v'=>$config['chance035'],'j'=>$config['coin035']),
				'6' => array('id'=>7,'min'=>145,'max'=>179,'prize'=>$config['goods145179'],'v'=>$config['chance145179'],'j'=>$config['coin145179']),
				'7' => array('id'=>8,'min'=>109,'max'=>143,'prize'=>$config['goods109143'],'v'=>$config['chance109143'],'j'=>$config['coin109143']),
				'8' => array('id'=>9,'min'=>217,'max'=>251,'prize'=>$config['goods217251'],'v'=>$config['chance217251'],'j'=>$config['coin217251']),
				'9' => array('id'=>10,'min'=>325,'max'=>359,'prize'=>$config['goods325359'],'v'=>$config['chance325359'],'j'=>$config['coin325359'])
			);
			$arr = $money = array();
			foreach ($prize_arr as $key => $val) {
				$arr[$val['id']] = $val['v'];
				if ($val['j'] > 0) array_push($money, $val['id']);
			}
			$rid = $this->get_rand($arr);
			$res = $prize_arr[$rid - 1];
			$min = $res['min'];
			$max = $res['max'];
			$result['angle'] = mt_rand($min, $max);
			$result['prize'] = $res['prize'];
			$this->db->transaction('begin');
			try {
				$sql = "UPDATE `{$this->db_prefix}members` SET `score`=`score`-{$score} where uid={$uid} LIMIT 1";
				if (!$this->db->query($sql, 0)) throw new Exception('数据库查询失败，请重试');
				$this->user['score'] -= $score;
				$_SESSION[$this->user_session] = serialize($this->user);
				if (in_array($rid, $money)) {
					$this->set_coin(array(
						'uid' => $this->user['uid'],
						'coin' => $res['j'],
						'liqType' => 120,
						'extfield0' => 0,
						'extfield1' => 0,
						'info' => '大转盘奖金'
					));
					$para = array(
						'uid' => $this->user['uid'],
						'info' => $res['prize'],
						'swapTime' => $this->time,
						'swapIp' => $this->ip(true),
						'coin' => $res['j'],
						'score' => $this->user['score']-$score,
						'xscore' => $score,
					);
					if (!$this->db->insert($this->db_prefix.'dzp_swap', $para)) throw new Exception('数据库更新失败，请重试');
				} else if ($rid == 8) {
					$sql = "UPDATE `{$this->db_prefix}members` SET `score`=`score`+{$score} WHERE `uid`={$uid} LIMIT 1";
					if (!$this->db->query($sql, 0)) throw new Exception('数据库更新失败，请重试');
				}
				$this->db->transaction('commit');
			} catch(Exception $e) {
				$this->db->transaction('rollBack');
				$result['angle'] = 0;
				$result['prize'] = $e->getMessage();
			}
		}
		echo json_encode($result);
	}
	
    private function get_rand($proArr) {
        $result = '';
        $proSum = array_sum($proArr);
        foreach ($proArr as $key => $proCur) {
            $randNum = mt_rand(4, $proSum);
            if ($randNum <= $proCur) {
               $result = $key;
               break;
            } else {
				$proSum -= $proCur;
			}
		}
		unset($proArr);
		return $result;
    }
	
	private function getdzpSettings(){
		$sql = "SELECT * from `{$this->db_prefix}dzpparams`";
		if ($data = $this->db->query($sql, 3)) {
			foreach ($data as $var) {
				$this->dzpsettings[$var['name']] = $var['value'];
			}
		}
	}

}