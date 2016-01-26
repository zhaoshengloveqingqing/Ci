<?php defined('BASEPATH') or exit('No direct script access allowed');

function smarty_function_picture($params, $template) {
	$src = smarty_plugin_get_variable($params, $template, 'src', true);
	$alt = smarty_plugin_get_variable($params, $template, 'alt');
	$path = smarty_plugin_get_variable($params, $template, 'path');
    $CI =&get_instance();
    $CI->load->helper('image');
	$file = find_img($src);

	ci_log('The image file that needs to be picture is %s', $file);
	if($file == null) {// We can't read the file
		$src = 'default.png';
	}

	$size = get_image_size($src);
	$size = $size['width'];

	$attr = $params;
	$medias = array();
	$ret = array();

	$attr['src'] = $src;
	if($path != '') {
		$attr['path'] = site_url($path);
	}

	$ret []= '<picture '._parse_form_attributes($attr, array()).' >';

	foreach($params as $key => $value) {
		// Check if user has set the customized media
		if(strpos($key, 'media') !== false) {
			$media = str_replace('media', '', $key);
			$medias []= $media;
			$ret []= "\t".'<source src="'.site_url('responsive/size/'.$value.'/'.$src).'" media="(min-width:'.$media.'px)">';
			continue;
		}
		$attr[$key] = smarty_plugin_get_variable($params, $template, $key, false);
	}

	if($path == '') {
		$resolutions = get_ci_config('resolutions');
		foreach($resolutions as $res) {
			if(array_search($res, $medias) !== false) // If the resolution is already covered
				continue;
			$ret []= "\t".'<source src="'.site_url('responsive/size/'.(float)$res / 2880 * (float)$size .'/'.$src).'" media="(min-width:'.$res.'px)">';
		}
	}


	$ret []= "\t".'<noscript>';
    $ret []= "\t\t".'<img src="'.site_url('static/img/'.$src).'" alt="'.$alt.'">';
	$ret []= "\t".'</noscript>';
	$ret []= '</picture>';
	return implode("\n",$ret);
}
