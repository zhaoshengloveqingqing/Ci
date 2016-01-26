<?php defined('BASEPATH') or exit('No direct script access allowed');


function smarty_block_report($params, $content = '', $template, &$repeat) {
    if($repeat)
        return;
    $CI = &get_instance();
    $CI->load->library(array('report'));
    $name = get_default($params, 'name');
    $begin = get_default($params, 'begin');
    $end = get_default($params, 'end');
    $args = get_default($params, 'args');
    $args = count($args) ? $args : array();
    $mode = get_default($params, 'mode');
    $mode = $mode ? $mode : 'day';
    return "var $name = new Highcharts.Chart(".$CI->report->show($name, $begin, $end, $args, $mode).");";
}