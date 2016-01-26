<?php defined("BASEPATH") or exit("No direct script access allowed");

/**
 * The Base class for all the module classes
 */
class Pinet_Module {
	private static $instance;

	public function __construct() {
		if(get_class($this) === 'Pinet_Module') // Skipping the CI Load
			return;
		self::$instance =& $this;
		$this->CI = &get_instance();
		$this->load = $this->CI->load;
		$module_path = dirname(get_class_script_path(get_class($this)));
		$this->CI->load->add_package_path($module_path);
		$this->CI->addTemplateDir($module_path.'/views');
	}


	public function scss($file, $version = null, $index = -1, $module = null, $scssFolder = null) {
		$this->CI->scss($file, $version, $index, $module, $scssFolder);
	}
	public function widget($widget) {
		return $this->CI->load->widget($widget);
	}

	public static function &get_instance() {
		return self::$instance;
	}

	public function helper($name) {
		$this->CI->load->helper($name);
	}

	public function library($name, $alias = null) {
		if(is_array($name)) {
			$ret = array();
			foreach($name as $n) {
				$ret []= $this->library($n);
			}
			return $ret;
		}

		$this->CI->load->library($name);
		if($alias) {
			$this->$alias = $this->CI->$name;
		}
		else {
			$this->$name = $this->CI->$name;
		}

		return $this->CI->$name;
	}

	public function render($template, $args = array()) {
		return $this->CI->render($template, $args);
	}

	public function model($name, $alias = null) {
		if(is_array($name)) {
			$ret = array();
			foreach($name as $n) {
				$ret []= $this->model($n);
			}
			return $ret;
		}

		$this->CI->load->model($name);
		if($alias) {
			$this->$alias = $this->CI->$name;
		}
		else {
			$this->$name = $this->CI->$name;
		}

		return $this->CI->$name;
	}
}
