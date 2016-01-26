<?php defined('BASEPATH') or exit('No direct script access allowed');

class Audit_Model extends Pinet_Model {
	function __construct() {
		parent::__construct('audits');
	}

	public function addAudit($method, $args) {
		$CI = &get_instance();
		$CI->load->model('user_model');
		$session = '';
		if(isset($CI->session)) {
			$session = dump_s($CI->session->all_userdata());
		}
		$this->insert(array(
			'controller' => get_class($CI),
			'method' => $method,
			'user_id' => $CI->user_model->getLoginUserID(),
			'session' => $session,
			'args' => dump_s($args)
		));
	}
}
