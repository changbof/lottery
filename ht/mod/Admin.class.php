<?php
class Admin extends AdminBase{
	public final function index($params=''){
		$this->display('index.php');
	}
	
	public final function login($params=''){
		$this->display('login.php');
	}
	
}