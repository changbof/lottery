<?php
// [易优付]支付接口
class pay_ekepay {

	// 银行代码
	private $banks = array(
		'3'  => '985', // 广东发展银行
		'4'  => '982',  // 华夏银行
		'5'  => '981', // 交通银行
		'6'  => '978', // 平安银行
		'7'  => '977', // 上海浦东发展银行
		'8'  => '972', // 兴业银行
		'9'  => '971', // 中国邮政储蓄银行
		'10' => '986', // 中国光大银行
		'11' => '967', // 中国工商银行
		'12' => '965', // 中国建设银行
		'13' => '980', // 中国民生银行
		'14' => '964', // 中国农业银行
		'15' => '963', // 中国银行
		'16' => '970', // 招商银行
		'17' => '962', // 中信银行
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
		$bank_url = "http://Gate.ekepay.com/paybank.aspx"; //网银支付接口URL
		$parter   = PAY_ID; //商户id
		$key      = PAY_SECRET;
		$url      = "parter=". $parter ."&type=". $type ."&value=". $amount. "&orderid=". $orderid ."&callbackurl=". $url_callback;
		$sign	  = md5($url. $key); // 签名
		$url	  = $bank_url . "?" . $url . "&sign=" .$sign. "&hrefbackurl=". $url_return; // 最终url
		//页面跳转
		header("location:" .$url);
	}
	
	/**
	 * @name 回调方法
	 */
	public function callback() {
		$key            = PAY_SECRET;  
		$orderid        = trim($_GET['orderid']);
		$opstate        = trim($_GET['opstate']);
		$ovalue         = trim($_GET['ovalue']);
		$sign           = trim($_GET['sign']);
		
		//订单号为必须接收的参数，若没有该参数，则返回错误
		if(empty($orderid)) {
			die("opstate=-1");		//签名不正确，则按照协议返回数据
		}
		
		$sign_text	= "orderid=$orderid&opstate=$opstate&ovalue=$ovalue".$key;
		$sign_md5 = md5($sign_text);
		if($sign_md5 != $sign){
			die("opstate=-2");		//签名不正确，则按照协议返回数据
		} else {
			if ($opstate==0) {
				require_once(SYSTEM."/core/pay.core.php");
				$pay = new pay();
				$pay->call($ovalue, $orderid);
				echo "支付成功！";
			} else if ($opstate==1) {
				echo "请求参数无效！";				
			} else {
				echo "签名错误!";
			}
		}
		die("opstate=0");
	}

}