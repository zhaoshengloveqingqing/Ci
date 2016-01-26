<?php defined('BASEPATH') or exit('No direct script access allowed');

class Group_Model extends Pinet_Model {
	public function __construct(){
		parent::__construct('groups');
	}

	public function getOrCreate($name, $id = NULL) {
		$group = $this->getGroup($name);
		if($group)
			return $group;

        $group['name'] = $name;
        if($id)
            $group['id'] = $id;

        $id = $this->insert($group);
		return $this->load($id);
	}

	public function getGroup($groupname) {
		$this->result_mode = 'object';
		$ret = $this->get(array(
			'name'=>$groupname
		));	
		if(isset($ret->id)) {
			return $ret;
		}
		return NULL;
	}

	public function getGroupID($groupname) {
		$ret = $this->getGroup($groupname);
		if($ret) {
			return $ret->id;
		} else {
			return -1;
		}
	}

	public function getUserGroup() {
		return $this->getOrCreate('user', 1);
	}	

	public function getAdminGroup() {
		return $this->getOrCreate('admin', 2);
	}

	public function getPartnerGroup() {
		return $this->getOrCreate('partner', 3);
	}

    public function addGroup($groups) {
		if(is_array($groups)){
			foreach ($groups as $group) {
				$this->insert(array(
					'name'=>$group
				));				
			}
		}else{
			$this->insert(array(
				'name'=>$groups
			));
		}
	}
}
