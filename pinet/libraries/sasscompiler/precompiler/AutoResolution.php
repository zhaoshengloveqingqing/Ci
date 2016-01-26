<?php
	class AutoResolution_Precompiler {

		public function suffix($compiler) {
			if(!isset($compiler->resolutions))
				return;

			$this->addBeforeResponsive($compiler);
			foreach($compiler->resolutions as $k => $r) {
				 if (is_numeric($k) && is_string($r) && !is_numeric($r) ) {
				 	$screen_width = $k;
				 	$alias_width =$r;
				 }
				 else if (is_numeric($k) && is_numeric($r)) {
				 	$screen_width = $r;
				 	$alias_width = 0;
				 }
				 else if (is_string($k) && !is_numeric($k)) {
				 	$screen_width = $r;
				 	$alias_width =$k;
				 }
				 else {
				 	$screen_width = $k;
				 	$alias_width =0;
				 }

				$next_value = get_array_next($compiler->resolutions, $r);
				if (is_string($next_value[1])) {
					$next_screen_width = $next_value[0];
				}
				else {
					$next_screen_width = $next_value[1];
				}

				if (!$next_screen_width) {
					$next_screen_width = 2881;
				}

				$this->addBeforeResolution($compiler, $screen_width);
				$compiler->suffix .= '@media screen and (min-width: '.$screen_width.'px) {'."\n";
				$compiler->suffix .= '$screen-width:'. $screen_width.';';
			 	$compiler->suffix .= '$alias-width:'.$alias_width.';';
			 	$this->addPrependResolution($compiler, $screen_width);
				foreach($compiler->sasses as $s) {
					$s = str_replace('.scss', '', $s);
					$basename = basename($s);
					$name = str_replace('/', '_', $s);
					if($basename == $name) {
						$this->addConstruct($basename, $compiler, $screen_width.','.$alias_width);
					}else {
						$this->addConstruct($name, $compiler, $screen_width.','.$alias_width);
					}
				}
				$this->addAppendResolution($compiler, $screen_width);
				$compiler->suffix .= '}'."\n";
				$compiler->suffix .= '@media screen and (min-width: '.$screen_width.'px) and (max-width: '.($next_screen_width - 1).'px)  {'."\n";
				$compiler->suffix .= '$screen-width:'. $screen_width.';';
			 	$compiler->suffix .= '$alias-width:'.$alias_width.';';
			 	$compiler->suffix .= '$next-screen-width:'.$next_screen_width.';';
			 	// $this->addPrependResolution($compiler, $screen_width);
				foreach($compiler->sasses as $s) {
					$s = str_replace('.scss', '', $s);
					$basename = basename($s);
					$name = str_replace('/', '_', $s);
					if($basename == $name) {
						$this->addSection($basename, $compiler, $screen_width.','.$alias_width.','.$next_screen_width);
					}else {
						$this->addSection($name, $compiler, $screen_width.','.$alias_width.','.$next_screen_width);
					}
				}
				$compiler->suffix .= '}'."\n";
				$compiler->suffix .= '@media screen and (min-width: '.$screen_width.'px) and (max-width: '.($next_screen_width - 1).'px)  {'."\n";
				$compiler->suffix .= '$screen-width:'. $screen_width.';';
			 	$compiler->suffix .= '$alias-width:'.$alias_width.';';
			 	$compiler->suffix .= '$next-screen-width:'.$next_screen_width.';';
		 		$lasts = end($compiler->sasses);
				$lasts = str_replace('.scss', '', $lasts);
				$basename = basename($lasts);
				$name = str_replace('/', '_', $lasts);
				if($basename == $name) {
					$this->addModule($basename, $compiler, $screen_width.','.$alias_width.','.$next_screen_width);
				}else {
					$this->addModule($name, $compiler, $screen_width.','.$alias_width.','.$next_screen_width);
				}
				$compiler->suffix .= '}'."\n";
				// $this->addAfterResolution($compiler, $screen_width);
			}
			$this->addAfterResponsive($compiler);
		}

		protected function addConstruct($name, $compiler, $args) {
			$the_name = 'responsive_'.$name;
			if(strpos($compiler->content, $the_name) !== FALSE) {
					$compiler->suffix .= "\t".'@include '.$the_name.'('.$args.');'."\n";
			}
		}

		protected function addModule($name, $compiler, $args) {
			$the_name = 'module_'.$name;
			if(strpos($compiler->content, $the_name) !== FALSE) {
					$compiler->suffix .= "\t".'@include '.$the_name.'('.$args.');'."\n";
			}
		}

		protected function addSection($name, $compiler, $args) {
			$the_name = 'section_'.$name;
			if(strpos($compiler->content, $the_name) !== FALSE) {
					$compiler->suffix .= "\t".'@include '.$the_name.'('.$args.');'."\n";
			}
		}

		protected function addPrependResolution($compiler, $res) {
			$the_name = 'prepend_resolution_'.$res;
			if(strpos($compiler->content, $the_name) !== FALSE) {
				$compiler->suffix .= "\t".'@include '.$the_name.'();'."\n";
			}
		}

		protected function addAppendResolution($compiler, $res) {
			$the_name = 'append_resolution_'.$res;
			if(strpos($compiler->content, $the_name) !== FALSE) {
				$compiler->suffix .= "\t".'@include '.$the_name.'();'."\n";
			}
		}

		protected function addBeforeResolution($compiler, $res) {
			$the_name = 'before_resolution_'.$res;
			if(strpos($compiler->content, $the_name) !== FALSE) {
				$compiler->suffix .= "\t".'@include '.$the_name.'();'."\n";
			}
		}

		protected function addAfterResolution($compiler, $res) {
			$the_name = 'after_resolution_'.$res;
			if(strpos($compiler->content, $the_name) !== FALSE) {
				$compiler->suffix .= "\t".'@include '.$the_name.'();'."\n";
			}
		}

		protected function addBeforeResponsive($compiler) {
			$the_name = 'before_responsive';
			if(strpos($compiler->content, $the_name) !== FALSE) {
				$compiler->suffix .= "\t".'@include '.$the_name.'();'."\n";
			}
		}

		protected function addAfterResponsive($compiler) {
			$the_name = 'after_responsive';
			if(strpos($compiler->content, $the_name) !== FALSE) {
				$compiler->suffix .= "\t".'@include '.$the_name.'();'."\n";
			}
		}

	}
