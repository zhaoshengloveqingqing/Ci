<?php defined('BASEPATH') or exit('No direct script access allowed');

function smarty_block_link($params, $content = '', $template, &$repeat) {
	if($repeat)
		return;
	$href = get_default($params, 'href', null);

	$toggle = get_default($params, 'toggle', '');
	if ($toggle == 'tooltip') {
		$params['data-toggle'] = 'tooltip';
		$params['data-placement'] = get_default($params, 'placement', 'bottom');
	}

	if(isset($href)) {
		return anchor($href, $content, $params);
	}
	$uri = get_default($params, 'uri', '');
	unset($params['uri']);
	return anchor($uri, $content, $params);
}
