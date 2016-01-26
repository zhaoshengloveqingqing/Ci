<?php defined("BASEPATH") or exit("No direct script access allowed");

class Alert_Widget extends Pinet_Widget {
	public function __construct() {
		parent::__construct();
	}

	public function init() {
		parent::init();
		$this->CI->load->library(array('pinet_alert'));
	}
}