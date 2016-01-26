<?php defined('BASEPATH') or exit('No direct script access allowed');

if(!function_exists('smarty_function_input')) {
	require_once(dirname(__FILE__).'/function.function.input.php');
}

function smarty_function_textarea($params, $template) {
	$CI = &get_instance();

	$attr = get_attr($params, $template);
	$value = $attr['value'];
	
	if(isset($template->block_data)) {
		$f = $template->block_data;
	}
	if($f->state == 'readonly') {
		return create_tag('div', array('class'=>array('form-element', 'textarea', 'readonly')), array(), $value);
	}

	return form_textarea($attr, $value);
}
