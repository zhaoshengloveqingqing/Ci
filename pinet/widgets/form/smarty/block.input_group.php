<?php defined('BASEPATH') or exit('No direct script access allowed');

function smarty_block_input_group($params, $content = '', $template, &$repeat) {
	if($repeat)
		return;
	$params['class'] = make_classes('input-group', get_default($params, 'class', null));
	return create_tag('div', $params, array(), $content);
}
