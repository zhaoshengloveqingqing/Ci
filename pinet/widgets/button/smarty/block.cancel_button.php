<?php defined('BASEPATH') or exit('No direct script access allowed');

require_widget_smarty("button","button");

function smarty_block_cancel_button($params, $content = '', $template, &$repeat) {
	if($repeat) {
		return;
	}
	$breadscrums = get_breadscrums();
	if (count($breadscrums) > 1) {
		array_pop($breadscrums);
	}
	$url = array_pop($breadscrums);
	$params['tag'] = 'a';
	$params['href'] = site_url($url);
	$params['title'] = $url;
	return pinet_smarty_create_button($params, $content);
}