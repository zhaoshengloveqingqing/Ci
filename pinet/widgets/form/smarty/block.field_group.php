<?php defined('BASEPATH') or exit('No direct script access allowed');


if(!function_exists('smarty_function_input')) {
	require_once(dirname(__FILE__).'/function.input.php');
}

/**
 * The field group has many states, list as below:
 *
 * None: The field group won't show in the form
 * Hidden: The field group is hidden from user
 * Disabled: The field group is disabled
 * Readonly: The field group is readonly
 *
 * The field state can be set in 4 ways:
 *
 * 1. By the tag itself
 * 2. By the security engine
 * 3. By the controller
 * 4. By the form configuration
 *
 * The priority of the value is like the list too. So, the tag itself
 * will have the highest priority than others.
 */
function smarty_block_field_group($params, $content = '', $template, &$repeat) {
	$CI = &get_instance();
	$CI->load->helper('form');

	$labelClass = get_default($params, 'labelClass');
	$inputClass = get_default($params, 'inputClass');
	$glyphicon = get_default($params, 'glyphicon');

	if($labelClass == "") {
		$labelClass = "pinet-form-label";
	}

	if($inputClass == "") {
		$inputClass = "pinet-form-input";
	}

	$f = get_field($params, $template);

	if($f == null)
		return '';

	if($repeat) { // This is the start part
		// Set the field to parent's template variables to let the form fields to access it
		$parent_vars['current_form_field'] = new Smarty_Variable($f);
		$template->block_data = $f;
		return;
	}

	if($f->state == 'none') {
		ci_log('The field is not allowed for this security configuration', $f);
		return '';
	}

	$layout = get_default($params, 'layout', array());

	$classes = make_classes(get_default($params, 'class'), array('form-group', 'control-group'));
	$label_layout_class = 'col-1280-2';
	$element_layout_class = 'col-1280-10';
	if(is_array($layout)) {
		if(isset($layout['label']))
			$label_layout_class = 'col-1280-'.$layout['label'];
		if(isset($layout['element']))
			$element_layout_class = 'col-1280-'.$layout['element'];
		if(isset($layout['mobile-label']))
			$label_layout_class .= ' col-320-'.$layout['mobile-label'];
		if(isset($layout['mobile-element']))
			$element_layout_class .= ' col-320-'.$layout['mobile-element'];
	}
	else {
        if(isset($f->container))
            $template->block_data = $f->container;
        else
            $template->block_data = null;
		return create_tag('div', array(
			'class' => $classes
		), array(), $content);
	}

	// Creating the label div
	if (!isset($f->label)) {
		$f->label = '';
	}
	$label = create_tag('label', array(
		'for' => $f->getId(),
		'class' => array($label_layout_class, $labelClass, 'control-label', isset($f->required)?'form_field_required':'')
	), array(), $f->label);


	if(trim($content) == '') { // If we don't have any sub element, just add an input
		$inputData = array();
		// Check field type
		if (isset($f->type) && $f->type != '') {
			$inputData['type'] = $f->type;
		}
		$content = smarty_function_input($inputData, $template);
	}

	if(is_string($glyphicon) && $glyphicon != '') {
		$classes = make_classes($classes, array('has-feedback'));
		$content .= create_tag('span', array('class' => array('glyphicon', 'glyphicon-'.$glyphicon, 'form-control-feedback')), array(), '');
	}

	// Adding the error notification area
	$content .= create_tag('p', array('class' => array('help-block')), array(), '');

	// Creating the element div
	$element = create_tag('div', array('class' => array($element_layout_class, $inputClass)), array(), $content);

	if(isset($f->container))
		$template->block_data = $f->container;
	else
		$template->block_data = null;

	if($f->state == 'hidden') {
		$classes []= 'hidden';
	}
	// Jam them together
	return create_tag('div', array(
		'class' => $classes
	), array(), $label."\n".$element);
}
