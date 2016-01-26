<?php defined('BASEPATH') or exit('No direct script access allowed');

function smarty_block_button_group($params, $content = '', $template, &$repeat) {
	if($repeat)
		return;
	$params['data-toggle'] = 'buttons';
	$params['class'] = make_classes('btn-group' ,get_default($params, 'class', ''));
	return create_tag('div', $params, array(), $content);
}
