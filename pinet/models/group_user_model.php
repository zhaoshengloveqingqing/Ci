<?php defined('BASEPATH') or exit('No direct script access allowed');

class Group_User_Model extends Pinet_Model {
	public function __construct(){
		parent::__construct('groups_users');
        $this->load->model(array('group_model'));
	}

    public function getUserGroupID($user_id){
        $this->result_mode = 'object';
        $groups = array_map(function($i){ return $i->group_id;}, $this->get_all(array(
            'user_id'=>$user_id
        )));
        if(count($groups)){
            return $groups[0];
        }
        return -1;
    }

    public function isAdmin($user_id){
        $group = $this->group_model->getAdminGroup();
        return $group->id == $this->getUserGroupID($user_id);
    }

    public function isPartner($user_id){
        $group = $this->group_model->getPartnerGroup();
        return $group->id == $this->getUserGroupID($user_id);
    }
}
