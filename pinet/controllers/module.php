<?php defined("BASEPATH") or exit("No direct script access allowed");

class Module extends Pinet_Controller {
	public function __construct() {
		parent::__construct();
	}

	public function is_method_valid($method) {
		return $this->load->module($method);
	}

	public function _get_method($method, &$args) {
		$this->module = $this->load->module($method);
		if(isset($this->module)) {
			if(count($args)) {
				return array_shift($args);
			}
			else {
				return 'index';
			}
		}
		else
			show_error(lang_f('No module named %s found!', $method));
	}

	public function _process($method, $args) {
		if(isset($this->module))
			$this->_default_process($this->module, $method, $args);
	}
}
