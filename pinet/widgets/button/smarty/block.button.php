<?php defined('BASEPATH') or exit('No direct script access allowed');

function pinet_smarty_create_button($params, $content = '') {
	$tag = get_default($params, 'tag', 'button');

	if(isset($params['tag'])) {
		unset($params['tag']);
	}

	$show = get_default($params, 'show', 'default');
	if(isset($params['show'])) {
		unset($params['show']);
	}

	if($tag == 'input' && !isset($params['type']))
		$params['type'] = 'button';

	$params['class'] = make_classes('btn', 'pinet-btn', 'btn-'.$show, get_default($params, 'class', null));

	if($tag == 'input' && !isset($params['value'])) {
		$params['value'] = $content;
	}

	$params['role'] = 'button';

	if(!isset($params['title'])) {
		$params['title'] = trim(strip_tags($content));
	}

	if(isset($params['icon']) && $params['icon'] != '') {
		$iconName = $params['icon'];
		unset($params['icon']);
		$iconParams = array();
		$iconParams['class'] = make_classes('glyphicon', 'glyphicon-'.$iconName);
		$iconParams['aria-hidden'] = 'true';
		$icon = create_tag('span', $iconParams, array(), '');
		$content = $icon.$content;
	}

	if($tag == 'input')
		return create_tag('input', $params, array());
	return create_tag($tag, $params, array(), $content);
}

function smarty_block_button($params, $content = '', $template, &$repeat) {
	if($repeat) {
		return;
	}
	return pinet_smarty_create_button($params, $content);
}
