<?php defined('BASEPATH') or exit('No direct script access allowed');

require_widget_smarty("button","button");

function smarty_block_link_button($params, $content = '', $template, &$repeat) {
	if($repeat) {
		return;
	}
	$params['tag'] = 'a';
	if(isset($params['uri']) && $params['uri'] != '') {
		unset($params['href']);
		$params['href'] = site_url($params['uri']);
		unset($params['uri']);
	}
	return pinet_smarty_create_button($params, $content);
}