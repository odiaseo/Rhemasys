<?php

class Admin_Service_Ajax_Bootstrap extends Zend_Application_Bootstrap_Bootstrap {
    
    protected $_resourceLoader; 
    public $frontController;
 
 
	protected function _initAutoload() { 
		$autoloader = new Zend_Application_Module_Autoloader ( array (
			'namespace' => 'Admin', 
			'basePath'  => realpath(APPLICATION_PATH . '/modules/admin') ) 
		);

		$autoloader = new Zend_Application_Module_Autoloader ( array (
			'namespace' => 'Help', 
			'basePath'  => realpath(APPLICATION_PATH . '/modules/help') ) 
		);

		$autoloader = new Zend_Application_Module_Autoloader (array (
				'namespace' => 'Rhema', 
				'basePath'  => APPLICATION_PATH . '/../library/rhema' ) 
		);	
			
		return $autoloader;
	}
 

     
	public function _initDoctrine() {
		require_once 'Doctrine.php';
		$loader = Zend_Loader_Autoloader::getInstance ();
		$loader->pushAutoloader ( array ('Doctrine', 'autoload' ) );	
		$doctrineConfig = $this->getOption ( 'doctrine' ); 
		
		$manager = Doctrine_Manager::getInstance ();
		$manager->setAttribute(Doctrine::ATTR_AUTO_ACCESSOR_OVERRIDE, true);
		$manager->setAttribute(Doctrine::ATTR_USE_NATIVE_ENUM, true);
		$manager->setAttribute(Doctrine::ATTR_MODEL_LOADING, Doctrine::MODEL_LOADING_CONSERVATIVE );
		$manager->setAttribute(Doctrine::ATTR_AUTOLOAD_TABLE_CLASSES, false );
		$manager->setAttribute(Doctrine_Core::ATTR_USE_DQL_CALLBACKS, true);
		
		$manager->connection( $doctrineConfig ['connection_string']['con1'],'con1');
		$manager->connection( $doctrineConfig ['connection_string']['con2'],'con2');
	 
		$manager->registerExtension('Blameable'); 
		$manager->registerExtension('Subsite',$doctrineConfig ['extension_path']);
		 
		return $manager;
	}

 	 
	
	public function run(){ 
 		$handler = new Admin_Service_Ajax_Responce($_REQUEST);
 		$output  = $handler->process($_SERVER['REQUEST_URI']); 	
 		
 		die(Zend_Json::encode($output));
	}
}