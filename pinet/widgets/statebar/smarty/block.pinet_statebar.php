<?php defined('BASEPATH') or exit('No direct script access allowed');

function smarty_block_pinet_statebar($params, $content = '', $template, &$repeat) {
	if($repeat)
		return;

	$params['class'] = make_classes(array('pinet-statebar'), get_default($params, 'class', array()));

	return create_tag('div', $params, array(), $content);
}

