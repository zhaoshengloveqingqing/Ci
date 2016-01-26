<?php defined('BASEPATH') or exit('No direct script access allowed');

function smarty_block_row($params, $content, $template, &$repeat) {
	if($repeat) // Skip the first time
		return;

	$params['class'] = make_classes('row', get_default($params, 'class', null));
	return create_tag('div', $params, array(), $content);
}