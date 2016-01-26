<?php defined('BASEPATH') or exit('No direct script access allowed');

class Interceptor {
	public $refer;
	public $pattern;
	public $type;
	public $field;
	public $method;
	public $tag;
	public $controller;

	public function accept($method = 'index') {
		if(isset($this->controller)) {
			$cls = get_controller_class();
			if(is_array($this->controller)){
				foreach($this->controller as $p) {
					if(preg_match($p, $cls)) {
						return true;
					}
				}
				return false;
			}
			if(!preg_match($this->controller, $cls)) {
				return false;
			}
		}
		if(is_array($this->pattern)) {
			foreach($this->pattern as $p) {
				if(preg_match($p, $method)) {
					return true;
				}
			}
			return false;
		}
		return preg_match($this->pattern, $method);
	}

	public function toString() {
		return sprintf('Inteceptor {pattern: %s, type: %s, field: %s, method: %s}',
			$this->pattern, $this->type, $this->field, $this->method);
	}

	/**
	 * Call the intercepted method, using for around mostly
	 */
	public function process() {
		$CI = &get_instance();
		return call_user_func_array(array($CI, '_process'), array($this->call_method, $this->args));
	}

	public function intercept($method, $args, $ret = null) {
		$CI = &get_instance();
		$field = $this->field;
		$this->args = $args;
		$this->ret = $ret;
		$this->call_method = $method;
		if(isset($CI->$field)) {
			return call_user_func_array(array($CI->$field, $this->method), array($this));
		}
		else
			$this->process();
	}
}

class InterceptorConfig {
	public $define;
	public $interceptors; 
	private $_interceptor_objects;

	public function __construct() {
		$this->define = new stdclass();
		$this->interceptors = new stdclass();
	}

	public function getInterceptors($method = 'index') {
		$ret = array();
		$ret['before'] = array();
		$ret['after'] = array();
		$ret['around'] = array();
		$ret['render'] = array();
		if(!isset($this->_interceptor_objects)) { // Not initialized yet
			$this->_interceptor_objects = array();
			foreach($this->interceptors as $key => $i) {
				$interceptor = copy_new($i, 'Interceptor');
				if($key) { // If it has refer
					if(isset($this->define->$key)) {
						$interceptor = copy_object($this->define->$key,
							$interceptor);
					}
				}
				$this->_interceptor_objects []= $interceptor;
			}
		}
		foreach($this->_interceptor_objects as $i) {
			if($i->accept($method)) {
				$ret[$i->type][]= $i;
			}
		}
		return $ret;
	}

	public function merge($parent) {
		foreach($parent->define as $k => $v) {
			if(!isset($this->define->$k)) {
				$this->define->$k = $v;
			}
		}
		foreach($parent->interceptors as $k => $v) {
			$this->interceptors->$k = $v;
		}
	}
}

/**
 * The base class for the interceptor support for controller
 *
 * @author Jack
 * @date Fri Aug 15 14:47:09 2014
 * @version 1.0
 */
class Interceptor_Support {

	private $config;

	public function getConfigJson($class) {
		$path = FCPATH.'/application/config/interceptors/'.strtolower($class).'.json';
		if(file_exists($path)) {
			return json_decode(file_get_contents($path));
		}
		return null;
	}

	public function buildInterceptorConfig($class) {
		$parent_class = get_parent_class($class);
		if($parent_class) {
			$config = $this->buildInterceptorConfig($parent_class); // Get parent's config first
		}

		// Getting the config for current
		$to_merge = $this->getConfigJson($class);
		if($to_merge == null) {
			$to_merge = new InterceptorConfig();
		}
		else {
			if(!isset($to_merge->define)) { // If the config is just for arrays
				$to_merge = array(
					'define' => new stdclass(),
					'interceptors' => $to_merge
				);
			}
			$to_merge = copy_new($to_merge, "InterceptorConfig");
		}

		if(!isset($config)) // If we don't have any parent
			return $to_merge;

		$config->merge($to_merge);

		return $config;
	}

	/**
	 * Get all the interceptors that has been configured in configuration files
	 */
	public function getInterceptors($method = 'index') {
		$CI = &get_instance();
		$class = get_class($CI);
		if(!isset($this->config)) {
			$this->config = $this->buildInterceptorConfig($class); // Build the config first
		}
		return $this->config->getInterceptors($method);
	}
}
