<?php defined('BASEPATH') or exit('No direct script access allowed');

function smarty_function_closebutton($params, $template) {
	$span = create_tag("span", array("aria-hidden"=>true), array(), '&times;');
	$sr_only_span = create_tag("span", array("class"=>"sr-only"), array(), 'close');
	$content = $span.$sr_only_span;
	return create_tag('button', array('class'=>'close'),array(),$content);
}