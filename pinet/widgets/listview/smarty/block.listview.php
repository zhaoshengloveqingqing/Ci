<?php defined('BASEPATH') or exit('No direct script access allowed');

function smarty_block_listview($params, $content = '', $template, &$repeat) {
	if($repeat) {
		return;
	}
	$attr = array(
		'id' => 'listview',
		'class' => make_classes(array('listview', 'display'), get_default($params, 'class', array()))
	);
	return create_tag('ul', $attr, array(), $content);
}
