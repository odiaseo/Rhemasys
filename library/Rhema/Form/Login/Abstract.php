<?php
	abstract class Rhema_Form_Login_Abstract extends Rhema_Form_Abstract {

		protected $_loggedInUser ;
		protected $_isLoggedIn = false;


		public function __construct($options=null){

			parent::__construct($options);
			$loginUrl = $this->getView()->url(array(), 'site-login-page');
			
			$this->setMethod('post')
				 ->setAction($loginUrl);
			
			//$this->setDescription('Member Login');


	        $this->addElement('text', 'username', array(
	            'label'      => 'Username:',
	            'required'   => true,
	            'filters'    => array('StringTrim'),
	            'decorators' => $this->getElemDecor('username'),
	            'description'	=> '',
	            'order'		 => 5
	        ));

	        $this->addElement('password', 'password', array(
	            'label'      => 'Password:',
	            'required'   => true,
	            'filters'    => array('StringTrim'),
	            'decorators' => $this->getElemDecor('password'),
	        	'order'		 => 10
	        ));


	      // $this->addElement( $this->getRecaptchaElement());
	       
	        // Add the submit button
 			$this->addElement( $this->getSubmitButton('Login'));	
		}


	}