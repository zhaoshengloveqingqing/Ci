<?php defined("BASEPATH") or exit("No direct script access allowed");

class Security_Target_Model extends Pinet_Model {
    public function __construct() {
        parent::__construct('security_targets');
        $this->load->model(array('security_subject_model', 'security_configuration_model'));
    }

	public function loadTemplate() {
		$config = FCPATH.APPPATH.'config/security_template.json';
		$this->sec_template = json_decode(file_get_contents($config));
	}

	public function getTemplateTarget($subject, $config, $type = 'actions') {
		if(!isset($this->sec_template))
			$this->loadTemplate();

		$target = null;
		$target_exact = null;
		foreach($this->sec_template as $k => $v) {
			if($k == 'default' || $this->security_subject_model->translate($k) == $subject) {

				if(isset($v->$type)) {
					foreach($v->$type as $target_template) {
						if(is_regex($target_template->controller)) {
							if(preg_match($target_template->controller, $config->controller)) {
								if(is_regex($target_template->method)) {
									if(preg_match($target_template->method, $config->method)) {
										$target = $target_template;
									}
								}
								else {
									if($target_template->method == $config->method) {
										$target = $target_template;
									}
								}
							}
						}
						else {
							if($target_template->controller == $config->controller) {
								if(is_regex($target_template->method)) {
									if(preg_match($target_template->method, $config->method)) {
										$target = $target_template;
									}
								}
								else {
									if($target_template->method == $config->method) {
										$target = $target_template;
										if(isset($config->args) && isset($target_template->args) && $target_template->args == $config->args)
											$target_exact = $target_template;
									}
								}
							}
						}
					}
				}
			}
		}

		if($target_exact) {
			return $target_exact;
		}
		return $target;
	}

	public function getTarget($subject, $config) {
		$this->result_mode = 'object';
		$target = $this->get(array(
			'subject_id' => $subject->id,
			'config_id' => $config->id
		));
		if(isset($target->id)) {
			return $target;
		}
		return null;
	}

    public function addTarget($target, $subject = array(), $config = array()){
        if($subject){
            $user_id=0;
            $group_id=0;
            if(isset($subject['user_id']))
                $user_id = $subject['user_id'];
            if(isset($subject['group_id']))
                $group_id = $subject['group_id'];
            $target['subject_id'] = $this->security_subject_model->getOrCreateSubject($user_id, $group_id)->id;
        }
        if($config){
            $target['config_id'] = $this->security_configuration_model->addConfig($config);
        }
        return $this->insert(array(
            'type' => $target['type'],
            'subject_id' => $target['subject_id'],
            'config_id' => $target['config_id'],
            'operation' => $target['operation'],
            'tag' => $target['tag']
        ));
    }
}
