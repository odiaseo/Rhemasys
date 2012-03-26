<?php

class ErrorController extends Zend_Controller_Action{
 
	public function init(){ 
 
	}
	
	public function indexAction(){
		
	}
	public function errorAction(){
		$errors = $this->_getParam('error_handler');
		echo $errors->exception->getMessage();
	}
}