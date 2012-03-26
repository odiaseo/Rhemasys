<?php
	class Admin_Service_Setting extends Rhema_Cache {

		protected static $_instance;
		protected $_table;
		protected $_setting = array();
		
		protected  function __construct(){
			$this->_table = MODEL_PREFIX . 'Setting';
		}
		
    	private function __clone() {
    	}		
    	
	    public static function getInstance() {
        	if (null === self::$_instance) {
            	self::$_instance = new self();
        	}

        	return self::$_instance;
    	}			
	}