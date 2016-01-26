<?php defined("BASEPATH") or exit("No direct script access allowed");

function smarty_function_init_js($params, $template) {
	$CI = &get_instance();
	$out = array();

	if(isset($CI->initJSes)) {
		foreach($CI->initJSes as $js) {
			$out []= $js;
		}
	}

	$out []= "\n\tjQuery(function($) {";
	$out []= "
		if(typeof(initialise) === 'function') {
			initialise();
		}
";

	$out []= "\t});\n";
	return create_tag('script', array('type' => 'text/javascript'), array(), implode("\n", $out));

}
