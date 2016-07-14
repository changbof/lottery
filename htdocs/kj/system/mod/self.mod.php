<?php

class mod_self extends mod {
	
	public function __construct() {
		$this->user_check = false;
		parent::__construct();
	}
	
	public function index() {
		$id = $this->get_id();
		$this->client_type = 'common';
		header('Content-type: application/xml');
		$this->display('self/'.$id);
	}
	
}