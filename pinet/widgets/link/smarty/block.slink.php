<?php defined('BASEPATH') or exit('No direct script access allowed');

function smarty_block_slink($params, $content = '', $template, &$repeat) {
	if($repeat)
		return;

	$href = get_default($params, 'href', null);
	if (isset($params['uri']) && $params['uri'] != '') {
		$uri = $params['uri'];
		$params['href'] = site_url($uri);
	}else if (isset($params['uri']) && $params['uri'] == '') {
		$uri = $params['uri'];
		$params['href'] = '';
	}else{
		if ($href == '') {
			trigger_error('must need href attribute');
		}else {
			$params['href'] = $href;
		}
	}

	unset($params['uri']);
	$params['href'] = lang($href);
	return create_tag('a', $params, array(), $content);
}

