<?php defined('BASEPATH') or exit('No direct script access allowed');

function smarty_function_elapsed_time($params, $template) {
	global $BM;
	return $BM->elapsed_time('total_execution_time_start', 'total_execution_time_end');
}
