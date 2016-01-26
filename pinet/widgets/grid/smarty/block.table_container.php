<?php defined('BASEPATH') or exit('No direct script access allowed');

function smarty_block_table_container($params, $content, $template, &$repeat) {
	if($repeat) // Skip the first time
		return;

	$params['class'] = make_classes('table-container', get_default($params, 'class', null));
	return create_tag('div', $params, array(), $content);
}