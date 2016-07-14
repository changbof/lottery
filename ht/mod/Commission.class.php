<?php
class Commission extends AdminBase{
	public $pageSize=15;

	public final function conCommissionList(){
		$this->display('commission/con-list.php');
	}

	public final function lossCommissionList(){
		$this->display('commission/loss-list.php');
	}

	public final function updateCommStatus(){
		$updateNames = array('conCommStatus_update', 'lossCommStatus_update');
		if(!isset($_GET['commStatusName'])) throw new Exception('参数出错');
		$commStatusName = $_GET['commStatusName'];
		$updateName = $commStatusName.'_update';
		if (!in_array($updateName, $updateNames)) throw new Exception('非法请求');
		$updateValue = $this->settings[$updateName];
		if ($updateValue + 86400 > time()) throw new Exception('您今天已经更新过发放状态，请明天再次更新');
		$sql = "update {$this->prename}members set $commStatusName=0";
		$this->update($sql);
		$today = strtotime('today');
		$sql = "UPDATE {$this->prename}params SET value='$today' WHERE name='$updateName' LIMIT 1";
		$this->update($sql);
		$systemCacheFilename = $this->cacheDir.'systemSettings';
		if (is_file($systemCacheFilename)) unlink($systemCacheFilename);
		echo '更新发放状态成功！！！';
	}
	public final function conComSingle($uid){
		if(!$uid=intval($uid)) throw new Exception('参数出错');

		$yesterday = date("Y-m-d",strtotime("-1 day"));
		$fromTime = strtotime($yesterday.' 00:00:00');
		$toTime = strtotime($yesterday.' 23:59:59');
		//$toTime = time();
		// 加载系统设置
		// and betAmount > ".floatval($this->settings['conCommissionBase1'])."
		$this->getSystemSettings();
		//echo floatval($this->settings['conCommissionBase1']);
		//exit;
		$sql="select u.username, u.coin, u.uid, u.type, u.parentId, sum(b.mode * b.beiShu * b.actionNum) betAmount from lottery_members u left join lottery_bets b on u.uid=b.uid and b.isDelete=0 and b.actionTime between $fromTime and $toTime where 1 and u.uid={$uid} group by u.uid";
		$res = $this->getRows($sql);
		$userbets = $res[0];
		$betAmount = $userbets['betAmount'];
		$this->beginTransaction();
		try{
			$conCommissionBase = array(
				'value' => floatval($this->settings['conCommissionBase']),
				'parent' => floatval($this->settings['conCommissionParentAmount']),
				'top' => floatval($this->settings['conCommissionParentAmount2']),
			);
			$conCommissionBase2 = array(
				'value' => floatval($this->settings['conCommissionBase2']),
				'parent' => floatval($this->settings['conCommissionParentAmount3']),
				'top' => floatval($this->settings['conCommissionParentAmount4']),
			);
			if ($conCommissionBase['value'] > $conCommissionBase2['value']) {
				$conBig = $conCommissionBase;
				$conSmall = $conCommissionBase2;
			} else {
				$conBig = $conCommissionBase2;
				$conSmall = $conCommissionBase;
			}
			$rebateValue = 0;
			$rebateParent = 0;
			$rebateTop = 0;
			if ($betAmount > $conSmall['value'] && $betAmount <= $conBig['value']) {
				$rebateValue = $conSmall['value'];
				$rebateParent = $conSmall['parent'];
				$rebateTop = $conSmall['top'];
			} else if ($betAmount > $conBig['value']) {
				$rebateValue = $conBig['value'];
				$rebateParent = $conBig['parent'];
				$rebateTop = $conBig['top'];
			}
			if ($rebateValue && ($rebateParent || $rebateTop)) {
				$log = array(
					'liqType' => 53,
					'extfield0' => $uid,
				);
				if ($parentId = $userbets['parentId']) {
					if ($rebateParent) {
						$log['coin'] = $rebateParent;
						$log['uid'] = $parentId;
						$log['info'] = '下级['.$userbets['username'].']消费佣金';
						$log['extfield1'] = $userbets['username'];
						$this->addCoin($log);
						$sql="select username from {$this->prename}members where `uid`=?";
						$parentName = $this->getValue($sql, $parentId);
						$this->addLog(20, $this->adminLogType[20].'['.$parentName.'<='.$userbets['username'].']', $uid, $userbets['username']);
					}
					$sql="select parentId,username from {$this->prename}members where `uid`=?";
					$res=$this->getRows($sql, $parentId);
					$parent = $res[0];
					if($parentId = $parent['parentId']){
						if($rebateTop){
							$log['coin'] = $rebateTop;
							$log['uid'] = $parentId;
							$log['info'] = '下级['.$parent['username'].'<='.$userbets['username'].']消费佣金';
							$log['extfield1'] = $parent['username'].'<='.$userbets['username'];
							$this->addCoin($log);
							$sql="select username from {$this->prename}members where `uid`=?";
							$parentName = $this->getValue($sql, $parentId);
							$this->addLog(20, $this->adminLogType[20].'['.$parentName.'<='.$parent['username'].'<='.$userbets['username'].']', $uid, $userbets['username']);
						}
					}
				}
			}
			$sql="update {$this->prename}members set conCommStatus=1 where uid=$uid";
			if($this->update($sql)){
				$this->commit();
				echo "消费佣金发放成功";
			}
		}catch(Exception $e){
			$this->rollBack();
			throw $e;
		}
	}
		

	public final function lossComSingle($uid){
		if(!$uid=intval($uid)) throw new Exception('参数出错');

		$yesterday = date("Y-m-d",strtotime("-1 day"));
		$fromTime = strtotime($yesterday.' 00:00:00');
		$toTime = strtotime($yesterday.' 23:59:59');
		//$toTime = time();
		// 加载系统设置
		// and betAmount > ".floatval($this->settings['conCommissionBase1'])."
		$this->getSystemSettings();
		//echo floatval($this->settings['conCommissionBase1']);
		//exit;
		$sql="select u.username, u.coin, u.uid, u.type, u.parentId, sum(b.mode * b.beiShu * b.actionNum) betAmount, sum(b.bonus) zjAmount, (select sum(coin) from lottery_coin_log l where l.`uid`=u.`uid` and liqType in(2,3) and l.actionTime between $fromTime and $toTime) fanDianAmount from lottery_members u left join lottery_bets b on u.uid=b.uid and b.isDelete=0 and b.actionTime between $fromTime and $toTime where 1 and u.uid={$uid} group by u.uid";
		$res = $this->getRows($sql);
		$userloss = $res[0];

		$sql2="select sum(coin) from {$this->prename}coin_log where uid=? and liqType in(50,51,52,53,56) and l.actionTime between $fromTime and $toTime";
		$userloss['brokerageAmount'] = $this->getValue($sql, $uid);

		$lossAmount = $userloss['zjAmount'] - $userloss['betAmount'] + $userloss['fanDianAmount'] + $userloss['brokerageAmount'];
		//var_dump($lossAmount);
		//exit;
		$this->beginTransaction();
		try{
			$lossCommissionBase = array(
				'value' => floatval($this->settings['lossCommissionBase']),
				'parent' => floatval($this->settings['lossCommissionParentAmount']),
				'top' => floatval($this->settings['lossCommissionParentAmount2']),
			);
			$lossCommissionBase2 = array(
				'value' => floatval($this->settings['lossCommissionBase2']),
				'parent' => floatval($this->settings['lossCommissionParentAmount3']),
				'top' => floatval($this->settings['lossCommissionParentAmount4']),
			);
			if ($lossCommissionBase['value'] > $lossCommissionBase2['value']) {
				$lossBig = $lossCommissionBase;
				$lossSmall = $lossCommissionBase2;
			} else {
				$lossBig = $lossCommissionBase2;
				$lossSmall = $lossCommissionBase;
			}
			$rebateValue = 0;
			$rebateParent = 0;
			$rebateTop = 0;
			if (abs($lossAmount) > $lossSmall['value'] && abs($lossAmount) <= $lossBig['value']) {
				$rebateValue = $lossSmall['value'];
				$rebateParent = $lossSmall['parent'];
				$rebateTop = $lossSmall['top'];
			} else if (abs($lossAmount) > $lossBig['value']) {
				$rebateValue = $lossBig['value'];
				$rebateParent = $lossBig['parent'];
				$rebateTop = $lossBig['top'];
			}
			if ($rebateValue && ($rebateParent || $rebateTop)) {
				$log = array(
					'liqType' => 56,
					'extfield0' => $uid
				);
				if($parentId = $userloss['parentId']) {
					if($rebateParent) {
						$log['coin'] = $rebateParent;
						$log['uid'] = $parentId;
						$log['info'] = '下级['.$userloss['username'].']亏损佣金';
						$log['extfield1'] = $userloss['username'];
						$this->addCoin($log);
						$sql="select username from {$this->prename}members where `uid`=?";
						$parentName = $this->getValue($sql, $parentId);
						$this->addLog(21, $this->adminLogType[21].'['.$parentName.'<='.$userloss['username'].']', $uid, $userloss['username']);
					}
					$sql = "select parentId,username from {$this->prename}members where `uid`=?";
					$res = $this->getRows($sql, $parentId);
					$parent = $res[0];
					if($parentId = $parent['parentId']){
						if($rebateTop) {
							$log['coin'] = $rebateTop;
							$log['uid'] = $parentId;
							$log['info'] = '下级['.$parent['username'].'<='.$userloss['username'].']亏损佣金';
							$log['extfield1'] = $parent['username'].'<='.$userloss['username'];
							$this->addCoin($log);
							$sql="select username from {$this->prename}members where `uid`=?";
							$parentName = $this->getValue($sql, $parentId);
							$this->addLog(21, $this->adminLogType[21].'['.$parentName.'<='.$parent['username'].'<='.$userloss['username'].']', $uid, $userloss['username']);
						}
					}
				}
			}
			$sql="update {$this->prename}members set lossCommStatus=1 where uid=$uid";
			if($this->update($sql)){
				$this->commit();
				echo "亏损佣金发放成功";
			}
		}catch(Exception $e){
			$this->rollBack();
			throw $e;
		}
	}
}
?>
