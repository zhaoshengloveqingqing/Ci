<?php defined('BASEPATH') or exit('No direct script access allowed');

class Pinet_PHPUnit_Framework_TestCase extends PHPUnit_Framework_TestCase {
	protected $CI;
	
	public function setUp() {
		$mute = (getenv('MUTE_PHPUNIT'));
		$ref = new ReflectionClass($this);
		$func = $this->getName();
		if(!$mute && $func != 'testStub')
			echo "\n----------".$ref->name." | ".$func."----------\n";
		$this->CI =& get_instance();
		$this->doSetUp();
		$this->CI->load->helper(array('url', 'test'));
		$this->CI->load->spark('curl/1.3.0');
		$this->CI->curl->http_header('User-Agent', fake_uagent());
	}

	public function helper($name) {
		$this->CI->load->helper($name);
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

	public function url($url) {
		return site_url($url);
	}

	public function _urlGet($url, $param = array()) {
		return $this->CI->curl->simple_get($url, $param, array(CURLOPT_FAILONERROR => FALSE));
	}

	public function _urlPost($url, $param = array()) {
		return $this->CI->curl->simple_post($url, $param, array(CURLOPT_FAILONERROR => FALSE));
	}

	public function getJSON($url, $param = array(), $debug = FALSE) {
        $result = $this->_urlGet($this->url($url), $param);
        if($debug) {
            echo $result;
        }
		return json_decode($result);
	}

	public function postJSON($url, $param = array()) {
		return json_decode($this->_urlPost($this->url($url), $param));
	}

	public function tearDown() {
		$ref = new ReflectionClass($this);
		$this->doTearDown();
		$func = $this->getName();
		$mute = (getenv('MUTE_PHPUNIT'));
		if(!$mute && $func != 'testStub')
			echo "\n==========".$ref->name." | ".$func."==========\n";
		if (ob_get_length() == 0 ) {
			ob_start();
    }
	}

	public function testStub() {
	}

	public function doSetUp() {
	}

	public function doTearDown() {
	}
}
