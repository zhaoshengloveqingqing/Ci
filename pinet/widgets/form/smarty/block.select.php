<?php defined('BASEPATH') or exit('No direct script access allowed');

if(!function_exists('smarty_function_input')) {
	require_once(dirname(__FILE__).'/function.function.input.php');
}

function smarty_block_select($params, $content, $template, &$repeat) {
	if($repeat) // Skip the first time
		return;

	$attr = get_attr($params, $template);
	$field = get_field($params, $template);

	$CI = &get_instance();
	if(isset($field->model)) {
		$CI->load->model($field->model);
		$parent_vars = $template->parent->tpl_vars;
		$form_data = get_form_data($parent_vars);
		$options = widget_select_get_options(get_default($params, 'options', array()), $form_data, $field, $CI->{$field->model});
	}
	else {
		$options = get_default($params, 'options', array());
	}

	$attr['url'] = current_url();
	if (isset($field->filters)) {

		$hasfield = false;
		$rel = array();
		foreach ($field->filters as $key => $filter) {
			if (isset($filter->field)) {
				$rel[] = $filter->field;
				$hasfield = true;
			}
		}

		if ($hasfield) {
			$attr['data-rel'] = implode(',', $rel);
		}
	}

	if (isset($params['noselectboxit']) && $params['noselectboxit'] != '') {
		$attr["data-no-selectBoxIt"] = true;
	}

	$parent_vars = $template->parent->tpl_vars;
	$form_data = get_form_data($parent_vars);
	$selected = get_default($params, 'selected', array());
	$extra = _parse_form_attributes($attr, array());
	if(count($selected) == 0)
		$selected = $attr['value'];
	return form_dropdown($attr['name'], $options, $selected, $extra);
}

