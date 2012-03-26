<?php
	class Elm_FormController extends Zend_Controller_Action{
		
	    public function init(){
	    	/* Initialize action controller here */
			parent::init();
			
	    }	 
	       
		public function indexAction(){
		}

		public function contactAction(){
 			$mail     = 'info@rhema-webdesign.com';
			$mailHide = new Zend_Service_ReCaptcha_MailHide(PUBLIC_KEY, PRIVATE_KEY, $mail);  
			$this->view->mailto = $mailHide;	
			 		
		}
	}