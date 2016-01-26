<?php defined('BASEPATH') or exit('No direct script access allowed');;

function smarty_block_faq($params, $content = '', $template, &$repeat) {
	if($repeat) {
		return;
	}

	$tag = get_default($params, 'tag', 'span');

	if(isset($params['tag'])) {
		unset($params['tag']);
	}

	$placement = get_default($params, 'placement', 'bottom');

	$params["data-placement"] = $placement;
	if(isset($params['placement'])) {
		unset($params['placement']);
	}


	$auto = get_default($params, 'auto', true);

	if ($auto) {
		$params['data-toggle'] = 'tooltip';
	}
	if(isset($params['auto'])) {
		unset($params['auto']);
	}


	$show = get_default($params, 'show', 'default');

	$params['class'] = make_classes('faq', 'faq-'.$show, get_default($params, 'class', null));

	if(!isset($params['title'])) {
		$params['title'] = trim(strip_tags($content));
	}


	return create_tag($tag, $params, array(), $content);
}