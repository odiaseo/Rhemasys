<?php
	class Elm_IndexController extends Zend_Controller_Action { 
	    
	    public function init(){
	    	/* Initialize action controller here */
			parent::init();
			
	    }	 
	       
		public function indexAction(){
		}
		
		public function loginAction(){ 
			$url	= array(  'module' 		=> DEFAULT_MODULE
							, 'controller' 	=> 'index'
							, 'action'	  	=> 'login');			
			if(Zend_Auth::getInstance()->hasIdentity()){
				$this->getAuthService()->clear();
			}
			
			$this->_model 	= MODEL_PREFIX  . 'User';
			$request   		= $this->getRequest(); 
			$loginForm 		= new Admin_Form_Login();  
			$action         = $this->_utility->assemble($url,FRONT_MENU_ROUTE);
			$loginForm->setAction($action);
			
		 	if($this->getRequest()->isPost()){
				$submittedData = $request->getPost();	
				if ($loginForm->isValid($submittedData)) {			
					if(false === $this->getAuthService()->authenticate($submittedData)){
						$loginForm->setDescription('Login failed, please try again.');
					}else{	 
						$returnTo = $this->_utility->getSessData('returnto');
						$this->removeCacheFiles(); 
						
						if($returnTo){
							$returnTo = str_replace(BASE_URL, '', $returnTo);
							$this->_utility->unsetSessData('returnto');
							$this->_redirect($returnTo); 
						}else{
							$url['module'] 		= DEFAULT_MODULE;
							$url['controller'] 	= 'index';
							$url['action'] 		= 'index';
							
							$returnto           = $this->view->url($url, FRONT_MENU_ROUTE);
							$returnTo 			= str_replace(BASE_URL, '', $returnTo);
							$this->_redirect($returnTo);
							//$this->_helper->redirector()->gotoRoute($url, FRONT_MENU_ROUTE);				         
						}
					}
				} 
	 		}
	 		
	 	   $this->view->form = $loginForm;  
		}
		
		public function bannerAction(){
	    	//$output     = $this->view->render('index/banner.phtml');	    	 
	    	//return  $output;			
		}
		
		public function featureAction(){
			$feat       			= $this->_utility->getCached()->getFeaturedItems();
			$this->view->keys       = array(0,1,2,3); //array_rand($feat, 4);
			$this->view->featured 	= $feat;
	    	//$output     			= $this->view->render('index/feature.phtml');	    	 
	    	//return  $output;			
		}
		
		public function mediaAction(){
			
		}
		
		public function searchAction(){
			$form 					= new Rhema_Form_Search_Simple();
			
			$parm['module']			= 'storefront';
			$parm['controler']      = 'index';
			$parm['action']			= 'site-search';
			$action				    = $this->view->url($parm, FRONT_MENU_ROUTE);
			
			$form->setAction($action);
			$form->setMethod('post');
			
			$this->view->searchForm = $form;
	    	//$output     			= $this->view->render('index/search.phtml');	    	 
	    	//return  $output;			
		}
		
		public function resultAction(){
			
		}
		
		public function latestnewsAction(){
			
		}
		
		public function contactAction(){
			$contactForm 		= new Admin_Form_Contact();
			$this->view->form 	= $contactForm;
		}
	}