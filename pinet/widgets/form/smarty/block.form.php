<?php defined('BASEPATH') or exit('No direct script access allowed');

function smarty_block_form($params, $content, $template, &$repeat) {
	if($repeat) // Skip the first time
		return;

	$action = get_default($params, 'action');
	$id = get_default($params, 'id', '');
	$class = get_default($params, 'class');
	$attr = get_default($params, 'attr', array());
	$hidden = get_default($params, 'hidden', array());

	$attr['id'] = $id;
	$attr['role'] = 'form';
	if($class != '') 
		$attr['class'] = $class;

	$ret = array();
	$ret []= form_open($action, $attr, $hidden);
	$ret []= $content;
	$ret []= form_close();

	return implode("\n", $ret);
}
