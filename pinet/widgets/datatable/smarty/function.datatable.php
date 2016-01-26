<?php defined('BASEPATH') or exit('No direct script access allowed');

function smarty_function_datatable($params, $template) {
	$attr = array(
		'id' => 'datatable',
		'class' => make_classes(array('datatable', 'display'), get_default($params, 'class', array()))
	);
	return create_tag('table', $attr, array(), ' ');
}
