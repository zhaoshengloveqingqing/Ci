<?php defined("BASEPATH") or exit("No direct script access allowed");

class Security_Engine {
	private $CI;

	public function __construct() { 
		$this->CI = &get_instance();
		$this->CI->load->model('security_model');
		$this->security_model = $this->CI->security_model;
	}

	public function validate($obj) {
		$subjects = $this->CI->security_model->getCurrentSubjects();
        $result = null;

		if(is_object($obj)) {
            $class_type = get_class($obj);
            if($class_type == 'Action'){
                foreach($subjects as $subject) {
                    $result = $this->validateAction($obj, $subject); // Only use the last result
                }
            }elseif($class_type == 'FormField'){
                if(isset($this->form_target)) {
                    if($this->form_target == 'PASS')
                        return 'allow';
                }
                else {
                    foreach($subjects as $subject) {
                        $this->form_target = $this->getActionTarget('form', $subject, 'views');
                    }
                    if(!isset($this->form_target)) {
                        $this->form_target = 'PASS';
                        return 'allow';
                    }
                }
                return $this->validateField($this->form_target, $obj);
            }else{
                if(isset($this->datatable_target)) {
                    if($this->datatable_target == 'PASS')
                        return 'allow';
                }
                else {
                    foreach($subjects as $subject) {
                        $this->datatable_target = $this->getActionTarget('datatable', $subject, 'views');
                    }
                    if(!isset($this->datatable_target)) {
                        $this->datatable_target = 'PASS'; // If none target found for this datatable, just make it pass to avoid recreate the datatable_target every time
                        return 'allow';
                    }
                }
                return $this->validateColumn($this->datatable_target, $obj);
            }
		}

		if($result) 
			return $result;

		return 'deny'; // Deny as default
	}

	public function validateColumn($target, $col) {
        foreach($target->columns as $c) {
            if($col->data == $c)
                return 'deny';
        }
        return 'allow';
	}

	public function validateField($target ,$field) {
		foreach($target->fields as $k => $v) {
			if($k == $field->name)
				return $v;
		}
		return 'default';
	}

	public function getActionTarget($action, $subject, $type = 'actions') {
		$confs = $this->security_model->getCurrentConfigurations($action);
		$target = null;

		foreach($confs as $conf) {
			$tmp = $this->security_model->getTarget($subject, $conf);
			if($tmp)
				$target = $tmp;
		}

		if($target) // If we can find the define in database, just return it
			return $target;

		if(is_string($action)) {
			return $this->security_model->getTemplateTarget($subject, get_controller_meta(), $type);
		}

		return $this->security_model->getTemplateTarget($subject, $action, $type);
	}

	public function validateAction($action, $subject) {
		$target = $this->getActionTarget($action, $subject);
		if($target)
			return $target->operation;
		return null;
	}
}
