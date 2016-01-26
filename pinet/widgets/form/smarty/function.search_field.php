<?php defined('BASEPATH') or exit('No direct script access allowed');

function smarty_function_search_field($params, $template) {
	$attr = '';
	foreach ($params as $key => $value) {
		$attr .= $key.'="'.$value.'"';
	}
	$text = <<<TEXT
	{field_group class="search" field=search layout=fasle}
		{input $attr}
	{/field_group}
TEXT;
	return $template->fetch('string:'.$text);
}