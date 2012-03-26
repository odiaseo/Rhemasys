<?php
	class Elm_Bootstrap extends Zend_Application_Module_Bootstrap {
		
		public function _initModuleResourceAutoloader(){
			$this->getResourceLoader()->addResourceTypes(array(
				'modelResource' => array(
						'path'		=> 'models/resources',
						'namespace'	=> 'Resource',
					)
				));			
		}
		
		public static function autoload($path) {
			include str_replace ( '_', '/', $path ) . '.php';
			return $path;
		}
	}