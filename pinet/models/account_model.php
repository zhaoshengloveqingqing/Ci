<?php defined('BASEPATH') or exit('No direct script access allowed');

class Account_Model extends Pinet_Model{
	public function __construct(){
		parent::__construct('accounts');	
	}

	public function getAccount($userid){
		$this->result_mode = 'object';
		return $this->get(array(
			'user_id' => $userid
		));
	}

    public function getAccountByID($id){
        $this->result_mode = 'object';
        return $this->load($id);
    }
}