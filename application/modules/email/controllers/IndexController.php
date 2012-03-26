<?php

class Email_IndexController extends Zend_Controller_Action{

    public function init(){
    
        /* Initialize action controller here */
        parent::init();

    }

    public function indexAction(){
		 
    }
    
        
    public function sendAction(){
    	$tr = new Zend_Mail_Transport_Smtp('mail.rhemastudio.com');
    	Zend_Mail::setDefaultTransport($tr);
    	
    	$r = getcwd();
    	$str = file_get_contents($r.'/magnet_email.html');
    	$mail = new Zend_Mail();
    	$mail->setBodyHtml($str) ;
    	$mail->setFrom('info@rhema-webdesign.com');
    	$mail->addTo('info@rhemastudio.com','ade@reveal.plus.com');
    	$mail->setSubject('Fridge Magnets');
    	$mail->send();	
    }
}