<?php defined('BASEPATH') or exit('No direct script access allowed');

class Navigation {
	private $CI;
	private $current_navigations;

	public function __construct() {
		$this->CI = &get_instance();
		$this->CI->load->model('action_model');
		$this->CI->config->load('navigations');
		$this->action_model = $this->CI->action_model;
	}

	public function getMainNavigations() {
		$main = $this->_getSubNavigations('main_navigation');
		if(count($main) == 0)
			$this->installNavigations();// We don't have any navigations in the database
		return $this->_getSubNavigations('main_navigation');
	}

	public function clear() {
		$this->CI->action_model->clearNavigations();
	}

	/**
	 * Reading the top navigation from the database first, if can't find any, using the 
	 * configuration file to retrieve all the first level navigations
	 */
	public function getNavigations() {
		if(isset($this->current_navigations))
			return $this->current_navigations;

		$main = $this->getMainNavigations();

		// Get current action
		$action = $this->action_model->getCurrentAction();

		if(!$action) { // Testing for current navigation
			$controller = get_controller_class();
			$method = 'index';
			if(get_breadscrums()) {
				$last = array_pop(get_breadscrums());
				$last = explode('/', $last);
				$controller = $last[0];
				if(count($last) > 1)
					$method = $last[1];
			}
			$action = $this->action_model->getActionByController($controller, $method);
		}

		if($action) {
			$action->subnavi = $this->_getSubNavigations($action->name);
			while($action->group != 'main_navigation') {
				$action_siblings = $this->_getSubNavigations($action->group);
				$arr = array();
				foreach($action_siblings as $n) {
					if($n->name == $action->name) {
						$n->current = true;
						if(isset($action->subnavi)) {
							$n->subnavi = $action->subnavi;
						}
					}
					$arr []= $n;
				}
				$action = $this->action_model->getActionByName($action->group);
				$action->subnavi = $arr;
			}
			foreach($main as $navi) {
				if($navi->name == $action->name) {
					$navi->current = true;
					if(isset($action->subnavi)) {
						$navi->subnavi = $action->subnavi;
					}
				}
			}
		}

		$this->current_navigations = $main;
		return $main;
	}

	protected function installNavigations($actions = null) {
		if($actions == null) {
			$actions = get_ci_config('navigations');
		}
		foreach($actions as $action) {
			$this->action_model->insert($action);
			if(isset($action['subnavi'])) {
				$arr = array();
				$subnavis = $action['subnavi'];
				foreach($subnavis as $subnavi) {
					$subnavi['group'] = $action['name'];
					$arr []= $subnavi;
				}
				//ci_log('The subnavis is ', $arr);
				$this->installNavigations($arr);
			}
		}
	}

	/**
	 * The subnavigations is just a group of actions, defer all the work to action model
	 */
	public function _getSubNavigations($navi) {
		return $this->action_model->getActionsByGroup($navi);
	}

	public function navigate($navi) {
		$navigation = $this->action_model->getActionByName($navi);
		if($navigation != null) { // We do have this navigation and have the rights to view it
			$navis = $this->getNavigations();
			foreach($navis as $n) {
				if($n->name == $navi) {
					// Setting the current navigation
					$n->current = true;
					$this->current = $n; 
				}
			}
			return $navis;
		}
		return false;
	}
}
