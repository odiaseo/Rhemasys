<?php

class Blog_IndexController extends Zend_Controller_Action{

    public function init(){
    
        /* Initialize action controller here */
        parent::init();

    }

    public function indexAction(){
		 
    }
    
    public function categoryAction(){    	
    	$this->_table 				=  'blog_category';
    	$this->_helper->displayGrid();  
    }

    public function commentAction(){    	
    	$this->_table 				=  'blog_comment';
    	$this->_helper->displayGrid(); 
 
    }
         
    public function addpostAction(){ 	
    	$this->_table 				=  'blog_post';
    	$this->_helper->displayGrid(); 
    }

}