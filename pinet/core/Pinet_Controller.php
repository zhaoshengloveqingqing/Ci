<?php defined('BASEPATH') or exit('No direct script access allowed');

/**
 * The stream wrapper for all the files in the CI environemnt
 */
class CIStream {

	var $path;
	var $realpath;
	var $filehandle;

	function stream_open($path, $mode, $options, &$opened_path) {
		$this->path = substr($path, 5);
		foreach(array(FCPATH.APPPATH, FCPATH.'pinet/') as $p) {
			if(file_exists($p.$this->path)) {
				$this->realpath = $p.$this->path;
				$this->filehandle = fopen($this->realpath, $mode);
				$opened_path = $this;
				return $this;
			}
		}
        return false;
    }

	function stream_stat() {
		if(isset($this->filehandle))
			return fstat($this->filehandle);
		return null;
	}

	function url_stat($path, $flags) {
		if(isset($this->filehandle))
			return fstat($this->filehandle);
		return null;
	}

    function stream_read($count) {
		if(isset($this->filehandle))
			return fread($this->filehandle, $count);
		return null;
    }

    function stream_write($data) {
		if(isset($this->filehandle))
			return fwrite($this->filehandle, $data);
		return -1;
    }

    function stream_tell() {
		if(isset($this->filehandle))
			return ftell($this->filehandle);
		return -1;
    }

    function stream_eof() {
		if(isset($this->filehandle))
			return feof($this->filehandle);
		return true;
    }

    function stream_seek($offset, $whence) {
		if(isset($this->filehandle))
			return fseek($this->filehandle, $offset, $whence);
		return -1;
    }

    function stream_metadata($path, $option, $var) {
		if(isset($this->realpath)) {
			$var = pathinfo($this->realpath);
			return true;
		}
        return false;
    }
}

stream_wrapper_register("ci", "CIStream");

class FormFieldRule {
	private $rule;
	private $arg;
	public static $MAPPING = array(
		'required' => 'required',
		'number' => 'numeric',
		'maxlength' => 'max_length',
		'minlength' => 'min_length',
		'email' => 'valid_email',
		'match' => 'matches',
		'max' => 'greater_than',
		'min' => 'less_than',
		'password' => 'alpha_numeric',
		'pattern' => 'callback_pattern',
	);

	public function __construct($rule = '', $arg = null) {
		$this->rule = $rule;
		$this->arg = $arg;
	}

	public function isRequired() {
		return $this->rule == 'required';
	}

	public function toJQValidationRule() {
		if($this->arg == null) {
			if($this->rule == 'required')
				return array('required' => '');
			if($this->rule == 'select')
				return array('type' => '');
			return array('type' => $this->rule);
		}
		else {
			if(is_array($this->arg)) { // The rules that have additional args
	    		switch ($this->rule) {
	     			case 'max':
	     			case 'min':
	     				return array('type'=>'number', $this->rule => $this->arg[0],'data-validation-'.$this->rule.'-message' => $this->arg[1]);
	     			case 'maxlength':
	     			case 'minlength':
	     			case 'pattern':
	     				return array( $this->rule => $this->arg[0],$this->rule => $this->arg[0],'data-validation-'.$this->rule.'-message' => $this->arg[1]);
	     			case 'match':
	     			case 'maxchecked':
	     			case 'minchecked':
	     			case 'regex':
	     			case 'callback':
	     			case 'ajax':
	     				return array('data-validation-'.$this->rule.'-'.$this->rule=>$this->arg[0],'data-validation-'.$this->rule.'-message' => $this->arg[1]);
	    		}
			}
			else { // The rules that have only 1 arg
	    		switch ($this->rule) {
				    case 'select':
				    	return array('type' => '', 'data-validation-'.$this->rule.'-message' => $this->arg);
	    			case 'required':
	    				return array('required'=>'', 'data-validation-'.$this->rule.'-message' => $this->arg,'data-validation-'.$this->rule.'-message' => $this->arg);
	     			case 'maxlength':
	     			case 'minlength':
	     			case 'pattern':
	     				return array( $this->rule => $this->arg);
	     			case 'match':
	     			case 'maxchecked':
	     			case 'minchecked':
	     			case 'regex':
	     			case 'callback':
	     			case 'ajax':
	     				return array('data-validation-'.$this->rule.'-'.$this->rule=>$this->arg);
	    		}
				return array('type' => $this->rule, 'data-validation-'.$this->rule.'-message' => $this->arg);
			}
		}
	}

	public function toFormValidationRule() {
		$r = $this->rule;
		if(isset(FormFieldRule::$MAPPING[$r])) {
			$r = FormFieldRule::$MAPPING[$r];
		}
		if($this->arg != null) {
			if(is_array($this->arg))
				return $r . '[' . $this->arg[0] . ']';
			return $r . '[' . $this->arg . ']';
		}
		return $r;
	}
}

class FormField {
	public $name;
	public $label;
	public $defaultValue;
	public $rules;
	public $placeholder;
	public $translateRules;
	public $field = '';
	public $state;


	public function init() {
		$this->name = $this->field;
		if(!isset($this->state))
			$this->state = 'default';
		if(isset($this->placeholder))
			$this->placeholder = $this->label;
		$r = $this->rules;
		if(!is_array($r)){
			$r = array($r);
		}
		$this->rules = array();
		foreach($r as $k => $v) {
			$rule = null;
			if(is_numeric($k)) { // If this is only an element, then it is a rule
				$rule = new FormFieldRule($v);
			}
			else {
				$rule = new FormFieldRule($k, $v);
			}
			if($rule->isRequired()) {
				$this->required = true;
				$this->label .= '<b>*</b>';
			}
			$this->rules []= $rule;
		}
	}

	public function getFormValidationRules() {
		return array_map(function($r){return $r->toFormValidationRule();}, $this->rules);
	}

	public function getJQValidationRules() {
		return	array_reduce($this->rules, function($ret, $r){return copy_arr($r->toJQValidationRule(), $ret);}, array());
	}

	public function getId() {
		return "field_".$this->name;
	}
}

class JavaScriptSrc {
	public $name;
	public $version;
	public $module;
	public $position;
	public $jsFolder;

	public function __construct($name, $version, $module, $position = 'foot') {
		$this->name = $name;
		$this->version = $version;
		$this->module = $module;
		$this->position = $position;
	}

	public function render() {
		if(stripos($this->name, ' ')) { // If the text is pure javascript, adding the script tag
			return "\t<script type='text/javascript'>\n".$this->name."\n</script>";
		}
		$CI = &get_instance();
		if($this->module == null) {
			$src = $CI->config->item('js_folder');
			if(isset($this->jsFolder)) {
				$src = $this->jsFolder;
			}
		}
		else {
			if(isset($this->version) && $this->version !== null) {
				$src = "static/lib/".$this->module.'-'.$this->version.'/js/';
			}
			else
				$src = "static/lib/".$this->module.'/js/';
		}
		$src .= $this->name.'.js';
		foreach(array('', APPPATH, 'pinet/') as $d) {
			$p = FCPATH.$d.$src;
			if(file_exists($p))
				return "<script type='text/javascript' src='".base_url($d.$src)."'></script>\n";
		}
		return '';
	}
}

class CssLink {
	public $name;
	public $version;
	public $module;
	public $is_less;
	public $cssFolder;

	public function __construct($name, $version, $module, $is_less = false) {
		$this->name = $name;
		$this->version = $version;
		$this->module = $module;
		$this->is_less = $is_less;
	}

	public function render() {
		$CI = &get_instance();
		if($this->module == null) {
			$href = $CI->config->item('css_folder');
			if(isset($this->cssFolder)) {
				$href = $this->cssFolder;
			}
		}
		else {
			if(isset($this->version) && $this->version !== null) {
				$href = "static/lib/".$this->module.'-'.$this->version.'/css/';
			}
			else
				$href = "static/lib/".$this->module.'/css/';
		}

		if($this->is_less)
			$href .= $this->name.'.less';
		else
			$href .= $this->name.'.css';

		foreach(array(APPPATH, 'pinet/') as $d) {
			$p = FCPATH.$d.$href;
			if(file_exists($p)) {
				if($this->is_less) {
					$less_js = get_ci_config('less_js');
					if($less_js) {
						return "\t".'<link rel="stylesheet/less" type="text/css" href="'.base_url($d.$href).'"/>'."\n";
					}
					$href = $CI->less_compiler->compile($d.$href);
				}
				return "\t".'<link rel="stylesheet" type="text/css" href="'.base_url($d.$href).'"/>'."\n";
			}
		}
		return '';
	}
}

class Pinet_Container {
	public $name;
	public $state;
	public $args;

	public function __construct($name = '', $state = '', $args = array()) {
		$this->name = $name;
		$this->state = $state;
		$this->args = $args;
	}
}

class Pinet_Controller extends CI_Controller {

	private $pageconfig;
	private $jsFolder = '/static/js';
	private $cssFolder = '/static/css';
	public $jsFiles;
	public $cssFiles;
	public $initJSes;
	public $smarty;
	public $use_less = false;
	public $messages = '';
	public $formFields = array();
	public $state = '';
	public $containers;
	public $_states = array();

	function __construct() {
		parent::__construct();

		$this->hackFormValidation();

		$this->load->helper(array('language', 'url', 'common', 'page','form'));
		$this->load->library(array('form_validation', 'log', 'interceptor_support'));

		if(ENVIRONMENT != 'production')
			$this->load->library('fb');

		if(get_ci_config('enable_audit')) {
			$this->log('The audit manager is enabled for this application');
			$this->load->library('audit_manager');
		}

		if(get_ci_config('enable_transaction')) {
			$this->log('The transaction manager is enabled for this application');
			$this->load->library('transaction_manager');
		}

		if(get_ci_config('enable_security') && !defined('PHPUNIT_TEST')) { // Skip the testing environment
			$this->log('The security engine is enabled for this application');
			$this->load->library('security_engine');
		}

		if(get_ci_config('enable_themes')) {
			$this->log('The themes support is enabled');
			$this->load->library('theme_manager');
		}

		// Load the default language files, if the lang is set
		if($this->messages != '')
			$this->loadLang();

		// This is for the js and css auto import support
		$this->jsFolder = $this->config->item('js_folder');
		$this->cssFolder = $this->config->item('css_folder');
		$this->bootstrapFolder = $this->config->item('bootstrap_folder');
		$this->datatablesFloder = $this->config->item('datatables_folder');
		$this->use_less = $this->config->item('use_less');

		// This is used for hacking smarty view
		$this->load->spark('smartyview/0.0.1');
		$arr = obj2array($this->smartyview);
		$this->smarty = $arr['smarty'];
		$this->smarty->template_dir = FCPATH.APPPATH.'views';
		$this->smarty->addPluginsDir(FCPATH.'/pinet/smarty_plugins');
		$this->addTemplateDir(FCPATH.APPPATH.'views/layouts/'); // Adding support for layouts

		$this->jsFiles = array();
		$this->cssFiles = array();
	}

	public function addSmartyPluginDir($path) {
		if(file_exists($path)) {
			$this->smarty->addPluginsDir($path);
		}
	}

	public function addTemplateDir($path) {
		if(file_exists($path)) {
			if(!is_array($this->smarty->template_dir))
				$this->smarty->template_dir = array($this->smarty->template_dir);
			array_unshift($this->smarty->template_dir, $path);
		}
	}

	public function getState($name = '') {
		if(isset($this->_states[$name]))
			return $this->_states[$name];
		return $this->state;
	}

	public function setState($state, $name = '') {
		if($name == '')
			$this->state = $state;
		else {
			$this->_states[$name] = $state;
		}
	}

	public function getContainer($name) {
		if(isset($this->containers[$name])) {
			return $this->containers[$name];
		}
		return null;
	}

	public function addContainer($name, $state, $args = array()) {
		if(!isset($this->containers)) {
			$this->containers = array();
		}
	 	$ret = new Pinet_Container($name, $state, $args);
		$this->containers[$name] = $ret;
		return $ret;
	}

	public function initJS($js) {
		if(!isset($this->initJSes)) {
			$this->initJSes = array();
		}
		$this->initJSes []= $js;
	}

	public function getAlerts() {
		return $this->getPinetAlert()->getAlerts();
	}

	public function addAlert($alert) {
		return $this->getPinetAlert()->addAlert($alert);
	}

	public function getPinetAlert() {
		if(!isset($this->pinet_alert)) {
			$this->load->library('pinet_alert');
		}
		return $this->pinet_alert;
	}

	public function clearAlerts() {
		$this->getPinetAlert()->clear();
	}

	protected function initForm($class, $method) {
		if($method == null)
			$method = 'index';
		$location = BASEPATH.'../'.APPPATH.'config/form/'.$class.'/'.$method.'.json';
		if(file_exists($location)) { // The configuration file is there
			$this->log('Loading the form for %s -> %s', get_class($this), $method);
			$json = json_decode(file_get_contents($location));
			foreach($json as $field) {
				// TODO: Add validation
				$field->rules = isset($field->rules)? (array) $field->rules: array();
				$this->addField($field);
			}
		}
	}

	function table($object, $label) {
		if(isset($this->fb))
			$this->fb->table($label, $object);
	}

	function dump($object, $label = null) {
		if(isset($this->fb))
			$this->fb->dump($label, $object);
	}

	function _log_format($level, $format, $obj = null, $args = null) {
		if($args == null)
			$str = $format;
		else
			$str = vsprintf($format, $args);

		$str_with_dump = $str;
		$cli = $this->input->is_cli_request();
		if($obj != null) {
			$str_with_dump .= ' --- DUMP --- :'.dump_s($obj);
		}
		switch(strtolower($level)) {
		case 'debug':
		case 'trace':
			if(!$cli && isset($this->fb))
				$this->fb->trace($str);
			$this->log->write_log('debug', $str_with_dump);
			break;
		case 'log':
			if(!$cli && isset($this->fb))
				$this->fb->log($obj, $str);
			$this->log->write_log('info', $str_with_dump);
			break;
		case 'info':
			if(!$cli && isset($this->fb))
				$this->fb->info($obj, $str);
			$this->log->write_log('info', $str_with_dump);
			break;
		case 'warn':
			if(!$cli && isset($this->fb))
				$this->fb->warn($obj, $str);
			$this->log->write_log('error', $str_with_dump);
			if(ENVIRONMENT != 'production')
				trigger_error($str_with_dump);
			break;
		case 'error':
			if(!$cli && isset($this->fb))
				$this->fb->error($obj, $str);
			$this->log->write_log('error', $str_with_dump);
			if(ENVIRONMENT != 'production')
				trigger_error($str_with_dump);
			break;
		}
	}

	function _write_log($level, $args) {
		$count = count($args);

		if($count == 0) {
			return;
		}

		$msg = $args[0];
		if($count == 1) {
			$this->_log_format($level, $msg);
		}
		else {
			$arr = $args;
			$obj = $arr[1];

			if(gettype($obj) === 'object' ||
				gettype($obj) === 'array') {
				$this->_log_format($level, $msg, $obj, array_splice($arr, 2));
			}
			else {
				$this->_log_format($level, $msg, null, array_splice($arr, 1));
			}
		}
	}

	function log() {
		$this->_write_log('log', func_get_args());
	}

	function trace() {
		$this->_write_log('trace', func_get_args());
	}

	function info() {
		$this->_write_log('info', func_get_args());
	}

	function warn() {
		$this->_write_log('warn', func_get_args());
	}

	function error() {
		$this->_write_log('error', func_get_args());
	}

	function widgetCSS($wpath, $file, $index=-1) {
		$this->css($file, null, -1, null, false, $wpath.'/css/');
	}

	function widgetJS($wpath, $file, $index=-1) {
		$this->js($file, null, -1, null, 'foot', $wpath.'/js/');
	}

	function scss($file, $version = null, $index = -1, $module = null, $scssFolder = null) {
		$this->load->library('sasscompiler');
		if(isset($version) && isset($module)) {
			foreach(array(APPPATH, 'pinet/') as $d) {
				$p = FCPATH.$d.'static/scss/lib/'.$module.'-'.$version.'/';
				$this->sasscompiler->addIncludePath($p);
				$this->sasscompiler->addSass($file, $index);
			}
		}
		else {
			$this->sasscompiler->addSass($file, $index);
		}
	}

	function js($file, $version = null, $index = -1, $module = null, $position = 'foot', $jsFolder = null) {
		foreach ($this->jsFiles as $k => $js) {
			if ($js->name === $file) {
				if ($js->version === $version) {
					return false;
				}
				else {
					trigger_error('The javascript file '.$file.' of version '.$version.' has a different version '.$js->version.' registered');
					break;
				}
			}
		}
		$js = new JavaScriptSrc($file, $version, $module, $position);
		if(isset($jsFolder)) {
			$js->jsFolder = $jsFolder;
		}
		if($index == -1)
			$this->jsFiles []= $js;
		else
			$this->jsFiles = insert_at($this->jsFiles, $js, $index);
	}

	function css($file, $version = null, $index = -1, $module = null, $less = false, $cssFolder = null) {
		foreach ($this->cssFiles as $k => $css) {
			if ($css->name === $file) {
				if ($css->version === $version) {
					return false;
				}
				else {
					trigger_error('The css file '.$file.' of version '.$version.' has a different version '.$css->version.' registered');
					break;
				}
			}
		}
		$css = new CssLink($file, $version, $module, $less);
		if(isset($cssFolder)) {
			$css->cssFolder = $cssFolder;
		}
		if($index == -1)
			$this->cssFiles []= $css;
		else
			$this->cssFiles = insert_at($this->cssFiles, $css, $index);
	}

	function resetcss($version = '2.0.0') {
		return $this->css('reset-css', $version, 0, 'reset-css');
	}

	function less($less, $version = null, $index = -1, $module = null) {
		if(!isset($this->_less_loaded)) {
			$this->_lesscss();
		}
		return $this->css($less, $version, $index, $module, true);
	}

	function _lesscss($version = '1.7.3') {
		$this->_less_loaded = true;
		return $this->js('less', $version, -1, 'less');
	}

	function init_responsive() {
		$this->jquery();
		$this->bootstrap();
		$this->resetcss();
		$this->jqueryPicture();
		$this->jqueryMobile();
	}

	function jquery_listview($version = '0.0.1') {
		$this->js('jquery-listview', $version, -1, 'jquery-listview'); // Add jquery at the first
		$this->js('jquery-listview-layout', $version, -1, 'jquery-listview');
		$this->js('string', '1.9.0', -1, 'string'); // Add jquery at the first
		$this->js('store','1.0.0',-1,'store');  //Add localstorage support
		$this->less('jquery-listview', $version, -1, 'jquery-listview');
	}

	function jquery($version = '2.1.3') {
		return $this->js('jquery.min', $version, 0, 'jquery'); // Add jquery at the first
	}

	function jqueryMobile($version = '1.0.0') {
		return $this->js('jquery-mobile', $version, -1, 'jquery-mobile');
	}

	function jqueryPicture($version = '0.9.0') {
		return $this->js('jquery-picture', $version, -1, 'jquery-picture');
	}

	function jqBootstrapValidation($version = '1.3.6') {
		$this->js('jqBootstrapValidation', $version, -1, 'jqBootstrapValidation');
	}

	function jquery_ui($version = '1.11.1') {
		$this->js('core', $version, -1, 'jquery-ui');
		$this->js('widget', $version, -1, 'jquery-ui');
		$this->js('mouse', $version, -1, 'jquery-ui');
		$this->js('selectable', $version, -1, 'jquery-ui');
		$this->css('base/all', $version, -1, 'jquery-ui');
	}

	function dataTable($version = '1.10.1') {
		$this->jquery_ui();
		$this->js('jquery.dataTables', $version, -1, 'jquery.dataTables');
		$this->css('jquery.dataTables', $version, -1, 'jquery.dataTables');
	}

	function dateTimePicker($version = '3.1.3') {
		$this->js('moment', '2.8.3', -1, 'moment');
		$this->js('bootstrap-datetimepicker', $version, -1, 'bootstrap-datetimepicker');
		$this->css('bootstrap-datetimepicker', $version, -1, 'bootstrap-datetimepicker');
	}

	function jquery_selectBoxIt($version = '3.8.0') {
		$this->js('jquery.selectBoxIt', $version, -1, 'jquery.selectBoxIt');
		$this->css('jquery.selectBoxIt', $version, -1, 'jquery.selectBoxIt');
	}

	function stickup($version = '1.0.0') {
		$this->js('stickup', $version, -1, 'stickup');
	}

	function jquery_pinet($version = '1.0.0') {
		$this->js('common', $version, -1, 'jquery.pinet');
	}

	function bootstrap($version = '3.2.0') {
		$this->js('bootstrap',$version,1,'bootstrap');
		$this->css('bootstrap',$version,0,'bootstrap');
	}

	function highcharts($version = '4.0.3') {
		$this->js('highcharts', $version, -1, 'highcharts');
	}

	function jquery_inputmask($version = '3.1.25') {
		$this->js('jquery.inputmask', $version, -1, 'jquery.inputmask');
		$this->js('jquery.inputmask.extensions', $version, -1, 'jquery.inputmask');
		$this->js('jquery.inputmask.date.extensions', $version, -1, 'jquery.inputmask');
	}

	function highstock($version = '2.0.3') {
		$this->js('highstock', $version, -1, 'highstock');
	}

	function datepicker($version = '1.3.0') {
		$this->js('bootstrap-datepicker', $version, -1, 'bootstrap-datepicker');
		$this->js('locales/bootstrap-datepicker.zh-CN', $version, -1, 'bootstrap-datepicker');
		$this->css('datepicker3', $version, -1, 'bootstrap-datepicker');
	}

	function jquery_mousewheel($version = '3.1.12') {
		$this->js('jquery.mousewheel', $version, -1, 'jquery.mousewheel');
	}

	function bootstrap_fileinput($version = '2.4.0')  {
		$this->js('fileinput', $version, -1, 'bootstrap-fileinput');
		$this->css('fileinput', $version, -1, 'bootstrap-fileinput');
	}

	function jquery_file_upload($version = '9.8.0') {
		$this->js('jquery.fileupload', $version, -1, 'jQuery-File-Upload');
		$this->js('jquery.fileupload-process', $version, -1, 'jQuery-File-Upload');
		$this->js('jquery.fileupload-validate', $version, -1, 'jQuery-File-Upload');
		$this->css('jquery.fileupload', $version, -1, 'jQuery-File-Upload');
	}

	function magicsuggest($version = '2.1.4') {
		$this->css('magicsuggest', $version, -1, 'magicsuggest');
		$this->js('magicsuggest', $version, -1, 'magicsuggest');
	}

	/**
	 * This method is to hack CI's form validation library to
	 * let it support get request as well
	 */
	function hackFormValidation() {
		if(count($_POST) == 0) {
			$_POST = $_GET;
			$_POST['__nouse__'] = '';
		}
	}

	function clearRules() {
		$this->form_validation->_field_data = array();
	}

	/**
	 * Add the form validation rules, needs 3 parameters.
	 *
	 * @name The field name
	 * @label The label for this parameter
	 * @rules The rules for this paramter, can be an array
	 */
	function _param($name, $label, $rules = 'required') {
		$the_rules = $rules;
		if(is_array($the_rules)) {
			$the_rules = implode('|', $rules);
		}
		$this->form_validation->set_rules($name, $label,$the_rules);
	}

	function getField($name) {
		foreach($this->formFields as $field) {
			if($name == $field->name) {
				if($this->state && $this->state != 'default') {
					$field->state = $this->state;
				}
				return $field;
			}
		}
		return null;
	}

	function addField($field) {
		$formField = copy_new($field, 'FormField');
		ci_log("The form field is ", $formField);
		$formField->init();
		$this->formFields [] = $formField;
		$this->_param($formField->name, $formField->label, $formField->getFormValidationRules());
	}

	function isValid() {
		return $this->form_validation->run() == TRUE;
	}

	function jsonErr($content, $headers = array()) {
		$this->jsonOut($content, 400, $headers);
	}

	function jsonOut($content, $status = 200, $headers = array()) {
		set_status_header($status);
		$headers []= "Content-Type: application/json";
		foreach($headers as $header) {
			header($header);
		}
		echo json_encode($content);
	}

	function isAjax() {
		return $this->input->is_ajax_request();
 	}

	function isDataTableRequest() {
		return $this->isAjax() && $this->input->get('draw') != '' && $this->input->get('search') != '';
	}

	function getDefaultModel() {
		if(isset($this->default_model)) {
			$default_model = $this->default_model;
			if(is_array($this->default_model)) {
				if(isset($this->default_model[get_controller_method()])) {
					$default_model = $this->default_model[get_controller_method()];
				}
			}
			if(is_subclass_of($default_model, 'Pinet_Model')) {
				return $default_model;
			}
		}
		return null;
	}

	function datatableProcess() {
		$this->load->library('datatable');
		$p = $this->datatable->buildPagination($this->input->get());
		$this->log('page', $this->datatable->transport);
		$this->log('page', $p);
		$default_model = $this->getDefaultModel();
		if($default_model) {
			$method = get_controller_method().'_datatable_customize';
			if(method_exists($this, $method)) {
				$p = $this->$method($p);
			}
			$default_model->pagination($p);
			$this->log('Queries', $this->db->queries);
			return $this->jsonOut($this->datatable->prepareOutput($p));
		}
		return $this->jsonOut(array());
	}

	function isPost() {
		return strtolower($_SERVER['REQUEST_METHOD']) == 'post';
	}

	function selectProcess() {
		$field = $this->input->get('field');
		$detail = ($this->input->get('detail'));

		if($field && $detail) {
			$field = $this->getField($field);
			if($field && isset($field->model)) {
				$this->load->model($field->model);
				$this->jsonOut(widget_select_get_options(array(), (object) $detail, $field, $this->{$field->model}));
			}
			else {
				show_error("The field $field is not valid!");
			}
		}
		else {
			show_error('The select request is not valid!');
		}
	}

	function _default_process($obj, $method, $args) {
        if(!$this->input->is_cli_request()) {
            $m = $method;
            if($this->isDataTableRequest()) {
                $m = $method.'_datatable';
                if(method_exists($obj, $m)) // If the {method}_datatble exists, using the override one
                    return $obj->$m($args);
                else if (isset($obj->default_model)) { // Else test if the default model is there, using the normal one
					$this->default_model = $obj->default_model;
                    return $this->datatableProcess();
				}
                // We don't have any datatable method here, fallback to the normal routine
            }
            // Initiliase the form support
            $this->initForm(get_class($obj),$method);

            if ($this->isAjax()) { // If the request is ajax, try to find the method suffix with ajax
                $m = $method.'_ajax';
				if($this->input->get_request_header('Pinet', TRUE) == 'Select') {
					$m = $method.'_select';

					if(method_exists($obj, $m)) {
						return $obj->$m($args);
					}

					return $this->selectProcess();
				}
            }
            if ($this->isPost()) { // If the request is a form post, try to find the method suffix with form suffix
                $m = $method.'_form';
            }

            if($m != $method && method_exists($obj, $m)) {
                $this->log('Calling method %s instead of %s', $m, $method);
				$GLOBALS['CURRENT_METHOD'] = $m;
				$GLOBALS['CURRENT_ARGS'] = $args;
                return call_user_func_array(array($obj, $m), $args);
            }
        }
		$GLOBALS['CURRENT_METHOD'] = $method;
		$GLOBALS['CURRENT_ARGS'] = $args;
		return call_user_func_array(array($obj, $method), $args);
	}

	function _process($method, $args) {
		return $this->_default_process($this, $method, $args);
	}

	function is_method_valid($method) {
		return method_exists($this, $method);
	}

	function _get_method($method, &$args) {
		if(method_exists($this, $method)) {
			return $method;
		}
		else {
			if(get_class($this) != 'Page') { // Skip the page viewing
				$this->warn('The method of %s is not exists, change to index', $method);
			}
			array_unshift($args, $method);
		}
		return 'index';
	}

	function processAutoload() {
		if (defined('ENVIRONMENT') AND file_exists(APPPATH.'config/'.ENVIRONMENT.'/autoload.php')) {
			include(APPPATH.'config/'.ENVIRONMENT.'/autoload.php');
		}
		else {
			include(APPPATH.'config/autoload.php');
		}

		if(!isset($autoload)) {
			return FALSE;
		}

		if(isset($autoload['widgets'])) {
			foreach($autoload['widgets'] as $w) {
				$this->load->widget($w);
			}
		}
	}
	/**
	 * Overload the CI's default method to support ajax, form and the interceptors
	 */
	function _remap($method, $args) {
		$this->processAutoload();
		set_breadscrum(); // Setting the breadscrums
		$method = $this->_get_method($method, $args);
		// Checking for the interceptors
		$interceptors = $this->interceptor_support->getInterceptors($method);
		$this->interceptors = $interceptors; // Save the interceptors for render to use

		// For before interceptor
		$failed = false;
		foreach($interceptors['before'] as $before) {
			if(!$before->intercept($method, $args)) { // If any before inteceptor thinks this execution should be fail, then fail it
				$failed = true;
				if(isset($before->error_message)) {
					$this->error('Failed to pass the %s when calling method %s of %s of error message %s with args', $args, $before->toString(), $method, get_class($this), $before->error_message);
					trigger_error($before->error_message);
				}
				else {
					$this->error('Failed to pass the %s when calling method %s of %s of with args', $args, $before->toString(), $method, get_class($this));
				}
				break;
			}
		}

		if($failed) {
			return false;
		}

		// For around interceptor
		if(count($interceptors['around']) > 0) {
			$around = $interceptors['around'][0];
			if(count($interceptors['around']) > 1) {
				$this->warn('The around interceptor for method %s is bigger than 1', $args, $method);
			}
			return $around->intercept($method, $args);
		}

		// Using git to test if the file has been changed, test this after the interceptors
		// In order pass the security check.
        if(file_exists(FCPATH.APPPATH.'.git/HEAD')) {
            $ref = file_get_contents(FCPATH.APPPATH.'.git/HEAD');
            $ref = explode(' ', $ref);
            $ref = trim($ref[1]);
            $ref = FCPATH.APPPATH.'.git/'.$ref;
        }

        if(get_ci_config('enable_cache') && !isset($this->disable_cache) && isset($ref) && cache_support($ref))
            return;

		// Run the original method
		$ret = $this->_process($method, $args);

		// Run the after method
		foreach($interceptors['after'] as $after) {
			$after->intercept($method, $args);
		}
		return $ret;
	}

	function loadLang() {
		$lang = get_lang(); // Get the language context
		$this->log('Loading the language file for %s', $lang);
		if($this->messages != '') { // If set the lang manually, load the lang from that file
			return $this->lang->load($this->messages, $lang);
		}
		$name = strtolower(get_class($this));
		return $this->lang->load($name); // Load the lang file by controller class name by default
	}

	function render($template, $args = array()) {
		$t = '';
		if(isset($this->title)) {
				$t = $this->title;
		}
		if(isset($args['title']))
			$t = ($args['title']);

		if(isset($this->navigation)) {
			$args['navigations'] = $this->navigation->getNavigations();
		}

		if(get_ci_config('less_js')) {
			$args['use_less_js'] = true;
		}

		if(is_array($t)) {
			$args['title'] = lang_f($t[0], $t[1]);
		}
		else {
			$args['title'] = lang($t);
		}
		if(!$this->input->get('nohead'))
			$args['has_head'] = true;
		else
			$args['has_head'] = false;

		if(isset($this->interceptors) && isset($this->interceptors['render'])) {
			foreach($this->interceptors['render'] as $i) {
				$i->intercept($template, $args);
			}
		}
		$this->smartyview->render($template.'.tpl', $args);
	}

	function setLang($lang) {
		$this->input->set_cookie(array(
			'name'   => 'pinet_language',
			'value'  => $lang,
			'expire' => '311040000'
		));
	}
}

class Pinet_Base {
	protected $CI;

	public function __construct() {
		$this->CI = &get_instance();
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

	public function helper($name) {
		if(is_array($name)) {
			$ret = array();
			foreach($name as $n) {
				$ret []= $this->helper($n);
			}
			return $ret;
		}

		$this->CI->load->helper($name);
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
