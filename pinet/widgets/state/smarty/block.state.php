<?php defined('BASEPATH') or exit('No direct script access allowed');

function smarty_block_state($params, $content = '', $template, &$repeat) {
	if($repeat) { // This is the start part
		return;
	}
	$state = get_default($params, 'name', 'default');

	if(!(isset($template->block_data) && gettype($template->block_data) === 'object')) {
		$template->block_data = new stdclass();
	}

	if($state == 'default' && smarty_get_parent_tag($template) == 'states' && !isset($template->block_data->out)){
		return $content;
	}

	$CI = &get_instance();

	$name = get_default((array)$template->block_data, 'name', '');
	if($state == $CI->getState($name)) {
		$template->block_data->out = $state;
		return $content;
	}

	return '';
}
