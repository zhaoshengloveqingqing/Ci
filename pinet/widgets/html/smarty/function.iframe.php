<?php defined("BASEPATH") or exit("No direct script access allowed");

function smarty_function_iframe($params, $template) {
	$src = get_default($params, 'src', null);
	if(!$src) {
		ci_error('The src attribute must be set for iframe.');
		return '';
	}

	$src = site_url($src);

	$lazy = get_default($params, 'lazy', false);

	$class = $lazy? 'iframe-lazy': 'iframe';
	$params['class'] = make_classes($class, get_default($params, 'class'));
	if($lazy) {
		$params['data-src'] = $src;
		unset($params['src']);
	}
	return create_tag('iframe', $params, array(), '');
}
