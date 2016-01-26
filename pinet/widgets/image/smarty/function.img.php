<?php defined('BASEPATH') or exit('No direct script access allowed');

function smarty_function_img($params, $template) {
	$CI = &get_instance();
	$CI->load->helper('html');
	$attr = array();
	$src = get_default($params, 'src');
	$class = get_default($params, 'class');
	$alt = get_default($params, 'alt');
	$title = get_default($params, 'title');
	$width = get_default($params, 'width');
	$height = get_default($params, 'height');

	if($src != '')
		$attr['src'] = $src;
	if($class != '')
		$attr['class'] = $class;
	if($alt != '')
		$attr['alt'] = $alt;
	if($title != '')
		$attr['title'] = $title;
	if($width != '')
		$attr['width'] = $width;
	if($height != '')
		$attr['height'] = $height;

	return img($attr);
}
