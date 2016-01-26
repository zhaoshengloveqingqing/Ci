<?php defined('BASEPATH') or exit('No direct script access allowed');

require_once(dirname(__FILE__).'/lessphp/lessc.inc.php');

define('LESS_COMPILE_DIR', APPPATH.'cache/css');

class Less_Compiler {
	public function __construct() {
		$this->compiler = new lessc;
	}

	function _compile($path, $less, $out = null) {
		return $this->compiler->compileString($path, $less, $out);
	}

	function compile($less) {
		$path = FCPATH.$less;
		if(file_exists($path)) {
			$path_parts = pathinfo($path);
			$name = $path_parts['filename'];
			if(!file_exists(FCPATH.LESS_COMPILE_DIR)) {
				mkdir(FCPATH.LESS_COMPILE_DIR);
			}
			$dir = basename(dirname($less));
			$out = FCPATH.LESS_COMPILE_DIR.'/'.$dir.'/'.$name.'.css';
			if(!file_exists($out)) {
				if(!file_exists(FCPATH.LESS_COMPILE_DIR.'/'.$dir)) {
					mkdir(FCPATH.LESS_COMPILE_DIR.'/'.$dir);
				}
				$content = file_get_contents($path);
				$content = '@site_base:"'.site_url('')."\";\n".$content;
				$content .= '@fcpath:"'.FCPATH."\";\n".$content;
				$this->_compile($path, $content, $out);
			}
			return LESS_COMPILE_DIR.'/'.$dir.'/'.$name.'.css';
		}
		else {
			ci_error('The less file %s is not exists!', $less);
		}
	}
}
