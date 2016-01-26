<?php defined('BASEPATH') or exit('No direct script access allowed');

	if(!extension_loaded('sass')) {
		show_error('Cant\'t find any sass plugin installed!!');
		die;
	}

	require_once(dirname(__FILE__).'/sasscompiler/PluginLoader.php');

	/**
	 * Loading all the sass file altogether, and compile the string as sass
	 */
	class SassCompiler {
		public function __construct() {
			$this->sass = new Sass();
			if(!get_ci_config('debug_sass', false)) {
				$this->sass->setStyle(Sass::STYLE_COMPRESSED);
			}
			$this->sasses = array();
			$this->includePathes = array();
			$this->resolutions = get_ci_config('resolutions');
			foreach(array(APPPATH, 'pinet/') as $p) {
				$this->includePathes []= FCPATH.$p.'static/scss/';
			}
		}

		public function widget($name) {
			if(is_array($name)) {
				foreach($name as $n) {
					$this->widget($n);
				}
			}
			else {
				if(isset($this->theme)) {
					$this->addSass('/themes/'.$this->theme.'/widgets/'.$name);
				}
			}
		}

		public function common($name) {
			if(is_array($name)) {
				foreach($name as $n) {
					$this->common($n);
				}
			}
			else
				$this->addSass('common/'.$name);
		}

		public function precompile() {
			if(!isset($this->p)) {
				$this->p = new PluginLoader();
				$this->ps = $this->p->load(dirname(__FILE__).'/sasscompiler/precompiler');
			}

			if(isset($this->theme)) {
				foreach(array(APPPATH, 'pinet/') as $p) {
					$this->addIncludePath(FCPATH.$p.'static/scss/themes/'.$this->theme);
				}
			}

			$this->prefix = '';
			$this->suffix = '';
			$this->addSass('variables', 0); // Auto added the variables scss before compile
			foreach($this->ps as $plugin) {
				if(method_exists($plugin, 'prefix')) {
					$plugin->prefix($this);
				}
			}

			$this->content = $this->prefix."\n";
			foreach($this->sasses as $sass) {
				$this->content .= $this->readFile($sass)."\n";
			}


			foreach($this->ps as $plugin) {
				if(method_exists($plugin, 'suffix')) {
					$plugin->suffix($this);
				}
			}
			$this->content .= $this->suffix;
			return $this->content;
		}

		public function readFile($file) {
			foreach($this->includePathes as $path) {
				$filepath = $path.'/'.$file;
				if(file_exists($filepath) && is_file($filepath) &&
					is_readable($filepath)) {
					return file_get_contents($filepath);
				}
			}
			return '';
		}

		public function compile() {
			$args = func_get_args();
			if($args) {
				foreach($args as $sass) {
					$this->addSass($sass);
				}
			}

			$content = $this->precompile();
			$this->sass->setIncludePath(implode(PATH_SEPARATOR, $this->includePathes));
			$this->sass->setImagePath(site_url(APPPATH.'/static/img/'));
			return $this->sass->compile($content);
		}

		public function addIncludePath($path, $index = -1) {
			if(array_search($path, $this->includePathes) === FALSE) {
				if($index == -1)
					$this->includePathes []= $path;
				else
					array_splice($this->includePathes, $index, 0, $path);
			}
		}

		public function lib($name, $version = '1.0.0') {
			$this->addIncludePath(dirname(__FILE__).'/css/lib/'.$name.'-'.$version);
		}

		public function addSass($file, $index = -1) {
			$pi = pathinfo($file);

			if(!isset($pi['extension']) || $pi['extension'] != 'scss')
				$file .= '.scss';

			if(array_search($file, $this->sasses) === FALSE) {
				if($index == -1)
					$this->sasses []= $file;
				else
					array_splice($this->sasses, $index, 0, $file);
			}
		}
	}
