<?php defined("BASEPATH") or exit("No direct script access allowed");

class Security_Configuration_Model extends Pinet_Model {
    function __construct() {
        parent::__construct('security_configurations');
    }

	public function getAllMatch($type = 'action') {
		$this->result_mode = 'object';
		return $this->getOrCreate(array(
			'type' => $type,
			'controller' => '*',
			'method' => '*'
		));
	}

	public function getMatchController($controller, $type = 'action') {
		$this->result_mode = 'object';
		return $this->get_all(array(
			'type' => $type,
			'controller' => $controller
		));
	}

	public function getMatchMethod($method, $type = 'action') {
		$this->result_mode = 'object';
		return $this->get(array(
			'type' => $type,
			'controller' => '*',
			'method' => $method
		));
	}

	public function getCurrentConfigurations($obj = null) {
		$type = 'action';
		if($obj == null) {
			$action = get_controller_meta();
		}
		else {
			if(is_string($obj)) {
				$type = $obj;
				$action = get_controller_meta();
			}
			else {
				$action = $obj;
			}
		}
		$ret = array();
		$ret []= $this->getAllMatch($type);

		$c = $this->getMatchController($action->controller, $type);
		$method_same = array();
		$method_match = array();
		$match = null;
		foreach($c as $conf) {
			$conf->tag = isset($conf->tag)? json_decode($conf->tag): array();
			if($conf->method == $action->method) {
				if($action->args == $conf->tag) {
					$match = $conf;
				}
				else
					$method_same []= $conf;
			}
			else {
				if(is_regex($conf->method) && preg_match($conf->method, $action->method)) {
					$method_match []= $conf;
				}
			}
		}

		if($method_match) {
			foreach($method_match as $m) {
				$ret []= $m;
			}
		}

		$c = $this->getMatchMethod($action->method, $type);
		if(isset($c->id))
			$ret []= $c;

		if($method_same) {
			foreach($method_same as $m) {
				$ret []= $m;
			}
		}

		if($match) {
			$ret []= $match;
		}

		return $ret;
	}

    public function addConfig($config){
		return $this->insert($config);
    }

    public function getConfigs($type, $controller){
        $this->db->select('id, method, tag');
        $this->db->from('security_configurations');
        $this->db->where('type', $type);
        $this->db->where('controller', $controller);
        return $this->db->get()->result_array();
    }
}
