<?php defined('BASEPATH') or exit('No direct script access allowed');

function smarty_block_uri($params, $content = '', $template, &$repeat) {
	if($repeat)
		return;
	return site_url($content);
}
