<?php defined('BASEPATH') or exit('No direct script access allowed');

function smarty_function_js($params, $template) {
	$position = get_default($params, 'position', 'foot');
	$CI = &get_instance();
	foreach($CI->jsFiles as $js) {
		if($position == $js->position)
			echo $js->render();
	}
}
