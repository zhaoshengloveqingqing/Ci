<?php defined("BASEPATH") or exit("No direct script access allowed");

class MY_Migration extends CI_Migration {

	public function __construct($config = array()) {
		parent::__construct();
		// Loading the configuration fo migration
		$this->config->load('migration');

		foreach ($this->config->config as $key => $val) {
			$this->{'_' . $key} = $val;
		}

		log_message('debug', 'Migrations class initialized');

		// Are they trying to use migrations while it is disabled?
		if ($this->_migration_enabled !== TRUE) {
			show_error('Migrations has been loaded but is disabled or set up incorrectly.');
		}

		// If not set, set it
		$this->_migration_path == '' AND $this->_migration_path = APPPATH . 'migrations/';

		// Add trailing slash if not set
		$this->_migration_path = rtrim($this->_migration_path, '/').'/';

		// Load migration language
		$this->lang->load('migration');

		// They'll probably be using dbforge
		$this->load->dbforge();

		// If the migrations table is missing, make it
		if ( ! $this->db->table_exists('migrations')) {
			$this->dbforge->add_field(array(
				'version' => array('type' => 'INT', 'constraint' => 3),
			));

			$this->dbforge->create_table('migrations', TRUE);

			$this->db->insert('migrations', array('version' => 0));
		}
	}

	public function helper($helpers) {
		$CI = &get_instance();
		$CI->load->helper($helpers);
	}

	public function library($name, $alias = null) {
		$CI = &get_instance();
		if(is_array($name)) {
			$ret = array();
			foreach($name as $n) {
				$ret []= $this->library($n);
			}
			return $ret;
		}

		$CI->load->library($name);
		if($alias) {
			$this->$alias = $CI->$name;
		}
		else {
			$this->$name = $CI->$name;
		}

		return $CI->$name;
	}

	public function model($name, $alias = null) {
		$CI = &get_instance();
		if(is_array($name)) {
			$ret = array();
			foreach($name as $n) {
				$ret []= $this->model($n);
			}
			return $ret;
		}

		$CI->load->model($name);
		if($alias) {
			$this->$alias = $CI->$name;
		}
		else {
			$this->$name = $CI->$name;
		}

		return $CI->$name;
	}
	public function add_key($key = '', $primary = FALSE) {
		$this->dbforge->add_key($key, $primary);
	}

	public function add_field($field = '') {
		$this->dbforge->add_field($field);
	}

	public function create_database($db_name) {
		$this->dbforge->create_database($db_name);
	}

	public function drop_database($db_name) {
		$this->dbforge->drop_database($db_name);
	}

	public function create_table($table = '', $if_not_exists = FALSE) {
		$this->dbforge->create_table($table, $if_not_exists);
	}

	public function drop_tables() {
		$this->drop_table(func_get_args());
	}

	public function drop_table($table_name) {
		if(is_array($table_name)) {
			foreach($table_name as $t) {
				$this->dbforge->drop_table($t);
			}
		}
		else
			$this->dbforge->drop_table($table_name);
	}

	public function rename_table($table_name, $new_table_name) {
		$this->dbforge->rename_table($table_name, $new_table_name);
	}

	public function add_column($table = '', $field = array(), $after_field = '') {
		$this->dbforge->add_column($table, $field, $after_field);
	}

	public function drop_column($table = '', $column_name = '') {
		$this->dbforge->drop_column($table, $column_name);
	}

	public function modify_column($table = '', $field = array()) {
		$this->dbforge->modify_column($table, $field);
	}

	public function add_id() {
		$this->add_field(array(
			'id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => TRUE,
				'auto_increment' => TRUE
				)
			)
		);
		$this->add_key('id', true);
	}

	public function bool($default = 0) {
		return array('type' => 'bool', 'default' => $default);
	}

	public function timestamp($null = TRUE, $default = TRUE) {
        if(!$default){
            return array(
                'type' => 'timestamp',
                'null' => $null
            );
        }
		return array(
            'type' => 'timestamp',
            'null' => $null,
            'default'=>'CURRENT_TIMESTAMP'
        );
	}

    public function datetime($null = TRUE) {
        return array(
            'type' => 'datetime',
            'null' => $null
        );
    }

    public function date($null = TRUE) {
        return array(
            'type' => 'date',
            'null' => $null
        );
    }

	public function text($null = TRUE, $default = null) {
		return array(
			'type' => 'text',
			'null' => $null
		);
	}

	public function varchar($constraint = 64, $uniq = FALSE, $null = TRUE, $default = null) {
		$arr = array(
			'type' => 'varchar',
			'constraint' => $constraint,
			'uniq'=>$uniq,
			'null' => $null
		);
		if($default)
			$arr['default'] = $default;
		return $arr;
	}

	public function int($default = 0, $constraint = 11, $unsigned = TRUE, $null = TRUE) {
		return array(
			'type' => 'int',
			'constraint' => $constraint,
			'unsigned' => $unsigned,
			'null' => $null,
			'default' => $default
		);
	}

	public function tint($default = 0, $constraint = 1, $unsigned = TRUE, $null = TRUE) {
		return array(
			'type' => 'tinyint',
			'constraint' => $constraint,
			'null' => $null,
			'default' => $default
		);
	}

	public function decimal($constraint, $null = TRUE){
		return array(
			'type' => 'decimal',
			'constraint' => $constraint,
			'null' => $null
		);
	}

	public function add_table($table_name, $fields, $keys = array(), $has_id = TRUE) {
		// Adding ID
		if($has_id)
			$this->add_id();

		// Adding Fields
		$this->add_field($fields);

		// Adding keys
		$this->add_key($keys);

		// Now creating the table
		$this->create_table($table_name);
	}
}
