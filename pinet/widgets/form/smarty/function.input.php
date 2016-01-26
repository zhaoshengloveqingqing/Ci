<?php defined('BASEPATH') or exit('No direct script access allowed');

function get_form_data($parent_vars) {
	$CI = &get_instance();
	$form_data = null;
	if(isset($CI->form_data)) {
		$form_data = $CI->form_data;
	}
	else if(isset($parent_vars['form_data'])) {
		$form_data = $parent_vars['form_data']->value;
	}
	return $form_data;
}

function get_field($params, $template) {
	if(isset($template->block_data) && is_object($template->block_data) && get_class(
		$template->block_data) === 'FormField') {
		return $template->block_data;
	}
	$CI = &get_instance();
	$field = get_default($params, 'field');
	if($field == null) {
		trigger_error('The field parameter is required for field group');
		return null;
	}
	$f = $CI->getField($field); // Getting the state from controller
	if($f == null) {
		trigger_error('The field '.$field.' is not found in the controller.');
		return null;
	}

	if(isset($CI->security_engine)) { // We have security enabled
		$ss = $CI->security_engine->validate($f); // Getting the state from security engine
		if($ss != 'allow')
			$f->state = $ss;
	}

	if(isset($template->block_data) && gettype($template->block_data) === 'object' // Getting the state from the container
		&&get_class($template->block_data) === 'Pinet_Container') {
		$f->container = $template->block_data;
		$f->state = $template->block_data->state;
	}
	return $f;
}

function get_attr($params, $template) {
	$CI = &get_instance();
	$CI->load->helper('form');

	$parent_vars = $template->parent->tpl_vars;
	$f = get_field($params, $template);
	$field = $f->name;

	$state = get_default($params, 'state', null);
	if($state != null) {
		$f->state = $state;
	}

	$classes = array();
	$value = get_default($params, 'value');
	$classes []= get_default($params, 'class');
	$classes []= 'form-control';
	$attr = get_default($params, 'attr', array());
	$class = implode(' ', $classes);
	$type = get_default($params, 'type', 'text');

	if($f->defaultValue != '') {
		$value = $f->defaultValue;
	}

	// Added the support for CI Form Validation
	$setValue = set_value($f->name);
	if($value == '' && $setValue != '') {
		$value = $setValue;
	}

	// Now for form object rendering
	$form_data = get_form_data($parent_vars);

	if(isset($f->mask)) {
		$json = new stdclass();
		if(is_string($f->mask)) {
			$json->mask = $f->mask;
		}
		else {
			$json = '';
			$jsonarray = (array)$f->mask;
			foreach ($jsonarray as $key => $value) {
				$json .=  "'".$key."':'".$value."'";
			}
		}
		$json = str_replace('"', "'", json_encode($json));
		$attr['data-inputmask'] = substr($json, 1, strlen($json) - 2);
	}

	if(isset($form_data) && isset($form_data->$field))
		$value = $form_data->$field;

	$attr['class'] = $class;
	$attr['name'] = $f->name;
	$attr['id'] = $f->getId();
	$attr['type'] = $type;
	$attr['value'] = $value;
	if($f->state == 'disabled') {
		$attr['disabled'] = 'true';
	}
	$rules = $f->getJQValidationRules();
	foreach ($rules as $k => $v) {
		$attr[$k] = $v;
	}

	if(isset($f->placeholder) && $f->placeholder != '') {
		$attr['placeholder'] = lang($f->placeholder);
	}
	else {
		$attr['placeholder'] = get_default($params, 'placeholder', '');
	}

	if (isset($params['type']) && $params['type'] != '') {
		$attr['data-type'] = $params['type'];
	}
	return $attr;
}

function smarty_function_input($params, $template) {
	// TODO: Maybe we can let the field configure the formatter even not in readonly state?
	$attr = get_attr($params, $template);
	if(is_class($template->block_data, "FormField")) {
		$f = $template->block_data;
		if($f->state == 'readonly') { // We are in readonly format
			if(isset($f->formatter)) {
				return call_user_func($f->formatter, $attr);
			}
			else {
				return create_tag('span', array('class' => array('form-element', 'input', 'readonly')), array(), $attr['value']);
			}
		}
	}
	return form_input($attr);
}
