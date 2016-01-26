<?php defined("BASEPATH") or exit("No direct script access allowed");

class Sample_Widget extends Pinet_Widget {
	public function __construct() {
		parent::__construct();
	}

	public function init() {
		parent::init();
		ci_log('Sample Widget is initializing.....');
	}
}
