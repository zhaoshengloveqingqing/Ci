<?php defined('BASEPATH') or exit('No direct script access allowed');

if(!function_exists('smarty_function_input')) {
	require_once(dirname(__FILE__).'/function.function.input.php');
}

function smarty_function_label($params, $template) {
	if(isset($template->block_data)) {
		$f = $template->block_data;
		$field = $f->name;
		$params['for'] = $f->getId();
		$params['class'] = make_classes(get_default($params, 'class'), 'control-label');
		return create_tag('label', $params, array(), $f->label);
	}
	return create_tag('label', $params);
}
