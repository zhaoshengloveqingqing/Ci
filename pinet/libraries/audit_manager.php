<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

class Audit_Manager {
	public function __construct() {
		$CI = &get_instance();
		$CI->load->model('audit_model');
	}
	public function audit_before($interceptor) {
		$CI = &get_instance();
		$CI->audit_model->addAudit(
			$interceptor->call_method,
			$interceptor->args
		);
		$CI->log('Before running method %s of %s, with args', $interceptor->args,
			$interceptor->call_method, get_class($CI));
		return true; // Always pass for audit
	}
	public function audit_after($interceptor) {
		$CI = &get_instance();
		$CI->log('After running method %s of %s, with args', $interceptor->args,
			$interceptor->call_method, get_class($CI));
	}
}
