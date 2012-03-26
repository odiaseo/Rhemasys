<?php

class Rhema_Model_Template_Listener_Cache implements Doctrine_Record_Listener{
	    
	protected $_options = array();
	 
	public function __construct(array $options = array())    {
        $this->_options = $options; 
    }
    
     public function preDqlSelect(Doctrine_Event $event) { 		
     	if(extension_loaded('apc')) {
			$driver = new Doctrine_Cache_Apc();
			$query  = $event->getQuery(); 
			$query->useResultCache($driver)
				  ->useQueryCache($driver);	
     	}	 	     
    }

}
