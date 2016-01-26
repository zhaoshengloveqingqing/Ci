<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

class Report_Model extends Pinet_Model {

	public $begin;
	public $end;
	public $timestamp_col = 'timestamp';
	public $mode = 'day';
	public $step = 1;
	public $args = array();

	public function queryRows($begin, $end) {
		$this->result_mode = 'object';
		$begin = $begin->format('Y-m-d H:i:s');
		$end = $end->format('Y-m-d H:i:s');

		if(isset($this->sql)) {
			$query_args = copy_arr($this->args);
			$query_args []= $begin;
			$query_args []= $end;
			$result = $this->query($this->fixPrefix($this->sql), $query_args);
		}
		else {
			$this->select('avg('.$this->col.') as value, '.$this->groupby_col.' as legend');
			$this->group_by($this->groupby_col);

			if(isset($this->orderby_col))
				$this->order_by($this->orderby_col);
			else
				$this->order_by($this->groupby_col);

			if($this->where) {
				if(!is_array($this->where)) {
					$this->where = (array) $this->where;
				}
				$this->where[$this->timestamp_col.' >= '] = $begin;
				$this->where[$this->timestamp_col.' < '] = $end;
			}
			else {
				$this->where = array(
					$this->timestamp_col.' >= ' => $begin,
					$this->timestamp_col.' < ' => $end
				);
			}
			$result = $this->get_all($this->where);
		}
		if(count($result)) {
			return array_map(function($item){ return array($item->legend, $item->value); }, $result);
		}
		return array();
	}

	private function getRowIndex($results, $item) {
		foreach($results as $key=>$result) {
			if($result['name'] == $item)
				return $key;
		}
		return null;
	}

	public function getSeries() {
		$times = tokenize_time($this->begin, $this->end, $this->mode, $this->step);
		$results = array();
        $categories = array();
		$CI = &get_instance();
		for($i = 0; $i <= count($times) - 2; $i++) {
            $rows = $this->queryRows($times[$i], $times[$i + 1]);
            if(count($rows))
                $categories [] = $times[$i]->format('Y-m-d');
			foreach($rows as $row) {
				$item = $row[0];
				$method = 'report_label_'.$this->name;
				if(method_exists($CI, $method)) {
					$item = $CI->$method($row[0]);
				}
				$value = doubleval($row[1]);
                $index = $this->getRowIndex($results, $item);
				if($index === null) {
					$results []= array('name' => $item, 'data' => array($value));
				}
				else {
                    $results[$index]['data'][] = $value;
				}
			}
		}
        $this->xAxis->categories = $categories;
		$this->series = $results;
	}

	public function show() {
		$this->getSeries();
		return json_encode($this);
	}
}

/**
 * The report library
 */
class Report extends Pinet_Base {

	public function show($name, $begin = null, $end = null, $args = array(), $mode = 'day') {
		$this->name = $name;
		$path = FCPATH.APPPATH.'config/report/'.$name.'.json';
		$config = null;
		if(file_exists($path)) {
			$config = json_decode(file_get_contents($path));
            if(!isset($config->chart) || !isset($config->chart->renderTo)){
                $config->chart->renderTo = $name;
            }
		}
		if($config == null) {
			show_error("Report $name's configuration is not found!");
			return;
		}
		$config->begin = $begin;
		$config->end = $end;
		$config->mode = $mode;
		$config->name = $name;
		$config->args = $args;
		$this->model = copy_new($config, 'Report_Model');
		return $this->model->show();
	}
}
