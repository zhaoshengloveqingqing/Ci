<?php defined("BASEPATH") or exit("No direct script access allowed");

/**
 * The helper library for pinet controller to support themes.
 */
class Theme_Manager {
	public function handle($i) {
		$template = $i->call_method; // Yes, this is a little bit tricky, since the render inteceptor is added after the desigin, and try to not interfere with the origin design.
		$args = $i->args;

		$CI = &get_instance();
		if(method_exists($CI, 'getTheme')) {
			$theme = $CI->getTheme();
		}
		$module = &get_current_module();

		if(isset($module)) { // We're in a module
			$module_path = dirname(get_class_script_path(get_class($module)));
			if(method_exists($module, 'getTheme')) {
				$theme = $module->getTheme();
			}
			if(isset($theme)) {
				ci_log('Adding the path %s', $module_path.'/views/themes/'.$theme.'/');
				$CI->addTemplateDir($module_path.'/views/themes/'.$theme.'/');
				ci_log('The template dirs is ', $CI->smarty->template_dir);
			}
		}
		else { // We're in a controller
			if(isset($theme)) {
				$CI->addTemplateDir(FCPATH.APPPATH.'/views/themes/'.$theme);
			}
		}
		if(isset($theme)) {
			if(isset($CI->sasscompiler)) {
				foreach(array(APPPATH, 'pinet/') as $d) {
					$p = FCPATH.$d.'static/scss/themes/'.$theme.'/';
					ci_log('Adding the include path for scss %s', $p);
					$CI->sasscompiler->addIncludePath($p, 0);
				}
			}
		}
	}
}
