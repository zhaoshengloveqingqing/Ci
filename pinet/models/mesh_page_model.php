<?php defined("BASEPATH") or exit("No direct script access allowed");

class Mesh_Page_Model extends Pinet_Model {
	public function __construct() {
		parent::__construct('mesh_pages');
	}

	public function getWidgetNames($name) { 
		$id = $this->getMeshPageID($name);
		if($id != -1) {
			$this->db->select('widget');
			$this->db->distinct();
			$this->db->where(array(
				'page_id' => $id
			));

			$this->db->from('mesh_page_widgets');
			$ret = $this->db->get();
			return array_map(function($data){return $data->widget;},$ret->result());
		}
		return array();
	}

	public function getMeshPageID($name) {
		$this->result_mode = 'object';
		$result = $this->get('name', $name);
		if(isset($result->id))
			return $result->id;
		return -1;
	}

	public function removeWidget($name, $tag, $widget) {
		$id = $this->getMeshPageID($name);
		if($id != -1) {
			$this->mydelete('mesh_page_widgets', array(
				'tag' => $tag,
				'page_id' => $id,
				'widget' => $widget
			));
		}
	}

	public function addWidget($name, $tag, $widget, $block, $parameters = array(), $power = 0) {
		$id = $this->getMeshPageID($name);
		if($id != -1) {
			return $this->myinsert('mesh_page_widgets', array(
				'page_id' => $id,
				'tag' => $tag,
				'widget' => $widget,
				'power' => $power,
				'block' => $block,
				'parameters' => json_encode($parameters)
			));
		}
	}

	public function getWidgets($name, $block = null) {
		$id = $this->getMeshPageID($name);
		if($id != -1) {
			$this->db->where(array('page_id' => $id));
			if($block != null)
				$this->db->where(array('block' => $block));
			$this->db->from('mesh_page_widgets');
			$this->db->order_by('power desc');
			return $this->db->get()->result();
		}
	}
}
