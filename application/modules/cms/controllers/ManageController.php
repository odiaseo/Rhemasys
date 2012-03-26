<?php

/**
 * ManageController
 * 
 * @author
 * @version 
 */

require_once 'Zend/Controller/Action.php';

class Cms_ManageController extends Zend_Controller_Action {
	/**
	 * The default action - show the home page
	 */
 
	public function indexAction() {
		// TODO Auto-generated ManageController::indexAction() default action
	}
	
	public function eventAction(){   	
    	$this->_request->setParam('table', 'event');
    	$this->_helper->displayGrid(); 	
	}
 
	public function newsAction(){   	
    	$this->_request->setParam('table', 'news');
    	$this->_helper->displayGrid(); 
	}
	
	public function userAction(){   	
    	$this->_request->setParam('table', 'user');
    	//$this->_request->setParam('sidx',$this->_request->getParam('sidx', 'lastname'));
    	$this->_helper->displayGrid();  
	}
	
	public function portfolioAction(){
    	$this->_request->setParam('table', 'portfolio');
    	$this->_helper->displayGrid(); 		
	}
	
	public function reviewAction(){
    	$this->_request->setParam('table', 'portfolio_comment');
    	$this->_helper->displayGrid(); 		
	}
}
