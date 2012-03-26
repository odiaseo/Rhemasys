<?php
class Admin_BuildController extends Zend_Controller_Action {
	
	protected $_rhema 		= 'http://www.rhema-webdesign.com/rest/index';
	protected $_buildList   = array();
	
    public function init() {
        /* Initialize action controller here */
        parent::init(); 
    }

    public function indexAction(){
    	$this->createDefaultFiles('dirname');
    }
    
    public function getBuildList($dir) {	 
    	if(!count($this->_buildList)){
    		$client = new Zend_Rest_Client($this->_rhema);
    		$return =  $client->arg($dir)->getSetUpData()->get();
    		if($return->isSuccess()){
    			$this->_buildList = $return->public();
    		} 
    	}
    	return $this->_buildList;
    }
    
    public function updateDatabase(){
    	
    }
    
    public function createDefaultFiles($dirname){
    	
    	$data	= $this->getBuildList($dirname);
    	foreach($data as $type => $itemsArray){
    		if('public' == $type){
	    		$buildDir	= realpath(APPLICATION_PATH . '/../' . PUBLIC_DIR);
    		}else{
    			$buildDir	= realpath(APPLICATION_PATH . '/../' . ADMIN_SITE_DIR);
    		}
    		
	    	foreach($itemsArray as $item){    		 
				$file   = implode(DIRECTORY_SEPARATOR , array($buildDir, $dirname, $item)) ;    			 
				if(is_file($file)){   			
					mkdir(dirname($file), 0644, true);
					$content = $this->getDefaultContent(basename($file)) ;
					file_put_contents($file,$content);
				}else{
					mkdir($file, 0644, true);
				}    		 
	    	}
    	}
    }
 
    
    public function getDefaultContent($file = ''){
    	
    	switch($file){
    		
    		default:
    		$content = '';	
    	}
    	
    	return $content;
    }
}