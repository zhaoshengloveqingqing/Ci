<?php defined('BASEPATH') or exit('No direct script access allowed');

function dropdown_button_process_item($item) {
	if(is_object($item) && get_class($item) == 'Action') {
		return create_tag('a', array(
			'title' => $item->label,
			'href' => $item->uri(),
		), array(), $item->label);
	}
	else if(is_array($item) && isset($item['uri'])) {
		$item = (object) $item;
		return create_tag('a', array(
			'title' => $item->label,
			'href' => $item->uri,
		), array(), $item->label);
	}
	return '';
}

function smarty_block_dropbutton($params, $content = '', $template, &$repeat) {
	if($repeat) {
		return;
	}

	$ret = array();

	$show = get_default($params, 'show', 'default');
	$params['class'] = make_classes('btn', 'btn-'.$show, 'dropdown-toggle', get_default($params, 'class', null));
	$label = get_default($params, 'label', '').' ';
	if(!isset($params['title'])) {
		$params['title'] = strip_tags($label);
	}

	$params['data-toggle'] = 'dropdown';
	$params['aria-expanded'] = 'false';
	$items = get_default($params, 'items', null);
	if(isset($params['items']))
		unset($params['items']);

	$split = get_default($params, 'split', null);
	if(isset($split) && $split == true) {
		unset($params['split']);
		$tags []= create_tag('button', array('class'=>array('btn','btn-'.$show)), array(), $label);
		$label = '';
	}

	$tags []= create_tag('button', $params, array(), $label.create_tag('span', array('class' => 'caret'), array(), ''));


	if($items) {
		$list_tags = array();
		foreach($items as $item) {
			$a = dropdown_button_process_item($item);
			if($a != '')
				$list_tags []= create_tag('li', array(), array(), dropdown_button_process_item($item));
			else
				$list_tags []= create_tag('li', array('class' => 'divider'), array(), '');
		}
		$tags []= create_tag('ul', array(
			'class' => 'dropdown-menu',
			'role' => 'menu'
		), array(), implode("\n", $list_tags));
	}
	else {
		$tags []= $content;
	}

	$direction = get_default($params, 'direction', '');
	$dropbuttonClass = array('btn-group');
	if($direction == 'up') {
		$dropbuttonClass[] = "dropup";
	}
	return create_tag('div', array('class' => $dropbuttonClass), array(), implode("", $tags));
}
