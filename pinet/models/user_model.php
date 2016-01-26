<?php defined('BASEPATH') or exit('No direct script access allowed');

class User_Model extends Pinet_Model {
	function __construct() {
		parent::__construct('users');
		$this->load->model(array('group_model','account_model'));					
	}

	public function getByUsername($username) {
		$this->result_mode = 'object';
		return $this->get(array('username' => $username));
	}

	public function getByEmailAddress($username) {
		$this->result_mode = 'object';
		return $this->get(array('email_address' => $username));
	}

	public function addUserToGroup($uid, $gid) {
		$this->myinsert('groups_users', array(
			'user_id' => $uid,
			'group_id' => $gid
		));
	}

	public function getLoginUserID() {
		$this->load->library('session');
		return $this->session->userdata('user_id');
	}

	public function getAllGroups() {
		$ret = array();
		$this->result_mode = 'object';
		$groups = $this->myget_all('groups');
		foreach($groups as $group) {
			$key = $group->id;
			$ret[$key] = $group->group_name;
		}
		return $ret;
	}

	public function haveUser($id) {
		$this->db->or_where(array(
			'username' => $id,
			'email_address' => $id
		));
		$this->result_mode = 'object';
		return isset($this->get()->id);
	}

	public function rememberMe() {
		// TODO: Add using cookie
	} 

	public function login($id, $password) {
		$this->result_mode = 'object';
		$this->db->or_where(array(
			'username' => $id,
			'email_address' => $id
		));
		$ret = $this->get();
		if(isset($ret->id)) {
			if($ret->password == md5($password)) {
                $now = new DateTime();
                $this->update($ret->id, array('timestamp'=> $now->format('Y-m-d H:i:s')));
				$this->session->set_userdata('user_id', $ret->id);
				return $ret->id;
			}
			return lang_f('The password for user %s is not correct!', $id);
		}
		return lang_f('Username or email address %s is not found.', $id);
	}

	public function isLoggedIn() {
		return $this->getLoginUserID();
	}

	public function logout() {
		$this->session->unset_userdata('user_id');
	}

	public function register($userInfo) {
		if($this->haveUser($userInfo['username'])) {
			return -1;
		}else{
			$user = array(
				'email_address'=> $userInfo['email_address'],
				'password'=> md5($userInfo['password']),
				'username'=>$userInfo['username'],
				'status'=>'ACTIVE',
                'user_type' => $userInfo['user_type']
			);
			$user_id = $this->insert($user);

			//add user account info
			$this->addAccount($user_id, $userInfo);

			//add association
			$this->addGroups2Users($user_id, $userInfo['group_id']);

			return $user_id;
		}
	}

	public function addAccount($userid, $post) {
		$this->user_model->myinsert('accounts',array(
			'user_id'=>$userid,
			'name'=>$post['name'],
			'mobile'=>$post['mobile'],
			'sex'=>$post['sex'],
			'birthday'=>(isset($post['birthday']) && $post['birthday'] ? $post['birthday'] : null),
			'contact_company'=>(isset($post['contact_company'])? $post['contact_company'] : null),
			'contact_name'=>$post['contact_name'],
			'contact_country'=>$post['contact_country'],
			'contact_province'=>$post['contact_province'],
			'contact_city'=>$post['contact_city'],
			'contact_street'=>$post['contact_street'],
			'contact_postalcode'=>$post['contact_postalcode'],
			'contact_profile'=>$post['contact_profile']	
		));
	}

	public function addGroups2Users($userid, $groupid) {
		$group_id = $this->getGroupID($userid);
		if($group_id == -1) {
			$this->user_model->myinsert('groups_users',array(
				'group_id'=>$groupid,
				'user_id'=>$userid
			));
		}
	}

	public function getGroups($userid) {
        $this->result_mode = 'object';
		return array_map(function($i){ return $i->group_id;}, $this->myget_all('groups_users',array(
			'user_id'=>$userid
		)));
	}

	public function getGroupID($userid) { // XXX: Only pickup first groupid XXX
		$groups = $this->getGroups($userid);
		if(count($groups)) {
			return $groups[0];
		}
		return -1;
	}

    public function changePassword($user_id, $old_pwd, $new_pwd, $new_confirm_pwd){
        if($new_pwd != $new_confirm_pwd){
            return lang('Different new password');
        }else{
            $this->result_mode='object';
            $user = $this->load($user_id);
            if($user){
                if($user->password == md5($old_pwd)){
                    return $this->update($user_id, array('password'=>md5($new_pwd)));
                }else{
                    return lang('Wrong old password');
                }
            }
        }
        return lang('Not find current user');
    }

    public function getUserInfo($user_id){
        $this->db->select('u.id,u.username,u.email_address,a.first_name,a.last_name,a.birthday,a.sex,a.mobile,a.contact_company,a.contact_street,a.contact_city,a.contact_province,a.contact_postalcode,a.contact_profile,a.profile_image_path');
        $this->db->from('users as u');
        $this->db->join('accounts as a', 'u.id=a.user_id', 'inner');
        $this->db->where('u.id', $user_id);
        $data = $this->db->get()->result();
        if(count($data)){
            $user_info = $data[0];
            if($user_info->birthday == '0000-00-00'){
                $user_info->birthday = '';
            }
            return $user_info;
        }
        return null;
    }

    public function checkUserUsername($user_id, $username){
        $this->db->select('id');
        $this->db->from('users');
        $this->db->where('id <>', $user_id);
        $this->db->where_or_and(array(
            'username' => $username,
            'email_address' => $username
        ));
        $this->result_mode = 'object';
        $result = $this->db->get()->row();
        if($result)
            return false;
        return true;
    }

    public function checkUserEmail($user_id, $email){
        $this->db->select('id');
        $this->db->from('users');
        $this->db->where('id <>', $user_id);
        $this->db->where_or_and(array(
            'username' => $email,
            'email_address' => $email
        ));
        $this->result_mode = 'object';
        $result = $this->db->get()->row();
        if($result)
            return false;
        return true;
    }

    public function updateUserInfo($data){
        $user_id = $data['id'];
        if((!$this->checkUserUsername($user_id, $data['username'])) || (!$this->checkUserEmail($user_id, $data['email_address']))){
            return lang('Username or email is exits!');
        }
        $result = $this->update($user_id, array(
            'email_address'=> $data['email_address'],
            'username'=> $data['username']
        ));
        $account = $this->account_model->getAccount($user_id);
        if($account){
            $result = $this->myupdate('accounts', $account->id, array(
                'first_name'=> $data['first_name'],
                'last_name'=> $data['last_name'],
                'name'=> $data['last_name'].' '.$data['first_name'],
                'mobile'=> $data['mobile'],
                'sex'=> $data['sex'],
                'birthday'=> $data['birthday'],
                'contact_company'=> $data['contact_company'],
                'contact_street'=> $data['contact_street'],
                'contact_city'=> $data['contact_city'],
                'contact_province'=> $data['contact_province'],
                'contact_postalcode'=> $data['contact_postalcode'],
                'contact_profile'=> $data['contact_profile'],
                'type'=> $data['type'],
                'profile_image_path'=> $data['path']
            ));
        }
        return $result;
    }
}
