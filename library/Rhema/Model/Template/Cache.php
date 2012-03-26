<?php

class  Rhema_Model_Template_Cache extends Doctrine_EventListener{
   protected $_options = array(
       'driver' => null,
   ); 
   
	public function __construct(array $options = array())    {
        $this->_options = $options; 
    }
         
    public function preDqlSelect(Doctrine_Event $event) { 		
        if(extension_loaded('apc')) { 
			$query  = $event->getQuery(); 
			$query->useResultCache(true)
				  ->useQueryCache(true);	
     	} 	 	     
    }
}