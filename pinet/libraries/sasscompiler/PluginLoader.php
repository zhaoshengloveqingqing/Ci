<?php defined('BASEPATH') or exit('No direct script access allowed');
	class PluginLoader {
		public function load($dir) {
			$ret = array();
			foreach (new DirectoryIterator($dir) as $fileInfo) {
				if($fileInfo->getExtension() != 'php') continue; // Skip all files except php
				require_once($fileInfo->getPathname());
				$cls = $fileInfo->getBaseName('.php').'_Precompiler';
				if(class_exists($cls)) {
					$ret []= new $cls();
				}
			}
			return $ret;
		}
	}
