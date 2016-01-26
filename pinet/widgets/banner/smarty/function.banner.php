<?php defined('BASEPATH') or exit('No direct script access allowed');

function smarty_function_banner($params, $template) {
	$src = get_default($params, 'src', null);
	if($src == null) {
		ci_error(lang_f('The banner\'s attribute src must be set!'));
		return '';
	}
	$params['class'] = make_classes('pinet_banner', get_default($params, 'class', null));

	$height = get_default($params, 'height', null);
	if($height != null) {
		$params['style'] .= 'height:'.$height.';';
	}
	return create_tag('div', $params, array(), '');
}
