<?php defined("BASEPATH") or exit("No direct script access allowed");

class Rule_Manager {
	public function __construct() {
		$this->CI = &get_instance();

		// Load the clips rule engine
		$this->CI->load->library('clips');
		$this->clips = $this->CI->clips;
	}

	public function handle($inteceptor) {
		$re = new ReflectionAnnotatedClass(get_class($this->CI));
		$method = $re->getMethod($inteceptor->call_method);
		if($method->hasAnnotation('RunRule')) {
			$run = $method->getAnnotations('RunRule');
			$run = $run[0];

			// Clear the clips context if needed
			if($run->clear)
				$this->clips->clear();

			if($run->templates) {
				if(is_string($run->templates))
					$run->templates = array($run->templates);
				foreach($run->templates as $t) {
					$this->clips->template($t);
				}
			}

			// Let the call method to add the facts
			$ret = $inteceptor->process();

			// Load the rules
			if($run->rules) {
				if(is_string($run->rules))
					$run->value = array($run->rules);

				if(is_array($run->rules)) {
					$run->value = $run->rules;
				}
			}

			if(isset($run->value)) {
				if(is_string($run->value))
					$run->value = array($run->value);

				foreach($run->value as $v) {
					$this->clips->load('ci://config/rules/'.$v);
				}
			}

			// Run the clips context
			$this->clips->run();

			// Return the original return
			return $ret;
		}
		return $inteceptor->process();
	}
}
