<?php

class Elm_PostController extends Zend_Controller_Action{

    public function init(){    
        /* Initialize action controller here */
        parent::init();
    }

    public function indexAction(){
		 
    }
    
    public function summaryAction(){
    	
    }
    
    public function detailAction(){
    	$request	= $this->getRequest();
    	$params 	= $request->getParams();
    	$slug		= $request->getParam('slug');
    	
    	if($slug){
    		$this->view->post = $this->getCached()->getPost($slug);
    	}
    	
    }
    
    public function categoryAction(){
    	
    }
    
    public function archiveAction(){
    	
    }

	public function latestpostAction(){
		$this->view->latest = $this->getCached()->topPosts();
	}
	
	public function topPosts(){
		return Blog_Model_BlogPost::getTopPosts();
	}
	
	public function getPost($slug){
		return Blog_Model_BlogPost::getItem($slug);
	}
}