<?php

class Cms_MenuController extends Zend_Controller_Action{
	
	
    public function init(){
    
        /* Initialize action controller here */
        parent::init();
    	$this->_table  = $this->_request->getParam('table');
		$rootOnly      = $this->_request->getParam(Admin_Model_AdminMenu::TYPE_ROOTS_ONLY);
		$ophanOnly     = $this->_request->getParam(Admin_Model_AdminMenu::TYPE_OPHANS);
		
		if($rootOnly){
			$this->_urlParams[Admin_Model_AdminMenu::TYPE_ROOTS_ONLY] = true ;
		}elseif($ophanOnly){
			$this->_urlParams[Admin_Model_AdminMenu::TYPE_OPHANS] = true;
		}        
    }

    /**
     * Initial screen displayed on first entry.
     *
     */
        
    public function indexAction(){
    	$this->_table = $this->_request->getParam('table','menu'); 
    	$this->_helper->setupMenuTab($this->_table);
    	//$this->showMenuTabs($scope);    	
    	//$this->displayGrid(array());
    }
    
	public function rootAction(){
		
	}   

    public function frontendAction(){
    	$table		= 'menu';
    	$this->_helper->setupMenuTab($table); 
    }
    
	
	public function siteaccessAction(){  
		$table      = 'menu';		 
		if('updateAcl' == $this->_getParam('task')){
			$this->_helper->accessControl->updateAcl($table); 
		}
		$this->_helper->accessControl->showTabs($table);
	}	
  
    public function treeAction(){ 
    	$helper = $this->_helper->getHelper('SetupMenuTab');
    	$return = $helper->updateTree();
    	$this->_helper->json->sendJson($return);
    	exit();
    }
 

    protected function _getScope($table){
        	switch($table){
    		case 'admin_menu': $scope = Admin_Model_AdminMenu::SCOPE_ADMIN; break;
    		case 'ecom_navigation_menu': $scope = Admin_Model_AdminMenu::SCOPE_ECOM; break;
    		case 'menu': 
    		default  : $scope = Admin_Model_AdminMenu::SCOPE_SITE; break;
    	}    
    	return $scope;	
    }
    
	public function refreshAction(){
		$request 		= $this->getRequest();
		$task    		= $request->getParam('task'); 	
		$rootType 		= $request->getParam('rootType',null);
		$root_id		= $request->getParam('root_id',null);
		$suffix    		= ('admin' == $rootType) ? 'admin_menu' :   'menu';		 
 	
		$output         = ''; 
			
		switch($task){
			case 'tree':{
				if($root_id and $rootType){
					$menuOption 	= array('root_id' => $root_id);	 
					$menus			= $this->_utility->buildNavigation($menuOption, $suffix);	
					$output         = $this->view->navigation()->menu()->renderMenu($menus['container']);				
				}
				break;
			}
			
			case 'ophan':{
	  			$res      			= Admin_Model_AdminMenu::getOphanMenus();	  	
	  			$output  			= Rhema_Util::generateOptionArray($res, 'id', 'label', false);					
				break;
			}
		}
		
		Rhema_Util::setAjaxData(Zend_Json::encode($output));
	}   
}