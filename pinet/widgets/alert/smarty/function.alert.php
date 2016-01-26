<?php defined('BASEPATH') or exit('No direct script access allowed');

function smarty_function_alert($params, $template) {
	$CI = &get_instance();
	$ret = array();
	ci_log('The alerts is ', $CI->getAlerts());
	$origin_class = get_default($params,'class');
	foreach($CI->getAlerts() as $alert) {
		$classes = make_classes($origin_class, 'alert');
		$classes [] = 'pinet-alert-'.$alert->type;
		$classes [] = 'alert-map-item';
		$params['class'] = $classes;

		$yes_btn = create_tag('button',
			array('class'=>array('btn', 'pinet-alert-btn-default', 'yes')),
			array(), 'YES');

		$no_btn = create_tag('button',
			array('class'=>array('btn', 'pinet-alert-btn-default', 'no')),
			array(), 'NO');

		$content = $yes_btn;

		$menu = create_tag('div', array('class'=>array('menu')), array(), $content);
		$info =  create_tag('div', array('class'=>'info'), array(
		), $alert->message);


		$alert_inner = $menu . $info;

		$ret[] = create_tag('div', $params, array(
			'role' => 'alert'
		), $alert_inner);
	}
	$CI->clearAlerts();
    $alerts = '';
    if($ret){
    	$alertClasses = make_classes('pinet-alert-map');
    }
    else {
    	$alertClasses = make_classes('pinet-alert-map','pinet-hide');
    }
    $alertmap_inner =  create_tag('div', array('class'=>'pinet-alert-map-inner'), array(
    ), implode("\n", $ret));

	$alerts = create_tag('div', array('class'=>$alertClasses), array(), $alertmap_inner);

	return $alerts;
}
