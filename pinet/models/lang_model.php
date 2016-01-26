<?php defined("BASEPATH") or exit("No direct script access allowed");

class Lang_Model extends Pinet_Model {
	function __construct() {
		parent::__construct('languages');
	}

	function line($line, $lang = '') {
		if($line == 'db_error_heading')
			return $line;
		$l = $lang == '' ? get_lang() : $lang;
		$CI = &get_instance();
		$ret = $this->get(array('lang' => $l));
		$this->result_mode = 'object';
		if(isset($ret->value)) { // If we can get the value, then use the value
			return $ret->value;
		}
		if($l == get_default_lang()) // If this is the default language, just return the line
			return $line;

		return $this->line($line, get_default_lang()); // Return the default line for this
	}
}
