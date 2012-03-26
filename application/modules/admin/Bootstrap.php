<?php
class Admin_Bootstrap extends Zend_Application_Module_Bootstrap {
		
    protected function _initModuleAutoloader() {        
        $this->_resourceLoader = new Zend_Application_Module_Autoloader(array(
            'namespace' => 'Admin',
            'basePath'  => APPLICATION_PATH . '/modules/admin',
        ));
        
        $this->_resourceLoader->addResourceTypes(array(
              'form' => array(
              'path'      => 'forms',
              'namespace' => 'Form',
            ), 
              'service' => array(
              'path'      => 'services',
              'namespace' => 'Service',
            )
        ));
       
        
    }	  
}