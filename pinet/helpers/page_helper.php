<?php defined("BASEPATH") or exit("No direct script access allowed");

function createElement($arg, $type, $prefix, $suffix){
	if(!empty($arg[$type])){
		if(is_array($arg[$type])){
			foreach ($arg[$type] as $v){
				echo  $prefix.base_url(CSS_PATH.$v).$suffix;
			}
		}else{
			echo $prefix.base_url(CSS_PATH.$arg[$type]).$suffix;
		}
	}	
}

function createCommonCSS($arg) {
	$type = 'COMMONCSS';
	$prefix = '<link rel="stylesheet" href="';
	$suffix = '.css">';
	createElement($arg, $type, $prefix, $suffix);
}

function createCSS($arg) {
	$type = 'CSS';
	$prefix = '<link rel="stylesheet" href="';
	$suffix = '.css">';
	createElement($arg, $type, $prefix, $suffix);	
}

function createJS($arg) {
	$type = 'JS';
	$prefix = '<script type="text/javascript" src="';
	$suffix = '.js"></script>';
	createElement($arg, $type, $prefix, $suffix);
}

function p($obj){
	echo "<pre>";
	print_r($obj);
	echo "</pre>";
}
