<?php defined('BASEPATH') or exit('No direct script access allowed');


class DataTableTransport {
	public $draw; // The draw parameter
	public $recordsTotal; // The total results in database
	public $recordsFiltered; // The current total
	public $start; // The offset
	public $length; // The length
	public $search;
	public $columns;
	public $order;
	public $joins;

	public function getField($col, $type="all") {
		if($type == 'all' || (isset($col[$type.'able']) && $col[$type.'able'] == 'true')) {
			if(isset($col['data'])) {
				return $col['data'];
			}
			if(isset($col['refer'])) {
				return $col['refer'];
			}
		}
	}

	public function toPagination() {
		$p = new PaginationSupport(0, $this->start, $this->length);
		$selects = array();

		$CI = &get_instance();
		$orig_columns = $CI->datatable->model->columns;
		foreach($this->columns as $col) {
			$field = $this->getField($col);
			$dataCol = null;
			foreach($orig_columns->columns as $c) {
				if(isset($c->refer) && $c->refer == $field) {
					if(isset($c->referCol)) {
						$selects []= $c->referCol.' as '.$field;
						$referCol = $c->referCol;
					}
					else
						$selects []= $field;
					break;
				}
			}
			foreach($orig_columns->columns as $c) {
				if($c->data == $field) {
					if(isset($c->dataCol)) {
						$selects []= $c->dataCol.' as '.$field;
						$dataCol = $c->dataCol;
					}
					else
						$selects []= $field;

					if(isset($c->refer)) {
						if(isset($c->referCol)) {
							$selects []= $c->referCol.' as '.$c->refer;
						}
						else
							$selects []= $c->refer;
					}
					break;
				}
			}
			if($this->search != null && isset($this->search['value']) // Using search to set where
				&& $this->search['value'] != '' && $this->getField($col, 'search') != null) {
				$value = $this->search['value'];
				if($dataCol != null)
					$p->where[$dataCol] = $value;
				else
					$p->where[$field] = $value;
			}
		}

		if(isset($CI->datatable->model->conditions)) {
			ci_log('Filtering the datatable using conditions', $CI->datatable->model->conditions);
			foreach($CI->datatable->model->conditions as $k => $v) {
				$p->and_or_where[$k] = $v;
			}
		}

		if($this->joins != null) {
			$p->joins = $this->joins;
		}

		if($this->order != null) {
			foreach($this->order as $o) {
				$col = $o['column'];
				$field = $this->getField($this->columns[$col], 'order');
				if($field == null)
					continue;
				$dir = $o['dir'];

				$p->orderBy []= array($field, $dir);
			}
		}

		$p->select = implode(',', $selects);

		$p->tag = $this->draw;// Setting the pagination tag to remember the draw

		if(isset($CI->datatable->model->customized_query)) {
			$p->customized_query = $CI->datatable->model->customized_query;
		}

		return $p;
	}

	public function fromPagination($p) {
		if($p == null || gettype($p) === 'object'
			|| get_class($p) === 'PaginationSupport') { // We only support PaginationSupport
			$this->draw = $p->tag;
			$this->data = $p->items;
			$this->recordsTotal = $p->dbtotal;
			$this->recordsFiltered = $p->total;
			$this->start = $p->offset;
			$this->length = $p->length;
			return $this;
		}
		return array();
	}
}

class DataTable {
	public $transport;

	public function __construct($config = array()) {
		$CI = &get_instance();
		$CI->load->model('datatable_model'); // Load the databale model
		$this->model = $CI->datatable_model;
		$this->model->init(); // Initialise the datatable using the configuration file
		$CI->dataTable(); // Add the js and css

		$js = 'window.datatable_conf = '.str_replace('\/', '/', json_encode($CI->datatable_model)).";\n";
		$js .= <<<EOT
window.datatable_conf = $.extend(true, window.datatable_conf, {
	search: {
		regex: true
	},
	language: {
		search: "",
		paginate: {
			previous: '<i class="glyphicon glyphicon-backward"></i>',
			next: '<i class="glyphicon glyphicon-forward"></i>'
		}
	},
	autoWidth: false
});
window.pinet_datatables = [];
$('.datatable').each(function(i){
	$(this).dataTable(window.datatable_conf);
	var api = $(this).DataTable();
	window.pinet_datatables[i] = {};
	window.pinet_datatables[i]['api'] = $(this).DataTable();
	window.pinet_datatables[i]['el'] = $(this);
});
EOT;
		$js .= read_config_file('datatable/datatable.js');
		if($this->model->selectType == 'multi') {
			$js .= <<<EOT
			$("#datatable tbody").selectable({
				delay: 1
			});
EOT;
		}
		$CI->js($js);
	}

	public function show() {
		$CI = &get_instance();
		// print_r(str_replace('\/', '/', json_encode($CI->datatable_model)));die;
		echo str_replace('\/', '/', json_encode($CI->datatable_model));
	}

	public function getTransport($data) {
		$this->transport = copy_new($data, 'DataTableTransport');
		if(isset($this->model->joins))
			$this->transport->joins = $this->model->joins;
		return $this->transport;
	}

	public function prepareOutput($p) {
		if($p !== null || gettype($p) === 'object'
			|| get_class($p) === 'PaginationSupport') { // We only support PaginationSupport
			return $this->transport->fromPagination($p);
		}
		else {
			trigger_error('The data that trying output is not PaginationSupport!');
		}
	}

	public function buildPagination($data) {
		return $this->getTransport($data)->toPagination();
	}

    public function buildCustomizeData($data){
        $CI = &get_instance();
        $dt = new DataTableTransport();
        $count = count($data);
        $dt->draw = 1;
        $dt->data = $data;
        $dt->recordsTotal = $count;
        $dt->recordsFiltered = $count;
        $dt->start = 1;
        $dt->length = 100;
        return $CI->jsonOut($dt);
    }
}
