<?php defined("BASEPATH") or exit("No direct script access allowed");

function smarty_function_icon($params, $template) {
	$type = get_default($params, 'type');

	$tag = get_default($params, 'tag', 'i');

	if(isset($params['tag'])) {
		unset($params['tag']);
	}

	$params['class'] = make_classes('glyphicon', 'glyphicon-'.$type, get_default($params, 'class', null));
	$params['aria-hidden'] = 'true';
	return create_tag($tag, $params, array(), '');
}
