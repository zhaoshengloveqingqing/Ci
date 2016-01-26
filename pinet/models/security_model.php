<?php defined("BASEPATH") or exit("No direct script access allowed");

class Security_Model extends Pinet_Model {
    function __construct() {
        parent::__construct();
        $this->load->model(array('security_target_model', 'security_subject_model', 'security_configuration_model'));
    }

	public function getTarget($subject, $conf) {
		return $this->security_target_model->getTarget($subject, $conf);
	}

	public function getCurrentConfigurations($type) {
		return $this->security_configuration_model->getCurrentConfigurations($type);
	}

	public function getTemplateTarget($subject, $config, $type) {
		return $this->security_target_model->getTemplateTarget($subject, $config, $type);
	}

	public function getCurrentSubjects() {
        return $this->security_subject_model->getCurrentSubjects();
	}

    public function addTarget($target, $subject = array(), $config = array()){
        return $this->security_target_model->addTarget($target, $subject, $config);
    }

    public function getOrCreateSubject($user_id = 0, $group_id = 0){
        return $this->security_subject_model->getOrCreateSubject($user_id, $group_id);
    }

    public function addConfig($config){
        return $this->security_configuration_model->addConfig($config);
    }

    public function getConfigs($type, $controller){
        return $this->security_configuration_model->getConfigs($type, $controller);
    }
}
