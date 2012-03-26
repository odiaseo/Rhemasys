<?php
 
	class Rhema_Widget_Controller_Menu extends Rhema_Widget_Abstract{	    
	    
	    public function indexMethod(){
	    	
	    }
	      
		public function mainMenuMethod(){
			$request     = $this->getRequest();
	    	$title       = $request->getParam('title', 'Main');
			$option      = array('title' => $title);
			$result 	 = $this->_utility->getCached('menu')->getMainMenu($option);
			Zend_Registry::set('menuContainer',$result); 
			$this->view->navData = $result; 	
	    }
	
    
	    
	    public function displayMethod(){
	    	$output     = '';
	    	$data		= $this->getRequest()->getParam('menu-data', null);
	    	if($data){
	    		$model      = key($data);
	    		$option     = $data[$model];
	    		//$option 	= array('title' => 'Account');
	    		$result     = $this->_utility->getCached('menu')->getMainMenu($option);
	    		$this->view->navData = $result;
	    		$output     = $this->view->render('menu/display.phtml');
	    	}
	    	return $output;
	    }
	    
	    public function breadcrumbMethod(){
    		$option        = array('title' => 'Main');
    		$nav           = $this->_utility->getCached('menu')->getMainMenu($option);
    		
	    	if(isset($nav['container'])){
	    		$container     = $nav['container'];
	    	}else{
	    		$container     = new Zend_Navigation(array());
	    	}
	    	
	    	$this->view->menuContainer = $container;
	    }
	    

	    
	    public function dirMethod(){
	    	$request = $this->getRequest();
	    	$dirOnly = $request->getParam('dir') == 1 ? true : false;
	    	$actions = $request->getParam('act') == 1 ? true : false;
	    	//$path    = $request->getParam('path','');	
	    	$module  = $request->getParam('m', null);
	    	$contr   = $request->getParam('c',null);    	
	    	
	    	if($module){
	    		$path		= '/modules/' . $module . '/views/scripts';
	    	}
	    	
	    	if($contr){
	    		$path      .= '/' . $contr;
	    	}
	    	$arr	 = Rhema_Util::getDir($path, $dirOnly, $actions, false);
	    	
	    	$this->_utility->setAjaxData(Zend_Json::encode($arr));
	    }    
	}