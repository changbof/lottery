<?php

class mod_sys extends mod {

	public function notice() {
		if ($this->post) {
			if (!array_key_exists('type', $_GET)) $_GET['type'] = 'list';
			if ($_GET['type'] === 'list') {
				$this->notice_list();
			} else if ($_GET['type'] === 'content') {
				$this->notice_content();
			} else {
				core::__403();
			}
		} else {
			$this->ajax();
		}
	}
	
	private function notice_list() {
		$tpl = $this->ispage ? '/sys/notice_list_body' : '/sys/notice_list';
		$page_current = $this->get_page();
		$pagesize = $this->pagesize;
		$skip = ($page_current - 1) * $pagesize;
		$total = $this->db->query("SELECT COUNT(1) AS __total FROM `{$this->db_prefix}content` WHERE `enable`=1", 2);
		$total = $total['__total'];
		$page_max = $this->get_page_max($total);
		$data = $this->db->query("SELECT `id`,`title`,`addTime` FROM `{$this->db_prefix}content` WHERE `enable`=1 ORDER BY `id` DESC LIMIT $skip,$pagesize", 3);
		$this->display($tpl, array(
			'data' => $data,
			'page_current' => $page_current,
			'page_max' => $page_max,
			'page_url' => '/sys/notice?'.http_build_query(array('type' => 'list', 'page' => '{page}')),
			'page_container' => '#sys-notice-dom .body',
		));
	}
	
	private function notice_content() {
		if (!array_key_exists('id', $_GET) || !core::lib('validate')->number($_GET['id'])) core::__403();
		$id = intval($_GET['id']);
		$data = $this->db->query("SELECT * FROM `{$this->db_prefix}content` WHERE `id`={$id} LIMIT 1", 2);
		if (!$data || !$data['enable']) core::error('您查询的公告不存在');
		$this->display('/sys/notice_content', array('data' => $data));
	}

}