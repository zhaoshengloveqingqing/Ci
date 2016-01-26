<?php defined('BASEPATH') or exit('No direct script access allowed');

function smarty_block_col($params, $content, $template, &$repeat) {
	if($repeat) // Skip the first time
		return;

	$col = create_tag('div', array('class'=>'scroll'), array(), $content);
	$params['class'] = make_classes('col', get_default($params, 'class', null));
	return create_tag('div', $params, array(), $col);
}