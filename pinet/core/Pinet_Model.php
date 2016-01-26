<?php defined('BASEPATH') or exit('No direct script access allowed');

class PaginationSupport {

	/**
	 * The select fields
	 */
	public $select = '*';
	/**
	 * The total records of this type in database
	 */
	public $dbtotal;
	/**
	 * The total records count
	 */
	public $total;

	/**
	 * The current record offset 
	 */
	public $offset;

	/**
	 * The total page item array length
	 */
	public $length;

	/**
	 * The items
	 */
	public $items;

	/**
	 * The join options
	 */
	public $joins = array();

	/**
	 * The order by parameters
	 */
	public $orderBy = array();

	/**
	 * The where parameters
	 */
	public $where = array();

    /**
     * The and where parameters are used to be with $where parameters
     * like this: $and where conditions and ($where condition1 or $where condition2)
     */
    public $and_or_where = array();

	/**
	 * The tag object for carrying data between queries
	 */
	public $tag = null; 

	/**
	 * The query object for query pagination using queries
	 *
	 * The query object has these fields (query, total_query, filtered_query)
	 */
	public $customized_query = null;

	public function __construct($total = 0, $offset = 0, $length = -1) {
		$CI = &get_instance();
		if($length != -1)
			$this->length = $length;
		else
			$this->length = $CI->config->item('pagination_length');
		$this->offset = $offset;
	}

	public function first() {
		$this->offset = 0;
	}

	public function last() {
		$this->offset = ($this->totalCount() - 1) * $this->length;
	}

	public function page($page) {
		if($page <= 0 || $page > $this->totalCount()) {
			return false;
		}
		$this->offset = ($page - 1) * $this->length;
		return $page;
	}

	public function next() {
		if($this->hasNext()) {
			p($this->current());
			$this->offset = ($this->current() + 1) * $this->length;
			return $this->current();
		}
		return false;
	}

	public function prev() {
		if($this->hasPrev()) {
			$this->offset = ($this->current() - 1) * $this->length;
			return $this->current();
		}
		return false;
	}

	public function hasNext() {
		return $this->current() < $this->totalCount();
	}

	public function hasPrev() {
		return $this->offset - $this->length > 0;
	}

	public function totalCount() {
		if($this->length) {
			return ceil($this->total / $this->length);
		}
		return 0;
	}

	public function current() {
		if($this->length) {
			$ret = ceil($this->offset / $this->length);
			return $ret? $ret: 1;
		}
		return 0;
	}

	public static function fromJson($json) {
		$obj = (array) json_decode($json);
		$ret = new PaginationSupport();
		copyArray2Obj($obj, $ret);
		return $ret;
	}

	public function toJson() {
		return json_encode($this);
	}
}

class Pinet_Model extends MY_Model {

	public function __construct($table_name = '') {
		$this->table = $table_name;
	}

	function __get($key) {
		$CI =& get_instance();
		if(isset($CI->$key)) {
			return $CI->$key;
		}
		return $this->$key;
	}

	public function setResultMode($mode = 'object') {
		$this->result_mode = $mode;
	}
	public function getOrCreate($data) {
		$this->result_mode = 'object';
		$ret = $this->get($data);
		if(isset($ret->id)) {
			return $ret;
		}

		$ret = $this->insert($data);
		return $this->load($ret);
	}

	/**
	 * Loading from the table according to the ids. If not exists, return null
	 */
	public function load($id) {
        $this->result_mode = 'object';
		if(is_array($id)) {
			$ret = array();
			foreach($id as $i) {
				$ret []= $this->load($i);
			}
			return $ret;
		}
		else {
			$result = $this->db->get_where($this->table, array('id' => $id));
			return $result->first_row();
		}
	}

    private function _callbacks($name, $params = array()) {
        $data = (isset($params[0])) ? $params[0] : FALSE;
        
        if (!empty($this->$name)) {
            foreach ($this->$name as $method) {
                $data = call_user_func_array(array($this, $method), $params);
            }
        }
        
        return $data;
    }

	public function query($sql, $args = array()) {
        $sql = $this->_callbacks('before_query', array($sql));
        if ($this->result_mode == 'object') {
            $result = $this->db->query($sql, $args)->result();
        } else {
            $result = $this->db->query($sql, $args)->result_array();
        }
        
        foreach ($result as &$row) {
            $row = $this->_callbacks('after_query', array($row));
        }
        return $result;
	}

	protected function fixPrefix($query) {
		return str_replace('$$', $this->db->dbprefix, $query);
	}

	private function queryCount($count_query) {
		$result = $this->db->query($this->fixPrefix($count_query))->result_array();
		if(count($result)) {
			foreach($result[0] as $value) {
				return $value;
			}
		}
		return 0;
	}

	private function customizeQuery($pagination) {
		ci_log('Using the customize query to query pagination', $pagination->customized_query);
		$sql = $pagination->customized_query->query.' limit '.$pagination->offset.','.$pagination->length;
		$pagination->items = $this->db->query($this->fixPrefix($sql))->result();
		$pagination->dbtotal = $this->queryCount($pagination->customized_query->dbtotal_count_query);
		$pagination->total = $this->queryCount($pagination->customized_query->total_count_query);
		return $pagination;
	}

	public function pagination($pagination) {
		if(isset($pagination->customized_query)) {
			return $this->customizeQuery($pagination);
		}
		// Get total counts
		if ((isset($pagination->where) && $pagination->where != null) || (isset($pagination->and_or_where) && $pagination->and_or_where != null)) {
			$this->result_mode = 'object';
			$this->db->select('count(*) as count');
			if(isset($pagination->joins) && count($pagination->joins) > 0) {
				foreach($pagination->joins as $join) {
					call_user_func_array(array(&$this->db, 'join'), $join);
				}
			}
			$this->db->from($this->table);
            if (isset($pagination->and_or_where) && $pagination->and_or_where != null) {
                $this->db->where_and_and($pagination->and_or_where);
                $this->db->where_or_and($pagination->where);
            }else{
                if (isset($pagination->where) && $pagination->where != null) {
                    $this->db->or_where($pagination->where);
                }
            }
			$result = $this->db->get()->result();
			$pagination->total = $result[0]->count;
		}
		else {
			if(isset($pagination->joins) && count($pagination->joins) > 0) {
				foreach($pagination->joins as $join) {
					call_user_func_array(array(&$this->db, 'join'), $join);
				}
			}
			$pagination->total = $this->count_all();
		}

		$pagination->dbtotal = $this->count_all();

		// Select the fields
		$this->db->select($pagination->select);
		// Get all the results
		$this->db->from($this->table);
        if (isset($pagination->and_or_where) && $pagination->and_or_where != null) {
            $this->db->where_and_and($pagination->and_or_where);
            $this->db->where_or_and($pagination->where);
        }else{
            if (isset($pagination->where) && $pagination->where != null) {
                $this->db->or_where($pagination->where);
            }
        }
		if(isset($pagination->joins) && count($pagination->joins) > 0) {
			foreach($pagination->joins as $join) {
				call_user_func_array(array(&$this->db, 'join'), $join);
			}
		}
		if (isset($pagination->orderBy) && $pagination->orderBy != null) {
			foreach($pagination->orderBy as $o) {
				$this->db->order_by($o[0], $o[1]);
			}
		}
		$this->db->limit($pagination->length, $pagination->offset);
		$this->result_model = 'array';
		$pagination->items = $this->db->get()->result();
		return $pagination;
	}

	public function clear() {
		return $this->db->empty_table($this->table);
	}
	public function myclear($table) {
		return $this->db->empty_table($table);
	}

	public function myget_all() { // TODO: Add unittest
		if(func_num_args() < 1) {
			$this->warn('Args not sufficient for get', func_get_args());
			return -1;
		}
        $args = & func_get_args();
		$tmp = $this->table;        
		$this->table = $args[0];
		$args = array_splice($args, 1);
		$ret = call_user_func_array(array($this,"get_all"), $args);
		$this->table = $tmp;
		return $ret;
	}
	public function myget() { // TODO: Add unittest
		if(func_num_args() < 1) {
			$this->warn('Args not sufficient for get', func_get_args());
			return -1;
		}
        $args = & func_get_args();
		$tmp = $this->table;        
		$this->table = $args[0];
		$args = array_splice($args, 1);
		$CI = &get_instance();
		$ret = call_user_func_array(array($this,"get"), $args);
		$this->table = $tmp;
		return $ret;
	}

    public function mydelete() { // TODO: Add unittest
		if(func_num_args() < 1) {
			$this->warn('Args not sufficient for delete', func_get_args());
			return -1;
		}
        $args = & func_get_args();
		$tmp = $this->table;        
		$this->table = $args[0];
		$args = array_splice($args, 1);
		$ret = call_user_func_array(array($this, "delete"), $args);
		$this->table = $tmp;
		return $ret;
	}

    public function myupdate($table, $primary_value, $data, $skip_validation = FALSE) {
		$tmp = $this->table;
		$tmpfields = $this->fields; 
		$this->fields = array();		
		$this->table = $table;
		$ret = $this->update($primary_value, $data, $skip_validation);
		$this->table = $tmp;
		$this->fields = $tmpfields;		
		return $ret;
	}

	public function select($columns) {
		if(func_num_args()) {
			$columns = func_get_args();
		}
		if(is_array($columns)) {
			$columns = implode(',', $columns);
		}
		$this->db->select($columns);
		return $this;
	}

	public function myinsert($table, $data, $skip_validation = FALSE) {
		$tmp = $this->table;
		$tmpfields = $this->fields; 
		$this->fields = array();
		$this->table = $table;
		$ret = $this->insert($data, $skip_validation);
		$this->table = $tmp;
		$this->fields = $tmpfields;
		return $ret;
	}

    public function insert($data, $skip_validation = FALSE) {
		return parent::insert((array) $data, $skip_validation);
	}

    public function orderBy($order_by){
        $this->db->order_by($order_by) ;
        return $this;
    }

}
