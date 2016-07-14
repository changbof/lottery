<?php
// [环迅]支付接口
class pay_ips {

	// 银行代码
	private $banks = array(
		'3'  => '1114', // 广东发展银行
		'4'  => '1111',  // 华夏银行
		'5'  => '1108', // 交通银行
		'6'  => '1121', // 平安银行
		'7'  => '1109', // 上海浦东发展银行
		'8'  => '1103', // 兴业银行
		'9'  => '1119', // 中国邮政储蓄银行
		'10' => '1112', // 中国光大银行
		'11' => '1100', // 中国工商银行
		'12' => '', // 中国建设银行
		'13' => '1110', // 中国民生银行
		'14' => '1101', // 中国农业银行
		'15' => '1107', // 中国银行
		'16' => '1102', // 招商银行
		'17' => '1104', // 中信银行
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
		//获取输入参数
		$pVersion = 'v1.0.0';//版本号
		$pMerCode = PAY_MERCODE;//商户号
		$pMerName = PAY_MERNAME;//商户名
		$pMerCert = PAY_MERCERT;//商户证书
		$pAccount  =  PAY_ACCOUNT;//账户号
		$pMsgId = 'msg'.rand(1000, 9999);//消息编号
		$pReqDate = date('Ymdhis');//商户请求时间

		$pMerBillNo = $orderid;//商户订单号
		$pAmount = $amount;//订单金额 
		$pDate = date('Ymd');//订单日期
		$pCurrencyType = 'GB';//币种
		$pGatewayType = '01';//支付方式
		$pLang = 156;//语言
		$pMerchanturl = $url_return;//支付结果成功返回的商户URL 
		$pFailUrl = "";//支付结果失败返回的商户URL 
		$pAttach = $this->user['username'];//商户数据包
		$pOrderEncodeTyp = 5;//订单支付接口加密方式 默认为5#md5
		$pRetEncodeType = 17;//交易返回接口加密方式
		$pRetType = 3;//返回方式 
		$pServerUrl = $url_callback;//Server to Server返回页面 
		$pBillEXP = 1;//订单有效期(过期时间设置为1小时)
		$pGoodsName = '充值';//商品名称
		$pIsCredit = 1;//直连选项
		$pBankCode = $type;//银行号
		$pProductType= 1;//产品类型
		
		//请求报文的消息体
		$strbodyxml= "<body>"
					."<MerBillNo>".$pMerBillNo."</MerBillNo>"
					."<Amount>".$pAmount."</Amount>"
					."<Date>".$pDate."</Date>"
					."<CurrencyType>".$pCurrencyType."</CurrencyType>"
					."<GatewayType>".$pGatewayType."</GatewayType>"
					."<Lang>".$pLang."</Lang>"
					."<Merchanturl>".$pMerchanturl."</Merchanturl>"
					."<FailUrl>".$pFailUrl."</FailUrl>"
					."<Attach>".$pAttach."</Attach>"
					."<OrderEncodeType>".$pOrderEncodeTyp."</OrderEncodeType>"
					."<RetEncodeType>".$pRetEncodeType."</RetEncodeType>"
					."<RetType>".$pRetType."</RetType>"
					."<ServerUrl>".$pServerUrl."</ServerUrl>"
					."<BillEXP>".$pBillEXP."</BillEXP>"
					."<GoodsName>".$pGoodsName."</GoodsName>"
					."<IsCredit>".$pIsCredit."</IsCredit>"
					."<BankCode>".$pBankCode."</BankCode>"
					."<ProductType>".$pProductType."</ProductType>"
					."</body>";
		$Sign=$strbodyxml.$pMerCode.$pMerCert; //签名明文
		$pSignature = md5($strbodyxml.$pMerCode.$pMerCert);//数字签名
		//请求报文的消息头
		$strheaderxml= "<head>"
						."<Version>".$pVersion."</Version>"
						."<MerCode>".$pMerCode."</MerCode>"
						."<MerName>".$pMerName."</MerName>"
						."<Account>".$pAccount."</Account>"
						."<MsgId>".$pMsgId."</MsgId>"
						."<ReqDate>".$pReqDate."</ReqDate>"
						."<Signature>".$pSignature."</Signature>"
						."</head>";
		//提交给网关的报文
		$strsubmitxml =  "<Ips>"
						."<GateWayReq>"
						.$strheaderxml
						.$strbodyxml
						."</GateWayReq>"
						."</Ips>";
		echo '<form name="form1" id="form1" method="post" action="http://newpay.ips.com.cn/psfp-entry/gateway/payment.html" target="_self">';
		echo '<input type="hidden" name="pGateWayReq" value="'.$strsubmitxml.'" />';
		echo '</form>';
		echo '<script language="javascript">document.form1.submit();</script>';
	}
	
	/**
	 * @name 回调方法
	 */
	public function callback() {
		$paymentResult = $_POST["paymentResult"];//获取信息
		$xml=simplexml_load_string($paymentResult,'SimpleXMLElement', LIBXML_NOCDATA);
		//读取相关xml中信息
		$ReferenceIDs = $xml->xpath("GateWayRsp/head/ReferenceID");//关联号
		//var_dump($ReferenceIDs); 
		$ReferenceID = $ReferenceIDs[0];//关联号
		$RspCodes = $xml->xpath("GateWayRsp/head/RspCode");//响应编码
		$RspCode=$RspCodes[0];
		$RspMsgs = $xml->xpath("GateWayRsp/head/RspMsg"); //响应说明
		$RspMsg=$RspMsgs[0];
		$ReqDates = $xml->xpath("GateWayRsp/head/ReqDate"); // 接受时间
		$ReqDate=$ReqDates[0];
		$RspDates = $xml->xpath("GateWayRsp/head/RspDate");// 响应时间
		$RspDate=$RspDates[0];
		$Signatures = $xml->xpath("GateWayRsp/head/Signature"); //数字签名
		$Signature=$Signatures[0];
		$MerBillNos = $xml->xpath("GateWayRsp/body/MerBillNo"); // 商户订单号
		$MerBillNo=$MerBillNos[0];
		$CurrencyTypes = $xml->xpath("GateWayRsp/body/CurrencyType");//币种
		$CurrencyType=$CurrencyTypes[0];
		$Amounts = $xml->xpath("GateWayRsp/body/Amount"); //订单金额
		$Amount=$Amounts[0];
		$Dates = $xml->xpath("GateWayRsp/body/Date");    //订单日期
		$Date=$Dates[0];
		$Statuss = $xml->xpath("GateWayRsp/body/Status");  //交易状态
		$Status=$Statuss[0];
		$Msgs = $xml->xpath("GateWayRsp/body/Msg");    //发卡行返回信息
		$Msg=$Msgs[0];
		$Attachs = $xml->xpath("GateWayRsp/body/Attach");    //数据包
		$Attach=$Attachs[0];
		$IpsBillNos = $xml->xpath("GateWayRsp/body/IpsBillNo"); //IPS订单号
		$IpsBillNo=$IpsBillNos[0];
		$IpsTradeNos = $xml->xpath("GateWayRsp/body/IpsTradeNo"); //IPS交易流水号
		$IpsTradeNo=$IpsTradeNos[0];
		$RetEncodeTypes = $xml->xpath("GateWayRsp/body/RetEncodeType");    //交易返回方式
		$RetEncodeType=$RetEncodeTypes[0];
		$BankBillNos = $xml->xpath("GateWayRsp/body/BankBillNo"); //银行订单号
		$BankBillNo=$BankBillNos[0];
		$ResultTypes = $xml->xpath("GateWayRsp/body/ResultType"); //支付返回方式
		$ResultType=$ResultTypes[0];
		$IpsBillTimes = $xml->xpath("GateWayRsp/body/IpsBillTime"); //IPS处理时间
		$IpsBillTime=$IpsBillTimes[0];
		
		$arrayMer = array (
			'mername' => PAY_MERNAME,
			'mercert' => PAY_MERCERT,
			'acccode' => PAY_ACCOUNT,
		);
		$sbReq = "<body>"
				. "<MerBillNo>" . $MerBillNo . "</MerBillNo>"
				. "<CurrencyType>" . $CurrencyType . "</CurrencyType>"
				. "<Amount>" . $Amount . "</Amount>"
				. "<Date>" . $Date . "</Date>"
				. "<Status>" . $Status . "</Status>"
				. "<Msg><![CDATA[" . $Msg . "]]></Msg>"
				. "<Attach><![CDATA[" . $Attach . "]]></Attach>"
				. "<IpsBillNo>" . $IpsBillNo . "</IpsBillNo>"
				. "<IpsTradeNo>" . $IpsTradeNo . "</IpsTradeNo>"
				. "<RetEncodeType>" . $RetEncodeType . "</RetEncodeType>"
				. "<BankBillNo>" . $BankBillNo . "</BankBillNo>"
				. "<ResultType>" . $ResultType . "</ResultType>"
				. "<IpsBillTime>" . $IpsBillTime . "</IpsBillTime>"
				. "</body>";
		$sign=$sbReq.$pmercode.$arrayMer['mercert'];
		$md5sign = md5($sign);
		
		//判断签名
		if ($Signature == $md5sign) {
			if ($RspCode == '000000') {
				require_once(SYSTEM."/core/pay.core.php");
				$pay = new pay();
				$pay->call($Amounts, $MerBillNos);
				echo "支付成功！";
			} else {
				echo '订单支付失败';
			}
		} else {
			echo "订单签名错误";
		}

	}
}