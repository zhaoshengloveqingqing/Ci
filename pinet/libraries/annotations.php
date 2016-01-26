<?php defined('BASEPATH') or exit('No direct script access allowed');

require_once(dirname(__FILE__).'/addendum/annotations.php');

class Annotations { // The annotations support
}

class RunRule extends Annotation {
	public $clear = true;
	public $rules = array();
	public $templates = array();
}
