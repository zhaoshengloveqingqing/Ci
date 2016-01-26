<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

class Transaction_Manager {
	public function handle($interceptor) {
		$CI = &get_instance();
		if(isset($CI->db)) {
			$CI->log('Starting the transaction for method %s of %s', $interceptor->call_method,
				get_class($CI));
			$CI->db->trans_start();
		}

		$ret = $CI->_process($interceptor->call_method, $interceptor->args);

		if(isset($CI->db)) {
			$CI->log('Completing the transaction for method %s of %s', $interceptor->call_method,
				get_class($CI));
			$CI->db->trans_complete();
		}

		if ($CI->db->trans_status() === false) { // We got an transaction error here
			$CI->error('Error in the transaction for method %s of %s', $interceptor->call_method,
				get_class($CI));
			switch($interceptor->tag) {
			case 'error':
				trigger_error(sprintf('Error in the transaction for method %s of %s', $interceptor->call_method,
				get_class($CI)));
			}
		}
		
		return $ret;
	}
}
