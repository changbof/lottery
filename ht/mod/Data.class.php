<?php

/**
 * 与开奖数据有关
 */
class Data extends AdminBase{
	public $pageSize=15;
	private $encrypt_key='lottery_running';
	private $dataPort=65531;
	
	public final function index($type){
		$this->type=$type;
		$this->display('data/index.php');
	}
	
	public final function add($type, $actionNo, $actionTime){
		$para=array(
			'type'=>$type,
			'actionNo'=>$actionNo,
			'actionTime'=>$actionTime
		);
		$this->display('data/add-modal.php', 0, $para);
	}
	
	public final function back($type, $actionNo, $actionTime){
		$para=array(
			'type'=>$type,
			'actionNo'=>$actionNo,
			'actionTime'=>$actionTime
		);
		$this->display('data/back-modal.php', 0, $para);
	}
	
	public final function backed(){		
	    $para=$_POST;		
		$type = intval($para['type']);
		$number = $para['number'];
		$sql="select * from {$this->prename}bets where type={$type} and actionNo='{$number}'";		
		if($data=$this->getRows($sql)){
			foreach($data as $var){
				$c=intval($var['actionNum'])*$var['mode']*intval($var['beiShu']);
				$this->update("update {$this->prename}members set coin=coin+{$c} where username='{$var['username']}'");
				$this->delete("delete from {$this->prename}bets where id={$var['id']}");
				
				$mm=$this->getRow("select * from {$this->prename}members where username='{$var['username']}'");				
				/*$pp=array(
					'uid'=>$var['uid'],
					'type'=>$var['type'],
					'playedId'=>$var['playedId'],					
					'coin'=>$c,
					'userCoin'=>$mm['coin'],
					'fcoin'=>0,
					'liqType'=>0,
					'actionUID'=>0,					
					'actionTime'=>UNIX_TIMESTAMP(),
					'actionIP'=>0,
					'info'=>$number.'期未开奖退款',
					'extfield0'=>0,
					'extfield1'=>'0',
					'extfield2'=>'0'
				);
				$this->insertRow($this->prename .'coin_log', $pp);*/
				
				$inserts = "insert into lottery_coin_log (uid,type,playedId,coin,userCoin,fcoin,liqType,actionUID,actionTime,actionIP,info,extfield0,extfield1) values ('".$var['uid']."',".$var['type'].",".$var['playedId'].",'".$c."','".$mm['coin']."',0,255,0,UNIX_TIMESTAMP(),'0','".$number."期未开奖退款"."','".$var['wjorderId']."','".$var['uid']."')";
				$this->query($inserts);
				
				
			}
			//echo '退款成功';
		}
	}

    public final function updatedata($type, $actionNo, $actionTime){
		$para=array(
			'type'=>$type,
			'actionNo'=>$actionNo,
			'actionTime'=>$actionTime
		);
		$this->display('data/update-modal.php', 0, $para);
	}
	
	public final function kj(){
		$para=$_GET;
		$para['key']=$this->encrypt_key;
		$url=$GLOBALS['conf']['node']['access'] . '/data/kj';
		echo $this->http_post($url, $para);
	}

	public final function added(){
		$para=$_POST;
		$para['type']=intval($para['type']);
		$para['key']=$this->encrypt_key;
        print_r($para);
		$url=$GLOBALS['conf']['node']['access'] . '/data/add';
		if(!$this->getValue("select data from {$this->prename}data where type={$para['type']} and number='{$para['number']}'")) $this->addLog(17,$this->adminLogType[17].'['.$para['data'].']', 0, $this->getValue("select shortName from {$this->prename}type where id=?",$para['type']).'[期号:'.$para['number'].']');

        echo $this->http_post($url, $para);
	}

	public final function updatedataed(){
		$id=intval($_POST['id']);
		$para['data']=$_POST['data'];
		$sql="update {$this->prename}data set data='{$para['data']}' where id={$id}";

		if($this->update($sql)){
			echo '修改成功';
		}
	}
	
	public function http_post($url, $data) {
		$data_url = http_build_query ($data);
		$data_len = strlen ($data_url);
	
		return file_get_contents ($url, false, stream_context_create (array ('http'=>array ('method'=>'POST'
				, 'header'=>"Connection: close\r\nContent-Length: $data_len\r\n"
				, 'content'=>$data_url
				))));
	}

    /**
     * 利润试算(暂只支持快3) todo
     *
     * 提交过来的参数:
     *     type - 投注种类，对应lottery_type.id
     *     number - 投注期号
     */
    public final function budget_lirun(){

        $bjAmount = $zjAmount = 0;  // 投注金额,中奖金额

        $LiRunLv = $this->settings['LiRunLv'] || 2 ; // 利润率 默认2%
        $para=$_POST;
        $type = intval($para['type']);
        $number = $para['number'];
        $KjData = $para['data'];  // 开奖号码,如'1,2,3'
        $budgetMode = $para['budget']; // 试算模式, 0-自动 1-手动输入号码

        if($budgetMode==1) { //手动计算
            $zjinfo = $this->getZjInfo($type,$number,$KjData);
            $bjAmount = $zjinfo['bjAmount'];
            $zjAmount = $zjinfo['zjAmount'];
            // 开始计算利润
            $LiRun = round(($zjAmount * 100) / $bjAmount, 2);
            if(abs($LiRun - $LiRunLv)>=0.2){
                return array('code'=>1,'msg'=>'利润符合系统设置值. 试算利润为'.$LiRun);
            }else{
                return array('code'=>0,'msg'=>'利润少于系统设置值. 试算利润为'.$LiRun);
            }

        } else { // 自动计算
            $sql = "select * from {$this->prename}budget where type={$type} and actionNo='{$number}' order by id desc limit 0,1";
            $budget = $this->getRow($sql);
            if($budget['status']!=0){ // 已试算过
                return $this->recommendLotteryNo($type,$number);
            }else{ // 先试算,再取号
                $sql = "select id,lotteryNo from {$this->prename}budget where type={$type} and actionNo='{$number}' order by id ";
                if ($data = $this->getRows($sql)) {
                    foreach ($data as $var) {
                        $zjinfo = $this->getZjInfo($type,$number,$var['lotteryNo']);
                        $bjAmount = $zjinfo['bjAmount'];
                        $zjAmount = $zjinfo['zjAmount'];
                        // 开始计算利润
                        $LiRun = round(($zjAmount * 100) / $bjAmount, 2);
                        $this->setBudget($var['id'],$LiRun); // 更新利润到试算表中
                        // todo
                    }
                }
                return $this->recommendLotteryNo($type,$number);
            }
        }
    }

    /**
     * 试给一个开奖号码 来获取彩票类型当期所有玩法金额信息: 投注金额/中奖金额
     * @param $type
     * @param $number
     * @param $kjData  尝试给出的开奖号码
     * @return array bjAmount:投注金额/zjAmount:中奖金额
     * @throws Exception
     */
    public final function getZjInfo($type,$number,$kjData){
        $zjInfo = Array();
        $bjAmount = $zjAmount = 0;  // 投注金额,中奖金额
        $sql = "select * from {$this->prename}bets where type={$type} and actionNo='{$number}'";
        if ($data = $this->getRows($sql)) {
            foreach ($data as $var) {
                // 获取该玩法的算法函数
                $playeds_func = null;
                $playeds_func = $this->playeds[$var['playedId']]['ruleFun'];
                if (!function_exists($playeds_func)) throw new Exception('算法不是可用的函数');

                try {
                    $zjCount = $this->$playeds_func($var['actionData'], $kjData) || 0; // 中奖注数
                    $bjAmount += floor($var['actionNum']) * $var['mode'] * floor($var['beiShu']); //投注金额:投注注数* 模式 * 倍数
                    $zjAmount += $var['bonusProp'] * floor($zjCount) * floor($var['beiShu']) * ($var['mode'] / 2); //中奖金额: 奖金比例(赔率) * 中奖注数 * 倍数 * (模式/2)
                } catch (Exception $e) {
                    throw new Exception('计算中奖号码时出错: ' . $e);
                }
            }
            $zjInfo = Array(
                'bjAmount'=>$bjAmount,
                'zjAmount'=>$zjAmount
            );
        }
        return $zjInfo;
    }

    /**
     * 开奖利润试算 设置利润值
     * @param $id
     * @param $lirun
     * @return int >0 成功; <0 失败
     */
    public final function setBudget($id,$lirun){
        if(!$id=intval($id)) return -2;
        $sql="update {$this->prename}budget set tryProfits=? enable where id=?";
        if($this->update($sql,$lirun,$id)){
            $this->addLog(17,$this->adminLogType[17].'[操作ID:'.$id.']',$id,'开奖利润试算');
            return 1;
        }else{
            return -1;
        }
    }

    /**
     * 自动试算开奖号码,推荐最接近系统利润率的3个号码
     * @param $type
     * @param $number
     * @return array  如:3,5,6(2.01),1,3,5(2.05),2,5,6(2.03) (括号里代表利润,单位%)
     */
    public final function recommendLotteryNo($type,$number){
        $sql = "select actionNo,type,group_concat(l) as profits from " .
               "( select actionNo,type,CONCAT(lotteryNo,'(',tryProfits,')') as l from {$this->prename}budget " .
               "  where actionNo='{$number}' and type={$type}  and status<>0 order by lr asc limit 0,3 " .
               ") as t group by actionNo,type ";
        if ($data = $this->getRow($sql)) {
            return array('code' => 1, 'msg' => '推荐号码:' . $data['profits']);
        } else {
            return array('code' => -1, 'msg' => '计算中奖号码时出错');
        }
    }
}
