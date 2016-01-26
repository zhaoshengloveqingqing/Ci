<?php defined('BASEPATH') or exit('No direct script access allowed');

function smarty_block_bs_container($params, $content, $template, &$repeat) {
	if($repeat) // Skip the first time
		return;

	$containerClass = 'container';
	if (isset($params['display']) && $params['display'] != '') {
		$display = $params['display'];
		unset($params['display']);
		$containerClass .= ' container-'.$display;
	}

	$params['class'] = make_classes($containerClass, get_default($params, 'class', null));
	$template->block_data = array('sdsds');
	return create_tag('div', $params, array(), $content);
}