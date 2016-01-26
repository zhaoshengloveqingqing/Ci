<?php defined('BASEPATH') or exit('No direct script access allowed');

/**
 * This is the model for actions, the actions is the operations in the system,
 * each action means a call to a controller's method, it can indeed have more
 * data than that, and all the other data is stored as json in the fields field.
 *
 * The action can be something in the toolbar, or in the navigation, the main 
 * navigation is a special group of action, named main_navigation.
 */
class Action_Model extends Pinet_Model {
	public function __construct() {
		parent::__construct('actions');
	}

	public function validAction($action) {
		if(!$action)
			return false;

		$CI = &get_instance();
        if(!isset($CI->security_engine))
            return true;

		$result = $CI->security_engine->validate($action);
		ci_log('The result for action is %s', $action, $result);

		if($action->group == MAIN_NAVIGATION) {
			return $result == SECURITY_ALLOW;
		}

		if($result != SECURITY_ALLOW)
			return false;

		$parent_action = $this->getActionByName($action->group);

		return $this->validAction($parent_action);
	}

	public function clearNavigations() {
		$this->removeByGroupRecursive(MAIN_NAVIGATION);
	}

	public function removeByGroupRecursive($group) {
		$this->result_mode = 'object';
		$list = $this->get_all(array('group' => $group));
		foreach($list as $action) {
			$this->removeByGroupRecursive($action->name);
		}
		$this->delete(array('group' => $group));
	}

	public function removeByGroup($group) {
		$this->delete(array('group' => $group));
	}

	public function getCurrentAction() {
		$controller = get_controller_class();
		$method = get_controller_method();
		$action = $this->getActionByController($controller, $method);
		if($this->validAction($action))
			return $action;
		return null;
	}

	public function getActionByName($name)  {
		$this->result_mode = 'object';
		$action = $this->get('name', $name);
		if(isset($action->name)) {
            $action = copy_new($action, 'Action');
			if($this->validAction($action))
				return $action;
		}
		return null;
	}

	public function getActionByController($controller, $method) {
		$this->result_mode = 'object';
		$action = $this->get(array(
			'controller' => $controller,
			'method' => $method
		));
        $action = copy_new($action, 'Action');
		if(isset($action->controller) && $this->validAction($action)) {
			return $action;
		}
		return null;
	}

	public function getActionsByGroup($group) {
		$this->result_mode = 'object';
		$ret = array();
		foreach($this->get_all('group', $group) as $a) {
			$action = copy_new($a, 'Action'); 
			if($this->validAction($action))
				$ret []= $action;
		}
		return $ret;
	}
}
