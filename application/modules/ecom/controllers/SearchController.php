<?php
	class Ecom_SearchController extends Zend_Controller_Action { 
	    
	    public function init(){
	    	/* Initialize action controller here */
			parent::init();
			
	    }	 
	     
	    public function indexAction(){
	    	$search		= $this->_getParam('searchType');
	    	if($search){	
	    		$search = strtolower($search); 		
	    		$name   = $search . 'Action';
	    		$this->_helper->viewRenderer->setScriptAction($search);
	    		$this->$name();
	    	}
	    	 
	    }
	    
	    public function categoryAction(){
			$request			= $this->getRequest();
	    	$slug 	 			= $this->_getParam(CATEGORY_MAP);
	    	$page    			= $this->_getParam('page', 1);
	    	$limit   			= $this->_utility->getSetting(1);
	    	$search				= $this->_getParam('searchType');
	    	   	
	    	//$url				= $this->_utility->assemble(array(CATEGORY_MAP => $slug, 'page'=> $page), CATEGORY_ROUTE);
	    	$url				= $this->baseHref . "/category/$slug";
	    	
	    	$display_type       = $this->_getParam('display_type', 3);	    	
	    	$templateId		    = $this->_utility->getSessData('display_template_id_' . $display_type); 	    	
	    	$displayTemplate    = $this->getCached('displaytemplate')->getDisplayTemplate($templateId, $display_type);	 
	    	  	     	 
	    	$pageLayout   		= Ecom_Model_EcomProductCategory::getSearchLayout($slug, CATEGORY_MAP, $limit, $page, $url);	    							
	    						
	    	$pager			 				= $pageLayout->getPager(); 
	    	$this->view->displayTemplate    = $displayTemplate;
	    	$this->view->products			= $pager->execute();
	    	$this->view->nav 				= $pageLayout;
	    	 
	    }	    
	    
	    public function branchAction(){
			$request				= $this->getRequest(); 
	    	$slug 					= $this->_getParam(CATEGORY_MAP); 
	    	$page    				= $this->_getParam('page', 1); 	    	
	    	$display_type 			= 2;
	    	$children				= array();
	    	$url					= $this->baseHref . "/branch/$slug";
	    	
	    	$templateId		    	= $this->_utility->getSessData('display_template_id_' . $display_type);
	    	$displayTemplate    	= $this->getCached('displaytemplate')->getDisplayTemplate($templateId, $display_type);	
    	    $pageLayout 	    	= Ecom_Model_EcomNavigationMenu::getSubCategoryLayout($slug, $page, $url);  
    	      	    
	    	$pager			 		= $pageLayout->getPager(); 	    	
	    	$res					= $pager->execute();	    	
	    	$category 				= $res[0];
		
			if($category){
				$node     			= $category->getNode();
				$children 			= $node->getChildren();
			}
   	
	    	$this->view->category			= $children;
	    	$this->view->nav 				= $pageLayout;
	    	$this->view->displayTemplate    = $displayTemplate;
	    }
	    
	    public function getDisplayTemplate($templateId, $displayType){
	    	//return Ecom_Model_EcomAttribute::getAttributeTemplates($templateId, $displayType);
	    	return Ecom_Model_EcomTemplateAttribute::getAttributes($templateId, $displayType);
	    }
	     
	    public function productAction(){
	    	$productId				= $this->_getParam('product_id');
	    	$model					= ECOM_PREFIX . 'EcomProduct';
	    	
	    	$display_type 			= 1;
	    	$templateId		    	= $this->_utility->getSessData('display_template_id_' . $display_type);
	    	$displayTemplate    	= $this->getCached('displaytemplate')->getDisplayTemplate($templateId, $display_type);
	    	
	    	$this->view->displayTemplate    = $displayTemplate;	
	    	$this->view->product 			= Doctrine_Core::getTable($model)->find($productId);
	    	
	    	
	    	//$this->_utility->setHeaderFiles(array('/featuredimagezoomer.js'));
	    }
	}