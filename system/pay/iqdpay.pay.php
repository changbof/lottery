<?php
// [爱钱道 ]支付接口
class pay_iqdpay {

	// 银行代码
	private $banks = array(
	'3'  => '100022', // 广东发展银行
	'4'  => '100019',  // 华夏银行
	'5'  => '100015', // 交通银行
	'6'  => '100030', // 平安银行
	'7'  => '100021', // 上海浦东发展银行
	'8'  => '100020', // 兴业银行
	'9'  => '100025', // 中国邮政储蓄银行
	'10' => '100024', // 中国光大银行
	'11' => '100012', // 中国工商银行
	'12' => '100014', // 中国建设银行
	'13' => '100018', // 中国民生银行
	'14' => '100013', // 中国农业银行
	'15' => '100017', // 中国银行
	'16' => '100016', // 招商银行
	'17' => '100023', // 中信银行
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
		//
		
		$merchantCode = PAY_MERCODE;
		$tonkeyKey = PAY_MERCERT;
		$postUrl = 'http://gateway.miss-shoes.cn/Gateway/IQiandao';


		
		
		$version = "V1.0";
		$merchantCode = $merchantCode;
		$orderId = $orderid;
		$amount = number_format($amount,2,".","");
		$asyNotifyUrl = $url_callback;;
		$synNotifyUrl =$url_return;
		$orderDate = date("YmdHis");
		$tradeIp = $this->ip();
		$payCode = $type;
		$cardNo = "";
		$cardPassword = "";
		$qq = "";
		$telephone = "";
		$goodsName = $orderId;
		$goodsDescription = "";
		$remark1 = $this->user['username'];
		$remark2 = "";

		$signText = 'Version=['.$version.']MerchantCode=['.$merchantCode.']OrderId=['.$orderId.']Amount=['.$amount.']AsyNotifyUrl=['.$asyNotifyUrl.']SynNotifyUrl=['.$synNotifyUrl.']OrderDate=['.$orderDate.']TradeIp=['.$tradeIp.']PayCode=['.$payCode.']TokenKey=['.$tonkeyKey.']';

		$md5Sign = strtoupper(md5($signText));
		//
		?>
		<html>
		<head>
		<title>Payment By CreditCard online</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		</head>
		<body>
		<form id="frm1" name="frm1" method="post" action="<?php echo "$postUrl"; ?>">
		<input type="hidden" id="Version" name="Version" value="<?php echo "$version"; ?>"/>
		<input type="hidden" id="MerchantCode" name="MerchantCode" value="<?php echo "$merchantCode"; ?>" />
		<input type="hidden" id="OrderId" name="OrderId" value="<?php echo "$orderId"; ?>" />
		<input type="hidden" id="Amount" name="Amount" value="<?php echo "$amount"; ?>" />
		<input type="hidden" id="AsyNotifyUrl" name="AsyNotifyUrl" value="<?php echo "$asyNotifyUrl"; ?>" />
		<input type="hidden" id="SynNotifyUrl" name="SynNotifyUrl" value="<?php echo "$synNotifyUrl"; ?>" />
		<input type="hidden" id="OrderDate" name="OrderDate" value="<?php echo "$orderDate"; ?>"  />
		<input type="hidden" id="TradeIp" name="TradeIp" value="<?php echo "$tradeIp"; ?>" />
		<input type="hidden" id="PayCode" name="PayCode" value="<?php echo "$payCode"; ?>" />
		<input type="hidden" id="CardNo" name="CardNo" value="<?php echo "$cardNo"; ?>" />
		<input type="hidden"  id="CardPassword" name="CardPassword" value="<?php echo "$cardPassword"; ?>" />
		<input type="hidden"  id="QQ" name="QQ" value="<?php echo "$qq"; ?>" />
		<input type="hidden"  id="Telephone" name="Telephone" value="<?php echo "$telephone"; ?>" />
		<input type="hidden"  id="GoodsName" name="GoodsName" value="<?php echo "$goodsName"; ?>" />
		<input type="hidden"  id="GoodsDescription" name="GoodsDescription" value="<?php echo "$goodsDescription"; ?>" />
		<input type="hidden"  id="Remark1" name="Remark1" value="<?php echo "$remark1"; ?>" />
		<input type="hidden"  id="Remark2" name="Remark2" value="<?php echo "$remark2"; ?>" />
		<input type="hidden"  id="SignValue" name="SignValue" value="<?php echo "$md5Sign"; ?>" />
		<script language="javascript">
		document.getElementById("frm1").submit();
		</script>
		</form>
		</body>
		</html>
		<?php
	}
	
	/**
	* @name 回调方法
	*/
	public function callback() {
		
		$merchantCode = PAY_MERCODE;
		$tonkeyKey = PAY_MERCERT;

		if(isset($_POST["SerialNo"])){
			$version = $_POST["Version"];
			$merchantCode = $_POST["MerchantCode"];
			$orderId = $_POST["OrderId"];
			$orderDate = $_POST["OrderDate"];
			$tradeIp = $_POST["TradeIp"];
			$serialNo = $_POST["SerialNo"];
			$amount = $_POST["Amount"];
			$payCode = $_POST["PayCode"];
			$state = $_POST["State"];
			$message = $_POST["Message"];
			$finishTime = $_POST["FinishTime"];
			$qq = $_POST["QQ"];
			$telephone = $_POST["Telephone"];
			$goodsName = $_POST["GoodsName"];
			$goodsDescription = $_POST["GoodsDescription"];
			$remark1 = $_POST["Remark1"];
			$remark2 = $_POST["Remark2"];
			$signValue = $_POST["SignValue"];

			$signText = 'Version=['.$version.']MerchantCode=['.$merchantCode.']OrderId=['.$orderId.']OrderDate=['.$orderDate.']TradeIp=['.$tradeIp.']SerialNo=['.$serialNo.']Amount=['.$amount.']PayCode=['.$payCode.']State=['.$state.']FinishTime=['.$finishTime.']TokenKey=['.$tonkeyKey.']';
			$md5Sign = strtoupper(md5($signText));

		}else{
			$version = $_POST["Version"];
			$merchantCode = $_POST["MerchantCode"];
			$orderId = $_POST["OrderId"];
			$orderDate = $_POST["OrderDate"];
			$tradeIp = $_POST["TradeIp"];
			$payCode = $_POST["PayCode"];
			$state = $_POST["State"];
			$message = $_POST["Message"];
			$qq = $_POST["QQ"];
			$telephone = $_POST["Telephone"];
			$goodsName = $_POST["GoodsName"];
			$goodsDescription = $_POST["GoodsDescription"];
			$remark1 = $_POST["Remark1"];
			$remark2 = $_POST["Remark2"];
			$signValue = $_POST["SignValue"];

			$signText = 'Version=['.$version.']MerchantCode=['.$merchantCode.']OrderId=['.$orderId.']OrderDate=['.$orderDate.']TradeIp=['.$tradeIp.']PayCode=['.$payCode.']State=['.$state.']TokenKey=['.$tonkeyKey.']';
			$md5Sign = strtoupper(md5($signText));
		}

		if($signValue == $md5Sign)
		{
			if($state=='8888') {
				require_once(SYSTEM."/core/pay.core.php");
				$pay = new pay();
				$pay->call($amount, $orderId);
				echo "支付成功！";
				echo 'ok';
			}
		}
		else{
			echo '验签失败';
		}
	}
	
	public function ip($outFormatAsLong=false){
		if (isset($HTTP_SERVER_VARS['HTTP_X_FORWARDED_FOR']))
		$ip = $HTTP_SERVER_VARS['HTTP_X_FORWARDED_FOR'];
		elseif (isset($HTTP_SERVER_VARS['HTTP_CLIENT_IP']))
		$ip = $HTTP_SERVER_VARS['HTTP_CLIENT_IP'];
		elseif (isset($HTTP_SERVER_VARS['REMOTE_ADDR']))
		$ip = $HTTP_SERVER_VARS['REMOTE_ADDR'];
		elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR']))
		$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		elseif (isset($_SERVER['HTTP_CLIENT_IP']))
		$ip = $_SERVER['HTTP_CLIENT_IP'];
		elseif (isset($_SERVER['REMOTE_ADDR']))
		$ip = $_SERVER['REMOTE_ADDR'];
		else
		$ip = '0.0.0.0';
		if(strrpos(',',$ip)>=0){
			$ip=explode(',',$ip,2);
			$ip=current($ip);
		}
		$rip=$outFormatAsLong?ip2long($ip):$ip;
		if($rip=="::1"){
			$rip="127.0.0.1";
		}
		return $rip;
	}
}