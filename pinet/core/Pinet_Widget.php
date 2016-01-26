<?php defined("BASEPATH") or exit("No direct script access allowed");

/**
 * The base class for all the widgets.
 */
class Pinet_Widget {
	public function __construct() {
		$this->CI = &get_instance();
	}

	/**
	 * Initialize the dependencies(JS, CSS, SCSS) by reading the config.
	 * Then contributing the js, css, scss and smarty plugins to the whole framework
	 */
	public function init() {
		$this->loadConfig();
		$this->initDepends();
		$this->initJS();
		$this->initCSS();
		$this->initSCSS();
		$this->initSmarty();
	}

	public function initDepends() {
		if(isset($this->config->depends)) {
			ci_log('The depends for widget %s is ', $this->config->depends, get_class($this));
			foreach($this->config->depends as $d) {
				$this->CI->load->widget($d);
			}
		}
	}

	public function initSmarty() {
		// Adding the smarty folder of this widget to the smarty scan directory
		$this->CI->addSmartyPluginDir(dirname(get_class_script_path($this)).'/smarty');
		// ?(smarty need layout tpl)
		$this->CI->addTemplateDir(dirname(get_class_script_path($this)).'/smarty');
	}

	public function initSCSS() {
		if(!isset($this->CI->sasscompiler)) {
			$this->CI->load->library('sasscompiler');
		}
		$this->CI->sasscompiler->addIncludePath(dirname(get_class_script_path($this)).'/scss');
		if(isset($this->config->scss)) {
			$scss_config = $this->config->scss;
			if(isset($scss_config->depends)) {
				foreach($scss_config->depends as $d) {
					$module = get_default((array)$d, 'module', null);
					$file = get_default((array)$d, 'file', null);
					$version = get_default((array)$d, 'version', null);
					$index = get_default((array)$d, 'index', -1);
					$this->CI->scss($file, $version, $index, $module);
				}
			}
			if(isset($scss_config->files)) {
				foreach($scss_config->files as $file) {
					$this->CI->scss($file);
				}
			}
		}
	}

	public function initJS() {
		if(isset($this->config->js)) {
			$js_config = $this->config->js;
			if(isset($js_config->depends)) {
				foreach($js_config->depends as $d) {
					$module = get_default((array)$d, 'module', null);
					$file = get_default((array)$d, 'file', null);
					$version = get_default((array)$d, 'version', null);
					$index = get_default((array)$d, 'index', -1);
					$position = get_default((array)$d, 'position', 'foot');
					$this->CI->js($file, $version, $index, $module, $position);
				}
			}

			if(isset($js_config->files)) {
				$wpath = substr(dirname(get_class_script_path($this)), strlen(FCPATH));
				foreach($js_config->files as $js) {
					$this->CI->widgetJS($wpath, $js);
				}
			}
		}
	}

	public function initCSS() {
		if(isset($this->config->css)) {
			$css_config = $this->config->css;
			if(isset($css_config->depends)) {
				foreach($css_config->depends as $css) {
					$c = (array) $css;
					$file = get_default($c, 'file');
					$version = get_default($c, 'version', null);
					$index = get_default($c, 'index', -1);
					$module = get_default($c, 'module', null);
					$this->CI->css($file, $version, $index, $module);
				}
			}
		}

		if(isset($css_config->files)) {
			$wpath = substr(dirname(get_class_script_path($this)), strlen(FCPATH));
			foreach($css_config->files as $css) {
				$this->CI->widgetCSS($wpath, $css);
			}
		}
	}

	public function loadConfig() {
		$path = dirname(get_class_script_path($this)).'/widget.json';
		if(file_exists($path)) {
			$json = file_get_contents($path);
			$this->config = json_decode($json);
			ci_log('The config is ', $this->config);
		}
		else
			show_error(lang_f('Cant\'t find configuration file for widget %s', get_class($this)));
	}
}
