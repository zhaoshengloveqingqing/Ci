<?php defined('BASEPATH') or exit('No direct script access allowed');

function smarty_block_states($params, $content = '', $template, &$repeat) {
	if($repeat) { // This is the start part
		return;
	}
	if(isset($template->block_data) && gettype($template->block_data) === 'object'
		&& get_class($template->block_data) === 'Pinet_Container') {
		$template->block_data->out = null;
	}
	else {
		$template->block_data = null;
	}
	return $content;
}
