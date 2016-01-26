<?php defined("BASEPATH") or exit("No direct script access allowed");

class Security_Subject_Model extends Pinet_Model {
    function __construct() {
        parent::__construct('security_subjects');
		$this->load->model(array('group_model', 'user_model'));
    }

	public function getCurrentSubjects() {
		if(!$this->user_model->isLoggedIn()) {
			return array($this->getAnonymousSubject());
		}

		$ret =  array($this->getUserSubject());
		$userid = $this->user_model->getLoginUserID();
		$groupid = $this->user_model->getGroupID($userid);

		$us = $this->getSubject($userid, 0);
		$gs = $this->getOrCreateSubject(0, $groupid);
		$ret []= $gs;
		if($us != null) {
			$ret []= $us;
		}
		return $ret;
	}

	public function translate($subject) {
		switch($subject) {
		case 'anonymous':
		case 'anony':
			return $this->getAnonymousSubject();
		case '*':
		case 'user':
		case 'all_users':
			return $this->getUserSubject();
		case 'admin':
			return $this->getAdminSubject();
		case 'partner':
			return $this->getPartnerSubject();
		default:
			return $this->getAnonymousSubject();
		}
	}

	public function getAnonymousSubject() {
		return $this->getOrCreateSubject(-1, -1, 1);
	}

	public function getSubject($user_id = 0, $group_id = 0) {
        $this->result_mode = 'object';
        $subject = $this->get(array(
            'user_id' => $user_id,
            'group_id' => $group_id
        ));
        if(isset($subject->id)) {
            return $subject;
		} 
		return null;
	}

    public function getOrCreateSubject($user_id = 0, $group_id = 0, $sid = -1){
        $this->result_mode = 'object';
        $subject = $this->get(array(
            'user_id' => $user_id,
            'group_id' => $group_id
        ));
        if(isset($subject->id)) {
            return $subject;
		} 
		else {
			if($sid != -1) {
				$id = $this->insert(array(
					'id' => $sid,
					'user_id' => $user_id,
					'group_id' => $group_id
				));
			}
			else {
				$id = $this->insert(array(
					'user_id' => $user_id,
					'group_id' => $group_id
				));
			}
			return $this->load($id);
        }
    }

    public function getUserSubject() {//normal subject
		return $this->getOrCreateSubject(0, 0, 2);
    }

    public function getAdminSubject() {
		return $this->getOrCreateSubject(0, $this->group_model->getAdminGroup()->id, 3);
    }

    public function getPartnerSubject() {
		return $this->getOrCreateSubject(0, $this->group_model->getPartnerGroup()->id, 4);
    }
}
