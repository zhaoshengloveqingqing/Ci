<?php defined('BASEPATH') or exit('No direct script access allowed');

function smarty_block_radio_button($params, $content = '', $template, &$repeat) {
	if($repeat)
		return;
	$show = get_default($params, 'show', 'default');
	if(isset($params['show'])) {
		unset($params['show']);
	}
	$classes = make_classes('btn', 'pinet-btn', 'btn-'.$show, get_default($params, 'class', null));
	$params['type'] = 'radio';
	$params['autocomplete'] = 'off';
	$input = create_tag('input', $params, array());

	if(isset($params["checked"]) && $params["checked"] != '') {
		$classes[] = "active";
	}

	return create_tag('label', array('class'=>$classes), array(), $input.$content);
}