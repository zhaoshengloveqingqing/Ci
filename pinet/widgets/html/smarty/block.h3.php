<?php defined('BASEPATH') or exit('No direct script access allowed');

function smarty_block_h3($params, $content = '', $template, &$repeat) {
	if($repeat)
		return;
	return build_tag('h3', $params, $content);
}