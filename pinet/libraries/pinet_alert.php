<?php defined("BASEPATH") or exit("No direct script access allowed");

class Alert {
	public $type;
	public $message;
	public $from;
}

class Pinet_Alert {
	public function __construct() {
		$CI = &get_instance();
		$CI->load->library('session');
	}
	public function getAlerts() {
		$CI = &get_instance();
		$alerts = $CI->session->userdata('alerts');
		if(!$alerts) {
			$alerts = array();
		}
		return $alerts;
	}

	public function addAlert($alert) {
		if(!isset($alert)) {
			return;
		}
		$alerts = $this->getAlerts();
		$alerts []= copy_new($alert);
		$CI = &get_instance();
		$CI->session->set_userdata('alerts', $alerts);
	}

	public function clear() {
		$CI = &get_instance();
		$CI->session->unset_userdata('alerts');
	}
}
