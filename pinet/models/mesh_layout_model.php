<?php defined("BASEPATH") or exit("No direct script access allowed");

class Mesh_Layout_Model extends Pinet_Model {
	public function __construct() {
		parent::__construct('mesh_layouts');
	}

	public function getLayout($name) {
		$this->result_mode = 'object';
		return $this->get('name', $name);
	}
}
