<?php defined('BASEPATH') or exit('No direct script access allowed');

function smarty_function_helpblock($params, $template) {
	$classes = make_classes(get_default($params, 'class'), array('help-block'));
	return create_tag('p', array('class' => $classes));
}