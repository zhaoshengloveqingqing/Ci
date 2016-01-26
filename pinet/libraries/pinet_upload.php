<?php defined("BASEPATH") or exit("No direct script access allowed");

class Pinet_Upload extends CI_Upload {
	private $system_upload_path = 'application/static/uploads';
    public $relative_path='';
	public function __construct($props = array()) {
		parent::__construct($props);
		$this->upload_path = FCPATH.$this->system_upload_path.'/tmp';
		if(!file_exists($this->upload_path)) {
			mkdir($this->upload_path, 0755, true);
		}
	}

	public function do_upload($field = 'userfile') {
		$ret = parent::do_upload($field);
		if($ret) {
			$this->site_path = ($this->system_upload_path.'/tmp/'.$this->file_name);
            $this->relative_path = 'tmp/'.$this->file_name;
		}
		return $ret;
	}

	public function done_upload($category = '') {
		if($this->file_name) {
			$dest = $this->system_upload_path.'/'.$category.'/';
			if(!file_exists($dest)) {
				mkdir($dest, 0755, true);
			}
			$path = $this->upload_path.$this->file_name;
			$name = md5(get_timestamp().$this->file_name);
			$path_parts = pathinfo($path);
			$name.= '.'.$path_parts['extension'];
			rename($path, $dest.$name);
			$this->site_path = ($dest.$name);
			$this->relative_path = ($category.'/'.$name);
			return true;
		}
		return false;
	}
}
