<?php 

class Rhema_Controller_Action_Helper_MigrateDb  extends Zend_Controller_Action_Helper_Abstract{
	
	public function direct(){
		set_time_limit(0);
		$result         = array();
		$options        = Zend_Controller_Front::getInstance()->getParam('bootstrap')->getOptions();
		$doctrineConfig = $options['doctrine'];	
		$mgPath         = $doctrineConfig['migrations_path']; 	
	 	$errorMsg       = '';
	 	
		if(!file_exists($mgPath)){
			@mkdir($mgPath, 0775, true);
		}
			
		$migration 			  = new Doctrine_Migration($doctrineConfig['migrations_path'], 'admin');
		$result['oldVersion'] = $migration->getCurrentVersion();
				
		try{		  			 
			$migration->migrate();							
		}catch(Exception $e){
			$errorMsg = $e->getMessage();
		};
			
		$result['newVersion'] = $migration->getCurrentVersion();
		
		if($result['oldVersion'] == $result['newVersion']){
			$result['error'] = $errorMsg ;
		} 
		return $result;
	}
}
	