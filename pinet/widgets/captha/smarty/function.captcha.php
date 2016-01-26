<?php defined('BASEPATH') or exit('No direct script access allowed');

function smarty_function_captcha($params, $template) {
	$CI = &get_instance();
	$CI->load->library('securimage/securimage');
	echo $CI->securimage->getCaptchaHtml();
}
