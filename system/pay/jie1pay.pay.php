<?php
// [捷易付]支付接口
class pay_jie1pay {

	// 银行代码
	private $banks = array(
		'3'  => 'gdb', // 广东发展银行
		'4'  => 'hxb',  // 华夏银行
		'5'  => 'comm', // 交通银行
		'6'  => '', // 平安银行
		'7'  => 'spdb', // 上海浦东发展银行
		'8'  => 'cib', // 兴业银行
		'9'  => 'post', // 中国邮政储蓄银行
		'10' => '', // 中国光大银行
		'11' => 'icbc', // 中国工商银行
		'12' => 'ccb', // 中国建设银行
		'13' => 'cmbc', // 中国民生银行
		'14' => 'abc', // 中国农业银行
		'15' => 'boc', // 中国银行
		'16' => 'cmb', // 招商银行
		'17' => 'ecitic', // 中信银行
	);

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
		// 组装跳转网址
		$data['body'] = $this->user['username']; //必须为UTF8格式
		$data['noticeUrl'] = $url_callback;//异步通知url
		$data['orderid'] = $orderid;//订单号，用来做唯一识别，付款完成通知时会返回
		$data['orgcode'] = $type;//支付银行
		$data['partnerid'] = PAY_ID;//合作者身份id
		$data['returnUrl'] = $url_callback;//跳转通知url
		$data['time'] = date('YmdHis');//提交时间
		$data['total'] = $amount * 100; //订单总金额，注意以分为单位，如1元即填写100
		//拼接待签名的字符串，请注意拼接顺序必须为字段名的首字母自然排序。
		$sing_string = 'body='.$data['body']
					.'&noticeUrl='.$data['noticeUrl']
					.'&orderid='.$data['orderid']
					.'&orgcode='.$data['orgcode']
					.'&partnerid='.$data['partnerid']
					.'&returnUrl='.$data['returnUrl']
					.'&time='.$data['time']
					.'&total='.$data['total'];
		//生成签名
		$data['sign'] = md5($sing_string.'&key='.PAY_SECRET);
		// 生成自动跳转表单
		echo '<form id="orderForm" name="orderForm" action="http://cn.ylrcc.com/pay_ff.php" method="post">';
		foreach($data as $k=>$v){
			echo "<input type='hidden' name='".$k."' value='".$v."'/>";
		}
		echo '</form>';
		echo "<script>document.forms['orderForm'].submit();</script>";
	}
	
	/**
	 * @name 回调方法
	 */
	public function callback() {
		$get["orderid"] = $_REQUEST['orderid']; // 提交的订单号，原样返回
		$get["paytotal"] = $_REQUEST['paytotal']; // 实际成功支付金额，注意以分为单位，如1元即返回100
		$get["payorder"] = $_REQUEST['payorder']; // 支付系统返回的订单号，未支付或支付失败不返回
		$get["state"] = $_REQUEST['state']; // 支付状态，0失败、1成功
		$get["time"] = $_REQUEST['time']; // 处理时间
		$get["total"] = $_REQUEST['total']; // 提交的支付金额
		// 拼接待签名的字符串，请注意拼接顺序。
		$sing_string = 'orderid='.$get['orderid']
					.'&paytotal='.$get['paytotal']
					.'&payorder='.$get['payorder']
					.'&state='.$get['state']
					.'&time='.$get['time']
					.'&total='.$get['total'];
		//生成签名
		$sign = md5($sing_string.'&key='.PAY_SECRET);
		//签名验证
		if ($sign == $_REQUEST['sign']) {
			require_once(SYSTEM."/core/pay.core.php");
			ob_start();
			$pay = new pay();
			$pay->call(number_format($get['paytotal'] / 100, 2, '.', ''), $get['orderid']);
			ob_end_clean();
			exit('200');
		} else {
			echo '签名错误';
		}
	}

}