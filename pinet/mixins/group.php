<?php defined("BASEPATH") or exit("No direct script access allowed");

class Group_Mixin extends Mixin {
	public function _propertyNames() {
		return array('groups', 'duck');
	}

	private function getGroups() {
		$id = $this->getID();
		if($id != -1)
			return $this->user_model->getGroups($id);
		return array();
	}

	public function __get($key) {
		if($key == 'groups')
			return $this->getGroups();
		return $this->$key;
	}

	public function init() {
		parent::init();
		$this->duck = 'Hello';
		$this->model(array('group_model', 'user_model'));
	}

	public function apply() {
		echo 'Applying';
	}
}
