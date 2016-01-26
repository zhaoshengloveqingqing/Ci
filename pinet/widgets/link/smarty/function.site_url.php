<?php defined('BASEPATH') or exit('No direct script access allowed');

function smarty_function_site_url($params, $template) {
	return site_url($params['url']);
}
