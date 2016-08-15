<?php

class lib_game {

	private $db; // 数据库连接
	private $db_prefix; // 数据库表前缀
	private $time; // 当前时间
	private $types = array(); // 彩种列表
	private $ftimes = array(); // 彩种延迟时间列表
	
	public function __construct() {
		$this->db = core::lib('db');
		$this->db_prefix = DB_PREFIX;
		$this->time = time();
	}
	
	// 获取彩种列表
	public function get_types() {
		if($this->types) return $this->types;
		$sql = "SELECT * FROM `{$this->db_prefix}type` WHERE `isDelete`=0 ORDER BY `sort` ASC";
		$data = $this->db->query($sql, 3);
		foreach ($data as $v) $this->types[$v['id']] = $v;
		return $this->types;
	}
	
	 //获取当期时间
	public function get_game_current_time($type_id, $old = 0) {
		$current = $this->get_game_no($type_id);
		if ($type_id == 1 && $current['actionTime'] == '00:00') {
			$actionTime = strtotime($current['actionTime']) + 24 * 3600;
		} else {
			$actionTime = strtotime($current['actionTime']);
		}
		if (!$actionTime) $actionTime = $old;
		return $actionTime;
	}
	
    /** 获取延迟时间(开奖前停止下注时间)
     * @param $type_id int 彩种ID
     * @return mixed
     */
	public function get_type_ftime($type_id) {
		if (!array_key_exists($type_id, $this->ftimes)) {
			$ftime = $this->db->query("SELECT `data_ftime` FROM `{$this->db_prefix}type` WHERE `id`=$type_id LIMIT 1", 2);
			$ftime = $ftime ? $ftime['data_ftime'] : 0;
			$this->ftimes[$type_id] = $ftime;
		}
		return $this->ftimes[$type_id];
	}

    /**
     * @name 期号格式化 流水号若有两个"0",则替换为一个"0"
     * @param $no
     * @return mixed
     */
	private function no_format($no) {
		$no = str_replace('-', '', $no);
		//$no = preg_replace('/[0]{2,}(\d{1,})$/', '0$1', $no);  // modify by aboooo at 20160815
		return $no;
	}

    /**
     * @name 读取下期期号
     * @param int type_id 彩种ID
     * @param int time 时间，默认为当前时间
     * @return PDOStatement
     */
	public function get_game_no($type_id, $time=null){
		$type_id = intval($type_id);
		if($time===null) $time = $this->time;
		$ftime = $this->get_type_ftime($type_id); // 取得玩法开奖前停止下注时间,如30秒
		$action_time = date('H:i:s', $time + $ftime);
		
		$sql = "SELECT `actionNo`,`actionTime` FROM `{$this->db_prefix}data_time` WHERE `type`=$type_id AND `actionTime`>'$action_time' ORDER BY `actionTime` LIMIT 1";
		$result = $this->db->query($sql, 2);

        // 若当前是一天中的最后一期,则查询不到下期期号,则取第二天的第一期期号,时间加上一天:24*3600
		if(!$result){
			$sql = "SELECT `actionNo`,`actionTime` FROM `{$this->db_prefix}data_time` WHERE `type`=$type_id ORDER BY `actionTime` LIMIT 1";
			$result = $this->db->query($sql, 2);
			$time = $time + 24 * 3600;  // 这个时间有点问题?
		}
		
		$types = $this->get_types();
		if(($func = $types[$type_id]['onGetNoed']) && method_exists($this, $func)) {
			$this->$func($result['actionNo'], $result['actionTime'], $time);
		}
		
		$result['actionNo'] = $this->no_format($result['actionNo']);
		return $result;
	}
	
	/**
	 * @name 读取上期(当前期)期号
	 * @param int type_id 彩种ID
	 * @param int time 时间，默认为当前时间
	 */
	public function get_game_last_no($type_id, $time = null) {
		$type_id = intval($type_id);
		if($time===null) $time = $this->time;
		$ftime = $this->get_type_ftime($type_id);
		$action_time = date('H:i:s', $time + $ftime);
		
		$sql = "SELECT `actionNo`,`actionTime` FROM `{$this->db_prefix}data_time` WHERE `type`=$type_id AND `actionTime`<='$action_time' ORDER BY `actionTime` DESC LIMIT 1";
		$result = $this->db->query($sql, 2);

        // 取上一天最后一期期号与开奖时间
		if (!$result) {
			$sql = "SELECT `actionNo`,`actionTime` FROM `{$this->db_prefix}data_time` WHERE `type`=$type_id ORDER BY `actionNo` DESC LIMIT 1";
			$result = $this->db->query($sql, 2);
			$time = $time - 24*3600;
		}
		
		$types = $this->get_types();
		if (($func = $types[$type_id]['onGetNoed']) && method_exists($this, $func)) {
			$this->$func($result['actionNo'], $result['actionTime'], $time);
		}
		
		$result['actionNo'] = $this->no_format($result['actionNo']);
		return $result;
	}
	
	// 获取近期期号(往前期)
	public function get_game_recent_no($type_id, $num) {
		$type_id = intval($type_id);
		$time = $this->time;
		$ftime = $this->get_type_ftime($type_id);
		$action_time = date('H:i:s', $time + $ftime);
		
		$where = "WHERE `type`=$type_id AND `actionTime`<='$action_time'";
		$data = $this->db->query("SELECT COUNT(1) AS `__total` FROM `{$this->db_prefix}data_time` $where", 2);
		$total = $data['__total'] ? $data['__total'] : 1;
		$skip = $total > $num ? $num : $total - 1;
		$sql = "SELECT `actionNo`,`actionTime` FROM `{$this->db_prefix}data_time` $where ORDER BY `actionTime` DESC LIMIT $skip,1";
		$result = $this->db->query($sql, 2);
		
		if (!$result) {
			$sql = "SELECT `actionNo`,`actionTime` FROM `{$this->db_prefix}data_time` WHERE `type`=$type_id ORDER BY `actionNo` DESC LIMIT 1";
			$result = $this->db->query($sql, 2);
			$time = $time - 24*3600;
		}
		
		$types = $this->get_types();
		if (($func = $types[$type_id]['onGetNoed']) && method_exists($this, $func)) {
			$this->$func($result['actionNo'], $result['actionTime'], $time);
		}
		
		$result['actionNo'] = $this->no_format($result['actionNo']);
		return $result;
	}
	
	// 获取近期期号(往后期)
	public function get_game_next_nos($type_id, $num) {
		$type_id = intval($type_id);
		$time = $this->time;
		$ftime = $this->get_type_ftime($type_id);
		$action_time = date('H:i:s', $time + $ftime);
		
		$where = "WHERE `type`=$type_id AND `actionTime`>='$action_time'";
		$data = $this->db->query("SELECT COUNT(1) AS `__total` FROM `{$this->db_prefix}data_time` $where", 2);
		$total = $data['__total'] ? $data['__total'] : 1;
		$limit = $num ? ($total > $num ? $num : $total) : $total;
		$sql = "SELECT `actionNo`,`actionTime` FROM `{$this->db_prefix}data_time` $where ORDER BY `actionTime` ASC LIMIT {$limit}";
		$result = $this->db->query($sql, 3);
		
		if (!$result) {
			$sql = "SELECT `actionNo`,`actionTime` FROM `{$this->db_prefix}data_time` WHERE `type`=$type_id ORDER BY `actionNo` ASC LIMIT {$num}";
			$result = $this->db->query($sql, 3);
			$time = $time - 24*3600;
		}
		
		$types = $this->get_types();
		if (($func = $types[$type_id]['onGetNoed']) && method_exists($this, $func)) {
			foreach ($result as &$r) {
				$this->$func($r['actionNo'], $r['actionTime'], $time);
				$r['actionNo'] = $this->no_format($r['actionNo']);
			}
		}
		
		return $result;
	}

    /**
     * @param $actionTime 开奖时间
     * @param null $time  开奖时间对应期数所在的日期
     */
	private function setTimeNo(&$actionTime, &$time=null) {
		if (!preg_match('/^(\d{2}\:){2}\d{2}$/', $actionTime)) core::error('开奖时间表中时间数据错误');
		if(!$time) $time = $this->time;
		$actionTime = date('Y-m-d ', $time).$actionTime;
	}
	
	private function noHdCQSSC(&$actionNo, &$actionTime, $time=null) {
		if (!is_numeric($actionNo)) core::error('开奖时间表中期号数据错误');
		$this->setTimeNo($actionTime, $time);
		if ($actionNo === 0 || $actionNo === 120){
			$actionNo = date('Ymd120', $time - 24 * 3600);
			$actionTime = date('Y-m-d 00:00', $time);
		} else {
			$actionNo = date('Ymd', $time).substr(1000 + $actionNo, 1);
		}
	}
	
	private function no0Hd(&$actionNo, &$actionTime, $time=null) {
//		$this->setTimeNo($actionTime, $time);
//		$no = substr(1000 + $actionNo, 1);
//		if (substr($no, 0, 1) === '0') $no = substr($no, 1);
//		$actionNo = date('Ymd', $time).$no;
        // 与后台保持一致 Modify by aboooo
        $this->setTimeNo($actionTime, $time);
        $actionNo=date('Ymd', $time).substr(1000+$actionNo,1);

	}
	
	private function no0Hd_1(&$actionNo, &$actionTime, $time=null) {
		$this->setTimeNo($actionTime, $time);
		$actionNo = date('Ymd', $time).substr(100 + $actionNo, 1);
	}
	
	private function no0Hd_2(&$actionNo, &$actionTime, $time=null) {
		$this->setTimeNo($actionTime, $time);
		$actionNo = date('Ymd', $time).substr(1000 + $actionNo, 1);
	}
	
	private function no0Hd_3(&$actionNo, &$actionTime, $time=null) {
		$this->setTimeNo($actionTime, $time);
		$actionNo = date('ymd', $time).substr(100 + $actionNo, 1);
	}
	
	private function pai3(&$actionNo, &$actionTime, $time=null) {
		$this->setTimeNo($actionTime, $time);
		$actionNo = date('Yz', $time) - 7;
		$actionNo = substr($actionNo, 0, 4).substr(substr($actionNo, 4) + 1001, 1);
		if ($actionTime < date('Y-m-d H:i:s', $time)) $actionTime = date('Y-m-d 18:30', $time);
	}
	
	private function pai3x(&$actionNo, &$actionTime, $time=null) {
		$this->setTimeNo($actionTime, $time);
		$actionNo = date('Yz', $time) - 7;
		$actionNo = substr($actionNo, 0, 4).substr(substr($actionNo, 4) + 1001, 1);
		if($actionTime < date('Y-m-d H:i:s', $time)) $actionTime = date('Y-m-d 20:30', $time);
	}
	
	private function noxHd(&$actionNo, &$actionTime, $time=null) {
		$this->setTimeNo($actionTime, $time);
		if ($actionNo > 84) $time -= 24 * 3600;
		$actionNo = date('Ymd', $time).substr(1000 + $actionNo, 1);
	}
	
	private function BJpk10(&$actionNo, &$actionTime, $time=null) {
		$this->setTimeNo($actionTime, $time);
		$actionNo = 179 * (strtotime(date('Y-m-d', $time)) - strtotime('2007-11-11')) / 3600 / 24 + $actionNo - 1267;
	}
	
	private function no0Hdx(&$actionNo, &$actionTime, $time=null) {
		$this->setTimeNo($actionTime, $time);
		$actionNo = date('Ymd', $time).substr(10000 + $actionNo, 1);
	}
	
	private function Kuai8(&$actionNo, &$actionTime, $time=null) {
		$this->setTimeNo($actionTime, $time);
		$actionNo = 179 * (strtotime(date('Y-m-d', $time)) - strtotime('2004-09-19')) / 3600 / 24 + $actionNo - 77 - 1253;
	}
	
	private function noHd(&$actionNo, &$actionTime, $time=null) {
		$this->setTimeNo($actionTime, $time);
		$actionNo = date('Ymd', $time).substr(100 + $actionNo, 1);
	}

}