<?php defined("BASEPATH") or exit("No direct script access allowed");

class Mesh_Page {
	public function __construct() {
		$this->CI = &get_instance();
		$this->mesh_cache = FCPATH.APPPATH.'cache/mesh/';
		if(!file_exists($this->mesh_cache)) {
			mkdir($this->mesh_cache, 0755, true);
		}
		$this->CI->load->model('mesh_model');
		$this->model = $this->CI->mesh_model;
	}

	public function processLayout() {
		if(isset($this->mesh_page)) {
			if(isset($this->mesh_page->layout)) {
				$this->layout = $this->model->buildLayout($this->mesh_page->layout);
				$path = $this->layout->getTemplatePath();
				if(file_exists($path)) {
					$this->out []= '{extends file="'.$this->mesh_page->layout.'.tpl"}';
					foreach($this->layout->getBlocks() as $block) {
						$this->processBlock($block);
					}
				}
			}
		}
	}

	public function processBlock($block) {
		$name = $this->mesh_page->name;
		$widgets = $this->model->getWidgets($name, $block->name);
		if(!count($widgets)) // If we don't have any widgets, skip this one
			return;
		$b = '{block name='.$block->name.'';
		if($block->type == 'append') {
			$b .= ' append}';
		}
		else {
			$b .= '}';
		}
		$this->out []= $b;
		foreach($widgets as $widget) {
			$this->processWidget($widget);
		}
		$this->out []= '{/block}';
	}

	public function getWidgetType($name) {
		foreach($this->CI->smarty->plugins_dir as $dir) {
			if(file_exists($dir.'/function.'.$name.'.php')) {
				return 'function';
			}
			if(file_exists($dir.'/block.'.$name.'.php')) {
				return 'block';
			}
		}
		return null;
	}

	public function processWidget($widget) {
		$w = '{'.$widget->tag;
		if(isset($widget->parameters)) {
			$attr = json_decode($widget->parameters);
			foreach($attr as $k => $v) {
				if(is_numeric($k)) {
					$text = $v;
				}
				else if(is_object($v)) {
					if(isset($v->type)) {
						switch($v->type) {
						case 'const':
						case 'var':
							$w .= ' '.$k.'='.$v->value;
							break;
						}
					}
				}
				else {
					$w .= ' '.$k.'="'.$v.'"';
				}
			}
		}
		$w .= '}';
		if(isset($text) || $this->getWidgetType($widget->widget) == 'block') {
			if(isset($text))
				$w .= $text;
			$w .= '{/'.$widget->tag.'}';
		}
		$this->out []= $w;
	}

	public function initWidgets() {
		if(isset($this->mesh_page)) {
			foreach($this->model->getWidgetNames($this->mesh_page->name) as $widget) {
				if($widget)
					$this->CI->load->widget($widget);
			}
		}
	}

	public function render($name, $args = array()) {
		$this->out = array();
		$this->mesh_page = $this->model->getMeshPage($name);
		if(!$this->mesh_page) {
			show_error(lang_f('Can\'t find mesh page named %s to render', $name));
			return;
		}

		$this->initWidgets();
		$this->processLayout();
		$mesh_page = $this->mesh_cache.$name.microtime();
		$content = implode("\n", $this->out);
		file_put_contents($mesh_page.'.tpl', $content);
		$this->CI->render($mesh_page, $args);
	}
}
