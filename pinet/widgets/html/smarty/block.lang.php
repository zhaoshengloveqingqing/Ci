
<?php defined('BASEPATH') or exit('No direct script access allowed');


function smarty_block_lang($params, $content = '', $template, &$repeat) {
	if($repeat)
		return;
	return lang($content);
}
