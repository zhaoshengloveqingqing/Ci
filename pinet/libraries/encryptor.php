<?php defined("BASEPATH") or exit("No direct script access allowed");

class Encryptor {
	public function __construct() {
		$this->key_path = get_ci_config('encryptor_key', FCPATH.APPPATH.'config/encrypt.key');
		$this->public_key_path = get_ci_config('encryptor_public_key', FCPATH.APPPATH.'config/encrypt_public.key');
		if(file_exists($this->key_path)) {
			ci_log('Loading the private key file from %s', $this->key_path);
			$this->key = file_get_contents($this->key_path);
		}
		else {
			ci_error('Can\'t find private key file at %s', $this->key_path);
		}
		if(file_exists($this->public_key_path)) {
			ci_log('Loading the public key file from %s', $this->public_key_path);
			$this->public_key = file_get_contents($this->public_key_path);
		}
		else {
			ci_error('Can\'t find public key file at %s', $this->key_path);
		}
	}

	public function encrypt($text) {
		if($this->key) {
			$ret = '';
			if(openssl_private_encrypt($text, $ret, $this->key)) {
				return base64_encode($ret);
			}
		}
		return null;
	}

	public function decrypt($text) {
		if($this->public_key) {
			$ret = '';
			if(openssl_public_decrypt(base64_decode($text), $ret, $this->public_key)) {
				return $ret;
			}
		}
		return null;
	}
}
