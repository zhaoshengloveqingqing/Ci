<?php
	class AutoConstruct_Precompiler {
		public function suffix($compiler) {
			foreach($compiler->sasses as $s) {
				$s = str_replace('.scss', '', $s);
				$basename = basename($s);
				$name = str_replace('/', '_', $s);

				if($basename != $name) {
					$this->addConstruct($basename, $compiler);
				}
				$this->addConstruct($name, $compiler);
			}
		}

		protected function addConstruct($name, $compiler) {
			$the_name = 'init_'.$name;
			if(strpos($compiler->content, $the_name) !== FALSE) {
				$compiler->suffix .= '@include '.$the_name.'();'."\n";
			}
		}
	}
