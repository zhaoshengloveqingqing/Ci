<?php defined('BASEPATH') or exit('No direct script access allowed');;

function smarty_block_tag($params, $content = '', $template, &$repeat) {
	if($repeat) {
		return;
	}

	$tag = get_default($params, 'tag', 'span');

	if(isset($params['tag'])) {
		unset($params['tag']);
	}

	$show = get_default($params, 'show', 'default');

	$params['class'] = make_classes('label', 'label-'.$show, get_default($params, 'class', null));

	if(!isset($params['title'])) {
		$params['title'] = trim(strip_tags($content));
	}

	return create_tag($tag, $params, array(), $content);
}