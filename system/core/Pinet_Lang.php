<?php defined("BASEPATH") or exit("No direct script access allowed");

class Pinet_Lang extends CI_Lang {
	private $CI;
	private $lang_model;

	function __construct() {
		parent::__construct();
	}

	function line($line = '') {
		if(!$this->CI) {
			$this->CI = &get_instance();
			$this->CI->load->model('lang_model');
			$this->lang_model = $this->CI->lang_model;
		}

		$lang = get_lang();

		$l = $this->lang_model->line($line, $lang);
		if($l != $line) // If we found the line in the database
			return $l;

		return parent::line($line, $lang); // Fallback to CI's default
	}
}
