<?php defined('BASEPATH') or exit('No direct script access allowed');

if(!function_exists('smarty_function_input')) {
	require_once(dirname(__FILE__).'/function.input.php');
}

function smarty_function_password($params, $template) {
	$params['type'] = 'password';
	return smarty_function_input($params, $template);
}
