<?php defined('BASEPATH') or exit('No direct script access allowed');


function smarty_block_div($params, $content = '', $template, &$repeat) {
	if($repeat)
		return;
	return build_tag('div', $params, $content);
}