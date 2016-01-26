<?php defined('BASEPATH') or exit('No direct script access allowed');

function add_container($params, $template, $default_name = '') {
	$name = get_default($params, 'name', $default_name);
	$state = get_default($params, 'state', null);
	if($name == '') {
		return;
	}
	$CI = &get_instance();

	if($state == null)
		$state = $CI->getState($name);
	else
		$CI->setState($state, $name);
	$args = get_default($params, 'args', array());

	$container = $CI->addContainer($name, $state, $args);
	if(isset($template->block_data) && is_object($template->block_data)
		&& get_class($template->block_data) == 'Pinet_Container') {
			$container->parent = $template->block_data;
	}
	$template->block_data = $container;
}
function smarty_block_container($params, $content = '', $template, &$repeat) {
	if($repeat) {
		add_container($params, $template);
		return;
	}

    $layout = get_default($params, 'layout', false);

	if(isset($template->block_data->parent) && is_object($template->block_data->parent)
		&& get_class($template->block_data->parent) == 'Pinet_Container') {
			$template->block_data = $template->block_data->parent;
	}
	else
		$template->block_data = null;

    if($layout){
        return create_tag('div', array(
            'class' => make_classes(get_default($params, 'class'))
        ),array(),$content);
    }
    return $content;
}
