<?php defined('BASEPATH') or exit('No direct script access allowed');


class DataTableColumn {
    public $cellType = 'td';
	public $className = null;
	public $contentPadding = 0;	
	public $data = null;
	public $defaultContent = null;
	public $name = null;
	public $orderable = true;
	/*
	public $orderData = null;
	public $orderSequence = 'asc';
	 */
	public $searchable = true;
	public $title = null;
	public $type = 'string';
	public $visible = true;
	public $width = 10;

	public function __construct(
		$data = null,
		$name = null,
		$title = null,
		$width = 10,
		$defaultContent = null,
		$className = null,
		$cellType = 'td',
		$orderable = true,
		$searchable = true,
		$orderSequence = 'asc',
		$type = 'string',
		$visible = true,
		$contentPadding = 0) {

		$this->cellType = $cellType;
		$this->className = $className;
		$this->contentPadding = $contentPadding;
		$this->data = $data;
		$this->defaultContent = $defaultContent;
		$this->name = $name;
		$this->orderable = $orderable;
		$this->searchable = $searchable;
		$this->title = $title;
		$this->type = $type;
		$this->visible = $visible;
		$this->width = $width;
	}

	public static function from_object($obj) {
		$ret = copy_new($obj, "DataTableColumn");
		if(isset($ret->data) && $ret->data !== null) {
			if(strpos($ret->data, '.') !== false) { // Replace the . to _ to avoid the bug
				$ret->dataCol = $ret->data;
				$ret->data = str_replace('.', '_', $ret->data);
			}
		}
		if(isset($ret->refer) && $ret->refer !== null) {
			if(strpos($ret->refer, '.') !== false) { // Replace the . to _ to avoid the bug
				$ret->referCol = $ret->refer;
				$ret->refer = str_replace('.', '_', $ret->refer);
			}
		}
		if(isset($ret->action)) {
			$ret->action = site_url($ret->action);
			if(isset($ret->toggle)) {
				$ret->render = 'datatable_toggle_column';
			}
			else
				$ret->render = 'datatable_action_column';
		}
		else if(isset($ret->refer)) {
			$ret->render = 'datatable_action_column';
		}

		return $ret;
	}
}

class DataTableColumns implements JsonSerializable {
	public $columns = array();

	public function jsonSerialize() {
		return $this->columns;
	}
	
	public static function from_object($columns) {
		$ret = new DataTableColumns();
		$CI = &get_instance();
		if(gettype($columns) === 'array') {
			foreach($columns as $column) {
				if(isset($CI->security_engine) && $CI->security_engine->validate($column) == 'deny') { // If we do have security engine, and the security engine says that the column should not be shown, we won't show it.
					$CI->log('Won\'t show column for this security configuration', $column);
					continue;
				}
				$ret->columns []= DataTableColumn::from_object($column);
			}
		}
		return $ret;
	}

}

/**
 * The model for DataTable
 */
class DataTable_Model extends Pinet_Model {

	public $ajax = '';
	public $columns = array();
	public $info = null;
	public $processing = true;
	public $searching = true;
	public $serverSide = true;
	public $stateSave = true;
	public $data = null;
	public $deferLoading = null;
	public $selectType = "multi";
	public $conditions = null;
	public $customized_query = null;

	public function __construct() {
		parent::__construct();
		$this->columns = array();
	}

	public function fixColumns() {
		if(isset($this->columns) && (gettype($this->columns) == 'array' 
			|| get_class($this->columns) !== 'DataTableColumns')) {
			// This must be initialzed by JSON or array object
			$this->columns = DataTableColumns::from_object($this->columns);
		}
	}

	public function init() {
		$class = get_class(get_instance());
		$method = $this->uri->segment(2);
		if($method == null)
			$method = 'index';		
		$location = BASEPATH.'../'.APPPATH.'config/datatable/'.$class.'/'.$method.'.json';
		if(file_exists($location)) { // The configuration file is there
			$json = file_get_contents($location);
			$this->fromJSON($json); // Initialize this model using this configuration
		}
		$this->ajax = site_url($this->uri->uri_string);
	}

	private function fromJSON($json) {
		copy_object(json_decode($json), $this);
		$this->fixColumns(); // Fix the columns if needed (when initialize from JSON)
		return $this;
	}
}
