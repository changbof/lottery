<?php
// [币币]支付接口
class pay_bi {

	// 银行代码
	private $banks = array(
		'3'  => '10014', // 广东发展银行
		'4'  => '10006',  // 华夏银行
		'5'  => '10016', // 交通银行
		'6'  => '10017', // 平安银行
		'7'  => '10012', // 上海浦东发展银行
		'8'  => '10002', // 兴业银行
		'9'  => '10011', // 中国邮政储蓄银行
		'10' => '10005', // 中国光大银行
		'11' => '10018', // 中国工商银行
		'12' => '10020', // 中国建设银行
		'13' => '10004', // 中国民生银行
		'14' => '10022', // 中国农业银行
		'15' => '10009', // 中国银行
		'16' => '10001', // 招商银行
		'17' => '10003', // 中信银行
	);
	
	function encrypts($parameter,$mkey) {
		ksort($parameter);
		reset($parameter);			
		$sign  = '';			
		foreach ($parameter AS $key => $val) {
			if ($val != '' && $key != 'p8_reply') {
				$sign  .= "$key=$val&";
			}
		}
		$sign  = $sign."p8_reply=".trim($_GET['p8_reply']).$mkey; 
		$sign  = md5($sign);	
		return $sign;
	}
	
	private function encrypt($parameter,$mkey) {
		ksort($parameter);
		reset($parameter);
		$sign  = '';
		foreach ($parameter AS $key => $val) {
			if($val!='') {
				$sign  .= "$key=$val&";
			}
		}
		$sign  = $sign."p10_reply=1&p11_mode=2&p12_ver=1";
		$sign  = $sign.$mkey;
		$sign  = md5($sign);
		return $sign;
	}

	/**
	 * @name 支付方法
	 * @param int bankid 银行ID
	 * @param int amount 充值金额
	 * @param string orderid 订单ID
	 * @param string url_callback 回调地址
	 * @param string url_return 充值完成后返回地址
	 */
	public function pay($bankid, $amount, $orderid, $url_callback, $url_return) {
		// 获取银行代码
		$type = $this->banks[$bankid];
		//获取输入参数
		$parameter = array(
			'p1_md' => 1,
			'p2_xn' => $orderid,
			'p3_bn' => PAY_ID,
			'p4_pd' => $type,
			'p5_name' => $this->user['uid'],
			'p6_amount' => sprintf("%.2f", $amount),
			'p7_cr' => 1,
			'p8_ex' => $this->user['uid'],
			'p9_url' => $url_callback,
			'p10_reply' => 1,
			'p11_mode' => 2,
			'p12_ver' => 1,
		);
		$parameters = array(
			'p1_md' => $parameter['p1_md'],
			'p2_xn' => $parameter['p2_xn'],
			'p3_bn' => $parameter['p3_bn'],
			'p4_pd' => $parameter['p4_pd'],
			'p5_name' => $parameter['p5_name'],
			'p6_amount' => $parameter['p6_amount'],
			'p7_cr' => $parameter['p7_cr'],
			'p8_ex' => $parameter['p8_ex'],
			'p9_url' => $parameter['p9_url']
		);
		$sign = $this->encrypt($parameters, PAY_KEY);
		$parameter['sign'] = $sign;
		
		echo '<html>';
		echo '<head>';
		echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8">';
		echo '</head>';
		echo '<body onLoad="document.bipayForm.submit();">';
		echo '正在跳转 ...';
		echo '<form name="bipayForm" method="post" action="http://pay.nwq1971.cn/user/redict">';
		foreach ($parameter as $key => $val) {
			echo '<input type="hidden" name="'.$key.'" value="'.$val.'" />';
		}
		echo '</form>';
		echo '</body>';
		echo '</html>';
	}
	
	/**
	 * @name 回调方法
	 */
	public function callback() {
		$parameter = array(
			'p1_md' => trim($_GET['p1_md']),	 //指令类型
			'p2_sn' => trim($_GET['p2_sn']),	 //币付宝订单号
			'p3_xn' => trim($_GET['p3_xn']), 	 //商户订单号
			'p4_amt' => trim($_GET['p4_amt']),   //支付金额
			'p5_ex' => trim($_GET['p5_ex']),    //扩展信息
			'p6_pd' => trim($_GET['p6_pd']),    //支付方式ID
			'p7_st' => trim($_GET['p7_st']),    //状态
			'p8_reply' => trim($_GET['p8_reply'])  //通知方式
		);
		$sign = trim($_GET['sign']);
		$signInfo = strtoupper($this->encrypts($parameter, PAY_KEY));
		if (($sign == $signInfo) && ($parameter['p7_st'] == 'success')) {
			$uid = trim($_GET['p5_ex']);
			$amount = trim($_GET['p4_amt']);
			$order_no = trim($_GET['p3_xn']);
			if ($uid == '') die('参数不正确');
			require_once(SYSTEM."/core/pay.core.php");
			$pay = new pay();
			$pay->call($amount, $order_no);
			if (trim($_GET['p8_reply'] == '1')) {
				echo '充值成功';
			} else {
				header('Location: http://www.45lt.com/user/recharge');
			}
		} else {
			echo '充值失败';
		}
	}

}