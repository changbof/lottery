<?php
class Score extends AdminBase{
	public $pageSize=15;
	
	public final function settings(){
		$this->display('score/settings.php');
	}
	
	public final function updateSettings() {
		$sql="insert into {$this->prename}exchange_params (name, `value`) values";
		if(!$para=$_POST) throw new Exception('参数出错');
		if(!ctype_digit($para['score']) || $para['score']<0) throw new Exception('请正确输入积分!');
		if($para['switchWeb']!=0 && $para['switchWeb']!=1) throw new Exception('请正确设置积分兑换开关!');
		
		foreach($para as $key=>$var){
			$sql.="('$key', '$var'),";
		}
		$sql=rtrim($sql,',');
		$sql.=' on duplicate key update `value`=values(`value`)';
		
		if($this->insert($sql)){
			$this->addLog(16,$this->adminLogType[16]);
			$sql="select * from {$this->prename}exchange_params";
			$scoresettings = array();
			$data = $this->getRows($sql);
			foreach ($data as $v) $scoresettings[$v['name']] = $v['value'];
			return $scoresettings;
		}else{
			throw new Exception('未知错误');
		}
	}
	
	public final function pointList(){
		$this->display('score/point-list.php');
	}

}