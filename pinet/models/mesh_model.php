<?php defined("BASEPATH") or exit("No direct script access allowed");

class MeshLayoutBlock {
	public $name;
	public $type;
	public $description;

	public function __construct($name, $description, $type = 'default') {
		$this->name = $name;
		$this->description = $description;
		$this->type = $type;
	}
}

class MeshLayout {
	public $layoutFolder;
	public $name;
	public $author;
	public $version;
	public $description;

	public function __construct($name, $layoutFolder) {
		$this->name = $name;
		$this->layoutFolder = $layoutFolder;
	}

	public function getBlocks() {
		$blocks = array();
		foreach($this->blocks as $block) {
			$blocks []= $block;
		}

		if(isset($this->parent)) { // This layout has parent
			foreach($this->parent->getBlocks() as $block) {
				$found = false;
				foreach($blocks as $b) {
					if($b->name == $block->name) { // This block is same with parent, using parent's description
						$b->description = $block->description;
						$found = true;
						break;
					}
				}
				if(!$found) // We don't have this block in current layout
					$blocks []= $block;
			}
		}
		return $blocks;
	}

	public function getTemplatePath() {
		return $this->layoutFolder.$this->name.'.tpl';
	}

	public function parse() {
		if(isset($this->blocks)) {
			return true;
		}

		$content = file_get_contents($this->getTemplatePath());
		$state = '';
		$this->blocks = array();
		foreach(explode("\n", $content) as $line) {
			$l = trim($line);
			if(strpos($l, '{block') !== false) {
				$state = 'block';
			}
			if(strpos($l, '{extends') !== false) {
				$state = 'extend';
			}
			if($state != 'block') {
				if(strpos($l, '{**') !== false) {
					$state = 'meta';
				}
				if(strpos($l, '**}') !== false) {
					$state = 'meta_end';
				}
			}
			switch($state) {
			case 'extend':
				if(preg_match_all('/\{extends file=\'(.+).tpl\'\}/', $l, $names)) {
					ci_log('The parent is ', $names);
					$this->parent = new MeshLayout($names[1][0], $this->layoutFolder);
					$this->parent->parse();
				}
				break;
			case 'block':
				if(preg_match_all('/\{block name=(\w+)\}/', $l, $names)) {
					if(count($names[0]) == 1) { // We only support for 1 block per line
						$block_name = $names[1][0];
						$block_comment = '';
						$block_type = 'default';
						if(preg_match_all('/\{\*\*\s*(.+)\s*\*\*\}/', $l, $comments)) {
							$block_comment = trim($comments[1][0]);
						}
						if(strpos($l, '{/block}') === false) {
							$block_type = 'append';
						}
						else {
							$state = 'block_end';
						}
						$this->blocks []= new MeshLayoutBlock($block_name, $block_comment, $block_type);
					}
				}
				break;
			case 'meta':
				$i = strpos($l, '@');
				if($i !== false) {
					$l = substr($l, $i);
					$kv = explode(':', $l);
					if(count($kv) > 1) {
						$k = trim(str_replace('@', '', $kv[0]));
						$this->$k = trim($kv[1]);
					}
				}
				break;
			}
		}
	}

	public function isValid() {
		return file_exists($this->getTemplatePath());
	}
}

class Mesh_Model extends Pinet_Model {
	public function __construct() {
		parent::__construct();
		$CI = &get_instance();
		$CI->load->model('mesh_page_model');
		$this->layoutFolder = FCPATH.APPPATH.'views/layouts/';
	}

	public function loadPage($id) {
		return $this->mesh_page_model->load($id);
	}

	public function getWidgetNames($name) {
		return $this->mesh_page_model->getWidgetNames($name);
	}

	public function getWidgets($name, $block = null) {
		return $this->mesh_page_model->getWidgets($name, $block);
	}

	public function addWidget($name, $tag, $widget, $block, $parameters = array()) {
		return $this->mesh_page_model->addWidget($name, $tag, $widget, $block, $parameters);
	}

	public function removeWidget($name, $tag, $widget) {
		$this->mesh_page_model->removeWidget($name, $tag, $widget);
	}

	public function buildLayout($name) {
		$layout = new MeshLayout($name, $this->layoutFolder);
		if($layout->isValid()) {
			$layout->parse(); // Parse the layout's information
			return $layout;
		}
		return null;
	}

	public function addMeshPage($page) {
		$o = (object) $page;
		if(isset($o->layout_name)) {
			return $this->mesh_page_model->insert(array(
				'layout' => $o->layout_name,
				'name' => $o->page_name,
				'notes' => $o->page_notes
			));
		}
		return -1;
	}

	public function clear() {
		$this->mesh_page_model->clear();
	}

	public function getMeshPage($name) {
		$this->mesh_page_model->result_mode = 'object';
		$result = $this->mesh_page_model->get('name', $name);
		if(isset($result->id))
			return $result;
		return null;
	}
}
