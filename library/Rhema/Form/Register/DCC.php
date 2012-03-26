<?php
	class Rhema_Form_Register_DCC extends Rhema_Form_Abstract{
		public function init(){
			parent::init();
			
			$util  			= Rhema_Util::getInstance();
			$locale 		= Zend_Registry::get('Zend_Locale');
			$countryList 	= $locale->getTranslationList('Territory',$locale, 2);
			$salut		    = Rhema_Model_Service::factory('salutation')->findAll();
			$clientIp       = Rhema_Util::getClientIp();
			asort($countryList);
			
			$this->addElement('select', 'salutation_id', array(
				'label'			=> 'Title',
			    'value'			=> '1',
				'multiOptions'	=> Rhema_Util::generateOptionArray($salut, 'id', 'title', true),
				'decorators' 	=> $this->getElemDecor('salutation_id'),
			));	 
			
			$this->addElement('text', 'firstname', array(
				'label'			=> 'First Name',
	            'required'   	=> true,
	            'filters'    	=> array('StringTrim', 'StripTags'),
	            'decorators' 	=> $this->getElemDecor('firstname'),
	            'description'	=> ''
			));

			$this->addElement('text', 'lastname', array(
				'label'			=> 'Surname',
	            'required'   	=> true,
	            'filters'    	=> array('StringTrim', 'StripTags'),
	            'decorators' 	=> $this->getElemDecor('lastname'),
	            'description'	=> ''
			));			

			$this->addElement('text', 'line1', array(
				'label'			=> 'Address 1',
	            'required'   	=> true,
	            'filters'    	=> array('StringTrim', 'StripTags'),
	            'decorators' 	=> $this->getElemDecor('line1'),
	            'description'	=> ''
			));

			$this->addElement('text', 'line2', array(
				'label'			=> 'Address 2',
	            'required'   	=> false,
	            'filters'    	=> array('StringTrim', 'StripTags'),
	            'decorators' 	=> $this->getElemDecor('line2'),
	            'description'	=> ''
			));	
			$this->addElement('text', 'line3', array(
				'label'			=> 'Address 3',
	            'required'   	=> false,
	            'filters'    	=> array('StringTrim', 'StripTags'),
	            'decorators' 	=> $this->getElemDecor('line3'),
	            'description'	=> ''
			));

			$this->addElement('text', 'city', array(
				'label'			=> 'City',
	            'required'   	=> true,
	            'filters'    	=> array('StringTrim', 'StripTags'),
	            'decorators' 	=> $this->getElemDecor('city'),
	            'description'	=> ''
			));

			$this->addElement('text', 'state', array(
				'label'			=> 'State',
	            'required'   	=> false,
	            'filters'    	=> array('StringTrim', 'StripTags'),
	            'decorators' 	=> $this->getElemDecor('state'),
	            'description'	=> ''
			));
						
			$this->addElement('text', 'region', array(
				'label'			=> 'Region',
				'decorators' 	=> $this->getElemDecor('region'),
	            'filters'    	=> array('StringTrim', 'StripTags'),
			));	
			
			
			$this->addElement('select', 'country', array(
				'label'			=> 'country',
	            'required'   	=> true,
	            'decorators' 	=> $this->getElemDecor('country'),
			    'value'			=> $locale->getRegion(),
			    'multiOptions'	=> $countryList 
			));	
			
			$this->addElement('text', 'post_code', array(
				'label'			=> 'Post Code',
	            'required'   	=> true,
			//    'validators'	=> array('PostCode'),
	            'decorators' 	=> $this->getElemDecor('post_code'),
	            'filters'    	=> array('StringTrim', 'StripTags', 'StringToUpper'),
	            
	            'description'	=> ''
			));		
			$this->addElement('text', 'telephone', array(
				'label'			=> 'Telephone',
	            'required'   	=> true, 
				'validators'	=> array('Digits'),
	            'filters'    	=> array('StringTrim', 'StripTags'),
	            'decorators' 	=> $this->getElemDecor('telephone'),
	            'description'	=> ''
			));	
			
			$this->addElement('text', 'email', array(
				'label'			=> 'Email',
	            'required'   	=> true,
			    'value'			=> '',			    
			    'validators'	=> array('EmailAddress'),
	            'filters'    	=> array('StringTrim', 'StripTags', 'StringToLower'),
	            'decorators' 	=> $this->getElemDecor('email'),
	            'description'	=> ''
			));	
			
			$this->addElement('text', 'username', array(
				'label'			=> 'Confirm Email',
	            'required'   	=> true,
			    'value'			=> '',	
			    'validators'	=> array('EmailAddress'),
			    'autocomplete'  => 'off',		    
			   // 'validators'	=> array('Callback' => array(new Admin_Model_AddressBook(), 'isUniqueEmail')),
	            'filters'    	=> array('StringTrim', 'StripTags', 'StringToLower'),
	            'decorators' 	=> $this->getElemDecor('username'),
	            'description'	=> ''
			));	
						
			$this->addElement('password', 'password', array(
				'label'			=> 'Password',
	            'required'   	=> true,
				'value'			=> '',
			    'autocomplete'  => 'off',
			    'validators'	=> array(array('validator' => 'StringLength', 'min' => 6) , 'Alnum'),
	            'filters'    	=> array('StringTrim', 'StripTags', 'StringToLower'),
	            'decorators' 	=> $this->getElemDecor('password'),
	            'description'	=> ''
			));
			
			$this->addElement('password', 'confirm_password', array(
				'label'			=> 'Confirm Password',
	            'required'   	=> true,
			    'autocomplete'  => 'off',
			  //  'validators'	=> array(array('validator' => 'Identical')),
	            'filters'    	=> array('StringTrim', 'StripTags', 'StringToLower'),
	            'decorators' 	=> $this->getElemDecor('confirm_password'),
	            'description'	=> ''
			));			

			$this->addElement('radio', 'is_mailing', array(
				'label'			=> 'Would you like to be on our mailing list ?',
	            'required'   	=> true,
			    'value'			=> 1,
	            'decorators' 	=> $this->getElemDecor('is_mailing'),
				'multiOptions'	=> array(0 => 'No', 1 => 'Yes', 2 => 'Already On'),
	            'description'	=> ''
			));	
			
			$this->addElement('textarea', 'findus', array(
				'label'			=> 'How did you find us?',
	            'required'   	=> true,
				'filters'    	=> array('StringTrim', 'StripTags'),
	            'decorators' 	=> $this->getElemDecor('findus'), 
	            'description'	=> '',
			    'rows'			=> 5
			));	
	
			$this->addElement('textarea', 'inquiry', array(
				'label'			=> 'Please leave any additional information , comment, question or query you may have',
	            'decorators' 	=> $this->getElemDecor('inquiry'), 
				'filters'    	=> array('StringTrim', 'StripTags'),
	            'description'	=> '',
			    'rows'			=> 5
			));
						
			$this->addElement('hidden', Rhema_Form_Abstract::RETURN_URL_KEY, array( 
				'decorators' 	=> array('ViewHelper', ),
	            'value'			=> Zend_Controller_Front::getInstance()->getRequest()->getServer('HTTP_REFERRER')
			));	
			
			
			$this->addElement('hidden', 'ip', array(
				'label'			=> '', 
				'validators'    => array('Ip'),
				'decorators' 	=> array('ViewHelper', ),
	            'value'			=> $clientIp
			));	

			$this->addElement( $this->getRecaptchaElement());
			
	        // Add the submit button
	        $this->addElement('submit', __CLASS__, array(
	            'ignore'   		=> true,
	            'label'    		=> 'Register',
	            'order'			=> 1000,
	            'decorators' 	=> $this->buttonDecorators
	        ));
 			
		}
	}