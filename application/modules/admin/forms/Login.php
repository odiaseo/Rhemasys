<?php
	class Admin_Form_Login extends Rhema_Form_Login_Abstract {		
		
		protected $_loggedInUser ;
		protected $_isLoggedIn = false;
		
 
		public function __construct($options=null){	
			 
			parent::__construct($options);
			
		/*	$this->setMethod('post');
			$this->setDescription('Member Login');
 
						
	        $this->addElement('text', 'username', array(
	            'label'      => 'Username:',
	            'required'   => true,
	            'filters'    => array('StringTrim'),
	        ));

	        $this->addElement('password', 'password', array(
	            'label'      => 'Password:',
	            'required'   => true,
	            'filters'    => array('StringTrim'),
	        ));
	        
	
	        // Add a captcha
	         $this->addElement('captcha', 'captcha', array(
	            'label'      => 'Please enter the 5 letters displayed below:',
	            'required'   => true,
	            'captcha'    => array(
	                'captcha' => 'Figlet', 
	                'wordLen' => 8, 
	                'timeout' => 300
	            )
	        ));
	
	        // Add the submit button
	        $this->addElement('submit', 'submit', array(
	            'ignore'   => true,
	            'label'    => 'Login',
	            'decorators' => array('ViewHelper',array('HtmlTag',array('tag' => 'dd','id' =>'form-submit')))
	        ));
	
	        /* And finally add some CSRF protection
	        $this->addElement('hash', 'csrf', array(
	            'ignore' => true,
	        ));*/

		} 
			
 
	}