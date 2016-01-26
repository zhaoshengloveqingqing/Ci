<?php defined('BASEPATH') or exit('No direct script access allowed');

function smarty_block_radio_group($params, $content = '', $template, &$repeat) {
	if($repeat)
		return;
	$params['data-toggle'] = 'buttons';
	$show = get_default($params, 'show', 'default');
	if(isset($params['show'])) {
		unset($params['show']);
	}
	$params['class'] = make_classes('btn-group pinet-radio-group btn-group-'.$show ,get_default($params, 'class', ''));
	return create_tag('div', $params, array(), $content);
}