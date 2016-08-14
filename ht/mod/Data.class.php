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
        $rst = array('code'=>'0','message'=>'试算失败,请检查输入格式是否正确.');
        $bjAmount = $zjAmount = 0;  // 投注金额,中奖金额
        //$this->getSystemSettings();
        $LiRunLv = $this->settings['LiRunLv']; // 利润率 默认2%
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
            $LiRun = round((1 - $zjAmount / $bjAmount)*100, 2);
            if(abs($LiRun - $LiRunLv)<=0.2){
                $rst['code'] = '1';
                $rst['message'] = '合适.系统设置利润为'.$LiRunLv.'%. 试算利润为'.$LiRun.'% ('.$zjAmount.'/'.$bjAmount.').' ;
            }else{
                $rst['code'] = '0';
                $rst['message'] = '不合适.系统设置利润为'.$LiRunLv.'%. 试算利润为'.$LiRun.'% ('.$zjAmount.'/'.$bjAmount.').' ;
            }

        } else { // 自动计算
            $sql = "select * from {$this->prename}budget where type={$type} and actionNo='{$number}' order by id desc limit 0,1";
            $budget = $this->getRow($sql);
            if($budget['status']!=0){ // 已试算过
                $rst = $this->recommendLotteryNo($type,$number);
            }else{ // 先试算,再取号
                $sql = "select id,lotteryNo from {$this->prename}budget where type={$type} and actionNo='{$number}' order by id ";
                if ($data = $this->getRows($sql)) {
                    foreach ($data as $var) {
                        $zjinfo = $this->getZjInfo($type,$number,$var['lotteryNo']);
                        $bjAmount = $zjinfo['bjAmount'];
                        $zjAmount = $zjinfo['zjAmount'];
                        // 开始计算利润
                        $LiRun = round((1 - $zjAmount / $bjAmount)*100, 2);
                        $this->setBudget($var['id'],$LiRun); // 更新利润到试算表中
                        // todo
                    }
                }
                $rst = $this->recommendLotteryNo($type,$number);
            }
        }
        return $rst;
    }

    /**
     * 试给一个开奖号码 来获取彩票类型当期所有玩法金额信息: 投注金额/中奖金额
     * @param $type
     * @param $number
     * @param $kjData  尝试给出的开奖号码
     * @return array bjAmount:投注金额/zjAmount:中奖金额
     * @throws Exception
     */
    public function getZjInfo($type,$number,$kjData){
        $zjInfo = array('bjAmount'=>0,'bjAmount'=>0);
        $bjAmount = $zjAmount = 0;  // 投注金额,中奖金额
        $sql = "select * from {$this->prename}bets where isDelete=0 and type={$type} and actionNo='{$number}'";
        if ($data = $this->getRows($sql)) {
            $this->getPlayeds();
            foreach ($data as $var) {
                // 获取该玩法的算法函数
                if(($fun=$this->playeds[$var['playedId']]['ruleFun']) && method_exists($this,$fun)){
                    try {
                        $zjCount = $this->$fun($var['actionData'], $kjData); // 中奖注数
                        $bjAmount += floor($var['actionNum']) * $var['mode'] * floor($var['beiShu']); //投注金额:投注注数* 模式 * 倍数
                        $zjAmount += $var['bonusProp'] * floor($zjCount) * floor($var['beiShu']) * ($var['mode'] / 2); //中奖金额: 奖金比例(赔率) * 中奖注数 * 倍数 * (模式/2)

                    } catch (Exception $e) {
                        throw new Exception('计算中奖号码时出错: ' . $e);
                    }
                } else {
                    throw new Exception('算法不是可用的函数');
                    continue;
                }
            }
            $zjInfo['bjAmount']=$bjAmount;
            $zjInfo['zjAmount']=$zjAmount;
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
            return '1';
        }else{
            return '-1';
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
               "  where actionNo='{$number}' and type={$type}  and status<>0 order by l asc limit 0,3 " .
               ") as t group by actionNo,type ";
//        print_r($sql);
        if ($data = $this->getRow($sql)) {
            return array('code' => '1', 'message' => '推荐号码:' . $data['profits']);
        } else {
            return array('code' => '-1', 'message' => '计算中奖号码时出错.');
        }
    }

    //==================================================================================
    /**
     * 功能:获得投注数据中开奖号码的中奖注数
     * 算法模型
     *　function func(betData, kjData, betWeiShu)
     */

    /** K3和值
     * @param $betData
     * @param $kjData
     * @return int
     */
    public function k3hz($betData, $kjData){
        $kjData = explode(',',$kjData);
        $hz = array_sum($kjData);
        $reg = '|('.$hz.')|';
        preg_match($reg,$betData,$r);
        return count($r);
    }

    // K3三同号单选
    public function k33dx($betData, $kjData){
        $kjData = str_replace(',','',$kjData);
        return stripos($betData, $kjData)!== false ? 1 : 0;
    }

    // K3三同号通选
    public function k33tx($betData, $kjData){
        return self::k33dx($betData, $kjData);
    }

    // K3三连号通选
    public function k33ltx($betData, $kjData){
        return self::k33dx($betData, $kjData);
    }

    // K3三不同号
    public function k33x($betData, $kjData){
        return self::zx($betData, $kjData);
    }

    // K3二不同号
    public function k32x($betData, $kjData){
        return self::k33x($betData, $kjData);
    }

    // K3二同号复选
    public function k32fx($betData, $kjData){
        $strkj = 'k32fx';
        $betData = preg_replace('/\*\s?/','',$betData);

        $data = explode(',',$kjData);
        sort($data);
        $r = preg_match('/(?<k1>[\d])\k<k1>{1}/',join('',$data),$match);
        if($r)
            $strkj = $match[0];

        return stripos($betData,$strkj)!== false ? 1 : 0;
    }

    // K3二同号单选
    public function k32dx($betData, $kjData){
        $count = 0;
        $data = explode(',',$kjData);
        sort($data);
        $data = join('',$data);
        $r = preg_match('/(?<k1>[\d])\k<k1>{1}/',$data,$match);
        if(!$r) return 0;

        $kjdb = $match[0];
        $kjt = trim($data,$kjdb);
        $betData = explode(',',$betData);

        if (strpos($betData[0],$kjdb)!== false && strpos($betData[1],$kjt)!== false) $count=1;

        return $count;

    }

    /**
     * 常用算法
     */


    /** 组合
     * @param $a                备选数组
     * @param $m                选取的个数
     * @return array            组合
     */

    function combination($a, $m) {
        $r = array();

        $n = count($a);
        if ($m <= 0 || $m > $n) {
            return $r;
        }

        for ($i=0; $i<$n; $i++) {
            $t = array($a[$i]);
            if ($m == 1) {
                $r[] = $t;
            } else {
                $b = array_slice($a, $i+1);
                $c = combination($b, $m-1);
                foreach ($c as $v) {
                    $r[] = array_merge($t, $v);
                }
            }
        }

        return $r;
    }

    public function combina($ar, $n){
        $c = count($ar);
        if ($n>$c) return false; // parameter wrong
        if ($c>50) return false; // too big array :)

        $r = array();

        $code = "";
        $list = array();
        for($i=1;$i<=$n;$i++){
            $list[] = '$v'.$i;
            $code .= 'foreach($ar as $k'.$i.'=>$v'.$i.'){';
            if($i!=$n) $code .= 'unset($ar[$k'.$i.']);';
        }
        $code .= '$t = array('.join(',',$list).');';
        $code .= 'sort($t);';
        $code .= '$r[] = join(",",$t);';
        for($i=$n-1;$i>0;$i--){
            $code .= '}$ar[$k'.$i.']=$v'.$i.';';
        }
        $code .= '}';
        eval($code);
        return array_values(array_unique($r));
    }

    /**
     * 组合算法
     * @param array $elements    备选数组
     * @param $chosen            选取的个数
     * @return array             组合
     */
    function array_combination(array $elements, $chosen)
    {
        $result = array();

        for ($i = 0; $i < $chosen;   $i++) { $vecm[$i] = $i; }
        for ($i = 0; $i < $chosen-1; $i++) { $vecb[$i] = $i; }
        $vecb[$chosen - 1] = count($elements) - 1;
        $result[] = $vecm;

        $mark = $chosen - 1;
        while (true) {
            if ($mark == 0) {
                $vecm[0]++;
                $result[] = $vecm;
                if ($vecm[0] == $vecb[0]) {
                    for ($i = 1; $i < $chosen; $i++) {
                        if ($vecm[$i] < $vecb[$i]) {
                            $mark = $i;
                            break;
                        }
                    }
                    if (($i == $chosen) && ($vecm[$chosen - 1] == $vecb[$chosen - 1])) { break; }
                }
            } else {
                $vecm[$mark]++;
                $mark--;
                for ($i = 0; $i <= $mark; $i++) {
                    $vecb[$i] = $vecm[$i] = $i;
                }
                $vecb[$mark] = $vecm[$mark + 1] - 1;
                $result[] = $vecm;
            }
        }

        return $result;
    }
    /**
     * 常用算法：zx
     *
     * @params $bet		投注列表：1 2 3 4 5 6
     * @params $data    开奖所需的号码：4,5,2
     *
     * @return int		返回中奖注数
     */
    public function zx($bet, $data){
        $bet = explode(' ',$bet);
        $data = explode(',',$data);
        sort($data);
        $strData = implode(',',$data);
        return count( array_filter( self::combina($bet,count($data)), function($v) use($strData){return !!($v == $strData);} ) );
    }

    //==================================================================================
}
