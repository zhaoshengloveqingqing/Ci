<?php defined('BASEPATH') or exit('No direct script access allowed');

function smarty_block_figure($params, $content = '', $template, &$repeat) {
	if($repeat) { // Skip the start part
		return;
	}

	$src = smarty_plugin_get_variable($params, $template, 'src', true);
	$path = smarty_plugin_get_variable($params, $template, 'path');

	$size = get_image_size($src);
	$size = $size['width'];

	if($path == '') { // If we are using auto resizing, skip the resolutions
		$resolutions = get_ci_config('resolutions');
		foreach($resolutions as $res) {
			$attr['data-media'.$res] = site_url('responsive/size/'.(float)$res / 2880 * (float)$size .'/'.$src);
		}
	}

	foreach($params as $key => $value) {
		if($key == 'path') {
			$attr[$key] = site_url(get_smarty_variable($params, $template, 'path', $value));
			continue;
		}

		if(strpos($key, 'media') !== false) {
			$attr['data-'.$key] = site_url('responsive/size/'.$value.'/'.$src);
		}
		else
			$attr[$key] = get_smarty_variable($params, $template, $value, $value);
	}

	$ret = array();
	$ret []= '<figure '._parse_form_attributes($attr, array()).'>';
	if(isset($attr['action']))
		$ret []= '<a href="'.$attr['action'].'">';
	$ret []= '<noscript>';
    $ret []= '<img src="'.site_url('static/img/'.$src).'">';
    $ret []= '</noscript>';
	if(isset($attr['action']))
		$ret []= '</a>';
	$ret []= '<figcaption>';
	$ret []= $content;
	$ret []= '</figcaption>';
	$ret []= '</figure>';
	return implode("\n",$ret);
}
